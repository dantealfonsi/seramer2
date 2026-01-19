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
                <?php if (isset($_SESSION['flash_message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?> alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['flash_message']['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <?php unset($_SESSION['flash_message']); ?>
                    </div>
                <?php endif; ?>


                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white h-100 shadow-sm border-0" style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white opacity-75 small text-uppercase fw-bold">Primeros Pagos (Total)</h6>
                                        <h3 class="text-white mb-0">Bs. <?php echo number_format($summary['total_initial'], 2, ',', '.'); ?></h3>
                                    </div>
                                    <i class="ri-skip-back-line fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white h-100 shadow-sm border-0" style="background: linear-gradient(135deg, #00b09b 0%, #96c93d 100%);">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white opacity-75 small text-uppercase fw-bold">Total Recaudado</h6>
                                        <h3 class="text-white mb-0">Bs. <?php echo number_format($summary['total_final'], 2, ',', '.'); ?></h3>
                                    </div>
                                    <i class="ri-money-dollar-circle-line fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-white h-100 shadow-sm border-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white opacity-75 small text-uppercase fw-bold">Pago más Alto</h6>
                                        <h3 class="text-white mb-0">Bs. <?php echo number_format($summary['total_max'], 2, ',', '.'); ?></h3>
                                    </div>
                                    <i class="ri-funds-line fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small text-muted text-uppercase fw-bold">Desde</label>
                                <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($filters['date_from']); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted text-uppercase fw-bold">Hasta</label>
                                <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($filters['date_to']); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted text-uppercase fw-bold">Estatus Caja</label>
                                <select name="status" class="form-select">
                                    <option value="">Todas</option>
                                    <option value="active" <?php echo $filters['status'] === 'active' ? 'selected' : ''; ?>>Activas</option>
                                    <option value="inactive" <?php echo $filters['status'] === 'inactive' ? 'selected' : ''; ?>>Inactivas</option>
                                </select>
                            </div>
                                <div class="col-12 d-flex justify-content-end gap-2">
                                    <button type="submit" class="btn btn-primary" style="background-color: #837aff; border-color: #837aff;">
                                        <i class="ri-search-line me-1"></i> Filtrar 
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='index.php'">
                                        <i class="ri-refresh-line"></i> Limpiar
                                    </button>
                                </div>
                        </form>
                    </div>
                </div>

                <!-- Report Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                         <h5 class="card-title mb-0 d-flex align-items-center" style="font-weight: 600;">
                             <i class="ri-file-list-3-line mr-2" style="font-size: 1.5rem;background: #837aff;color: white;padding: .4rem;border-radius: .7rem;"></i>
                             Historial de Actividad por Caja
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover w-100" id="dailyReportTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Nombre de Caja</th>
                                        <th>Cant. Inicial</th>
                                        <th>Cant. Máxima</th>
                                        <th>Total Recaudado</th>
                                        <th>Estatus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reportData as $row): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($row['open_date'])); ?></td>
                                            <td><span class="fw-bold"><?php echo htmlspecialchars($row['register_name']); ?></span></td>
                                            <td>Bs. <?php echo number_format($row['initial_amount'], 2, ',', '.'); ?></td>
                                            <td>Bs. <?php echo number_format($row['max_amount'], 2, ',', '.'); ?></td>
                                            <td class="fw-bold text-success">Bs. <?php echo number_format($row['total_collected'], 2, ',', '.'); ?></td>
                                            <td>
                                                <?php if ($row['total_collected'] > 0): ?>
                                                    <span class="badge rounded-pill bg-success">Con Actividad</span>
                                                <?php else: ?>
                                                    <span class="badge rounded-pill bg-light text-dark border">Sin Actividad</span>
                                                <?php endif; ?>
                                            </td>
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
