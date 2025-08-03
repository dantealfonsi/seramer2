<?php
// Incluir el controlador de usuarios
require_once __DIR__ . '/../../controllers/UserController.php';

$userController = new UserController();

// Preparar parámetros para el controlador
$params = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $params = $_POST;
    $params['_method'] = 'POST';
}

// Obtener datos del controlador
$data = $userController->create($params);

// Verificar si hay redirección
if (!$data['success'] && isset($data['redirect'])) {
    header('Location: ' . $data['redirect']);
    exit;
}

// Extraer variables del resultado del controlador
$message = $data['message'];
$messageType = $data['messageType'];
$errors = $data['errors'];
$staff_id = $data['staff_id'];
$username = $data['username'];
$email = $data['email'];
$is_manager = $data['is_manager'];
$is_rrhh = $data['is_rrhh'];
$available_staff = $data['available_staff'] ?? [];

// Determinar el título de la página
$page_title = $is_manager && !$is_rrhh ? 
    'Crear Usuario - Departamento: ' . $is_manager['name'] : 
    'Crear Usuario';

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
                        <?php if ($is_manager && !$is_rrhh): ?>
                            <small class="text-muted">Solo puedes crear usuarios para personal de tu departamento</small>
                        <?php endif; ?>
                    </div>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="ri-arrow-left-line me-1"></i>Volver a la Lista
                    </a>
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

                    <?php if (empty($available_staff)): ?>
                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>
                            No hay personal disponible para crear usuarios.
                            <?php if ($is_manager && !$is_rrhh): ?>
                                Todo el personal de tu departamento ya tiene usuario asignado.
                            <?php else: ?>
                                Todo el personal activo ya tiene usuario asignado.
                            <?php endif; ?>
                        </div>
                        <a href="index.php" class="btn btn-primary">Volver a la Lista</a>
                    <?php else: ?>
                        <!-- Formulario de creación -->
                        <form method="POST" class="row g-3">
                            <!-- Selección de Personal -->
                            <div class="col-12">
                                <label for="staff_id" class="form-label">
                                    <strong>Personal</strong>
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="staff_id" id="staff_id" class="form-select" required onchange="fillUserData()">
                                    <option value="">Selecciona un miembro del personal</option>
                                    <?php 
                                    $grouped_staff = [];
                                    foreach ($available_staff as $staff): 
                                        if ($is_rrhh) {
                                            $grouped_staff[$staff['department_name']][] = $staff;
                                        } else {
                                            $grouped_staff[''][] = $staff;
                                        }
                                    endforeach;
                                    
                                    foreach ($grouped_staff as $dept_name => $staff_list):
                                        if ($dept_name && $is_rrhh): ?>
                                            <optgroup label="<?php echo htmlspecialchars($dept_name); ?>">
                                        <?php endif;
                                        
                                        foreach ($staff_list as $staff): ?>
                                            <option value="<?php echo $staff['id']; ?>" 
                                                    data-email="<?php echo htmlspecialchars($staff['first_name'] . '.' . $staff['last_name'] . '@empresa.com'); ?>"
                                                    data-username="<?php echo htmlspecialchars(strtolower(substr($staff['first_name'], 0, 1) . $staff['last_name'])); ?>"
                                                    <?php echo ($staff_id == $staff['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?>
                                                (<?php echo htmlspecialchars($staff['id_number']); ?>) - 
                                                <?php echo htmlspecialchars($staff['job_position_name']); ?>
                                            </option>
                                        <?php endforeach;
                                        
                                        if ($dept_name && $is_rrhh): ?>
                                            </optgroup>
                                        <?php endif;
                                    endforeach; ?>
                                </select>
                                <div class="form-text">Selecciona el personal para el cual crear el usuario del sistema</div>
                            </div>

                            <!-- Datos del Usuario -->
                            <div class="col-md-6">
                                <label for="username" class="form-label">
                                    <strong>Nombre de Usuario</strong>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="username" id="username" class="form-control" 
                                       value="<?php echo htmlspecialchars($username ?? ''); ?>" 
                                       required minlength="3" placeholder="ej: jperez">
                                <div class="form-text">Mínimo 3 caracteres, solo letras y números</div>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">
                                    <strong>Email</strong>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="email" name="email" id="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                                       required placeholder="ej: usuario@empresa.com">
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label">
                                    <strong>Contraseña</strong>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="password" name="password" id="password" class="form-control" 
                                       required minlength="6" placeholder="Mínimo 6 caracteres">
                                <div class="form-text">La contraseña debe tener al menos 6 caracteres</div>
                            </div>

                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">
                                    <strong>Confirmar Contraseña</strong>
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" 
                                       required placeholder="Repetir la contraseña">
                            </div>

                            <!-- Botones -->
                            <div class="col-12">
                                <hr>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-save-line me-1"></i>Crear Usuario
                                    </button>
                                    <a href="index.php" class="btn btn-outline-secondary">
                                        <i class="ri-close-line me-1"></i>Cancelar
                                    </a>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-llenar datos del usuario basado en la selección del personal
function fillUserData() {
    const staffSelect = document.getElementById('staff_id');
    const selectedOption = staffSelect.options[staffSelect.selectedIndex];
    
    if (selectedOption.value) {
        const suggestedEmail = selectedOption.getAttribute('data-email');
        const suggestedUsername = selectedOption.getAttribute('data-username');
        
        document.getElementById('email').value = suggestedEmail;
        document.getElementById('username').value = suggestedUsername;
    } else {
        document.getElementById('email').value = '';
        document.getElementById('username').value = '';
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
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
