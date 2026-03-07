<?php
/**
 * Vista: Historial de Actividad - Reporte Individual de Cobranza
 */
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../models/UserRecordsModel.php';
require_once __DIR__ . '/../../models/UserModel.php';

include __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';

$userRecordsModel = new UserRecordsModel();
$userModel = new UserModel();
$usersList = $userModel->getAllForSelect();

$filters = [
    'user_id'    => $_GET['filter_user'] ?? null,
    'start_date' => $_GET['start_date'] ?? null,
    'end_date'   => $_GET['end_date'] ?? null,
];

$records = [];
$filtered = false;
if (!empty(array_filter($filters))) {
    $records = $userRecordsModel->getRecords(array_filter($filters));
    $filtered = true;
}
?>

<style>
    .main-container { padding: 1.5rem; background-color: #f5f5f9; min-height: calc(100vh - 100px); }
    .report-card { border-radius: 12px; }
    .filter-section { background: #fff; border-radius: 12px; padding: 1.5rem; border: 1px solid #e0e0e0; margin-bottom: 1.5rem; }
</style>

<div class="main-content main-container">
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background-color: #e7e7ff !important;">
                            <i class="ri-time-line" style="color: #696cff; font-size: 2rem;"></i>
                        </div>
                        <div>
                            <h2 class="mb-0 fw-bold" style="color: #43495b;">Historial de Actividad</h2>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="index.php">Cobranza</a></li>
                                    <li class="breadcrumb-item active">Historial de Actividad</li>
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
        <div class="filter-section shadow-sm">
            <h6 class="mb-3"><i class="ri-filter-line me-1 text-primary"></i> Filtros de Búsqueda</h6>
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Usuario</label>
                    <select class="form-select" name="filter_user">
                        <option value="">-- Todos los Usuarios --</option>
                        <?php foreach ($usersList as $u): ?>
                            <option value="<?= htmlspecialchars($u['id']) ?>" <?= ($_GET['filter_user'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['username']) ?> (<?= htmlspecialchars($u['staff_first_name'] . ' ' . $u['staff_last_name']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Desde</label>
                    <input type="date" class="form-control" name="start_date" value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Hasta</label>
                    <input type="date" class="form-control" name="end_date" value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="ri-search-line me-1"></i> Filtrar
                    </button>
                    <a href="activity_history_page.php" class="btn btn-outline-secondary">
                        <i class="ri-refresh-line"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Resultados -->
        <div class="card shadow-sm border-0 report-card">
            <div class="card-body">
                <?php if (!$filtered): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="ri-filter-line" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Aplique filtros para generar el reporte</h5>
                        <p class="small">Seleccione un usuario o rango de fechas y haga clic en "Filtrar".</p>
                    </div>
                <?php elseif (empty($records)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="ri-inbox-line" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">No se encontraron registros</h5>
                        <p class="small">Ajuste los filtros e intente nuevamente.</p>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-3">Registros encontrados: <strong><?= count($records) ?></strong></p>
                    <div class="table-responsive">
                        <table id="activityTable" class="table table-striped table-hover align-middle w-100">
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
                                        <td><?= htmlspecialchars($record['id']) ?></td>
                                        <td data-order="<?= strtotime($record['created_at']) ?>">
                                            <?= date('d/m/Y H:i:s', strtotime($record['created_at'])) ?>
                                        </td>
                                        <td><?= htmlspecialchars($record['username']) ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($record['department_name'] ?? 'N/A') ?></span></td>
                                        <td><?= htmlspecialchars($record['action']) ?></td>
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

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script type="text/javascript" src="../../public/datatables/datatables.min.js"></script>
<link rel="stylesheet" type="text/css" href="../../public/datatables/datatables.min.css"/>
<script>
$(document).ready(function() {
    if ($.fn.DataTable && $('#activityTable').length) {
        $('#activityTable').DataTable({
            responsive: true,
            dom: '<"d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2"Bf>rtip',
            buttons: [
                { extend: 'excelHtml5', text: '<i class="ri-file-excel-line"></i> Excel', className: 'btn btn-success btn-sm me-1' },
                { extend: 'pdfHtml5', text: '<i class="ri-file-pdf-line"></i> PDF', className: 'btn btn-danger btn-sm me-1' },
                { extend: 'print', text: '<i class="ri-printer-line"></i> Imprimir', className: 'btn btn-info btn-sm' }
            ],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
            order: [[1, 'desc']]
        });
    }
});
</script>
