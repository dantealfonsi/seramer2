<?php
require_once __DIR__ . '/../../controllers/ContractController.php';

$controller = new ContractController();
$data = $controller->index();
$contracts = $data['contracts'];
$metrics = $data['metrics'];
$page_title = $data['page_title'];
$filters = $data['filters'];
$fiscalYears = $data['fiscalYears'];
$totalContracts = count($contracts);

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #696cff 0%, #a2a4ff 100%);
        color: white;
    }
    .main-container {
        padding: 1.5rem;
        background-color: #f5f5f9;
    }
    #contractsTable thead th {
        background-color: #000000 !important;
        color: white !important;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
        border: none;
        padding: 1.25rem 1rem;
    }
    #contractsTable thead th:first-child {
        border-top-left-radius: 8px;
    }
    #contractsTable thead th:last-child {
        border-top-right-radius: 8px;
    }
    .card-inside {
        background-color: #fff;
        border: 1px solid #d9dee3;
        border-radius: 0.5rem;
    }
    .badge-simultaneous { background-color: #e7e7ff; color: #696cff; }
    .badge-advance { background-color: #fff2e2; color: #fdac41; }
</style>

<div class="main-content main-container">
    <div class="container-xxl">
        <div class="row">
            <div class="col-12">
                <!-- Contenedor Blanco Principal -->
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        
                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-center mb-5">
                            <h5 class="mb-0 d-flex align-items-center" style="font-size: 1.75rem; font-weight: 600; color: #43495b;">
                                <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;">
                                    <i class="ri-file-text-line" style="color: #696cff; font-size: 1.5rem;"></i>
                                </div>
                                <?php echo htmlspecialchars($page_title); ?>
                            </h5>
                            <div class="d-flex gap-2">
                                <div id="bulkActionsContainer" class="d-none align-items-center gap-2 me-3">
                                    <span class="badge bg-label-primary px-3 py-2" id="selectedCount">0 seleccionados</span>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ri-settings-3-line me-1"></i> Acciones Masivas
                                        </button>
                                        <ul class="dropdown-menu shadow-sm border-0">
                                            <li class="dropdown-header text-uppercase small fw-bold">Estado de Contrato</li>
                                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="bulkChangeStatus('active')"><i class="ri-checkbox-circle-line text-success me-2"></i>Activo</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="bulkChangeStatus('renewed')"><i class="ri-refresh-line text-info me-2"></i>Renovado</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="bulkChangeStatus('canceled')"><i class="ri-close-circle-line text-danger me-2"></i>Cancelado</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li class="dropdown-header text-uppercase small fw-bold">Estado de Pago</li>
                                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="bulkChangePaymentStatus('up to date')"><i class="ri-check-line text-success me-2"></i>Al día</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="bulkChangePaymentStatus('delinquent')"><i class="ri-alert-line text-warning me-2"></i>Moroso</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="bulkChangePaymentStatus('unable to pay')"><i class="ri-error-warning-line text-danger me-2"></i>Insolvente</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <a href="create.php" class="btn btn-primary px-4 shadow-sm" style="background-color: #696cff; border-color: #696cff; font-weight: 500;">
                                    <i class="ri-add-line me-1"></i> Nuevo Contrato
                                </a>
                            </div>
                        </div>

                        <!-- Filtros Avanzados -->
                        <div class="card-inside p-4 mb-4">
                            <form method="GET" action="index.php" class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small text-uppercase">Adjudicatario / ID</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0"><i class="ri-user-search-line text-muted"></i></span>
                                        <input type="text" name="awardee" class="form-control border-start-0" placeholder="Nombre o Cédula..." value="<?php echo htmlspecialchars($filters['awardee']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold small text-uppercase">Año Fiscal</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0"><i class="ri-calendar-line text-muted"></i></span>
                                        <select name="fiscal_year_id" class="form-select border-start-0">
                                            <option value="">Todos</option>
                                            <?php foreach ($fiscalYears as $fy): ?>
                                                <option value="<?php echo $fy['id']; ?>" <?php echo $filters['fiscal_year_id'] == $fy['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($fy['year']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold small text-uppercase">Estado</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0"><i class="ri-checkbox-circle-line text-muted"></i></span>
                                        <select name="status" class="form-select border-start-0">
                                            <option value="">Todos</option>
                                            <option value="active" <?php echo $filters['status'] === 'active' ? 'selected' : ''; ?>>Activo</option>
                                            <option value="renewed" <?php echo $filters['status'] === 'renewed' ? 'selected' : ''; ?>>Renovado</option>
                                            <option value="canceled" <?php echo $filters['status'] === 'canceled' ? 'selected' : ''; ?>>Cancelado</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold small text-uppercase">Tipo</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0"><i class="ri-file-list-3-line text-muted"></i></span>
                                        <select name="type" class="form-select border-start-0">
                                            <option value="">Todos</option>
                                            <option value="simultaneous" <?php echo $filters['type'] === 'simultaneous' ? 'selected' : ''; ?>>Simultáneo</option>
                                            <option value="advance" <?php echo $filters['type'] === 'advance' ? 'selected' : ''; ?>>Anticipado</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 d-flex align-items-end gap-2 text-nowrap">
                                    <a href="index.php" class="btn btn-outline-secondary w-50 d-flex align-items-center justify-content-center">
                                        <i class="ri-refresh-line me-1"></i> Limpiar
                                    </a>
                                    <button type="submit" class="btn btn-info w-50 text-white d-flex align-items-center justify-content-center">
                                        <i class="ri-search-line me-1"></i> Buscar
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Métricas Rápidas -->
                        <div class="row g-4 mb-4">
                            <!-- Total -->
                            <div class="col-md-4">
                                <div class="card border-0 bg-gradient-primary h-100 shadow-sm" style="border-radius: 0.5rem;">
                                    <div class="card-body p-4 position-relative">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-white bg-opacity-25 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                                                <i class="ri-file-copy-2-line ri-2x text-white"></i>
                                            </div>
                                            <div>
                                                <h3 class="mb-0 text-white fw-bold"><?php echo number_format($metrics['total']); ?></h3>
                                                <p class="mb-0 text-white-50 fw-semibold">Total Contratos</p>
                                            </div>
                                        </div>
                                        <div class="position-absolute" style="right: 15px; bottom: 10px; opacity: 0.1;">
                                            <i class="ri-file-text-line text-white" style="font-size: 4rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Activos -->
                            <div class="col-md-4">
                                <div class="card border-0 h-100 shadow-sm" style="border-radius: 0.5rem; background-color: #e8fadf;">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-success bg-opacity-10 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                                                <i class="ri-checkbox-circle-line ri-2x text-success"></i>
                                            </div>
                                            <div>
                                                <h3 class="mb-0 text-success fw-bold"><?php echo number_format($metrics['active']); ?></h3>
                                                <p class="mb-0 text-muted fw-semibold">Contratos Activos</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Detalles por Tipo -->
                            <div class="col-md-4">
                                <div class="card border-0 h-100 shadow-sm" style="border-radius: 0.5rem; background-color: #f0f2f4;">
                                    <div class="card-body p-3 d-flex flex-column justify-content-center">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted small fw-bold text-uppercase">Tipos de Contrato</span>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <div class="flex-grow-1 bg-white p-2 rounded-2 text-center border">
                                                <h6 class="mb-0 fw-bold text-primary"><?php echo $metrics['simultaneous']; ?></h6>
                                                <small class="text-muted" style="font-size: 0.65rem;">Simultáneos</small>
                                            </div>
                                            <div class="flex-grow-1 bg-white p-2 rounded-2 text-center border">
                                                <h6 class="mb-0 fw-bold text-warning"><?php echo $metrics['advance']; ?></h6>
                                                <small class="text-muted" style="font-size: 0.65rem;">Anticipados</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mensajes Flash -->
                        <?php if (isset($_SESSION['flash_message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['flash_message']['type'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show mb-4" role="alert">
                            <i class="ri-info-line me-2"></i><?php echo $_SESSION['flash_message']['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['flash_message']); ?>
                        <?php endif; ?>

                        <!-- Tabla -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle w-100" id="contractsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 40px !important;"><input type="checkbox" class="form-check-input" id="selectAll"></th>
                                        <th>Adjudicatario</th>
                                        <th class="text-center">Año / Tipo</th>
                                        <th>Locales / Categorías</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Pago</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($contracts as $contract): ?>
                                    <tr data-id="<?= $contract['id'] ?>">
                                        <td><input type="checkbox" class="form-check-input contract-checkbox" value="<?= $contract['id'] ?>"></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-label-info rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background-color: #e0f2ff !important; color: #007bff !important;">
                                                    <i class="ri-user-line"></i>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold text-dark"><?= htmlspecialchars($contract['awardee_name']) ?></span>
                                                    <small class="text-muted font-monospace" style="font-size: 0.75rem;">ID: <?= htmlspecialchars($contract['awardee_id_number']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex flex-column align-items-center gap-1">
                                                <span class="badge bg-label-primary px-3"><?= $contract['fiscal_year'] ?></span>
                                                <span class="badge <?= $contract['type'] === 'simultaneous' ? 'badge-simultaneous' : 'badge-advance' ?> px-2 py-1" style="font-size: 0.7rem; font-weight: 600;">
                                                    <i class="<?= $contract['type'] === 'simultaneous' ? 'ri-time-line' : 'ri-forward-end-line' ?> me-1"></i>
                                                    <?= $contract['type'] === 'simultaneous' ? 'Simultáneo' : 'Anticipado' ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1 mb-1">
                                                <?php if (!empty($contract['locations_list'])): 
                                                    $locs = explode('||', $contract['locations_list']);
                                                    foreach (array_slice($locs, 0, 2) as $loc): ?>
                                                    <span class="badge border text-dark bg-light px-2 py-1" style="font-size: 0.65rem;"><i class="ri-map-pin-line me-1"></i><?= htmlspecialchars($loc) ?></span>
                                                <?php endforeach; if (count($locs) > 2): ?>
                                                    <span class="badge border text-dark bg-light px-2 py-1" style="font-size: 0.65rem;">+<?= count($locs)-2 ?> más</span>
                                                <?php endif; else: ?>
                                                    <small class="text-muted italic">Sin locales</small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php if (!empty($contract['categories_list'])):
                                                    $cats = explode('||', $contract['categories_list']);
                                                    foreach (array_slice($cats, 0, 2) as $cat):
                                                        $isInternal = strpos($cat, 'INT:') === 0;
                                                        $name = substr($cat, 4);
                                                ?>
                                                    <span class="badge bg-label-<?= $isInternal ? 'primary' : 'secondary' ?> px-2" style="font-size: 0.65rem;"><?= htmlspecialchars($name) ?></span>
                                                <?php endforeach; if (count($cats) > 2): ?>
                                                    <span class="badge bg-label-secondary px-2" style="font-size: 0.65rem;">+<?= count($cats)-2 ?></span>
                                                <?php endif; endif; ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $statusMap = ['active' => 'success', 'renewed' => 'info', 'canceled' => 'danger'];
                                            $statusLabels = ['active' => 'Activo', 'renewed' => 'Renovado', 'canceled' => 'Cancelado'];
                                            $color = $statusMap[$contract['status']] ?? 'secondary';
                                            $textLabel = $statusLabels[$contract['status']] ?? $contract['status'];
                                            ?>
                                            <span class="badge bg-label-<?= $color ?> px-3 py-2 w-100" style="font-size: 0.8rem; font-weight: 600;">
                                                <?= $textLabel ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $pStatusMap = ['up to date' => 'success', 'delinquent' => 'warning', 'unable to pay' => 'danger'];
                                            $pStatusLabels = ['up to date' => 'Al día', 'delinquent' => 'Moroso', 'unable to pay' => 'Insolvente'];
                                            $pColor = $pStatusMap[$contract['status_payment']] ?? 'secondary';
                                            $pLabel = $pStatusLabels[$contract['status_payment']] ?? 'Pendiente';
                                            ?>
                                            <span class="badge bg-<?= $pColor ?> px-3 py-2 w-100" style="font-size: 0.8rem; font-weight: 600;">
                                                <?= $pLabel ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-icon border-0 bg-transparent hide-arrow dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class="ri-more-2-line" style="font-size: 1.25rem; color: #43495b;"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                    <li><a class="dropdown-item" href="detail.php?id=<?= $contract['id'] ?>"><i class="ri-eye-line me-2 text-primary"></i>Ver Detalles</a></li>
                                                    <li><a class="dropdown-item" href="edit.php?id=<?= $contract['id'] ?>"><i class="ri-edit-line me-2 text-warning"></i>Editar Contrato</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li class="dropdown-header text-uppercase small fw-bold">Gestión de Estado</li>
                                                    <li><a class="dropdown-item text-success" href="javascript:void(0);" onclick="singleChangeStatus(<?= $contract['id'] ?>, 'active')"><i class="ri-checkbox-circle-line me-2"></i>Marcar Activo</a></li>
                                                    <li><a class="dropdown-item text-info" href="javascript:void(0);" onclick="singleChangeStatus(<?= $contract['id'] ?>, 'renewed')"><i class="ri-refresh-line me-2"></i>Marcar Renovado</a></li>
                                                    <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="singleChangeStatus(<?= $contract['id'] ?>, 'canceled')"><i class="ri-close-circle-line me-2"></i>Marcar Cancelado</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li class="dropdown-header text-uppercase small fw-bold">Estado de Pago</li>
                                                    <li><a class="dropdown-item text-success" href="javascript:void(0);" onclick="singleChangePaymentStatus(<?= $contract['id'] ?>, 'up to date')"><i class="ri-check-line me-2"></i>Marcar Al día</a></li>
                                                    <li><a class="dropdown-item text-warning" href="javascript:void(0);" onclick="singleChangePaymentStatus(<?= $contract['id'] ?>, 'delinquent')"><i class="ri-alert-line me-2"></i>Marcar Moroso</a></li>
                                                    <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="singleChangePaymentStatus(<?= $contract['id'] ?>, 'unable to pay')"><i class="ri-error-warning-line me-2"></i>Marcar Insolvente</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger fw-bold" href="javascript:void(0);" onclick="deleteContract(<?= $contract['id'] ?>)"><i class="ri-delete-bin-line me-2"></i>Eliminar Contrato</a></li>
                                                </ul>
                                            </div>
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
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<!-- DataTables Dependencies -->
<script type="text/javascript" src="../../public/assets/js/pdf_logo.js"></script>
<script type="text/javascript" src="../../public/datatables/jszip.min.js"></script>
<script type="text/javascript" src="../../public/datatables/datatables.min.js"></script>
<script type="text/javascript" src="../../public/datatables/buttons.html5.min.js"></script>
<script type="text/javascript" src="../../public/datatables/pdfmake.min.js"></script>
<script type="text/javascript" src="../../public/datatables/vfs_fonts.js"></script>
<link rel="stylesheet" type="text/css" href="../../public/datatables/datatables.min.css"/> 
<link rel="stylesheet" type="text/css" href="../../public/datatables/buttons.bootstrap5.min.css"/>

<script>
$(document).ready(function() {
    if ($.fn.DataTable) {
        const table = $('#contractsTable').DataTable({
            responsive: true,
            dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rtip',
            buttons: [
                {
                    extend: 'pdfHtml5',
                    text: '<i class="ri-file-pdf-line me-1"></i> PDF',
                    className: 'btn btn-danger btn-sm me-1',
                    exportOptions: { columns: [1, 2, 3, 4, 5] },
                    orientation: 'landscape',
                    pageSize: 'LETTER',
                    customize: function (doc) {
                        doc.content.splice(0, 1);
                        doc.content.unshift({
                            columns: [
                                { image: commonPdfLogo, width: 50 },
                                {
                                    text: [
                                        { text: 'REPÚBLICA BOLIVARIANA DE VENEZUELA\n', fontSize: 10, bold: true },
                                        { text: 'SERVICIO AUTÓNOMO DE MERCADO MUNICIPAL DE BERMÚDEZ\n', fontSize: 10, bold: true },
                                        { text: 'LISTADO DE CONTRATOS REGISTRADOS', fontSize: 12, bold: true }
                                    ],
                                    margin: [10, 0, 0, 0]
                                }
                            ],
                            margin: [0, 0, 0, 10]
                        });
                        const table = doc.content.find(content => content.table);
                        if (table) {
                            table.table.widths = ['25%', '15%', '30%', '15%', '15%'];
                            table.table.body[0].forEach(cell => {
                                cell.fillColor = '#2d4154';
                                cell.color = 'white';
                                cell.bold = true;
                            });
                        }
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="ri-file-excel-line me-1"></i> Excel',
                    className: 'btn btn-success btn-sm me-1',
                    exportOptions: { columns: [1, 2, 3, 4, 5] },
                    title: 'Contratos_Seramer'
                },
                {
                    extend: 'print',
                    text: '<i class="ri-printer-line me-1"></i> Imprimir',
                    className: 'btn btn-info btn-sm',
                    exportOptions: { columns: [1, 2, 3, 4, 5] },
                    messageTop: `
                        <div style="text-align: center; margin-bottom: 20px;">
                            <h1 style="margin: 0; font-size: 1.5em; text-align: center;">Servicio Autonómo de Mercados de Bermúdez</h1>
                            <h2 style="margin: 0; font-size: 1.2em; text-align: center;">Listado de Contratos Registrados</h2>
                        </div>`,
                    customize: function (win) {
                        $(win.document.body).find('table').addClass('w-100').css('width', '100%');
                        $(win.document.body).find('head').append(
                            '<style>@media print { @page { size: letter; margin: 1cm; } } table thead th { background-color: #343a40 !important; color: white !important; -webkit-print-color-adjust: exact; text-align: left !important;}</style>'
                        );
                    }
                },
                'colvis'
            ],
            language: {
                "decimal": "",
                "emptyTable": "No hay datos disponibles en la tabla",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                "infoEmpty": "Mostrando 0 a 0 de 0 entradas",
                "infoFiltered": "(filtrado de _MAX_ entradas totales)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ entradas",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron registros coincidentes",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "aria": {
                    "sortAscending": ": activar para ordenar la columna ascendente",
                    "sortDescending": ": activar para ordenar la columna descendente"
                } 
            },
            order: [[1, 'asc']],
            columnDefs: [
                { targets: [0, 6], orderable: false }
            ]
        });

        $('.contract-checkbox, #selectAll').on('change', function() {
            if (this.id === 'selectAll') {
                $('.contract-checkbox').prop('checked', this.checked);
            }
            const selectedCount = $('.contract-checkbox:checked').length;
            if (selectedCount > 0) {
                $('#bulkActionsContainer').removeClass('d-none').addClass('d-flex');
                $('#selectedCount').text(selectedCount + ' seleccionado' + (selectedCount > 1 ? 's' : ''));
            } else {
                $('#bulkActionsContainer').removeClass('d-flex').addClass('d-none');
                $('#selectAll').prop('checked', false);
            }
        });
    }
});

function getSelectedIds() {
    return $('.contract-checkbox:checked').map(function() { return $(this).val(); }).get();
}

function bulkChangeStatus(status) {
    const ids = getSelectedIds();
    if (ids.length === 0) return;
    Swal.fire({
        title: '¿Confirmar cambio masivo?',
        text: `Se actualizará el estado de ${ids.length} contratos.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar',
        customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'bulk_actions.php?action=update_status',
                method: 'POST',
                data: JSON.stringify({ ids: ids, status: status }),
                contentType: 'application/json',
                success: function(response) { location.reload(); }
            });
        }
    });
}

function bulkChangePaymentStatus(status) {
    const ids = getSelectedIds();
    if (ids.length === 0) return;
    Swal.fire({
        title: '¿Confirmar cambio de pago?',
        text: `Actualizar estado de pago para ${ids.length} contratos.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar',
        customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'bulk_actions.php?action=update_payment_status',
                method: 'POST',
                data: JSON.stringify({ ids: ids, status_payment: status }),
                contentType: 'application/json',
                success: function(response) { location.reload(); }
            });
        }
    });
}

function deleteContract(id) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción eliminará el contrato y sus facturas asociadas. No se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ff3e1d',
        cancelButtonColor: '#8592a3',
        customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'delete.php?id=' + id,
                method: 'POST',
                success: function(response) {
                    const res = typeof response === 'string' ? JSON.parse(response) : response;
                    if (res.success) { location.reload(); }
                    else { Swal.fire('Error', res.message || 'No se pudo eliminar el contrato', 'error'); }
                },
                error: function() { Swal.fire('Error', 'Error en el servidor', 'error'); }
            });
        }
    });
}

function singleChangeStatus(id, status) {
    $.post('bulk_actions.php?action=update_status', JSON.stringify({ ids: [id], status: status }))
      .done(() => location.reload());
}

function singleChangePaymentStatus(id, status) {
    $.post('bulk_actions.php?action=update_payment_status', JSON.stringify({ ids: [id], status_payment: status }))
      .done(() => location.reload());
}
</script>
