<?php
// C:\xampp\htdocs\seramer2\views\inspections\view.php
// Vista de detalles de un reporte de inspección

session_start();

// Incluir los controladores necesarios
require_once __DIR__ . '/../../controllers/InspectionController.php';
require_once __DIR__ . '/../../controllers/InspectionTrackingController.php'; 
require_once __DIR__ . '/../../config/Database.php';

$inspectionReportsController = new InspectionController();
$trackingController = new InspectionTrackingController(); 

// Asume que la sesión ya maneja el ID del usuario actual.
$current_admin_user_id = $_SESSION['user_id'] ?? 1; 

// Obtener el ID del reporte
$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'No se especificó un reporte para ver.'
    ];
    header('Location: index.php');
    exit;
}

// -----------------------------------------------------------
// --- MANEJO DE POST PARA AÑADIR/ELIMINAR SEGUIMIENTO ---
// -----------------------------------------------------------

// 1. Manejo de Añadir Seguimiento (POST action_type)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_type'])) {
    
    $report_id_for_redirect = $_POST['report_id'] ?? $id; 
    
    $data = [
        'report_id' => $report_id_for_redirect, 
        'inspection_id' => $_POST['inspection_id'] ?? null,
        'admin_user_id' => $_POST['admin_user_id'] ?? null,
        'action_type' => $_POST['action_type'] ?? null,
        'action_description' => $_POST['action_description'] ?? null,
        'action_result' => $_POST['action_result'] ?? null,
        'current_status' => $_POST['current_status'] ?? null
    ];

    if ($data['inspection_id'] && $data['admin_user_id'] && $data['report_id']) {
        // La línea 49 estaba aquí, ahora dentro del if.
        $store_result = $trackingController->store($data); 

        if ($store_result['success']) {
            $message = 'Seguimiento añadido.';
            if ($store_result['status_changed']) {
                $message .= ' El estado de la inspección cambió a ' . htmlspecialchars($store_result['new_status']) . '.';
            }
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => $message];
        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Error al añadir seguimiento: ' . $store_result['message']];
        }
    } else {
         $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Faltan datos esenciales para el seguimiento.'];
    }

    // Redireccionar para evitar re-envío del formulario y actualizar la vista
    header('Location: view.php?id=' . $report_id_for_redirect); 
    exit;
}

// 2. Manejo de Eliminación de Seguimiento (POST _method=DELETE para Tracking)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_method'] ?? null) === 'DELETE' && isset($_POST['tracking_id'])) {
    
    $tracking_id = $_POST['tracking_id'];
    $report_id_for_redirect = $_POST['report_id'] ?? $id; 

    $delete_result = $trackingController->delete($tracking_id);

    if ($delete_result['success']) {
        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Registro de seguimiento eliminado exitosamente.'];
    } else {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Error al eliminar el registro de seguimiento.'];
    }

    // Redireccionar
    header('Location: view.php?id=' . $report_id_for_redirect); 
    exit;
}

// -----------------------------------------------------------
// --- OBTENER DATOS PRINCIPALES DE LA VISTA ---
// -----------------------------------------------------------

// Usar el controlador de Reportes para obtener los datos del Reporte principal
$result = $inspectionReportsController->view($id);

if (!$result['success']) {
    $_SESSION['flash_message'] = ['type' => 'error', 'message' => $result['message']];
    header('Location: index.php');
    exit;
}

// Extraer variables para la vista
$report = $result['report'];
$page_title = $result['page_title'];

// Obtener los registros de seguimiento (Línea de tiempo)
// **Se asume que scheduled_inspection_id ya está en $report**
$tracking_result = $trackingController->index($report['scheduled_inspection_id']); 

if (!$tracking_result['success']) {
    $tracking_records = [];
    // Opcional: Loguear el error si es crítico: error_log("Error al obtener tracking: " . $tracking_result['message']);
} else {
    $tracking_records = $tracking_result['tracking_records'];
}


// --- FUNCIONES AUXILIARES DE TRADUCCIÓN Y ESTILO ---

