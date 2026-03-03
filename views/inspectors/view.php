<?php
// Vista de detalles de un inspector

session_start();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/InspectorsController.php';

$inspectorsController = new InspectorsController();

// Obtener el ID del inspector
$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'No se especificó un inspector para ver.'
    ];
    header('Location: index.php');
    exit;
}

// Usar el controlador para obtener los datos
$result = $inspectorsController->view($id);

// Si hay error o no se encuentra el inspector, redirigir
if (!$result['success']) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => $result['message']
    ];
    header('Location: index.php');
    exit;
}

// Extraer variables para la vista
$inspector = $result['inspector'];
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
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="card-title d-flex align-items-center mb-1" style="font-size: 1.4rem;font-weight: 600;">
                                <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="ri-user-line" style="color: #696cff; font-size: 1.5rem;"></i></div>
                                <?php echo htmlspecialchars($page_title); ?>
                            </h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="index.php">Inspectores</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Detalles de Inspector</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="btn-group" role="group">
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="ri-arrow-left-line"></i> Volver al listado
                            </a>
                            <a href="edit.php?id=<?php echo $inspector['inspector_id']; ?>" class="btn btn-warning">
                                <i class="ri-edit-2-line"></i> Editar
                            </a>
                            <button type="button" 
                                    class="btn btn-outline-danger" 
                                    onclick="confirmDelete(<?php echo $inspector['inspector_id']; ?>)">
                                <i class="ri-delete-bin-line"></i> Eliminar
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <th width="20%">ID del Inspector:</th>
                                            <td><?php echo htmlspecialchars($inspector['inspector_id']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Código:</th>
                                            <td>
                                                <span class="badge bg-primary fs-6">
                                                    <?php echo htmlspecialchars($inspector['inspector_code']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Nombre Completo:</th>
                                            <td><strong><?php echo htmlspecialchars($inspector['full_name']); ?></strong></td>
                                        </tr>
                                            <th>Teléfono:</th>
                                            <td><?php echo htmlspecialchars($inspector['phone_number'] ?? 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td><?php echo htmlspecialchars($inspector['email'] ?? 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Fecha de Contratación:</th>
                                            <td><?php echo htmlspecialchars($inspector['hire_date']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Estado:</th>
                                            <td>
                                                <?php
                                                $status_badge = ($inspector['is_active'] == 1) ? 'success' : 'danger';
                                                $status_text = ($inspector['is_active'] == 1) ? 'Activo' : 'Inactivo';
                                                ?>
                                                <span class="badge bg-<?php echo $status_badge; ?> fs-6">
                                                    <?php echo htmlspecialchars($status_text); ?>
                                                </span>
                                            </td>
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
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación de Inspector</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar al inspector con ID: <strong id="inspectorId"></strong>?</p>
                <p class="text-danger"><small>Esta acción no se puede deshacer y lo eliminará de forma permanente.</small></p>
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
let deleteInspectorId = null;

function confirmDelete(id) {
    deleteInspectorId = id;
    document.getElementById('inspectorId').textContent = id;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteInspectorId) {
        // Crear formulario para enviar la solicitud de eliminación
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'delete.php';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = deleteInspectorId;
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