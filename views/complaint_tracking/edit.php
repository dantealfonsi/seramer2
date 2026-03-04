<?php
session_start();
require_once __DIR__ . '/../../controllers/ComplaintTrackingController.php';
require_once __DIR__ . '/../../models/UserModel.php';

$trackingController = new ComplaintTrackingController();
$usersModel = new UserModel();
$errors = [];
$record = null;
$page_title = 'Editar Registro de Seguimiento';

// Obtener el ID del registro de la URL
$tracking_id = $_GET['id'] ?? null;

if (!$tracking_id) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'ID de registro de seguimiento no proporcionado.'];
    header("Location: ../complaints/index.php");
    exit;
}

// Cargar los datos del registro si existe un ID válido
$result = $trackingController->edit($tracking_id);
if ($result['success']) {
    $record = $result['record'];
} else {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => $result['message']];
    header("Location: view.php?id=" . ($record['complaint_id'] ?? ''));
    exit;
}

// RBAC: Solo RRHH puede editar seguimiento
if (!isset($_SESSION['selected_department']) || $_SESSION['selected_department'] !== 'Recursos Humanos') {
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'No tiene permisos para acceder a esta sección.'
    ];
    header("Location: ../complaints/view.php?id=" . $record['complaint_id']);
    exit;
}

// Opciones predefinidas para el formulario
$action_types = [
    'Assignment' => 'Asignación',
    'Follow-up' => 'Seguimiento',
    'Resolution' => 'Resolución',
    'Observation' => 'Observación'
];

// El ID del usuario sigue siendo el del registro original o el de la sesión
$admin_user_id = $_SESSION['user_id'] ?? $record['admin_user_id'];

// Manejar la solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'admin_user_id' => $record['admin_user_id'], // Mantener el autor original o forzar el actual si se prefiere
        'action_type' => $_POST['action_type'] ?? '',
        'action_description' => $_POST['action_description'] ?? '',
        'action_result' => $_POST['action_result'] ?? ''
    ];

    $result = $trackingController->update($tracking_id, $data);

    if (isset($result['redirect'])) {
        header("Location: " . $result['redirect']);
        exit;
    }

    if (!$result['success']) {
        $errors = $result['errors'] ?? [$result['message']];
    } else {
        // Recargar el registro con los nuevos datos después de la actualización exitosa
        $result = $trackingController->edit($tracking_id);
        $record = $result['record'];
    }
}

// Obtener la lista de usuarios para el menú desplegable
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
                                <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="ri-edit-box-line" style="color: #696cff; font-size: 1.5rem;"></i></div>
                                <?php echo htmlspecialchars($page_title); ?>
                            </h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="../complaints/index.php">Quejas</a></li>
                                    <li class="breadcrumb-item"><a href="../complaints/view.php?id=<?php echo htmlspecialchars($record['complaint_id']); ?>">Detalles de Queja</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Editar Seguimiento</li>
                                </ol>
                            </nav>
                        </div>
                        <a href="../complaints/view.php?id=<?php echo htmlspecialchars($record['complaint_id']); ?>" class="btn btn-outline-secondary">
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
                            <input type="hidden" name="complaint_id" value="<?php echo htmlspecialchars($record['complaint_id']); ?>">
                            
                            <div class="mb-3">
                                <label for="admin_user_display" class="form-label">Usuario Administrador</label>
                                <?php 
                                // Buscar el nombre del usuario autor del registro
                                $author_name = $_SESSION['user_full_name'] ?? $_SESSION['username'];
                                foreach ($admin_users as $user) {
                                    if ($user['id'] == $record['admin_user_id']) {
                                        $author_name = $user['username']; // O usar full_name si está disponible en $user
                                        break;
                                    }
                                }
                                ?>
                                <input type="text" class="form-control bg-light" id="admin_user_display" value="<?php echo htmlspecialchars($author_name); ?>" readonly>
                                <input type="hidden" name="admin_user_id" value="<?php echo htmlspecialchars($record['admin_user_id']); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="action_type" class="form-label">Tipo de Acción</label>
                                <select class="form-control" id="action_type" name="action_type" required>
                                    <option value="">Seleccione un tipo</option>
                                    <?php foreach ($action_types as $value => $label): ?>
                                        <option value="<?php echo htmlspecialchars($value); ?>" <?php echo ($record['action_type'] == $value) ? 'selected' : ''; ?>><?php echo htmlspecialchars($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="action_description" class="form-label">Descripción</label>
                                <textarea class="form-control" id="action_description" name="action_description" rows="5" required><?php echo htmlspecialchars($record['action_description']); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="action_result" class="form-label">Resultado</label>
                                <textarea class="form-control" id="action_result" name="action_result" rows="5"><?php echo htmlspecialchars($record['action_result'] ?? ''); ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line"></i> Actualizar Registro
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
