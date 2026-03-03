<?php
// Vista de detalles de una sanción

session_start();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/SanctionsController.php';

$sanctionsController = new SanctionsController();

// Obtener el ID de la sanción
$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'No se especificó una sanción para ver.'
    ];
    header('Location: index.php');
    exit;
}

// Usar el controlador para obtener los datos
$result = $sanctionsController->view($id);

// Si hay error o no se encuentra la sanción, redirigir
if (!$result['success']) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => $result['message']
    ];
    header('Location: index.php');
    exit;
}

// Extraer variables para la vista
$sanction = $result['sanction'];
$page_title = $result['page_title'];

// Incluir header y layouts
require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';

$allowed_sanction_status = [
    'Imposed' => 'Impuesta',
    'Paid' => 'Pagada',
    'Pending' => 'Pendiente',
    'Canceled' => 'Cancelada'
];
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="card-title d-flex align-items-center mb-1" style="font-size: 1.4rem;font-weight: 600;">
                                <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="ri-forbid-2-line" style="color: #696cff; font-size: 1.5rem;"></i></div>
                                <?php echo htmlspecialchars($page_title); ?>
                            </h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="index.php">Sanciones</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Detalles de Sanción</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="btn-group" role="group">
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="ri-arrow-left-line"></i> Volver al listado
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <th width="40%">ID:</th>
                                            <td><?php echo htmlspecialchars($sanction['sanction_id']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Infracción:</th>
                                            <td>
                                                <span class="badge bg-primary fs-6">
                                                    <?php echo ucfirst(htmlspecialchars($sanction['infraction_type_name'] ?? 'Infracción')); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Tipo de Sanción:</th>
                                            <td>
                                                <span class="badge bg-info fs-6">
                                                    <?php echo ucfirst(htmlspecialchars($sanction['severity_name'])); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Monto de Multa:</th>
                                            <td><?php echo htmlspecialchars($sanction['fine_amount'] ?? 'N/A'); ?> <?php echo htmlspecialchars($sanction['fine_currency'] ?? ''); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Fecha de Imposición:</th>
                                            <td><?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($sanction['imposition_date']))); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Inicio de Efecto:</th>
                                            <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($sanction['effect_start_date']))); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Fin de Efecto:</th>
                                            <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($sanction['effect_end_date']))); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Estado:</th>
                                            <td>
                                                <?php
                                                    $status_colors = [
                                                        'Imposed' => 'warning',
                                                        'Paid' => 'success',
                                                        'Pending' => 'secondary',
                                                        'Canceled' => 'danger'
                                                    ];
                                                    $color = $status_colors[$sanction['sanction_status']] ?? 'btn-outline-secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $color; ?> fs-6">
                                                    <?php echo htmlspecialchars($allowed_sanction_status[$sanction['sanction_status']] ?? $sanction['sanction_status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>¿Es Reincidencia?:</th>
                                            <td>
                                                <?php if ($sanction['is_repeat_offense'] == 1) : ?>
                                                    <span class="badge bg-danger">Sí</span>
                                                <?php else : ?>
                                                    <span class="badge bg-success">No</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Impuesta por:</th>
                                            <td><?php echo htmlspecialchars($sanction['imposed_by_user_id']); // Reemplazar con el nombre de usuario si es posible ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted"><i class="ri-file-text-line"></i> Observaciones de la Sanción</h6>
                                        <p class="card-text"><?php echo nl2br(htmlspecialchars($sanction['sanction_observations'] ?? 'No hay observaciones.')); ?></p>
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
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación de Sanción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar la sanción con ID: <strong id="sanctionId"></strong>?</p>
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
let deleteSanctionId = null;

function confirmDelete(id) {
    deleteSanctionId = id;
    document.getElementById('sanctionId').textContent = id;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteSanctionId) {
        // Crear formulario para enviar la solicitud de eliminación
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'delete.php';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = deleteSanctionId;
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