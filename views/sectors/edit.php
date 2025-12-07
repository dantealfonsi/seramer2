<?php
require_once __DIR__ . '/../../controllers/SectorController.php';

$controller = new SectorController();
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

$sector = $data_view['sector'];
$page_title = $data_view['page_title'];
$zones = $data_view['zones'];

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
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Zona</label>
                                    <select name="zone_id" class="form-control" required>
                                        <option value="">Seleccione Zona</option>
                                        <?php foreach ($zones as $zone): ?>
                                            <option value="<?php echo $zone['id']; ?>" <?php echo ($sector['zone_id'] == $zone['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($zone['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nombre del Sector</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($sector['name']); ?>" required>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Descripción</label>
                                    <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($sector['description']); ?></textarea>
                                </div>
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
