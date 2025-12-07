<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Editar Zona</h5>
            </div>
            <div class="card-body">
                <form action="<?= $app['url'] ?>/zone/update/<?= $zone['id'] ?>" method="POST">
                    <?= \Core\Security::csrfField() ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($zone['name']) ?>" placeholder="Nombre de la zona" required />
                                <label for="name">Nombre *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="description" name="description" value="<?= htmlspecialchars($zone['description'] ?? '') ?>" placeholder="Descripción" />
                                <label for="description">Descripción</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="ri ri-save-line me-1"></i>
                                Actualizar
                            </button>
                            <a href="<?= $app['url'] ?>/zone/index" class="btn btn-outline-secondary">
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

