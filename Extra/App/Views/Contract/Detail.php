<!-- Botón Volver -->
<div class="row mb-3">
    <div class="col-12 d-flex justify-content-end">
        <a href="<?= $app['url'] ?>/contract/index" class="btn btn-outline-secondary">
            <i class="ri ri-arrow-left-line me-1"></i>
            Volver al Listado
        </a>
    </div>
</div>
<div class="row">
    <!-- Información del Contrato -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Información del Contrato</h5>
                <span class="badge bg-label-primary">Contrato #<?= $contract['id'] ?></span>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-start mb-4">
                    <div class="avatar avatar-md me-3">
                        <span class="avatar-initial rounded bg-label-primary">
                            <i class="ri ri-user-line ri-24px"></i>
                        </span>
                    </div>
                    <div>
                        <h6 class="mb-0">
                            <?= htmlspecialchars($contract['awardee_first_name'] . ' ' . $contract['awardee_last_name']) ?>
                        </h6>
                        <small class="text-muted">C.I.: <?= htmlspecialchars($contract['awardee_id_number']) ?></small>
                    </div>
                </div>
                
                <ul class="list-unstyled mb-0">
                    <li class="d-flex align-items-center mb-3">
                        <i class="ri ri-calendar-line me-2 text-primary"></i>
                        <div>
                            <small class="text-muted d-block">Año Fiscal</small>
                            <span class="fw-medium"><?= $contract['fiscal_year'] ?></span>
                        </div>
                    </li>
                    <li class="d-flex align-items-center mb-3">
                        <i class="ri ri-calendar-check-line me-2 text-success"></i>
                        <div>
                            <small class="text-muted d-block">Fecha Inicio</small>
                            <span class="fw-medium"><?= date('d/m/Y', strtotime($contract['start_date'])) ?></span>
                        </div>
                    </li>
                    <li class="d-flex align-items-center mb-3">
                        <i class="ri ri-calendar-close-line me-2 text-danger"></i>
                        <div>
                            <small class="text-muted d-block">Fecha Fin</small>
                            <span class="fw-medium"><?= date('d/m/Y', strtotime($contract['end_date'])) ?></span>
                        </div>
                    </li>
                    <li class="d-flex align-items-center mb-3">
                        <i class="ri ri-file-list-line me-2 text-info"></i>
                        <div>
                            <small class="text-muted d-block">Tipo</small>
                            <?php if ($contract['type'] === 'simultaneous'): ?>
                                <span class="badge bg-label-info">Simultáneo</span>
                            <?php else: ?>
                                <span class="badge bg-label-warning">Anticipado</span>
                            <?php endif; ?>
                        </div>
                    </li>
                    <li class="d-flex align-items-center">
                        <i class="ri ri-repeat-line me-2 text-secondary"></i>
                        <div>
                            <small class="text-muted d-block">Modalidad</small>
                            <span class="fw-medium"><?= $contract['contract_mode'] === 'monthly' ? 'Mensual' : 'Semanal' ?></span>
                        </div>
                    </li>
                </ul>
                
                <div class="mt-4">
                    <a href="<?= $app['url'] ?>/contract/edit/<?= $contract['id'] ?>" class="btn btn-primary w-100">
                        <i class="ri ri-edit-line me-1"></i>
                        Editar Contrato
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Categorías de Negocio -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Categorías de Negocio</h5>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="ri ri-add-line"></i>
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($categories)): ?>
                    <div class="alert alert-warning" role="alert">
                        <i class="ri ri-information-line me-2"></i>
                        No hay categorías asignadas
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush" id="categories-list">
                        <?php foreach ($categories as $category): ?>
                            <div class="list-group-item px-0" 
                                 data-category-id="<?= $category['type'] === 'internal' ? 'int-' . $category['internal_category_id'] : 'ext-' . $category['external_category_id'] ?>">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <?php if ($category['type'] === 'internal'): ?>
                                            <i class="ri ri-store-2-line me-2 text-success"></i>
                                            <div>
                                                <div class="fw-medium"><?= htmlspecialchars($category['internal_category_name'] ?? '') ?></div>
                                                <small class="text-muted">Rubro Interno</small>
                                            </div>
                                        <?php else: ?>
                                            <i class="ri ri-building-line me-2 text-info"></i>
                                            <div>
                                                <div class="fw-medium"><?= htmlspecialchars($category['external_category_name'] ?? '') ?></div>
                                                <small class="text-muted">Rubro Externo</small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-icon btn-text-danger" 
                                            onclick="removeCategory(<?= $contract['id'] ?>, '<?= $category['type'] ?>', <?= $category['type'] === 'internal' ? $category['internal_category_id'] : $category['external_category_id'] ?>)"
                                            data-bs-toggle="tooltip" title="Eliminar categoría">
                                        <i class="ri ri-close-line ri-20px"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Locales Asignados -->
    <div class="col-lg-4 col-md-12 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Locales Asignados</h5>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addLocationModal">
                    <i class="ri ri-add-line"></i>
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($locations)): ?>
                    <div class="alert alert-warning" role="alert">
                        <i class="ri ri-information-line me-2"></i>
                        No hay locales asignados
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush" id="locations-list">
                        <?php foreach ($locations as $location): ?>
                            <div class="list-group-item px-0" data-location-id="<?= $location['stall_id'] ?>">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="ri ri-map-pin-line me-2 text-primary"></i>
                                        <div>
                                            <div class="fw-medium">Local <?= htmlspecialchars($location['stall_number']) ?></div>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($location['zone_name']) ?> - 
                                                <?= htmlspecialchars($location['sector_name']) ?>
                                            </small>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-icon btn-text-danger" 
                                            onclick="removeLocation(<?= $contract['id'] ?>, <?= $location['stall_id'] ?>)"
                                            data-bs-toggle="tooltip" title="Eliminar local">
                                        <i class="ri ri-close-line ri-20px"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Pagos del Contrato -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <?php if (empty($payments)): ?>
            <div class="card-body">
                <div class="alert alert-info" role="alert">
                    <i class="ri ri-information-line me-2"></i>
                    No hay pagos registrados para este contrato.
                </div>
                <div class="text-center mt-3">
                    <button type="button" class="btn btn-primary" onclick="window.location.href='<?= $app['url'] ?>/cobro/create?contract_id=<?= $contract['id'] ?>'">
                        <i class="ri ri-add-line me-1"></i>
                        Registrar Pago
                    </button>
                </div>
            </div>
            <?php else: ?>
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <div class="d-flex gap-3 align-items-center flex-wrap">
                    <h5 class="mb-0">Pagos del Contrato</h5>
                    
                    <!-- Acciones masivas -->
                    <div id="bulkActionsPayments" class="d-none align-items-center gap-2">
                        <span class="badge bg-label-primary" id="selectedPaymentsCount">0 seleccionados</span>
                        
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri ri-settings-3-line me-1"></i>
                                Acciones
                            </button>
                            <ul class="dropdown-menu">
                                <li><h6 class="dropdown-header">Cambiar Estado del Pago</h6></li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="bulkChangePaymentStatus('pending')">
                                        <i class="ri ri-time-line text-warning me-2"></i>
                                        <span>Marcar como Pendiente</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="bulkChangePaymentStatus('paid')">
                                        <i class="ri ri-checkbox-circle-line text-success me-2"></i>
                                        <span>Marcar como Pagado</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="bulkChangePaymentStatus('cancelled')">
                                        <i class="ri ri-close-circle-line text-danger me-2"></i>
                                        <span>Marcar como Cancelado</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="bulkChangePaymentStatus('refunded')">
                                        <i class="ri ri-refund-line text-info me-2"></i>
                                        <span>Marcar como Reembolsado</span>
                                    </a>
                                </li>
                                
                                <li><hr class="dropdown-divider"></li>
                                
                                <li>
                                    <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="bulkDeletePayments()">
                                        <i class="ri ri-delete-bin-7-line me-2"></i>
                                        <span>Eliminar Pagos</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex gap-2 align-items-center">
                    <label for="paymentDateFilter" class="form-label mb-0 me-2">Filtrar:</label>
                    <select id="paymentDateFilter" class="form-select form-select-sm" style="width: auto;">
                        <option value="">Todos los meses</option>
                        <?php
                        // Obtener años y meses únicos de los pagos
                        $monthsSpanish = [
                            'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo',
                            'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio',
                            'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre',
                            'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'
                        ];
                        
                        $dateOptions = [];
                        foreach ($payments as $payment) {
                            if (!empty($payment['payment_date'])) {
                                $date = date('Y-m', strtotime($payment['payment_date']));
                                $monthEng = date('F', strtotime($payment['payment_date']));
                                $year = date('Y', strtotime($payment['payment_date']));
                                $monthSpa = $monthsSpanish[$monthEng] ?? $monthEng;
                                $dateLabel = "{$monthSpa} {$year}";
                                $dateOptions[$date] = $dateLabel;
                            }
                        }
                        ksort($dateOptions);
                        foreach ($dateOptions as $date => $label):
                        ?>
                            <option value="<?= $date ?>"><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="card-datatable table-responsive">
                <table id="paymentsTable" class="datatables-customers table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Referencia</th>
                            <th>Fecha de Pago</th>
                            <th>Frecuencia</th>
                            <th>Tasa Euro</th>
                            <th>Monto</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="paymentsTableBody">
                                <?php foreach ($payments as $payment): ?>
                                <tr data-id="<?= $payment['id'] ?>" data-payment-date="<?= date('Y-m', strtotime($payment['payment_date'])) ?>">
                                    <td></td>
                                    <td><strong><?= htmlspecialchars($payment['payment_reference'] ?? '-') ?></strong></td>
                                    <td><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></td>
                                    <td>
                                        <span class="text-muted fw-bold">
                                            <?= number_format($payment['total_payment_count'] ?? 0, 2, '.', '') ?> veces
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($payment['euro_rate_id'] && $payment['euro_rate_value']): ?>
                                            <span class="badge bg-primary fw-bold">
                                                Bs. <?= number_format($payment['euro_rate_value'], 2, '.', ',') ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">No asignada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($payment['euro_rate_id'] && $payment['calculated_amount'] > 0): ?>
                                            <span class="fw-medium text-dark fw-bold">
                                                Bs. <?= number_format($payment['calculated_amount'], 2, '.', ',') ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="fw-medium">
                                                Bs. 0.00
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'paid' => 'success',
                                            'cancelled' => 'danger',
                                            'refunded' => 'info'
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Pendiente',
                                            'paid' => 'Pagado',
                                            'cancelled' => 'Cancelado',
                                            'refunded' => 'Reembolsado'
                                        ];
                                        $color = $statusColors[$payment['status']] ?? 'secondary';
                                        $label = $statusLabels[$payment['status']] ?? $payment['status'];
                                        ?>
                                        <span class="badge bg-<?= $color ?> fw-bold"><?= $label ?></span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-icon btn-text-danger" 
                                                onclick="deletePayment(<?= $payment['id'] ?>)"
                                                data-bs-toggle="tooltip" title="Eliminar pago">
                                            <i class="ri ri-delete-bin-line ri-20px"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>



