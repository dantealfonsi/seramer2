<?php
// Vista de listado de infracciones

session_start();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/InfractionsController.php';

$infractionsController = new InfractionsController();

// Preparar parámetros desde la petición
$params = [
    'page' => $_GET['page'] ?? 1,
    'search' => $_GET['search'] ?? ''
];

// Usar el controlador para obtener los datos
$result = $infractionsController->index($params);

// Extraer variables para la vista
$infractions = $result['infractions'];
$current_page = $result['current_page'];
$total_pages = $result['total_pages'];
$total_records = $result['total_records'];
$search = $result['search'];
$page_title = $result['page_title'];
$has_search = $result['has_search'];

// Incluir header y layouts
require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title">
                            <i class="ri-alert-line me-1"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri-add-line"></i> Nueva Infracción
                        </a>
                    </div>
                    
                    <div class="card-body border-bottom">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control" 
                                           name="search" 
                                           placeholder="Buscar por adjudicatario o puesto..."
                                           value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="ri-search-line"></i>
                                    </button>
                                </div>
                            </div>
                            <?php if ($has_search): ?>
                            <div class="col-md-3">
                                <a href="index.php" class="btn btn-outline-info">
                                    <i class="ri-close-line"></i> Limpiar filtros
                                </a>
                            </div>
                            <?php endif; ?>
                        </form>
                        
                        <?php if ($has_search): ?>
                        <div class="mt-2">
                            <small class="text-muted">
                                Mostrando resultados para: <strong>"<?php echo htmlspecialchars($search); ?>"</strong>
                                (<?php echo $total_records; ?> resultado<?php echo $total_records != 1 ? 's' : ''; ?>)
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if (isset($_SESSION['flash_message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['flash_message']['type'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show mx-3 mt-3" role="alert">
                        <?php echo htmlspecialchars($_SESSION['flash_message']['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['flash_message']); ?>
                    <?php endif; ?>

                    <div class="card-body">
                        <?php if (empty($infractions)): ?>
                            <div class="text-center py-4">
                                <i class="ri-alert-line text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-2">
                                    <?php echo $has_search ? 'No se encontraron infracciones con ese criterio' : 'No hay infracciones registradas'; ?>
                                </h5>
                                <?php if (!$has_search): ?>
                                <p class="text-muted">Comienza creando la primera infracción.</p>
                                <a href="create.php" class="btn btn-primary">
                                    <i class="ri-add-line"></i> Crear Primera Infracción
                                </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Adjudicatario</th>
                                            <th>Puesto</th>
                                            <th>Tipo</th>
                                            <th>Fecha</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($infractions as $infraction): ?>
                                        <tr>
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
                                                    <?php echo htmlspecialchars($infraction['infraction_type_name']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                $infraction_date = new DateTime($infraction['infraction_datetime']);
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
                                            // Aquí también se corrige la clave
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
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php if ($total_pages > 1): ?>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <small class="text-muted">
                                        Mostrando página <?php echo $current_page; ?> de <?php echo $total_pages; ?>
                                        (<?php echo $total_records; ?> registro<?php echo $total_records != 1 ? 's' : ''; ?> total<?php echo $total_records != 1 ? 'es' : ''; ?>)
                                    </small>
                                </div>
                                <nav aria-label="Paginación de infracciones">
                                    <ul class="pagination pagination-sm mb-0">
                                        <?php if ($current_page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo ($current_page - 1); ?><?php echo $has_search ? '&search=' . urlencode($search) : ''; ?>">
                                                <i class="ri-arrow-left-s-line"></i>
                                            </a>
                                        </li>
                                        <?php endif; ?>

                                        <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                        <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $has_search ? '&search=' . urlencode($search) : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                        <?php endfor; ?>

                                        <?php if ($current_page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo ($current_page + 1); ?><?php echo $has_search ? '&search=' . urlencode($search) : ''; ?>">
                                                <i class="ri-arrow-right-s-line"></i>
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
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
                <p>¿Está seguro que desea eliminar la infracción con ID: <strong id="infractionId"></strong>?</p>
                <p class="text-danger"><small>Esta acción no se puede deshacer y eliminará el registro de forma permanente.</small></p>
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
        // Crear formulario para enviar la solicitud de eliminación
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'delete.php';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = deleteInfractionId;
        form.appendChild(idInput);
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
});
</script>