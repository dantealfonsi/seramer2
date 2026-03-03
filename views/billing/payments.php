<?php
require_once __DIR__ . '/../../controllers/BillingController.php';

$controller = new BillingController();

// Get filters
$filters = [
    'date_from' => $_GET['date_from'] ?? date('Y-m-01'),
    'date_to' => $_GET['date_to'] ?? date('Y-m-d')
];

// Get data
$payments = $controller->getPaymentHistory(500, $filters);
$kpis = $controller->getDashboardKPIs($filters);
$page_title = 'Pagos Recibidos';

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<style>
    .kpi-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        transition: transform 0.2s;
        height: 100%;
        color: white;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
    }
    .kpi-icon {
        font-size: 2.5rem;
        opacity: 0.8;
    }
    .kpi-value {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 0;
    }
    .kpi-label {
        font-size: 0.9rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.9;
    }
    .kpi-subtext {
        font-size: 0.8rem;
        margin-top: 5px;
        opacity: 0.8;
    }
</style>

<div class="main-content">
    <div class="container-fluid">
        <!-- KPI Cards -->
        <div class="row mb-4 g-3">
            <div class="col-md-6">
                <div class="card card-status-danger h-100" style="background-color: var(--metro-danger-light);">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="page-icon me-3" style="width:52px;height:52px;font-size:1.6rem; color: var(--metro-danger) !important; background-color: transparent !important;">
                            <i class="ri-user-unfollow-line"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold" style="color: var(--metro-danger);"><?php echo number_format($kpis['debtors_count']); ?></h3>
                            <p class="mb-0 text-muted fw-semibold" style="font-size:0.85rem;">ADJUDICATARIOS CON DEUDA</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-status-success h-100" style="background-color: var(--metro-success-light);">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="page-icon me-3" style="width:52px;height:52px;font-size:1.6rem; color: var(--metro-success) !important; background-color: transparent !important;">
                            <i class="ri-secure-payment-line"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold" style="color: var(--metro-success);"><?php echo number_format($kpis['payments_count']); ?></h3>
                            <p class="mb-0 text-muted fw-semibold" style="font-size:0.85rem;">PAGOS RECIBIDOS (<?php echo $kpis['solvency_rate']; ?>% SOLVENCIA)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-0 py-3">
                        <h5 class="mb-0 card-title d-flex align-items-center" style="font-size: 1.4rem;font-weight: 600;">
                            <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; background-color: #e7e7ff !important;">
                                <i class="ri-file-list-3-line" style="color: #696cff; font-size: 2rem;"></i>
                            </div>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                    </div>
                    
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="filter-card">
                            <div class="filter-card-title">
                                <i class="ri-filter-2-line"></i> Opciones de Filtrado Avanzado
                            </div>
                            <div class="filter-card-body">
                                <form method="GET" action="" class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small text-uppercase">Desde</label>
                                        <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($filters['date_from']); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small text-uppercase">Hasta</label>
                                        <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($filters['date_to']); ?>">
                                    </div>
                                    <div class="col-12 filter-card-actions">
                                        <a href="payments.php" class="btn btn-filter-clear"><i class="ri-refresh-line me-1"></i> Limpiar</a>
                                        <button type="submit" class="btn btn-filter-apply"><i class="ri-search-line me-1"></i> Filtrar</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Data Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover w-100" id="paymentsTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Adjudicatario</th>
                                        <th>Cédula/RIF</th>
                                        <th>Referencia</th>
                                        <th>Método</th>
                                        <th>Tipo</th>
                                        <th>Concepto</th>
                                        <th>Monto (Bs)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($payment['date'] ?? $payment['payment_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($payment['id_number']); ?></td>
                                            <td><?php echo htmlspecialchars($payment['payment_reference'] ?? $payment['transaction_reference'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($payment['payment_method_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php 
                                                    $type = $payment['source_type'] ?? ($payment['payment_type'] ?? 'General');
                                                    $badgeClass = (strtolower($type) === 'contract' || strtolower($type) === 'mensualidad') ? 'bg-info' : 'bg-warning';
                                                    if (strtolower($type) === 'contract') $type = 'Contrato';
                                                ?>
                                                <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($type); ?></span>
                                            </td>
                                            <td><small><?php echo htmlspecialchars($payment['concept'] ?? 'Pago'); ?></small></td>
                                            <td class="fw-bold">Bs. <?php echo number_format($payment['amount'] ?? $payment['amount_paid'] ?? 0, 2); ?></td>
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

<!-- DataTables Scripts -->
<script type="text/javascript" src="../../public/assets/js/pdf_logo.js"></script>
<script type="text/javascript" src="../../public/datatables/datatables.min.js"></script>
<script type="text/javascript" src="../../public/datatables/pdfmake.min.js"></script>
<script type="text/javascript" src="../../public/datatables/vfs_fonts.js"></script>
<link rel="stylesheet" type="text/css" href="../../public/datatables/datatables.min.css"/> 
<link rel="stylesheet" type="text/css" href="../../public/datatables/buttons.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="../../public/assets/css/dani-styles.css"/>

<script>
$(document).ready(function() {
    // PDF Header Customization
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
            text: 'Reporte de Pagos Recibidos',
            alignment: 'center',
            style: 'header3',
            margin: [0, 0, 0, 10]
        });

        doc.styles.header1 = { fontSize: 14, bold: true, margin: [0, 10, 0, 0] };
        doc.styles.header2 = { fontSize: 12, bold: true, margin: [0, 0, 0, 5] };
        doc.styles.header3 = { fontSize: 10, italics: true };

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

    // Common Language Settings
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

    if ($.fn.DataTable) {
        $('#paymentsTable').DataTable({ 
            responsive: true,
            dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rtip',
            buttons: [
                {
                    extend: 'pdfHtml5',
                    text: '<i class="ri-file-pdf-line"></i> PDF',
                    className: 'btn btn-danger btn-sm me-1',
                    orientation: 'landscape',
                    pageSize: 'LETTER', 
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5, 7] }, // Exclude Concepto if too long
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
                            canvas: [{ type: 'line', x1: 0, y1: 5, x2: 750, y2: 5, lineWidth: 1, lineColor: '#000000' }],
                            margin: [0, 0, 0, 20]
                        });

                        // 4. Agregar Título Centrado
                        doc.content.splice(2, 0, {
                            text: 'Historial de Pagos Recibidos',
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
                    exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7] },
                    title: 'Pagos_Recibidos' 
                },
                {
                    extend: 'print',
                    text: '<i class="ri-printer-line"></i> Imprimir',
                    className: 'btn btn-info btn-sm',
                    exportOptions: { columns: ':visible' },
                    messageTop: `
                        <div style="text-align: center; margin-bottom: 20px;">
                            <h1 style="margin: 0; font-size: 1.5em; text-align: center;">Servicio Autonómo de Mercados de Bermúdez</h1>
                            <h2 style="margin: 0; font-size: 1.2em; text-align: center;">Historial de Pagos Recibidos</h2>
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
        });
    }
});
</script>
