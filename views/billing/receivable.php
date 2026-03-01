<?php
require_once __DIR__ . '/../../controllers/BillingController.php';

$controller = new BillingController();

// Get search parameters
$searchTerm = $_GET['search_term'] ?? '';
$searchType = $_GET['search_type'] ?? 'id_number';
$idPrefix = $_GET['id_prefix'] ?? 'V';

// Lógica de autoselección: Si es búsqueda por ID y el término es numérico
if ($searchType === 'id_number' && !empty($searchTerm)) {
    // Si el término contiene letras al inicio (como V123 o J123), las separamos
    if (preg_match('/^([VEJ])(\d+)$/i', $searchTerm, $matches)) {
        $idPrefix = strtoupper($matches[1]);
        $searchTerm = $matches[2];
    } elseif (ctype_digit($searchTerm)) {
        // Si solo son números, decidimos por longitud (RIF suele ser > 8 dígitos o empezar por un patrón)
        // En Venezuela Cédulas son hasta 3X.XXX.XXX (8 dígitos). RIFs suelen tener 9 dígitos + dígito de control.
        if (strlen($searchTerm) >= 9) {
            $idPrefix = 'J';
        } else {
            $idPrefix = 'V';
        }
    }
}

// Perform search
$result = $controller->searchDebtor($searchTerm, $searchType, ['id_prefix' => $idPrefix]);

$page_title = 'Procesar Cobros';
$has_results = $result['has_results'];
$error = $result['error'] ?? null;

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<style>
    .debt-amount-badge {
        font-size: 1.5rem;
        font-weight: bold;
        padding: 0.5rem 1.5rem;
        border-radius: 10px;
    }
    .search-error-msg {
        color: #dc3545;
        font-size: 0.85rem;
        margin-top: 0.25rem;
        display: none;
    }
    .input-group-text-prefix {
        background-color: #f8f9fa;
        min-width: 60px;
    }
