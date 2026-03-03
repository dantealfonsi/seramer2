<?php
// views/roles/index.php

session_start();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/UsersFiscController.php';
require_once __DIR__ . '/../../controllers/RolesController.php';

$controller = new UsersFiscController();
$rol = new RolesController();
$result = $controller->indexRoles();

$roles = $result['roles'];
$areas = $result['areas'];
$page_title = $result['page_title'];

// Incluir layouts
require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';

// Array para mapear la posición del carácter al tipo de permiso
$permission_types = [
    0 => 'r', 1 => 'w', 2 => 'x', // Grupo 1
    3 => 'r', 4 => 'w', 5 => 'x', // Grupo 2
    6 => 'r', 7 => 'w', 8 => 'x', // Grupo 3
];
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title d-flex align-items-center" style="font-size: 1.4rem;font-weight: 600;">
                            <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="ri-lock-2-line" style="color: #696cff; font-size: 1.5rem;"></i></div>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                    </div>
                    
                    <div class="card-body">
                        <div class="alert alert-info mb-4 shadow-sm border-start border-info border-5">
                            <i class="ri-information-line"></i>
                            <div class="alert-content">
                                <h6 class="alert-heading fw-bold">Estructura de Permisos (rwx)</h6>
                                <p class="mb-2">
                                    La máscara de permisos (9 caracteres) se divide en tres grupos de 3: <strong>Infracciones (rwx)</strong>, <strong>Tasas/Configuración (rwx)</strong> y <strong>Usuarios/Auditoría (rwx)</strong>.
                                </p>
                                <hr>
                                <p class="mb-0 small">
                                    Se refiere a los permisos básicos de un archivo o directorio: <strong>Read (leer)</strong>, <strong>Write (escribir)</strong> y <strong>eXecute (ejecutar)</strong>. Estos permisos se agrupan en tres categorías: el propietario, el grupo y los demás usuarios, y definen qué acciones se pueden realizar sobre el recurso (por ejemplo, <strong>rwx</strong> para el propietario significa lectura, escritura y ejecución).
                                </p>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Rol</th>
                                        <th>Máscara (Actual)</th>
                                        <?php $area_index = 0; ?>
                                        <?php foreach ($areas as $key => $name): ?>
                                            <th class="text-center" colspan="3">
                                                <?php echo htmlspecialchars($name); ?>
                                            </th>
                                            <?php $area_index++; ?>
                                        <?php endforeach; ?>
                                    </tr>
                                    <tr>
                                        <th colspan="2"></th>
                                        <?php for ($i = 0; $i < count($areas) * 3; $i++): ?>
                                            <th class="text-center permission-type" data-index="<?php echo $i; ?>">
                                                <?php echo htmlspecialchars(strtoupper($permission_types[$i])); ?>
                                            </th>
                                        <?php endfor; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if ($rol->hasPermission('USERS_AUDIT', 'r')):
                                    foreach ($roles as $role): ?>
                                        <tr data-role-id="<?php echo htmlspecialchars($role['role_id']); ?>">
                                            <td class="fw-bold"><?php echo htmlspecialchars(ucfirst($role['role_name'])); ?></td>
                                            <td class="text-monospace">
                                                <span class="badge bg-secondary mask-display" id="mask-<?php echo $role['role_id']; ?>">
                                                    <?php echo htmlspecialchars($role['permissions_mask']); ?>
                                                </span>
                                            </td>
                                            
                                            <?php 
                                                // Iterar sobre los 9 permisos individuales si tienes permiso
                                                if ($rol->hasPermission('USERS_AUDIT', 'w')):
                                                $mask = $role['permissions_mask'];
                                                for ($i = 0; $i < 9; $i++): 
                                                    $char = substr($mask, $i, 1);
                                                    $isChecked = ($char !== '-');
                                                    $permission = $permission_types[$i];
                                                    if($role['role_name'] == "administrador" && $_SESSION['user_nivel']=='1') {
                                                        $isDisabled = "disabled";
                                                    } else {
                                                        $isDisabled = "";
                                                    }                                                    

                                            ?>
                                                <td class="text-center p-0">
                                                    <div class="form-check form-check-inline m-0">
                                                        <input class="form-check-input permission-checkbox" 
                                                               type="checkbox" 
                                                               id="perm_<?php echo $role['role_id'] . '_' . $i; ?>"
                                                               data-role-id="<?php echo $role['role_id']; ?>"
                                                               data-index="<?php echo $i; ?>"
                                                               data-permission-char="<?php echo $permission; ?>"
                                                               <?php echo $isChecked ? 'checked' : ''; ?>
                                                               <?php echo ' '. $isDisabled; ?>>
                                                    </div>
                                                </td>
                                            <?php endfor; endif;?>
                                            
                                        </tr>
                                    <?php endforeach; endif;?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
/*inyectamos el nivel del usuario*/

document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.permission-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const roleId = this.getAttribute('data-role-id');
            const row = document.querySelector(`tr[data-role-id="${roleId}"]`);
            
            // 1. CONSTRUIR LA NUEVA MÁSCARA
            let newMaskArray = [];
            // Recorre los 9 checkboxes de esa fila en orden (0 a 8)
            for (let i = 0; i < 9; i++) {
                const cb = row.querySelector(`input[data-index="${i}"]`);
                // Si está chequeado, usa el carácter de permiso (r, w, o x), sino usa '-'
                if (cb) {
                    newMaskArray.push(cb.checked ? cb.getAttribute('data-permission-char') : '-');
                }
            }
            const newMask = newMaskArray.join('');

            // 2. CONFIRMACIÓN Y ACTUALIZACIÓN
            if (!confirm(`¿Estás seguro de actualizar los permisos del rol ID ${roleId} a la máscara: ${newMask}?`)) {
                // Revertir el estado del checkbox si el usuario cancela
                this.checked = !this.checked; 
                return;
            }

            // 3. Petición AJAX (Enviar al endpoint de roles)
            fetch('ajax_update_role.php', { // Tendrás que crear este archivo de endpoint
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_permissions&role_id=${roleId}&permissions_mask=${newMask}`
            })
            .then(response => response.json())
            .then(data => {
                const maskDisplay = document.getElementById(`mask-${roleId}`);
                if (data.success) {
                    maskDisplay.textContent = newMask; // Actualizar la máscara en la interfaz
                    maskDisplay.classList.add('bg-success');
                    maskDisplay.classList.remove('bg-secondary');
                    setTimeout(() => {
                        maskDisplay.classList.remove('bg-success');
                        maskDisplay.classList.add('bg-secondary');
                    }, 1500); // Muestra un feedback de éxito temporal
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: '❌ Error: ' + data.message,
                        confirmButtonText: 'Entendido'
                    });
                    this.checked = !this.checked; // Revertir si hubo error en el servidor
                }
            })
            .catch(error => {
                console.error('Error de conexión AJAX:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: '❌ Error de conexión al servidor.',
                    confirmButtonText: 'Entendido'
                });
                this.checked = !this.checked; // Revertir
            });
        });
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>