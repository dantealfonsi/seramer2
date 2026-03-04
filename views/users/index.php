<?php
// Ejemplo de cómo usar el UserController en la vista de listado

// Incluir el controlador
require_once __DIR__ . '/../../controllers/UserController.php';

$userController = new UserController();

// Preparar parámetros desde la petición
$params = [
    'department' => $_GET['department'] ?? '',
    'status' => $_GET['status'] ?? '',
    'role' => $_GET['role'] ?? '',
    'search' => $_GET['search'] ?? ''
];

// Usar el controlador para obtener los datos
$result = $userController->index($params);

// Si hay error de permisos o redirección, manejarla
if (!$result['success'] && isset($result['redirect'])) {
    header('Location: ' . $result['redirect']);
    exit;
}

// Extraer variables para la vista
$users = $result['users'];
$total_users = $result['total_users'];
$departments = $result['departments'];
$department_filter = $result['department_filter'];
$status_filter = $result['status_filter'] ?? '';
$role_filter = $result['role_filter'] ?? '';
$all_roles = $result['all_roles'] ?? [];
$page_title = $result['page_title'];
$is_manager = $result['is_manager'];
$is_rrhh = $result['is_rrhh'];

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
                            <h5 class="card-title mb-0 d-flex align-items-center" style="font-size: 1.4rem;font-weight: 600;">
                                <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;">
                                    <i class="ri-user-line" style="color: #696cff; font-size: 1.5rem;"></i>
                                </div>
                                <?php echo htmlspecialchars($page_title); ?>
                            </h5>
                        </div>
                        <div class="card-tools">
                            <a href="create.php" class="btn btn-primary">
                                <i class="ri-add-line mr-1"></i>
                                Nuevo Usuario
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="filter-card">
                            <div class="filter-card-title">
                                <i class="ri-filter-2-line"></i> Opciones de Filtrado Avanzado
                            </div>
                            <div class="filter-card-body">
                                <form action="index.php" method="GET">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label for="search" class="form-label small">Búsqueda Rápida</label>
                                            <input type="text" class="form-control" id="search" name="search" 
                                                placeholder="Nombre, Usuario..." 
                                                value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                        </div>

                                        <?php if ($is_rrhh || !empty($_SESSION['is_superadmin'])): ?>
                                        <div class="col-md-3">
                                            <label for="department" class="form-label small">Departamento</label>
                                            <select class="form-select" id="department" name="department">
                                                <option value="">-- Todos los Departamentos --</option>
                                                <?php foreach ($departments as $dept): ?>
                                                    <?php 
                                                        $d_id = is_array($dept) ? $dept['id'] : $dept;
                                                        $d_name = is_array($dept) ? $dept['name'] : $dept;
                                                    ?>
                                                    <option value="<?php echo htmlspecialchars($d_id); ?>" 
                                                            <?php echo ($department_filter == $d_id) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($d_name); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <?php endif; ?>

                                        <div class="col-md-3">
                                            <label for="status" class="form-label small">Estado</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="">-- Todos los Estados --</option>
                                                <option value="active" <?php echo ($status_filter === 'active') ? 'selected' : ''; ?>>Activo</option>
                                                <option value="inactive" <?php echo ($status_filter === 'inactive') ? 'selected' : ''; ?>>Inactivo</option>
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label for="role" class="form-label small">Rol Asignado</label>
                                            <select class="form-select" id="role" name="role">
                                                <option value="">-- Todos los Roles --</option>
                                                <?php foreach ($all_roles as $role): ?>
                                                    <option value="<?php echo $role['id']; ?>" 
                                                            <?php echo ($role_filter == $role['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($role['name'] . ($is_rrhh ? " ({$role['department_name']})" : "")); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-12 filter-card-actions">
                                            <a href="index.php" class="btn btn-filter-clear"><i class="ri-refresh-line me-1"></i> Limpiar</a>
                                            <button type="submit" class="btn btn-filter-apply"><i class="ri-search-line me-1"></i> Filtrar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Toast Notifications -->
                        <?php if (isset($_GET['success'])): ?>
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            let message = '';
                            switch ('<?php echo $_GET['success']; ?>') {
                                case 'user_created': message = 'Usuario creado exitosamente'; break;
                                case 'user_updated': message = 'Usuario actualizado exitosamente'; break;
                                case 'user_deactivated': message = 'Usuario desactivado exitosamente'; break;
                                case 'user_reactivated': message = 'Usuario reactivado exitosamente'; break;
                                default: message = 'Operación realizada exitosamente';
                            }
                            Swal.fire({
                                toast: true, position: 'top-end', icon: 'success', title: message,
                                showConfirmButton: false, timer: 4000, timerProgressBar: true, width: '450px'
                            });
                        });
                        </script>
                        <?php endif; ?>

                        <?php if (isset($_GET['error'])): ?>
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            let message = '';
                            switch ('<?php echo $_GET['error']; ?>') {
                                case 'user_not_found': message = 'Usuario no encontrado'; break;
                                case 'no_permission': message = 'No tiene permisos para realizar esta acción'; break;
                                case 'invalid_user': message = 'ID de usuario no válido'; break;
                                default: message = '<?php echo addslashes($_GET['error']); ?>';
                            }
                            Swal.fire({
                                toast: true, position: 'top-end', icon: 'error', title: message,
                                showConfirmButton: false, timer: 4000, timerProgressBar: true, width: '450px'
                            });
                        });
                        </script>
                        <?php endif; ?>



                        <!-- Tabla de usuarios -->
                        <?php if (empty($users)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No hay usuarios para mostrar</h5>
                                <p class="text-muted">
                                    <?php if ($is_manager && !$is_rrhh): ?>
                                        No hay usuarios en su departamento o no tiene permisos para verlos.
                                    <?php else: ?>
                                        No se encontraron usuarios con los filtros aplicados.
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php else: ?>
                            <div>
                                <table class="datatables-users table table-striped table-hover" id="usersTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Usuario</th>
                                            <th>Personal Asociado</th>
                                            <th>Departamento</th>
                                            <th>Rol(es)</th>
                                            <th>Email</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                                    <br>
                                                    <small class="text-muted">ID: <?php echo $user['id']; ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($user['staff_first_name']): ?>
                                                        <?php echo htmlspecialchars($user['staff_first_name'] . ' ' . $user['staff_last_name']); ?>
                                                        <br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($user['staff_job_position']); ?></small>
                                                    <?php else: ?>
                                                        <span class="text-muted">Sin personal asociado</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($user['department_name'] ?? 'N/A'); ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-label-info">
                                                        <?php 
                                                            if (!empty($user['is_superadmin'])) {
                                                                echo 'Superadmin';
                                                            } else {
                                                                echo htmlspecialchars($user['role_names'] ?? 'Sin Rol Fijo'); 
                                                            }
                                                        ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>">
                                                        <?php echo htmlspecialchars($user['email']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php if ($user['status'] === 'active'): ?>
                                                        <span class="badge text-bg-success">Activo</span>
                                                    <?php else: ?>
                                                        <span class="badge text-bg-danger">Inactivo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    
                                                        <a href="view.php?id=<?php echo $user['id']; ?>" 
                                                           class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                                            <i class="ri-eye-line"></i>
                                                        </a>
                                                        <?php 
                                                            $is_self = $user['id'] == $_SESSION['user_id'];
                                                            $is_target_admin = !empty($user['is_superadmin']) || (isset($user['role_names']) && strpos(strtolower($user['role_names']), 'admin') !== false);
                                                            // Un usuario no puede modificarse a sí mismo desde el listado (seguridad)
                                                            // Los administradores solo pueden ser modificados por superadmins
                                                            $can_modify = (!$is_self) && (!empty($_SESSION['is_superadmin']) || $is_rrhh || !$is_target_admin);
                                                        ?>
                                                        
                                                        <a href="<?php echo $can_modify ? 'edit.php?id=' . $user['id'] : 'javascript:void(0);'; ?>" 
                                                           class="btn btn-sm btn-outline-warning <?php echo !$can_modify ? 'disabled' : ''; ?>" 
                                                           title="<?php echo $can_modify ? 'Editar' : 'Usuario Protegido'; ?>"
                                                           <?php echo !$can_modify ? 'aria-disabled="true"' : ''; ?>>
                                                            <i class="ri-edit-line"></i>
                                                        </a>

                                                        <?php if ($user['status'] === 'active'): ?>
                                                            <a href="javascript:void(0);" 
                                                               class="btn btn-sm btn-outline-danger <?php echo !$can_modify ? 'disabled' : ''; ?>" 
                                                               title="<?php echo $can_modify ? 'Desactivar' : 'Usuario Protegido'; ?>"
                                                               <?php echo $can_modify ? 'onclick="confirmToggleStatus('.$user['id'].', \'deactivate\', \''.addslashes($user['username']).'\')"' : 'aria-disabled="true"'; ?>>
                                                                <i class="ri-user-unfollow-line"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="javascript:void(0);" 
                                                               class="btn btn-sm btn-outline-success <?php echo !$can_modify ? 'disabled' : ''; ?>" 
                                                               title="<?php echo $can_modify ? 'Reactivar' : 'Usuario Protegido'; ?>"
                                                               <?php echo $can_modify ? 'onclick="confirmToggleStatus('.$user['id'].', \'reactivate\', \''.addslashes($user['username']).'\')"' : 'aria-disabled="true"'; ?>>
                                                                <i class="ri-user-received-line"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<!-- DataTables Scripts -->
<script type="text/javascript" src="../../public/datatables/datatables.min.js"></script>
<script type="text/javascript" src="../../public/datatables/pdfmake.min.js"></script>
<script type="text/javascript" src="../../public/datatables/vfs_fonts.js"></script>
<link rel="stylesheet" type="text/css" href="../../public/datatables/datatables.min.css"/> 

<script>
$(document).ready(function() {
    if ($.fn.DataTable) {
        const table = $('#usersTable').DataTable({
            responsive: true,
            dom: '<"d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3"Bf>rtip',
            buttons: [
                {
                    extend: 'pdfHtml5',
                    text: '<i class="ri-file-pdf-line"></i> PDF',
                    className: 'btn btn-danger btn-sm me-1',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5]
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="ri-file-excel-line"></i> Excel',
                    className: 'btn btn-success btn-sm me-1',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5]
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="ri-printer-line"></i> Imprimir',
                    className: 'btn btn-info btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5]
                    }
                }
            ],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            pageLength: 10,
            order: []
        });

        // Si hay una búsqueda desde el backend (URL), pasarla a DataTables
        const searchInput = '<?php echo addslashes($params["search"] ?? ""); ?>';
        if (searchInput) {
            table.search(searchInput).draw();
        }
    } else {
        console.error("DataTables no cargado");
    }
});

function confirmToggleStatus(id, action, username) {
    const isDeactivate = action === 'deactivate';
    Swal.fire({
        title: isDeactivate ? '¿Desactivar usuario?' : '¿Reactivar usuario?',
        text: `¿Estás seguro de que deseas ${isDeactivate ? 'desactivar' : 'reactivar'} al usuario "${username}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: isDeactivate ? '#ff3e1d' : '#71dd37',
        cancelButtonColor: '#8592a3',
        confirmButtonText: isDeactivate ? 'Sí, desactivar' : 'Sí, reactivar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: `btn btn-${isDeactivate ? 'danger' : 'success'} me-3`,
            cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `${action}.php?id=${id}`;
        }
    });
}
</script>