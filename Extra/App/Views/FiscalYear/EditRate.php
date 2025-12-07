<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Editar Tasa de Euro</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning" role="alert">
                    <h6 class="alert-heading mb-2">
                        <i class="ri ri-error-warning-line me-2"></i>Actualización Automática
                    </h6>
                    <p class="mb-0">
                        Al modificar esta tasa, el sistema:
                    </p>
                    <ul class="mb-0">
                        <li>Actualizará <strong>TODAS las facturas pendientes</strong> de <?= ucfirst($rate['month']) ?> <?= $rate['year'] ?></li>
                        <li>Recalculará los montos a pagar usando la nueva tasa</li>
                    </ul>
                </div>
                
                <form action="<?= $app['url'] ?>/fiscalyear/updaterate/<?= $rate['id'] ?>" method="POST" class="mt-4">
                    <?= \Core\Security::csrfField() ?>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" id="month" name="month" required disabled>
                                    <option value="">Seleccionar...</option>
                                    <?php 
                                    $monthNumbers = [
                                        'enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4,
                                        'mayo' => 5, 'junio' => 6, 'julio' => 7, 'agosto' => 8,
                                        'septiembre' => 9, 'octubre' => 10, 'noviembre' => 11, 'diciembre' => 12
                                    ];
                                    $currentMonthNum = $monthNumbers[strtolower($rate['month'])] ?? 0;
                                    
                                    foreach ($months as $num => $name): 
                                        $selected = $num == $currentMonthNum ? 'selected' : '';
                                    ?>
                                        <option value="<?= $num ?>" <?= $selected ?>><?= $name ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="month">Mes *</label>
                                <small class="text-muted">El mes no puede modificarse</small>
                            </div>
                            <!-- Campo oculto para enviar el mes -->
                            <input type="hidden" name="month" value="<?= $currentMonthNum ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" class="form-control" id="year" name="year" value="<?= $rate['year'] ?>" min="2020" max="2050" required readonly />
                                <label for="year">Año *</label>
                                <small class="text-muted">El año no puede modificarse</small>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" class="form-control" id="bs_value" name="bs_value" value="<?= $rate['bs_value'] ?>" placeholder="50.5000" step="0.0001" min="0" required autofocus />
                                <label for="bs_value">Valor en Bs. *</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="ri ri-save-line me-1"></i>
                                Actualizar Tasa
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

