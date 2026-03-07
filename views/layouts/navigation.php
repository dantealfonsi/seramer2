<?php 
// Incluir la configuración al inicio de cada vista
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

// Inicializar el controlador de autenticación
$authController = new AuthController();

// Verificar autenticación
$authController->requireAuth();

// Obtener datos del usuario actual
$current_user = $authController->getCurrentUser();
$user_departments = $current_user['departments'] ?? [];
$current_department = $current_user['selected_department'] ?? '';
$is_superadmin = !empty($_SESSION['is_superadmin']);
$department_menus = [];

// Obtener menús específicos del departamento
$userModel = new UserModel();
$all_master_menus = $userModel->getMasterMenus();

/**
 * Función helper para verificar si un enlace o submenú debe estar activo/abierto
 */
function isMenuItemActive($itemUrl) {
    if (empty($itemUrl)) return false;
    
    // Obtener solo la ruta del script actual (sin query params)
    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Obtener la ruta del ítem (ej: /seramer2/views/users/index.php)
    $itemPath = parse_url(url($itemUrl), PHP_URL_PATH);
    
    return ($currentPath === $itemPath);
}

function isMenuGroupActive($menu) {
    if (!isset($menu['submenu'])) return false;
    
    // Si estamos en una página de configuración global, no queremos que el menú de departamento
    // "reclame" la apertura si el usuario es superadmin (para evitar doble resaltado)
    $globalPages = ['views/users/index.php', 'views/roles/index.php', 'views/departments/index.php'];
    
    foreach ($menu['submenu'] as $submenu) {
        if (isMenuItemActive($submenu['url'])) {
            // Prioridad: Si es una página global y el ítem está en un submenú,
            // devolvemos true pero el loop principal decidirá si lo abre.
            return true;
        }
    }
    return false;
}