</style>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header d-flex justify-content-between align-items-center bg-white border-0 py-3">
                        <h5 class="mb-0 card-title d-flex align-items-center" style="font-size: 1.4rem;font-weight: 600;">
                            <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; background-color: #e7e7ff !important;">
                                <i class="ri-money-dollar-circle-line" style="color: #696cff; font-size: 2rem;"></i>
                            </div>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                    </div>
                    
                    <div class="card-body pt-0">
                        <!-- Redesigned Search Form -->
                        <div class="bg-light p-4 rounded-3 mb-4">
                            <form method="GET" action="" id="searchForm">
                                <div class="row g-4">
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold text-muted small text-uppercase mb-2">Método de Búsqueda</label>
                                        <select name="search_type" id="search_type" class="form-select form-select-lg border-0 shadow-sm">
                                            <option value="id_number" <?php echo $searchType === 'id_number' ? 'selected' : ''; ?>>Cédula / RIF</option>
                                            <option value="name" <?php echo $searchType === 'name' ? 'selected' : ''; ?>>Nombre del Moroso</option>
                                            <option value="stall" <?php echo $searchType === 'stall' ? 'selected' : ''; ?>>Número de Puesto</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold text-muted small text-uppercase mb-2">Dato a Ingresar</label>
                                        <div class="input-group input-group-lg shadow-sm">
                                            <select name="id_prefix" id="id_prefix" class="form-select border-0 bg-white" style="max-width: 80px; <?php echo $searchType !== 'id_number' ? 'display: none;' : ''; ?>">
                                                <option value="V" <?php echo $idPrefix === 'V' ? 'selected' : ''; ?>>V</option>
                                                <option value="E" <?php echo $idPrefix === 'E' ? 'selected' : ''; ?>>E</option>
                                                <option value="J" <?php echo $idPrefix === 'J' ? 'selected' : ''; ?>>J</option>
                                            </select>
                                            <input type="text" name="search_term" id="search_term" class="form-control border-0" 
                                                   placeholder="Ingrese el dato..." 
                                                   value="<?php echo htmlspecialchars($searchTerm); ?>" required>
                                            <button class="btn btn-primary px-4" type="submit" id="btnSearch">
                                                <i class="ri-search-line me-1"></i> Buscar
                                            </button>
                                        </div>
                                        <div id="search_validation_msg" class="search-error-msg"></div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="ri-error-warning-line me-2"></i> <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($has_results): ?>
                            <?php 
                                $awardee = $result['awardee'];
                                $contractPayments = $result['contract_payments'];
                                $sanctions = $result['sanctions'];
                                $totalDebt = $result['total_debt'];
                                $paymentMethods = $result['paymentMethods'];
                            ?>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card bg-label-primary shadow-none border">
                                        <div class="card-body">
                                            <h6 class="card-title text-primary"><i class="ri-user-line me-2"></i>Datos del Contribuyente</h6>
                                            <p class="mb-1"><strong>Nombre:</strong> <?php echo htmlspecialchars($awardee['first_name'] . ' ' . $awardee['last_name']); ?></p>
                                            <p class="mb-1"><strong>Cédula/RIF:</strong> <?php echo htmlspecialchars($awardee['id_number']); ?></p>
                                            <p class="mb-0"><strong>Teléfono:</strong> <?php echo htmlspecialchars($awardee['phone'] ?? 'N/A'); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-label-danger shadow-none border h-100 d-flex align-items-center justify-content-center">
                                        <div class="card-body text-center">
                                            <p class="mb-1 text-danger fw-semibold">DEUDA TOTAL PENDIENTE</p>
                                            <div class="debt-amount-badge bg-danger text-white">
                                                Bs. <?php echo number_format($totalDebt, 2); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="nav-align-top mb-4">
                                <ul class="nav nav-tabs" role="tablist">
                                    <li class="nav-item">
                                        <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#tab-contracts">
                                            <i class="ri-file-text-line me-1"></i> Contratos
                                            <span class="badge rounded-pill bg-label-primary ms-1"><?php echo count($contractPayments); ?></span>
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-fines">
                                            <i class="ri-alert-line me-1"></i> Multas
                                            <span class="badge rounded-pill bg-label-danger ms-1"><?php echo count($sanctions); ?></span>
                                        </button>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane fade show active" id="tab-contracts" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover w-100" id="contractsBillingTable">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Referencia</th>
                                                        <th>Período</th>
                                                        <th>Monto (EUR)</th>
                                                        <th>Tasa (Bs)</th>
                                                        <th>Monto (Bs)</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($contractPayments as $payment): ?>
                                                        <?php if (($payment['status'] ?? '') !== 'paid'): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($payment['payment_reference'] ?? ''); ?></td>
                                                            <td><?php echo htmlspecialchars(($payment['month_name'] ?? '') . ' ' . ($payment['year'] ?? '')); ?></td>
                                                            <td><?php echo number_format($payment['amount_euro'] ?? 0, 2); ?></td>
                                                            <td><?php echo number_format($payment['rate_amount'] ?? 40, 2); ?></td>
                                                            <td><strong>Bs. <?php echo number_format($payment['amount_bs'] ?? 0, 2); ?></strong></td>
                                                            <td>
                                                                <button class="btn btn-sm btn-success btn-pay-action" 
                                                                        data-type="contract"
                                                                        data-id="<?php echo $payment['id']; ?>"
                                                                        data-amount="<?php echo $payment['amount_bs']; ?>"
                                                                        data-label="Pago de Contrato: <?php echo htmlspecialchars($payment['payment_reference']); ?>">
                                                                    <i class="ri-money-dollar-circle-line me-1"></i> Pagar
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="tab-fines" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover w-100" id="finesBillingTable">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Fecha de Sanción</th>
                                                        <th>Tipo de Infracción</th>
                                                        <th>Monto</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($sanctions as $sanction): ?>
                                                        <tr>
                                                            <td><?php echo date('d/m/Y', strtotime($sanction['imposition_date'])); ?></td>
                                                            <td><?php echo htmlspecialchars($sanction['infraction_type_name'] ?? 'Infracción'); ?></td>
                                                            <td><strong>Bs. <?php echo number_format($sanction['fine_amount'], 2); ?></strong></td>
                                                            <td>
                                                                <button class="btn btn-sm btn-danger btn-pay-action" 
                                                                        data-type="fine"
                                                                        data-id="<?php echo $sanction['sanction_id']; ?>"
                                                                        data-amount="<?php echo $sanction['fine_amount']; ?>"
                                                                        data-label="Pago de Multa #<?php echo $sanction['sanction_id']; ?>">
                                                                    <i class="ri-money-dollar-circle-line me-1"></i> Pagar
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Processing Modal -->
<div class="modal fade" id="paymentProcessModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Recaudación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    <input type="hidden" name="payment_type" id="proc-type">
                    <input type="hidden" name="payment_id" id="proc-id">
                    <input type="hidden" name="sanction_id" id="proc-sanction-id">
                    
                    <div class="mb-4 text-center">
                        <div class="p-3 bg-label-info rounded">
                            <h6 id="proc-label" class="mb-1"></h6>
                            <h3 id="proc-display-amount" class="mb-0 text-info fw-bold"></h3>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Monto a Cobrar (Bs.)</label>
                        <input type="number" step="0.01" name="amount" id="proc-amount" class="form-control form-control-lg" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Método de Pago</label>
                        <select name="payment_method_id" id="proc-payment-method" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php if (isset($paymentMethods)): ?>
                                <?php foreach ($paymentMethods as $pm): ?>
                                    <option value="<?php echo $pm['id']; ?>"><?php echo htmlspecialchars($pm['name']); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="transaction_ref_container">
                        <label class="form-label">Referencia Bancaria/Transacción</label>
                        <input type="text" name="transaction_reference" id="proc-transaction-ref" class="form-control" placeholder="Solo números">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmCollection">
                    <i class="ri-check-line me-1"></i> Registrar Cobro
                </button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<!-- Scripts after footer to ensure jQuery is loaded -->
