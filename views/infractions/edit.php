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

$infraction_types = $infractionsModel->getInfractionTypesList();

$stalls =  $infractionsModel->getStallsList();
$stallDict = [];
foreach ($stalls as $id => $stall_number) {
    $stallDict[] = ['id' => $id, 'stall_number' => $stall_number];     
}

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
        'infraction_datetime' => trim($_POST['infraction_datetime'] ?? '') . ' 00:00:00',
        'infraction_description' => trim($_POST['infraction_description'] ?? ''),
        'infraction_status' => trim($_POST['infraction_status'] ?? 'Reported'),
        'sanction_type_id' => filter_input(INPUT_POST, 'sanction_type_id', FILTER_SANITIZE_NUMBER_INT),
        'effect_end_date' => trim($_POST['effect_end_date'] ?? '00:00:00'),
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
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
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
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="font-size: 2rem;font-weight: 600;">
                            <i class="ri-alert-line me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="<?php echo $is_edit ? 'view.php?id=' . htmlspecialchars($idEdit) : 'index.php'; ?>" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i> 
                            <?php echo $is_edit ? 'Volver a detalles' : 'Volver al listado'; ?>
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

                        <form method="POST" enctype="multipart/form-data" novalidate>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="awardee_id" class="form-label">
                                            Adjudicatario <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="awardee_id" name="awardee_id" onchange="loadSanctions()" required>
                                            <option value="">Seleccione un adjudicatario</option>
                                            <?php foreach ($awardees as $adj): ?>
                                            <option value="<?php echo htmlspecialchars($adj['id']); ?>" 
                                                     <?php echo ($form_data['awardee_id'] == $adj['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($adj['first_name']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="stall_id" class="form-label">
                                            Puesto
                                        </label>
                                        <select class="form-select" id="stall_id" name="stall_id">
                                            <option value="">Seleccione un puesto (opcional)</option>
                                            <?php foreach ($stalls as $item): ?>
                                                <option value="<?php echo htmlspecialchars($item['id']); ?>"
                                                    <?php echo ((int)$form_data['stall_id'] == (int)$item['id']) ? ' selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($item['stall_number']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="infraction_type_id" class="form-label">
                                            Tipo de Infracción <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="infraction_type_id" name="infraction_type_id" required>
                                            <option value="">Seleccione un tipo de infracción</option>
                                            <?php foreach ($infraction_types as $type): ?>
                                            <option value="<?php echo htmlspecialchars($type['infraction_type_id']); ?>"
                                                     <?php echo ($form_data['infraction_type_id'] == $type['infraction_type_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($type['infraction_type_name']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="sanction_type_id" class="form-label">Sanción Aplicada</label>
                                        <select class="form-select" id="sanction_type_id" name="sanction_type_id" required>
                                            <option value="" selected disabled>Seleccionar Tipo de Sanción</option>
                                            <?php foreach ($sanction_types as $type) : ?>
                                                <option value="<?php echo htmlspecialchars($type['sanction_type_id']); ?>">
                                                    <?php echo htmlspecialchars($type['severity_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>  
                                    <div class="col-md-6 mb-3">
                                        <label for="effect_end_date" class="form-label">Fecha limite del Efecto de la Sancion</label>
                                        <input type="date" class="form-control" id="effect_end_date" name="effect_end_date">
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
                                               required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="infraction_description" class="form-label">
                                            Descripción <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control" 
                                                  id="infraction_description" 
                                                  name="infraction_description" 
                                                  rows="3" 
                                                  required><?php echo htmlspecialchars($form_data['infraction_description']); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="inspector_observations" class="form-label">
                                            Observaciones del Inspector
                                        </label>
                                        <textarea class="form-control" 
                                                  id="inspector_observations" 
                                                  name="inspector_observations" 
                                                  rows="3"><?php echo htmlspecialchars($form_data['inspector_observations']); ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="proof" class="form-label">
                                            Prueba (Imagen/Video)
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
function loadSanctions() {
    const awardeeId = document.getElementById('awardee_id').value;

    // Limpiar opciones actuales
    sanctionSelect.innerHTML = '<option value="" selected disabled>Seleccionar Tipo de Sanción</option>';

    if (awardeeId) {

    }
}
</script>
<?php include __DIR__ . '/../layouts/footer.php'; ?>