<?php
session_start();
require_once __DIR__ . '/../../controllers/SanctionTypesController.php';

$sanctionTypesController = new SanctionTypesController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $sanctionTypesController->store($_POST);

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
        $_SESSION['form_data'] = $_POST;
    }
}

$page_title = 'Registrar Nuevo Tipo de Sanción';
$form_data = $_SESSION['form_data'] ?? [];
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
                        <h5 class="card-title d-flex align-items-center" style="font-size: 1.4rem;font-weight: 600;">
                            <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="ri-add-line" style="color: #696cff; font-size: 1.5rem;"></i></div>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="create.php" method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="sanction_type_name" class="form-label">Nombre del Tipo de
                                        Sanción</label>
                                    <input onkeyup="validarText('sanction_type_name',3,'errorTextSanctionTypeName')"
                                        type="text" class="form-control" id="sanction_type_name"
                                        name="sanction_type_name"
                                        value="<?php echo htmlspecialchars($form_data['sanction_type_name'] ?? ''); ?>"
                                        required>
                                    <div id="errorTextSanctionTypeName" style="color: red;"></div>
                                </div>
                                <div class="col-md-6">
                                    <label for="description" class="form-label">Descripción</label>
                                    <input onkeyup="validarText('description',8,'errorTextDescription')" type="text"
                                        class="form-control" id="description" name="description"
                                        value="<?php echo htmlspecialchars($form_data['description'] ?? ''); ?>">
                                    <div id="errorTextDescription" style="color: red;"></div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Registrar Tipo de Sanción</button>
                                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
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