<script type="text/javascript" src="../../public/assets/js/pdf_logo.js"></script>
<script type="text/javascript" src="../../public/datatables/datatables.min.js"></script>
<script type="text/javascript" src="../../public/datatables/pdfmake.min.js"></script>
<script type="text/javascript" src="../../public/datatables/vfs_fonts.js"></script>
<link rel="stylesheet" type="text/css" href="../../public/datatables/datatables.min.css"/> 
<link rel="stylesheet" type="text/css" href="../../public/datatables/buttons.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="../../public/assets/css/dani-styles.css"/>

<script>
$(document).ready(function() {
    
    // Search Type Toggle
    $('#search_type').on('change', function() {
        const type = $(this).val();
        if (type === 'id_number') {
            $('#id_prefix').show();
            $('#search_term').attr('placeholder', 'Ingrese el número...');
        } else {
            $('#id_prefix').hide();
            $('#search_term').attr('placeholder', type === 'name' ? 'Ingrese el nombre...' : 'Ingrese el número de puesto...');
        }
        $('#search_validation_msg').hide();
    });

    // Search Validation
    $('#searchForm').on('submit', function(e) {
        const type = $('#search_type').val();
        const term = $('#search_term').val();
        const prefix = $('#id_prefix').val();
        const errorMsg = $('#search_validation_msg');
        
        if (type === 'id_number') {
            if (prefix === 'V' || prefix === 'E') {
                if (!/^\d{7,8}$/.test(term)) {
                   e.preventDefault();
                   errorMsg.text('Para ' + prefix + ' debe ingresar de 7 a 8 números.').show();
                   return false;
                }
            } else if (prefix === 'J') {
                if (!/^\d{1,9}$/.test(term)) {
                   e.preventDefault();
                   errorMsg.text('Para J debe ingresar de 1 a 9 números.').show();
                   return false;
                }
            }
        }
        errorMsg.hide();
    });

    // Payment Method Toggle for Transaction Ref
    $('#proc-payment-method').on('change', function() {
        const text = $(this).find('option:selected').text();
        const container = $('#transaction_ref_container');
        const input = $('#proc-transaction-ref');
        
        if (text.toLowerCase().includes('efectivo')) {
            container.fadeOut();
            input.val('').prop('required', false);
        } else {
            container.fadeIn();
            input.prop('required', true);
        }
    });

    // Transaction Ref numeric-only validation
    $('#proc-transaction-ref').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    
    // Common settings
    const commonLanguage = {
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
        "paginate": { "first": "Primero", "last": "Último", "next": "Siguiente", "previous": "Anterior" },
        "aria": { "sortAscending": ": activar para ordenar la columna ascendente", "sortDescending": ": activar para ordenar la columna descendente" } 
    };

    const commonPdfCustom = function (doc, title) {
        doc.content.splice(0, 0, {
            text: 'Servicio Autonómo de Mercados de Bermúdez', 
            alignment: 'center', 
            style: 'header1'
        }, {
            text: title, 
            alignment: 'center', 
            style: 'header2'
        }, {
            text: '', margin: [0, 0, 0, 10]
        });

        doc.styles.header1 = { fontSize: 14, bold: true, margin: [0, 10, 0, 0] };
        doc.styles.header2 = { fontSize: 12, bold: true, margin: [0, 0, 0, 5] };

        const table = doc.content.find(content => content.table);
        if (table && table.table.body.length > 0) {
            const headerRow = table.table.body[0];
            headerRow.forEach(cell => {
                cell.fillColor = '#343a40'; 
                cell.color = '#ffffff';      
                cell.bold = true;
                cell.alignment = 'left'; 
            });
            table.table.widths = Array(table.table.body[0].length).fill('*');
        }
    };
    
    // Initialize DataTables for Contracts
    if ($.fn.DataTable) {
        $('#contractsBillingTable').DataTable({ 
            responsive: true,
            dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rtip',
            buttons: [
                {
                    extend: 'pdfHtml5',
                    text: '<i class="ri-file-pdf-line"></i> PDF',
                    className: 'btn btn-danger btn-sm me-1',
                    orientation: 'portrait',
                    pageSize: 'LETTER', 
                    exportOptions: { columns: [0, 1, 2, 3, 4] }, // Exclude Acciones (5)
                    customize: function (doc) {
                        // 1. Remover título por defecto
                        doc.content.splice(0, 1);

                        // 2. Agregar Encabezado Institucional (Logo + Texto)
                        doc.content.unshift({
                            columns: [
                                {
                                    image: commonPdfLogo,
                                    width: 50
                                },
                                {
                                    text: [
                                        { text: 'REPÚBLICA BOLIVARIANA DE VENEZUELA\\n', fontSize: 10, bold: true },
                                        { text: 'GOBIERNO BOLIVARIANA DE VENEZUELA\\n', fontSize: 10, bold: true },
                                        { text: 'SERVICIO AUTÓNOMO DE MERCADO MUNICIPAL DE BERMÚDEZ\\n', fontSize: 10, bold: true },
                                        { text: 'DIRECCIÓN DE ADMINISTRACIÓN "SERAMER"', fontSize: 10, bold: true }
                                    ],
                                    margin: [10, 0, 0, 0]
                                }
                            ],
                            margin: [0, 0, 0, 10]
                        });

                        // 3. Agregar Línea Horizontal
                        doc.content.splice(1, 0, {
                            canvas: [{ type: 'line', x1: 0, y1: 5, x2: 515, y2: 5, lineWidth: 1, lineColor: '#000000' }],
                            margin: [0, 0, 0, 20]
                        });

                        // 4. Agregar Título Centrado
                        doc.content.splice(2, 0, {
                            text: 'Pagos de Contratos Pendientes',
                            style: 'header',
                            alignment: 'center',
                            margin: [0, 0, 0, 15]
                        });

                        // 5. Estilo de tabla
                        doc.styles.header = { fontSize: 14, bold: true };
                        const table = doc.content.find(content => content.table);
                        if (table && table.table.body.length > 0) {
                            const headerRow = table.table.body[0];
                            headerRow.forEach(cell => {
                                cell.fillColor = '#2d4154';
                                cell.color = '#ffffff';
                                cell.bold = true;
                            });
                            
                            // Zebra striping
                            for (let i = 1; i < table.table.body.length; i++) {
                                if (i % 2 === 0) {
                                    table.table.body[i].forEach(cell => {
                                        cell.fillColor = '#f2f2f2';
                                    });
                                }
                            }
                            
                            table.table.widths = Array(table.table.body[0].length).fill('*');
                        }
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="ri-file-excel-line"></i> Excel',
                    className: 'btn btn-success btn-sm me-1',
                    exportOptions: { columns: [0, 1, 2, 3, 4] },
                    title: 'Contratos_Pendientes' 
                },
                {
                    extend: 'print',
                    text: '<i class="ri-printer-line"></i> Imprimir',
                    className: 'btn btn-info btn-sm',
                    exportOptions: { columns: [0, 1, 2, 3, 4] },
                    messageTop: `
                        <div style="text-align: center; margin-bottom: 20px;">
                            <h1 style="margin: 0; font-size: 1.5em; text-align: center;">Servicio Autonómo de Mercados de Bermúdez</h1>
                            <h2 style="margin: 0; font-size: 1.2em; text-align: center;">Pagos de Contratos Pendientes</h2>
                        </div>`,
                    customize: function (win) {
                        $(win.document.body).find('table').addClass('w-100').css('width', '100%');
                        $(win.document.body).find('head').append(
                            '<style>@media print { @page { size: letter; margin: 1cm; } } table thead th { background-color: #343a40 !important; color: white !important; -webkit-print-color-adjust: exact; text-align: left !important; }</style>'
                        );
                    }
                },
                'colvis' 
            ],
            language: commonLanguage,
            order: [[1, 'desc']], 
            columnDefs: [{ "orderable": false, "targets": 5 }]
        });

        // Initialize DataTables for Fines
        $('#finesBillingTable').DataTable({ 
            responsive: true,
            dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rtip',
            buttons: [
                {
                    extend: 'pdfHtml5',
                    text: '<i class="ri-file-pdf-line"></i> PDF',
                    className: 'btn btn-danger btn-sm me-1',
                    orientation: 'portrait',
                    pageSize: 'LETTER', 
                    exportOptions: { columns: [0, 1, 2] }, // Exclude Acciones (3 now)
                    customize: function (doc) {
                        // 1. Remover título por defecto
                        doc.content.splice(0, 1);

                        // 2. Agregar Encabezado Institucional (Logo + Texto)
                        doc.content.unshift({
                            columns: [
                                {
                                    image: commonPdfLogo,
                                    width: 50
                                },
                                {
                                    text: [
                                        { text: 'REPÚBLICA BOLIVARIANA DE VENEZUELA\\n', fontSize: 10, bold: true },
                                        { text: 'GOBIERNO BOLIVARIANA DE VENEZUELA\\n', fontSize: 10, bold: true },
                                        { text: 'SERVICIO AUTÓNOMO DE MERCADO MUNICIPAL DE BERMÚDEZ\\n', fontSize: 10, bold: true },
                                        { text: 'DIRECCIÓN DE ADMINISTRACIÓN "SERAMER"', fontSize: 10, bold: true }
                                    ],
                                    margin: [10, 0, 0, 0]
                                }
                            ],
                            margin: [0, 0, 0, 10]
                        });

                        // 3. Agregar Línea Horizontal
                        doc.content.splice(1, 0, {
                            canvas: [{ type: 'line', x1: 0, y1: 5, x2: 515, y2: 5, lineWidth: 1, lineColor: '#000000' }],
                            margin: [0, 0, 0, 20]
                        });

                        // 4. Agregar Título Centrado
                        doc.content.splice(2, 0, {
                            text: 'Multas Pendientes',
                            style: 'header',
                            alignment: 'center',
                            margin: [0, 0, 0, 15]
                        });

                        // 5. Estilo de tabla
                        doc.styles.header = { fontSize: 14, bold: true };
                        const table = doc.content.find(content => content.table);
                        if (table && table.table.body.length > 0) {
                            const headerRow = table.table.body[0];
                            headerRow.forEach(cell => {
                                cell.fillColor = '#2d4154';
                                cell.color = '#ffffff';
                                cell.bold = true;
                            });
                            
                            // Zebra striping
                            for (let i = 1; i < table.table.body.length; i++) {
                                if (i % 2 === 0) {
                                    table.table.body[i].forEach(cell => {
                                        cell.fillColor = '#f2f2f2';
                                    });
                                }
                            }
                            
                            table.table.widths = Array(table.table.body[0].length).fill('*');
                        }
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="ri-file-excel-line"></i> Excel',
                    className: 'btn btn-success btn-sm me-1',
                    exportOptions: { columns: [0, 1, 2] },
                    title: 'Multas_Pendientes' 
                },
                {
                    extend: 'print',
                    text: '<i class="ri-printer-line"></i> Imprimir',
                    className: 'btn btn-info btn-sm',
                    exportOptions: { columns: [0, 1, 2] },
                    messageTop: `
                        <div style="text-align: center; margin-bottom: 20px;">
                            <h1 style="margin: 0; font-size: 1.5em; text-align: center;">Servicio Autonómo de Mercados de Bermúdez</h1>
                            <h2 style="margin: 0; font-size: 1.2em; text-align: center;">Multas Pendientes</h2>
                        </div>`,
                    customize: function (win) {
                        $(win.document.body).find('table').addClass('w-100').css('width', '100%');
                        $(win.document.body).find('head').append(
                            '<style>@media print { @page { size: letter; margin: 1cm; } } table thead th { background-color: #343a40 !important; color: white !important; -webkit-print-color-adjust: exact; text-align: left !important; }</style>'
                        );
                    }
                },
                'colvis' 
            ],
            language: commonLanguage,
            order: [[0, 'desc']], 
            columnDefs: [{ "orderable": false, "targets": 3 }]
        });
    }

    // Payment Processing JS
    $('.btn-pay-action').on('click', function() {
        const type = $(this).data('type');
        const id = $(this).data('id');
        const amount = $(this).data('amount');
        const label = $(this).data('label');
        
        $('#proc-type').val(type);
        if (type === 'contract') {
            $('#proc-id').val(id);
            $('#proc-sanction-id').val('');
        } else {
            $('#proc-id').val('');
            $('#proc-sanction-id').val(id);
        }
        
        $('#proc-amount').val(amount);
        $('#proc-display-amount').text('Bs. ' + parseFloat(amount).toLocaleString('es-VE', {minimumFractionDigits: 2}));
        $('#proc-label').text(label);
        
        // Reset modal fields
        $('#proc-payment-method').val('');
        $('#proc-transaction-ref').val('');
        $('#transaction_ref_container').show();
        
        $('#paymentProcessModal').modal('show');
    });

    $('#btnConfirmCollection').on('click', function() {
        const btn = $(this);
        const form = $('#paymentForm');
        
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return;
        }

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Procesando...');
        
        $.ajax({
            url: '../ajax/process_payment.php',
            method: 'POST',
            data: form.serialize(),
            success: function(res) {
                const parsedRes = typeof res === 'string' ? JSON.parse(res) : res;
                if (parsedRes.success) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: parsedRes.message,
                        icon: 'success',
                        customClass: { container: 'swal2-high-zindex' }
                    }).then(() => { location.reload(); });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: parsedRes.message,
                        icon: 'error',
                        customClass: { container: 'swal2-high-zindex' }
                    });
                    btn.prop('disabled', false).html('<i class="ri-check-line me-1"></i> Registrar Cobro');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error de comunicación con el servidor', 'error');
                btn.prop('disabled', false).html('<i class="ri-check-line me-1"></i> Registrar Cobro');
            }
        });
    });
});
</script>
