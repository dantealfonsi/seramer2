<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title mb-1">Bienvenido al Sistema de Gestión Municipal</h4>
                <p class="card-text">Panel de control y estadísticas del mercado</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Estadísticas Cards -->
    <div class="col-lg-3 col-sm-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="card-info">
                        <p class="card-text mb-1">Adjudicatarios</p>
                        <div class="d-flex align-items-end mb-1">
                            <h4 class="card-title mb-0 me-2">0</h4>
                            <small class="text-success">(Activos)</small>
                        </div>
                    </div>
                    <div class="card-icon">
                        <span class="badge bg-label-primary rounded-circle p-2">
                            <i class="ri ri-group-line ri-24px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-sm-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="card-info">
                        <p class="card-text mb-1">Contratos</p>
                        <div class="d-flex align-items-end mb-1">
                            <h4 class="card-title mb-0 me-2">0</h4>
                            <small class="text-success">(Vigentes)</small>
                        </div>
                    </div>
                    <div class="card-icon">
                        <span class="badge bg-label-success rounded-circle p-2">
                            <i class="ri ri-file-text-line ri-24px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-sm-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="card-info">
                        <p class="card-text mb-1">Pagos Pendientes</p>
                        <div class="d-flex align-items-end mb-1">
                            <h4 class="card-title mb-0 me-2">0</h4>
                            <small class="text-warning">(Este mes)</small>
                        </div>
                    </div>
                    <div class="card-icon">
                        <span class="badge bg-label-warning rounded-circle p-2">
                            <i class="ri ri-time-line ri-24px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-sm-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="card-info">
                        <p class="card-text mb-1">Recaudación</p>
                        <div class="d-flex align-items-end mb-1">
                            <h4 class="card-title mb-0 me-2">Bs. 0</h4>
                            <small class="text-success">(Este mes)</small>
                        </div>
                    </div>
                    <div class="card-icon">
                        <span class="badge bg-label-info rounded-circle p-2">
                            <i class="ri ri-money-dollar-circle-line ri-24px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Acciones Rápidas -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Acciones Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="<?= $app['url'] ?>/awardee/create" class="list-group-item list-group-item-action d-flex align-items-center">
                        <i class="ri ri-add-circle-line me-3 text-primary"></i>
                        <div>
                            <h6 class="mb-0">Registrar Adjudicatario</h6>
                            <small class="text-muted">Crear un nuevo adjudicatario en el sistema</small>
                        </div>
                    </a>
                    <a href="<?= $app['url'] ?>/contract/create" class="list-group-item list-group-item-action d-flex align-items-center">
                        <i class="ri ri-file-add-line me-3 text-success"></i>
                        <div>
                            <h6 class="mb-0">Nuevo Contrato</h6>
                            <small class="text-muted">Crear un contrato de adjudicación</small>
                        </div>
                    </a>
                    <a href="<?= $app['url'] ?>/cobro/index" class="list-group-item list-group-item-action d-flex align-items-center">
                        <i class="ri ri-cash-line me-3 text-info"></i>
                        <div>
                            <h6 class="mb-0">Registrar Cobro</h6>
                            <small class="text-muted">Procesar pagos de mensualidades</small>
                        </div>
                    </a>
                    <a href="<?= $app['url'] ?>/fiscalyear/createrate" class="list-group-item list-group-item-action d-flex align-items-center">
                        <i class="ri ri-currency-line me-3 text-warning"></i>
                        <div>
                            <h6 class="mb-0">Establecer Tasa del Euro</h6>
                            <small class="text-muted">Actualizar la tasa de cambio mensual</small>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Información del Sistema -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Información del Sistema</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-3">
                        <div class="d-flex align-items-center">
                            <i class="ri ri-checkbox-circle-line text-success me-2"></i>
                            <span><strong>Versión:</strong> 1.0.0</span>
                        </div>
                    </li>
                    <li class="mb-3">
                        <div class="d-flex align-items-center">
                            <i class="ri ri-checkbox-circle-line text-success me-2"></i>
                            <span><strong>PHP:</strong> 8.4</span>
                        </div>
                    </li>
                    <li class="mb-3">
                        <div class="d-flex align-items-center">
                            <i class="ri ri-checkbox-circle-line text-success me-2"></i>
                            <span><strong>Base de Datos:</strong> MySQL 8.4.5</span>
                        </div>
                    </li>
                    <li class="mb-3">
                        <div class="d-flex align-items-center">
                            <i class="ri ri-checkbox-circle-line text-success me-2"></i>
                            <span><strong>Arquitectura:</strong> MVC</span>
                        </div>
                    </li>
                    <li>
                        <div class="d-flex align-items-center">
                            <i class="ri ri-shield-check-line text-success me-2"></i>
                            <span><strong>Seguridad:</strong> CSRF Protection, Password Hashing</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

