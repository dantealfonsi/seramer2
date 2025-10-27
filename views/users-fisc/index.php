<?php
// views/fiscalization_users/index.php

session_start();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/UsersFiscController.php';

$usersController = new UsersFiscController();
$result = $usersController->indexFiscalizationUsers();

// Extraer variables para la vista
$users = $result['users'];
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
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
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

<?php include __DIR__ . '/../layouts/footer.php'; ?>