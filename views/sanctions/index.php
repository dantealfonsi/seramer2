<?php
// Vista de listado de sanciones

session_start();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/SanctionsController.php';

$sanctionsController = new SanctionsController();

// Usar el controlador para obtener los datos
$sanctions = $sanctionsController->index();

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
                <!-- Mensajes flash -->
                <?php if (isset($_SESSION['flash_message'])) : ?>
                    <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?> alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['flash_message']['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['flash_message']); ?>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="font-size: 2rem;font-weight: 600;">
                            <i class="ri-forbid-2-line me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            Listado de Sanciones
                        </h5>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri-add-line"></i> Nueva Sanción
                        </a>
                    </div>
                    
                    <div class="card-body">
                        <?php if (empty($sanctions)) : ?>
                            <div class="alert alert-info text-center" role="alert">
                                No se encontraron sanciones registradas.
                            </div>
                        <?php else : ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Infracción</th>
                                            <th>Tipo de Sanción</th>
                                            <th>Monto de Multa</th>
                                            <th>Fecha de Imposición</th>
                                            <th>Estado</th>
                                            <th class="text-end">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sanctions as $sanction) : ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($sanction['sanction_id']); ?></td>
                                                <td><?php echo htmlspecialchars($sanction['infraction_description']); ?></td>
                                                <td><?php echo htmlspecialchars($sanction['severity_name']); ?></td>
                                                <td><?php echo htmlspecialchars($sanction['fine_amount'] ?? 'N/A') . ' ' . htmlspecialchars($sanction['fine_currency'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($sanction['imposition_date']))); ?></td>
                                                <td>
                                                    <?php
                                                        $status_colors = [
                                                            'Imposed' => 'warning',
                                                            'Paid' => 'success',
                                                            'Pending' => 'secondary',
                                                            'Canceled' => 'danger'
                                                        ];
                                                        $color = $status_colors[$sanction['sanction_status']] ?? 'info';
                                                    ?>
                                                    <span class="badge bg-<?php echo $color; ?>"><?php echo htmlspecialchars($allowed_sanction_status[$sanction['sanction_status']]); ?></span>
                                                </td>
                                                <td class="text-end">
                                                    <a href="view.php?id=<?php echo $sanction['sanction_id']; ?>" class="btn btn-sm btn-info">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                    <a href="edit.php?id=<?php echo $sanction['sanction_id']; ?>" class="btn btn-sm btn-warning">
                                                        <i class="ri-edit-line"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $sanction['sanction_id']; ?>)">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación de eliminación -->
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