<!-- Modal para Agregar Local -->
<div class="modal fade" id="addLocationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri ri-store-2-line me-2"></i>
                    Agregar Local al Contrato
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addLocationForm">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label">Zona</label>
                            <select class="form-select" id="modal_zone_select" required>
                                <option value="">Seleccionar zona...</option>
                                <?php foreach ($zones as $zone): ?>
                                    <option value="<?= $zone['id'] ?>">
                                        <?= htmlspecialchars($zone['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label class="form-label">Sector</label>
                            <select class="form-select" id="modal_sector_select" disabled required>
                                <option value="">Primero seleccione una zona...</option>
                            </select>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label class="form-label">Local</label>
                            <select class="form-select" id="modal_location_select" disabled required>
                                <option value="">Primero seleccione zona y sector...</option>
                            </select>
                        </div>
                        
                        <!-- Opción para crear nuevo local -->
                        <div class="col-12 mb-3">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleCreateLocalForm()">
                                <i class="ri ri-add-circle-line me-1"></i>
                                ¿No encuentras el local? Crear Nuevo Local
                            </button>
                        </div>
                        
                        <!-- Formulario para crear nuevo local (oculto por defecto) -->
                        <div class="col-12" id="createLocalForm" style="display: none;">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Crear Nuevo Local</h6>
                                    <div class="mb-2">
                                        <label class="form-label">Número de Local *</label>
                                        <input type="text" class="form-control" id="new_stall_number" placeholder="Ej: 101, A-5, etc.">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Descripción (opcional)</label>
                                        <textarea class="form-control" id="new_stall_description" rows="2" placeholder="Descripción del local..."></textarea>
                                    </div>
                                    <div class="alert alert-warning alert-sm mb-0">
                                        <i class="ri ri-information-line me-1"></i>
                                        Se creará en la zona y sector seleccionados arriba.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <i class="ri ri-information-line me-2"></i>
                        <div>
                            Solo se muestran locales disponibles que no estén asignados a otros contratos activos del mismo adjudicatario.
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="ri ri-close-line me-1"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="saveLocationBtn">
                    <i class="ri ri-add-line me-1"></i>
                    Agregar Local
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Agregar Categorías -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri ri-price-tag-3-line me-2"></i>
                    Agregar Categoría al Contrato
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addCategoryForm">
                    <!-- Tabs para Interno/Externo -->
                    <ul class="nav nav-pills mb-3" id="categoryTypeTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="internal-tab" data-bs-toggle="pill" 
                                    data-bs-target="#internal-categories" type="button" role="tab">
                                <i class="ri ri-store-2-line me-1"></i>
                                Rubros Internos
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="external-tab" data-bs-toggle="pill" 
                                    data-bs-target="#external-categories" type="button" role="tab">
                                <i class="ri ri-building-line me-1"></i>
                                Rubros Externos
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="categoryTabsContent">
                        <!-- Categorías Internas -->
                        <div class="tab-pane fade show active" id="internal-categories" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label">Seleccionar Rubro Interno</label>
                                <select class="form-select" id="internal_category_select">
                                    <option value="">Seleccionar...</option>
                                    <?php foreach ($internalCategories as $category): ?>
                                        <option value="<?= $category['id'] ?>">
                                            <?= htmlspecialchars($category['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Categorías Externas -->
                        <div class="tab-pane fade" id="external-categories" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label">Seleccionar Rubro Externo</label>
                                <select class="form-select" id="external_category_select">
                                    <option value="">Seleccionar...</option>
                                    <?php foreach ($externalCategories as $category): ?>
                                        <option value="<?= $category['id'] ?>">
                                            <?= htmlspecialchars($category['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <i class="ri ri-information-line me-2"></i>
                        <div>
                            Seleccione un rubro interno o externo para agregarlo al contrato.
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="ri ri-close-line me-1"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="saveCategoryBtn">
                    <i class="ri ri-add-line me-1"></i>
                    Agregar Categoría
                </button>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
const contractId = <?= $contract['id'] ?>;
const appUrl = '<?= $app['url'] ?>';

// ==========================================
// Funcionalidad para Agregar Locales
// ==========================================
const modalZoneSelect = document.getElementById('modal_zone_select');
const modalSectorSelect = document.getElementById('modal_sector_select');
const modalLocationSelect = document.getElementById('modal_location_select');
const saveLocationBtn = document.getElementById('saveLocationBtn');
const addLocationModal = document.getElementById('addLocationModal');

// Cargar sectores cuando se selecciona zona
modalZoneSelect.addEventListener('change', function() {
    const zoneId = this.value;
    modalSectorSelect.disabled = true;
    modalLocationSelect.disabled = true;
    modalSectorSelect.innerHTML = '<option value="">Cargando...</option>';
    modalLocationSelect.innerHTML = '<option value="">Seleccione sector primero</option>';
    
    if (!zoneId) {
        modalSectorSelect.innerHTML = '<option value="">Primero seleccione una zona...</option>';
        return;
    }
    
    fetch(appUrl + '/sector/getByZone/' + zoneId)
        .then(response => response.json())
        .then(data => {
            modalSectorSelect.innerHTML = '<option value="">Seleccionar sector...</option>';
            if (data.success && data.sectors.length > 0) {
                data.sectors.forEach(sector => {
                    const option = document.createElement('option');
                    option.value = sector.id;
                    option.textContent = sector.name;
                    modalSectorSelect.appendChild(option);
                });
                modalSectorSelect.disabled = false;
            } else {
                modalSectorSelect.innerHTML = '<option value="">No hay sectores disponibles</option>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalSectorSelect.innerHTML = '<option value="">Error al cargar sectores</option>';
        });
});

// Cargar locales cuando se selecciona sector
modalSectorSelect.addEventListener('change', function() {
    const sectorId = this.value;
    modalLocationSelect.disabled = true;
    modalLocationSelect.innerHTML = '<option value="">Cargando...</option>';
    
    if (!sectorId) {
        modalLocationSelect.innerHTML = '<option value="">Primero seleccione un sector</option>';
        return;
    }
    
    fetch(appUrl + '/marketstall/getBySector/' + sectorId)
        .then(response => response.json())
        .then(data => {
            modalLocationSelect.innerHTML = '<option value="">Seleccionar local...</option>';
            if (data.success && data.stalls.length > 0) {
                data.stalls.forEach(stall => {
                    const option = document.createElement('option');
                    option.value = stall.id;
                    option.textContent = 'Local ' + stall.stall_number;
                    option.dataset.zoneName = stall.zone_name;
                    option.dataset.sectorName = stall.sector_name;
                    option.dataset.stallNumber = stall.stall_number;
                    modalLocationSelect.appendChild(option);
                });
                modalLocationSelect.disabled = false;
            } else {
                modalLocationSelect.innerHTML = '<option value="">No hay locales disponibles</option>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalLocationSelect.innerHTML = '<option value="">Error al cargar locales</option>';
        });
});

// Función para mostrar/ocultar formulario de crear local
function toggleCreateLocalForm() {
    const createForm = document.getElementById('createLocalForm');
    const locationSelect = document.getElementById('modal_location_select');
    
    if (createForm.style.display === 'none') {
        createForm.style.display = 'block';
        locationSelect.disabled = true;
        locationSelect.required = false;
    } else {
        createForm.style.display = 'none';
        locationSelect.disabled = false;
        locationSelect.required = true;
        // Limpiar campos
        document.getElementById('new_stall_number').value = '';
        document.getElementById('new_stall_description').value = '';
    }
}

// Agregar local al contrato o crear uno nuevo
saveLocationBtn.addEventListener('click', function() {
    const createForm = document.getElementById('createLocalForm');
    const isCreatingNew = createForm.style.display !== 'none';
    
    let requestData = {
        contract_id: contractId,
        csrf_token: document.querySelector('meta[name="csrf-token"]')?.content
    };
    
    // Si está creando un nuevo local
    if (isCreatingNew) {
        const zoneId = modalZoneSelect.value;
        const sectorId = modalSectorSelect.value;
        const stallNumber = document.getElementById('new_stall_number').value.trim();
        const description = document.getElementById('new_stall_description').value.trim();
        
        if (!zoneId || !sectorId) {
            alert('Por favor seleccione zona y sector');
            return;
        }
        
        if (!stallNumber) {
            alert('Por favor ingrese el número de local');
            return;
        }
        
        requestData.create_new = true;
        requestData.zone_id = zoneId;
        requestData.sector_id = sectorId;
        requestData.stall_number = stallNumber;
        requestData.description = description;
    } else {
        // Si está seleccionando un local existente
        const locationId = modalLocationSelect.value;
        
        if (!locationId) {
            alert('Por favor seleccione un local');
            return;
        }
        
        requestData.stall_id = locationId;
    }
    
    saveLocationBtn.disabled = true;
    saveLocationBtn.innerHTML = '<i class="ri ri-loader-4-line ri-spin me-1"></i> ' + 
                                (isCreatingNew ? 'Creando y agregando...' : 'Agregando...');
    
    fetch(appUrl + '/contract/addLocation', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cerrar modal
            const modalInstance = bootstrap.Modal.getInstance(addLocationModal);
            modalInstance.hide();
            
            // Limpiar formulario
            modalZoneSelect.selectedIndex = 0;
            modalSectorSelect.disabled = true;
            modalLocationSelect.disabled = true;
            modalSectorSelect.innerHTML = '<option value="">Primero seleccione una zona...</option>';
            modalLocationSelect.innerHTML = '<option value="">Primero seleccione zona y sector...</option>';
            document.getElementById('createLocalForm').style.display = 'none';
            document.getElementById('new_stall_number').value = '';
            document.getElementById('new_stall_description').value = '';
            
            // Recargar página para mostrar el nuevo local
            location.reload();
            
            if (typeof notyf !== 'undefined') {
                notyf.success(data.message || 'Local agregado exitosamente');
            }
        } else {
            if (typeof notyf !== 'undefined') {
                notyf.error(data.message || 'Error al agregar el local');
            } else {
                alert(data.message || 'Error al agregar el local');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof notyf !== 'undefined') {
            notyf.error('Error de conexión al agregar el local');
        } else {
            alert('Error de conexión al agregar el local');
        }
    })
    .finally(() => {
        saveLocationBtn.disabled = false;
        saveLocationBtn.innerHTML = '<i class="ri ri-add-line me-1"></i> Agregar Local';
    });
});

// ==========================================
// Eliminar Local del Contrato
// ==========================================
function removeLocation(contractId, locationId) {
    Swal.fire({
        title: '¿Eliminar Local?',
        text: '¿Estás seguro de eliminar este local del contrato?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: 'btn btn-danger me-3',
            cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(appUrl + '/contract/removeLocation', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    contract_id: contractId,
                    stall_id: locationId,
                    csrf_token: document.querySelector('meta[name="csrf-token"]')?.content
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remover del DOM
                    const locationElement = document.querySelector(`[data-location-id="${locationId}"]`);
                    if (locationElement) {
                        locationElement.remove();
                    }
                    
                    if (typeof notyf !== 'undefined') {
                        notyf.success(data.message || 'Local eliminado exitosamente');
                    }
                    
                    // Recargar si no quedan locales
                    if (document.querySelectorAll('[data-location-id]').length === 0) {
                        location.reload();
                    }
                } else {
                    if (typeof notyf !== 'undefined') {
                        notyf.error(data.message || 'Error al eliminar el local');
                    } else {
                        alert(data.message || 'Error al eliminar el local');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof notyf !== 'undefined') {
                    notyf.error('Error de conexión');
                } else {
                    alert('Error de conexión');
                }
            });
        }
    });
}

// ==========================================
// Eliminar Registro de Pago
// ==========================================
function deletePayment(paymentId) {
    Swal.fire({
        title: '¿Eliminar Pago?',
        text: '¿Estás seguro de eliminar este registro de pago? Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: 'btn btn-danger me-3',
            cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(appUrl + '/contract/deletePayment/' + paymentId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    csrf_token: document.querySelector('meta[name="csrf-token"]')?.content
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                    if (typeof notyf !== 'undefined') {
                        notyf.success(data.message || 'Pago eliminado exitosamente');
                    }
                } else {
                    if (typeof notyf !== 'undefined') {
                        notyf.error(data.message || 'Error al eliminar el pago');
                    } else {
                        alert(data.message || 'Error al eliminar el pago');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof notyf !== 'undefined') {
                    notyf.error('Error de conexión');
                } else {
                    alert('Error de conexión');
                }
            });
        }
    });
}

// ==========================================
// Funcionalidad para Agregar Categorías
// ==========================================
const saveCategoryBtn = document.getElementById('saveCategoryBtn');
const addCategoryModal = document.getElementById('addCategoryModal');
const internalCategorySelect = document.getElementById('internal_category_select');
const externalCategorySelect = document.getElementById('external_category_select');
const internalTab = document.getElementById('internal-tab');
const externalTab = document.getElementById('external-tab');

saveCategoryBtn.addEventListener('click', function() {
    // Determinar qué pestaña está activa
    const isInternalActive = internalTab.classList.contains('active');
    const categoryType = isInternalActive ? 'internal' : 'external';
    const categoryId = isInternalActive ? 
                       parseInt(internalCategorySelect.value) : 
                       parseInt(externalCategorySelect.value);
    
    if (!categoryId) {
        alert('Por favor seleccione una categoría');
        return;
    }
    
    saveCategoryBtn.disabled = true;
    saveCategoryBtn.innerHTML = '<i class="ri ri-loader-4-line ri-spin me-1"></i> Agregando...';
    
    fetch(appUrl + '/contract/addCategory', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            contract_id: contractId,
            category_type: categoryType,
            category_id: categoryId,
            csrf_token: document.querySelector('meta[name="csrf-token"]')?.content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cerrar modal
            const modalInstance = bootstrap.Modal.getInstance(addCategoryModal);
            modalInstance.hide();
            
            // Limpiar selects
            internalCategorySelect.selectedIndex = 0;
            externalCategorySelect.selectedIndex = 0;
            
            // Recargar página
            location.reload();
            
            if (typeof notyf !== 'undefined') {
                notyf.success(data.message || 'Categoría agregada exitosamente');
            }
        } else {
            if (typeof notyf !== 'undefined') {
                notyf.error(data.message || 'Error al agregar la categoría');
            } else {
                alert(data.message || 'Error al agregar la categoría');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof notyf !== 'undefined') {
            notyf.error('Error de conexión al agregar la categoría');
        } else {
            alert('Error de conexión al agregar la categoría');
        }
    })
    .finally(() => {
        saveCategoryBtn.disabled = false;
        saveCategoryBtn.innerHTML = '<i class="ri ri-add-line me-1"></i> Agregar Categoría';
    });
});

// ==========================================
// Eliminar Categoría del Contrato
// ==========================================
function removeCategory(contractId, categoryType, categoryId) {
    Swal.fire({
        title: '¿Eliminar Categoría?',
        text: '¿Estás seguro de eliminar esta categoría del contrato?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: 'btn btn-danger me-3',
            cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(appUrl + '/contract/removeCategory', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    contract_id: contractId,
                    category_type: categoryType,
                    category_id: categoryId,
                    csrf_token: document.querySelector('meta[name="csrf-token"]')?.content
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remover del DOM
                    const categoryElement = document.querySelector(`[data-category-id="${categoryType === 'internal' ? 'int-' + categoryId : 'ext-' + categoryId}"]`);
                    if (categoryElement) {
                        categoryElement.remove();
                    }
                    
                    if (typeof notyf !== 'undefined') {
                        notyf.success(data.message || 'Categoría eliminada exitosamente');
                    }
                    
                    // Recargar si no quedan categorías
                    if (document.querySelectorAll('[data-category-id]').length === 0) {
                        location.reload();
                    }
                } else {
                    if (typeof notyf !== 'undefined') {
                        notyf.error(data.message || 'Error al eliminar la categoría');
                    } else {
                        alert(data.message || 'Error al eliminar la categoría');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof notyf !== 'undefined') {
                    notyf.error('Error de conexión');
                } else {
                    alert('Error de conexión');
                }
            });
        }
    });
}

