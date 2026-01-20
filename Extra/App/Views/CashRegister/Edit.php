<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Editar Caja</h5>
            </div>
            <div class="card-body">
                <form action="<?= $app['url'] ?>/cashregister/update/<?= $cashRegister['id'] ?>" method="POST">
                    <?= \Core\Security::csrfField() ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($cashRegister['name']) ?>" required />
                                <label for="name">Nombre de la Caja *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" id="user_id" name="user_id" required>
                                    <option value="">Seleccionar usuario...</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?= $user['id'] ?>" <?= $cashRegister['user_id'] == $user['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($user['username']) ?> - 
                                            <?= htmlspecialchars($user['email'] ?? '') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="user_id">Usuario Asignado *</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="ri ri-save-line me-1"></i>
                                Actualizar Caja
                            </button>
                            <a href="<?= $app['url'] ?>/cashregister/index" class="btn btn-outline-secondary">
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

