<?php
session_start();
require_once __DIR__ . '/../../controllers/ComplaintsController.php';

$complaintsController = new ComplaintsController();

$params = [
    'page' => $_GET['page'] ?? 1,
    'search' => $_GET['search'] ?? ''
];
$result = $complaintsController->index($params);
extract($result); // Extrae $complaints, $current_page, $total_pages, etc.

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];
    $deleteResult = $complaintsController->delete($deleteId);

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

// Opciones en español para los select de estado y prioridad
$allowed_priority = [
    'Low' => 'Baja',
    'Medium' => 'Media',
    'High' => 'Alta',
    'Urgent' => 'Urgente'
];
$allowed_status = [
    'Received' => 'Recibido',
    'In Process' => 'En Proceso',
    'Resolved' => 'Resuelto',
    'Closed' => 'Cerrado'
];

$allowed_tipo = [
     'Suggestion' => 'Sugerencia',
     'Claim' => 'Reclamo',
     'Question' => 'Pregunta'    
]

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
                            <i class="ri-chat-voice-line me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri-add-line"></i> Nueva Queja
                        </a>
                    </div>
                    
                    <div class="card-body border-bottom">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="Buscar por cliente, descripción, puesto..." value="<?php echo htmlspecialchars($search); ?>">
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
                        <?php if (empty($complaints)): ?>
                            <div class="text-center py-4">
                                <i class="ri-chat-off-line text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-2">
                                    <?php echo $has_search ? 'No se encontraron quejas con ese criterio' : 'No hay quejas registradas'; ?>
                                </h5>
                                <?php if (!$has_search): ?>
                                    <a href="create.php" class="btn btn-primary mt-2">
                                        <i class="ri-add-line"></i> Registrar Primera Queja
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Cliente</th>
                                            <th>Tipo</th>
                                            <th>Prioridad</th>
                                            <th>Estado</th>
                                            <th>Fecha</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($complaints as $complaint): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($complaint['client_name']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($complaint['client_email']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($allowed_tipo[$complaint['complaint_type']]); ?></span>
                                            </td>
                                            <td>
                                                <?php
                                                $priority_colors = ['Low' => 'secondary', 'Medium' => 'info', 'High' => 'warning', 'Urgent' => 'danger'];
                                                $p_color = $priority_colors[$complaint['complaint_priority']] ?? 'light';
                                                ?>
                                                <span class="badge bg-<?php echo $p_color; ?>"><?php echo htmlspecialchars($allowed_priority[$complaint['complaint_priority']]); ?></span>
                                            </td>
                                            <td>
                                                <?php
                                                $status_colors = ['Received' => 'primary', 'In Process' => 'warning', 'Resolved' => 'success', 'Closed' => 'dark'];
                                                $s_color = $status_colors[$complaint['complaint_status']] ?? 'light';
                                                ?>
                                                <span class="badge bg-<?php echo $s_color; ?>"><?php echo htmlspecialchars($allowed_status[$complaint['complaint_status']]); ?></span>
                                            </td>
                                            <td>
                                                <?php 
                                                $date = new DateTime($complaint['complaint_timestamp']);
                                                echo $date->format('d/m/Y H:i'); 
                                                ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="view.php?id=<?php echo $complaint['complaint_id']; ?>" class="btn btn-sm btn-outline-primary" title="Ver detalles"><i class="ri-eye-line"></i></a>
                                                    <a href="edit.php?id=<?php echo $complaint['complaint_id']; ?>" class="btn btn-sm btn-outline-warning" title="Editar"><i class="ri-edit-line"></i></a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $complaint['complaint_id']; ?>)" title="Eliminar"><i class="ri-delete-bin-line"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php if ($total_pages > 1): ?>
                            <div class="d-flex justify-content-between align-items-center mt-3">
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
                <p>¿Está seguro que desea eliminar la queja con ID: <strong id="complaintId"></strong>?</p>
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
let deleteComplaintId = null;

function confirmDelete(id) {
    deleteComplaintId = id;
    document.getElementById('complaintId').textContent = id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteComplaintId) {
        window.location.href = 'index.php?delete_id=' + deleteComplaintId; 
    }
});
</script>