// ==========================================
// Inicializar DataTable para Pagos
// ==========================================
<?php if (!empty($payments)): ?>
$(document).ready(function() {
    // Inicializar DataTable con checkboxes
    const paymentsTable = initDataTableWithCheckbox('paymentsTable', {
        createUrl: '<?= $app['url'] ?>/cobro/create?contract_id=<?= $contract['id'] ?>',
        bulkDeleteUrl: false, // Desactivar botón de eliminar por defecto, se usa el dropdown
        order: [[2, 'desc']], // Ordenar por fecha de pago descendente (columna 2 ahora por el checkbox)
        pageLength: 10,
        drawCallback: function() {
            // Reinicializar tooltips después de cada redibujado de la tabla
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });
    
    // Manejar selección de checkboxes para mostrar acciones masivas
    paymentsTable.on('select deselect', function() {
        const selectedCount = paymentsTable.rows({ selected: true }).count();
        
        if (selectedCount > 0) {
            $('#bulkActionsPayments').removeClass('d-none').addClass('d-flex');
            $('#selectedPaymentsCount').text(selectedCount + ' seleccionado' + (selectedCount > 1 ? 's' : ''));
        } else {
            $('#bulkActionsPayments').removeClass('d-flex').addClass('d-none');
        }
    });
    
    // ==========================================
    // Filtro de Pagos por Fecha (integrado con DataTables)
    // ==========================================
    const paymentDateFilter = document.getElementById('paymentDateFilter');
    
    // Función de filtro personalizado para DataTables
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            // Solo aplicar este filtro a la tabla de pagos
            if (settings.nTable.id !== 'paymentsTable') {
                return true;
            }
            
            const selectedDate = paymentDateFilter ? paymentDateFilter.value : '';
            if (!selectedDate) {
                return true; // Mostrar todo si no hay filtro
            }
            
            // Obtener el atributo data-payment-date de la fila
            const row = paymentsTable.row(dataIndex).node();
            const rowDate = $(row).attr('data-payment-date');
            
            return rowDate === selectedDate;
        }
    );
    
    if (paymentDateFilter) {
        paymentDateFilter.addEventListener('change', function() {
            // Redibujar la tabla con el nuevo filtro
            paymentsTable.draw();
        });
    }
    
    // Inicializar tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
});

