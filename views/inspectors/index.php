<?php
// Vista de listado de inspectores

session_start();

// Incluir el controlador necesario
require_once __DIR__ . '/../../controllers/InspectorsController.php';

$inspectorsController = new InspectorsController();
$result = $inspectorsController->index();

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'DELETE') {
    $deleteId = $_POST['id'];
    $deleteResult = $inspectorsController->delete($deleteId);

    $_SESSION['flash_message'] = [
        'type' => $deleteResult['success'] ? 'success' : 'danger',
        'message' => $deleteResult['message']
    ];

    header("Location: index.php");
    exit;    
}
// Si no se pudo obtener la lista, manejar el error
if (!$result['success']) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'No se pudo cargar la lista de inspectores. ' . ($result['message'] ?? '')
    ];
    // Opcional: Redirigir a una página de error o simplemente mostrar el mensaje
}

$inspectors = $result['inspectors'] ?? [];
$page_title = $result['page_title'] ?? 'Listado de Inspectores';

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
                        <li class="breadcrumb-item active" aria-current="page">Inspectores</li>
                    </ol>
                </nav>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="font-size: 2rem;font-weight: 600;">
                            <i class="ri-user-line me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri-add-line"></i> Crear Nuevo Inspector
                        </a>
                    </div>

                    <div class="card-body">
                        <?php
                        if (isset($_SESSION['flash_message'])) {
                            $alert_type = $_SESSION['flash_message']['type'] === 'success' ? 'success' : 'danger';
                            echo '<div class="alert alert-' . $alert_type . ' alert-dismissible fade show" role="alert">';
                            echo htmlspecialchars($_SESSION['flash_message']['message']);
                            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                            echo '</div>';
                            unset($_SESSION['flash_message']);
                        }
                        ?>

                        <?php if (empty($inspectors)): ?>
                            <div class="alert alert-info" role="alert">
                                No se encontraron inspectores.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Código</th>
                                            <th>Nombre Completo</th>
                                            <th>Email</th>
                                            <th>Teléfono</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($inspectors as $inspector): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($inspector['inspector_id']); ?></td>
                                                <td><?php echo htmlspecialchars($inspector['inspector_code']); ?></td>
                                                <td><?php echo htmlspecialchars($inspector['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars($inspector['email'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($inspector['phone_number'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <?php
                                                    $status_badge = ($inspector['is_active'] == 1) ? 'success' : 'danger';
                                                    $status_text = ($inspector['is_active'] == 1) ? 'Activo' : 'Inactivo';
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_badge; ?>">
                                                        <?php echo htmlspecialchars($status_text); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <a href="view.php?id=<?php echo htmlspecialchars($inspector['inspector_id']); ?>" class="btn btn-info btn-sm" title="Ver">
                                                            <i class="ri-eye-line"></i>
                                                        </a>
                                                        <a href="edit.php?id=<?php echo htmlspecialchars($inspector['inspector_id']); ?>" class="btn btn-warning btn-sm" title="Editar">
                                                            <i class="ri-edit-line"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-danger btn-sm" title="Eliminar" onclick="confirmDelete(<?php echo htmlspecialchars($inspector['inspector_id']); ?>)">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    </div>
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

<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación de Inspector</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar (desactivar) al inspector con ID: <strong id="inspectorId"></strong>?</p>
                <p class="text-danger"><small>Esta acción no elimina el registro de la base de datos, solo lo desactiva.</small></p>
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
        // Crear un formulario para enviar la solicitud de eliminación
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'index.php';
        
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