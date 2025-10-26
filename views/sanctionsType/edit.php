<?php
session_start();
require_once __DIR__ . '/../../controllers/SanctionTypesController.php';

$sanctionTypesController = new SanctionTypesController();
$errors = [];
$form_data = [];

// Maneja la petición POST para actualizar los datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['sanction_type_id'] ?? null;
    $result = $sanctionTypesController->update($_POST);

    if ($result['success']) {
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => $result['message']
        ];
        header("Location: index.php");
        exit;
    } else {
        $errors = $result['errors'] ?? ['Error desconocido.'];
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => implode('<br>', $errors)
        ];
        $form_data = $_POST;
    }
}

// Maneja la petición GET para mostrar el formulario de edición
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'ID de tipo de sanción no válido.'
    ];
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];
$result = $sanctionTypesController->edit($id);
extract($result);

if (!$success) {
    // Si no se encuentra el tipo de sanción, redirige con un mensaje de error
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'Tipo de sanción no encontrado.'
    ];
    header("Location: index.php");
    exit;
}

// Si hay datos de formulario en la sesión (por un error de validación), los usa
$form_data = $_SESSION['form_data'] ?? $sanction_type;
unset($_SESSION['form_data']);

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';

?>

<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?> mt-2" role="alert">
        <?php echo $_SESSION['flash_message']['message']; ?>
    </div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title" style="font-size: 2rem;font-weight: 600;">
                            <i class="ri-edit-line me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="edit.php?id=<?php echo htmlspecialchars($sanction_type['sanction_type_id']); ?>" method="POST">
                            <input type="hidden" name="sanction_type_id" value="<?php echo htmlspecialchars($sanction_type['sanction_type_id']); ?>">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="sanction_type_name" class="form-label">Nombre del Tipo de Sanción</label>
                                    <input type="text" class="form-control" id="sanction_type_name" name="sanction_type_name" value="<?php echo htmlspecialchars($form_data['severity_name'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="description" class="form-label">Descripción</label>
                                    <input type="text" class="form-control" id="description" name="description" value="<?php echo htmlspecialchars($form_data['description'] ?? ''); ?>">
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="ri-save-line"></i> Guardar Cambios
                                    </button>
                                    <a href="view.php?id=<?php echo htmlspecialchars($sanction_type['sanction_type_id']); ?>" class="btn btn-secondary">
                                        <i class="ri-close-line"></i> Cancelar
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>