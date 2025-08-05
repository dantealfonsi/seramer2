<?php
// Vista de detalles de una infracción

session_start();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/InfractionsController.php';

$infractionsController = new InfractionsController();

// Obtener el ID de la infracción
$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'No se especificó una infracción para ver.'
    ];
    header('Location: index.php');
    exit;
}

// Usar el controlador para obtener los datos
$result = $infractionsController->view($id);

// Si hay error o no se encuentra la infracción, redirigir
if (!$result['success']) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => $result['message']
    ];
    header('Location: index.php');
    exit;
}

// Extraer variables para la vista
$infraction = $result['infraction'];
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
                        <li class="breadcrumb-item"><a href="index.php">Infracciones</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detalles de Infracción</li>
                    </ol>
                </nav>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="font-size: 2rem;font-weight: 600;">
                            <i class="ri-alert-line me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <div class="btn-group" role="group">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Volver al listado
                            </a>
                            <a href="edit.php?id=<?php echo $infraction['id_infraction']; ?>" class="btn btn-warning">
                                <i class="ri-edit-line"></i> Editar
                            </a>
                            <button type="button" 
                                class="btn btn-danger" 
                                onclick="confirmDelete(<?php echo $infraction['id_infraction']; ?>)">
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
                                            <td><?php echo htmlspecialchars($infraction['id_infraction']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Adjudicatario:</th>
                                            <td>
                                                <a href="adjudicataries/view.php?id=<?php echo $infraction['id_adjudicatory']; ?>">
                                                    <strong><?php echo htmlspecialchars($infraction['adjudicatory_name']); ?></strong>
                                                </a>
                                                (<?php echo htmlspecialchars($infraction['adjudicatory_document']); ?>)
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Puesto:</th>
                                            <td>
                                                <span class="badge bg-secondary fs-6">
                                                    <?php echo htmlspecialchars($infraction['stall_code'] ?? 'N/A'); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Tipo de Infracción:</th>
                                            <td>
                                                <span class="badge bg-info fs-6">
                                                    <?php echo htmlspecialchars($infraction['infraction_type_name']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Fecha y Hora:</th>
                                            <td>
                                                <?php 
                                                $infraction_datetime = new DateTime($infraction['infraction_datetime']);
                                                echo htmlspecialchars($infraction_datetime->format('d/m/Y H:i A')); 
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Estado:</th>
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
                                                <span class="badge bg-<?php echo $color; ?> fs-6">
                                                    <?php echo htmlspecialchars($infraction['infraction_status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted"><i class="ri-file-text-line"></i> Descripción</h6>
                                        <p class="card-text"><?php echo nl2br(htmlspecialchars($infraction['infraction_description'])); ?></p>
                                    </div>
                                </div>
                                <div class="card bg-light border-0 mt-3">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted"><i class="ri-file-edit-line"></i> Observaciones del Inspector</h6>
                                        <p class="card-text"><?php echo nl2br(htmlspecialchars($infraction['inspector_observations'] ?? 'No hay observaciones.')); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <div class="row">
                                <div class="col-12">
                                    <h5><i class="ri-image-line"></i> Evidencia</h5>
                                    <?php if (!empty($infraction['proof'])): ?>
                                        <a href="<?php echo htmlspecialchars('../../public/uploads/infractions/' . $infraction['proof']); ?>" target="_blank">
                                            <img src="<?php echo htmlspecialchars('../../public/uploads/infractions/' . $infraction['proof']); ?>" 
                                                 alt="Evidencia de la infracción" 
                                                 class="img-fluid rounded shadow-sm" 
                                                 style="max-height: 400px; object-fit: cover;">
                                        </a>
                                    <?php else: ?>
                                        <div class="alert alert-warning text-center">
                                            No se ha adjuntado ninguna evidencia para esta infracción.
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