<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Establecer Tasa de Euro</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning" role="alert">
                    <h6 class="alert-heading mb-2">
                        <i class="ri ri-error-warning-line me-2"></i>Proceso Automático
                    </h6>
                    <p class="mb-0">
                        Al establecer la tasa del euro para un mes específico, el sistema:
                    </p>
                    <ul class="mb-0">
                        <li>Actualizará <strong>TODAS las facturas pendientes</strong> del mes seleccionado</li>
                        <li>Calculará automáticamente el monto a pagar usando la fórmula: (Monto Base EUR × Tasa BS) ÷ Payment Count</li>
                        <li>Si ya existe una tasa para el mes, la actualizará</li>
                    </ul>
                </div>
                
                <form action="<?= $app['url'] ?>/fiscalyear/storerate" method="POST" class="mt-4">
                    <?= \Core\Security::csrfField() ?>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" id="month" name="month" required>
                                    <option value="">Seleccionar...</option>
                                    <?php foreach ($months as $num => $name): ?>
                                        <option value="<?= $num ?>"><?= $name ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="month">Mes *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" class="form-control" id="year" name="year" placeholder="2025" min="2020" max="2050" value="<?= date('Y') ?>" required />
                                <label for="year">Año *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" class="form-control" id="bs_value" name="bs_value" placeholder="50.5000" step="0.0001" min="0" required />
                                <label for="bs_value">Valor en Bs. *</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="ri ri-save-line me-1"></i>
                                Establecer Tasa
                            </button>
                            <a href="<?= $app['url'] ?>/fiscalyear/rates" class="btn btn-outline-secondary">
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

