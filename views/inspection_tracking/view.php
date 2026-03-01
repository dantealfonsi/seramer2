<?php
session_start();
require_once __DIR__ . '/../../controllers/InspectionTrackingController.php';
require_once __DIR__ . '/../../controllers/InspectionsController.php';

$trackingController = new InspectionTrackingController();
$inspectionsController = new InspectionsController(); // Asumiendo que tienes un controlador principal
$inspection_id = $_GET['id'] ?? null;
$result = null;

if (!$inspection_id) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'ID de inspección no proporcionado.'];
    header("Location: ../inspections/index.php"); // Redirige a la lista de inspecciones
    exit;
}

// 1. Obtener la información de la inspección y su historial de seguimiento
$result = $trackingController->index($inspection_id);

if (!$result['success']) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => $result['message']];
    header("Location: ../inspections/index.php");
    exit;
}

$tracking_records = $result['tracking_records'];
$inspection = $result['inspection'];
$page_title = $result['page_title'];

// 2. Manejar la solicitud de eliminación (DELETE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $delete_id = $_POST['id'];
    $deleteResult = $trackingController->delete($delete_id);
    if ($deleteResult['success']) {
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => $deleteResult['message']];
    } else {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => $deleteResult['message']];
    }
    // Redireccionar para evitar reenvío del formulario
    header("Location: view.php?id=" . $inspection_id);
    exit;
}

// Definiciones para mostrar en la vista (deberían venir de un modelo/config)
$allowed_status = [
    'Scheduled' => 'Programada',
    'In Progress' => 'En Progreso',
    'Completed' => 'Completada',
    'Cancelled' => 'Cancelada'
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
                        <h5 class="card-title d-flex align-items-center" style="font-size: 1.4rem;font-weight: 600;">
                            <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="ri-eye-line" style="color: #696cff; font-size: 1.5rem;"></i></div>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="../inspections/index.php" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i> Volver a Inspecciones
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="card bg-light mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Detalles de la Inspección</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-2"><strong>ID de Inspección:</strong> <?php echo htmlspecialchars($inspection['inspection_id']); ?></p>
                                <p class="mb-2"><strong>Descripción:</strong> <?php echo nl2br(htmlspecialchars($inspection['description'])); ?></p>
                                <p class="mb-2"><strong>Fecha Programada:</strong> <?php echo htmlspecialchars($inspection['scheduled_date']); ?></p>
                                <p class="mb-2"><strong>Estado:</strong> <span class="badge <?php 
                                    if ($inspection['status'] === 'Scheduled') echo 'bg-info';
                                    if ($inspection['status'] === 'In Progress') echo 'bg-primary';
                                    if ($inspection['status'] === 'Completed') echo 'bg-success';
                                    if ($inspection['status'] === 'Cancelled') echo 'bg-danger';
                                ?>">
                                    <?php echo htmlspecialchars($allowed_status[$inspection['status']]); ?>
                                </span></p>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Historial de Seguimiento</h6>
                            <a href="create.php?inspection_id=<?php echo htmlspecialchars($inspection_id); ?>" class="btn btn-primary">
                                <i class="ri-add-line"></i> Añadir Registro
                            </a>
                        </div>
                        
                        <?php if (empty($tracking_records)): ?>
                            <div class="alert alert-info">No hay registros de seguimiento para esta inspección.</div>
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
                                                    <div class="btn-group">
                                                        <a href="edit.php?id=<?php echo htmlspecialchars($record['tracking_id']); ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="ri-pencil-line"></i></a>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo htmlspecialchars($record['tracking_id']); ?>)" title="Eliminar"><i class="ri-delete-bin-line"></i></button>
                                                    </div>
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
    /* ... (Mismo CSS de Timeline que ya proporcionaste) ... */
</style>

<script>
    function confirmDelete(id) {
        const modal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
        document.getElementById('deleteId').value = id;
        modal.show();
    }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>