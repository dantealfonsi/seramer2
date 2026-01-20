<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="<?= $app['url'] ?>/home/index" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img src="/seramer-local/public/assets/img/logo.png" style="width: 3rem;height: 3rem;" alt="Logo" class="logo" />
            </span>
            <span class="app-brand-text demo menu-text fw-bold">SERAMER</span>
        </a>
        
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="ri ri-arrow-left-s-line align-middle"></i>
        </a>
    </div>
    
    <div class="menu-inner-shadow"></div>
    
    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item">
            <a href="<?= $app['url'] ?>/home/index" class="menu-link">
                <i class="menu-icon tf-icons ri ri-home-5-line"></i>
                <div data-i18n="Dashboard">Dashboard</div>
            </a>
        </li>
        
        <!-- Módulos -->
        <li class="menu-header small">
            <span class="menu-header-text">Gestión</span>
        </li>
        
        <!-- Adjudicatarios -->
        <li class="menu-item">
            <a href="<?= $app['url'] ?>/awardee/index" class="menu-link">
                <i class="menu-icon tf-icons ri ri-group-line"></i>
                <div>Adjudicatarios</div>
            </a>
        </li>
        
        <!-- Contratos -->
        <li class="menu-item">
            <a href="<?= $app['url'] ?>/contract/index" class="menu-link">
                <i class="menu-icon tf-icons ri ri-file-text-line"></i>
                <div>Contratos</div>
            </a>
        </li>
        
        <!-- Planificación -->
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ri ri-calendar-check-line"></i>
                <div>Planificación</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item">
                    <a href="<?= $app['url'] ?>/planning/anticipados" class="menu-link">
                        <div>Anticipados</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="<?= $app['url'] ?>/planning/simultaneos" class="menu-link">
                        <div>Simultáneos</div>
                    </a>
                </li>
            </ul>
        </li>
        
        <!-- Cobros -->
        <li class="menu-item">
            <a href="<?= $app['url'] ?>/cobro/index" class="menu-link">
                <i class="menu-icon tf-icons ri ri-money-dollar-circle-line"></i>
                <div>Cobros</div>
            </a>
        </li>
        
        <!-- Caja Diaria -->
        <li class="menu-item">
            <a href="<?= $app['url'] ?>/dailycash/index" class="menu-link">
                <i class="menu-icon tf-icons ri ri-lock-unlock-line"></i>
                <div>Caja Diaria</div>
            </a>
        </li>
        
        <!-- Reportes -->
        <li class="menu-item">
            <a href="<?= $app['url'] ?>/report/index" class="menu-link">
                <i class="menu-icon tf-icons ri ri-file-list-3-line"></i>
                <div>Reportes</div>
            </a>
        </li>
        
        <!-- Configuración -->
        <li class="menu-header small">
            <span class="menu-header-text">Configuración</span>
        </li>
        
        <!-- Años Fiscales -->
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ri ri-calendar-line"></i>
                <div>Año Fiscal</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item">
                    <a href="<?= $app['url'] ?>/fiscalyear/index" class="menu-link">
                        <div>Años Fiscales</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="<?= $app['url'] ?>/fiscalyear/rates" class="menu-link">
                        <div>Tasas de Euro</div>
                    </a>
                </li>
            </ul>
        </li>
        
        <!-- Catálogo -->
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ri ri-list-check"></i>
                <div>Catálogo</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item">
                    <a href="<?= $app['url'] ?>/internalcategory/index" class="menu-link">
                        <div>Rubros Internos</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="<?= $app['url'] ?>/externalcategory/index" class="menu-link">
                        <div>Rubros Externos</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="<?= $app['url'] ?>/zone/index" class="menu-link">
                        <div>Zonas</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="<?= $app['url'] ?>/sector/index" class="menu-link">
                        <div>Sectores</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="<?= $app['url'] ?>/marketstall/index" class="menu-link">
                        <div>Locales</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="<?= $app['url'] ?>/paymentmethod/index" class="menu-link">
                        <div>Métodos de Pago</div>
                    </a>
                </li>
            </ul>
        </li>
        
        <!-- Gestión de Cajas -->
        <li class="menu-item">
            <a href="<?= $app['url'] ?>/cashregister/index" class="menu-link">
                <i class="menu-icon tf-icons ri ri-safe-line"></i>
                <div>Gestión de Cajas</div>
            </a>
        </li>
        
        <!-- Historial de Actividades -->
        <li class="menu-item">
            <a href="<?= $app['url'] ?>/activitylog/index" class="menu-link">
                <i class="menu-icon tf-icons ri ri-history-line"></i>
                <div>Historial de Actividades</div>
            </a>
        </li>
        
        <!-- Usuarios -->
        <li class="menu-item">
            <a href="<?= $app['url'] ?>/user/index" class="menu-link">
                <i class="menu-icon tf-icons ri ri-user-settings-line"></i>
                <div>Usuarios</div>
            </a>
        </li>
    </ul>
</aside>


