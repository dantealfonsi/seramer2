<?php
// Verificar acceso - permitir RRHH y jefes de departamento
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
AuthMiddleware::requireUserManagementAccess();

// Incluir header y configuración
require_once __DIR__ . '/../../models/UserModel.php';

$userModel = new UserModel();

// Verificar roles
$is_manager = AuthMiddleware::isManager();
$is_rrhh = AuthMiddleware::hasAccessToDepartment('Recursos Humanos');

// Obtener ID del usuario y validarlo
$user_id = $_GET['id'] ?? null;

if (!$user_id || !is_numeric($user_id)) {
    header('Location: index.php?error=invalid_user_id');
    exit;
}

// Convertir a entero para seguridad
$user_id = (int)$user_id;

// Obtener datos completos del usuario
$user = $userModel->getUserWithStaffDetails($user_id);

if (!$user) {
    // Intentar obtener usuario básico como fallback
    $basic_user = $userModel->getById($user_id);
    if (!$basic_user) {
        header('Location: index.php?error=user_not_found');
        exit;
    } else {
        // Usar datos básicos como fallback
        $user = $basic_user;
        $user['departments'] = $userModel->getUserDepartments($user_id);
        $user['activity_log'] = [];
    }
}

// Verificar permisos: RRHH puede ver cualquiera, jefes solo de su departamento
if ($is_manager && !$is_rrhh) {
    if (($user['department_id'] ?? null) != $is_manager['id']) {
        header('Location: index.php?error=no_permission');
        exit;
    }
}

$page_title = $is_manager && !$is_rrhh ? 
    'Información del Usuario - Departamento: ' . $is_manager['name'] : 
    'Información del Usuario';

// Verificar si tiene datos completos de staff
$has_staff_data = !empty($user['first_name']) && !empty($user['last_name']);
?>

