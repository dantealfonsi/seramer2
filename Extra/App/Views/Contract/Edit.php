<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Editar Contrato #<?= $contract['id'] ?></h5>
            </div>
            <div class="card-body">
                <form action="<?= $app['url'] ?>/contract/update/<?= $contract['id'] ?>" method="POST" id="contractForm">
                    <?= \Core\Security::csrfField() ?>
                    
                    <!-- Información del Adjudicatario -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold">Información del Adjudicatario</h6>
                            <hr>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" id="awardee_id" name="awardee_id" required>
                                    <option value="">Seleccionar adjudicatario...</option>
                                    <?php foreach ($awardees as $awardee): ?>
                                        <option value="<?= $awardee['id'] ?>" <?= $awardee['id'] == $contract['awardee_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars(\App\Models\AwardeeModel::getFullName($awardee)) ?> - 
                                            <?= htmlspecialchars($awardee['id_number']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="awardee_id">Adjudicatario *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" id="fiscal_year_id" name="fiscal_year_id" required>
                                    <option value="">Seleccionar año fiscal...</option>
                                    <?php foreach ($fiscalYears as $fiscalYear): ?>
                                        <option value="<?= $fiscalYear['id'] ?>" <?= $fiscalYear['id'] == $contract['fiscal_year_id'] ? 'selected' : '' ?>>
                                            <?= $fiscalYear['year'] ?> 
                                            <?= $fiscalYear['status'] === 'active' ? '(Activo)' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="fiscal_year_id">Año Fiscal *</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Información del Contrato -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold">Detalles del Contrato</h6>
                            <hr>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                       value="<?= $contract['start_date'] ?>" required />
                                <label for="start_date">Fecha Inicio *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="date" class="form-control" id="end_date" name="end_date" 
                                       value="<?= $contract['end_date'] ?>" required />
                                <label for="end_date">Fecha Fin *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="simultaneous" <?= $contract['type'] === 'simultaneous' ? 'selected' : '' ?>>Simultáneo</option>
                                    <option value="advance" <?= $contract['type'] === 'advance' ? 'selected' : '' ?>>Anticipado</option>
                                </select>
                                <label for="type">Tipo de Contrato *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" id="contract_mode" name="contract_mode" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="monthly" <?= $contract['contract_mode'] === 'monthly' ? 'selected' : '' ?>>Mensual</option>
                                    <option value="weekly" <?= $contract['contract_mode'] === 'weekly' ? 'selected' : '' ?>>Semanal</option>
                                </select>
                                <label for="contract_mode">Modalidad de Pago *</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning" role="alert">
                        <i class="ri ri-information-line me-2"></i>
                        <strong>Nota:</strong> Para modificar las categorías de negocio o los locales asignados, debes hacerlo desde la vista de detalle del contrato.
                    </div>
                    
                    <!-- Botones de Acción -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="ri ri-save-line me-1"></i>
                                Actualizar Contrato
                            </button>
                            <a href="<?= $app['url'] ?>/contract/detail/<?= $contract['id'] ?>" class="btn btn-outline-secondary">
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

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validar fechas
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    startDateInput.addEventListener('change', function() {
        endDateInput.min = this.value;
    });
    
    // Establecer min en end_date al cargar
    endDateInput.min = startDateInput.value;
});
</script>
<?php $pageScripts = ob_get_clean(); ?>

