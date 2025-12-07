        <!-- Estadísticas del mes -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Contratos</h6>
                                <h3 class="mb-0"><?= number_format($statistics['total_contracts'] ?? 0) ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="ri ri-file-list-line ri-32px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Monto Total</h6>
                                <h3 class="mb-0">$<?= number_format($statistics['total_amount'] ?? 0, 2) ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="ri ri-money-dollar-circle-line ri-32px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Pendientes</h6>
                                <h3 class="mb-0"><?= number_format($statistics['pending_payments'] ?? 0) ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="ri ri-time-line ri-32px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Morosos</h6>
                                <h3 class="mb-0"><?= number_format($statistics['delinquent_payments'] ?? 0) ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="ri ri-alarm-warning-line ri-32px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center border-bottom">
                <h5 class="mb-0">
                    <i class="ri ri-calendar-check-line me-2"></i>
                    Planificación de Cobros - <?= htmlspecialchars($current_month_spanish) ?> <?= $current_year ?>
                </h5>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary" onclick="location.reload()">
                        <i class="ri ri-refresh-line me-1"></i> Actualizar
                    </button>
                    <button type="button" class="btn btn-success" onclick="exportCSV()">
                        <i class="ri ri-download-line me-1"></i> Exportar CSV
                    </button>
                </div>
            </div>

            <div class="card-header d-flex justify-content-between align-items-center border-bottom">
                <!-- Filtros -->
                <div class="row mb-4">
                    <div class="col-12">
                        <form method="GET" class="d-flex flex-wrap gap-2" id="filtersForm">
                            <!-- Filtro de Año -->
                            <div class="flex-shrink-0">
                                <select class="form-select" name="year" id="yearSelect">
                                    <option value="">Año actual</option>
                                    <?php if (!empty($fiscal_years)): ?>
                                        <?php foreach ($fiscal_years as $fy): ?>
                                            <?php 
                                            // Extraer el año de la fecha de inicio
                                            $fyYear = date('Y', strtotime($fy['start_date']));
                                            ?>
                                            <option value="<?= $fyYear ?>" 
                                                    <?= ($filters['year'] == $fyYear) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($fyYear) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <!-- Filtro de Mes -->
                            <div class="flex-shrink-0">
                                <select class="form-select" name="month" id="monthSelect">
                                    <option value="">Mes actual</option>
                                    <?php foreach ($months as $monthNum => $monthName): ?>
                                        <option value="<?= $monthNum ?>" 
                                                <?= ($filters['month'] == $monthNum) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($monthName) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Filtro de Zona -->
                            <div class="flex-shrink-0">
                                <select class="form-select" name="zone_id" id="zoneSelect" onchange="loadSectors()">
                                    <option value="">Todas las zonas</option>
                                    <?php foreach ($zones as $zone): ?>
                                        <option value="<?= $zone['id'] ?>" 
                                                <?= ($filters['zone_id'] == $zone['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($zone['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="flex-shrink-0">
                                <select class="form-select" name="sector_id" id="sectorSelect">
                                    <option value="">Todos los sectores</option>
                                    <?php foreach ($sectors as $sector): ?>
                                        <option value="<?= $sector['id'] ?>" 
                                                <?= ($filters['sector_id'] == $sector['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($sector['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="flex-shrink-0 d-flex align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="show_delinquent" 
                                        value="1" id="showDelinquent" 
                                        <?= ($filters['show_delinquent'] == '1') ? 'checked' : '' ?>>
                                    <label class="form-check-label ms-2" for="showDelinquent">
                                        Solo morosos
                                    </label>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri ri-search-line me-1"></i> Filtrar
                                </button>
                            </div>
                            <div class="flex-shrink-0">
                                <a href="?" class="btn btn-outline-secondary">
                                    <i class="ri ri-close-line me-1"></i> Limpiar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
            
                
            <!-- Tabla de contratos -->
            <div class="card-datatable table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID Contrato</th>
                            <th>Adjudicatario</th>
                            <th>Cédula</th>
                            <th>Zona/Sector</th>
                            <th>Rubros</th>
                            <th>Locales</th>
                            <th>Monto del Mes</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($contracts)): ?>
                            <?php foreach ($contracts as $contract): ?>
                            <tr>
                                <td>
                                    <strong>#<?= $contract['contract_id'] ?></strong>
                                </td>
                                <td><?= htmlspecialchars($contract['awardee_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($contract['awardee_id_number'] ?? 'N/A') ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($contract['zone_name'] ?? 'N/A') ?></strong>
                                    <?php if (!empty($contract['sector_name'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($contract['sector_name']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= number_format($contract['total_categories'] ?? 0) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= number_format($contract['total_locations'] ?? 0) ?></span>
                                </td>
                                <td>
                                    <strong>$<?= number_format($contract['calculated_amount'], 2) ?></strong>
                                    <?php if ($contract['multiplier_factor'] > 0 && $contract['euro_rate_value'] > 0): ?>
                                        <br><small class="text-muted">
                                            <?= number_format($contract['multiplier_factor'], 2) ?> × €<?= number_format($contract['euro_rate_value'], 2) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusBadges = [
                                        'Pendiente' => 'bg-warning text-dark',
                                        'Pagado' => 'bg-success',
                                        'Moroso' => 'bg-danger',
                                        'Cancelado' => 'bg-secondary'
                                    ];
                                    $status = $contract['payment_status_text'];
                                    $badgeClass = $statusBadges[$status] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= htmlspecialchars($status) ?>
                                    </span>
                                    <?php if (!empty($contract['payment_date'])): ?>
                                        <small class="text-muted d-block">
                                            Vence: <?= date('d/m/Y', strtotime($contract['payment_date'])) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= $app['url'] ?>/contract/detail/<?= $contract['contract_id'] ?>" 
                                        class="btn btn-outline-primary" title="Ver detalles">
                                            <i class="ri ri-eye-line"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ri ri-calendar-line ri-64px text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No hay contratos programados para este mes</p>
                                        <?php if (!empty($filters['zone_id']) || !empty($filters['sector_id']) || !empty($filters['show_delinquent'])): ?>
                                            <small class="text-muted">Intenta ajustar los filtros</small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
        </div>

<?php ob_start(); ?>
<script>
$(document).ready(function() {
    $('.head-label').html('<h5 class="card-title mb-0">Planificación de Cobros</h5>');
});

// Función para cargar sectores según la zona seleccionada
function loadSectors() {
    const zoneId = document.getElementById('zoneSelect').value;
    const sectorSelect = document.getElementById('sectorSelect');
    
    // Limpiar sectores
    sectorSelect.innerHTML = '<option value="">Todos los sectores</option>';
    
    if (zoneId) {
        fetch('<?= $app['url'] ?>/planning/getSectorsByZone/' + zoneId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.sectors.forEach(sector => {
                        const option = document.createElement('option');
                        option.value = sector.id;
                        option.textContent = sector.name;
                        sectorSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading sectors:', error);
            });
    }
}

// Función para exportar a CSV
function exportCSV() {
    alert('Funcionalidad de exportación próximamente disponible');
}

// Auto-submit form on checkbox change
document.getElementById('showDelinquent').addEventListener('change', function() {
    document.getElementById('filtersForm').submit();
});
</script>
<?php $pageScripts = ob_get_clean(); ?>

