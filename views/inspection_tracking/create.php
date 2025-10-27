<?php
session_start();
require_once __DIR__ . '/../../controllers/InspectionTrackingController.php';
require_once __DIR__ . '/../../models/UserModel.php';

$trackingController = new InspectionTrackingController();
$usersModel = new UserModel();
$errors = [];
$inspection_id = $_GET['inspection_id'] ?? null; // **CORREGIDO**
$page_title = 'Añadir Registro de Seguimiento de Inspección';
if (!$inspection_id) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'ID de inspección no proporcionado.'];
    header("Location: ../inspections/index.php");
    exit;
}

// Opciones predefinidas para el formulario
$action_types = [
    'Schedule Update' => 'Actualización de Agenda',
    'Field Visit' => 'Visita de Campo',
    'Report Generation' => 'Generación de Reporte',
    'Completion' => 'Finalización'
];

// ID de usuario administrador de la sesión (Placeholder)
$admin_user_id = 1; 
$admin_users = $usersModel->getAll();

// Manejar la solicitud POST (Creación)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'inspection_id' => $inspection_id, // Usamos el ID de la URL
        'admin_user_id' => $_POST['admin_user_id'] ?? $admin_user_id,
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
    } else {
        // Redireccionar al historial de la inspección principal después de la creación exitosa
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => $result['message']];
        header("Location: view.php?id=" . $inspection_id);
        exit;
    }
}

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
                        <h5 class="card-title" style="font-size: 2rem;font-weight: 600;">
                            <i class="ri-add-box-line me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="view.php?id=<?php echo htmlspecialchars($inspection_id); ?>" class="btn btn-secondary">
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
                            <input type="hidden" name="inspection_id" value="<?php echo htmlspecialchars($inspection_id); ?>">
                            
                            <div class="mb-3">
                                <label for="admin_user_id" class="form-label">Usuario Administrador</label>
                                <select class="form-control" id="admin_user_id" name="admin_user_id" required>
                                    <option value="">Seleccione un usuario</option>
                                    <?php foreach ($admin_users as $user): ?>
                                        <option value="<?php echo htmlspecialchars($user['id']); ?>" <?php echo ($admin_user_id == $user['id']) ? 'selected' : ''; ?>>
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
                                        <option value="<?php echo htmlspecialchars($value); ?>" <?php echo (isset($_POST['action_type']) && $_POST['action_type'] === $value) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="action_description" class="form-label">Descripción de la Acción</label>
                                <textarea class="form-control" id="action_description" name="action_description" rows="5" required><?php echo htmlspecialchars($_POST['action_description'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="action_result" class="form-label">Resultado / Notas</label>
                                <textarea class="form-control" id="action_result" name="action_result" rows="5"><?php echo htmlspecialchars($_POST['action_result'] ?? ''); ?></textarea>
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