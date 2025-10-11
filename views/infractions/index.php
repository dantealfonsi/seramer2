<?php
// Vista de listado de infracciones

session_start();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/InfractionsController.php';
//require_once __DIR__ . '/../../models/MarketStallsModel.php';

$infractionsController = new InfractionsController();

// Preparar parámetros desde la petición
$filters = [
    'search' => $_GET['search'] ?? '',
    'infraction_date' => $_GET['infraction_date'] ?? null,
    'infraction_status' => $_GET['infraction_status'] ?? null,
    'infraction_type_id' => $_GET['infraction_type_id'] ?? null,
    'stall_id' => $_GET['stall_id'] ?? null,
    'awardee_id' => $_GET['awardee_id'] ?? null,
];

// Limpiar el arreglo eliminando valores nulos o vacíos
$activeFilters = array_filter($filters);

$params = [
    'filters' => $activeFilters,
    'limit' => 10,
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
$stalls = $infractionsController->getStallsList();
$infraction_types = $infractionsController->getInfractionTypesList();

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
                        <h5 class="card-title" style="font-size: 2rem;font-weight: 600;">
                            <i class="ri-alert-line me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri-add-line"></i> Nueva Infracción
                        </a>
                    </div>
                    
                    <div class="card-body border-bottom">
                        <form action="index.php" method="GET" class="card p-3 mb-4 shadow-sm">
                            <h6 class="card-title mb-3"><i class="ri-filter-2-line me-1"></i> Opciones de Filtrado</h6>
                            <div class="row g-3">
                                
                                <!-- Filtro General de Búsqueda (por nombre, puesto, tipo) -->
                                <div class="col-md-3">
                                    <label for="search" class="form-label small">Búsqueda General</label>
                                    <!-- Mantener el valor actual del filtro después de la búsqueda -->
                                    <input type="text" class="form-control" id="search" name="search" 
                                        placeholder="Nombre, Puesto, Tipo..." 
                                        value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                </div>

                                <!-- 1. Filtro por Estado (infraction_status) -->
                                <div class="col-md-3">
                                    <label for="infraction_status" class="form-label small">Estado</label>
                                    <select class="form-select" id="infraction_status" name="infraction_status">
                                        <option value="">-- Todos los Estados --</option>
                                        <?php 
                                        $allowed_status = [
                                            'Reported' => 'Reportada',
                                            'In Process' => 'En Proceso',
                                            'Resolved' => 'Resuelta',
                                            'Cancelled' => 'Cancelada',
                                            // 'Pending' => 'Pendiente',
                                            // 'Sanctioned' => 'Sancionada',
                                            // 'Archived' => 'Archivada'
                                        ];                                        
                                        $current_status = $_GET['infraction_status'] ?? '';
                                        foreach ($allowed_status as $key => $value): ?>
                                            <option value="<?php echo $key; ?>" 
                                                    <?php echo ($current_status === $key) ? 'selected' : ''; ?>>
                                                <?php echo $value; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- 2. Filtro por Tipo de Infracción (infraction_type_id) -->
                                <div class="col-md-3">
                                    <label for="infraction_type_id" class="form-label small">Tipo de Infracción</label>
                                    <select class="form-select" id="infraction_type_id" name="infraction_type_id">
                                        <option value="">-- Todos los Tipos --</option>
                                        <?php 
                                        // Supongamos que tienes la variable $infraction_types cargada
                                        // con los IDs y nombres desde la base de datos.
                                        // Ejemplo de estructura: [['infraction_type_id' => 1, 'name' => 'Ruido Excesivo']]
                                        $current_type = $_GET['infraction_type_id'] ?? '';
                                        // Aquí deberías iterar sobre $infraction_types
                                        if (isset($infraction_types) && is_array($infraction_types)) {
                                            foreach ($infraction_types as $type) {
                                                $id = $type['infraction_type_id'];
                                                $name = $type['infraction_type_name'];
                                                echo "<option value=\"$id\" " . (($current_type == $id) ? 'selected' : '') . ">$name</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <!-- 3. Filtro por Fecha (infraction_date) -->
                                <div class="col-md-3">
                                    <label for="infraction_date" class="form-label small">Fecha Específica</label>
                                    <input type="date" class="form-control" id="infraction_date" name="infraction_date" 
                                        value="<?php echo htmlspecialchars($_GET['infraction_date'] ?? ''); ?>">
                                </div>

                                <!-- 4. Filtro por Puesto (stall_id) -->
                                <!-- NOTA: Los filtros por IDs (Stall y Awardee) requieren cargar listas grandes, 
                                    por lo que se suelen implementar como selects o campos de autocompletado. -->
                                <div class="col-md-3">
                                    <label for="stall_id" class="form-label small">Puesto (ID/Nro)</label>
                                    <input type="number" class="form-control" id="stall_id" name="stall_id" 
                                        placeholder="Ej: 15" 
                                        value="<?php echo htmlspecialchars($_GET['stall_id'] ?? ''); ?>">
                                </div>

                                <!-- 5. Filtro por Adjudicatario (awardee_id) -->
                                <div class="col-md-3">
                                    <label for="awardee_id" class="form-label small">Adjudicatario (ID)</label>
                                    <input type="number" class="form-control" id="awardee_id" name="awardee_id" 
                                        placeholder="Ej: 42" 
                                        value="<?php echo htmlspecialchars($_GET['awardee_id'] ?? ''); ?>">
                                </div>

                                <!-- Botones de Acción -->
                                <div class="col-12 d-flex justify-content-end align-items-end">
                                    <a href="index.php" class="btn btn-outline-secondary me-2">Limpiar Filtros</a>
                                    <button type="submit" class="btn btn-info">
                                        <i class="ri-search-line"></i> Buscar / Filtrar
                                    </button>
                                </div>
                            </div>
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
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
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
                                                    <?php echo htmlspecialchars($infraction['stall_number'] ?? 'N/A'); ?>
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
                                                    <a href="view.php?id=<?php echo $infraction['infraction_id']; ?>" class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                    <a href="edit.php?id=<?php echo $infraction['infraction_id']; ?>" class="btn btn-sm btn-outline-warning" title="Editar">
                                                        <i class="ri-edit-line"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Eliminar"
                                                            onclick="confirmDelete(<?php echo $infraction['infraction_id']; ?>)">
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