<?php include __DIR__ . '/../layouts/header.php'; ?>
<?php include __DIR__ . '/../layouts/navigation.php'; ?>
<?php include __DIR__ . '/../layouts/navigation-top.php'; ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Alerta si faltan datos de personal -->
    <?php if (!$has_staff_data): ?>
        <div class="alert alert-warning alert-dismissible" role="alert">
            <i class="ri-alert-line me-2"></i>
            <strong>Información Incompleta:</strong> Este usuario no tiene datos de personal completos asociados. 
            Se muestra la información disponible del sistema de usuarios.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <div class="row">
        <!-- Información Principal -->
        <div class="col-xl-4 col-lg-5 col-md-5">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="user-avatar-section">
                        <div class="d-flex align-items-center flex-column">
                            <img class="img-fluid rounded-circle mb-3" 
                                 src="<?php echo '../../public/assets/img/avatars/1.png'; ?>" 
                                 height="100" width="100" alt="User avatar">
                            <div class="user-info text-center">
                                <h4 class="mb-2">
                                    <?php 
                                    if (!empty($user['first_name']) && !empty($user['last_name'])) {
                                        echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
                                    } else {
                                        echo htmlspecialchars($user['username']);
                                    }
                                    ?>
                                </h4>
                                <span class="badge bg-<?php echo ($user['status'] == 'active') ? 'success' : 'danger'; ?> rounded-pill">
                                    <?php echo ($user['status'] == 'active') ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-around flex-wrap my-4 py-3">
                        <div class="d-flex align-items-start me-4 mt-3 gap-3">
                            <span class="badge bg-primary p-2 rounded">
                                <i class="ri-user-line ri-24px"></i>
                            </span>
                            <div>
                                <h5 class="mb-0"><?php echo htmlspecialchars($user['username']); ?></h5>
                                <span>Usuario del Sistema</span>
                            </div>
                        </div>
                    </div>

                    <h5 class="pb-2 border-bottom mb-4">Datos de Contacto</h5>
                    <div class="info-container">
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <span class="fw-medium me-2">Email:</span>
                                <span><?php echo htmlspecialchars($user['email']); ?></span>
                            </li>
                            <li class="mb-3">
                                <span class="fw-medium me-2">Último Login:</span>
                                <span><?php echo $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Nunca'; ?></span>
                            </li>
                            <li class="mb-3">
                                <span class="fw-medium me-2">Fecha de Creación:</span>
                                <span><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></span>
                            </li>
                        </ul>
                        
                        <div class="d-flex justify-content-center pt-3 gap-2">
                            <?php if ($is_rrhh || ($is_manager && ($user['department_id'] ?? null) == $is_manager['id'])): ?>
                                <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-primary">
                                    <i class="ri-edit-line me-1"></i>Editar
                                </a>
                            <?php endif; ?>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="ri-arrow-left-line me-1"></i>Volver
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información Detallada -->
        <div class="col-xl-8 col-lg-7 col-md-7">
            <!-- Información Personal -->
            <div class="card mb-4">
                <h5 class="card-header">
                    <i class="ri-user-3-line me-2"></i>Información Personal
                </h5>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-6 col-12">
                            <dl class="row mb-0">
                                <dt class="col-sm-4 fw-medium text-nowrap">Nombre:</dt>
                                <dd class="col-sm-8">
                                    <?php 
                                    if (!empty($user['first_name']) && !empty($user['last_name'])) {
                                        echo htmlspecialchars($user['first_name'] . ' ' . 
                                            (($user['middle_name'] ?? '') ? $user['middle_name'] . ' ' : '') . 
                                            $user['last_name'] . 
                                            (($user['second_last_name'] ?? '') ? ' ' . $user['second_last_name'] : ''));
                                    } else {
                                        echo '<em class="text-muted">Datos de personal no disponibles</em>';
                                    }
                                    ?>
                                </dd>
                                
                                <dt class="col-sm-4 fw-medium text-nowrap">Cédula:</dt>
                                <dd class="col-sm-8">
                                    <?php echo !empty($user['id_number']) ? htmlspecialchars($user['id_number']) : '<em class="text-muted">No registrada</em>'; ?>
                                </dd>
                                
                                <dt class="col-sm-4 fw-medium text-nowrap">Fecha de Nac.:</dt>
                                <dd class="col-sm-8">
                                    <?php echo !empty($user['birth_date']) ? date('d/m/Y', strtotime($user['birth_date'])) : '<em class="text-muted">No registrada</em>'; ?>
                                </dd>
                                
                                <dt class="col-sm-4 fw-medium text-nowrap">Género:</dt>
                                <dd class="col-sm-8">
                                    <?php 
                                    $gender = $user['gender'] ?? null;
                                    if ($gender === null || $gender === '') {
                                        echo '<em class="text-muted">No especificado</em>';
                                    } elseif ($gender == 1) {
                                        echo 'Femenino';
                                    } else {
                                        echo 'Masculino';
                                    }
                                    ?>
                                </dd>
                            </dl>
                        </div>
                        <div class="col-xl-6 col-12">
                            <dl class="row mb-0">
                                <dt class="col-sm-4 fw-medium text-nowrap">Fecha de Ingreso:</dt>
                                <dd class="col-sm-8">
                                    <?php echo !empty($user['hire_date']) ? date('d/m/Y', strtotime($user['hire_date'])) : '<em class="text-muted">No registrada</em>'; ?>
                                </dd>
                                
                                <dt class="col-sm-4 fw-medium text-nowrap">Estado del Personal:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-<?php echo ($user['status'] == 'active') ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($user['status'] ?? 'desconocido'); ?>
                                    </span>
                                </dd>
                                
                                <dt class="col-sm-4 fw-medium text-nowrap">Grado Académico:</dt>
                                <dd class="col-sm-8">
                                    <?php echo !empty($user['academic_degree_name']) ? htmlspecialchars($user['academic_degree_name']) : '<em class="text-muted">No registrado</em>'; ?>
                                </dd>
                                
                                <dt class="col-sm-4 fw-medium text-nowrap">Especialización:</dt>
                                <dd class="col-sm-8">
                                    <?php echo !empty($user['academic_specialization_name']) ? htmlspecialchars($user['academic_specialization_name']) : '<em class="text-muted">No registrada</em>'; ?>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información Laboral -->
            <div class="card mb-4">
                <h5 class="card-header">
                    <i class="ri-building-line me-2"></i>Información Laboral
                </h5>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-6 col-12">
                            <dl class="row mb-0">
                                <dt class="col-sm-4 fw-medium text-nowrap">Departamento:</dt>
                                <dd class="col-sm-8">
                                    <?php if (!empty($user['department_name'])): ?>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($user['department_name']); ?></span>
                                    <?php else: ?>
                                        <em class="text-muted">No asignado</em>
                                    <?php endif; ?>
                                </dd>
                                
                                <dt class="col-sm-4 fw-medium text-nowrap">División:</dt>
                                <dd class="col-sm-8">
                                    <?php echo !empty($user['division_name']) ? htmlspecialchars($user['division_name']) : '<em class="text-muted">No asignada</em>'; ?>
                                </dd>
                                
                                <dt class="col-sm-4 fw-medium text-nowrap">Cargo:</dt>
                                <dd class="col-sm-8">
                                    <?php echo !empty($user['job_position_name']) ? htmlspecialchars($user['job_position_name']) : '<em class="text-muted">No asignado</em>'; ?>
                                </dd>
                            </dl>
                        </div>
                        <div class="col-xl-6 col-12">
                            <dl class="row mb-0">
                                <dt class="col-sm-4 fw-medium text-nowrap">Departamentos Asignados:</dt>
                                <dd class="col-sm-8">
                                    <?php if (!empty($user['departments'])): ?>
                                        <?php foreach ($user['departments'] as $dept): ?>
                                            <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($dept['name']); ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <em>Ninguno asignado</em>
                                    <?php endif; ?>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historial de Actividad -->
            <div class="card">
                <h5 class="card-header">
                    <i class="ri-time-line me-2"></i>Historial de Actividad Reciente
                </h5>
                <div class="card-body">
                    <?php if (!empty($user['activity_log'])): ?>
                        <div class="timeline timeline-center">
                            <?php foreach (array_slice($user['activity_log'], 0, 10) as $index => $activity): ?>
                                <div class="timeline-item">
                                    <div class="timeline-point timeline-point-<?php echo ($index % 2 == 0) ? 'primary' : 'info'; ?>">
                                        <i class="ri-history-line"></i>
                                    </div>
                                    <div class="timeline-event">
                                        <div class="timeline-header">
                                            <h6 class="mb-0"><?php echo htmlspecialchars($activity['action']); ?></h6>
                                            <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($activity['created_at'])); ?></small>
                                        </div>
                                        <?php if (!empty($activity['details'])): ?>
                                            <p class="mb-0 text-muted"><?php echo htmlspecialchars($activity['details']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="ri-history-line ri-48px text-muted mb-3 d-block"></i>
                            <h6 class="text-muted">No hay actividad registrada</h6>
                            <p class="text-muted mb-0">El historial de actividad aparecerá aquí cuando el usuario realice acciones en el sistema.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding: 0;
}

.timeline-item {
    position: relative;
    padding-left: 40px;
    padding-bottom: 20px;
}

.timeline-item:not(:last-child):before {
    content: '';
    position: absolute;
    left: 14px;
    top: 30px;
    height: calc(100% - 10px);
    width: 2px;
    background-color: #e9ecef;
}

.timeline-point {
    position: absolute;
    left: 0;
    top: 5px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
}

.timeline-point-primary {
    background-color: #8b5cf6;
}

.timeline-point-info {
    background-color: #06b6d4;
}

.timeline-event {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border-left: 3px solid #e9ecef;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>