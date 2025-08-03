<?php
// Verificar acceso - permitir RRHH y jefes de departamento
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
AuthMiddleware::requireUserManagementAccess();

// Incluir header y configuración
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../models/UserModel.php';

$userModel = new UserModel();

// Obtener parámetros de paginación y filtros
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;

// Verificar si el usuario actual es jefe de departamento
$is_manager = AuthMiddleware::isManager();
$is_rrhh = AuthMiddleware::hasAccessToDepartment('Recursos Humanos');

// Lógica de acceso y filtrado
if ($is_rrhh) {
    // RRHH ve todos los usuarios con filtro opcional
    $department_filter = isset($_GET['department']) ? $_GET['department'] : '';
    $users = $userModel->getAll($page, $limit, $department_filter);
    $total_users = $userModel->countUsers($department_filter);
    $departments = ['Recursos Humanos', 'Liquidacion', 'Fiscalizacion', 'Cobranza'];
    $page_title = 'Gestión de Usuarios - Vista Completa';
} else if ($is_manager) {
    // Jefe de departamento ve solo usuarios de su departamento
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $user_id = $_SESSION['user_id'];
    $users = $userModel->getUsersByManagerDepartment($user_id, $page, $limit);
    $total_users = $userModel->countUsersByManagerDepartment($user_id);
    $departments = [$is_manager['name']]; // Solo su departamento
    $department_filter = $is_manager['name'];
    $page_title = 'Gestión de Usuarios - Departamento: ' . $is_manager['name'];
} else {
    // No debería llegar aquí por el middleware, pero por seguridad
    header('Location: ../dashboard/dashboard.php');
    exit;
}

