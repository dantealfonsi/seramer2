<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Crear Nuevo Año Fiscal</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning" role="alert">
                    <h6 class="alert-heading mb-2">
                        <i class="ri ri-error-warning-line me-2"></i>Importante
                    </h6>
                    <p class="mb-0">
                        Al crear un nuevo año fiscal, el sistema:
                    </p>
                    <ul class="mb-0">
                        <li>Desactivará el año fiscal anterior</li>
                        <li>Generará automáticamente los 12 pagos mensuales para <strong>todos los contratos vigentes</strong></li>
                        <li>Los pagos se crearán con tasa de euro PENDIENTE (NULL)</li>
                        <li>Deberás establecer las tasas de euro mensualmente para calcular los montos</li>
                    </ul>
                </div>
                
                <form action="<?= $app['url'] ?>/fiscalyear/store" method="POST" class="mt-4">
                    <?= \Core\Security::csrfField() ?>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" class="form-control" id="year" name="year" placeholder="2025" min="2020" max="2050" required />
                                <label for="year">Año *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="date" class="form-control" id="start_date" name="start_date" required />
                                <label for="start_date">Fecha de Inicio *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="date" class="form-control" id="end_date" name="end_date" required />
                                <label for="end_date">Fecha de Fin *</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="ri ri-save-line me-1"></i>
                                Crear Año Fiscal
                            </button>
                            <a href="<?= $app['url'] ?>/fiscalyear/index" class="btn btn-outline-secondary">
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

