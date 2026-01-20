<?php
// views/collection-reports/activityHistory.php
require_once __DIR__ . '/../../models/UserRecordsModel.php'; 

$userRecordsModel = new UserRecordsModel();

$filters = [
    'user_id'    => $_GET['filter_user'] ?? null,
    'start_date' => $_GET['start_date'] ?? null,
    'end_date'   => $_GET['end_date'] ?? null,
];

// Ideally we filter by Collection users only if not specified, but for now we keep it broad or rely on user selection
$records = $userRecordsModel->getRecords(array_filter($filters));
?>

<div class="card-body">
    <?php if (empty($records)): ?>
        <div class="text-center py-4">
            <i class="ri-alert-line text-muted" style="font-size: 3rem;"></i>
            <h5 class="text-muted mt-2">No se encontraron registros de actividad</h5>
            <p class="text-muted">Ajuste los filtros de búsqueda e intente nuevamente.</p>
        </div>
    <?php else: ?>
        <p class="text-muted mb-3">Registros encontrados: <strong><?php echo count($records); ?></strong></p>
        <div class="table-responsive">
            <table id="activityHistoryTable" class="table table-striped table-hover w-100">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Fecha/Hora</th>
                        <th>Usuario</th>
                        <th>Departamento</th>
                        <th>Acción Realizada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['id']); ?></td>
                            <td data-order="<?php echo strtotime($record['created_at']); ?>">
                                <?php echo date('d/m/Y H:i:s', strtotime($record['created_at'])); ?>
                            </td>
                            <td><?php echo htmlspecialchars($record['username']); ?></td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?php echo htmlspecialchars($record['department_name'] ?? 'N/A'); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($record['action']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    const tableId = '#activityHistoryTable';
    if ($.fn.DataTable.isDataTable(tableId)) {
        $(tableId).DataTable().destroy();
    }
    
    if ($.fn.DataTable) {
        $(tableId).DataTable({ 
            responsive: true,
            dom: 'Bfrtip',
            buttons: [
                { extend: 'excelHtml5', className: 'btn btn-success btn-sm me-1', text: '<i class="ri-file-excel-line"></i> Excel' },
                { extend: 'print', className: 'btn btn-info btn-sm', text: '<i class="ri-printer-line"></i> Imprimir' }
            ],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
            },
            order: [[1, 'desc']]
        });
    }
});
</script>
