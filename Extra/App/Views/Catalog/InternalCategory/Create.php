<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Crear Rubro Interno</h5>
            </div>
            <div class="card-body">
                <form action="<?= $app['url'] ?>/internalcategory/store" method="POST">
                    <?= \Core\Security::csrfField() ?>
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="name" name="name" placeholder="Nombre del rubro" required />
                                <label for="name">Nombre *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="number" step="0.01" class="form-control" id="payment_count" name="payment_count" placeholder="0.00" required />
                                <label for="payment_count">Monto (EUR) *</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="ri ri-save-line me-1"></i>
                                Guardar
                            </button>
                            <a href="<?= $app['url'] ?>/internalcategory/index" class="btn btn-outline-secondary">
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

