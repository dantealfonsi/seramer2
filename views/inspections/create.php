<?php
// Vista de creación de reportes de inspección

session_start();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/InspectionController.php';

$inspectionReportsController = new InspectionController();

$data = $inspectionReportsController->create();
extract($data); // Extrae $inspectors, $stalls, $awardees, $users

// Inicialización de $users si no se extrajo (asumiendo que viene del controlador)
if (!isset($users)) {
    $users = [];
}

$page_title = 'Registrar Nuevo Reporte de Inspección';
$errors = [];
$form_data = [
    'scheduled_inspection_id' => '',
    'main_inspector_id' => $_SESSION['user_id'] ?? '',
    'assistant_inspector_id' => '',
    'stall_id' => '',
    'awardee_id' => '',
    'general_observations' => '',
    'inspector_signature_url' => '', 
    'assistant_signature_url' => '', 
    'scheduled_date' => '', 
    'inspection_type' => 'Rutine', 
    'assigned_responsible_id' => $_SESSION['user_id'],
    'inspection_status' => 'Pending', 
    'observations' => '',
];

// Variable para controlar la vista inicial en caso de error de POST
$show_scheduled_inspections = false;

// Procesar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura de datos del formulario POST, incluyendo los de la segunda parte
    $form_data = [
        'main_inspector_id' => trim($_POST['main_inspector_id'] ?? ''),
        'assistant_inspector_id' => trim($_POST['assistant_inspector_id'] ?? ''),
        'stall_id' => trim($_POST['stall_id'] ?? ''),
        'awardee_id' => trim($_POST['awardee_id'] ?? ''),
        'general_observations' => trim($_POST['general_observations'] ?? ''),
        'inspector_signature_url' => trim($_POST['inspector_signature_url'] ?? ''),
        'assistant_signature_url' => trim($_POST['assistant_signature_url'] ?? ''),
        'scheduled_date' => trim($_POST['scheduled_date'] ?? ''), 
        'inspection_type' => trim($_POST['inspection_type'] ?? ''), 
        'assigned_responsible_id' => trim($_SESSION['user_id']), 
        'inspection_status' => trim($_POST['inspection_status'] ?? ''), 
        'observations' => trim($_POST['observations'] ?? ''), 
    ];

    $inspection_report_data = [
        'main_inspector_id' => $form_data['main_inspector_id'],
        'assistant_inspector_id' => $form_data['assistant_inspector_id'],
        'stall_id' => $form_data['stall_id'],
        'awardee_id' => $form_data['awardee_id'],
        'general_observations' => $form_data['general_observations'],
        'inspector_signature_url' => $form_data['inspector_signature_url'],
        'assistant_signature_url' => $form_data['assistant_signature_url'],
    ];

    $scheduled_inspection_data = [
        'scheduled_date' => $form_data['scheduled_date'],
        'inspection_type' => $form_data['inspection_type'],
        'assigned_responsible_id' => $_SESSION['user_id'],
        'inspection_status' => $form_data['inspection_status'],
        'observations' => $form_data['observations'],
    ];

    $data_to_store = array_merge($inspection_report_data, $scheduled_inspection_data);

    $result = $inspectionReportsController->store($data_to_store);

    if ($result['success']) {
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => $result['message']
        ];
        header('Location: ' . $result['redirect']);
        exit;
    } else {
        $errors = $result['errors'] ?? [$result['message']];
        // Si hay errores, forzamos la visualización de la segunda pestaña 
        $show_scheduled_inspections = true;
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
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Reportes de Inspección</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Registrar Nuevo</li>
                    </ol>
                </nav>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="font-size: 2rem;font-weight: 600;">
                            <i class="ri-file-add-line me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i> Volver al listado
                        </a>
                    </div>

                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h6 class="alert-heading">
                                    <i class="ri-error-warning-line"></i> Se encontraron errores:
                                </h6>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="create.php" novalidate>
                            <div id="inspection-form" style="display: <?php echo $show_scheduled_inspections ? 'none' : 'block'; ?>;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="main_inspector_id" class="form-label">
                                                Inspector Principal <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="main_inspector_id" name="main_inspector_id" required>
                                                <option value="">Seleccione un inspector</option>
                                                <?php foreach ($inspectors as $inspector): ?>
                                                    <option value="<?php echo htmlspecialchars($inspector['inspector_id']); ?>"
                                                             <?php echo ((int)$form_data['main_inspector_id'] == (int)$inspector['inspector_id']) ? 'selected' : ''; ?>>
                                                         <?php echo htmlspecialchars($inspector['full_name']); ?>
                                                     </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="assistant_inspector_id" class="form-label">
                                                Inspector Auxiliar
                                            </label>
                                            <select class="form-select" id="assistant_inspector_id" name="assistant_inspector_id">
                                                <option value="">Seleccione un inspector (opcional)</option>
                                                <?php foreach ($inspectors as $inspector): ?>
                                                    <option value="<?php echo htmlspecialchars($inspector['inspector_id']); ?>"
                                                             <?php echo ((int)$form_data['assistant_inspector_id'] == (int)$inspector['inspector_id']) ? 'selected' : ''; ?>>
                                                         <?php echo htmlspecialchars($inspector['full_name']); ?>
                                                     </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="stall_search" class="form-label">
                                                Puesto del Mercado <span class="text-danger">*</span>
                                            </label>
                                            <input list="stalls_datalist" id="stall_search" class="form-control" placeholder="Escriba el número de puesto..." oninput="onStallSelected()" onchange="onStallSelected()" required>
                                            <input type="hidden" name="stall_id" id="stall_id" value="<?php echo htmlspecialchars($form_data['stall_id']); ?>">
                                            <datalist id="stalls_datalist">
                                                <?php foreach ($stalls as $stall): ?>
                                                    <option value="<?php echo htmlspecialchars($stall['stall_number']); ?>" data-id="<?php echo $stall['id']; ?>">
                                                <?php endforeach; ?>
                                            </datalist>
                                        </div>
                                        
                                        <!-- Preparar datos de puestos para JS -->
                                        <?php
                                            $stalls_json = [];
                                            foreach ($stalls as $s) {
                                                $mapping = $stallAwardeeMapping[$s['id']] ?? null;
                                                $stalls_json[] = [
                                                    'id' => $s['id'],
                                                    'stall_number' => $s['stall_number'],
                                                    'awardee_id' => $mapping ? $mapping['id'] : '',
                                                    'awardee_name' => $mapping ? $mapping['name'] : ''
                                                ];
                                            }
                                        ?>
                                        <script>const STALLS_LIST = <?php echo json_encode($stalls_json); ?>;</script>

                                        <div class="mb-3">
                                            <label for="awardee_name_display" class="form-label">
                                                Adjudicatario <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" id="awardee_name_display" class="form-control" readonly placeholder="Se autocompletará al seleccionar el puesto">
                                            <input type="hidden" name="awardee_id" id="awardee_id" value="<?php echo htmlspecialchars($form_data['awardee_id']); ?>">
                                            <small class="text-muted">Se asigna automáticamente al seleccionar el puesto.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="general_observations" class="form-label">
                                                Observaciones Generales <span class="text-danger">*</span>
                                            </label>
                                            <textarea onKeyup="validarLocation('general_observations', 8)" class="form-control"
                                                     id="general_observations"
                                                     name="general_observations"
                                                     rows="5" required><?php echo htmlspecialchars($form_data['general_observations']); ?>
                                            </textarea>
                                            <div id="errorTextLocation" style="color: red;"></div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="inspector_signature_url" value="<?php echo htmlspecialchars($form_data['inspector_signature_url']); ?>">
                                    <input type="hidden" name="assistant_signature_url" value="<?php echo htmlspecialchars($form_data['assistant_signature_url']); ?>">
                                </div>
                                
                                <hr class="my-4">

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="index.php" class="btn btn-outline-secondary">
                                        <i class="ri-close-line"></i> Cancelar
                                    </a>
                                    <button type="button" onclick="nextStep()" class="btn btn-primary">
                                        <i class="ri-arrow-right-line"></i> Siguiente Paso
                                    </button>
                                </div>
                            </div>

                            <div id="scheduled-inspections" style="display: <?php echo $show_scheduled_inspections ? 'block' : 'none'; ?>;">
                                <h4 class="mb-3">Programar Inspección</h4>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="scheduled_date" class="form-label">Fecha Programada <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="scheduled_date" name="scheduled_date" value="<?php echo htmlspecialchars($form_data['scheduled_date']); ?>" min="<?php echo date('Y-m-d'); ?>" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="inspection_type" class="form-label">Tipo de Inspección <span class="text-danger">*</span></label>
                                        <select class="form-select" id="inspection_type" name="inspection_type" required>
                                            <option value="Rutine" <?php echo ($form_data['inspection_type'] == 'Rutine') ? 'selected' : ''; ?>>Rutina</option>
                                            <option value="New Stall" <?php echo ($form_data['inspection_type'] == 'New Stall') ? 'selected' : ''; ?>>Nuevo Puesto</option>
                                            <option value="Complain" <?php echo ($form_data['inspection_type'] == 'Complain') ? 'selected' : ''; ?>>Queja/Denuncia</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="inspection_status" class="form-label">Estado de la Inspección <span class="text-danger">*</span></label>
                                        <select class="form-select" id="inspection_status" name="inspection_status" required>
                                            <option value="Pending" <?php echo ($form_data['inspection_status'] == 'Pending') ? 'selected' : ''; ?>>Pendiente</option>
                                            <option value="In Progress" <?php echo ($form_data['inspection_status'] == 'In Progress') ? 'selected' : ''; ?>>En Progreso</option>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label for="observations" class="form-label">Observaciones</label>
                                        <textarea class="form-control" id="observations" name="observations" rows="4"><?php echo htmlspecialchars($form_data['observations']); ?></textarea>
                                    </div>
                                </div>
                                <hr class="my-4">
                                <div class="d-flex justify-content-between gap-2">
                                    <button type="button" onclick="backStep()" class="btn btn-outline-secondary">
                                        <i class="ri-arrow-left-line"></i> Paso Anterior
                                    </button>
                                    <div class="d-flex gap-2">
                                        <a href="index.php" class="btn btn-outline-secondary">
                                            <i class="ri-close-line"></i> Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-primary" id="btn-submit-inspection">
                                            <i class="ri-save-line"></i> Crear Inspección
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</script>

<script>
// Mapeo de Puesto -> Adjudicatario
const stallAwardeeMapping = <?php echo json_encode($stallAwardeeMapping); ?>;
// Mapeo ID -> Nombre Adjudicatario (para mostrarlo)
const awardeesList = <?php 
    $awJS = [];
    foreach($awardees as $aw) $awJS[$aw['id']] = $aw['full_name'];
    echo json_encode($awJS); 
?>;

function onStallSelected() {
    const searchValue = document.getElementById('stall_search').value.trim();
    const stall = STALLS_LIST.find(s => s.stall_number.trim() === searchValue);
    
    const stallIdHidden = document.getElementById('stall_id');
    const awardeeIdHidden = document.getElementById('awardee_id');
    const awardeeDisplay = document.getElementById('awardee_name_display');
    
    if (stall) {
        stallIdHidden.value = stall.id;
        awardeeIdHidden.value = stall.awardee_id;
        awardeeDisplay.value = stall.awardee_name || "N/A";
    } else {
        stallIdHidden.value = "";
        awardeeIdHidden.value = "";
        awardeeDisplay.value = "";
    }
}

// Inicialización si ya hay un stall_id al cargar (e.g. error de validación)
document.addEventListener('DOMContentLoaded', function() {
    const initialStallId = document.getElementById('stall_id').value;
    if (initialStallId) {
        const stall = STALLS_LIST.find(s => s.id == initialStallId);
        if (stall) {
            document.getElementById('stall_search').value = stall.stall_number;
            onStallSelected();
        }
    }
});
</script>

<script>
/**
 * Función para avanzar del paso 1 (Reporte) al paso 2 (Programación).
 * Incluye validación de campos obligatorios del paso 1.
 */
function nextStep() {
    // Referencias a los campos obligatorios del primer paso
    const mainInspector = document.getElementById('main_inspector_id').value;
    const stall = document.getElementById('stall_id').value;
    const awardee = document.getElementById('awardee_id').value;
    const generalObservations = document.getElementById('general_observations').value.trim();

    let valid = true;
    let requiredFields = [
        { id: 'main_inspector_id', value: mainInspector, name: 'Inspector Principal' },
        { id: 'stall_id', value: stall, name: 'Puesto del Mercado' },
        { id: 'awardee_id', value: awardee, name: 'Adjudicatario' },
        { id: 'general_observations', value: generalObservations, name: 'Observaciones Generales' }
    ];

    // Simple validación (agrega clase 'is-invalid' para feedback visual)
    requiredFields.forEach(field => {
        const element = document.getElementById(field.id);
        if (!field.value) {
            valid = false;
            element.classList.add('is-invalid');
        } else {
            element.classList.remove('is-invalid');
        }
    });
    
    // Si la validación es exitosa, muestra la siguiente sección
    if (valid) {
        document.getElementById('inspection-form').style.display = 'none';
        document.getElementById('scheduled-inspections').style.display = 'block';
        window.scrollTo(0, 0); // Opcional: desplazar al inicio de la página para ver el nuevo formulario
        validateStep2(); // Validar estado inicial del botón en el paso 2
    } else {
        Swal.fire({
            icon: 'warning',
            title: 'Campos Incompletos',
            text: 'Complete todos los campos obligatorios (marcados con *) antes de avanzar.',
            confirmButtonText: 'Entendido'
        });
    }
}

/**
 * Función para retroceder del paso 2 (Programación) al paso 1 (Reporte).
 */
function backStep() {
    document.getElementById('scheduled-inspections').style.display = 'none';
    document.getElementById('inspection-form').style.display = 'block';
    window.scrollTo(0, 0); // Opcional: desplazar al inicio de la página
}

/**
 * Valida los campos del paso 2 para habilitar/deshabilitar el botón de crear.
 */
function validateStep2() {
    const scheduledDate = document.getElementById('scheduled_date').value;
    const inspectionType = document.getElementById('inspection_type').value;
    const inspectionStatus = document.getElementById('inspection_status').value;
    const btnSubmit = document.getElementById('btn-submit-inspection');

    if (scheduledDate && inspectionType && inspectionStatus) {
        btnSubmit.disabled = false;
    } else {
        btnSubmit.disabled = true;
    }
}

// Agregar listeners para la validación en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const fieldsStep2 = ['scheduled_date', 'inspection_type', 'inspection_status'];
    fieldsStep2.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', validateStep2);
            element.addEventListener('change', validateStep2);
        }
    });

    // Validación inicial
    validateStep2();
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>