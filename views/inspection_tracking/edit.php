<?php
session_start();
require_once __DIR__ . '/../../controllers/InspectionTrackingController.php';
require_once __DIR__ . '/../../models/UserModel.php'; // Asumo que usas UserModel para los administradores

$trackingController = new InspectionTrackingController();
$usersModel = new UserModel();
$errors = [];
$record = null;
$page_title = 'Editar Registro de Seguimiento de Inspección';

// Obtener el ID del registro de la URL
$tracking_id = $_GET['id'] ?? null;

if (!$tracking_id) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'ID de registro de seguimiento no proporcionado.'];
    header("Location: ../inspections/index.php");
    exit;
}

// Cargar los datos del registro si existe un ID válido
$result = $trackingController->edit($tracking_id);
if ($result['success']) {
    $record = $result['record'];
} else {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => $result['message']];
    // Redireccionamos a la vista de detalles de la inspección a la que pertenece
    header("Location: view.php?id=" . $record['inspection_id']); 
    exit;
}

// Opciones predefinidas para el formulario
$action_types = [
    'Schedule Update' => 'Actualización de Agenda',
    'Field Visit' => 'Visita de Campo',
    'Report Generation' => 'Generación de Reporte',
    'Completion' => 'Finalización'
];

// Manejar la solicitud POST (Actualización)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Usa los valores del POST, si no están presentes, usa los valores existentes
    $data = [
        'admin_user_id' => $_POST['admin_user_id'] ?? $record['admin_user_id'],
        'action_type' => $_POST['action_type'] ?? $record['action_type'],
        'action_description' => $_POST['action_description'] ?? $record['action_description'],
        'action_result' => $_POST['action_result'] ?? $record['action_result']
    ];

    $result = $trackingController->update($tracking_id, $data);

    if (isset($result['redirect'])) {
        header("Location: " . $result['redirect']);
        exit;
    }

    if (!$result['success']) {
        $errors = $result['errors'] ?? [$result['message']];
        // Si falla, los datos del formulario (POST) deben sobrescribir $record para mostrar lo que el usuario intentó guardar
        $record = array_merge($record, $data); 
    } else {
        // Redireccionar al historial de la inspección principal después de la actualización exitosa
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => $result['message']];
        header("Location: view.php?id=" . $record['inspection_id']);
        exit;
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
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title d-flex align-items-center" style="font-size: 1.4rem;font-weight: 600;">
                            <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="ri-edit-box-line" style="color: #696cff; font-size: 1.5rem;"></i></div>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="view.php?id=<?php echo htmlspecialchars($record['inspection_id']); ?>" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i> Volver al Historial
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
                            <input type="hidden" name="inspection_id" value="<?php echo htmlspecialchars($record['inspection_id']); ?>">
                            
                            <div class="mb-3">
                                <label for="admin_user_id" class="form-label">Usuario Administrador</label>
                                <select class="form-control" id="admin_user_id" name="admin_user_id" required>
                                    <option value="">Seleccione un usuario</option>
                                    <?php foreach ($admin_users as $user): ?>
                                        <option value="<?php echo htmlspecialchars($user['id']); ?>" <?php echo ($record['admin_user_id'] == $user['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['username']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
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
                                <label for="action_description" class="form-label">Descripción de la Acción</label>
                                <textarea class="form-control" id="action_description" name="action_description" rows="5" required><?php echo htmlspecialchars($record['action_description']); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="action_result" class="form-label">Resultado / Notas</label>
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