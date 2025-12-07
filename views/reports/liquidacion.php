<?php
require_once __DIR__ . '/../../controllers/ReportController.php';

$controller = new ReportController();
$params = $_GET; // Pass GET parameters directly
$data = $controller->liquidacionReport($params);

$zones = $data['zones'];
$startDate = $data['startDate'];
$endDate = $data['endDate'];
$page_title = $data['page_title'];

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
                        <!-- Filter Form -->
                        <form method="GET" action="" class="mb-4 d-flex gap-3 align-items-end">
                            <div class="form-group mb-0">
                                <label class="form-label">Desde</label>
                                <input type="date" name="start_date" class="form-control" value="<?php echo $startDate; ?>">
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label">Hasta</label>
                                <input type="date" name="end_date" class="form-control" value="<?php echo $endDate; ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                        </form>
                    
                         <?php if (empty($zones)): ?>
                            <div class="alert alert-info">No hay recaudación registrada en el periodo seleccionado.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Zona</th>
                                            <th>Contratos con Movimiento</th>
                                            <th>Transacciones</th>
                                            <th>Total Recaudado (Bs)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            $totalGlobal = 0;
                                            foreach ($zones as $zone): 
                                                $totalGlobal += $zone['total_accumulated'];
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($zone['zone_name']); ?></td>
                                                <td><?php echo $zone['contracts_count']; ?></td>
                                                <td><?php echo $zone['payments_count']; ?></td>
                                                <td class="text-right"><?php echo number_format($zone['total_accumulated'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="font-weight-bold bg-light">
                                            <td colspan="3" class="text-right">TOTAL GLOBAL</td>
                                            <td class="text-right"><?php echo number_format($totalGlobal, 2); ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
