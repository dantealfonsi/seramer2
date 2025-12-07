<?php
require_once __DIR__ . '/../../controllers/DailyCashController.php';

$controller = new DailyCashController();
$data = $controller->index();
$cashRegisters = $data['cashRegisters'];
$currentUserId = $data['currentUserId'];

$page_title = $data['page_title'];

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <?php if (isset($_SESSION['flash_message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?>">
                        <?php echo $_SESSION['flash_message']['message']; ?>
                        <?php unset($_SESSION['flash_message']); ?>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo htmlspecialchars($page_title); ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($cashRegisters)): ?>
                            <div class="alert alert-info">No hay cajas registradas en el sistema.</div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($cashRegisters as $register): ?>
                                    <div class="col-md-4 mb-4">
                                        <div class="card h-100 border-<?php echo $register['is_open'] ? 'success' : 'secondary'; ?>">
                                            <div class="card-header bg-<?php echo $register['is_open'] ? 'success' : 'light'; ?> <?php echo $register['is_open'] ? 'text-white' : ''; ?>">
                                                <h5 class="mb-0"><?php echo htmlspecialchars($register['name']); ?></h5>
                                            </div>
                                            <div class="card-body">
                                                <p><strong>Usuario Asignado:</strong> <?php echo htmlspecialchars($register['assigned_user_name']); ?></p>
                                                <p><strong>Estado:</strong> 
                                                    <span class="badge <?php echo $register['is_open'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                        <?php echo $register['is_open'] ? 'ABIERTA' : 'CERRADA'; ?>
                                                    </span>
                                                </p>
                                                
                                                <?php if ($register['is_open']): ?>
                                                    <p>
                                                        <strong>Abierta desde:</strong><br>
                                                        <?php echo date('d/m/Y', strtotime($register['open_cash']['open_date'])); ?> 
                                                        <?php echo date('H:i', strtotime($register['open_cash']['open_time'])); ?>
                                                    </p>
                                                    <p><strong>Monto Inicial:</strong> Bs. <?php echo number_format($register['open_cash']['initial_amount'], 2); ?></p>
                                                <?php endif; ?>
                                                
                                                <div class="mt-3">
                                                    <?php if ($register['can_operate']): ?>
                                                        <?php if ($register['is_open']): ?>
                                                            <a href="close.php?id=<?php echo $register['open_cash']['id']; ?>" class="btn btn-danger btn-block w-100">
                                                                Cerrar Caja
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="open.php?id=<?php echo $register['id']; ?>" class="btn btn-success btn-block w-100">
                                                                Abrir Caja
                                                            </a>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <?php if ($register['user_id'] == 0): ?>
                                                            <span class="text-muted">Caja sin asignar</span>
                                                        <?php else: ?>
                                                            <span class="text-muted">No asignada a usted</span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
