<?php
// Vista de edición/creación de infracciones

session_start();

// Incluir el controlador y los modelos para cargar los datos de las listas
require_once __DIR__ . '/../../controllers/InfractionsController.php';
require_once __DIR__ . '/../../models/AdjudicatoriesModel.php';
require_once __DIR__ . '/../../models/MarketStallsModel.php';
require_once __DIR__ . '/../../models/InfractionTypesModel.php';

$infractionsController = new InfractionsController();

$id = $_GET['id'] ?? null;
$is_edit = !empty($id);
$infraction = null;
$page_title = 'Registrar Nueva Infracción';
$errors = [];
$form_data = [
    'id_adjudicatory' => '',
    'id_stall' => '',
    'id_infraction_type' => '',
    'infraction_datetime' => date('Y-m-d H:i:s'),
    'infraction_description' => '', 
    'infraction_status' => 'Reported', 
    'inspector_observations' => '', // Campo nuevo: Observaciones del inspector
    'proof' => '', 
];

// --- Cargar las listas de selección para los campos del formulario ---
$adjudicatoriesModel = new AdjudicatoriesModel();
$marketStallsModel = new MarketStallsModel();
$infractionTypesModel = new InfractionTypesModel();

$adjudicators = $adjudicatoriesModel->getAll();
$stalls = $marketStallsModel->getAll();
$infraction_types = $infractionTypesModel->getAll();

// Si estamos editando, obtener los datos de la infracción
if ($is_edit) {
    $result = $infractionsController->edit($id);
    
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
    
    // Asignar los valores a form_data usando los nombres de columna correctos
    $form_data['id_adjudicatory'] = $infraction['id_adjudicatory'];
    $form_data['id_stall'] = $infraction['id_stall'];
    $form_data['id_infraction_type'] = $infraction['id_infraction_type'];
    $form_data['infraction_datetime'] = date('Y-m-d', strtotime($infraction['infraction_datetime'])); // Formato Y-m-d para input date
    $form_data['infraction_status'] = $infraction['infraction_status'];
    $form_data['infraction_description'] = $infraction['infraction_description']; 
    $form_data['inspector_observations'] = $infraction['inspector_observations']; // Cargar observaciones
    $form_data['proof'] = $infraction['proof'] ?? '';
}

// Procesar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = [
        'id_adjudicatory' => trim($_POST['id_adjudicatory'] ?? ''),
        'id_stall' => trim($_POST['id_stall'] ?? ''),
        'id_infraction_type' => trim($_POST['id_infraction_type'] ?? ''),
        'infraction_datetime' => trim($_POST['infraction_datetime'] ?? ''),
        'infraction_description' => trim($_POST['infraction_description'] ?? ''), 
        'infraction_status' => trim($_POST['infraction_status'] ?? ''),
        'inspector_observations' => trim($_POST['inspector_observations'] ?? ''), // Se recibe el campo de observaciones
    ];

    // Usar el ID de la infracción en la actualización
    if ($is_edit) {
        $result = $infractionsController->update($id, $form_data);
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
                            <a href="view.php?id=<?php echo htmlspecialchars($id); ?>">Infracción #<?php echo htmlspecialchars($id); ?></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Editar</li>
                        <?php else: ?>
                        <li class="breadcrumb-item active" aria-current="page">Registrar Nuevo</li>
                        <?php endif; ?>
                    </ol>
                </nav>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="ri-alert-line me-1"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="<?php echo $is_edit ? 'view.php?id=' . htmlspecialchars($id) : 'index.php'; ?>" class="btn btn-secondary">
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
                                        <label for="adjudicatory_id" class="form-label">
                                            Adjudicatario <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="adjudicatory_id" name="id_adjudicatory" required>
                                            <option value="">Seleccione un adjudicatario</option>
                                            <?php foreach ($adjudicators as $adj): ?>
                                            <option value="<?php echo htmlspecialchars($adj['id_adjudicatory']); ?>" 
                                                     <?php echo ($form_data['id_adjudicatory'] == $adj['id_adjudicatory']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($adj['full_name_or_company_name']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="stall_id" class="form-label">
                                            Puesto
                                        </label>
                                        <select class="form-select" id="stall_id" name="id_stall">
                                            <option value="">Seleccione un puesto (opcional)</option>
                                            <?php foreach ($stalls as $stall): ?>
                                            <option value="<?php echo htmlspecialchars($stall['id_stall']); ?>"
                                                     <?php echo ($form_data['id_stall'] == $stall['id_stall']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($stall['stall_code']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="infraction_type_id" class="form-label">
                                            Tipo de Infracción <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="infraction_type_id" name="id_infraction_type" required>
                                            <option value="">Seleccione un tipo de infracción</option>
                                            <?php foreach ($infraction_types as $type): ?>
                                            <option value="<?php echo htmlspecialchars($type['id_infraction_type']); ?>"
                                                     <?php echo ($form_data['id_infraction_type'] == $type['id_infraction_type']) ? 'selected' : ''; ?>>
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
                                                Archivo actual: <a href="/uploads/infractions/<?php echo htmlspecialchars($form_data['proof']); ?>" target="_blank"><?php echo htmlspecialchars($form_data['proof']); ?></a>.
                                                <br>
                                                Puedes subir uno nuevo para reemplazarlo.
                                            </div>
                                        <?php endif; ?>
                                        <input type="file"
                                               class="form-control"
                                               id="proof"
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
                                <a href="<?php echo $is_edit ? 'view.php?id=' . htmlspecialchars($id) : 'index.php'; ?>" 
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

<?php include __DIR__ . '/../layouts/footer.php'; ?>