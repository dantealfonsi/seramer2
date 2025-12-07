<div class="row">
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="avatar avatar-lg mx-auto mb-3">
                    <span class="avatar-initial rounded-circle bg-label-danger">
                        <i class="ri ri-alert-line ri-32px"></i>
                    </span>
                </div>
                <h5 class="card-title">Contratos Morosos</h5>
                <p class="card-text text-muted">Reporte de contratos con facturas vencidas</p>
                <a href="<?= $app['url'] ?>/report/delinquentcontracts" class="btn btn-primary">
                    <i class="ri ri-file-list-3-line me-1"></i>
                    Ver Reporte
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="avatar avatar-lg mx-auto mb-3">
                    <span class="avatar-initial rounded-circle bg-label-info">
                        <i class="ri ri-map-pin-line ri-32px"></i>
                    </span>
                </div>
                <h5 class="card-title">Total por Zona</h5>
                <p class="card-text text-muted">Reporte de total acumulado monetariamente por zona</p>
                <a href="<?= $app['url'] ?>/report/zoneaccumulated" class="btn btn-primary">
                    <i class="ri ri-file-list-3-line me-1"></i>
                    Ver Reporte
                </a>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
$(document).ready(function() {
    // Configurar título de la tarjeta
    $('.head-label').html('<h5 class="card-title mb-0">Reportes</h5>');
});
</script>
<?php $pageScripts = ob_get_clean(); ?>

