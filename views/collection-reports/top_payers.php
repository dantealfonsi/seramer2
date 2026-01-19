<?php
// views/collection-reports/top_payers.php
require_once __DIR__ . '/../../models/CollectionReportModel.php';

$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

$model = new CollectionReportModel();
$topPayers = $model->getTopPayers(50, $startDate, $endDate); // Get top 50
?>

<div class="card-body">
    <h5 class="card-title text-primary mb-4"><i class="ri-user-star-line me-1"></i> Ranking de Mayores Pagadores</h5>
    
    <?php if (empty($topPayers)): ?>
        <p class="text-muted">No hay datos disponibles para el período seleccionado.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table id="topPayersTable" class="table table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 50px;">#</th>
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
                            <td>
                                <?php if($rank == 1): ?>
                                    <i class="ri-trophy-line text-warning fs-5"></i>
                                <?php elseif($rank == 2): ?>
                                    <i class="ri-trophy-line text-secondary fs-5"></i>
                                <?php elseif($rank == 3): ?>
                                    <i class="ri-trophy-line text-danger fs-5"></i>
                                <?php else: ?>
                                    <?php echo $rank; ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($payer['awardee_name']); ?></td>
                            <td><?php echo htmlspecialchars($payer['id_number']); ?></td>
                            <td class="text-end fw-bold text-success">
                                <?php echo 'Bs. ' . number_format($payer['total_paid'], 2, ',', '.'); ?>
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

<script>
$(document).ready(function() {
    $('#topPayersTable').DataTable({
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excelHtml5', className: 'btn btn-success btn-sm', text: 'Exportar Excel' },
            { extend: 'print', className: 'btn btn-secondary btn-sm', text: 'Imprimir' }
        ],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
        },
        pageLength: 20
    });
});
</script>
