<?php
// Vista de edición/creación de infracciones

session_start();

// Incluir el controlador y los modelos para cargar los datos de las listas
require_once __DIR__ . '/../../controllers/InfractionsController.php';
require_once __DIR__ . '/../../controllers/SanctionTypesController.php';
require_once __DIR__ . '/../../models/InfractionsModel.php';

$infractionsController = new InfractionsController();
$sanctionTypesController = new SanctionTypesController();

$idEdit = $_GET['id'] ?? null;
$is_edit = !empty($idEdit);
$infraction = null;
$page_title = 'Registrar Nueva Infracción';
$errors = [];
$form_data = [
    'awardee_id' => '',
    'stall_id' => '',
    'infraction_type_id' => '',
    'infraction_datetime' => date('Y-m-d H:i:s'),
    'infraction_description' => '',
    'infraction_status' => 'Reported',
    'inspector_observations' => '',
    'proof' => '',
];

// --- Cargar las listas de selección para los campos del formulario ---
$infractionsModel = new InfractionsModel();

$awardees = $infractionsModel->getAwardeesList();

// -> Nuevo: convertir la lista a un array seguro con solo los campos necesarios
$awardees_js = array_map(function($a){
    return [
        'id' => (int)($a['id'] ?? 0),
        'name' => trim($a['full_name'] ?? '')
    ];
}, $awardees);

$infraction_types = $infractionsModel->getInfractionTypesList();

$stalls =  $infractionsModel->getStallsList();
$stalls_js = array_map(function($s){
    return [
        'id' => (int)($s['id'] ?? 0),
        'stall_number' => $s['stall_number'] ?? '',
        'awardee_id' => (int)($s['awardee_id'] ?? 0),
        'awardee_name' => trim($s['awardee_full_name'] ?? '')
    ];
}, $stalls);

$sanction_types = $sanctionTypesController->index()['sanction_types'];

// Si estamos editando, obtener los datos de la infracción
if ($is_edit) {
    $result = $infractionsController->edit($idEdit);
    
    if (!$result['success']) {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => $result['message']
        ];
        header('Location: index.php');
        exit;
    }
    
    $infraction = $result['infraction'];
    $page_title = $result['page_title'];
    
    $form_data['awardee_id'] = $infraction['awardee_id'];
    $form_data['stall_id'] = $infraction['stall_id'];
    $form_data['infraction_type_id'] = $infraction['infraction_type_id'];
    // Solo la fecha para el input type="date"
    $form_data['infraction_datetime'] = date('Y-m-d', strtotime($infraction['infraction_datetime']));
    $form_data['infraction_status'] = $infraction['infraction_status'];
    $form_data['infraction_description'] = $infraction['infraction_description'];
    $form_data['inspector_observations'] = $infraction['inspector_observations'];
    $form_data['proof'] = $infraction['proof'] ?? '';
}

// Procesar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fileImagen = '';

    if (isset($_FILES['proof']) && $_FILES['proof']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../public/uploads/infractions/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = $_FILES['proof']['name'];
        $fileTmpName = $_FILES['proof']['tmp_name'];
        $fileSize = $_FILES['proof']['size'];
        $fileType = $_FILES['proof']['type'];
        $fileError = $_FILES['proof']['error'];

        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'JPG', 'jpeg', 'png', 'mp4', 'mov'];
        if (in_array($fileExt, $allowedExtensions)) {
            // Verificar el tamaño del archivo (5MB en este caso)
            if ($fileSize < 5000000) {
                // Generar un nombre único para evitar colisiones
                $newFileName = uniqid() . '.' . $fileExt;
                $fileDestination = $uploadDir . $newFileName;
                            
                // Mover el archivo subido al directorio de destino
                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    $fileImagen = $newFileName;
                } else {                    
                    $errors[] = 'Error al mover el archivo subido.';
                }
            } else {
                $errors[] = 'El archivo es demasiado grande (máximo 5MB).';
            }
        } else {
            $errors[] = 'Tipo de archivo no permitido.';
        }
    }

    // Si no se subió un nuevo archivo y estamos editando, mantenemos el anterior
    if ($is_edit && empty($fileImagen) && isset($infraction['proof'])) {
        $fileImagen = $infraction['proof'];
    }            

    $infractionType = $infractionsModel->getInfractionTypeById($_POST['infraction_type_id'] ?? 0);
    // Validaciones básicas
    $form_data = [
        'awardee_id' => trim($_POST['awardee_id'] ?? ''),
        'stall_id' => trim($_POST['stall_id'] ?? ''),
        'infraction_type_id' => trim($_POST['infraction_type_id'] ?? ''),
        'infraction_datetime' => trim($_POST['infraction_datetime']. ' 00:00:00' ?? 'NULL'),
        'infraction_description' => trim($_POST['infraction_description'] ?? ''),
        'infraction_status' => trim($_POST['infraction_status'] ?? 'Reported'),
        'sanction_type_id' => filter_input(INPUT_POST, 'sanction_type_id', FILTER_SANITIZE_NUMBER_INT),
        'effect_end_date' => trim($_POST['effect_end_date'] .'00:00:00' ?? 'NULL'),
        'inspector_observations' => trim($_POST['inspector_observations'] ?? ''),
        'proof' => $fileImagen,
    ];

    // Usar el ID de la infracción en la actualización

    if (empty($errors)) {
        if ($is_edit) {
            $result = $infractionsController->update($idEdit, $form_data);
        } else {
            $result = $infractionsController->store($form_data);
        }

        if ($result['success']) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => $result['message']
            ];
            header('Location: ' . $result['redirect']);            
            exit;
        } else {            
            $errors = $result['errors'] ?? [$result['message']];
        }
    }
}

