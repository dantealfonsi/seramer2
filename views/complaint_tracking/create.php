<?php
session_start();
require_once __DIR__ . '/../../controllers/ComplaintTrackingController.php';
require_once __DIR__ . '/../../models/UserModel.php';

$trackingController = new ComplaintTrackingController();
$usersModel = new UserModel();
$errors = [];
$complaint_id = $_GET['complaint_id'] ?? null;
$page_title = 'Añadir Registro de Seguimiento';

if (!$complaint_id) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'ID de queja no proporcionado.'];
    header("Location: ../complaints/index.php");
    exit;
}

// RBAC: Solo RRHH y Fiscalización pueden añadir seguimiento
$allowed_depts = ['Recursos Humanos', 'Fiscalizacion'];
if (!isset($_SESSION['selected_department']) || !in_array($_SESSION['selected_department'], $allowed_depts)) {
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'No tiene permisos para acceder a esta sección.'
    ];
    header("Location: ../complaints/view.php?id=" . $complaint_id);
    exit;
}

// Verificar si ya existe una resolución para esta queja
$trackingResult = $trackingController->index($complaint_id);
$has_resolution = false;
if ($trackingResult['success']) {
    foreach ($trackingResult['tracking_records'] as $record) {
        if ($record['action_type'] === 'Resolution') {
            $has_resolution = true;
            break;
        }
    }
}

if ($has_resolution) {
    $_SESSION['flash_message'] = [
        'type' => 'warning',
        'message' => 'Esta queja ya tiene una resolución registrada y no admite más seguimientos.'
    ];
    header("Location: ../complaints/view.php?id=" . $complaint_id);
    exit;
}

// Opciones predefinidas para el formulario
$action_types = [
    'Assignment' => 'Asignación',
    'Follow-up' => 'Seguimiento',
    'Resolution' => 'Resolución',
    'Observation' => 'Observación'
];

// El ID del usuario administrador proviene de la sesión
$admin_user_id = $_SESSION['user_id'] ?? 1; 

// Manejar la solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'complaint_id' => $complaint_id,
        'admin_user_id' => $admin_user_id, // Forzar el ID del usuario actual para mayor seguridad
        'action_type' => $_POST['action_type'] ?? '',
        'action_description' => $_POST['action_description'] ?? '',
        'action_result' => $_POST['action_result'] ?? ''
    ];

    $result = $trackingController->store($data);

    if (isset($result['redirect'])) {
        header("Location: " . $result['redirect']);
        exit;
    }

    if (!$result['success']) {
        $errors = $result['errors'] ?? [$result['message']];
    }
}

// Obtener la lista de usuarios para el menú desplegable (asumiendo que los administradores tienen un rol)
$admin_users = $usersModel->getAll();

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
                                <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="ri-add-box-line" style="color: #696cff; font-size: 1.5rem;"></i></div>
                                <?php echo htmlspecialchars($page_title); ?>
                            </h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="../complaints/index.php">Quejas</a></li>
                                    <li class="breadcrumb-item"><a href="../complaints/view.php?id=<?php echo htmlspecialchars($complaint_id); ?>">Detalles de Queja</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Añadir Seguimiento</li>
                                </ol>
                            </nav>
                        </div>
                        <a href="../complaints/view.php?id=<?php echo htmlspecialchars($complaint_id); ?>" class="btn btn-outline-secondary">
                            <i class="ri-arrow-left-line"></i> Volver
                        </a>
                    </div>
                    
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger" role="alert">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <!-- El complaint_id se pasa como campo oculto -->
                            <input type="hidden" name="complaint_id" value="<?php echo htmlspecialchars($complaint_id); ?>">
                            
                            <div class="mb-3">
                                <label for="admin_user_display" class="form-label">Usuario Administrador</label>
                                <input type="text" class="form-control bg-light" id="admin_user_display" value="<?php echo htmlspecialchars($_SESSION['user_full_name'] ?? $_SESSION['username']); ?>" readonly>
                                <input type="hidden" name="admin_user_id" value="<?php echo htmlspecialchars($admin_user_id); ?>">
                                <small class="text-muted">El registro se guardará a su nombre automáticamente.</small>
                            </div>

                            <div class="mb-3">
                                <label for="action_type" class="form-label">Tipo de Acción</label>
                                <select class="form-control" id="action_type" name="action_type" required>
                                    <option value="">Seleccione un tipo</option>
                                    <?php foreach ($action_types as $value => $label): ?>
                                        <option value="<?php echo htmlspecialchars($value); ?>"><?php echo htmlspecialchars($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="action_description" class="form-label">Descripción</label>
                                <textarea class="form-control" id="action_description" name="action_description" rows="5" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="action_result" class="form-label">Resultado</label>
                                <textarea class="form-control" id="action_result" name="action_result" rows="5"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line"></i> Guardar Registro
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
