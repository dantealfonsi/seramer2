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

<style>
    /* Estilo para que solo el header de la tabla de pagos sea negro */
    #paymentsTable thead th {
        background-color: #000000 !important;
        color: #ffffff !important;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
        border: none;
    }
    
    #paymentsTable {
        border-collapse: separate;
        border-spacing: 0;
    }

    #paymentsTable thead th:first-child {
        border-top-left-radius: 8px;
    }

    #paymentsTable thead th:last-child {
        border-top-right-radius: 8px;
    }
</style>

<div class="main-content">
    <div class="container-fluid">
        <!-- Header con Resumen -->
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <div>
                <h4 class="mb-1 d-flex align-items-center">
                    <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;">
                        <i class="ri-file-list-3-line" style="color: #696cff; font-size: 1.5rem;"></i>
                    </div>
                    Detalle del Contrato #<?= $contract['id'] ?>
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php">Contratos</a></li>
                        <li class="breadcrumb-item active">Detalle</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <!-- Botón de Editar movido a la información del contrato como se solicitó, 
                     pero dejamos un botón de Ver Pagos o Volver aquí -->
                <a href="index.php" class="btn btn-outline-secondary d-flex align-items-center justify-content-center">
                    <i class="ri-arrow-left-line me-1"></i> Volver al Listado
                </a>
            </div>
        </div>

        <!-- Fila Superior: Info, Categorías y Locales -->
        <div class="row g-4 mb-4">
            <!-- Columna 1: Información del Contrato -->
            <div class="col-xl-4 col-md-6">
                <div class="card h-100 border-primary border-top border-4">
                    <div class="card-header d-flex justify-content-between align-items-center pb-2">
                        <h6 class="mb-0 fw-bold"><i class="ri-information-line me-1"></i> Información del Contrato</h6>
                        <a href="edit.php?id=<?= $contract['id'] ?>" class="btn btn-sm btn-outline-warning" title="Editar Contrato">
                            <i class="ri-edit-line"></i> Editar
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Identificación -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-1 fw-bold">Contrato #<?= $contract['id'] ?></h6>
                            <h5 class="mb-0 fw-bold"><?= htmlspecialchars($contract['awardee_first_name'] . ' ' . $contract['awardee_last_name']) ?></h5>
                            <span class="text-muted d-block mt-1">C.I.: <?= htmlspecialchars($contract['awardee_id_number']) ?></span>
                        </div>
                        
                        <!-- Detalles: Fechas y Modalidades -->
                        <div class="row text-sm g-3 mt-2 border-top pt-3">
                            <div class="col-6">
                                <div class="text-muted mb-1"><small>Año Fiscal</small></div>
                                <div class="fw-bold"><?= $contract['fiscal_year'] ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted mb-1"><small>Tipo</small></div>
                                <div><span class="badge bg-label-<?= $contract['type'] === 'simultaneous' ? 'info' : 'warning' ?>">
                                    <?= $contract['type'] === 'simultaneous' ? 'Simultáneo' : 'Anticipado' ?>
                                </span></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted mb-1"><small>Fecha Inicio</small></div>
                                <div class="fw-bold"><?= date('d/m/Y', strtotime($contract['start_date'])) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted mb-1"><small>Fecha Fin</small></div>
                                <div class="fw-bold"><?= date('d/m/Y', strtotime($contract['end_date'])) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted mb-1"><small>Modalidad</small></div>
                                <div class="fw-bold"><?= isset($contract['contract_mode']) && $contract['contract_mode'] == 'weekly' ? 'Semanal' : 'Mensual' ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted mb-1"><small>Estado</small></div>
                                <div>
                                    <?php
                                    $cStatusMap = ['active' => 'success', 'renewed' => 'info', 'canceled' => 'danger'];
                                    $cStatusLabels = ['active' => 'Activo', 'renewed' => 'Renovado', 'canceled' => 'Cancelado'];
                                    $cColor = $cStatusMap[$contract['status']] ?? 'secondary';
                                    $cLabel = $cStatusLabels[$contract['status']] ?? ucfirst($contract['status']);
                                    ?>
                                    <span class="badge bg-label-<?= $cColor ?> w-100"><?= $cLabel ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna 2: Categorías de Negocio -->
            <div class="col-xl-4 col-md-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="ri-price-tag-3-line me-1"></i> Categorías de Negocio</h6>
                        <button class="btn btn-sm btn-icon btn-label-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal" title="Agregar Rubro"><i class="ri-add-line"></i></button>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush h-100 overflow-auto" style="max-height: 400px;">
                            <?php if (empty($categories)): ?>
                                <div class="p-4 text-center text-muted">Aún no hay rubros asignados</div>
                            <?php else: ?>
                                <?php foreach ($categories as $cat): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-label-<?= $cat['type'] === 'internal' ? 'primary' : 'info' ?> rounded p-2 me-3 d-flex align-items-center justify-content-center">
                                            <i class="ri-price-tag-3-line ri-20px"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold"><?= htmlspecialchars($cat['internal_category_name'] ?? $cat['external_category_name']) ?></h6>
                                            <small class="text-muted">Rubro <?= $cat['type'] === 'internal' ? 'Interno' : 'Externo' ?></small>
                                        </div>
                                    </div>
                                    <button class="btn btn-sm btn-icon btn-text-danger" onclick="removeCategory('<?= $cat['type'] ?>', <?= $cat['internal_category_id'] ?? $cat['external_category_id'] ?>)"><i class="ri-delete-bin-line"></i></button>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna 3: Locales Asignados -->
            <div class="col-xl-4 col-md-12">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="ri-store-2-line me-1"></i> Locales Asignados</h6>
                        <button class="btn btn-sm btn-icon btn-label-primary" data-bs-toggle="modal" data-bs-target="#addLocationModal" title="Agregar Local"><i class="ri-add-line"></i></button>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush h-100 overflow-auto" style="max-height: 400px;">
                            <?php if (empty($locations)): ?>
                                <div class="p-4 text-center text-muted">Aún no hay locales asignados</div>
                            <?php else: ?>
                                <?php foreach ($locations as $loc): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-label-success rounded p-2 me-3 d-flex align-items-center justify-content-center">
                                            <i class="ri-store-2-line ri-20px"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">Local <?= htmlspecialchars($loc['stall_number']) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($loc['zone_name']) ?> - <?= htmlspecialchars($loc['sector_name']) ?></small>
                                        </div>
                                    </div>
                                    <button class="btn btn-sm btn-icon btn-text-danger" onclick="removeLocation(<?= $loc['stall_id'] ?>)"><i class="ri-delete-bin-line"></i></button>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fila Inferior: Datatable de Pagos -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0"><i class="ri-money-dollar-circle-line me-1 text-primary"></i> Pagos del Contrato</h5>
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
                                        <?php 
                                            $pStatusMap = [
                                                'paid' => ['label' => 'Pagado', 'class' => 'success'],
                                                'pending' => ['label' => 'Pendiente', 'class' => 'warning'],
                                                'cancelled' => ['label' => 'Cancelado', 'class' => 'danger'],
                                                'refunded' => ['label' => 'Reembolsado', 'class' => 'info']
                                            ];
                                            $pStatus = $payment['status'];
                                            // Lógica para marcar como Moroso si está pendiente y vencido
                                            $isOverdue = ($pStatus === 'pending' && strtotime($payment['payment_date']) < strtotime(date('Y-m-d')));
                                            $pLabel = $isOverdue ? 'Moroso' : ($pStatusMap[$pStatus]['label'] ?? ucfirst($pStatus));
                                            $pClass = $isOverdue ? 'danger' : ($pStatusMap[$pStatus]['class'] ?? 'secondary');
                                        ?>
                                        <span class="badge bg-label-<?= $pClass ?> w-100">
                                            <?= $pLabel ?>
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

