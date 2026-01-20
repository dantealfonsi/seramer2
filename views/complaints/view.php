<?php
// Vista de detalles de una queja

session_start();

// Incluir los controladores necesarios
require_once __DIR__ . '/../../controllers/ComplaintsController.php';
require_once __DIR__ . '/../../controllers/ComplaintTrackingController.php';

$complaintsController = new ComplaintsController();
$trackingController = new ComplaintTrackingController();

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

// Usar el controlador para obtener los datos de la queja principal
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

// Obtener los registros de seguimiento para esta queja
$trackingResult = $trackingController->index($id);
$tracking_records = $trackingResult['tracking_records'] ?? [];

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
$allowed_tipes = [
    'Suggestion' => 'Sugerencia',
    'Claim' => 'Reclamo',
    'Question' => 'Pregunta'
];

// Manejar la solicitud de eliminación si se recibe (para registros de seguimiento)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_tracking') {
    // RBAC: Solo RRHH puede eliminar seguimiento
    if ($_SESSION['selected_department'] !== 'Recursos Humanos') {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'No tiene permisos para realizar esta acción.'];
        header("Location: view.php?id=" . $id);
        exit;
    }

    $delete_id = $_POST['id'];
    $deleteResult = $trackingController->delete($delete_id);
    if ($deleteResult['success']) {
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => $deleteResult['message']];
    } else {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => $deleteResult['message']];
    }
    header("Location: view.php?id=" . $id);
    exit;
}

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
                        <h5 class="card-title mb-0"  style="font-size: 2rem;font-weight: 600;">
                            <i class="ri-feedback-line me-1"  style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <div class="btn-group" role="group">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Volver al listado
                            </a>
                            <?php if ($_SESSION['selected_department'] === 'Recursos Humanos'): ?>
                                <a href="edit.php?id=<?php echo $complaint['complaint_id']; ?>" class="btn btn-warning">
                                    <i class="ri-edit-line"></i> Editar
                                </a>
                                <button type="button" 
                                        class="btn btn-danger" 
                                        onclick="confirmDeleteComplaint(<?php echo $complaint['complaint_id']; ?>)">
                                    <i class="ri-delete-bin-line"></i> Eliminar
                                </button>
                            <?php endif; ?>
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
                                                    <?php echo htmlspecialchars($allowed_tipes[$complaint['complaint_type']]); ?>
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
                                                    <?php echo htmlspecialchars($allowed_priority[$complaint['complaint_priority']]); ?>
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
                                                    'Closed' => 'dark',
                                                    'Cancelled' => 'danger'
                                                ];
                                                $color = $status_colors[$complaint['complaint_status']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $color; ?> fs-6">
                                                    <?php echo htmlspecialchars($allowed_status[$complaint['complaint_status']]); ?>
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

                        <hr class="my-4">

                        <!-- Historial de Seguimiento -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Historial de Seguimiento</h6>
                            <?php if ($_SESSION['selected_department'] === 'Recursos Humanos' || $_SESSION['selected_department'] === 'Fiscalizacion'): ?>
                            <a href="../complaint_tracking/create.php?complaint_id=<?php echo htmlspecialchars($complaint['complaint_id']); ?>" class="btn btn-primary">
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
                                                        <a href="../complaint_tracking/edit.php?id=<?php echo htmlspecialchars($record['tracking_id']); ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="ri-pencil-line"></i></a>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDeleteTrackingRecord(<?php echo htmlspecialchars($record['tracking_id']); ?>)" title="Eliminar"><i class="ri-delete-bin-line"></i></button>
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

<!-- Modal de Confirmación para eliminar registro de seguimiento -->
<div class="modal fade" id="deleteTrackingModal" tabindex="-1" aria-labelledby="deleteTrackingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTrackingModalLabel">Confirmar Eliminación de Registro de Seguimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas eliminar este registro de seguimiento? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <form id="deleteTrackingForm" method="POST">
                    <input type="hidden" name="action" value="delete_tracking">
                    <input type="hidden" name="id" id="deleteTrackingId">
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
    }

    .timeline-badge i {
        font-size: 1.5rem;
    }
</style>


<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script>
let deleteComplaintId = null;

function confirmDeleteComplaint(id) {
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

function confirmDeleteTrackingRecord(id) {
    const modal = new bootstrap.Modal(document.getElementById('deleteTrackingModal'));
    document.getElementById('deleteTrackingId').value = id;
    modal.show();
}
</script>
