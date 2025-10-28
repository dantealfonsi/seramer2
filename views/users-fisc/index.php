<?php
// views/fiscalization_users/index.php

session_start();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/UsersFiscController.php';
require_once __DIR__ . '/../../controllers/RolesController.php';

$usersController = new UsersFiscController();
$rol = new RolesController();
$result = $usersController->indexFiscalizationUsers();

// Extraer variables para la vista
$users = $result['users'];
$roles = $result['roles']; // ⬅️ Nuevo: roles disponibles
$page_title = $result['page_title'];

// Incluir header y layouts
require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title" style="font-size: 2rem;font-weight: 600;">
                            <i class="ri-team-line me-1"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                    </div>
                    
                    <div class="card-body">
                        <?php if (empty($users)): ?>
                            <div class="text-center py-5">
                                <i class="ri-alert-line text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-2">No hay usuarios activos registrados para Fiscalización.</h5>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID de Usuario</th>
                                            <th>Nombre Completo</th>
                                            <th>Cédula</th>
                                            <th>Usuario (Login)</th>
                                            <th>Email</th>
                                            <th>Estado</th>
                                            <th class="text-center" style="min-width: 300px;">Nivel de Fiscalización</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <?php if ($rol->hasPermission('USERS_AUDIT', 'r')): ?>
                                                <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($user['last_name'] . ' ' . $user['first_name']); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['id_number']); ?></td>
                                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <?php 
                                                        $status = $user['user_status'];
                                                        $badge_color = ($status === 'active') ? 'success' : 'danger';
                                                    ?>
                                                    <span class="badge bg-<?php echo $badge_color; ?>">
                                                        <?php echo htmlspecialchars(ucfirst($status)); ?>
                                                    </span>
                                                </td>
                                                <?php if ($rol->hasPermission('USERS_AUDIT', 'w')): ?>
                                                <td class="text-center">
                                                    <?php foreach ($roles as $role): ?>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input role-radio" 
                                                                type="radio" 
                                                                name="role_<?php echo htmlspecialchars($user['user_id']); ?>" 
                                                                id="role_<?php echo htmlspecialchars($user['user_id'] . '_' . $role['role_id']); ?>" 
                                                                value="<?php echo htmlspecialchars($role['role_id']); ?>"
                                                                data-user-id="<?php echo htmlspecialchars($user['user_id']); ?>"
                                                                data-role-name="<?php echo htmlspecialchars($role['role_name']); ?>"
                                                                <?php echo ($user['role_id'] == $role['role_id']) ? 'checked' : ''; ?>
                                                                <?php echo ($user['role_id'] == null && $role['role_name'] == 'oficina') ? 'checked' : ''; ?> 
                                                                >
                                                            <label class="form-check-label" for="role_<?php echo htmlspecialchars($user['user_id'] . '_' . $role['role_id']); ?>">
                                                                <?php echo htmlspecialchars(ucfirst($role['role_name'])); ?>
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </td> 
                                                <?php endif; ?>
                                                <?php endif; ?> 
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const radios = document.querySelectorAll('.role-radio');

    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            const userId = this.getAttribute('data-user-id');
            const roleId = this.value;
            const roleName = this.getAttribute('data-role-name');

            if (!confirm(`¿Estás seguro de que quieres asignar el rol '${roleName.toUpperCase()}' al usuario ID ${userId}?`)) {
                // Si cancela, revertir la selección (es complejo, pero se puede simplificar)
                // Por simplicidad, alertamos, pero en un entorno real deberías guardar la selección anterior.
                // Revertir a un estado conocido para evitar que la interfaz mienta:
                location.reload(); 
                return;
            }

            // Realizar la petición AJAX
            fetch('ajax_update_role.php', { // Tendrás que crear este archivo de endpoint
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_role&user_id=${userId}&role_id=${roleId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                } else {
                    alert('❌ Error: ' + data.message);
                    location.reload(); // Recargar si hay error para resetear la interfaz
                }
            })
            .catch(error => {
                console.error('Error en la conexión AJAX:', error);
                alert('❌ Error de conexión al servidor.');
                location.reload();
            });
        });
    });
});
</script>


<?php include __DIR__ . '/../layouts/footer.php'; ?>