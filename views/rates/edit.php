<?php
require_once __DIR__ . '/../../controllers/EuroRateController.php';

$controller = new EuroRateController();

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

$rate = $data_view['rate'];
$page_title = $data_view['page_title'];

// Handle POST
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
                    <div class="card-header border-bottom">
                        <h4 class="card-title mb-1 d-flex align-items-center">
                            <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;">
                                <i class="ri-money-euro-box-line" style="color: #696cff; font-size: 1.5rem;"></i>
                            </div>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="index.php">Tasa del Euro</a></li>
                                <li class="breadcrumb-item active">Editar</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="card-body">
                         <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mes</label>
                                    <select name="month" class="form-control" disabled>
                                        <?php 
                                        $months = [
                                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                                        ];
                                        foreach ($months as $num => $name): ?>
                                            <option value="<?php echo $num; ?>" <?php echo ($rate['month'] == $num) ? 'selected' : ''; ?>>
                                                <?php echo $name; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Mes y Año no se pueden editar.</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Año</label>
                                    <input type="number" name="year" class="form-control" value="<?php echo $rate['year']; ?>" disabled>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Valor (Bs)</label>
                                    <input type="number" step="0.000001" name="bs_value" class="form-control" value="<?php echo $rate['bs_value']; ?>" min="0.000001" required>
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