function translate_inspection_status($status) {
    $translations = [
        'Pending' => 'Pendiente',
        'In Progress' => 'En Progreso',
        'Completed' => 'Completada',
        'Cancelled' => 'Cancelada',
        'N/A' => 'N/A'
    ];
    return $translations[$status] ?? $status;
}

function translate_inspection_type($type) {
    $translations = [
        'Rutine' => 'Rutina',
        'New Stall' => 'Nuevo Puesto',
        'Complain' => 'Queja/Denuncia',
        'N/A' => 'N/A'
    ];
    return $translations[$type] ?? $type;
}

function get_status_class($status) {
    switch ($status) {
        case 'Pending':
            return 'ri-calendar-event-line text-warning';
        case 'In Progress':
            return 'ri-road-map-line text-info';
        case 'Completed':
            return 'ri-checkbox-circle-line text-success';
        case 'Cancelled':
            return 'ri-close-circle-line text-danger';
        default:
            return 'ri-edit-line text-secondary';
    }
}

// --- DEFINICIÓN DE OPCIONES DE ACCIÓN DINÁMICAS PARA EL MODAL ---
$current_status = $report['inspection_status'] ?? 'N/A';
$available_actions = [];

// Opción base: Nota simple (no fuerza un cambio de estado)
$available_actions['Simple Note'] = 'Nota Simple (Seguimiento sin cambio de estado)';

