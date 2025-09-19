<?php
session_start();
require_once __DIR__ . '/../../controllers/InfractionTypesController.php';

$infractionTypesController = new InfractionTypesController();

$params = [
    'page' => $_GET['page'] ?? 1,
    'search' => $_GET['search'] ?? ''
];

$result = $infractionTypesController->index($params);

// Extrae $infraction_types, $current_page, $total_pages, etc.
extract($result);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];
    $deleteResult = $infractionTypesController->delete($deleteId);

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
                            <i class="ri-alert-line me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri-add-line"></i> Nuevo Tipo de Infracción
                        </a>
                    </div>
                    
                    <div class="card-body border-bottom">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="Buscar por tipo de infracción, artículo violado..." value="<?php echo htmlspecialchars($search); ?>">
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

                    <div class="card-body">
                        <?php if (empty($infraction_types)): ?>
                            <div class="text-center py-4">
                                <i class="ri-file-search-line text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-2">
                                    <?php echo $has_search ? 'No se encontraron tipos de infracción con ese criterio' : 'No hay tipos de infracción registrados'; ?>
                                </h5>
                                <?php if (!$has_search): ?>
                                    <a href="create.php" class="btn btn-primary mt-2">
                                        <i class="ri-add-line"></i> Registrar Primer Tipo de Infracción
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Tipo de Infracción</th>
                                            <th>Descripción</th>
                                            <th>Artículo Violado</th>
                                            <th>Multa Base</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($infraction_types as $infractionType): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($infractionType['infraction_type_name']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($infractionType['description']); ?></td>
                                            <td><?php echo htmlspecialchars($infractionType['violated_article']); ?></td>
                                            <td><?php echo '$' . number_format($infractionType['base_fine'], 2); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="view.php?id=<?php echo $infractionType['infraction_type_id']; ?>" class="btn btn-sm btn-outline-primary" title="Ver detalles"><i class="ri-eye-line"></i></a>
                                                    <a href="edit.php?id=<?php echo $infractionType['infraction_type_id']; ?>" class="btn btn-sm btn-outline-warning" title="Editar"><i class="ri-edit-line"></i></a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $infractionType['infraction_type_id']; ?>)" title="Eliminar"><i class="ri-delete-bin-line"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php if ($total_pages > 1): ?>
                            <nav aria-label="Paginación de tipos de infracción">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&search=<?php echo urlencode($search); ?>">Anterior</a>
                                    </li>
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo ($current_page == $i) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                    </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search); ?>">Siguiente</a>
                                    </li>
                                </ul>
                            </nav>
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
                <p>¿Está seguro que desea eliminar el tipo de infracción con ID: <strong id="infractionTypeId"></strong>?</p>
                <p class="text-danger"><small>Esta acción es permanente y puede afectar a otros registros que hagan referencia a este tipo de infracción.</small></p>
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
let deleteInfractionTypeId = null;

function confirmDelete(id) {
    deleteInfractionTypeId = id;
    document.getElementById('infractionTypeId').textContent = id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteInfractionTypeId) {
        window.location.href = 'index.php?delete_id=' + deleteInfractionTypeId; 
    }
});
</script>
