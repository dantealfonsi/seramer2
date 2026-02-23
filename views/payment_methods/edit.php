<?php
require_once __DIR__ . '/../../controllers/PaymentMethodController.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$controller = new PaymentMethodController();
$data = $controller->edit($id);

if (!$data) {
    header('Location: index.php');
    exit;
}

$method = $data['method'];
$page_title = $data['page_title'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->update($id, $_POST);
    if ($result['success']) {
        header('Location: index.php');
        exit;
    } else {
        $error = $result['message'];
    }
}

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo htmlspecialchars($page_title); ?></h5>
                    </div>
                    <div class="card-body">
                         <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Nombre del Método de Pago</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($method['name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Estado</label>
                                <select name="is_active" class="form-select">
                                    <option value="1" <?php echo $method['is_active'] ? 'selected' : ''; ?>>Activo</option>
                                    <option value="0" <?php echo !$method['is_active'] ? 'selected' : ''; ?>>Inactivo</option>
                                </select>
                            </div>
                            <div class="text-end">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="ri-close-line me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-refresh-line me-1"></i> Actualizar
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
