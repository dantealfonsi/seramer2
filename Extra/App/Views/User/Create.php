<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Crear Nuevo Usuario</h5>
            </div>
            <div class="card-body">
                <form action="<?= $app['url'] ?>/user/store" method="POST">
                    <?= \Core\Security::csrfField() ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required />
                                <label for="username">Nombre de Usuario *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="email" class="form-control" id="email" name="email" placeholder="email@ejemplo.com" required />
                                <label for="email">Correo Electrónico *</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required />
                                <label for="password">Contraseña *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" id="status" name="status" required>
                                    <option value="active" selected>Activo</option>
                                    <option value="inactive">Inactivo</option>
                                </select>
                                <label for="status">Estado *</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="ri ri-save-line me-1"></i>
                                Guardar Usuario
                            </button>
                            <a href="<?= $app['url'] ?>/user/index" class="btn btn-label-secondary">
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

