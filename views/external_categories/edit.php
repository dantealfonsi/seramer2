<?php
require_once __DIR__ . '/../../controllers/ExternalCategoryController.php';

$controller = new ExternalCategoryController();
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}
$id = (int)$_GET['id'];
$data_view = $controller->edit($id);
if (!$data_view) {
    header('Location: index.php');
    exit;
}

$category = $data_view['category'];
$page_title = $data_view['page_title'];

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
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom py-3">
                        <h4 class="card-title mb-1 d-flex align-items-center">
                            <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;">
                                <i class="ri-external-link-line" style="color: #696cff; font-size: 1.5rem;"></i>
                            </div>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="index.php">Rubros Externos</a></li>
                                <li class="breadcrumb-item active">Editar</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="card-body">
                         <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Nombre del Rubro</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                            </div>
                             <div class="mb-3">
                                <label class="form-label">Tipo de Instalación</label>
                                <select name="installation_type" class="form-control">
                                    <option value="kiosk" <?php echo ($category['installation_type'] == 'kiosk') ? 'selected' : ''; ?>>Quiosco</option>
                                    <option value="store" <?php echo ($category['installation_type'] == 'store') ? 'selected' : ''; ?>>Local</option>
                                    <option value="stand" <?php echo ($category['installation_type'] == 'stand') ? 'selected' : ''; ?>>Puesto</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Cantidad de Pagos (Anual)</label>
                                <input type="number" name="payment_count" class="form-control" value="<?php echo htmlspecialchars($category['payment_count']); ?>" required>
                            </div>
                            <div class="text-end">
                                <a href="index.php" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Actualizar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
