<?php
require_once __DIR__ . '/../../controllers/RolesController.php';
require_once __DIR__ . '/../../models/UserModel.php';

$rolesController = new RolesController();
$userModel = new UserModel();

// Call create method of controller to handle POST if submitted
$params = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $params = $_POST;
    $params['_method'] = 'POST';
}

$result = $rolesController->create($params);

// Check if user is superadmin (done loosely here, properly enforced in controller)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$is_superadmin = !empty($_SESSION['is_superadmin']);

if (!$is_superadmin) {
    header("Location: index.php?error=no_permission");
    exit;
}

if ($result['success']) {
    header("Location: index.php?success=role_created");
    exit;
}

$message = $result['message'] ?? '';
$departments = $userModel->getAllDepartments();

// Get ALL menus across the system to allow mixed role assignments
$all_department_menus_json = json_encode($userModel->getMasterMenus());
$dept_map = [];
foreach ($departments as $d) {
    $dept_map[$d['id']] = $d['name'];
}
$dept_map_json = json_encode($dept_map);

$page_title = 'Crear Nuevo Rol';

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 d-flex align-items-center" style="font-size: 2rem;font-weight: 600;">
                            <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; background-color: #e7e7ff !important;">
                                <i class="ri-shield-add-line" style="color: #696cff; font-size: 2rem;"></i>
                            </div>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="index.php" class="btn btn-outline-secondary btn-sm">
                            <i class="ri-arrow-left-line mr-1"></i> Volver
                        </a>
                    </div>

                    <div class="card-body">
                        <?php if ($message && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="department_id" class="form-label">Departamento <span class="text-danger">*</span></label>
                                <select class="form-select" id="department_id" name="department_id" required>
                                    <option value="">Seleccione un departamento...</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['id']; ?>" <?php echo (isset($_POST['department_id']) && $_POST['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($dept['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre del Rol <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                                <div class="form-text">El nombre debe ser único por departamento. No use "admin" (reservado).</div>
                            </div>

                            <div class="mb-4">
                                <label for="description" class="form-label">Descripción</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            </div>

                            <h6 class="mb-3">Permisos / Nivel de Confianza</h6>
                            
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="form-check form-switch custom-switch-lg p-3 bg-light rounded border">
                                        <input class="form-check-input ms-0 me-3" type="checkbox" id="is_admin_toggle" onchange="toggleAllPermissions(this)">
                                        <label class="form-check-label fw-bold text-primary" for="is_admin_toggle" style="font-size: 1.1em;">
                                            🌟 Administrador (Acceso Total)
                                        </label>
                                        <small class="d-block text-muted ms-5 mt-1">Activar esta opción marcará todos los niveles de confianza y convertirá este rol en administrador de su departamento.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6 col-lg-3 mb-2">
                                    <div class="form-check form-switch custom-switch-lg">
                                        <input class="form-check-input" type="checkbox" id="can_read" name="can_read" value="1" <?php echo isset($_POST['can_read']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="can_read">Lectura</label>
                                    </div>
                                    <small class="text-muted d-block">Ver registros</small>
                                </div>
                                <div class="col-md-6 col-lg-3 mb-2">
                                    <div class="form-check form-switch custom-switch-lg">
                                        <input class="form-check-input" type="checkbox" id="can_write" name="can_write" value="1" <?php echo isset($_POST['can_write']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="can_write">Escritura</label>
                                    </div>
                                    <small class="text-muted d-block">Crear registros</small>
                                </div>
                                <div class="col-md-6 col-lg-3 mb-2">
                                    <div class="form-check form-switch custom-switch-lg">
                                        <input class="form-check-input" type="checkbox" id="can_modify" name="can_modify" value="1" <?php echo isset($_POST['can_modify']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="can_modify">Modificación</label>
                                    </div>
                                    <small class="text-muted d-block">Editar registros</small>
                                </div>
                                <div class="col-md-6 col-lg-3 mb-2">
                                    <div class="form-check form-switch custom-switch-lg">
                                        <input class="form-check-input" type="checkbox" id="can_delete" name="can_delete" value="1" <?php echo isset($_POST['can_delete']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="can_delete">Eliminación</label>
                                    </div>
                                    <small class="text-muted d-block">Borrar/Anular</small>
                                </div>
                            </div>
                            
                            <!-- MENU PERMISSIONS CONTAINER -->
                            <div id="menu-permissions-container" class="mt-4 border-top pt-3">
                                <h6 class="mb-3">Accesos del Menú</h6>
                                <p class="text-muted small mb-3">Seleccione a qué módulos y apartados del sistema tendrá acceso este rol. Puede elegir accesos de múltiples departamentos para crear roles mixtos.</p>
                                
                                <div class="row">
                                    <div class="col-12" id="menuAccordion">
                                        <!-- Checkboxes will be rendered here dynamically -->
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line mr-1"></i> Guardar Rol
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Checkbox & Dynamic Menu Logic -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const departmentSelect = document.getElementById('department_id');
    const menuContainer = document.getElementById('menu-permissions-container');
    const menuAccordion = document.getElementById('menuAccordion');
    const allMenus = <?php echo $all_department_menus_json; ?>;
    const deptMap = <?php echo $dept_map_json; ?>;

    function renderMenuChecks() {
        const deptId = departmentSelect.value;
        const deptName = deptId ? deptMap[deptId] : '';
        
        menuAccordion.innerHTML = '';
        if (!deptId || !allMenus || Object.keys(allMenus).length === 0) {
            menuContainer.style.display = 'none';
            return;
        }

        const isMixto = (deptName && deptName.toLowerCase() === 'mixto');
        menuContainer.style.display = 'block';

        let html = '<div class="accordion" id="renderedMenu">';
        let index = 0;
        let anyMenuShown = false;
        
        for (const [moduleName, nodes] of Object.entries(allMenus)) {
            // Only show the menus if it's "Mixto" or the module name matches the selected department
            if (!isMixto && moduleName !== deptName) {
                continue;
            }
            
            if (!nodes || nodes.length === 0) continue;
            
            anyMenuShown = true;
            html += `<h6 class="mt-3 mb-2 text-primary fw-bold" style="font-size: 0.9rem; text-transform: uppercase;">Módulo: ${moduleName}</h6>`;
            
            nodes.forEach((node) => {
                index++;
                html += `
                    <div class="accordion-item shadow-none border mb-2 rounded">
                        <h2 class="accordion-header" id="heading${index}">
                            <div class="d-flex align-items-center w-100 px-3 py-2 bg-light">
                                <div class="form-check me-3">
                                    <input class="form-check-input parent-checkbox" type="checkbox" 
                                           name="menu_permissions[]" 
                                           value="${node.title}" 
                                           id="parent_${index}">
                                </div>
                                <button class="accordion-button collapsed p-0 bg-light flex-grow-1 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${index}" aria-expanded="false" aria-controls="collapse${index}">
                                    <i class="me-2 ${node.icon ? node.icon : 'ri-folder-line'}"></i>
                                    ${node.title}
                                </button>
                            </div>
                        </h2>
                `;

                if (node.submenu) {
                    html += `
                        <div id="collapse${index}" class="accordion-collapse collapse" aria-labelledby="heading${index}">
                            <div class="accordion-body pt-1 pb-3 px-4">
                                <div class="row ms-4">
                    `;
                    node.submenu.forEach((subNode, subIndex) => {
                        let subValue = `${node.title}::${subNode.title}`;
                        html += `
                            <div class="col-md-6 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input child-checkbox" type="checkbox" 
                                           name="menu_permissions[]" 
                                           value="${subValue}" 
                                           id="child_${index}_${subIndex}"
                                           data-parent="parent_${index}">
                                    <label class="form-check-label" for="child_${index}_${subIndex}">
                                        ${subNode.title}
                                    </label>
                                </div>
                            </div>
                        `;
                    });
                    html += `</div></div></div>`;
                }
                html += `</div>`;
            });
        }
        html += '</div>';
        
        if (!anyMenuShown) {
            menuAccordion.innerHTML = '<div class="alert alert-warning">No hay menús registrados para este departamento.</div>';
        } else {
            menuAccordion.innerHTML = html;
        }

        attachCheckboxListeners();
    }

    function attachCheckboxListeners() {
        // When parent checked/unchecked -> logic for children
        const parentCheckboxes = document.querySelectorAll('.parent-checkbox');
        parentCheckboxes.forEach(parent => {
            parent.addEventListener('change', function() {
                const parentId = this.id;
                const children = document.querySelectorAll(`.child-checkbox[data-parent="${parentId}"]`);
                children.forEach(child => {
                    if(!child.disabled) {
                        child.checked = this.checked;
                    }
                });
            });
        });

        // When child checked -> ensure parent is checked
        const childCheckboxes = document.querySelectorAll('.child-checkbox');
        childCheckboxes.forEach(child => {
            child.addEventListener('change', function() {
                if (this.checked) {
                    const parentId = this.getAttribute('data-parent');
                    const parent = document.getElementById(parentId);
                    if (parent && !parent.disabled) {
                        parent.checked = true;
                    }
                }
            });
        });
    }

    departmentSelect.addEventListener('change', function() {
        renderMenuChecks();
    });

    // Initial render of all menus if department is already selected
    if (departmentSelect.value) {
        renderMenuChecks();
    }
});

function toggleAllPermissions(toggle) {
    const isChecked = toggle.checked;
    document.getElementById('can_read').checked = isChecked;
    document.getElementById('can_write').checked = isChecked;
    document.getElementById('can_modify').checked = isChecked;
    document.getElementById('can_delete').checked = isChecked;
    
    // Si queremos habilitar todos los menús al hacer clic, podríamos, pero de momento
    // esto cumple con encender los 4 niveles de confianza.
}
</script>

<style>
.custom-switch-lg .form-check-input {
    width: 3em;
    height: 1.5em;
    margin-right: 10px;
}
.custom-switch-lg {
    display: flex;
    align-items: center;
}
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
