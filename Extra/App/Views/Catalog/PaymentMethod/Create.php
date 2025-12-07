<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="ri ri-bank-card-line me-2"></i>
                    Crear Método de Pago
                </h5>
                <a href="<?= $app['url'] ?>/paymentmethod/index" class="btn btn-sm btn-secondary">
                    <i class="ri ri-arrow-left-line me-1"></i>
                    Volver
                </a>
            </div>
            <div class="card-body">
                <form action="<?= $app['url'] ?>/paymentmethod/store" method="POST" id="paymentMethodForm">
                    <input type="hidden" name="csrf_token" value="<?= \Core\Security::getCsrfToken() ?>">
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="name" class="form-label">
                                Nombre del Método de Pago <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="name" 
                                   name="name" 
                                   placeholder="Ej: Efectivo, Transferencia Bancaria, etc."
                                   required 
                                   maxlength="50">
                            <div class="form-text">Ingrese el nombre del método de pago (máximo 50 caracteres)</div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="is_active" class="form-label">Estado</label>
                            <select class="form-select" id="is_active" name="is_active">
                                <option value="1" selected>Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                            <div class="form-text">Define si el método estará disponible</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info d-flex align-items-center" role="alert">
                                <i class="ri ri-information-line me-2"></i>
                                <div>
                                    Los métodos de pago se utilizan para registrar los pagos de los adjudicatarios. 
                                    Asegúrate de crear solo los métodos que realmente se aceptan en el sistema.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri ri-save-line me-1"></i>
                            Guardar
                        </button>
                        <a href="<?= $app['url'] ?>/paymentmethod/index" class="btn btn-label-secondary">
                            <i class="ri ri-close-line me-1"></i>
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
$(document).ready(function() {
    $('#paymentMethodForm').on('submit', function(e) {
        const name = $('#name').val().trim();
        
        if (name === '') {
            e.preventDefault();
            notyf.error('El nombre del método de pago es requerido');
            $('#name').focus();
            return false;
        }
        
        if (name.length > 50) {
            e.preventDefault();
            notyf.error('El nombre no puede exceder los 50 caracteres');
            $('#name').focus();
            return false;
        }
    });
});
</script>
<?php $pageScripts = ob_get_clean(); ?>

