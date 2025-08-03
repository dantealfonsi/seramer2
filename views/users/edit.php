<?php
// Verificar acceso - permitir RRHH y jefes de departamento
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
AuthMiddleware::requireUserManagementAccess();

// Incluir header y configuración
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../models/UserModel.php';

$userModel = new UserModel();
$message = '';
$messageType = '';
$errors = [];

// Verificar roles
$is_manager = AuthMiddleware::isManager();
$is_rrhh = AuthMiddleware::hasAccessToDepartment('Recursos Humanos');

// Obtener ID del usuario a editar
$user_id = $_GET['id'] ?? null;

if (!$user_id) {
    header('Location: index.php');
    exit;
}

// Obtener datos del usuario
$user = $userModel->getUserWithStaffDetails($user_id);

if (!$user) {
    header('Location: index.php?error=user_not_found');
    exit;
}

// Verificar permisos: RRHH puede editar cualquiera, jefes solo de su departamento
if ($is_manager && !$is_rrhh) {
    if ($user['department_id'] != $is_manager['id']) {
        header('Location: index.php?error=no_permission');
        exit;
    }
}

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $status = $_POST['status'] ?? '';
    $change_password = isset($_POST['change_password']);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validaciones
    if (empty($username)) {
        $errors[] = 'El nombre de usuario es requerido';
    } elseif (strlen($username) < 3) {
        $errors[] = 'El nombre de usuario debe tener al menos 3 caracteres';
    }
    
    if (empty($email)) {
        $errors[] = 'El email es requerido';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El email no tiene un formato válido';
    }
    
    if (!in_array($status, ['active', 'inactive'])) {
        $errors[] = 'Estado inválido';
    }
    
    // Validar cambio de contraseña si se solicita
    if ($change_password) {
        if (empty($password)) {
            $errors[] = 'La nueva contraseña es requerida';
        } elseif (strlen($password) < 6) {
            $errors[] = 'La nueva contraseña debe tener al menos 6 caracteres';
        }
        
        if ($password !== $confirm_password) {
            $errors[] = 'Las nuevas contraseñas no coinciden';
        }
    }
    
    // Verificar que username y email no estén en uso por otro usuario
    if (empty($errors)) {
        $existing_user = $userModel->getByUsername($username);
        if ($existing_user && $existing_user['id'] != $user_id) {
            $errors[] = 'El nombre de usuario ya está en uso';
        }
        
        $existing_email = $userModel->getByEmail($email);
        if ($existing_email && $existing_email['id'] != $user_id) {
            $errors[] = 'El email ya está en uso';
        }
    }
    
    // Si no hay errores, actualizar el usuario
    if (empty($errors)) {
        try {
            $update_data = [
                'username' => $username,
                'email' => $email,
                'status' => $status
            ];
            
            if ($change_password) {
                $update_data['password'] = $password;
            }
            
            $result = $userModel->update($user_id, $update_data);
            
            if ($result) {
                $message = 'Usuario actualizado exitosamente';
                $messageType = 'success';
                // Recargar datos del usuario
                $user = $userModel->getUserWithStaffDetails($user_id);
            } else {
                $message = 'Error al actualizar el usuario';
                $messageType = 'danger';
            }
        } catch (Exception $e) {
            $message = 'Error interno del servidor';
            $messageType = 'danger';
        }
    }
}

$page_title = $is_manager && !$is_rrhh ? 
    'Editar Usuario - Departamento: ' . $is_manager['name'] : 
    'Editar Usuario';
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
                        <small class="text-muted">
                            Editando: <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
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
                                    <h6 class="card-title mb-3">
                                        <i class="ri-user-line me-2"></i>Información del Personal
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>Nombre Completo:</strong><br>
                                            <span><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Cédula:</strong><br>
                                            <span><?php echo htmlspecialchars($user['id_number']); ?></span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Departamento:</strong><br>
                                            <span class="badge bg-info"><?php echo htmlspecialchars($user['department_name']); ?></span>
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Cargo:</strong><br>
                                            <span><?php echo htmlspecialchars($user['job_position_name']); ?></span>
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
