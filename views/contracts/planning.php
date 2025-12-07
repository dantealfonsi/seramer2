<?php
require_once __DIR__ . '/../../controllers/PlanningController.php';

$controller = new PlanningController();
$data = $controller->index();
$page_title = $data['page_title'];
$zones = $data['zones'];

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo htmlspecialchars($page_title); ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Zona</label>
                                <select name="zone_id" class="form-control">
                                    <option value="">Todas</option>
                                    <?php foreach ($zones as $zone): ?>
                                        <option value="<?php echo $zone['id']; ?>"><?php echo htmlspecialchars($zone['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Año Fiscal</label>
                                <input type="number" name="year" class="form-control" value="<?php echo date('Y'); ?>">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Consultar</button>
                            </div>
                        </form>
                        
                        <div class="alert alert-info">
                            Funcionalidad de Reporte de Planificación en desarrollo. Seleccione filtros para ver resultados.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
