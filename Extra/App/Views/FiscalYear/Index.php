<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Años Fiscales</h5>
                <a href="<?= $app['url'] ?>/fiscalyear/create" class="btn btn-primary">
                    <i class="ri ri-add-line me-1"></i>
                    Nuevo Año Fiscal
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($fiscalYears)): ?>
                    <div class="alert alert-info" role="alert">
                        <i class="ri ri-information-line me-2"></i>
                        No hay años fiscales registrados. Haz clic en "Nuevo Año Fiscal" para crear uno.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Año</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Fin</th>
                                    <th>Estado</th>
                                    <th>Fecha Creación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fiscalYears as $fiscalYear): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($fiscalYear['year']) ?></strong></td>
                                    <td><?= date('d/m/Y', strtotime($fiscalYear['start_date'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($fiscalYear['end_date'])) ?></td>
                                    <td>
                                        <?php if ($fiscalYear['status'] === 'active'): ?>
                                            <span class="badge bg-label-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-label-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($fiscalYear['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