// Incluir header y layouts
require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="card-title d-flex align-items-center mb-1" style="font-size: 1.4rem;font-weight: 600;">
                                <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="<?php echo $is_edit ? 'ri-edit-2-line' : 'ri-alert-line'; ?>" style="color: #696cff; font-size: 1.5rem;"></i></div>
                                <?php echo htmlspecialchars($page_title); ?>
                            </h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="index.php">Infracciones</a></li>
                                    <?php if ($is_edit): ?>
                                    <li class="breadcrumb-item">
                                        <a href="view.php?id=<?php echo htmlspecialchars($idEdit); ?>">Infracción #<?php echo htmlspecialchars($idEdit); ?></a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">Editar</li>
                                    <?php else: ?>
                                    <li class="breadcrumb-item active" aria-current="page">Registrar Nuevo</li>
                                    <?php endif; ?>
                                </ol>
                            </nav>
                        </div>
                        <div class="d-flex gap-2">
                            <?php if ($is_edit): ?>
                            <a href="../../index.php?report=infraction_invoice.rep&action=view&id=<?php echo htmlspecialchars($idEdit); ?>" 
                               class="btn btn-primary" 
                               target="_blank">
                                <i class="ri-file-list-3-line"></i> 
                                Ver Factura
                            </a>
                            <?php endif; ?>
                            <a href="<?php echo $is_edit ? 'view.php?id=' . htmlspecialchars($idEdit) : 'index.php'; ?>" class="btn btn-outline-secondary">
                                <i class="ri-arrow-left-line"></i> 
                                <?php echo $is_edit ? 'Volver a detalles' : 'Volver al listado'; ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Campos Incompletos',
                                html: '<?php echo implode("<br>", array_map("htmlspecialchars", $errors)); ?>',
                                confirmButtonText: 'Entendido'
                            });
                        });
                        </script>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" novalidate>
                            <!-- Adjudicatario and Tipo de Infracción - Side by Side -->
                            <!-- Stall and Awardee - Side by Side -->
                            <div class="row">
                                <div class="col-md-6">
                                     <div class="mb-3">
                                         <label for="stall_search" class="form-label">
                                             Puesto <span class="text-danger">*</span>
                                         </label>
                                         <input list="stalls_datalist" id="stall_search" class="form-control" placeholder="Escriba el número de puesto..." oninput="autoSelectAwardeeByStall()" onchange="autoSelectAwardeeByStall()" required>
                                         <input type="hidden" name="stall_id" id="stall_id" value="<?php echo htmlspecialchars($form_data['stall_id']); ?>">
                                         <datalist id="stalls_datalist">
                                             <?php foreach ($stalls as $stall): ?>
                                                 <option value="<?php echo htmlspecialchars($stall['stall_number']); ?>" data-id="<?php echo $stall['id']; ?>">
                                             <?php endforeach; ?>
                                         </datalist>
                                     </div>
                                 </div>
                                 <div class="col-md-6">
                                     <div class="mb-3">
                                         <label for="awardee_name_display" class="form-label">
                                             Adjudicatario <span class="text-danger">*</span>
                                         </label>
                                         <input type="text" id="awardee_name_display" class="form-control" readonly placeholder="Se seleccionará automáticamente">
                                         <input type="hidden" name="awardee_id" id="awardee_id" value="<?php echo htmlspecialchars($form_data['awardee_id']); ?>">
                                     </div>
                                 </div>
                            </div>

                            <!-- Infraction Type and Date - Side by Side -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="infraction_type_id" class="form-label">
                                            Tipo de Infracción <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="infraction_type_id" name="infraction_type_id" onchange="selectTipeInfractions()" required>
                                            <option value="">Seleccione un tipo de infracción</option>
                                            <?php foreach ($infraction_types as $type): ?>
                                            <option value="<?php echo htmlspecialchars($type['infraction_type_id']); ?>"
                                                     <?php echo ($form_data['infraction_type_id'] == $type['infraction_type_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($type['infraction_type_name']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="infraction_datetime" class="form-label">
                                            Fecha de la Infracción <span class="text-danger">*</span>
                                        </label>
                                        <input type="date"
                                               class="form-control"
                                               id="infraction_datetime"
                                               name="infraction_datetime"
                                               value="<?php echo htmlspecialchars($form_data['infraction_datetime']); ?>"
                                               max="<?php echo date('Y-m-d'); ?>"
                                               required>
                                    </div>
                                </div>
                            </div>

                            <!-- Resumen de Sanciones - Full Width Card -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-body">
                                            <h6 class="card-subtitle mb-3 text-muted">
                                                <i class="ri-bar-chart-box-line me-1"></i>Resumen de Sanciones
                                            </h6>
                                            <div id="sanction_results" class="mb-3">
                                                <p class="text-muted mb-0"><i class="ri-information-line"></i> Seleccione un adjudicatario para ver el resumen</p>
                                            </div>
                                            <hr class="my-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <label class="form-label mb-0 fw-bold">Sanción a Aplicar:</label>
                                                <input readonly type="hidden" class="form-control" id="sanction_type_id" name="sanction_type_id" 
                                                       value="<?php echo isset($form_data['sanction_type_id']) ? htmlspecialchars($form_data['sanction_type_id']) : ''; ?>">
                                                <span class="badge bg-secondary" id="text_sanction_type_id" style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                                                    N/A
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <!-- Descripción and Observaciones - Side by Side -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="infraction_description" class="form-label">
                                            Descripción <span class="text-danger">*</span>
                                        </label>
                                        <textarea onKeyup="validarText('infraction_description',8,'errorTextLocation')" class="form-control" 
                                                  id="infraction_description" 
                                                  name="infraction_description" 
                                                  rows="5" 
                                                  required><?php echo htmlspecialchars($form_data['infraction_description']); ?>
                                        </textarea>
                                        <div id="errorTextLocation" style="color: red;"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="inspector_observations" class="form-label">
                                            Observaciones del Inspector
                                        </label>
                                        <textarea onKeyup="validarText('inspector_observations',8,'errorTextObservations')" class="form-control" 
                                                  id="inspector_observations" 
                                                  name="inspector_observations" 
                                                  rows="5"><?php echo htmlspecialchars($form_data['inspector_observations']); ?>
                                            </textarea>
                                        <div id="errorTextObservations" style="color: red;"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3" id="box_effect_end_date" style="display: none;">
                                        <label for="effect_end_date" class="form-label">Fecha limite del Efecto de la Sancion</label>
                                        <input type="date" class="form-control" id="effect_end_date" name="effect_end_date" min="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">

                            <!-- Evidencias and Estado - Side by Side -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="proof" class="form-label">
                                            Evidencias (Imagen/Video)
                                        </label>
                                        <?php if ($is_edit && $form_data['proof']): ?>
                                            <div class="alert alert-info">
                                                Archivo actual: <a href="../../public/uploads/infractions/<?php echo htmlspecialchars($form_data['proof']); ?>" target="_blank"><?php echo htmlspecialchars($form_data['proof']); ?></a>.
                                                <br>
                                                Puedes subir uno nuevo para reemplazarlo.
                                            </div>
                                        <?php endif; ?>
                                        <input type="file"
                                               class="form-control"
                                               id="proof"
                                               accept=".jpg, .jpeg, .png, .mp4, .mov"
                                               name="proof">
                                        <div class="form-text">
                                            Formatos permitidos: jpg, jpeg, png, mp4, mov.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">
                                            Estado <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="status" name="infraction_status" required <?php echo !$is_edit ? 'disabled' : ''; ?>>
                                            <option value="Reported" <?php echo ($form_data['infraction_status'] == 'Reported') ? 'selected' : ''; ?>>Reportada</option>
                                            <option value="In Process" <?php echo ($form_data['infraction_status'] == 'In Process') ? 'selected' : ''; ?>>En Proceso</option>
                                            <option value="Resolved" <?php echo ($form_data['infraction_status'] == 'Resolved') ? 'selected' : ''; ?>>Resuelta</option>
                                            <option value="Cancelled" <?php echo ($form_data['infraction_status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelada</option>
                                        </select>
                                        <?php if (!$is_edit): ?>
                                            <div class="form-text">
                                                El estado inicial es "Reportada" y no se puede cambiar al crear.
                                            </div>
                                            <input type="hidden" name="infraction_status" value="Reported">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?php echo $is_edit ? 'view.php?id=' . htmlspecialchars($idEdit) : 'index.php'; ?>" 
                                   class="btn btn-outline-secondary">
                                    <i class="ri-close-line"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-<?php echo $is_edit ? 'warning' : 'primary'; ?>">
                                    <i class="ri-<?php echo $is_edit ? 'save' : 'add'; ?>-line"></i> 
                                    <?php echo $is_edit ? 'Actualizar Infracción' : 'Registrar Infracción'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Inyectar la lista de adjudicatarios en JS (solo id y name)
const AWARDEES = <?php echo json_encode($awardees_js, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;
const awardeeById = new Map(AWARDEES.map(a => [String(a.id), a.name]));

// Inyectar la lista de puestos en JS
const STALLS = <?php echo json_encode($stalls_js, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;

// Inyectar la lista de tipos de infracciones en JS (solo id y sanction_type_id)
const infractionTypes = <?php echo json_encode($infraction_types, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;
const infractionTypeById = new Map(infractionTypes.map(it => [String(it.infraction_type_id), it.sanction_type_id]));
var countSanctions = {};

function getAwardeeName(id) {
    return awardeeById.get(String(id)) || 'N/A';
}

function getInfractionTypeName(id) {
    return infractionTypeById.get(String(id)) || 'N/A';
}

/**
 * Filtra los puestos (stalls) basándose en el adjudicatario seleccionado
 */
/**
 * Selecciona automáticamente el adjudicatario basado en el puesto seleccionado
 */
function autoSelectAwardeeByStall() {
    const searchValue = document.getElementById('stall_search').value.trim();
    if (!searchValue) {
        resetAwardeeFields();
        return;
    }

    const stall = STALLS.find(s => s.stall_number.trim() === searchValue);
    
    const stallIdHidden = document.getElementById('stall_id');
    const awardeeHidden = document.getElementById('awardee_id');
    const awardeeDisplay = document.getElementById('awardee_name_display');
    
    if (stall) {
        stallIdHidden.value = stall.id;
        if (stall.awardee_id) {
            awardeeHidden.value = stall.awardee_id;
            awardeeDisplay.value = stall.awardee_name || getAwardeeName(stall.awardee_id);
            loadInfractionCount(); 
        } else {
            resetAwardeeFields();
        }
    } else {
        stallIdHidden.value = "";
        resetAwardeeFields();
    }
}

function resetAwardeeFields() {
    document.getElementById('awardee_id').value = "";
    document.getElementById('awardee_name_display').value = "";
}

// Inicialización si ya hay un stall_id al cargar (e.g. edición)
document.addEventListener('DOMContentLoaded', function() {
    const initialStallId = document.getElementById('stall_id').value;
    if (initialStallId) {
        const stall = STALLS.find(s => s.id == initialStallId);
        if (stall) {
            document.getElementById('stall_search').value = stall.stall_number;
            autoSelectAwardeeByStall();
        }
    }
});

function getInfractionTypeName(id) {
    return infractionTypeById.get(String(id)) || 'N/A';
}

function selectTipeInfractions() {
    const typeId = document.getElementById('infraction_type_id').value;    
    const infractionTypeId = Number(getInfractionTypeName(typeId) || 1);
    let currentTypeCount = loadInfractionCount();
    determineSanctionSeverity(infractionTypeId, currentTypeCount)
}

function loadInfractionCount() {
    // 1. Obtener los valores de los dos SELECTs
    const awardeeId = document.getElementById('awardee_id').value;
    const typeId = document.getElementById('infraction_type_id').value;    
    const infractionTypeId = Number(getInfractionTypeName(typeId) || 1);

    // Verificar si ambos valores son válidos
    if (!awardeeId || !infractionTypeId) {
        console.warn('Falta seleccionar Awardee ID o Tipo de Infracción.');
        return;
    }

    // 2. Construir la URL de la API
    const apiUrl = `./api_infractions.php?contarInfraccionAnual=1&awardeeId=${awardeeId}&infractionTypeId=${infractionTypeId}`;
    
    // 3. Realizar la solicitud FETCH
    fetch(apiUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error de red o servidor: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            // 4. Obtener el conteo total
            const currentTypeCount = data.count || 0;
            console.log(`Infracciones acumuladas de tipo ${infractionTypeId} en este año: ${currentTypeCount}`);

            // 5. Aplicar la lógica de escalada de severidad
            determineSanctionSeverity(infractionTypeId, currentTypeCount);
        })
        .catch(error => {
            console.error('Hubo un problema al obtener el conteo de la infracción:', error);
            // Fallback: usar el conteo 0 y determinar la severidad por defecto
            determineSanctionSeverity(infractionTypeId, 0); 
        });
}


/**
 * Determina la severidad final de la sanción y actualiza el campo 'sanction_type_id'.
 * * @param {string} infractionTypeId - El ID del tipo de infracción seleccionada.
 * @param {number} currentTypeCount - El número de veces que se ha cometido esta infracción en el año.
 */
function determineSanctionSeverity(infractionTypeId, currentTypeCount) {
    var box_effect_end_date = document.getElementById('box_effect_end_date');

    // Mapeo  de severidad de sanción por defecto (asumido 1=leve, 2=moderada, 3=grave)
    const severityIdMap = {
        '1': '1', // Leve por defecto
        '2': '2', // Moderada por defecto
        '3': '3'  // Grave por defecto        
    };

    const textTypeInfraction = {
        '1': 'leve',      
        '2': 'moderada',
        '3': 'grave'
    };    

    const severityStyleMap = {
        '1': { text: 'LEVE', color: '#4CAF50', background: '#E8F5E9' }, // Verde
        '2': { text: 'MODERADA', color: '#FF9800', background: '#FFF3E0' }, // Naranja
        '3': { text: 'GRAVE', color: '#F44336', background: '#FFEBEE' } // Rojo
    };    

    let finalSanctionId = severityIdMap[infractionTypeId] || '1'; // Usar por defecto el ID de la infracción o '1' (leve)
    const finalStyle = severityStyleMap[finalSanctionId];
    const displayDiv = document.getElementById('text_sanction_type_id');
    const selectedText = textTypeInfraction[finalSanctionId] || 'leve';

    // Lógica de escalado de severidad basada en el conteo:
    
    if (currentTypeCount >= 4) {
        // Ejemplo: Si es la 5ª vez o más, siempre es Grave (ID 3)
        finalSanctionId = '3'; 
        console.log("Escalado: Reincidencia ALTA (>= 3) -> GRAVE");
    } else if (currentTypeCount >= 2) {
        // Ejemplo: Si es la 3ª vez o 4ª vez, Moderada (ID 2) si no es Grave por defecto
        if (finalSanctionId < '3') { // Solo escalar si no era Grave por defecto
            finalSanctionId = '2'; 
            console.log("Escalado: Reincidencia MEDIA (>= 2) -> MODERADA");
        }
    } else {
        // Si el conteo es 0 o 1, usa el ID de severidad por defecto de la infracción
        console.log("Escalado: Sin reincidencia -> Severidad por defecto");
    }

    // Actualizar el campo oculto o select de la severidad de la sanción
    const sanctionInput = document.getElementById('sanction_type_id');
    if (sanctionInput) {
        sanctionInput.value = finalSanctionId;
        displayDiv.textContent = selectedText.charAt(0).toUpperCase() + selectedText.slice(1); // Capitalizar la primera letra        
        console.log(`Sanción final ID establecida en: ${finalSanctionId}`);
    }
    if (displayDiv && finalStyle) {
        displayDiv.textContent = finalStyle.text;
        displayDiv.className = 'badge';
        
        // Aplicar estilos para el color
        displayDiv.style.color = finalStyle.color;
        displayDiv.style.fontWeight = 'bold';
        displayDiv.style.fontSize = '0.9rem';
        displayDiv.style.padding = '0.5rem 1rem';
        displayDiv.style.borderRadius = '5px';
        displayDiv.style.backgroundColor = finalStyle.background;
        displayDiv.style.border = `1px solid ${finalStyle.color}`;
    } else if (displayDiv) {
         // Fallback si no hay estilo (ej. si finalSanctionId no está en severityStyleMap)
        displayDiv.textContent = 'ERROR (ID ' + finalSanctionId + ')';
        displayDiv.className = 'badge bg-secondary';
        displayDiv.style.fontSize = '0.9rem';
        displayDiv.style.padding = '0.5rem 1rem';
    }    
    
    // Mostrar u ocultar la caja de "Fecha limite del Efecto de la Sancion"
    if (infractionTypeId == 3) { // Si es grave
        box_effect_end_date.style.display = 'block';
    } else {
        box_effect_end_date.style.display = 'none';
    }

}

function loadSanctions() {
    // 1. Obtener el valor del ID del adjudicatario desde el input
    const awardeeId = document.getElementById('awardee_id').value;
    
    // Filtrar los puestos basándose en el adjudicatario seleccionado
    filterStallsByAwardee();
    
    // Asume que tienes un elemento donde quieres mostrar los resultados, por ejemplo, un div
    const resultsContainer = document.getElementById('sanction_results');
    
    // Limpiar el contenedor de resultados previos
    if (resultsContainer) {
        resultsContainer.innerHTML = 'Cargando conteo de sanciones...';
    }

    if (!awardeeId || isNaN(awardeeId)) {
        if (resultsContainer) {
            resultsContainer.innerHTML = 'Por favor, ingrese un ID de Adjudicatario válido.';
        }
        return; // Salir si el ID no es válido
    }

    // 2. Construir la URL de la API
    // Asumiendo que la función está en un archivo que usa './api_infractions.php'
    const apiUrl = `./api_infractions.php?contarSancionesPorSeveridad=1&awardeeId=${awardeeId}`;

    // 3. Realizar la solicitud FETCH
    fetch(apiUrl)
        .then(response => {
            // Verificar si la respuesta fue exitosa (código 200-299)
            if (!response.ok) {
                // Lanzar un error si el estado no es OK (ej. 404, 500)
                throw new Error(`Error de red o servidor: ${response.statusText}`);
            }
            return response.json(); // Convertir la respuesta a JSON
        })
        .then(data => {
            // 4. Procesar y mostrar los datos
            console.log('Conteo de Sanciones:', data);

            if (resultsContainer) {
                const awardeeName = getAwardeeName(awardeeId);
                if (data.leve !== undefined) {
                    // Si la API devuelve el formato esperado
                    countSanctions = data; // Guardar los datos globalmente si es necesario
                    let html = `
                        <div class="mb-2">
                            <strong class="d-block mb-2">${awardeeName}</strong>
                            <small class="text-muted">ID: ${awardeeId}</small>
                        </div>
                        <div class="row g-2 mt-2">
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-success me-2" style="width: 12px; height: 12px; padding: 0;"></span>
                                    <span class="text-muted small">Leves:</span>
                                    <span class="ms-auto fw-bold">${data.leve}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-warning me-2" style="width: 12px; height: 12px; padding: 0;"></span>
                                    <span class="text-muted small">Moderadas:</span>
                                    <span class="ms-auto fw-bold">${data.moderada}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-danger me-2" style="width: 12px; height: 12px; padding: 0;"></span>
                                    <span class="text-muted small">Graves:</span>
                                    <span class="ms-auto fw-bold">${data.grave}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary me-2" style="width: 12px; height: 12px; padding: 0;"></span>
                                    <span class="text-muted small">Total:</span>
                                    <span class="ms-auto fw-bold">${data.total ?? (data.leve + data.moderada + data.grave)}</span>
                                </div>
                            </div>
                        </div>
                    `;
                    resultsContainer.innerHTML = html;
                } else {
                    // Si la API devuelve un error o un formato inesperado
                    resultsContainer.innerHTML = `Error: Formato de datos no reconocido o ID sin sanciones.`;
                    console.error('Datos inesperados:', data);
                }
            }
        })
        .catch(error => {
            // 5. Manejar errores de la solicitud (red, JSON malformado, error del servidor)
            console.error('Hubo un problema con la operación fetch:', error);
            if (resultsContainer) {
                resultsContainer.innerHTML = `❌ Error al cargar sanciones: ${error.message}`;
            }
        });
}
</script>
<?php include __DIR__ . '/../layouts/footer.php'; ?>