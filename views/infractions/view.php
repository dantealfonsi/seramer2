<?php
// Vista de listado de infracciones

session_start();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/InfractionsController.php';

$infractionsController = new InfractionsController();

// Obtener parámetros de búsqueda y paginación
$params = [
    'page' => $_GET['page'] ?? 1,
    'search' => $_GET['search'] ?? ''
];

// Usar el controlador para obtener los datos
$result = $infractionsController->index($params);

// Extraer variables para la vista
$infractions = $result['infractions'];
$currentPage = $result['current_page'];
$totalPages = $result['total_pages'];
$totalRecords = $result['total_records'];
$search = $result['search'];
$page_title = $result['page_title'];
$hasSearch = $result['has_search'];

// Mensaje flash para mostrar
$flash_message = $_SESSION['flash_message'] ?? null;
unset($_SESSION['flash_message']);

// Incluir header y layouts
require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="page-header-title">
                        <i class="ri-alert-line me-1"></i>
                        <?php echo htmlspecialchars($page_title); ?>
                    </h1>
                    <a href="create.php" class="btn btn-primary">
                        <i class="ri-add-line"></i> Registrar Nueva Infracción
                    </a>
                </div>

                <?php if ($flash_message): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flash_message['type']); ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($flash_message['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Listado de Infracciones</h5>
                        <form action="index.php" method="GET" class="d-flex">
                            <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Buscar..." value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-outline-secondary btn-sm" type="submit">
                                <i class="ri-search-line"></i>
                            </button>
                            <?php if ($hasSearch): ?>
                            <a href="index.php" class="btn btn-outline-danger btn-sm ms-2" title="Limpiar búsqueda">
                                <i class="ri-close-line"></i>
                            </a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Adjudicatario</th>
                                        <th>Puesto</th>
                                        <th>Tipo de Infracción</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($infractions)): ?>
                                        <?php foreach ($infractions as $infraction): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($infraction['id_infraction']); ?></td>
                                                <td>
                                                    <strong>
                                                        <?php echo htmlspecialchars($infraction['adjudicatory_name']); ?>
                                                    </strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?php echo htmlspecialchars($infraction['stall_name'] ?? 'N/A'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo htmlspecialchars($infraction['infraction_type']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $infraction_date = new DateTime($infraction['infraction_date']);
                                                    echo $infraction_date->format('d/m/Y'); 
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status_colors = [
                                                        'Reported' => 'warning',
                                                        'In Process' => 'primary',
                                                        'Resolved' => 'success',
                                                        'Cancelled' => 'danger'
                                                    ];
                                                    $color = $status_colors[$infraction['infraction_status']] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?php echo $color; ?>">
                                                        <?php echo htmlspecialchars($infraction['infraction_status']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="view.php?id=<?php echo $infraction['id_infraction']; ?>" class="btn btn-sm btn-info" title="Ver detalles">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                    <a href="edit.php?id=<?php echo $infraction['id_infraction']; ?>" class="btn btn-sm btn-warning" title="Editar">
                                                        <i class="ri-edit-line"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger" 
                                                            title="Eliminar"
                                                            onclick="confirmDelete(<?php echo $infraction['id_infraction']; ?>)">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <i class="ri-alert-line text-muted" style="font-size: 3rem;"></i>
                                                <h5 class="text-muted mt-2">No se encontraron infracciones</h5>
                                                <p class="text-muted">Intenta ajustar tu búsqueda o registra una nueva infracción.</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <?php if ($totalPages > 1): ?>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <p class="text-muted mb-0">Mostrando <?php echo count($infractions); ?> de <?php echo $totalRecords; ?> resultados</p>
                        <nav aria-label="Paginación">
                            <ul class="pagination mb-0">
                                <li class="page-item <?php echo $currentPage == 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>&search=<?php echo urlencode($search); ?>" aria-label="Anterior">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $currentPage == $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $currentPage == $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>&search=<?php echo urlencode($search); ?>" aria-label="Siguiente">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación de Infracción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar lógicamente la infracción con ID: <strong id="infractionId"></strong>?</p>
                <p class="text-danger"><small>Esta acción marcará la infracción como eliminada, pero se mantendrá un registro en la base de datos.</small></p>
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
let deleteInfractionId = null;

function confirmDelete(id) {
    deleteInfractionId = id;
    document.getElementById('infractionId').textContent = id;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteInfractionId) {
        // Enviar la solicitud de eliminación vía AJAX
        fetch(`delete.php?id=${deleteInfractionId}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Si la eliminación fue exitosa, recargar la página para ver el cambio
                window.location.reload();
            } else {
                // Manejar el error, por ejemplo, mostrando un mensaje
                alert('Error al eliminar la infracción: ' + data.message);
                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
                modal.hide();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrió un error al procesar la solicitud.');
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
            modal.hide();
        });
    }
});
</script>