$total_pages = ceil($total_users / $limit);
?>
<?php include __DIR__ . '/../layouts/header.php'; ?>
<?php include __DIR__ . '/../layouts/navigation.php'; ?>
<?php include __DIR__ . '/../layouts/navigation-top.php'; ?>


    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><?php echo htmlspecialchars($page_title); ?></h5>
                            <?php if ($is_manager): ?>
                                <small class="text-muted">Vista limitada a usuarios del departamento: <?php echo htmlspecialchars($is_manager['name']); ?></small>
                            <?php endif; ?>
                        </div>
                        <?php if ($is_rrhh || $is_manager): ?>
                            <a href="create.php" class="btn btn-primary">
                                <i class="ri-add-line me-1"></i>Crear Usuario
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Filtros - Solo para RRHH -->
                    <div class="card-body">
                        <?php if ($is_rrhh): ?>
                            <form method="GET" class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Filtrar por Departamento</label>
                                    <select name="department" class="form-select">
                                        <option value="">Todos los departamentos</option>
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?php echo htmlspecialchars($dept); ?>" 
                                                    <?php echo ($department_filter == $dept) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($dept); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-outline-primary d-block">Filtrar</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="ri-information-line me-2"></i>
                                Mostrando solo usuarios del departamento: <strong><?php echo htmlspecialchars($is_manager['name']); ?></strong>
                            </div>
                        <?php endif; ?>

                        <!-- Tabla de usuarios -->
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Usuario</th>
                                        <th>Email</th>
                                        <th>Nombre Completo</th>
                                        <th>Departamento</th>
                                        <th>Estado</th>
                                        <th>Último Login</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($users)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No hay usuarios registrados</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <?php 
                                                    $full_name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                                                    echo htmlspecialchars($full_name ?: 'Sin información');
                                                    ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo htmlspecialchars($user['department_name'] ?? 'Sin asignar'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo ($user['status'] == 'active') ? 'bg-success' : 'bg-danger'; ?>">
                                                        <?php echo ($user['status'] == 'active') ? 'Activo' : 'Inactivo'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php 
                                                    if ($user['last_login']) {
                                                        echo date('d/m/Y H:i', strtotime($user['last_login']));
                                                    } else {
                                                        echo 'Nunca';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                                data-bs-toggle="dropdown">
                                                            Acciones
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <!-- Ver detalles - disponible para todos -->
                                                            <li>
                                                                <a class="dropdown-item" href="view.php?id=<?php echo $user['id']; ?>">
                                                                    <i class="ri-eye-line me-1"></i>Ver Detalles
                                                                </a>
                                                            </li>
                                                            
                                                            <!-- Editar - disponible para RRHH y jefes de departamento -->
                                                            <?php if ($is_rrhh || $is_manager): ?>
                                                                <li>
                                                                    <a class="dropdown-item" href="edit.php?id=<?php echo $user['id']; ?>">
                                                                        <i class="ri-edit-line me-1"></i>Editar
                                                                    </a>
                                                                </li>
                                                            <?php endif; ?>
                                                            
                                                            <!-- Funciones avanzadas solo para RRHH -->
                                                            <?php if ($is_rrhh): ?>
                                                                <li>
                                                                    <a class="dropdown-item" href="permissions.php?user_id=<?php echo $user['id']; ?>">
                                                                        <i class="ri-shield-user-line me-1"></i>Permisos
                                                                    </a>
                                                                </li>
                                                            <?php endif; ?>
                                                            
                                                            <!-- Activar/Desactivar - disponible para RRHH y jefes -->
                                                            <?php if ($is_rrhh || $is_manager): ?>
                                                                <li><hr class="dropdown-divider"></li>
                                                                <?php if ($user['status'] == 'active'): ?>
                                                                    <li>
                                                                        <a class="dropdown-item text-warning" 
                                                                           href="javascript:void(0);" 
                                                                           onclick="confirmDeactivate(<?php echo $user['id']; ?>)">
                                                                            <i class="ri-user-unfollow-line me-1"></i>Desactivar
                                                                        </a>
                                                                    </li>
                                                                <?php else: ?>
                                                                    <li>
                                                                        <a class="dropdown-item text-success" 
                                                                           href="javascript:void(0);" 
                                                                           onclick="confirmReactivate(<?php echo $user['id']; ?>)">
                                                                            <i class="ri-user-add-line me-1"></i>Reactivar
                                                                        </a>
                                                                    </li>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                            
                                                            <!-- Información para jefes de departamento -->
                                                            <?php if ($is_manager && !$is_rrhh): ?>
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <a class="dropdown-item text-muted disabled" href="javascript:void(0);">
                                                                        <i class="ri-information-line me-1"></i>Vista de Jefe de Depto.
                                                                    </a>
                                                                </li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Paginación de usuarios" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php 
                                    $pagination_params = $is_rrhh ? '&department=' . urlencode($department_filter) : '';
                                    ?>
                                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo ($page - 1); ?><?php echo $pagination_params; ?>">
                                            Anterior
                                        </a>
                                    </li>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $pagination_params; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo ($page + 1); ?><?php echo $pagination_params; ?>">
                                            Siguiente
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>


<script>
function confirmDelete(userId) {
    if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
        // Aquí iría la llamada AJAX para eliminar
        window.location.href = 'delete.php?id=' + userId;
    }
}

function confirmDeactivate(userId) {
    if (confirm('¿Estás seguro de que deseas desactivar este usuario?\n\nEl usuario no podrá acceder al sistema hasta que sea reactivado.')) {
        window.location.href = 'deactivate.php?id=' + userId;
    }
}

function confirmReactivate(userId) {
    if (confirm('¿Estás seguro de que deseas reactivar este usuario?\n\nEl usuario podrá acceder nuevamente al sistema.')) {
        window.location.href = 'reactivate.php?id=' + userId;
    }
}

function showManagerLimitationAlert() {
    alert('Como jefe de departamento, tienes acceso completo para gestionar los usuarios de tu departamento. Para gestionar usuarios de otros departamentos, contacta al departamento de Recursos Humanos.');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>