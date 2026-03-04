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
                                    onclick="confirmDelete(<?php echo $inspector['inspector_id']; ?>, '<?php echo addslashes($inspector['full_name']); ?>', '<?php echo addslashes($inspector['inspector_code']); ?>')">
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

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script>
function confirmDelete(id, name, code) {
    Swal.fire({
        title: '¿Está seguro?',
        text: `¿Estás seguro que deseas eliminar al inspector "${name}" (Código: ${code})? Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff3e1d',
        cancelButtonColor: '#8592a3',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: 'btn btn-danger me-3',
            cancelButton: 'btn btn-outline-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'delete.php';
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;
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
}
</script>