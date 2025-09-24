<?php
session_start();
require_once __DIR__ . '/../../controllers/CitationsController.php';

$citationsController = new CitationsController();

$params = [
    'page' => $_GET['page'] ?? 1,
    'search' => $_GET['search'] ?? ''
];

// Asume que el modelo CitationsModel puede obtener los nombres de los mediadores e infracciones
// Por ejemplo, mediante un JOIN en la base de datos o un método adicional en el controlador.
$result = $citationsController->index($params);

// Verifica si la llamada al controlador devolvió un error
if (!isset($result['success']) || $result['success']) {
    // Si no hay un error 'success' falso, extrae los datos
    extract($result);
} else {
    // Si hay un error, maneja la situación (por ejemplo, muestra un mensaje de error)
    $citations = [];
    $current_page = 1;
    $total_pages = 1;
    $total_records = 0;
    $search = '';
    $page_title = 'Gestión de Citaciones';
    $has_search = false;
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'Hubo un error al cargar las citaciones.'
    ];
}


if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];
    $deleteResult = $citationsController->delete($deleteId);

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

// Opciones en español para el select de estado de citación
$allowed_status = [
    'Scheduled' => 'Programada',
    'Rescheduled' => 'Reprogramada',
    'Completed' => 'Completada',
    'Canceled' => 'Cancelada'
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
                            <i class="ri-calendar-event-line me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri-add-line"></i> Nueva Citación
                        </a>
                    </div>
                    
                    <div class="card-body border-bottom">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="Buscar por ubicación, estado..." value="<?php echo htmlspecialchars($search); ?>">
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
                        <?php if (empty($citations)): ?>
                            <div class="text-center py-4">
                                <i class="ri-calendar-close-line text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-2">
                                    <?php echo $has_search ? 'No se encontraron citaciones con ese criterio' : 'No hay citaciones programadas'; ?>
                                </h5>
                                <?php if (!$has_search): ?>
                                    <a href="create.php" class="btn btn-primary mt-2">
                                        <i class="ri-add-line"></i> Programar Primera Citación
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID Infracción</th>
                                            <th>Fecha y Hora</th>
                                            <th>Ubicación</th>
                                            <th>ID Mediador</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($citations as $citation): ?>
                                        <tr>
                                            <td>
                                                <strong>#<?php echo htmlspecialchars($citation['infraction_id']); ?></strong>
                                            </td>
                                            <td>
                                                <?php 
                                                $date = new DateTime($citation['citation_datetime']);
                                                echo $date->format('d/m/Y H:i'); 
                                                ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($citation['location']); ?>
                                            </td>
                                            <td>
                                                <strong>ID: <?php echo htmlspecialchars($citation['mediator_user_id']); ?></strong>
                                            </td>
                                            <td>
                                                <?php
                                                $status_colors = ['Scheduled' => 'primary', 'Rescheduled' => 'info', 'Completed' => 'success', 'Canceled' => 'dark'];
                                                $s_color = $status_colors[$citation['citation_status']] ?? 'light';
                                                ?>
                                                <span class="badge bg-<?php echo $s_color; ?>"><?php echo htmlspecialchars($allowed_status[$citation['citation_status']]); ?></span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="view.php?id=<?php echo $citation['citation_id']; ?>" class="btn btn-sm btn-outline-primary" title="Ver detalles"><i class="ri-eye-line"></i></a>
                                                    <a href="edit.php?id=<?php echo $citation['citation_id']; ?>" class="btn btn-sm btn-outline-warning" title="Editar"><i class="ri-edit-line"></i></a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $citation['citation_id']; ?>)" title="Eliminar"><i class="ri-delete-bin-line"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php if ($total_pages > 1): ?>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <nav aria-label="Page navigation example">
                                    <ul class="pagination mb-0">
                                        <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $current_page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Anterior</a>
                                        </li>
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $current_page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Siguiente</a>
                                        </li>
                                    </ul>
                                </nav>
                                <span class="text-muted">Mostrando <?php echo $total_records; ?> citaciones en total</span>
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
                <p>¿Está seguro que desea eliminar la citación con ID: <strong id="citationId"></strong>?</p>
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
let deleteCitationId = null;

function confirmDelete(id) {
    deleteCitationId = id;
    document.getElementById('citationId').textContent = id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteCitationId) {
        window.location.href = 'index.php?delete_id=' + deleteCitationId; 
    }
});
</script>