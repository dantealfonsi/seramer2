<?php
require_once __DIR__ . '/../../controllers/RolesController.php';
require_once __DIR__ . '/../../models/RoleModel.php';
require_once __DIR__ . '/../../models/UserModel.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rolesController = new RolesController();
$roleModel = new RoleModel();
$userModel = new UserModel();

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php?error=invalid_role");
    exit;
}

$role = $roleModel->getById($id);
if (!$role) {
    header("Location: index.php?error=role_not_found");
    exit;
}

$user_id = $_SESSION['user_id'];
$is_superadmin = $userModel->isSuperadmin($user_id);
$managerInfo = $userModel->isManager($user_id);

// Check basic access
if (!$is_superadmin && !$managerInfo) {
    header("Location: ../dashboard/dashboard.php");
    exit;
}

// Dept admin check
if (!$is_superadmin && $role['department_id'] != $managerInfo['id']) {
    header("Location: index.php?error=no_permission");
    exit;
}

$is_admin_role = ($role['name'] === 'admin');

// Processing POST
$params = [];
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $params = $_POST;
    $params['_method'] = 'POST';
    
    $result = $rolesController->update($id, $params);
    
    if ($result['success']) {
        header("Location: index.php?success=role_updated");
        exit;
    } else {
        $message = $result['message'];
    }
}

// Get all master menu nodes for the whole system to support mixed roles
$all_menus = $userModel->getMasterMenus();
$current_permissions = !empty($role['menu_json']) ? json_decode($role['menu_json'], true) : [];
if (!is_array($current_permissions)) $current_permissions = [];

