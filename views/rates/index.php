<?php
require_once __DIR__ . '/../../controllers/EuroRateController.php';

$controller = new EuroRateController();
$data = $controller->index();
$rates = $data['rates'];
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
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($page_title); ?></h5>
                        <a href="create.php" class="btn btn-primary btn-sm">
                            <i class="ri-add-line"></i> Registrar Tasa
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($rates)): ?>
                            <div class="alert alert-info">No hay tasas registradas.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Mes</th>
                                            <th>Año</th>
                                            <th>Valor (Bs)</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $months = [
                                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                                        ];
                                        foreach ($rates as $rate): ?>
                                            <tr>
                                                <td><?php echo $months[$rate['month']] ?? $rate['month']; ?></td>
                                                <td><?php echo $rate['year']; ?></td>
                                                <td><?php echo number_format($rate['bs_value'], 2); ?></td>
                                                <td>
                                                    <a href="edit.php?id=<?php echo $rate['id']; ?>" class="btn btn-sm btn-info" title="Editar">
                                                        <i class="ri-pencil-line"></i>
                                                    </a>
                                                    <!-- Delete logic usually via AJAX/Modal -->
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
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
