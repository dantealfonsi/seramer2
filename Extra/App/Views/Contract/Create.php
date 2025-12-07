<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Crear Contrato de Adjudicación</h5>
            </div>
            <div class="card-body">
                <form action="<?= $app['url'] ?>/contract/store" method="POST" id="contractForm">
                    <?= \Core\Security::csrfField() ?>
                    
                    <!-- Información del Adjudicatario -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold">Información del Adjudicatario</h6>
                            <hr>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="input-group">
                                <select class="form-select" id="awardee_id" name="awardee_id" required>
                                    <option value="">Seleccionar adjudicatario...</option>
                                    <?php foreach ($awardees as $awardee): ?>
                                        <option value="<?= $awardee['id'] ?>">
                                            <?= htmlspecialchars(\App\Models\AwardeeModel::getFullName($awardee)) ?> - 
                                            <?= htmlspecialchars($awardee['id_number']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-primary" id="add_awardee_btn" data-bs-toggle="modal" data-bs-target="#addAwardeeModal">
                                    <i class="ri ri-add-line"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" id="fiscal_year_id" name="fiscal_year_id" required>
                                    <option value="">Seleccionar año fiscal...</option>
                                    <?php foreach ($fiscalYears as $fiscalYear): ?>
                                        <option 
                                            value="<?= $fiscalYear['id'] ?>" 
                                            data-start="<?= $fiscalYear['start_date'] ?>"
                                            data-end="<?= $fiscalYear['end_date'] ?>"
                                            <?= $fiscalYear['status'] === 'active' ? 'selected' : '' ?>>
                                            <?= $fiscalYear['year'] ?> 
                                            (<?= date('d/m/Y', strtotime($fiscalYear['start_date'])) ?> - <?= date('d/m/Y', strtotime($fiscalYear['end_date'])) ?>)
                                            <?= $fiscalYear['status'] === 'active' ? ' • Activo' : '' ?>
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
                                <input 
                                    type="date" 
                                    class="form-control" 
                                    id="start_date" 
                                    name="start_date" 
                                    value="<?= date('Y-m-d') ?>"
                                    required />
                                <label for="start_date">Fecha Inicio *</label>
                            </div>
                            <small class="text-muted">
                                <i class="ri ri-information-line"></i>
                                Por defecto: Fecha actual
                            </small>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input 
                                    type="date" 
                                    class="form-control" 
                                    id="end_date" 
                                    name="end_date" 
                                    readonly
                                    required />
                                <label for="end_date">Fecha Fin *</label>
                            </div>
                            <small class="text-muted">
                                <i class="ri ri-information-line"></i>
                                Se ajusta según el año fiscal
                            </small>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="simultaneous">Simultáneo</option>
                                    <option value="advance">Anticipado</option>
                                </select>
                                <label for="type">Tipo de Contrato *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" id="contract_mode" name="contract_mode" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="monthly" selected>Mensual</option>
                                    <option value="weekly">Semanal</option>
                                </select>
                                <label for="contract_mode">Modalidad de Pago *</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Categorías de Negocio (Rubros) -->
                    <div class="row mb-4">
                        <div class="col-12 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="ri ri-price-tag-3-line me-2 text-primary"></i>
                                <h6 class="fw-bold mb-0">Categorías de Negocio (Rubros)</h6>
                            </div>
                            <hr>
                        </div>
                        
                        <!-- Categorías Externas -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Categorías Externas</label>
                            <div class="input-group">
                                <select class="form-select" id="external_category_select">
                                    <option value="">Seleccionar categoría externa...</option>
                                    <?php foreach ($externalCategories as $category): ?>
                                        <option 
                                            value="<?= $category['id'] ?>"
                                            data-name="<?= htmlspecialchars($category['name']) ?>"
                                            data-type="<?= htmlspecialchars($category['installation_type'] ?? '') ?>"
                                            data-payments="<?= $category['payment_count'] ?>">
                                            <?= htmlspecialchars($category['name']) ?> - <?= htmlspecialchars($category['installation_type']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-primary" id="add_external_category">
                                    <i class="ri ri-add-line"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Categorías Internas -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Categorías Internas</label>
                            <div class="input-group">
                                <select class="form-select" id="internal_category_select">
                                    <option value="">Seleccionar categoría interna...</option>
                                    <?php foreach ($internalCategories as $category): ?>
                                        <option 
                                            value="<?= $category['id'] ?>"
                                            data-name="<?= htmlspecialchars($category['name']) ?>"
                                            data-payments="<?= $category['payment_count'] ?>">
                                            <?= htmlspecialchars($category['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-primary" id="add_internal_category">
                                    <i class="ri ri-add-line"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Contenedor de Categorías Agregadas -->
                        <div class="col-12" id="selected_categories_container">
                            <!-- Las categorías agregadas aparecerán aquí -->
                        </div>
                    </div>
                    
                    <!-- Locales del Contrato -->
                    <div class="row mb-4">
                        <div class="col-12 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="ri ri-store-2-line me-2 text-primary"></i>
                                <h6 class="fw-bold mb-0">Locales del Contrato</h6>
                            </div>
                            <hr>
                        </div>
                        
                        <!-- Filtros en cascada -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Zona</label>
                            <select class="form-select" id="location_zone_select">
                                <option value="">Seleccionar zona...</option>
                                <?php foreach ($zones as $zone): ?>
                                    <option value="<?= $zone['id'] ?>">
                                        <?= htmlspecialchars($zone['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Sector</label>
                            <select class="form-select" id="location_sector_select" disabled>
                                <option value="">Primero seleccione una zona</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Local</label>
                            <select class="form-select" id="location_stall_select" disabled>
                                <option value="">Primero seleccione zona y sector</option>
                            </select>
                        </div>
                        
                        <div class="col-md-1 mb-3 d-flex align-items-end">
                            <button type="button" class="btn btn-primary w-100" id="add_location_btn" disabled title="Agregar local al contrato">
                                <i class="ri ri-add-line"></i>
                            </button>
                        </div>
                        
                        <!-- Botón para crear nuevo local -->
                        <div class="col-12 mb-3">
                            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createStallModal">
                                <i class="ri ri-add-circle-line me-1"></i>
                                Crear Nuevo Local
                            </button>
                        </div>
                        
                        <!-- Contenedor de Locales Agregados -->
                        <div class="col-12" id="selected_locations_container">
                            <!-- Los locales agregados aparecerán aquí -->
                        </div>
                    </div>
                    
                    <!-- Botones de Acción -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="ri ri-save-line me-1"></i>
                                Crear Contrato
                            </button>
                            <a href="<?= $app['url'] ?>/contract/index" class="btn btn-outline-secondary">
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

<!-- Modal para Crear Nuevo Local -->
<div class="modal fade" id="createStallModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri ri-store-2-line me-2"></i>
                    Crear Nuevo Local
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createStallForm">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" id="modal_zone_id" name="zone_id" required>
                                    <option value="">Seleccionar zona...</option>
                                    <?php foreach ($zones as $zone): ?>
                                        <option value="<?= $zone['id'] ?>">
                                            <?= htmlspecialchars($zone['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="modal_zone_id">Zona *</label>
                            </div>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <div class="form-floating form-floating-outline">
                                <select class="form-select" id="modal_sector_id" name="sector_id" required disabled>
                                    <option value="">Primero seleccione una zona...</option>
                                </select>
                                <label for="modal_sector_id">Sector *</label>
                            </div>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="modal_stall_number" name="stall_number" required />
                                <label for="modal_stall_number">Número de Local *</label>
                            </div>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <div class="form-floating form-floating-outline">
                                <textarea class="form-control" id="modal_stall_description" name="description" style="height: 80px;"></textarea>
                                <label for="modal_stall_description">Descripción (opcional)</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <i class="ri ri-information-line me-2"></i>
                        <div>
                            El local se creará y estará disponible para ser agregado al contrato.
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="ri ri-close-line me-1"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="saveStallBtn">
                    <i class="ri ri-save-line me-1"></i>
                    Crear Local
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Agregar Adjudicatario -->
<div class="modal fade" id="addAwardeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri ri-user-add-line me-2"></i>
                    Agregar Nuevo Adjudicatario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addAwardeeForm">
                    <div class="row">
                        <!-- Información Personal -->
                        <div class="col-12 mb-3">
                            <h6 class="text-primary">Información Personal</h6>
                            <hr>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="modal_first_name" name="first_name" required />
                                <label for="modal_first_name">Primer Nombre *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="modal_second_name" name="second_name" />
                                <label for="modal_second_name">Segundo Nombre</label>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="modal_first_surname" name="first_surname" required />
                                <label for="modal_first_surname">Primer Apellido *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="modal_second_surname" name="second_surname" />
                                <label for="modal_second_surname">Segundo Apellido</label>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="text" class="form-control" id="modal_id_number" name="id_number" required />
                                <label for="modal_id_number">Cédula *</label>
                            </div>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="tel" class="form-control" id="modal_phone" name="phone" />
                                <label for="modal_phone">Teléfono</label>
                            </div>
                        </div>
                        
                        <!-- Información de Contacto -->
                        <div class="col-12 mb-3">
                            <h6 class="text-primary">Información de Contacto</h6>
                            <hr>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <input type="email" class="form-control" id="modal_email" name="email" />
                                <label for="modal_email">Correo Electrónico</label>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-floating form-floating-outline">
                                <textarea class="form-control" id="modal_address" name="address" style="height: 58px;"></textarea>
                                <label for="modal_address">Dirección</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <i class="ri ri-information-line me-2"></i>
                        <div>
                            El adjudicatario se creará y estará disponible inmediatamente en el selector.
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="ri ri-close-line me-1"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="saveAwardeeBtn">
                    <i class="ri ri-save-line me-1"></i>
                    Guardar y Seleccionar
                </button>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Arrays para almacenar categorías seleccionadas
    let selectedExternalCategories = [];
    let selectedInternalCategories = [];
    
    const externalCategorySelect = document.getElementById('external_category_select');
    const internalCategorySelect = document.getElementById('internal_category_select');
    const addExternalBtn = document.getElementById('add_external_category');
    const addInternalBtn = document.getElementById('add_internal_category');
    const container = document.getElementById('selected_categories_container');
    
    // Agregar categoría externa
    addExternalBtn.addEventListener('click', function() {
        const selectedOption = externalCategorySelect.options[externalCategorySelect.selectedIndex];
        if (!selectedOption.value) return;
        
        const categoryId = selectedOption.value;
        const categoryName = selectedOption.dataset.name;
        const installationType = selectedOption.dataset.type || 'N/A';
        const payments = selectedOption.dataset.payments;
        
        // Verificar si ya está agregada
        if (selectedExternalCategories.includes(categoryId)) {
            alert('Esta categoría ya ha sido agregada');
            return;
        }
        
        selectedExternalCategories.push(categoryId);
        addCategoryCard('external', categoryId, categoryName, installationType, payments);
        
        // Reset select
        externalCategorySelect.selectedIndex = 0;
    });
    
    // Agregar categoría interna
    addInternalBtn.addEventListener('click', function() {
        const selectedOption = internalCategorySelect.options[internalCategorySelect.selectedIndex];
        if (!selectedOption.value) return;
        
        const categoryId = selectedOption.value;
        const categoryName = selectedOption.dataset.name;
        const payments = selectedOption.dataset.payments;
        
        // Verificar si ya está agregada
        if (selectedInternalCategories.includes(categoryId)) {
            alert('Esta categoría ya ha sido agregada');
            return;
        }
        
        selectedInternalCategories.push(categoryId);
        addCategoryCard('internal', categoryId, categoryName, 'interna', payments);
        
        // Reset select
        internalCategorySelect.selectedIndex = 0;
    });
    
    // Función para agregar tarjeta de categoría
    function addCategoryCard(type, id, name, installationType, payments) {
        const cardHtml = `
            <div class="col-md-6 mb-3 category-card" data-type="${type}" data-id="${id}">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="mb-2">
                                    <span class="badge bg-label-${type === 'external' ? 'warning' : 'info'} me-2">
                                        ${type === 'external' ? 'Externa' : 'Interna'}
                                    </span>
                                    <strong>${name}</strong>
                                </div>
                                <div>
                                    <small class="text-muted d-block">
                                        <i class="ri ri-information-line me-1"></i>
                                        Tipo: ${installationType}
                                    </small>
                                    <small class="text-muted d-block">
                                        <i class="ri ri-money-dollar-circle-line me-1"></i>
                                        Pagos: ${payments} vez(es)
                                    </small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-icon btn-text-danger remove-category" data-type="${type}" data-id="${id}">
                                <i class="ri ri-close-line ri-20px"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="${type === 'external' ? 'external_categories' : 'internal_categories'}[]" value="${id}">
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', cardHtml);
        
        // Agregar evento al botón de remover
        const removeBtn = container.lastElementChild.querySelector('.remove-category');
        removeBtn.addEventListener('click', function() {
            removeCategory(this.dataset.type, this.dataset.id);
        });
    }
    
    // Función para remover categoría
    function removeCategory(type, id) {
        if (type === 'external') {
            const index = selectedExternalCategories.indexOf(id);
            if (index > -1) {
                selectedExternalCategories.splice(index, 1);
            }
        } else {
            const index = selectedInternalCategories.indexOf(id);
            if (index > -1) {
                selectedInternalCategories.splice(index, 1);
            }
        }
        
        // Remover la tarjeta del DOM
        const card = container.querySelector(`.category-card[data-type="${type}"][data-id="${id}"]`);
        if (card) {
            card.remove();
        }
    }
    
    // ==========================================
    // Gestión de Fechas según Año Fiscal
    // ==========================================
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const fiscalYearSelect = document.getElementById('fiscal_year_id');
    
    // Función para actualizar fechas según año fiscal
    function updateDatesFromFiscalYear() {
        const selectedOption = fiscalYearSelect.options[fiscalYearSelect.selectedIndex];
        
        if (!selectedOption.value) {
            startDateInput.min = '';
            startDateInput.max = '';
            endDateInput.value = '';
            return;
        }
        
        const fiscalStartDate = selectedOption.dataset.start;
        const fiscalEndDate = selectedOption.dataset.end;
        
        // Establecer límites para fecha de inicio
        startDateInput.min = fiscalStartDate;
        startDateInput.max = fiscalEndDate;
        
        // Establecer fecha de fin automáticamente
        endDateInput.value = fiscalEndDate;
        
        // Obtener fecha actual
        const today = new Date().toISOString().split('T')[0];
        const fiscalStart = new Date(fiscalStartDate);
        const fiscalEnd = new Date(fiscalEndDate);
        const todayDate = new Date(today);
        
        // Si la fecha actual está dentro del año fiscal, usar hoy
        // Si no, usar el inicio del año fiscal
        if (todayDate >= fiscalStart && todayDate <= fiscalEnd) {
            // Fecha actual está en el rango del año fiscal
            if (startDateInput.value < fiscalStartDate || startDateInput.value > today) {
                startDateInput.value = today;
            }
        } else if (todayDate < fiscalStart) {
            // Año fiscal aún no ha comenzado
            startDateInput.value = fiscalStartDate;
        } else {
            // Año fiscal ya terminó (caso raro pero posible)
            startDateInput.value = fiscalStartDate;
        }
    }
    
    // Actualizar fechas cuando cambia el año fiscal
    fiscalYearSelect.addEventListener('change', updateDatesFromFiscalYear);
    
    // Validar que la fecha de inicio esté en el rango al cambiar
    startDateInput.addEventListener('change', function() {
        const selectedOption = fiscalYearSelect.options[fiscalYearSelect.selectedIndex];
        
        if (!selectedOption.value) return;
        
        const fiscalStartDate = selectedOption.dataset.start;
        const fiscalEndDate = selectedOption.dataset.end;
        
        if (this.value < fiscalStartDate) {
            alert('La fecha de inicio no puede ser anterior al inicio del año fiscal (' + 
                  new Date(fiscalStartDate).toLocaleDateString('es-ES') + ')');
            this.value = fiscalStartDate;
        } else if (this.value > fiscalEndDate) {
            alert('La fecha de inicio no puede ser posterior al fin del año fiscal (' + 
                  new Date(fiscalEndDate).toLocaleDateString('es-ES') + ')');
            this.value = fiscalEndDate;
        }
    });
    
    // Ejecutar al cargar la página si hay un año fiscal seleccionado
    if (fiscalYearSelect.value) {
        updateDatesFromFiscalYear();
    }
    
    // ==========================================
    // Funcionalidad para Agregar Adjudicatario
    // ==========================================
    const saveAwardeeBtn = document.getElementById('saveAwardeeBtn');
    const addAwardeeForm = document.getElementById('addAwardeeForm');
    const awardeeSelect = document.getElementById('awardee_id');
    const addAwardeeModal = document.getElementById('addAwardeeModal');
    
    saveAwardeeBtn.addEventListener('click', function() {
        // Validar campos requeridos
        const firstName = document.getElementById('modal_first_name').value.trim();
        const firstSurname = document.getElementById('modal_first_surname').value.trim();
        const idNumber = document.getElementById('modal_id_number').value.trim();
        
        if (!firstName || !firstSurname || !idNumber) {
            alert('Por favor complete los campos obligatorios: Primer Nombre, Primer Apellido y Cédula');
            return;
        }
        
        // Deshabilitar botón mientras se procesa
        saveAwardeeBtn.disabled = true;
        saveAwardeeBtn.innerHTML = '<i class="ri ri-loader-4-line ri-spin me-1"></i> Guardando...';
        
        // Recopilar datos del formulario
        const formData = new FormData(addAwardeeForm);
        
        // Enviar datos mediante AJAX
        fetch('<?= $app['url'] ?>/awardee/quickStore', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Agregar el nuevo adjudicatario al select
                const newOption = document.createElement('option');
                newOption.value = data.awardee.id;
                newOption.textContent = data.awardee.full_name + ' - ' + data.awardee.id_number;
                newOption.selected = true;
                awardeeSelect.appendChild(newOption);
                
                // Cerrar modal
                const modalInstance = bootstrap.Modal.getInstance(addAwardeeModal);
                modalInstance.hide();
                
                // Limpiar formulario
                addAwardeeForm.reset();
                
                // Mostrar mensaje de éxito
                if (typeof notyf !== 'undefined') {
                    notyf.success(data.message || 'Adjudicatario creado exitosamente');
                } else {
                    alert('Adjudicatario creado exitosamente');
                }
            } else {
                // Mostrar error
                if (typeof notyf !== 'undefined') {
                    notyf.error(data.message || 'Error al crear el adjudicatario');
                } else {
                    alert(data.message || 'Error al crear el adjudicatario');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof notyf !== 'undefined') {
                notyf.error('Error de conexión al crear el adjudicatario');
            } else {
                alert('Error de conexión al crear el adjudicatario');
            }
        })
        .finally(() => {
            // Rehabilitar botón
            saveAwardeeBtn.disabled = false;
            saveAwardeeBtn.innerHTML = '<i class="ri ri-save-line me-1"></i> Guardar y Seleccionar';
        });
    });
    
    // Limpiar formulario cuando se cierra el modal
    addAwardeeModal.addEventListener('hidden.bs.modal', function() {
        addAwardeeForm.reset();
    });
    
    // ==========================================
    // Funcionalidad para Locales del Contrato
    // ==========================================
    let selectedLocations = [];
    
    const locationZoneSelect = document.getElementById('location_zone_select');
    const locationSectorSelect = document.getElementById('location_sector_select');
    const locationStallSelect = document.getElementById('location_stall_select');
    const addLocationBtn = document.getElementById('add_location_btn');
    const locationsContainer = document.getElementById('selected_locations_container');
    
    // Cargar sectores cuando se selecciona zona
    locationZoneSelect.addEventListener('change', function() {
        const zoneId = this.value;
        locationSectorSelect.disabled = true;
        locationStallSelect.disabled = true;
        addLocationBtn.disabled = true;
        locationSectorSelect.innerHTML = '<option value="">Cargando...</option>';
        locationStallSelect.innerHTML = '<option value="">Seleccione sector primero</option>';
        
        if (!zoneId) {
            locationSectorSelect.innerHTML = '<option value="">Primero seleccione una zona</option>';
            return;
        }
        
        // Cargar sectores mediante AJAX
        fetch('<?= $app['url'] ?>/sector/getByZone/' + zoneId)
            .then(response => response.json())
            .then(data => {
                locationSectorSelect.innerHTML = '<option value="">Seleccionar sector...</option>';
                if (data.success && data.sectors.length > 0) {
                    data.sectors.forEach(sector => {
                        const option = document.createElement('option');
                        option.value = sector.id;
                        option.textContent = sector.name;
                        locationSectorSelect.appendChild(option);
                    });
                    locationSectorSelect.disabled = false;
                } else {
                    locationSectorSelect.innerHTML = '<option value="">No hay sectores disponibles</option>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                locationSectorSelect.innerHTML = '<option value="">Error al cargar sectores</option>';
            });
    });
    
    // Cargar locales cuando se selecciona sector
    locationSectorSelect.addEventListener('change', function() {
        const sectorId = this.value;
        locationStallSelect.disabled = true;
        addLocationBtn.disabled = true;
        locationStallSelect.innerHTML = '<option value="">Cargando...</option>';
        
        if (!sectorId) {
            locationStallSelect.innerHTML = '<option value="">Primero seleccione un sector</option>';
            return;
        }
        
        // Cargar locales disponibles mediante AJAX
        fetch('<?= $app['url'] ?>/marketstall/getBySector/' + sectorId)
            .then(response => response.json())
            .then(data => {
                locationStallSelect.innerHTML = '<option value="">Seleccionar local...</option>';
                if (data.success && data.stalls.length > 0) {
                    data.stalls.forEach(stall => {
                        const option = document.createElement('option');
                        option.value = stall.id;
                        option.textContent = 'Local ' + stall.stall_number;
                        option.dataset.zoneName = stall.zone_name;
                        option.dataset.sectorName = stall.sector_name;
                        option.dataset.stallNumber = stall.stall_number;
                        locationStallSelect.appendChild(option);
                    });
                    locationStallSelect.disabled = false;
                } else {
                    locationStallSelect.innerHTML = '<option value="">No hay locales disponibles</option>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                locationStallSelect.innerHTML = '<option value="">Error al cargar locales</option>';
            });
    });
    
    // Habilitar botón agregar cuando se selecciona local
    locationStallSelect.addEventListener('change', function() {
        addLocationBtn.disabled = !this.value;
    });
    
    // Agregar local al contrato
    addLocationBtn.addEventListener('click', function() {
        const selectedOption = locationStallSelect.options[locationStallSelect.selectedIndex];
        if (!selectedOption.value) return;
        
        const stallId = selectedOption.value;
        const zoneName = selectedOption.dataset.zoneName;
        const sectorName = selectedOption.dataset.sectorName;
        const stallNumber = selectedOption.dataset.stallNumber;
        
        // Verificar si ya está agregado
        if (selectedLocations.includes(stallId)) {
            alert('Este local ya ha sido agregado al contrato');
            return;
        }
        
        selectedLocations.push(stallId);
        addLocationCard(stallId, zoneName, sectorName, stallNumber);
        
        // Remover del select
        selectedOption.remove();
        locationStallSelect.selectedIndex = 0;
        addLocationBtn.disabled = true;
    });
    
    // Función para agregar tarjeta de local
    function addLocationCard(id, zoneName, sectorName, stallNumber) {
        const cardHtml = `
            <div class="col-md-6 mb-3 location-card" data-id="${id}">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="mb-2">
                                    <span class="badge bg-label-success me-2">
                                        <i class="ri ri-store-2-line"></i>
                                    </span>
                                    <strong>Local ${stallNumber}</strong>
                                </div>
                                <div>
                                    <small class="text-muted d-block">
                                        <i class="ri ri-map-pin-line me-1"></i>
                                        Zona: ${zoneName}
                                    </small>
                                    <small class="text-muted d-block">
                                        <i class="ri ri-building-line me-1"></i>
                                        Sector: ${sectorName}
                                    </small>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-icon btn-text-danger remove-location" data-id="${id}">
                                <i class="ri ri-close-line ri-20px"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="location_ids[]" value="${id}">
            </div>
        `;
        
        locationsContainer.insertAdjacentHTML('beforeend', cardHtml);
        
        // Agregar evento al botón de remover
        const removeBtn = locationsContainer.lastElementChild.querySelector('.remove-location');
        removeBtn.addEventListener('click', function() {
            removeLocation(this.dataset.id);
        });
    }
    
    // Función para remover local
    function removeLocation(id) {
        const index = selectedLocations.indexOf(id);
        if (index > -1) {
            selectedLocations.splice(index, 1);
        }
        
        // Remover la tarjeta del DOM
        const card = locationsContainer.querySelector(`.location-card[data-id="${id}"]`);
        if (card) {
            card.remove();
        }
    }
    
    // ==========================================
    // Funcionalidad para Crear Nuevo Local
    // ==========================================
    const createStallModal = document.getElementById('createStallModal');
    const createStallForm = document.getElementById('createStallForm');
    const saveStallBtn = document.getElementById('saveStallBtn');
    const modalZoneSelect = document.getElementById('modal_zone_id');
    const modalSectorSelect = document.getElementById('modal_sector_id');
    
    // Cargar sectores en el modal cuando se selecciona zona
    modalZoneSelect.addEventListener('change', function() {
        const zoneId = this.value;
        modalSectorSelect.disabled = true;
        modalSectorSelect.innerHTML = '<option value="">Cargando...</option>';
        
        if (!zoneId) {
            modalSectorSelect.innerHTML = '<option value="">Primero seleccione una zona...</option>';
            return;
        }
        
        fetch('<?= $app['url'] ?>/sector/getByZone/' + zoneId)
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
    
    // Guardar nuevo local
    saveStallBtn.addEventListener('click', function() {
        const zoneId = document.getElementById('modal_zone_id').value;
        const sectorId = document.getElementById('modal_sector_id').value;
        const stallNumber = document.getElementById('modal_stall_number').value.trim();
        
        if (!zoneId || !sectorId || !stallNumber) {
            alert('Por favor complete todos los campos obligatorios');
            return;
        }
        
        // Deshabilitar botón
        saveStallBtn.disabled = true;
        saveStallBtn.innerHTML = '<i class="ri ri-loader-4-line ri-spin me-1"></i> Guardando...';
        
        const formData = new FormData(createStallForm);
        
        fetch('<?= $app['url'] ?>/marketstall/quickStore', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Cerrar modal
                const modalInstance = bootstrap.Modal.getInstance(createStallModal);
                modalInstance.hide();
                
                // Limpiar formulario
                createStallForm.reset();
                modalSectorSelect.disabled = true;
                
                // Mostrar éxito
                if (typeof notyf !== 'undefined') {
                    notyf.success(data.message || 'Local creado exitosamente');
                } else {
                    alert('Local creado exitosamente');
                }
                
                // Si la zona/sector coinciden con los filtros actuales, recargar locales
                if (locationZoneSelect.value == zoneId && locationSectorSelect.value == sectorId) {
                    locationSectorSelect.dispatchEvent(new Event('change'));
                }
            } else {
                if (typeof notyf !== 'undefined') {
                    notyf.error(data.message || 'Error al crear el local');
                } else {
                    alert(data.message || 'Error al crear el local');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof notyf !== 'undefined') {
                notyf.error('Error de conexión al crear el local');
            } else {
                alert('Error de conexión al crear el local');
            }
        })
        .finally(() => {
            saveStallBtn.disabled = false;
            saveStallBtn.innerHTML = '<i class="ri ri-save-line me-1"></i> Crear Local';
        });
    });
    
    // Limpiar formulario cuando se cierra el modal
    createStallModal.addEventListener('hidden.bs.modal', function() {
        createStallForm.reset();
        modalSectorSelect.disabled = true;
        modalSectorSelect.innerHTML = '<option value="">Primero seleccione una zona...</option>';
    });
});
</script>
<?php $pageScripts = ob_get_clean(); ?>

