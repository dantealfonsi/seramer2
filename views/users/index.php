<?php
// Ejemplo de cómo usar el UserController en la vista de listado

// Incluir el controlador
require_once __DIR__ . '/../../controllers/UserController.php';

$userController = new UserController();

// Preparar parámetros desde la petición
$params = [
    'page' => $_GET['page'] ?? 1,
    'department' => $_GET['department'] ?? ''
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
$total_pages = $result['total_pages'];
$current_page = $result['current_page'];
$departments = $result['departments'];
$department_filter = $result['department_filter'];
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
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title" style="font-size: 2rem;font-weight: 600;">
                            <i class="ri ri-user-line mr-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <div class="card-tools">
                            <a href="create.php" class="btn btn-primary btn-sm">
                                <i class="ri-add-line mr-1"></i>
                                Nuevo Usuario
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Filtros (solo para RRHH) -->
                        <?php if ($is_rrhh): ?>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <form method="GET" action="">
                                        <div class="input-group">
                                            <select name="department" class="form-control">
                                                <option value="">Todos los Departamentos</option>
                                                <?php foreach ($departments as $dept): ?>
                                                    <option value="<?php echo htmlspecialchars($dept); ?>" 
                                                            <?php echo ($department_filter === $dept) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($dept); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="submit">
                                                    <i class="fas fa-filter"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>

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

                        <!-- Estadísticas -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info">
                                        <i class="fas fa-users"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total de Usuarios: </span>
                                        <span class="info-box-number"><?php echo $total_users; ?></span>
                                        <?php if ($department_filter): ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

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
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Usuario</th>
                                            <th>Personal Asociado</th>
                                            <th>Departamento</th>
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
                                                           class="btn btn-text-info waves-effect btn-sm" title="Ver detalles">
                                                            <i class="ri ri-eye-line"></i>
                                                        </a>
                                                        <a href="edit.php?id=<?php echo $user['id']; ?>" 
                                                           class="btn btn-text-warning waves-effect btn-sm" title="Editar">
                                                            <i class="ri ri-edit-line"></i>
                                                        </a>
                                                        <?php if ($user['status'] === 'active'): ?>
                                                            <a href="deactivate.php?id=<?php echo $user['id']; ?>" 
                                                               class="btn btn-text-danger waves-effect btn-sm" title="Desactivar"
                                                               onclick="return confirm('¿Está seguro de desactivar este usuario?')">
                                                                <i class="ri ri-user-line"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="reactivate.php?id=<?php echo $user['id']; ?>" 
                                                               class="btn btn-text-success waves-effect btn-sm" title="Reactivar"
                                                               onclick="return confirm('¿Está seguro de reactivar este usuario?')">
                                                                <i class="ri ri-user-line"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginación -->
                            <?php if ($total_pages > 1): ?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <nav aria-label="Paginación de usuarios">
                                            <ul class="pagination justify-content-center">
                                                <!-- Botón anterior -->
                                                <?php if ($current_page > 1): ?>
                                                    <li class="page-item">
                                                        <a class="page-link" href="?page=<?php echo ($current_page - 1); ?><?php echo $department_filter ? '&department=' . urlencode($department_filter) : ''; ?>">
                                                            <i class="fas fa-chevron-left"></i>
                                                        </a>
                                                    </li>
                                                <?php endif; ?>

                                                <!-- Números de página -->
                                                <?php
                                                $start = max(1, $current_page - 2);
                                                $end = min($total_pages, $current_page + 2);
                                                
                                                for ($i = $start; $i <= $end; $i++):
                                                ?>
                                                    <li class="page-item <?php echo ($i === $current_page) ? 'active' : ''; ?>">
                                                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo $department_filter ? '&department=' . urlencode($department_filter) : ''; ?>">
                                                            <?php echo $i; ?>
                                                        </a>
                                                    </li>
                                                <?php endfor; ?>

                                                <!-- Botón siguiente -->
                                                <?php if ($current_page < $total_pages): ?>
                                                    <li class="page-item">
                                                        <a class="page-link" href="?page=<?php echo ($current_page + 1); ?><?php echo $department_filter ? '&department=' . urlencode($department_filter) : ''; ?>">
                                                            <i class="fas fa-chevron-right"></i>
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>