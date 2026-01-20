<?php
session_start();
require_once __DIR__ . '/../../controllers/ComplaintTrackingController.php';
require_once __DIR__ . '/../../controllers/ComplaintsController.php';

$trackingController = new ComplaintTrackingController();
$complaintsController = new ComplaintsController();
$complaint_id = $_GET['id'] ?? null;
$result = null;

if (!$complaint_id) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'ID de queja no proporcionado.'];
    header("Location: ../complaints/index.php");
    exit;
}

$result = $trackingController->index($complaint_id);

if (!$result['success']) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => $result['message']];
    header("Location: ../complaints/index.php");
    exit;
}

$tracking_records = $result['tracking_records'];
$complaint = $result['complaint'];
$page_title = $result['page_title'];

// Manejar la solicitud de eliminación si se recibe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $delete_id = $_POST['id'];
    $deleteResult = $trackingController->delete($delete_id);
    if ($deleteResult['success']) {
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => $deleteResult['message']];
    } else {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => $deleteResult['message']];
    }
    // Redireccionar para evitar reenvío del formulario
    header("Location: view.php?id=" . $complaint_id);
    exit;
}

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
                            <i class="ri-eye-line me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="../complaints/index.php" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i> Volver a Quejas
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Detalles de la Queja Principal -->
                        <div class="card bg-light mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Detalles de la Queja Principal</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-2"><strong>Tipo de Queja:</strong> <?php echo htmlspecialchars($complaint['complaint_type']); ?></p>
                                <p class="mb-2"><strong>Descripción:</strong> <?php echo nl2br(htmlspecialchars($complaint['complaint_description'])); ?></p>
                                <p class="mb-2"><strong>Estado:</strong> <span class="badge <?php 
                                    if ($complaint['complaint_status'] === 'Received') echo 'bg-info';
                                    if ($complaint['complaint_status'] === 'Assigned') echo 'bg-primary';
                                    if ($complaint['complaint_status'] === 'Resolved') echo 'bg-success';
                                    if ($complaint['complaint_status'] === 'Closed') echo 'bg-dark';
                                ?>"><?php echo htmlspecialchars($allowed_status[$complaint['complaint_status']]); ?></span></p>
                                <p class="mb-2"><strong>Prioridad:</strong> <span class="badge <?php 
                                    if ($complaint['complaint_priority'] === 'High') echo 'bg-danger';
                                    if ($complaint['complaint_priority'] === 'Medium') echo 'bg-warning text-dark';
                                    if ($complaint['complaint_priority'] === 'Low') echo 'bg-secondary';
                                ?>"><?php echo htmlspecialchars($allowed_priority[$complaint['complaint_priority']]); ?></span></p>
                            </div>
                        </div>

                        <!-- Historial de Seguimiento -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Historial de Seguimiento</h6>
                            <?php if ($_SESSION['selected_department'] === 'Recursos Humanos' || $_SESSION['selected_department'] === 'Fiscalizacion'): ?>
                            <a href="create.php?complaint_id=<?php echo htmlspecialchars($complaint_id); ?>" class="btn btn-primary">
                                <i class="ri-add-line"></i> Añadir Registro
                            </a>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (empty($tracking_records)): ?>
                            <div class="alert alert-info">No hay registros de seguimiento para esta queja.</div>
                        <?php else: ?>
                            <ul class="timeline">
                                <?php foreach ($tracking_records as $record): ?>
                                    <li>
                                        <div class="timeline-badge">
                                            <i class="ri-check-line"></i>
                                        </div>
                                        <div class="timeline-panel card">
                                            <div class="card-body">
                                                <div class="timeline-heading d-flex justify-content-between align-items-start">
                                                    <h6 class="timeline-title mb-1"><?php echo htmlspecialchars($record['action_type']); ?></h6>
                                                    <?php if ($_SESSION['selected_department'] === 'Recursos Humanos'): ?>
                                                    <div class="btn-group">
                                                        <a href="edit.php?id=<?php echo htmlspecialchars($record['tracking_id']); ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="ri-pencil-line"></i></a>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo htmlspecialchars($record['tracking_id']); ?>)" title="Eliminar"><i class="ri-delete-bin-line"></i></button>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="timeline-body">
                                                    <p class="mb-1"><strong>Realizado por:</strong> <?php echo htmlspecialchars($record['admin_name']); ?></p>
                                                    <p class="mb-1"><strong>Descripción:</strong> <?php echo nl2br(htmlspecialchars($record['action_description'])); ?></p>
                                                    <?php if (!empty($record['action_result'])): ?>
                                                        <p class="mb-1"><strong>Resultado:</strong> <?php echo nl2br(htmlspecialchars($record['action_result'])); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="timeline-footer mt-2">
                                                    <small class="text-muted"><i class="ri-time-line"></i> <?php echo htmlspecialchars($record['action_datetime']); ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas eliminar este registro de seguimiento? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <form id="deleteForm" method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteId">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        list-style: none;
        padding: 20px 0 20px;
        position: relative;
    }

    .timeline:before {
        top: 0;
        bottom: 0;
        position: absolute;
        content: " ";
        width: 3px;
        background-color: #e5e5e5;
        left: 25px;
        margin-left: -1.5px;
        height: 90%;
    }

    .timeline > li {
        position: relative;
        margin-bottom: 20px;
    }

    .timeline > li:before,
    .timeline > li:after {
        content: " ";
        display: table;
    }

    .timeline > li:after {
        clear: both;
    }

    .timeline > li > .timeline-panel {
        float: left;
        width: calc(100% - 70px);
        margin-left: 70px;
        padding: 10px;
        border-radius: 8px;
    }
    
    .timeline-badge {
        color: #fff;
        width: 50px;
        height: 50px;
        line-height: 50px;
        font-size: 1.2em;
        text-align: center;
        position: absolute;
        top: 60px !important;
        left: 0;
        margin-left: -7px !important;
        background-color: #837aff;
        border-radius: 50%;
        border: 3px solid #fff;
        z-index: 100;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .timeline-badge i {
        font-size: 1.5rem;
    }
</style>

<script>
    function confirmDelete(id) {
        const modal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
        document.getElementById('deleteId').value = id;
        modal.show();
    }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
