<?php
/**
 * Vista: Ranking de Mayores Pagadores - Reporte Individual de Cobranza
 */
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../models/CollectionReportModel.php';

include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';

$model = new CollectionReportModel();

$startDate = $_GET['start_date'] ?? null;
$endDate   = $_GET['end_date'] ?? null;

$topPayers = $model->getTopPayers(50, $startDate, $endDate);
?>

<style>
    .main-container { padding: 1.5rem; background-color: #f5f5f9; min-height: calc(100vh - 100px); }
</style>

<div class="main-content main-container">
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background-color: #ffe0eb !important;">
                            <i class="ri-medal-fill" style="color: #ff3e1d; font-size: 2rem;"></i>
                        </div>
                        <div>
                            <h2 class="mb-0 fw-bold" style="color: #43495b;">Ranking de Mayores Pagadores</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="index.php">Cobranza</a></li>
                                    <li class="breadcrumb-item active">Mayores Pagadores</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Volver al Menú
                    </a>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h6 class="mb-3"><i class="ri-filter-line me-1 text-primary"></i> Rango de Fechas (Opcional)</h6>
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Desde</label>
                        <input type="date" class="form-control" name="start_date" value="<?= htmlspecialchars($startDate ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Hasta</label>
                        <input type="date" class="form-control" name="end_date" value="<?= htmlspecialchars($endDate ?? '') ?>">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="ri-search-line me-1"></i> Actualizar Ranking
                        </button>
                        <a href="top_payers_page.php" class="btn btn-outline-secondary" title="Limpiar Filtros">
                            <i class="ri-refresh-line"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Resultados -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <?php if (empty($topPayers)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="ri-inbox-line" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">No hay datos de pagos para el período seleccionado</h5>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table id="topPayersTable" class="table table-striped table-hover align-middle w-100">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 50px;">Rank</th>
                                    <th>Adjudicatario / Razón Social</th>
                                    <th>Documento (CI/RIF)</th>
                                    <th class="text-end">Total Pagado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                foreach ($topPayers as $payer): 
                                ?>
                                    <tr>
                                        <td class="text-center fw-bold fs-5">
                                            <?php if($rank == 1): ?>
                                                <i class="ri-trophy-fill text-warning"></i>
                                            <?php elseif($rank == 2): ?>
                                                <i class="ri-medal-line text-secondary"></i>
                                            <?php elseif($rank == 3): ?>
                                                <i class="ri-medal-line" style="color: #cd7f32;"></i>
                                            <?php else: ?>
                                                <?= $rank ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-semibold text-primary"><?= htmlspecialchars($payer['awardee_name']) ?></td>
                                        <td><span class="badge bg-label-dark"><?= htmlspecialchars($payer['id_number']) ?></span></td>
                                        <td class="text-end fw-bold text-success fs-6">
                                            Bs. <?= number_format($payer['total_paid'], 2, ',', '.') ?>
                                        </td>
                                    </tr>
                                <?php 
                                $rank++;
                                endforeach; 
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script type="text/javascript" src="../../public/datatables/datatables.min.js"></script>
<link rel="stylesheet" type="text/css" href="../../public/datatables/datatables.min.css"/>
<script>
$(document).ready(function() {
    if ($.fn.DataTable && $('#topPayersTable').length) {
        $('#topPayersTable').DataTable({
            responsive: true,
            dom: '<"d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2"Bf>rtip',
            buttons: [
                { extend: 'excelHtml5', text: '<i class="ri-file-excel-line"></i> Excel', className: 'btn btn-success btn-sm me-1' },
                { extend: 'pdfHtml5', text: '<i class="ri-file-pdf-line"></i> PDF', className: 'btn btn-danger btn-sm me-1' },
                { extend: 'print', text: '<i class="ri-printer-line"></i> Imprimir', className: 'btn btn-info btn-sm' }
            ],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
            pageLength: 20
        });
    }
});
</script>
