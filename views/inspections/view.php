<?php
// Vista de detalles de un reporte de inspección

session_start();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/InspectionController.php';

$inspectionReportsController = new InspectionController();

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

// Usar el controlador para obtener los datos
$result = $inspectionReportsController->view($id);

// Si hay error o no se encuentra el reporte, redirigir
if (!$result['success']) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => $result['message']
    ];
    header('Location: index.php');
    exit;
}

// Extraer variables para la vista
$report = $result['report'];
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
                        <li class="breadcrumb-item"><a href="index.php">Reportes de Inspección</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detalles de Reporte</li>
                    </ol>
                </nav>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="font-size: 2rem;font-weight: 600;">
                            <i class="ri-file-search-line me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
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
                                        <tr>
                                            <th width="30%">ID del Reporte:</th>
                                            <td><?php echo htmlspecialchars($report['report_id']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Fecha y Hora:</th>
                                            <td><?php echo htmlspecialchars($report['creation_date']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Puesto:</th>
                                            <td>
                                                <a href="/marketstalls/view.php?id=<?php echo htmlspecialchars($report['stall_id']); ?>">
                                                    <strong><?php echo htmlspecialchars($report['stall_code']); ?></strong>
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Adjudicatario:</th>
                                            <td>
                                                <a href="/awardees/view.php?id=<?php echo htmlspecialchars($report['awardee_id']); ?>">
                                                    <strong><?php echo htmlspecialchars($report['awardee_name']); ?></strong>
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Inspector Principal:</th>
                                            <td>
                                                <span class="badge bg-primary fs-6">
                                                    <?php echo htmlspecialchars($report['main_inspector_name']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Inspector Auxiliar:</th>
                                            <td>
                                                <?php if (!empty($report['assistant_inspector_name'])): ?>
                                                    <span class="badge bg-secondary fs-6">
                                                        <?php echo htmlspecialchars($report['assistant_inspector_name']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">No asignado</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="text-success mb-3"><i class="ri-calendar-check-line"></i> Detalles de la Cita Programada</h6>
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <th width="30%">Fecha Programada:</th>
                                            <td><?php echo htmlspecialchars($report['scheduled_date'] ?? 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Tipo de Inspección:</th>
                                            <td><?php echo htmlspecialchars($report['inspection_type'] ?? 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Estado de la Cita:</th>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                switch ($report['inspection_status']) {
                                                    case 'Pending':
                                                        $status_class = 'bg-warning text-dark';
                                                        break;
                                                    case 'In Progress':
                                                        $status_class = 'bg-info';
                                                        break;
                                                    case 'Completed':
                                                        $status_class = 'bg-success';
                                                        break;
                                                    case 'Cancelled':
                                                        $status_class = 'bg-danger';
                                                        break;
                                                    default:
                                                        $status_class = 'bg-secondary';
                                                }
                                                ?>
                                                <span class="badge <?php echo $status_class; ?> fs-6">
                                                    <?php echo htmlspecialchars($report['inspection_status'] ?? 'N/A'); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Observaciones:</th>
                                            <td><?php echo nl2br(htmlspecialchars($report['observations'] ?? 'N/A')); ?></td>
                                        </tr>
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
                            <div class="col-md-6">
                                <div class="card bg-light border-0 text-center">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted"><i class="ri-pencil-line"></i> Firma del Inspector Principal</h6>
                                        <?php if (!empty($report['inspector_signature_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($report['inspector_signature_url']); ?>" alt="Firma del Inspector" class="img-fluid border rounded" style="max-height: 150px;">
                                        <?php else: ?>
                                            <p class="text-muted mb-0">No hay firma.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light border-0 text-center">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted"><i class="ri-pencil-line"></i> Firma del Inspector Auxiliar</h6>
                                        <?php if (!empty($report['assistant_signature_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($report['assistant_signature_url']); ?>" alt="Firma del Inspector Auxiliar" class="img-fluid border rounded" style="max-height: 150px;">
                                        <?php else: ?>
                                            <p class="text-muted mb-0">No hay firma.</p>
                                        <?php endif; ?>
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

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script>
let deleteReportId = null;

function confirmDelete(id) {
    deleteReportId = id;
    document.getElementById('reportId').textContent = id;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteReportId) {
        // Crear formulario para enviar la solicitud de eliminación
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
</script>