<!-- DataTables Dependencies -->
<script type="text/javascript" src="../../public/assets/js/pdf_logo.js"></script>
<script type="text/javascript" src="../../public/datatables/jszip.min.js"></script>
<script type="text/javascript" src="../../public/datatables/datatables.min.js"></script>
<script type="text/javascript" src="../../public/datatables/pdfmake.min.js"></script>
<script type="text/javascript" src="../../public/datatables/vfs_fonts.js"></script>
<link rel="stylesheet" type="text/css" href="../../public/datatables/datatables.min.css"/> 
<link rel="stylesheet" type="text/css" href="../../public/datatables/buttons.bootstrap5.min.css"/>

<script>
const contractId = <?= $contract['id'] ?>;

$(document).ready(function() {
    $('#paymentsTable').DataTable({
        dom: '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        buttons: [
            {
                extend: 'pdfHtml5',
                text: '<i class="ri-file-pdf-line me-1"></i> PDF',
                className: 'btn btn-danger btn-sm me-1 mb-3',
                pageSize: 'LETTER',
                exportOptions: { columns: [1, 2, 3, 4, 5, 6] },
                customize: function (doc) {
                    if (typeof commonPdfLogo !== 'undefined') {
                        doc.content.splice(0, 1);
                        doc.content.unshift({
                            columns: [
                                { image: commonPdfLogo, width: 50 },
                                {
                                    text: [
                                        { text: 'SERVICIO AUTÓNOMO DE MERCADO MUNICIPAL DE BERMÚDEZ\n', fontSize: 10, bold: true },
                                        { text: 'HISTORIAL DE PAGOS DEL CONTRATO #' + contractId, fontSize: 12, bold: true }
                                    ],
                                    margin: [10, 0, 0, 0]
                                }
                            ],
                            margin: [0, 0, 0, 10]
                        });
                    }
                }
            },
            {
                extend: 'excelHtml5',
                text: '<i class="ri-file-excel-line me-1"></i> Excel',
                className: 'btn btn-success btn-sm me-1 mb-3',
                exportOptions: { columns: [1, 2, 3, 4, 5, 6] },
                title: 'Pagos del Contrato #' + contractId
            },
            {
                extend: 'print',
                text: '<i class="ri-printer-line me-1"></i> Imprimir',
                className: 'btn btn-outline-light btn-sm mb-3',
                exportOptions: { columns: [1, 2, 3, 4, 5, 6] },
                title: 'Pagos del Contrato #' + contractId,
                customize: function (win) {
                    $(win.document.body).css('font-size', '10pt').css('color', '#000').css('background', '#fff');
                    $(win.document.body).find('table').css('color', '#000');
                }
            }
        ],
        order: [[2, 'desc']],
        columnDefs: [ { targets: [0, 7], orderable: false } ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        }
    });

    $('.payment-checkbox, #selectAllPayments').on('change', function() {
        if (this.id === 'selectAllPayments') $('.payment-checkbox').prop('checked', this.checked);
        const count = $('.payment-checkbox:checked').length;
        $('#bulkPaymentsAction').toggleClass('d-none', count === 0);
    });
});


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
