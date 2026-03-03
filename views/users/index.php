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

                        <!-- Mensajes de estado -->
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success alert-dismissible" role="alert">
                                <?php
                                switch ($_GET['success']) {
                                    case 'user_created':
                                        echo 'Usuario creado exitosamente';
                                        break;
                                    case 'user_updated':
                                        echo 'Usuario actualizado exitosamente';
                                        break;
                                    case 'user_deactivated':
                                        echo 'Usuario desactivado exitosamente';
                                        break;
                                    case 'user_reactivated':
                                        echo 'Usuario reactivado exitosamente';
                                        break;
                                    default:
                                        echo 'Operación realizada exitosamente';
                                }
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <?php
                                switch ($_GET['error']) {
                                    case 'user_not_found':
                                        echo 'Usuario no encontrado en la base de datos';
                                        break;
                                    case 'no_permission':
                                        echo 'No tiene permisos para realizar esta acción';
                                        break;
                                    case 'invalid_user':
                                        echo 'ID de usuario no válido';
                                        break;
                                    case 'invalid_user_id':
                                        echo 'El ID de usuario proporcionado no es válido o está vacío';
                                        break;
                                    case 'staff_data_missing':
                                        echo 'El usuario existe pero no tiene datos de personal asociados. Contacte al administrador.';
                                        break;
                                    default:
                                        echo htmlspecialchars($_GET['error']);
                                }
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
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
                                                            $is_target_admin = !empty($user['is_superadmin']) || (isset($user['role_names']) && strpos(strtolower($user['role_names']), 'admin') !== false);
                                                            $can_modify = !empty($_SESSION['is_superadmin']) || $is_rrhh || !$is_target_admin;
                                                        ?>
                                                        
                                                        <a href="<?php echo $can_modify ? 'edit.php?id=' . $user['id'] : 'javascript:void(0);'; ?>" 
                                                           class="btn btn-sm btn-outline-warning <?php echo !$can_modify ? 'disabled' : ''; ?>" 
                                                           title="<?php echo $can_modify ? 'Editar' : 'Usuario Protegido'; ?>"
                                                           <?php echo !$can_modify ? 'aria-disabled="true"' : ''; ?>>
                                                            <i class="ri-edit-line"></i>
                                                        </a>

                                                        <?php if ($user['status'] === 'active'): ?>
                                                            <a href="<?php echo $can_modify ? 'deactivate.php?id=' . $user['id'] : 'javascript:void(0);'; ?>" 
                                                               class="btn btn-sm btn-outline-danger <?php echo !$can_modify ? 'disabled' : ''; ?>" 
                                                               title="<?php echo $can_modify ? 'Desactivar' : 'Usuario Protegido'; ?>"
                                                               <?php echo $can_modify ? 'onclick="return confirm(\'¿Está seguro de desactivar este usuario?\')"' : 'aria-disabled="true"'; ?>>
                                                                <i class="ri-user-unfollow-line"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="<?php echo $can_modify ? 'reactivate.php?id=' . $user['id'] : 'javascript:void(0);'; ?>" 
                                                               class="btn btn-sm btn-outline-success <?php echo !$can_modify ? 'disabled' : ''; ?>" 
                                                               title="<?php echo $can_modify ? 'Reactivar' : 'Usuario Protegido'; ?>"
                                                               <?php echo $can_modify ? 'onclick="return confirm(\'¿Está seguro de reactivar este usuario?\')"' : 'aria-disabled="true"'; ?>>
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
</script>