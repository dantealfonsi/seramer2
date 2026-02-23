<?php
require_once __DIR__ . '/../../controllers/ContractController.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$controller = new ContractController();
$result = $controller->detail((int)$_GET['id']);

if (!$result['success']) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => $result['message']];
    header('Location: index.php');
    exit;
}

$contract = $result['contract'];
$categories = $result['categories'];
$locations = $result['locations'];
$payments = $result['payments'];
$zones = $result['zones'];
$internalCategories = $result['internalCategories'];
$externalCategories = $result['externalCategories'];
$page_title = $result['page_title'];

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <!-- Header con Resumen -->
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <div>
                <h4 class="mb-1">Detalle del Contrato #<?= $contract['id'] ?></h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php">Contratos</a></li>
                        <li class="breadcrumb-item active">Detalle</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="edit.php?id=<?= $contract['id'] ?>" class="btn btn-outline-warning d-flex align-items-center justify-content-center">
                    <i class="ri-edit-line me-1"></i> Editar
                </a>
                <a href="index.php" class="btn btn-outline-secondary d-flex align-items-center justify-content-center">
                    <i class="ri-arrow-left-line me-1"></i> Volver al Listado
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Columna Izquierda: Tarjetas de Información -->
            <div class="col-xl-4">
                <!-- Info Adjudicatario -->
                <div class="card mb-4 border-primary border-top border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <div class="avatar avatar-lg bg-label-primary rounded p-2 me-3">
                                <i class="ri-user-star-line ri-24px"></i>
                            </div>
                            <div>
                                <h5 class="mb-0"><?= htmlspecialchars($contract['awardee_first_name'] . ' ' . $contract['awardee_last_name']) ?></h5>
                                <span class="badge bg-label-secondary">CI: <?= htmlspecialchars($contract['awardee_id_number']) ?></span>
                            </div>
                        </div>
                        <ul class="list-unstyled">
                            <li class="mb-3 d-flex align-items-center">
                                <i class="ri-calendar-event-line me-2 text-primary"></i>
                                <span><strong>Año Fiscal:</strong> <?= $contract['fiscal_year'] ?></span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="ri-calendar-line me-2 text-primary"></i>
                                <span><strong>Vigencia:</strong> <?= date('d/m/Y', strtotime($contract['start_date'])) ?> - <?= date('d/m/Y', strtotime($contract['end_date'])) ?></span>
                            </li>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="ri-file-info-line me-2 text-primary"></i>
                                <span><strong>Tipo:</strong> 
                                    <span class="badge bg-label-<?= $contract['type'] === 'simultaneous' ? 'info' : 'warning' ?>">
                                        <?= $contract['type'] === 'simultaneous' ? 'Simultáneo' : 'Anticipado' ?>
                                    </span>
                                </span>
                            </li>
                            <li class="d-flex align-items-center">
                                <i class="ri-checkbox-circle-line me-2 text-primary"></i>
                                <span><strong>Estado:</strong> 
                                    <span class="badge bg-<?= $contract['status'] === 'active' ? 'success' : 'danger' ?>"><?= ucfirst($contract['status']) ?></span>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Lista de Locales -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="ri-store-2-line me-1"></i> Locales Asignados</h6>
                        <button class="btn btn-sm btn-icon btn-label-primary" data-bs-toggle="modal" data-bs-target="#addLocationModal"><i class="ri-add-line"></i></button>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php if (empty($locations)): ?>
                                <div class="p-3 text-center text-muted">Sin locales asignados</div>
                            <?php else: ?>
                                <?php foreach ($locations as $loc): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0 small fw-bold">Local <?= $loc['stall_number'] ?></h6>
                                        <small class="text-muted"><?= $loc['zone_name'] ?> - <?= $loc['sector_name'] ?></small>
                                    </div>
                                    <button class="btn btn-sm btn-icon btn-text-danger" onclick="removeLocation(<?= $loc['stall_id'] ?>)"><i class="ri-delete-bin-line"></i></button>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Lista de Categorías -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="ri-price-tag-3-line me-1"></i> Rubros</h6>
                        <button class="btn btn-sm btn-icon btn-label-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal"><i class="ri-add-line"></i></button>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php if (empty($categories)): ?>
                                <div class="p-3 text-center text-muted">Sin rubros asignados</div>
                            <?php else: ?>
                                <?php foreach ($categories as $cat): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0 small fw-bold"><?= htmlspecialchars($cat['internal_category_name'] ?? $cat['external_category_name']) ?></h6>
                                        <small class="badge bg-label-<?= $cat['type'] === 'internal' ? 'primary' : 'info' ?> mt-1"><?= $cat['type'] === 'internal' ? 'Interno' : 'Externo' ?></small>
                                    </div>
                                    <button class="btn btn-sm btn-icon btn-text-danger" onclick="removeCategory('<?= $cat['type'] ?>', <?= $cat['internal_category_id'] ?? $cat['external_category_id'] ?>)"><i class="ri-delete-bin-line"></i></button>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Pagos -->
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0">Historial de Pagos</h5>
                        <div class="d-flex gap-2 align-items-center">
                            <span id="bulkPaymentsAction" class="d-none">
                                <button class="btn btn-sm btn-outline-danger me-2" onclick="bulkDeletePayments()">Eliminar Seleccionados</button>
                                <select class="form-select form-select-sm d-inline-block w-auto" id="bulkPaymentStatus" onchange="bulkUpdatePaymentStatus(this.value)">
                                    <option value="">Cambiar Estado...</option>
                                    <option value="paid">Pagado</option>
                                    <option value="pending">Pendiente</option>
                                    <option value="cancelled">Cancelado</option>
                                </select>
                            </span>
                            <a href="<?= BASE_URL ?>/views/cobro/create.php?contract_id=<?= $contract['id'] ?>" class="btn btn-success"><i class="ri-bank-card-line me-1"></i> Registrar Cobro</a>
                        </div>
                    </div>
                    <div class="card-datatable table-responsive">
                        <table id="paymentsTable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" class="form-check-input" id="selectAllPayments"></th>
                                    <th>Referencia</th>
                                    <th>Fecha</th>
                                    <th>Cant.</th>
                                    <th>Tasa €</th>
                                    <th>Monto Bs.</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td><input type="checkbox" class="form-check-input payment-checkbox" value="<?= $payment['id'] ?>"></td>
                                    <td><span class="fw-bold"><?= $payment['payment_reference'] ?? '-' ?></span></td>
                                    <td><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></td>
                                    <td><span class="badge bg-label-secondary"><?= $payment['total_payment_count'] ?></span></td>
                                    <td>Bs. <?= number_format($payment['euro_rate_value'] ?? 0, 2) ?></td>
                                    <td><strong>Bs. <?= number_format($payment['calculated_amount'] ?? 0, 2) ?></strong></td>
                                    <td>
                                        <span class="badge bg-<?= match($payment['status']) { 'paid' => 'success', 'pending' => 'warning', 'cancelled' => 'danger', default => 'secondary' } ?>">
                                            <?= ucfirst($payment['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-icon btn-text-danger" onclick="deletePayment(<?= $payment['id'] ?>)"><i class="ri-delete-bin-line"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals for Add Category/Location -->
<div class="modal fade" id="addLocationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Local</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Zona</label>
                        <select class="form-select" id="zone_id" onchange="loadSectors(this.value)">
                            <option value="">Seleccione Zona</option>
                            <?php foreach ($zones as $zone): ?>
                            <option value="<?= $zone['id'] ?>"><?= $zone['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Sector</label>
                        <select class="form-select" id="sector_id" disabled onchange="loadStalls(this.value)">
                            <option value="">Seleccione Sector</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Local Disponible</label>
                        <select class="form-select" id="stall_id" disabled>
                            <option value="">Seleccione Local</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="ajaxAddLocation()">Asignar Local</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Rubro (Categoría)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-int">Internos</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-ext">Externos</button></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tab-int">
                        <select class="form-select" id="int_cat_id">
                            <option value="">Seleccione Rubro Interno</option>
                            <?php foreach ($internalCategories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="tab-pane fade" id="tab-ext">
                        <select class="form-select" id="ext_cat_id">
                            <option value="">Seleccione Rubro Externo</option>
                            <?php foreach ($externalCategories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="ajaxAddCategory()">Asignar Rubro</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#paymentsTable').DataTable({
        order: [[2, 'desc']],
        columnDefs: [ { targets: 0, orderable: false } ]
    });

    $('.payment-checkbox, #selectAllPayments').on('change', function() {
        if (this.id === 'selectAllPayments') $('.payment-checkbox').prop('checked', this.checked);
        const count = $('.payment-checkbox:checked').length;
        $('#bulkPaymentsAction').toggleClass('d-none', count === 0);
    });
});

const contractId = <?= $contract['id'] ?>;

function loadSectors(zoneId) {
    const el = $('#sector_id');
    el.html('<option>Cargando...</option>').prop('disabled', true);
    $.get(`ajax.php?action=get_sectors&zone_id=${zoneId}`, function(data) {
        let html = '<option value="">Seleccione Sector</option>';
        data.forEach(s => html += `<option value="${s.id}">${s.name}</option>`);
        el.html(html).prop('disabled', false);
    }, 'json');
}

function loadStalls(sectorId) {
    const el = $('#stall_id');
    el.html('<option>Cargando...</option>').prop('disabled', true);
    $.get(`ajax.php?action=get_stalls&sector_id=${sectorId}`, function(data) {
        let html = '<option value="">Seleccione Local</option>';
        data.forEach(s => html += `<option value="${s.id}">Local ${s.stall_number}</option>`);
        el.html(html).prop('disabled', false);
    }, 'json');
}

function ajaxAddLocation() {
    const stallId = $('#stall_id').val();
    if (!stallId) return;
    $.post('ajax_detail.php?action=add_location', { contract_id: contractId, stall_id: stallId }, function(r) {
        if (r.success) location.reload(); else Swal.fire('Error', r.message, 'error');
    }, 'json');
}

function removeLocation(stallId) {
    Swal.fire({ title: '¿Remover local?', icon: 'warning', showCancelButton: true }).then(res => {
        if (res.isConfirmed) $.post('ajax_detail.php?action=remove_location', { contract_id: contractId, stall_id: stallId }, () => location.reload());
    });
}

function ajaxAddCategory() {
    const intId = $('#int_cat_id').val();
    const extId = $('#ext_cat_id').val();
    const type = intId ? 'internal' : (extId ? 'external' : '');
    const categoryId = intId || extId;
    if (!categoryId) return;
    $.post('ajax_detail.php?action=add_category', { contract_id: contractId, category_type: type, category_id: categoryId }, function(r) {
        if (r.success) location.reload(); else Swal.fire('Error', r.message, 'error');
    }, 'json');
}

function removeCategory(type, categoryId) {
    Swal.fire({ title: '¿Remover rubro?', icon: 'warning', showCancelButton: true }).then(res => {
        if (res.isConfirmed) $.post('ajax_detail.php?action=remove_category', { contract_id: contractId, category_type: type, category_id: categoryId }, () => location.reload());
    });
}

function deletePayment(id) {
    Swal.fire({ title: '¿Eliminar pago?', text: 'Se borrará el registro de este cobro', icon: 'warning', showCancelButton: true }).then(res => {
        if (res.isConfirmed) $.post('ajax_detail.php?action=delete_payment', { payment_id: id }, () => location.reload());
    });
}

function getSelectedPaymentIds() { return $('.payment-checkbox:checked').map(function() { return $(this).val(); }).get(); }

function bulkDeletePayments() {
    const ids = getSelectedPaymentIds();
    Swal.fire({ title: `¿Eliminar ${ids.length} pagos?`, icon: 'warning', showCancelButton: true }).then(res => {
        if (res.isConfirmed) $.post('ajax_detail.php?action=bulk_delete_payments', { ids: ids }, () => location.reload());
    });
}

function bulkUpdatePaymentStatus(status) {
    if (!status) return;
    const ids = getSelectedPaymentIds();
    $.post('ajax_detail.php?action=bulk_update_payment_status', { ids: ids, status: status }, () => location.reload());
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