$page_title = 'Editar Rol: ' . htmlspecialchars($role['name']);

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
                                <i class="ri-shield-edit-line" style="color: #696cff; font-size: 2rem;"></i>
                            </div>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="index.php" class="btn btn-outline-secondary btn-sm">
                            <i class="ri-arrow-left-line mr-1"></i> Volver
                        </a>
                    </div>

                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($is_admin_role): ?>
                            <div class="alert alert-warning mb-4">
                                <i class="ri-error-warning-line me-2"></i>
                                Este es el rol de Administrador. Sus propiedades base no pueden ser modificadas.
                            </div>
                        <?php endif; ?>

                        <?php if (!$is_superadmin): ?>
                            <div class="alert alert-info mb-4">
                                <i class="ri-information-line me-2"></i>
                                Como administrador de departamento, solo puede modificar los permisos de nivel de confianza.
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre del Rol <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? $role['name']); ?>" 
                                       <?php echo (!$is_superadmin || $is_admin_role) ? 'readonly' : 'required'; ?>>
                            </div>

                            <div class="mb-4">
                                <label for="description" class="form-label">Descripción</label>
                                <textarea class="form-control" id="description" name="description" rows="3" 
                                          <?php echo (!$is_superadmin || $is_admin_role) ? 'readonly' : ''; ?>><?php echo htmlspecialchars($_POST['description'] ?? $role['description']); ?></textarea>
                            </div>

                            <h6 class="mb-3">Permisos / Nivel de Confianza</h6>
                            <?php 
                            $is_full_admin = ($role['can_read'] && $role['can_write'] && $role['can_modify'] && $role['can_delete']);
                            ?>
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="form-check form-switch custom-switch-lg p-3 bg-light rounded border">
                                        <input class="form-check-input ms-0 me-3" type="checkbox" id="is_admin_toggle" onchange="toggleAllPermissions(this)" <?php echo $is_full_admin ? 'checked' : ''; ?>>
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
                                        <input class="form-check-input" type="checkbox" id="can_read" name="can_read" value="1" 
                                               <?php echo (isset($_POST['can_read']) || (!$_POST && $role['can_read'])) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="can_read">Lectura</label>
                                    </div>
                                    <small class="text-muted d-block">Ver registros</small>
                                </div>
                                <div class="col-md-6 col-lg-3 mb-2">
                                    <div class="form-check form-switch custom-switch-lg">
                                        <input class="form-check-input" type="checkbox" id="can_write" name="can_write" value="1" 
                                               <?php echo (isset($_POST['can_write']) || (!$_POST && $role['can_write'])) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="can_write">Escritura</label>
                                    </div>
                                    <small class="text-muted d-block">Crear registros</small>
                                </div>
                                <div class="col-md-6 col-lg-3 mb-2">
                                    <div class="form-check form-switch custom-switch-lg">
                                        <input class="form-check-input" type="checkbox" id="can_modify" name="can_modify" value="1" 
                                               <?php echo (isset($_POST['can_modify']) || (!$_POST && $role['can_modify'])) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="can_modify">Modificación</label>
                                    </div>
                                    <small class="text-muted d-block">Editar registros</small>
                                </div>
                                <div class="col-md-6 col-lg-3 mb-2">
                                    <div class="form-check form-switch custom-switch-lg">
                                        <input class="form-check-input" type="checkbox" id="can_delete" name="can_delete" value="1" 
                                               <?php echo (isset($_POST['can_delete']) || (!$_POST && $role['can_delete'])) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="can_delete">Eliminación</label>
                                    </div>
                                    <small class="text-muted d-block">Borrar/Anular</small>
                                </div>
                            </div>
                            
                            <!-- MENU PERMISSIONS -->
                            <h6 class="mb-3 mt-4 border-top pt-3">Accesos del Menú</h6>
                            <p class="text-muted small mb-3">Seleccione a qué módulos y apartados del sistema tendrá acceso este rol.</p>
                            
                            <div class="row">
                                <?php 
                                $is_mixto = (isset($role['department_name']) && strtolower($role['department_name']) === 'mixto');
                                $filtered_menus = [];
                                foreach ($all_menus as $deptName => $nodes) {
                                    if ($is_mixto || $deptName === ($role['department_name'] ?? '')) {
                                        $filtered_menus[$deptName] = $nodes;
                                    }
                                }
                                
                                if (empty($filtered_menus)): 
                                ?>
                                    <div class="col-12"><div class="alert alert-warning">No hay menús disponibles o aplicables para este departamento.</div></div>
                                <?php else: ?>
                                    <div class="col-12">
                                        <div class="accordion" id="menuAccordion">
                                            <?php 
                                            $index = 0;
                                            foreach ($filtered_menus as $deptName => $nodes): 
                                                if (empty($nodes)) continue;
                                            ?>
                                                <h6 class="mt-3 mb-2 text-primary fw-bold" style="font-size: 0.9rem; text-transform: uppercase;">Módulo: <?php echo htmlspecialchars($deptName); ?></h6>
                                                <?php foreach ($nodes as $node): 
                                                    $index++;
                                                    $is_node_checked = in_array($node['title'], $current_permissions);
                                                ?>
                                                    <div class="accordion-item shadow-none border mb-2 rounded">
                                                        <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                                            <div class="d-flex align-items-center w-100 px-3 py-2 bg-light">
                                                                <div class="form-check me-3">
                                                                    <input class="form-check-input parent-checkbox" type="checkbox" 
                                                                           name="menu_permissions[]" 
                                                                           value="<?php echo htmlspecialchars($node['title']); ?>" 
                                                                           id="parent_<?php echo $index; ?>"
                                                                           <?php echo $is_node_checked ? 'checked' : ''; ?>
                                                                           <?php echo ($is_admin_role) ? 'disabled checked' : ''; ?>>
                                                                </div>
                                                                <button class="accordion-button collapsed p-0 bg-light flex-grow-1 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="false" aria-controls="collapse<?php echo $index; ?>">
                                                                    <i class="me-2 <?php echo htmlspecialchars($node['icon'] ?? 'ri-folder-line'); ?>"></i>
                                                                    <?php echo htmlspecialchars($node['title']); ?>
                                                                </button>
                                                            </div>
                                                        </h2>
                                                        
                                                        <?php if (isset($node['submenu'])): ?>
                                                        <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $index; ?>">
                                                            <div class="accordion-body pt-1 pb-3 px-4">
                                                                <div class="row ms-4">
                                                                    <?php foreach ($node['submenu'] as $subIndex => $subNode): ?>
                                                                        <?php 
                                                                            $sub_value = $node['title'] . '::' . $subNode['title'];
                                                                            $is_sub_checked = in_array($sub_value, $current_permissions);
                                                                        ?>
                                                                        <div class="col-md-6 mb-2">
                                                                            <div class="form-check">
                                                                                <input class="form-check-input child-checkbox" type="checkbox" 
                                                                                       name="menu_permissions[]" 
                                                                                       value="<?php echo htmlspecialchars($sub_value); ?>" 
                                                                                       id="child_<?php echo $index; ?>_<?php echo $subIndex; ?>"
                                                                                       data-parent="parent_<?php echo $index; ?>"
                                                                                       <?php echo $is_sub_checked ? 'checked' : ''; ?>
                                                                                       <?php echo ($is_admin_role) ? 'disabled checked' : ''; ?>>
                                                                                <label class="form-check-label" for="child_<?php echo $index; ?>_<?php echo $subIndex; ?>">
                                                                                    <?php echo htmlspecialchars($subNode['title']); ?>
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Checkbox Logic -->
                            <script>
                            function toggleAllPermissions(toggle) {
                                const isChecked = toggle.checked;
                                document.getElementById('can_read').checked = isChecked;
                                document.getElementById('can_write').checked = isChecked;
                                document.getElementById('can_modify').checked = isChecked;
                                document.getElementById('can_delete').checked = isChecked;
                            }

                            document.addEventListener('DOMContentLoaded', function() {
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
                            });
                            </script>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line mr-1"></i> Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
