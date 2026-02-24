<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../controllers/LiquidacionReportController.php';

$controller = new LiquidacionReportController();
$data = $controller->delinquentcontracts();
extract($data);

include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<style>
    .main-container { padding: 1.5rem; background-color: #f5f5f9; width: 100%; }
    .table thead th { background-color: #000000 !important; color: white !important; text-transform: uppercase; font-weight: 600; padding: 1rem !important; }
</style>

<div class="main-content main-container">
    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="d-flex align-items-center">
                        <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #ffe0e0 !important;">
                            <i class="ri-alert-line" style="color: #ff3e1d; font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold" style="font-size: 1.75rem; color: #43495b;">Contratos Morosos</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="index.php">Reportes</a></li>
                                    <li class="breadcrumb-item active">Contratos Morosos</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <a href="index.php" class="btn btn-outline-secondary"><i class="ri-arrow-left-line me-1"></i> Volver</a>
                </div>

                <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                    <i class="ri-information-line me-2"></i>
                    <div><strong>Total de contratos en mora:</strong> <?= count($contracts) ?></div>
                </div>

                <div class="table-responsive">
                    <table id="reportTable" class="table table-hover align-middle w-100">
                        <thead>
                            <tr>
                                <th>Contrato</th>
                                <th>Adjudicatario</th>
                                <th>Cédula</th>
                                <th>Facturas</th>
                                <th>Monto Deuda</th>
                                <th>Pagado</th>
                                <th>Saldo</th>
                                <th>Mora</th>
                                <th>Desde</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contracts as $contract): ?>
                            <tr>
                                <td><span class="badge bg-label-primary">#<?= $contract['contract_id'] ?></span></td>
                                <td class="fw-bold"><?= htmlspecialchars($contract['awardee_name']) ?></td>
                                <td><?= htmlspecialchars($contract['awardee_id_number']) ?></td>
                                <td><span class="badge bg-label-danger"><?= $contract['overdue_payments_count'] ?></span></td>
                                <td class="text-danger fw-bold">Bs. <?= number_format($contract['total_amount_due'] ?? 0, 2) ?></td>
                                <td>Bs. <?= number_format($contract['total_paid'] ?? 0, 2) ?></td>
                                <td class="fw-bold">Bs. <?= number_format(($contract['total_amount_due'] ?? 0) - ($contract['total_paid'] ?? 0), 2) ?></td>
                                <td><span class="badge bg-label-warning"><?= $contract['days_overdue'] ?> días</span></td>
                                <td><?= date('d/m/Y', strtotime($contract['first_overdue_date'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<!-- DataTables setup -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    $('#reportTable').DataTable({
        responsive: true,
        dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rtip',
        buttons: [
            { extend: 'excelHtml5', text: '<i class="ri-file-excel-line me-1"></i> Excel', className: 'btn btn-success btn-sm me-1', title: 'Contratos Morosos' },
            { extend: 'pdfHtml5', text: '<i class="ri-file-pdf-line me-1"></i> PDF', className: 'btn btn-danger btn-sm me-1', orientation: 'landscape', title: 'Contratos Morosos' }
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
        order: [[7, 'desc']]
    });
});
</script>
