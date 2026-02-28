<?php
require_once __DIR__ . '/../../controllers/UserController.php';

$userController = new UserController();
$user_id = $_GET['id'] ?? null;

$params = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $params = $_POST;
    $params['_method'] = 'POST';
}

$data = $userController->edit($user_id, $params);

if (!$data['success'] && isset($data['redirect'])) {
    header('Location: ' . $data['redirect']);
    exit;
}

$user = $data['user'];
$message = $data['message'];
$messageType = $data['messageType'];
$errors = $data['errors'];
$is_manager = $data['is_manager'];
$is_rrhh = $data['is_rrhh'];
$available_roles = $data['available_roles'] ?? [];
$all_departments = $data['all_departments'] ?? [];
$all_roles = $data['all_roles'] ?? [];

// Determine current user role if any
$current_role_id = null;
if (!empty($user['departments'])) {
    // Assuming we edit the role of the primary/first department the user has
    $current_role_id = $user['departments'][0]['role_id'] ?? null;
}

$page_title = 'Editar Usuario';
?>

<?php include __DIR__ . '/../layouts/header.php'; ?>
<?php include __DIR__ . '/../layouts/navigation.php'; ?>
<?php include __DIR__ . '/../layouts/navigation-top.php'; ?>

<div class="main-content">
    <div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0 d-flex align-items-center" style="font-size: 2rem;font-weight: 600;">
                            <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; background-color: #e7e7ff !important;">
                                <i class="ri-user-line" style="color: #696cff; font-size: 2rem;"></i>
                            </div>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <small class="text-muted">
                            Editando: <?php echo htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: $user['username']); ?>
                            (<?php echo htmlspecialchars($user['username']); ?>)
                        </small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="view.php?id=<?php echo $user['id']; ?>" class="btn btn-outline-info">
                            <i class="ri-eye-line me-1"></i>Ver Detalles
                        </a>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="ri-arrow-left-line me-1"></i>Volver
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Mensajes -->
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Información del Personal -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title mb-3" style="font-size: 2rem;font-weight: 600;">
                                        <i class="ri-user-line me-2"></i>Información del Personal
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Nombre Completo:</strong><br>
                                            <span><?php echo htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: $user['username']); ?></span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Cédula:</strong><br>
                                            <span><?php echo htmlspecialchars($user['id_number'] ?? 'N/A'); ?></span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Departamento:</strong><br>
                                            <span class="badge bg-info"><?php echo htmlspecialchars($user['department_name'] ?? 'N/A'); ?></span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Cargo:</strong><br>
                                            <span><?php echo htmlspecialchars($user['job_position_name'] ?? 'N/A'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario de edición -->
                    <form method="POST" class="row g-3">
                        <!-- Datos básicos del usuario -->
                        <div class="col-md-6">
                            <label for="username" class="form-label">
                                <strong>Nombre de Usuario</strong>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="username" id="username" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" 
                                   required minlength="3">
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">
                                <strong>Email</strong>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email" id="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" 
                                   required>
                        </div>

                        <!-- Estado del usuario -->
                        <div class="col-md-6">
                            <label for="status" class="form-label">
                                <strong>Estado</strong>
                                <span class="text-danger">*</span>
                            </label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="active" <?php echo ($user['status'] == 'active') ? 'selected' : ''; ?>>
                                    Activo
                                </option>
                                <option value="inactive" <?php echo ($user['status'] == 'inactive') ? 'selected' : ''; ?>>
                                    Inactivo
                                </option>
                            </select>
                        </div>

                        <!-- Selección de Departamentos y Roles -->
                        <div class="col-12 mt-4">
                            <h6 class="border-bottom pb-2">Asignación de Departamentos y Roles</h6>
                            <p class="text-muted small mb-3">Marque los departamentos a los que pertenecerá el usuario y seleccione el rol correspondiente.</p>
                            <div class="row">
                                <?php 
                                // Make sure we have the full list of departments to show
                                $all_depts = isset($all_departments) ? $all_departments : (isset($departments) ? $departments : []);
                                // Group roles by department id for easy access
                                $roles_by_dept = [];
                                if(isset($all_roles)) {
                                    foreach($all_roles as $r) {
                                        $roles_by_dept[$r['department_id']][] = $r;
                                    }
                                }
                                
                                // Map user's current departments for easier checking
                                $user_depts_map = [];
                                if (isset($user['departments'])) {
                                    foreach ($user['departments'] as $ud) {
                                        $user_depts_map[$ud['id']] = $ud['role_id'];
                                    }
                                }
                                
                                if(empty($all_depts)): ?>
                                    <div class="alert alert-warning">No hay departamentos disponibles.</div>
                                <?php else:
                                    foreach($all_depts as $dept):
                                        $dept_roles = $roles_by_dept[$dept['id']] ?? [];
                                        $has_dept = isset($user_depts_map[$dept['id']]);
                                        $current_role_in_dept = $has_dept ? $user_depts_map[$dept['id']] : null;
                                        
                                        // Only show departments that have available roles for this user to assign
                                        if (empty($dept_roles) && !$is_rrhh && !isset($_SESSION['is_superadmin']) && !$has_dept) continue; 
                                ?>
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card h-100 shadow-none border">
                                            <div class="card-body p-3">
                                                <div class="form-check fw-bold mb-2">
                                                    <input class="form-check-input dept-checkbox" type="checkbox" id="dept_<?php echo $dept['id']; ?>" <?php echo $has_dept ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="dept_<?php echo $dept['id']; ?>">
                                                        <?php echo htmlspecialchars($dept['name']); ?>
                                                    </label>
                                                </div>
                                                <select name="department_roles[<?php echo $dept['id']; ?>]" id="role_select_<?php echo $dept['id']; ?>" class="form-select form-select-sm role-select" <?php echo !$has_dept ? 'disabled' : ''; ?>>
                                                    <option value="">Seleccione un Rol...</option>
                                                    <?php foreach($dept_roles as $role): ?>
                                                        <option value="<?php echo $role['id']; ?>" <?php echo ($current_role_in_dept == $role['id']) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($role['name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                <?php 
                                    endforeach; 
                                endif; ?>
                            </div>
                        </div>
                        
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const deptCheckboxes = document.querySelectorAll('.dept-checkbox');
                            deptCheckboxes.forEach(cb => {
                                cb.addEventListener('change', function() {
                                    const deptId = this.id.split('_')[1];
                                    const roleSelect = document.getElementById('role_select_' + deptId);
                                    if (this.checked) {
                                        roleSelect.disabled = false;
                                        roleSelect.required = true;
                                    } else {
                                        roleSelect.disabled = true;
                                        roleSelect.required = false;
                                        roleSelect.value = '';
                                    }
                                });
                            });
                        });
                        </script>

                        <!-- Información adicional -->
                        <div class="col-md-6">
                            <label class="form-label"><strong>Último Login</strong></label>
                            <input type="text" class="form-control" 
                                   value="<?php echo $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Nunca'; ?>" 
                                   readonly>
                        </div>

                        <!-- Cambio de contraseña -->
                        <div class="col-12">
                            <hr>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="change_password" name="change_password" 
                                       onchange="togglePasswordFields()">
                                <label class="form-check-label" for="change_password">
                                    <strong>Cambiar contraseña</strong>
                                </label>
                            </div>
                        </div>

                        <div id="password_fields" style="display: none;" class="col-12">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label">
                                        <strong>Nueva Contraseña</strong>
                                    </label>
                                    <input type="password" name="password" id="password" class="form-control" 
                                           minlength="6" placeholder="Mínimo 6 caracteres">
                                </div>

                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label">
                                        <strong>Confirmar Nueva Contraseña</strong>
                                    </label>
                                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" 
                                           placeholder="Repetir la nueva contraseña">
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="col-12">
                            <hr>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line me-1"></i>Guardar Cambios
                                </button>
                                <a href="view.php?id=<?php echo $user['id']; ?>" class="btn btn-outline-info">
                                    <i class="ri-eye-line me-1"></i>Ver Detalles
                                </a>
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="ri-close-line me-1"></i>Cancelar
                                </a>
                                
                                <?php if ($user['status'] == 'active'): ?>
                                    <button type="button" class="btn btn-outline-warning ms-auto" 
                                            onclick="confirmDeactivate(<?php echo $user['id']; ?>)">
                                        <i class="ri-user-unfollow-line me-1"></i>Desactivar Usuario
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-outline-success ms-auto" 
                                            onclick="confirmReactivate(<?php echo $user['id']; ?>)">
                                        <i class="ri-user-add-line me-1"></i>Reactivar Usuario
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Mostrar/ocultar campos de contraseña
function togglePasswordFields() {
    const checkbox = document.getElementById('change_password');
    const passwordFields = document.getElementById('password_fields');
    const passwordInputs = passwordFields.querySelectorAll('input');
    
    if (checkbox.checked) {
        passwordFields.style.display = 'block';
        passwordInputs.forEach(input => input.required = true);
    } else {
        passwordFields.style.display = 'none';
        passwordInputs.forEach(input => {
            input.required = false;
            input.value = '';
        });
    }
}

// Validación de contraseñas
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (password && confirmPassword && password !== confirmPassword) {
        this.setCustomValidity('Las contraseñas no coinciden');
    } else {
        this.setCustomValidity('');
    }
});

// Confirmación de desactivación
function confirmDeactivate(userId) {
    if (confirm('¿Estás seguro de que deseas desactivar este usuario? El usuario no podrá acceder al sistema hasta que sea reactivado.')) {
        window.location.href = 'deactivate.php?id=' + userId;
    }
}

// Confirmación de reactivación
function confirmReactivate(userId) {
    if (confirm('¿Estás seguro de que deseas reactivar este usuario?')) {
        window.location.href = 'reactivate.php?id=' + userId;
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
