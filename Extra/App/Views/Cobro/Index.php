<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Gestión de Cobros</h5>
            </div>
            <div class="card-body">
                <p class="card-text">Ingresa el número de cédula del adjudicatario para buscar sus facturas pendientes</p>
                
                <form action="<?= $app['url'] ?>/cobro/buscar" method="POST" class="mt-4">
                    <?= \Core\Security::csrfField() ?>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-floating form-floating-outline mb-3">
                                <input type="text" class="form-control" id="id_number" name="id_number" placeholder="Ej: V-12345678" required autofocus />
                                <label for="id_number">Número de Cédula</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100" style="height: 56px;">
                                <i class="ri ri-search-line me-1"></i>
                                Buscar Adjudicatario
                            </button>
                        </div>
                    </div>
                </form>
                
                <div class="alert alert-info mt-4" role="alert">
                    <h6 class="alert-heading mb-2">
                        <i class="ri ri-information-line me-2"></i>Instrucciones
                    </h6>
                    <ul class="mb-0">
                        <li>Ingresa el número de cédula del adjudicatario</li>
                        <li>El sistema mostrará todas las facturas pendientes</li>
                        <li>Podrás realizar pagos totales o parciales (abonos)</li>
                        <li>Se actualizará automáticamente el saldo restante</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar flex-shrink-0 me-3">
                        <span class="avatar-initial rounded bg-label-primary">
                            <i class="ri ri-cash-line ri-24px"></i>
                        </span>
                    </div>
                    <div>
                        <h6 class="mb-0">Pagos del Día</h6>
                        <small class="text-muted">Bs. 0.00</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar flex-shrink-0 me-3">
                        <span class="avatar-initial rounded bg-label-success">
                            <i class="ri ri-checkbox-circle-line ri-24px"></i>
                        </span>
                    </div>
                    <div>
                        <h6 class="mb-0">Facturas Pagadas Hoy</h6>
                        <small class="text-muted">0 facturas</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