// ==========================================
// Obtener IDs de pagos seleccionados
// ==========================================
function getSelectedPaymentIds() {
    const table = $('#paymentsTable').DataTable();
    const selectedRows = table.rows({ selected: true }).nodes();
    const ids = [];
    
    $(selectedRows).each(function() {
        const id = $(this).attr('data-id');
        if (id) ids.push(id);
    });
    
    return ids;
}

// ==========================================
// Cambiar estado de múltiples pagos (bulk)
// ==========================================
function bulkChangePaymentStatus(newStatus) {
    const ids = getSelectedPaymentIds();
    
    if (ids.length === 0) {
        Swal.fire({
            title: 'Sin Selección',
            text: 'Por favor selecciona al menos un pago',
            icon: 'warning',
            confirmButtonText: 'Entendido',
            customClass: {
                confirmButton: 'btn btn-primary'
            },
            buttonsStyling: false
        });
        return;
    }
    
    const statusLabels = {
        'pending': 'Pendiente',
        'paid': 'Pagado',
        'cancelled': 'Cancelado',
        'refunded': 'Reembolsado'
    };
    
    Swal.fire({
        title: '¿Cambiar Estado?',
        html: `¿Estás seguro de cambiar el estado de <strong>${ids.length}</strong> pago(s) a <strong>${statusLabels[newStatus]}</strong>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: 'btn btn-primary me-3',
            cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            console.log('Enviando petición a:', appUrl + '/contract/bulkupdateindividualpaymentstatus');
            console.log('IDs:', ids);
            console.log('Nuevo estado:', newStatus);
            console.log('CSRF Token:', csrfToken ? 'Presente' : 'Ausente');
            
            fetch(appUrl + '/contract/bulkupdateindividualpaymentstatus', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    ids: ids,
                    status: newStatus,
                    csrf_token: csrfToken
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (typeof notyf !== 'undefined') {
                        notyf.success(data.message || 'Estados actualizados exitosamente');
                    }
                    setTimeout(() => location.reload(), 1000);
                } else {
                    if (typeof notyf !== 'undefined') {
                        notyf.error(data.message || 'Error al actualizar los estados');
                    } else {
                        alert(data.message || 'Error al actualizar los estados');
                    }
                }
            })
            .catch(error => {
                console.error('Error completo:', error);
                if (typeof notyf !== 'undefined') {
                    notyf.error('Error de conexión: ' + error.message);
                } else {
                    alert('Error de conexión: ' + error.message);
                }
            });
        }
    });
}

// ==========================================
// Eliminar múltiples pagos (bulk)
// ==========================================
function bulkDeletePayments() {
    const ids = getSelectedPaymentIds();
    
    if (ids.length === 0) {
        Swal.fire({
            title: 'Sin Selección',
            text: 'Por favor selecciona al menos un pago',
            icon: 'warning',
            confirmButtonText: 'Entendido',
            customClass: {
                confirmButton: 'btn btn-primary'
            },
            buttonsStyling: false
        });
        return;
    }
    
    Swal.fire({
        title: '¿Eliminar Pagos?',
        html: `¿Estás seguro de eliminar <strong>${ids.length}</strong> pago(s)?<br>Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: 'btn btn-danger me-3',
            cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(appUrl + '/contract/bulkdeletepayments', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    ids: ids,
                    csrf_token: document.querySelector('meta[name="csrf-token"]')?.content
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (typeof notyf !== 'undefined') {
                        notyf.success(data.message || 'Pagos eliminados exitosamente');
                    }
                    setTimeout(() => location.reload(), 1000);
                } else {
                    if (typeof notyf !== 'undefined') {
                        notyf.error(data.message || 'Error al eliminar los pagos');
                    } else {
                        alert(data.message || 'Error al eliminar los pagos');
                    }
                }
            })
            .catch(error => {
                console.error('Error completo:', error);
                if (typeof notyf !== 'undefined') {
                    notyf.error('Error de conexión: ' + error.message);
                } else {
                    alert('Error de conexión: ' + error.message);
                }
            });
        }
    });
}

<?php else: ?>
// Inicializar tooltips si no hay pagos
$(document).ready(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();
});
<?php endif; ?>
</script>
<?php $pageScripts = ob_get_clean(); ?>

