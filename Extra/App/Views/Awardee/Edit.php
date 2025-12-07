<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Editar Adjudicatario</h5>
            </div>
            <div class="card-body">
                <form action="<?= $app['url'] ?>/awardee/update/<?= $awardee['id'] ?>" method="POST">
                    <?= \Core\Security::csrfField() ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($awardee['first_name']) ?>" placeholder="Primer Nombre" required />
                                <label for="first_name">Primer Nombre *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="middle_name" name="middle_name" value="<?= htmlspecialchars($awardee['middle_name'] ?? '') ?>" placeholder="Segundo Nombre" />
                                <label for="middle_name">Segundo Nombre</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($awardee['last_name']) ?>" placeholder="Primer Apellido" required />
                                <label for="last_name">Primer Apellido *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="second_last_name" name="second_last_name" value="<?= htmlspecialchars($awardee['second_last_name'] ?? '') ?>" placeholder="Segundo Apellido" />
                                <label for="second_last_name">Segundo Apellido</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="id_number" name="id_number" value="<?= htmlspecialchars($awardee['id_number']) ?>" placeholder="V-12345678" required />
                                <label for="id_number">Número de Cédula *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($awardee['phone'] ?? '') ?>" placeholder="Teléfono" />
                                <label for="phone">Teléfono</label>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($awardee['email'] ?? '') ?>" placeholder="email@ejemplo.com" />
                                <label for="email">Correo Electrónico</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="form-floating form-floating-outline">
                                <textarea class="form-control" id="address" name="address" placeholder="Dirección" rows="3"><?= htmlspecialchars($awardee['address'] ?? '') ?></textarea>
                                <label for="address">Dirección</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="ri ri-save-line me-1"></i>
                                Actualizar Adjudicatario
                            </button>
                            <a href="<?= $app['url'] ?>/awardee/index" class="btn btn-outline-secondary">
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

