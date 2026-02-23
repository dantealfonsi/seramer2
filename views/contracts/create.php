<?php
require_once __DIR__ . '/../../controllers/ContractController.php';

$controller = new ContractController();
$data = $controller->create();

$awardees = $data['awardees'];
$fiscalYears = $data['fiscalYears'];
$internalCategories = $data['internalCategories'];
$externalCategories = $data['externalCategories'];
$zones = $data['zones'];
$page_title = $data['page_title'];

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <form action="store.php" method="POST" id="contractForm" class="needs-validation" novalidate>
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center border-bottom py-3">
                    <h5 class="mb-0 d-flex align-items-center">
                        <i class="ri-file-add-line me-2 text-primary ri-24px"></i>
                        Información Detallada del Contrato
                    </h5>
                    <span class="badge bg-label-primary px-3">Nuevo Registro</span>
                </div>
                <div class="card-body p-4">
                    <!-- Sección 1: Datos Básicos -->
                    <div class="d-flex align-items-center mb-3">
                        <i class="ri-information-line me-2 text-primary ri-20px"></i>
                        <h6 class="fw-bold mb-0">Datos del Adjudicatario y Vigencia</h6>
                    </div>
                    
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <label class="form-label" for="awardee_id">Adjudicatario <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select class="form-select select2" id="awardee_id" name="awardee_id" required>
                                    <option value="">Seleccione Adjudicatario</option>
                                    <?php foreach ($awardees as $awardee): ?>
                                    <option value="<?= $awardee['id'] ?>">
                                        <?= htmlspecialchars($awardee['last_name'] . ' ' . $awardee['first_name']) ?> (<?= $awardee['id_number'] ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#addAwardeeModal">
                                    <i class="ri-add-line"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="fiscal_year_id">Año Fiscal <span class="text-danger">*</span></label>
                            <select class="form-select" id="fiscal_year_id" name="fiscal_year_id" required>
                                <option value="">Seleccione Año Fiscal</option>
                                <?php foreach ($fiscalYears as $fy): ?>
                                <option value="<?= $fy['id'] ?>" 
                                    data-start="<?= $fy['start_date'] ?>" 
                                    data-end="<?= $fy['end_date'] ?>"
                                    <?= $fy['status'] === 'active' ? 'selected' : '' ?>>
                                    <?= $fy['year'] ?> (<?= date('d/m/Y', strtotime($fy['start_date'])) ?> - <?= date('d/m/Y', strtotime($fy['end_date'])) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="start_date">Fecha Inicio <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="end_date">Fecha Fin <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="type">Tipo de Contrato <span class="text-danger">*</span></label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="simultaneous">Simultáneo (Mensual)</option>
                                <option value="advance">Anticipado (Anual)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="contract_mode">Modalidad <span class="text-danger">*</span></label>
                            <select class="form-select" id="contract_mode" name="contract_mode" required>
                                <option value="monthly">Mensual</option>
                                <option value="weekly">Semanal</option>
                            </select>
                        </div>
                    </div>

                    <!-- Sección 2: Rubros -->
                    <div class="d-flex align-items-center mb-3">
                        <i class="ri-price-tag-3-line me-2 text-primary ri-20px"></i>
                        <h6 class="fw-bold mb-0">Categorías de Negocio (Rubros)</h6>
                    </div>
                    <hr class="mt-0 mb-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Categorías Internas</label>
                            <div class="input-group">
                                <select class="form-select" id="internal_category_select">
                                    <option value="">Seleccionar categoría interna...</option>
                                    <?php foreach ($internalCategories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" 
                                            data-name="<?= htmlspecialchars($cat['name']) ?>"
                                            data-type="interna"
                                            data-payments="<?= number_format($cat['payment_count'], 2) ?>">
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-primary" onclick="addCategoryUI('internal')">
                                    <i class="ri-add-line"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Categorías Externas</label>
                            <div class="input-group">
                                <select class="form-select" id="external_category_select">
                                    <option value="">Seleccionar categoría externa...</option>
                                    <?php foreach ($externalCategories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" 
                                            data-name="<?= htmlspecialchars($cat['name']) ?>"
                                            data-type="externa"
                                            data-payments="<?= number_format($cat['payment_count'], 2) ?>">
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-primary" onclick="addCategoryUI('external')">
                                    <i class="ri-add-line"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="selected_categories_container" class="row g-3 mb-5">
                        <div class="col-12 text-center text-muted py-3 bg-light rounded-3" id="no-cats-msg">
                            <p class="mb-0 small">No hay categorías seleccionadas</p>
                        </div>
                    </div>

                    <!-- Sección 3: Locales -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            <i class="ri-store-2-line me-2 text-primary ri-20px"></i>
                            <h6 class="fw-bold mb-0">Locales Asignados</h6>
                        </div>
                        <button type="button" class="btn btn-sm btn-label-primary" data-bs-toggle="modal" data-bs-target="#createStallModal">
                            <i class="ri-add-circle-line me-1"></i> Crear Nuevo Local
                        </button>
                    </div>
                    <hr class="mt-0 mb-4">
                    <div class="row g-4 align-items-end mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">ZONA</label>
                            <select class="form-select" id="zone_id" onchange="loadSectors(this.value)">
                                <option value="">Seleccione Zona</option>
                                <?php foreach ($zones as $zone): ?>
                                <option value="<?= $zone['id'] ?>"><?= htmlspecialchars($zone['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">SECTOR</label>
                            <select class="form-select" id="sector_id" disabled onchange="loadStalls(this.value)">
                                <option value="">Primero seleccione una zona</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">LOCAL</label>
                            <select class="form-select" id="stall_id" disabled>
                                <option value="">Primero seleccione sector</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-primary w-100" onclick="addLocationUI()">
                                <i class="ri-add-line"></i>
                            </button>
                        </div>
                    </div>
                    <div id="selected_locations_container" class="row g-3 mb-5">
                        <div class="col-12 text-center text-muted py-3 bg-light rounded-3" id="no-locs-msg">
                            <p class="mb-0 small">No hay locales asignados</p>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <hr class="my-4">
                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary px-5 d-flex align-items-center">
                            <i class="ri-save-line me-2"></i> Guardar Contrato
                        </button>
                        <a href="index.php" class="btn btn-outline-secondary px-4 d-flex align-items-center">
                            <i class="ri-arrow-left-line me-2"></i> Volver al Listado
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Hidden inputs for dynamic collections -->
            <div id="dynamic_inputs"></div>
        </form>
    </div>
</div>

<!-- Modal para Crear Nuevo Local -->
<div class="modal fade" id="createStallModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-store-2-line me-2"></i>Crear Nuevo Local</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createStallForm">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" id="modal_zone_id" name="zone_id" onchange="loadModalSectors(this.value)" required>
                                    <option value="">Seleccionar zona...</option>
                                    <?php foreach ($zones as $zone): ?>
                                    <option value="<?= $zone['id'] ?>"><?= htmlspecialchars($zone['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="modal_zone_id">Zona *</label>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" id="modal_sector_id" name="sector_id" disabled required>
                                    <option value="">Seleccione Sector</option>
                                </select>
                                <label for="modal_sector_id">Sector *</label>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="modal_stall_number" name="stall_number" placeholder="Número de Local" required>
                                <label for="modal_stall_number">Número de Local *</label>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-floating form-floating-outline">
                                <textarea class="form-control" id="modal_stall_description" name="description" placeholder="Descripción" style="height: 80px;"></textarea>
                                <label for="modal_stall_description">Descripción (opcional)</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="quickCreateStall()">Guardar Local</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Registrar Nuevo Adjudicatario -->
<div class="modal fade" id="addAwardeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-user-add-line me-2"></i>Registrar Nuevo Adjudicatario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                    <i class="ri-information-line me-2"></i>
                    <div>El adjudicatario se creará y estará disponible inmediatamente en el selector.</div>
                </div>
                <form id="quickAwardeeForm">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <h6 class="fw-bold text-primary">Información Personal</h6>
                            <hr class="mt-0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="m_first_name" placeholder="Primer Nombre" required>
                                <label for="m_first_name">Primer Nombre *</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="m_middle_name" placeholder="Segundo Nombre">
                                <label for="m_middle_name">Segundo Nombre</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="m_last_name" placeholder="Primer Apellido" required>
                                <label for="m_last_name">Primer Apellido *</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="m_second_last_name" placeholder="Segundo Apellido">
                                <label for="m_second_last_name">Segundo Apellido</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="m_id_number" placeholder="00.000.000" required>
                                <label for="m_id_number">Cédula *</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="m_phone" placeholder="Teléfono">
                                <label for="m_phone">Teléfono</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="email" class="form-control" id="m_email" placeholder="email@ejemplo.com">
                                <label for="m_email">Correo Electrónico</label>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <h6 class="fw-bold text-primary mt-2">Información de Contacto</h6>
                            <hr class="mt-0">
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-floating form-floating-outline">
                                <textarea class="form-control" id="m_address" placeholder="Dirección" style="height: 80px;"></textarea>
                                <label for="m_address">Dirección de Habitación</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="quickCreateAwardee()">Guardar y Seleccionar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    updateEndDate();
    document.getElementById('fiscal_year_id').addEventListener('change', updateEndDate);
});

function updateEndDate() {
    const fySelect = document.getElementById('fiscal_year_id');
    const selected = fySelect.options[fySelect.selectedIndex];
    if (selected && selected.dataset.end) {
        document.getElementById('end_date').value = selected.dataset.end;
        document.getElementById('start_date').min = selected.dataset.start;
        document.getElementById('start_date').max = selected.dataset.end;
    }
}

// Categorías
let selectedCats = [];
function addCategoryUI(type = null) {
    let sel, val, name;
    if (type) {
        sel = document.getElementById(`${type}_category_select`);
        val = (type === 'internal' ? 'INT-' : 'EXT-') + sel.value;
        name = sel.options[sel.selectedIndex].text;
    } else {
        sel = document.getElementById('category_select');
        val = sel.value;
        name = sel.options[sel.selectedIndex].text;
        type = val.startsWith('INT') ? 'internal' : 'external';
    }

    const payments = sel.options[sel.selectedIndex].dataset.payments;
    const catTypeLabel = sel.options[sel.selectedIndex].dataset.type;

    if (!sel.value || selectedCats.includes(val)) return;
    
    selectedCats.push(val);
    const id = val.split('-')[1];
    document.getElementById('no-cats-msg').classList.add('d-none');
    
    const cardHtml = `
        <div class="col-md-6" id="cat-card-${val}">
            <div class="card border shadow-none mb-0">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-label-${type === 'internal' ? 'primary' : 'info'} me-3 rounded-pill p-2">
                                <i class="ri-price-tag-3-line ri-20px"></i>
                            </span>
                            <div>
                                <h6 class="mb-1 fw-bold">${type === 'internal' ? 'Interna' : 'Externa'} ${name}</h6>
                                <div class="d-flex gap-2 align-items-center">
                                    <small class="text-muted"><i class="ri-settings-line me-1"></i>Tipo: ${catTypeLabel}</small>
                                    <span class="text-muted small">•</span>
                                    <small class="text-muted"><i class="ri-wallet-line me-1"></i>Pagos: ${payments} vez(es)</small>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-icon btn-text-danger rounded-pill" onclick="removeCategoryUI('${val}')">
                            <i class="ri-close-line ri-20px"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('selected_categories_container').insertAdjacentHTML('beforeend', cardHtml);
    
    // Add hidden input
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = `${type}_categories[]`;
    input.value = id;
    input.id = `input-cat-${val}`;
    document.getElementById('dynamic_inputs').appendChild(input);
    
    updateCounters();
    sel.selectedIndex = 0;
}

function removeCategoryUI(val) {
    selectedCats = selectedCats.filter(c => c !== val);
    const el = document.getElementById(`cat-card-${val}`);
    if (el) el.remove();
    const input = document.getElementById(`input-cat-${val}`);
    if (input) input.remove();
    
    if (selectedCats.length === 0) document.getElementById('no-cats-msg').classList.remove('d-none');
    updateCounters();
}

// Locales
let selectedLocs = [];
function loadSectors(zoneId, target = 'sector_id') {
    const el = document.getElementById(target);
    el.innerHTML = '<option value="">Cargando...</option>';
    el.disabled = true;
    
    if (!zoneId) {
        el.innerHTML = '<option value="">Primero seleccione una zona</option>';
        return;
    }
    
    fetch(`ajax.php?action=get_sectors&zone_id=${zoneId}`)
        .then(r => r.json())
        .then(data => {
            el.innerHTML = '<option value="">Seleccione Sector</option>';
            data.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = s.name;
                el.appendChild(opt);
            });
            el.disabled = false;
        });
}

function loadStalls(sectorId) {
    const el = document.getElementById('stall_id');
    el.innerHTML = '<option value="">Cargando...</option>';
    el.disabled = true;
    
    if (!sectorId) {
        el.innerHTML = '<option value="">Primero seleccione sector</option>';
        return;
    }
    
    fetch(`ajax.php?action=get_stalls&sector_id=${sectorId}`)
        .then(r => r.json())
        .then(data => {
            el.innerHTML = '<option value="">Seleccione Local</option>';
            data.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = `Local ${s.stall_number}`;
                el.appendChild(opt);
            });
            el.disabled = false;
        });
}

function addLocationUI() {
    const sel = document.getElementById('stall_id');
    const val = sel.value;
    if (!val || selectedLocs.includes(val)) return;
    
    const zoneName = document.getElementById('zone_id').options[document.getElementById('zone_id').selectedIndex].text;
    const sectorName = document.getElementById('sector_id').options[document.getElementById('sector_id').selectedIndex].text;
    const stallNum = sel.options[sel.selectedIndex].text;
    
    selectedLocs.push(val);
    document.getElementById('no-locs-msg').classList.add('d-none');
    
    const cardHtml = `
        <div class="col-md-6" id="loc-card-${val}">
            <div class="card border shadow-none mb-0">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-label-success me-3">
                                <i class="ri-store-2-line"></i>
                            </span>
                            <div>
                                <p class="mb-0 fw-bold">${stallNum}</p>
                                <small class="text-muted">${zoneName} • ${sectorName}</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-icon btn-text-danger" onclick="removeLocationUI('${val}')">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('selected_locations_container').insertAdjacentHTML('beforeend', cardHtml);
    
    // Add hidden input
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = `location_ids[]`;
    input.value = val;
    input.id = `input-loc-${val}`;
    document.getElementById('dynamic_inputs').appendChild(input);
    
    updateCounters();
}

function removeLocationUI(val) {
    selectedLocs = selectedLocs.filter(l => l !== val);
    const el = document.getElementById(`loc-card-${val}`);
    if (el) el.remove();
    const input = document.getElementById(`input-loc-${val}`);
    if (input) input.remove();
    
    if (selectedLocs.length === 0) document.getElementById('no-locs-msg').classList.remove('d-none');
    updateCounters();
}

function updateCounters() {
    document.getElementById('cat_count').textContent = selectedCats.length;
    document.getElementById('loc_count').textContent = selectedLocs.length;
}

// Modal sectors
function loadModalSectors(id) { loadSectors(id, 'modal_sector_id'); }

// Máscara para Cédula / RIF
document.getElementById('m_id_number').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value) {
        value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
    e.target.value = value;
});

function quickCreateStall() {
    const sectorId = document.getElementById('modal_sector_id').value;
    const num = document.getElementById('modal_stall_number').value;
    const desc = document.getElementById('modal_stall_description').value;
    if (!sectorId || !num) return Swal.fire('Error', 'Complete zona, sector y número de local', 'error');
    
    $.ajax({
        url: 'ajax.php?action=create_stall',
        method: 'POST',
        data: JSON.stringify({ sector_id: sectorId, stall_number: num, description: desc }),
        contentType: 'application/json',
        success: function(r) {
            if (r.success) {
                Swal.fire('Éxito', 'Local creado. Ahora Selecciónelo en el formulario principal.', 'success');
                bootstrap.Modal.getInstance(document.getElementById('createStallModal')).hide();
                loadStalls(sectorId);
            }
        }
    });
}

function quickCreateAwardee() {
    const data = {
        first_name: document.getElementById('m_first_name').value,
        middle_name: document.getElementById('m_middle_name').value,
        last_name: document.getElementById('m_last_name').value,
        second_last_name: document.getElementById('m_second_last_name').value,
        id_number: document.getElementById('m_id_number').value,
        phone: document.getElementById('m_phone').value,
        email: document.getElementById('m_email').value,
        address: document.getElementById('m_address').value
    };
    
    if (!data.first_name || !data.last_name || !data.id_number) {
        return Swal.fire('Error', 'Nombre, Apellido y Cédula son obligatorios', 'error');
    }
    
    $.ajax({
        url: 'ajax.php?action=create_awardee',
        method: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        success: function(r) {
            if (r.success) {
                Swal.fire('Éxito', 'Adjudicatario registrado correctamente', 'success');
                const sel = document.getElementById('awardee_id');
                const opt = document.createElement('option');
                opt.value = r.id;
                opt.textContent = `${r.awardee.last_name} ${r.awardee.first_name} (${r.awardee.id_number})`;
                opt.selected = true;
                sel.appendChild(opt);
                
                // Trigger select2 update if used
                if ($(sel).data('select2')) {
                    $(sel).trigger('change');
                }
                
                bootstrap.Modal.getInstance(document.getElementById('addAwardeeModal')).hide();
            } else {
                Swal.fire('Error', r.message, 'error');
            }
        }
    });
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
