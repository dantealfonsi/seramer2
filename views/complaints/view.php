<?php
// Vista de detalles de una queja

session_start();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/ComplaintsController.php';

$complaintsController = new ComplaintsController();

// Obtener el ID de la queja
$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'No se especificó una queja para ver.'
    ];
    header('Location: index.php');
    exit;
}

// Usar el controlador para obtener los datos
$result = $complaintsController->view($id);

// Si hay error o no se encuentra la queja, redirigir
if (!$result['success']) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => $result['message']
    ];
    header('Location: index.php');
    exit;
}

// Extraer variables para la vista
$complaint = $result['complaint'];
$page_title = $result['page_title'];

// Incluir header y layouts
require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Quejas</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detalles de Queja</li>
                    </ol>
                </nav>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"  style="font-size: 2rem;font-weight: 600;">
                            <i class="ri-feedback-line me-1"  style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <div class="btn-group" role="group">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Volver al listado
                            </a>
                            <a href="edit.php?id=<?php echo $complaint['complaint_id']; ?>" class="btn btn-warning">
                                <i class="ri-edit-line"></i> Editar
                            </a>
                            <button type="button" 
                                    class="btn btn-danger" 
                                    onclick="confirmDelete(<?php echo $complaint['complaint_id']; ?>)">
                                <i class="ri-delete-bin-line"></i> Eliminar
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <th width="30%">ID:</th>
                                            <td><?php echo htmlspecialchars($complaint['complaint_id']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Cliente:</th>
                                            <td>
                                                <strong><?php echo htmlspecialchars($complaint['client_name']); ?></strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Teléfono del Cliente:</th>
                                            <td><?php echo htmlspecialchars($complaint['client_phone']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Email del Cliente:</th>
                                            <td><?php echo htmlspecialchars($complaint['client_email']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Tipo de Queja:</th>
                                            <td>
                                                <span class="badge bg-info fs-6">
                                                    <?php echo htmlspecialchars($complaint['complaint_type']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Prioridad:</th>
                                            <td>
                                                <?php
                                                $priority_colors = [
                                                    'Low' => 'success',
                                                    'Medium' => 'warning',
                                                    'High' => 'danger'
                                                ];
                                                $color = $priority_colors[$complaint['complaint_priority']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $color; ?> fs-6">
                                                    <?php echo htmlspecialchars($complaint['complaint_priority']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Estado:</th>
                                            <td>
                                                <?php
                                                $status_colors = [
                                                    'Received' => 'secondary',
                                                    'In Process' => 'primary',
                                                    'Resolved' => 'success',
                                                    'Cancelled' => 'danger'
                                                ];
                                                $color = $status_colors[$complaint['complaint_status']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $color; ?> fs-6">
                                                    <?php echo htmlspecialchars($complaint['complaint_status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted"><i class="ri-file-text-line"></i> Descripción de la Queja</h6>
                                        <p class="card-text"><?php echo nl2br(htmlspecialchars($complaint['complaint_description'])); ?></p>
                                    </div>
                                </div>
                                <div class="card bg-light border-0 mt-3">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted"><i class="ri-file-edit-line"></i> Observaciones Internas</h6>
                                        <p class="card-text"><?php echo nl2br(htmlspecialchars($complaint['internal_observations'] ?? 'No hay observaciones.')); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación de Queja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar la queja con ID: <strong id="complaintId"></strong>?</p>
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
let deleteComplaintId = null;

function confirmDelete(id) {
    deleteComplaintId = id;
    document.getElementById('complaintId').textContent = id;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteComplaintId) {
        // Crear formulario para enviar la solicitud de eliminación
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'delete.php';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = deleteComplaintId;
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