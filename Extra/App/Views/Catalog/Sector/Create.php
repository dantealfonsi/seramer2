<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Crear Sector</h5>
            </div>
            <div class="card-body">
                <form action="<?= $app['url'] ?>/sector/store" method="POST">
                    <?= \Core\Security::csrfField() ?>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" id="zone_id" name="zone_id" required>
                                    <option value="">Seleccionar zona...</option>
                                    <?php foreach ($zones as $zone): ?>
                                        <option value="<?= $zone['id'] ?>"><?= htmlspecialchars($zone['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="zone_id">Zona *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="name" name="name" placeholder="Nombre del sector" required />
                                <label for="name">Nombre del Sector *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="description" name="description" placeholder="Descripción" />
                                <label for="description">Descripción</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="ri ri-save-line me-1"></i>
                                Guardar
                            </button>
                            <a href="<?= $app['url'] ?>/sector/index" class="btn btn-outline-secondary">
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

