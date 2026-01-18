<?php
require_once __DIR__ . '/../../controllers/DailyCashController.php';

$controller = new DailyCashController();
$data = $controller->index();
$reportData = $data['reportData'];
$filters = $data['filters'];
$summary = $data['summary'];
$page_title = $data['page_title'];

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Inicio</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Reporte de Cierre de Caja</li>
                    </ol>
                </nav>

                <?php if (isset($_SESSION['flash_message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?> alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['flash_message']['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <?php unset($_SESSION['flash_message']); ?>
                    </div>
                <?php endif; ?>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card bg-danger text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white opacity-75">INICIO DEL DÍA (Todas las Cajas)</h6>
                                        <h2 class="text-white mb-0">Bs. <?php echo number_format($summary['total_initial'], 2, ',', '.'); ?></h2>
                                    </div>
                                    <div class="p-3 bg-white bg-opacity-25 rounded-circle">
                                        <i class="ri-safe-2-line fs-3"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white opacity-75">TOTAL RECAUDADO (Cierre)</h6>
                                        <h2 class="text-white mb-0">Bs. <?php echo number_format($summary['total_final'], 2, ',', '.'); ?></h2>
                                    </div>
                                    <div class="p-3 bg-white bg-opacity-25 rounded-circle">
                                        <i class="ri-money-dollar-circle-line fs-3"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Desde</label>
                                <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($filters['date_from']); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Hasta</label>
                                <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($filters['date_to']); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estatus Caja</label>
                                <select name="status" class="form-select">
                                    <option value="">Todas</option>
                                    <option value="active" <?php echo $filters['status'] === 'active' ? 'selected' : ''; ?>>Activas</option>
                                    <option value="inactive" <?php echo $filters['status'] === 'inactive' ? 'selected' : ''; ?>>Inactivas</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ri-filter-3-line me-1"></i> Filtrar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Report Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 card-title-premium d-flex align-items-center">
                            <i class="ri-file-list-3-line icon-premium"></i>
                            Historial de Cierres Diarios
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover w-100" id="dailyReportTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Caja</th>
                                        <th>Usuario</th>
                                        <th>Estatus Caja</th>
                                        <th>Monto Inicial</th>
                                        <th>Total Recaudado</th>
                                        <th>Balance Final</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reportData as $row): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($row['open_date'])); ?></td>
                                            <td><strong><?php echo htmlspecialchars($row['register_name']); ?></strong></td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span><?php echo htmlspecialchars($row['username']); ?></span>
                                                    <small class="text-muted"><?php echo htmlspecialchars($row['staff_name']); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($row['register_status'] === 'active'): ?>
                                                    <span class="badge bg-success">Activa</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inactiva</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>Bs. <?php echo number_format($row['initial_amount'], 2, ',', '.'); ?></td>
                                            <td class="text-success fw-bold">Bs. <?php echo number_format($row['total_collected'], 2, ',', '.'); ?></td>
                                            <td class="fw-bold">Bs. <?php echo number_format($row['initial_amount'] + $row['total_collected'], 2, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<!-- DataTables Scripts -->
<script type="text/javascript" src="../../public/datatables/datatables.min.js"></script>
<script type="text/javascript" src="../../public/datatables/pdfmake.min.js"></script>
<script type="text/javascript" src="../../public/datatables/vfs_fonts.js"></script>
<link rel="stylesheet" type="text/css" href="../../public/datatables/datatables.min.css"/> 
<link rel="stylesheet" type="text/css" href="../../public/datatables/buttons.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="../../public/assets/css/dani-styles.css"/>

<script>
$(document).ready(function() {
    $('#dailyReportTable').DataTable({
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="ri-file-excel-line"></i> Excel',
                className: 'btn btn-success btn-sm me-1',
                exportOptions: { columns: ':visible' }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="ri-file-pdf-line"></i> PDF',
                className: 'btn btn-danger btn-sm me-1',
                orientation: 'landscape',
                pageSize: 'LETTER',
                exportOptions: { columns: ':visible' }
            },
            {
                extend: 'print',
                text: '<i class="ri-printer-line"></i> Imprimir',
                className: 'btn btn-info btn-sm me-1',
                exportOptions: { columns: ':visible' }
            }
        ],
        order: [[0, 'desc']],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
        }
    });
});
</script>
