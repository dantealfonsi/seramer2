<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Abrir Caja: <?= htmlspecialchars($cashRegister['name']) ?></h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="ri ri-information-line me-2"></i>
                    <strong>Usuario Asignado:</strong> <?= htmlspecialchars($cashRegister['assigned_user_name'] ?? $cashRegister['username'] ?? 'N/A') ?>
                </div>
                
                <form action="<?= $app['url'] ?>/dailycash/storeopen" method="POST">
                    <?= \Core\Security::csrfField() ?>
                    <input type="hidden" name="cash_register_id" value="<?= $cashRegister['id'] ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" 
                                    class="form-control" 
                                    id="initial_amount" 
                                    name="initial_amount" 
                                    step="0.01" 
                                    min="0" 
                                    value="0.00"
                                    required />
                                <label for="initial_amount">Monto Inicial (Bs.) *</label>
                            </div>
                            <small class="text-muted">
                                <i class="ri ri-information-line"></i>
                                Ingrese el monto inicial con el que se abrirá la caja
                            </small>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-success me-2">
                                <i class="ri ri-lock-unlock-line me-1"></i>
                                Abrir Caja
                            </button>
                            <a href="<?= $app['url'] ?>/dailycash/index" class="btn btn-outline-secondary">
                                <i class="ri ri-arrow-left-line me-1"></i>
                                Cancelar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