if ($current_status === 'Pending') {
    $available_actions['Schedule Update'] = 'Actualización de Agenda (-> En Progreso)';
    $available_actions['Field Visit'] = 'Visita de Campo (-> En Progreso)';
    $available_actions['Report Generation'] = 'Generación de Reporte (-> En Progreso)';
    
} elseif ($current_status === 'In Progress') {
    $available_actions['Completion'] = 'Finalización (-> Completada)';
    $available_actions['Cancellation'] = 'Cancelación (-> Cancelada)';
} 
// -----------------------------------------------------------


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
                        <li class="breadcrumb-item"><a href="index.php">Reportes de Inspección</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detalles de Reporte</li>
                    </ol>
                </nav>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title d-flex align-items-center mb-0" style="font-size: 1.4rem;font-weight: 600;">
                            <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="ri-file-search-line" style="color: #696cff; font-size: 1.5rem;"></i></div>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <div class="btn-group" role="group">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Volver al listado
                            </a>
                            <a href="edit.php?id=<?php echo $report['report_id']; ?>" class="btn btn-warning">
                                <i class="ri-edit-line"></i> Editar
                            </a>
                            <button type="button" 
                                    class="btn btn-danger" 
                                    onclick="confirmDelete(<?php echo $report['report_id']; ?>)">
                                <i class="ri-delete-bin-line"></i> Eliminar
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3"><i class="ri-file-list-line"></i> Datos del Reporte</h6>
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr><th width="30%">ID del Reporte:</th><td><?php echo htmlspecialchars($report['report_id']); ?></td></tr>
                                        <tr><th>Fecha y Hora:</th><td><?php echo htmlspecialchars($report['creation_date']); ?></td></tr>
                                        <tr><th>Puesto:</th><td><a href="/marketstalls/view.php?id=<?php echo htmlspecialchars($report['stall_id']); ?>"><strong><?php echo htmlspecialchars($report['stall_code']); ?></strong></a></td></tr>
                                        <tr><th>Adjudicatario:</th><td><a href="/awardees/view.php?id=<?php echo htmlspecialchars($report['awardee_id']); ?>"><strong><?php echo htmlspecialchars($report['awardee_name']); ?></strong></a></td></tr>
                                        <tr><th>Inspector Principal:</th><td><span class="badge bg-primary fs-6"><?php echo htmlspecialchars($report['main_inspector_name']); ?></span></td></tr>
                                        <tr><th>Inspector Auxiliar:</th><td>
                                            <?php if (!empty($report['assistant_inspector_name'])): ?>
                                                <span class="badge bg-secondary fs-6"><?php echo htmlspecialchars($report['assistant_inspector_name']); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">No asignado</span>
                                            <?php endif; ?>
                                        </td></tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="text-success mb-3"><i class="ri-calendar-check-line"></i> Detalles de la Cita Programada</h6>
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr><th width="30%">Fecha Programada:</th><td><?php echo htmlspecialchars($report['scheduled_date'] ?? 'N/A'); ?></td></tr>
                                        <tr><th>Tipo de Inspección:</th><td><?php echo htmlspecialchars(translate_inspection_type($report['inspection_type'] ?? 'N/A')); ?></td></tr>
                                        <tr><th>Estado de la Cita:</th><td>
                                            <?php
                                            $status_class = '';
                                            $status_en = $report['inspection_status'] ?? 'N/A';
                                            $status_es = translate_inspection_status($status_en);
                                            switch ($status_en) {
                                                case 'Pending': $status_class = 'bg-warning text-dark'; break;
                                                case 'In Progress': $status_class = 'bg-info'; break;
                                                case 'Completed': $status_class = 'bg-success'; break;
                                                case 'Cancelled': $status_class = 'bg-danger'; break;
                                                default: $status_class = 'bg-secondary';
                                            }
                                            ?>
                                            <span class="badge <?php echo $status_class; ?> fs-6"><?php echo htmlspecialchars($status_es); ?></span>
                                        </td></tr>
                                        <tr><th>Observaciones:</th><td><?php echo nl2br(htmlspecialchars($report['observations'] ?? 'N/A')); ?></td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card bg-light border-0 mb-4">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted"><i class="ri-file-text-line"></i> Observaciones Generales del Reporte</h6>
                                        <p class="card-text"><?php echo nl2br(htmlspecialchars($report['general_observations'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="mb-0 text-secondary"><i class="ri-time-line me-1"></i> Historial de Seguimiento (Línea de Tiempo)</h5>
                                    
                                    <?php 
                                    $is_final = ($report['inspection_status'] === 'Completed' || $report['inspection_status'] === 'Cancelled');
                                    ?>
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addTrackingModal"
                                        <?php echo $is_final ? 'disabled title="El estado es final y no permite más seguimiento."' : ''; ?>
                                    >
                                        <i class="ri-add-line"></i> Añadir Seguimiento
                                    </button>
                                </div>
                                
                                <?php if (!empty($tracking_records)): ?>
                                <div class="timeline">
                                    <?php foreach ($tracking_records as $record): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-dot">
                                            <i class="<?php echo get_status_class($record['status_new']); ?>"></i> 
                                        </div>
                                        <div class="timeline-content card mb-3">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <h6 class="mb-1 text-primary">
                                                        Realizado por: <?php echo htmlspecialchars($record['admin_name']); ?>
                                                        <span class="badge bg-secondary ms-2"><?php echo translate_inspection_status($record['status_new']); ?></span>
                                                    </h6>
                                                    <small class="text-muted">
                                                        <?php echo date('Y-m-d H:i:s', strtotime($record['action_datetime'])); ?>
                                                    </small>
                                                </div>
                                                
                                                <?php
                                                // AJUSTE CRÍTICO: Separar la descripción original del resultado concatenado
                                                // La clave del array es 'update_description' ya que es el nombre de la columna real.
                                                $full_description = $record['update_description'] ?? ''; 
                                                $description_parts = explode("\n--- Resultado: ", $full_description, 2);
                                                $description = $description_parts[0];
                                                $result = $description_parts[1] ?? '';
                                                ?>
                                                
                                                <p class="mb-1 mt-1">
                                                    <strong>Descripción:</strong> <?php echo nl2br(htmlspecialchars($description)); ?><br>
                                                    
                                                    <?php if (!empty($result)): ?>
                                                    <strong class="mt-2 d-block">Resultado/Notas:</strong> <?php echo nl2br(htmlspecialchars($result)); ?>
                                                    <?php endif; ?>
                                                </p>
                                                
                                                <div class="mt-2 text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="confirmDeleteTracking(<?php echo $record['tracking_id']; ?>)">
                                                        <i class="ri-delete-bin-line"></i> Eliminar
                                                    </button>
                                                </div>
                                                
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-info">
                                    No se encontraron registros de seguimiento para esta inspección.
                                </div>
                                <?php endif; ?>
                                
                            </div>
                        </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addTrackingModal" tabindex="-1" aria-labelledby="addTrackingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTrackingModalLabel">Añadir Nuevo Seguimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="view.php?id=<?php echo htmlspecialchars($id); ?>" method="POST"> 
                <div class="modal-body">
                    <input type="hidden" name="inspection_id" value="<?php echo htmlspecialchars($report['scheduled_inspection_id']); ?>">
                    <input type="hidden" name="report_id" value="<?php echo htmlspecialchars($id); ?>"> 
                    <input type="hidden" name="current_status" value="<?php echo htmlspecialchars($report['inspection_status']); ?>">
                    <input type="hidden" name="admin_user_id" value="<?php echo htmlspecialchars($current_admin_user_id); ?>">
                    
                    <div class="mb-3">
                        <label for="action_type" class="form-label">Tipo de Acción (Define el posible cambio de Estado)</label>
                        <select class="form-select" id="action_type" name="action_type" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($available_actions as $value => $label): ?>
                                <option value="<?php echo htmlspecialchars($value); ?>">
                                    <?php echo htmlspecialchars($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="action_description" class="form-label">Descripción de la Acción</label>
                        <textarea class="form-control" id="action_description" name="action_description" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="action_result" class="form-label">Resultado / Notas Adicionales (Resultado)</label>
                        <textarea class="form-control" id="action_result" name="action_result" rows="2"></textarea>
                        <small class="form-text text-muted">Aquí puedes describir el resultado del paso (e.g., documentos solicitados). **Se guardará junto con la descripción**.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Seguimiento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación de Reporte</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar el reporte con ID: <strong id="reportId"></strong>?</p>
                <p class="text-danger"><small>Esta acción no se puede deshacer y eliminará el registro de forma permanente.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteTrackingModal" tabindex="-1" aria-labelledby="deleteTrackingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTrackingModalLabel">Confirmar Eliminación de Seguimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteTrackingForm" action="view.php?id=<?php echo htmlspecialchars($id); ?>" method="POST">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="tracking_id" id="trackingIdInput">
                <input type="hidden" name="report_id" value="<?php echo htmlspecialchars($id); ?>">
                <div class="modal-body">
                    <p>¿Está seguro que desea eliminar el registro de seguimiento?</p>
                    <p class="text-danger"><small>Esta acción es permanente y no afecta el estado de la inspección.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php include __DIR__ . '/../layouts/footer.php'; ?>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
    height: auto; 
}
.timeline::before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    left: 10px; 
    width: 2px;
    background-color: #dee2e6;
}
.timeline-item {
    position: relative;
    margin-bottom: 20px;
}
.timeline-dot {
    position: absolute;
    left: -20px; 
    top: 60px;
    width: 40px;
    height: 40px;
    background-color: #fff;
    border: 2px solid #dee2e6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1;
}
.timeline-dot i {
    font-size: 18px;
}
.timeline-content {
    margin-left: 30px;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
}
.timeline-dot .text-info { border-color: #0dcaf0; }
.timeline-dot .text-warning { border-color: #ffc107; }
.timeline-dot .text-success { border-color: #198754; }
.timeline-dot .text-primary { border-color: #0d6efd; }
.timeline-dot .text-danger { border-color: #dc3545; }
.timeline-dot .text-secondary { border-color: #6c757d; }
</style>

<script>
let deleteReportId = null;

// Lógica para eliminar Reporte Principal 
function confirmDelete(id) {
    deleteReportId = id;
    document.getElementById('reportId').textContent = id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteReportId) {
        // Lógica para enviar el formulario DELETE del reporte principal
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'delete.php'; 
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = deleteReportId;
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


// Lógica para eliminar Registro de Seguimiento
function confirmDeleteTracking(id) {
    // Seteamos el ID en el campo oculto del formulario dentro del modal
    document.getElementById('trackingIdInput').value = id; 
    
    const modal = new bootstrap.Modal(document.getElementById('deleteTrackingModal'));
    modal.show();
}
</script>