if (!empty($_SESSION['is_superadmin'])) {
    // Si es superadmin, mostramos un selector para elegir qué departamento ver
    $superadmin_selected_department = $_GET['superadmin_dept'] ?? ($_SESSION['superadmin_dept'] ?? 'Recursos Humanos');
    // Save to session
    $_SESSION['superadmin_dept'] = $superadmin_selected_department;
    
    // Obtener las llaves del master menu como los departamentos disponibles
    $available_master_depts = array_keys($all_master_menus);
    
    $department_menus = isset($all_master_menus[$superadmin_selected_department]) ? $all_master_menus[$superadmin_selected_department] : [];
} else {
    // Usuario normal
    if (!empty($current_department)) {
        $department_menus = $userModel->getMenusByDepartment($current_department);
    } else {
        $department_menus = [];
    }
}
?>
        <style>
        /* El estilo del menú lateral lo maneja neumorph-system.css */
        /* Sólo anulamos estilos inline residuales aquí */
        #layout-menu .app-brand-text {
            font-family: 'Inter', sans-serif;
            font-weight: 800 !important;
        }
        </style>
        <!-- Menu -->
        <aside id="layout-menu" class="layout-menu menu-vertical menu">
            <div class="app-brand demo" style="padding: 0rem !important;">
                <a href="<?php echo url('views/dashboard/dashboard.php'); ?>" class="app-brand-link">
                    <span class="app-brand-logo demo">
                        <img src="<?php echo img('logo.png'); ?>" alt="<?php echo PROJECT_NAME; ?>" width="64" height="64">
                    </span>
                    <span class="app-brand-text demo menu-text fw-semibold ms-2"  style="text-transform: capitalize;font-family: 'Inter';font-weight: bolder !important;font-size: 1.5rem;">SERAMER</span>
                </a>

                <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto" aria-label="Colapsar menú" title="Colapsar menú">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" style="color: var(--nm-primary, #6b7fe3);">
                        <path
                        d="M8.47365 11.7183C8.11707 12.0749 8.11707 12.6531 8.47365 13.0097L12.071 16.607C12.4615 16.9975 12.4615 17.6305 12.071 18.021C11.6805 18.4115 11.0475 18.4115 10.657 18.021L5.83009 13.1941C5.37164 12.7356 5.37164 11.9924 5.83009 11.5339L10.657 6.707C11.0475 6.31653 11.6805 6.31653 12.071 6.707C12.4615 7.09747 12.4615 7.73053 12.071 8.121L8.47365 11.7183Z"
                        fill-opacity="0.9" />
                        <path
                        d="M14.3584 11.8336C14.0654 12.1266 14.0654 12.6014 14.3584 12.8944L18.071 16.607C18.4615 16.9975 18.4615 17.6305 18.071 18.021C17.6805 18.4115 17.0475 18.4115 16.657 18.021L11.6819 13.0459C11.3053 12.6693 11.3053 12.0587 11.6819 11.6821L16.657 6.707C17.0475 6.31653 17.6805 6.31653 18.071 6.707C18.4615 7.09747 18.4615 7.73053 18.071 8.121L14.3584 11.8336Z"
                        fill-opacity="0.5" />
                    </svg>
                </a>
            </div>
            <div class="menu-inner-shadow"></div>

            <ul class="menu-inner py-1">
                <!-- Dashboard -->
                <li class="menu-item <?php echo (in_array(basename($_SERVER['PHP_SELF']), ['dashboard.php', 'settlement.php', 'collection.php'])) ? 'active' : ''; ?>" >
                    <a href="<?php echo url('views/dashboard/dashboard.php'); ?>" class="menu-link"  style="color:black">
                        <i class="menu-icon icon-base ri ri-home-smile-line"></i>
                        <div data-i18n="Dashboard">Dashboard</div>
                    </a>
                </li>

                <?php if (!empty($user_departments) && count($user_departments) > 1): ?>
                <!-- Selector de Departamento -->
                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text">Departamento</span>
                </li>
                <li class="menu-item"  style="color:black">
                    <select class="form-select form-select-sm" id="department-selector" onchange="changeDepartment(this.value)">
                        <?php foreach ($user_departments as $dept): ?>
                            <option value="<?php echo htmlspecialchars($dept['name']); ?>" 
                                    <?php echo ($dept['name'] == $current_department) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </li>
                <?php endif; ?>

                <!-- Solo mostrar Título de Departamento Actual si no es superadmin -->
                <?php if (empty($_SESSION['is_superadmin'])): ?>
                    <?php if (!empty($current_department)): ?>
                    <li class="menu-header small text-uppercase">
                        <span class="menu-header-text"><?php echo htmlspecialchars($current_department); ?></span>
                    </li>
                    <?php endif; ?>
                <?php else: ?>
                    <li class="menu-header small text-uppercase mt-3">
                        <span class="menu-header-text">Zona del Sistema (Superadmin)</span>
                    </li>
                    <li class="menu-item px-3 mb-2" style="color:black">
                        <form id="superadminDeptForm" method="GET" action="">
                            <select class="form-select form-select-sm border-primary shadow-sm" name="superadmin_dept" onchange="document.getElementById('superadminDeptForm').submit();" style="font-weight: bold; background-color: #f8f9fa;">
                                <?php foreach ($available_master_depts as $deptName): ?>
                                    <option value="<?php echo htmlspecialchars($deptName); ?>" <?php echo ($superadmin_selected_department === $deptName) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($deptName); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </li>
                <?php endif; ?>

                <!-- Menús -->
                <?php if (!empty($department_menus)): ?>
                    <?php foreach ($department_menus as $menu): ?>
                        
                        <?php if (isset($menu['submenu'])): ?>
                            <?php 
                            $isMenuOpen = isMenuGroupActive($menu); 
                            
                            // Prioridad Global: Si el usuario es superadmin y estamos en una sección 
                            // que suele ser global (como Control de Acceso), bajamos el flag de apertura
                            // para que se resalte abajo en "Configuraciones Globales"
                            if ($isMenuOpen && $is_superadmin) {
                                $globalPages = ['views/users/index.php', 'views/roles/index.php', 'views/departments/index.php'];
                                foreach ($menu['submenu'] as $sub) {
                                    if (in_array($sub['url'], $globalPages) && isMenuItemActive($sub['url'])) {
                                        $isMenuOpen = false;
                                        break;
                                    }
                                }
                            }
                            
                            // Verificar qué elementos del submenú son visibles
                            $visibleSubmenus = [];
                            foreach ($menu['submenu'] as $sub) {
                                if (empty($sub['hidden_in_menu'])) {
                                    $visibleSubmenus[] = $sub;
                                }
                            }
                            ?>
                            
                            <?php if (count($visibleSubmenus) === 1): ?>
                                <!-- Si solo hay un submenu visible, lo mostramos como un menú principal -->
                                <li class="menu-item <?php echo isMenuItemActive($visibleSubmenus[0]['url']) ? 'active' : ''; ?>">
                                    <a href="<?php echo url($visibleSubmenus[0]['url']); ?>" class="menu-link"  style="color:black">
                                        <i class="menu-icon icon-base <?php echo $menu['icon']; ?>"></i>
                                        <div data-i18n="<?php echo $menu['title']; ?>"><?php echo htmlspecialchars($menu['title']); ?></div>
                                    </a>
                                </li>
                            <?php elseif (count($visibleSubmenus) > 1): ?>
                                <!-- Menú con submenús completo -->
                                <li class="menu-item <?php echo $isMenuOpen ? 'active open' : ''; ?>" style="color:black">
                                    <a href="javascript:void(0);" class="menu-link menu-toggle"  style="color:black">
                                        <i class="menu-icon icon-base <?php echo $menu['icon']; ?>"></i>
                                        <div data-i18n="<?php echo $menu['title']; ?>"><?php echo htmlspecialchars($menu['title']); ?></div>
                                    </a>
                                    <ul class="menu-sub">
                                        <?php foreach ($visibleSubmenus as $submenu): ?>
                                            <li class="menu-item <?php echo isMenuItemActive($submenu['url']) ? 'active' : ''; ?>">
                                                <a href="<?php echo url($submenu['url']); ?>" class="menu-link"  style="color:black">
                                                    <div data-i18n="<?php echo $submenu['title']; ?>"><?php echo htmlspecialchars($submenu['title']); ?></div>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- Menú simple sin submenus -->
                            <li class="menu-item <?php echo isMenuItemActive($menu['url'] ?? '') ? 'active' : ''; ?>">
                                <a href="<?php echo url($menu['url'] ?? '#'); ?>" class="menu-link"  style="color:black">
                                    <i class="menu-icon icon-base <?php echo $menu['icon']; ?>"></i>
                                    <div data-i18n="<?php echo $menu['title']; ?>"><?php echo htmlspecialchars($menu['title']); ?></div>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php 
                $is_manager = $userModel->isManager($_SESSION['user_id'] ?? 0);
                if ($is_superadmin || $is_manager): 
                ?>
                <!-- Módulos Generales (Superadmin y Administradores) -->
                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text">Configuraciones Globales</span>
                </li>

                <li class="menu-item <?php echo isMenuItemActive('views/users/index.php') ? 'active' : ''; ?>">
                    <a href="<?php echo url('views/users/index.php'); ?>" class="menu-link" style="color:black">
                        <i class="menu-icon icon-base ri ri-user-settings-line"></i>
                        <div data-i18n="Usuarios">Usuarios</div>
                    </a>
                </li>

                <li class="menu-item <?php echo isMenuItemActive('views/roles/index.php') ? 'active' : ''; ?>">
                    <a href="<?php echo url('views/roles/index.php'); ?>" class="menu-link" style="color:black">
                        <i class="menu-icon icon-base ri ri-shield-keyhole-line"></i>
                        <div data-i18n="Roles">Roles</div>
                    </a>
                </li>

                <?php if ($is_superadmin): ?>
                <li class="menu-item <?php echo isMenuItemActive('views/departments/index.php') ? 'active' : ''; ?>">
                    <a href="<?php echo url('views/departments/index.php'); ?>" class="menu-link" style="color:black">
                        <i class="menu-icon icon-base ri ri-building-3-line"></i>
                        <div data-i18n="Departamentos">Departamentos</div>
                    </a>
                </li>

                <!-- Feature Oculta: Editor de Reportes -->
                <li class="menu-item <?php echo isMenuItemActive('views/reports/index.php') ? 'active' : ''; ?>">
                    <a href="<?php echo url('views/reports/index.php'); ?>" class="menu-link" style="color:black">
                        <i class="menu-icon icon-base ri ri-file-edit-line"></i>
                        <div data-i18n="Editor de Reportes">Editor de Reportes</div>
                    </a>
                </li>
                <?php endif; ?>
                <?php endif; ?>

               
            </ul>
        </aside>

        <script>
        function changeDepartment(departmentName) {
            // Enviar petición AJAX para cambiar departamento en sesión
            fetch('<?php echo url("ajax/change-department.php"); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    department: departmentName
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Recargar la página para mostrar nuevos menús
                    window.location.reload();
                } else {
                    console.error('Error cambiando departamento:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function showManagerMenuInfo() {
            Swal.fire({
                icon: 'info',
                title: 'Acceso Limitado',
                text: 'Como jefe de departamento, tiene acceso de solo lectura a la información de usuarios de tu departamento. Para realizar modificaciones, contacta al departamento de Recursos Humanos.',
                confirmButtonText: 'Entendido'
            });
        }
        </script>

        <!-- / Menu -->
        <!-- Nota: El toggle móvil del menú está en navigation-top.php (.layout-menu-toggle en el navbar).
             El .menu-mobile-toggler fue eliminado para que no haya toggle duplicado en móvil.
             neumorph-system.css oculta .menu-mobile-toggler con display:none. -->
