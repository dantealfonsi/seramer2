<div class="row">
    <?php foreach ($cashRegisters as $cashRegister): ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><?= htmlspecialchars($cashRegister['name']) ?></h5>
                <?php if ($cashRegister['is_open']): ?>
                    <span class="badge bg-label-success">Abierta</span>
                <?php else: ?>
                    <span class="badge bg-label-secondary">Cerrada</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Usuario Asignado:</strong><br>
                    <div class="d-flex align-items-center mt-1">
                        <div class="avatar avatar-sm me-2">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                <?= strtoupper(substr($cashRegister['username'] ?? 'U', 0, 1)) ?>
                            </span>
                        </div>
                        <div>
                            <div class="fw-semibold"><?= htmlspecialchars($cashRegister['assigned_user_name'] ?? $cashRegister['username'] ?? 'N/A') ?></div>
                            <small class="text-muted"><?= htmlspecialchars($cashRegister['username'] ?? '') ?></small>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($cashRegister['description'])): ?>
                <div class="mb-3">
                    <strong>Descripción:</strong><br>
                    <small class="text-muted"><?= htmlspecialchars($cashRegister['description']) ?></small>
                </div>
                <?php endif; ?>
                
                <?php if ($cashRegister['is_open'] && $cashRegister['open_cash']): ?>
                <div class="mb-3">
                    <strong>Monto Inicial:</strong><br>
                    <span class="text-success">Bs. <?= number_format($cashRegister['open_cash']['initial_amount'], 2) ?></span>
                </div>
                <div class="mb-3">
                    <strong>Fecha de Apertura:</strong><br>
                    <small class="text-muted"><?= date('d/m/Y H:i', strtotime($cashRegister['open_cash']['open_date'] . ' ' . $cashRegister['open_cash']['open_time'])) ?></small>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <?php if ($cashRegister['can_operate']): ?>
                    <?php if (!empty($cashRegister['is_open']) && !empty($cashRegister['open_cash']) && !empty($cashRegister['open_cash']['id'])): ?>
                        <a href="<?= $app['url'] ?>/dailycash/close/<?= $cashRegister['open_cash']['id'] ?>" class="btn btn-danger w-100">
                            <i class="ri ri-lock-line me-1"></i>
                            Cerrar Caja
                        </a>
                    <?php else: ?>
                        <a href="<?= $app['url'] ?>/dailycash/open/<?= $cashRegister['id'] ?>" class="btn btn-success w-100">
                            <i class="ri ri-lock-unlock-line me-1"></i>
                            Abrir Caja
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <button class="btn btn-secondary w-100" disabled>
                        <i class="ri ri-lock-line me-1"></i>
                        No asignada a ti
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (empty($cashRegisters)): ?>
<div class="alert alert-info">
    <i class="ri ri-information-line me-2"></i>
    No hay cajas registradas. <a href="<?= $app['url'] ?>/cashregister/create">Crear una caja</a>
</div>
<?php endif; ?>

<?php ob_start(); ?>
<script>
$(document).ready(function() {
    // Configurar título de la tarjeta
    $('.head-label').html('<h5 class="card-title mb-0">Apertura y Cierre de Caja</h5>');
});
</script>
<?php $pageScripts = ob_get_clean(); ?>

