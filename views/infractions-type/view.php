<?php
session_start();
require_once __DIR__ . '/../../controllers/InfractionTypesController.php';

$infractionTypesController = new InfractionTypesController();
$page_title = 'Ver Tipo de Infracción';

$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'No se especificó un tipo de infracción para ver.'
    ];
    header('Location: index.php');
    exit;
}

$infractionType = $infractionTypesController->getById($id);

if (!$infractionType) {
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'Tipo de infracción no encontrado.'
    ];
    header('Location: index.php');
    exit;
}

$page_title = "Detalles: " . $infractionType['infraction_type_name'];

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
                        <li class="breadcrumb-item"><a href="index.php">Tipos de Infracción</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detalles del Tipo de Infracción</li>
                    </ol>
                </nav>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="font-size: 2rem;font-weight: 600;">
                            <i class="ri-file-list-line me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <div class="btn-group" role="group">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Volver al listado
                            </a>
                            <a href="edit.php?id=<?php echo $infractionType['infraction_type_id']; ?>" class="btn btn-warning">
                                <i class="ri-edit-line"></i> Editar
                            </a>
                            <button type="button" 
                                    class="btn btn-danger" 
                                    onclick="confirmDelete(<?php echo $infractionType['infraction_type_id']; ?>)">
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
                                            <td><?php echo htmlspecialchars($infractionType['infraction_type_id']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Nombre:</th>
                                            <td>
                                                <strong><?php echo htmlspecialchars($infractionType['infraction_type_name']); ?></strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Descripción:</th>
                                            <td><?php echo nl2br(htmlspecialchars($infractionType['description'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Artículo Violado:</th>
                                            <td><?php echo htmlspecialchars($infractionType['violated_article']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Multa Base ($):</th>
                                            <td><?php echo htmlspecialchars(number_format($infractionType['base_fine'], 2)); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
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
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación de Tipo de Infracción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar el tipo de infracción con ID: <strong id="infractionTypeId"></strong>?</p>
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
let deleteInfractionTypeId = null;

function confirmDelete(id) {
    deleteInfractionTypeId = id;
    document.getElementById('infractionTypeId').textContent = id;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteInfractionTypeId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'delete.php';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = deleteInfractionTypeId;
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
