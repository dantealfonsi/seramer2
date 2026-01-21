<?php
require_once __DIR__ . '/../../views/layouts/header.php';
require_once __DIR__ . '/../../views/layouts/navigation.php';
require_once __DIR__ . '/../../views/layouts/navigation-top.php';
require_once __DIR__ . '/../../controllers/ProfileController.php';

$controller = new ProfileController();
$user = $controller->getUserProfileData();
?>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        
        <!-- Header Profile -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <!-- Banner Removed -->
                    <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-4">
                        <!-- Profile Image Removed -->
                        <div class="flex-grow-1 p-4">
                            <div class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4">
                                <div class="user-profile-info">
                                    <h4 class="mb-2"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                                    <ul class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-2">
                                        <li class="list-inline-item fw-semibold">
                                            <i class="ri-user-star-line me-1"></i> <?php echo htmlspecialchars($user['job_position_name'] ?? 'Cargo no definido'); ?>
                                        </li>
                                        <li class="list-inline-item fw-semibold">
                                            <i class="ri-building-line me-1"></i> <?php echo htmlspecialchars($user['department_name'] ?? 'Departamento no definido'); ?>
                                        </li>
                                        <li class="list-inline-item fw-semibold">
                                            <i class="ri-calendar-line me-1"></i> Ingreso: <?php echo htmlspecialchars($user['hiring_date'] ?? 'N/A'); ?>
                                        </li>
                                    </ul>
                                </div>
                                <a href="javascript:void(0)" class="btn btn-primary">
                                    <i class="ri-check-line me-1"></i> Activo
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Header Profile -->

        <!-- Details -->
        <div class="row">
            <div class="col-xl-4 col-lg-5 col-md-5">
                <!-- About User -->
                <div class="card mb-4">
                    <div class="card-body">
                        <small class="card-text text-uppercase text-muted">Información Personal</small>
                        <ul class="list-unstyled mb-4 mt-2">
                            <li class="d-flex align-items-center mb-3">
                                <i class="ri-user-line icon-lg text-primary me-2"></i>
                                <span class="fw-bold me-2">Nombre Completo:</span> <span><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                            </li>
                             <li class="d-flex align-items-center mb-3">
                                <i class="ri-id-card-line icon-lg text-primary me-2"></i>
                                <span class="fw-bold me-2">Cédula:</span> <span><?php echo htmlspecialchars($user['id_number'] ?? 'N/A'); ?></span>
                            </li>
                             <li class="d-flex align-items-center mb-3">
                                <i class="ri-cake-line icon-lg text-primary me-2"></i>
                                <span class="fw-bold me-2">Fecha Nac.:</span> <span><?php echo htmlspecialchars($user['birth_date'] ?? 'N/A'); ?></span>
                            </li>
                        </ul>
                        <small class="card-text text-uppercase text-muted">Contacto</small>
                        <ul class="list-unstyled mb-4 mt-2">
                            <li class="d-flex align-items-center mb-3">
                                <i class="ri-phone-line icon-lg text-success me-2"></i>
                                <span class="fw-bold me-2">Teléfono:</span> <span><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></span>
                            </li>
                            <li class="d-flex align-items-center mb-3">
                                <i class="ri-mail-line icon-lg text-danger me-2"></i>
                                <span class="fw-bold me-2">Email:</span> <span><?php echo htmlspecialchars($user['email']); ?></span>
                            </li>
                            <li class="d-flex align-items-center mb-3">
                                <i class="ri-map-pin-line icon-lg text-warning me-2"></i>
                                <span class="fw-bold me-2">Dirección:</span> <span><?php echo htmlspecialchars($user['address'] ?? 'No registrada'); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
                <!--/ About User -->
            </div>
            
            <div class="col-xl-8 col-lg-7 col-md-7">
                <!-- Activity / Details -->
                <div class="card mb-4">
                    <div class="card-header align-items-center">
                        <h5 class="card-action-title mb-0"><i class="ri-file-shield-2-line me-2"></i> Información Institucional</h5>
                    </div>
                   <div class="card-body">
                       <div class="row">
                           <div class="col-md-6 mb-3">
                               <label class="form-label fw-bold">Grado Académico</label>
                               <p class="form-control-static"><?php echo htmlspecialchars($user['academic_degree_name'] ?? 'N/A'); ?></p>
                           </div>
                           <div class="col-md-6 mb-3">
                               <label class="form-label fw-bold">Especialización</label>
                               <p class="form-control-static"><?php echo htmlspecialchars($user['academic_specialization_name'] ?? 'N/A'); ?></p>
                           </div>
                           <div class="col-md-6 mb-3">
                               <label class="form-label fw-bold">Usuario de Sistema</label>
                               <p class="form-control-static"><?php echo htmlspecialchars($user['username']); ?></p>
                           </div>
                           <div class="col-md-6 mb-3">
                               <label class="form-label fw-bold">Departamento Actual (Sesión)</label>
                               <span class="badge bg-label-primary"><?php echo htmlspecialchars($_SESSION['selected_department'] ?? 'N/A'); ?></span>
                           </div>
                       </div>
                   </div>
                </div>

                <div class="card">
                    <div class="card-header">
                         <h5 class="card-action-title mb-0"><i class="ri-history-line me-2"></i> Seguridad & Actividad</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning" role="alert">
                            <h6 class="alert-heading mb-1"><i class="ri-alert-line me-2"></i>Importante</h6>
                            <span>Para cambiar su contraseña o actualizar datos sensibles, por favor contacte al departamento de Recursos Humanos o TI.</span>
                        </div>
                        <ul class="timeline mt-3">
                             <li class="timeline-item timeline-item-transparent text-primary">
                                <span class="timeline-point timeline-point-primary"></span>
                                <div class="timeline-event">
                                    <div class="timeline-header mb-1">
                                        <h6 class="mb-0">Último Inicio de Sesión</h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($user['last_login'] ?? 'Hoy'); ?></small>
                                    </div>
                                    <p class="mb-0">Registro de acceso exitoso al sistema.</p>
                                </div>
                            </li>
                             <li class="timeline-item timeline-item-transparent text-success">
                                <span class="timeline-point timeline-point-success"></span>
                                <div class="timeline-event">
                                    <div class="timeline-header mb-1">
                                        <h6 class="mb-0">Estado de Cuenta</h6>
                                        <small class="text-muted">Activo</small>
                                    </div>
                                    <p class="mb-0">Su cuenta se encuentra operativa y sin restricciones.</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Details -->

    </div>
</div>

<?php 
require_once __DIR__ . '/../../views/layouts/footer.php'; 
?>
