<?php
session_start();
require_once __DIR__ . '/../../controllers/ConciliationReportsController.php';

$reportsController = new ConciliationReportsController();

$params = [
    'page' => $_GET['page'] ?? 1,
    'search' => $_GET['search'] ?? ''
];

$result = $reportsController->index($params);

extract($result); // Extrae $reports, $current_page, $total_pages, etc.

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];
    $deleteResult = $reportsController->delete($deleteId);

    $_SESSION['flash_message'] = [
        'type' => $deleteResult['success'] ? 'success' : 'danger',
        'message' => $deleteResult['message']
    ];

    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';

// Opciones para el select de resultados de conciliación
$allowed_results = [
    'Agreement Reached' => 'Acuerdo alcanzado',
    'No Agreement' => 'Sin acuerdo',
    'Case Postponed' => 'Caso pospuesto',
    'Absent Party' => 'Parte ausente'
];

?>
<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?> mt-2" role="alert">
        <?php echo htmlspecialchars($_SESSION['flash_message']['message']); ?>
    </div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title" style="font-size: 2rem;font-weight: 600;">
                            <i class="ri-file-text-line me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri-add-line"></i> Nuevo Informe
                        </a>
                    </div>
                    
                    <div class="card-body border-bottom">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="Buscar por resultado de conciliación..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-outline-secondary" type="submit"><i class="ri-search-line"></i></button>
                                </div>
                            </div>
                            <?php if ($has_search): ?>
                            <div class="col-md-3">
                                <a href="index.php" class="btn btn-outline-info"><i class="ri-close-line"></i> Limpiar búsqueda</a>
                            </div>
                            <?php endif; ?>
                        </form>
                    </div>

                    <?php if (isset($_SESSION['flash_message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['flash_message']['type'] === 'success' ? 'success' : 'danger'; ?> mx-3 mt-3" role="alert">
                        <?php echo htmlspecialchars($_SESSION['flash_message']['message']); ?>
                    </div>
                    <?php unset($_SESSION['flash_message']); ?>
                    <?php endif; ?>

                    <div class="card-body">
                        <?php if (empty($reports)): ?>
                            <div class="text-center py-4">
                                <i class="ri-file-forbid-line text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-2">
                                    <?php echo $has_search ? 'No se encontraron informes con ese criterio' : 'No hay informes de conciliación registrados'; ?>
                                </h5>
                                <?php if (!$has_search): ?>
                                    <a href="create.php" class="btn btn-primary mt-2">
                                        <i class="ri-add-line"></i> Registrar Primer Informe
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Citación ID</th>
                                            <th>Asistencia</th>
                                            <th>Resultado</th>
                                            <th>Fecha del Informe</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reports as $report): ?>
                                        <tr>
                                            <td>
                                                <a href="view.php?id=<?php echo $report['citation_id']; ?>">#<?php echo htmlspecialchars($report['citation_id']); ?></a>
                                            </td>
                                            <td>
                                                <?php if ($report['awardee_attendance'] == 1): ?>
                                                    <span class="badge bg-success">Presente</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Ausente</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($allowed_results[$report['result']] ?? $report['result']); ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $date = new DateTime($report['report_date']);
                                                echo $date->format('d/m/Y H:i'); 
                                                ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="view.php?id=<?php echo $report['report_id']; ?>" class="btn btn-sm btn-outline-primary" title="Ver detalles"><i class="ri-eye-line"></i></a>
                                                    <a href="edit.php?id=<?php echo $report['report_id']; ?>" class="btn btn-sm btn-outline-warning" title="Editar"><i class="ri-edit-line"></i></a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $report['report_id']; ?>)" title="Eliminar"><i class="ri-delete-bin-line"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php if ($total_pages > 1): ?>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <nav>
                                    <ul class="pagination mb-0">
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                                <small class="text-muted">Mostrando página <?php echo $current_page; ?> de <?php echo $total_pages; ?> (<?php echo $total_records; ?> registros)</small>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar el informe con ID: <strong id="reportId"></strong>?</p>
                <p class="text-danger"><small>Esta acción es permanente.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script>
let deleteReportId = null;

function confirmDelete(id) {
    deleteReportId = id;
    document.getElementById('reportId').textContent = id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteReportId) {
        window.location.href = 'index.php?delete_id=' + deleteReportId; 
    }
});
</script>