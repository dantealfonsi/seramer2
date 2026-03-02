<?php
require_once __DIR__ . '/../../models/BillingReportModel.php';
require_once __DIR__ . '/../../models/SectorModel.php';
require_once __DIR__ . '/../../models/ZoneModel.php';

$billingReportModel = new BillingReportModel();
$sectorModel = new SectorModel();
$zoneModel = new ZoneModel();

// Get filter parameters
$sectorFilter = $_GET['sector_id'] ?? '';
$zoneFilter = $_GET['zone_id'] ?? '';
$minDaysOverdue = $_GET['min_days'] ?? '1';

// Build filters
$filters = [];
if (!empty($sectorFilter)) $filters['sector_id'] = $sectorFilter;
if (!empty($zoneFilter)) $filters['zone_id'] = $zoneFilter;
if (!empty($minDaysOverdue)) $filters['min_days_overdue'] = $minDaysOverdue;

// Get delinquent accounts
$delinquentAccounts = $billingReportModel->getDelinquentAccounts($filters);

$sectors = $sectorModel->getAll();
$zones = $zoneModel->getAll();

$page_title = 'Control de Morosidad';

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<style>
    .overdue-low { background-color: rgba(255, 249, 196, 0.4); }
    .overdue-medium { background-color: rgba(255, 224, 178, 0.4); }
    .overdue-high { background-color: rgba(255, 205, 210, 0.4); }
</style>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 card-title d-flex align-items-center" style="font-size: 1.4rem;font-weight: 600;">
                            <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; background-color: #e7e7ff !important;">
                                <i class="ri-alarm-warning-line" style="color: #696cff; font-size: 2rem;"></i>
                            </div>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <button class="btn btn-primary" onclick="window.location.href='receivable.php'">
                            <i class="ri-money-dollar-circle-line me-1"></i> Ir a Cobranza
                        </button>
                    </div>
                    
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="filter-card">
                            <div class="filter-card-title">
                                <i class="ri-filter-2-line"></i> Opciones de Filtrado Avanzado
                            </div>
                            <div class="filter-card-body">
                                <form method="GET" action="" id="filterForm">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label small">Zona</label>
                                            <select name="zone_id" class="form-select">
                                                <option value="">Todas las Zonas</option>
                                                <?php foreach ($zones as $zone): ?>
                                                    <option value="<?php echo $zone['id']; ?>" <?php echo $zoneFilter == $zone['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($zone['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Sector</label>
                                            <select name="sector_id" class="form-select">
                                                <option value="">Todos los Sectores</option>
                                                <?php foreach ($sectors as $sector): ?>
                                                    <option value="<?php echo $sector['id']; ?>" <?php echo $sectorFilter == $sector['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($sector['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Antigüedad (Días)</label>
                                            <select name="min_days" class="form-select">
                                                <option value="1" <?php echo $minDaysOverdue == '1' ? 'selected' : ''; ?>>Cualquier retraso</option>
                                                <option value="30" <?php echo $minDaysOverdue == '30' ? 'selected' : ''; ?>>30+ días</option>
                                                <option value="60" <?php echo $minDaysOverdue == '60' ? 'selected' : ''; ?>>60+ días</option>
                                                <option value="90" <?php echo $minDaysOverdue == '90' ? 'selected' : ''; ?>>90+ días</option>
                                            </select>
                                        </div>
                                        <div class="col-12 filter-card-actions">
                                            <a href="delinquency.php" class="btn btn-filter-clear"><i class="ri-refresh-line me-1"></i> Limpiar</a>
                                            <button type="submit" class="btn btn-filter-apply"><i class="ri-search-line me-1"></i> Filtrar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="row mb-4 g-3">
                            <?php
                            $totalDebt = 0;
                            foreach ($delinquentAccounts as $acc) $totalDebt += $acc['total_debt'];
                            ?>
                            <div class="col-md-6">
                                <div class="card card-status-warning h-100" style="background-color: var(--metro-warning-light);">
                                    <div class="card-body p-3 d-flex align-items-center">
                                        <div class="page-icon me-3" style="width:52px;height:52px;font-size:1.6rem; color: var(--metro-warning) !important; background-color: transparent !important;">
                                            <i class="ri-alarm-warning-line"></i>
                                        </div>
                                        <div>
                                            <h3 class="mb-0 fw-bold" style="color: var(--metro-warning);"><?php echo count($delinquentAccounts); ?></h3>
                                            <p class="mb-0 text-muted fw-semibold" style="font-size:0.85rem;">CUENTAS EN MORA</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-status-danger h-100" style="background-color: var(--metro-danger-light);">
                                    <div class="card-body p-3 d-flex align-items-center">
                                        <div class="page-icon me-3" style="width:52px;height:52px;font-size:1.6rem; color: var(--metro-danger) !important; background-color: transparent !important;">
                                            <i class="ri-money-dollar-circle-line"></i>
                                        </div>
                                        <div>
                                            <h3 class="mb-0 fw-bold" style="color: var(--metro-danger);">Bs. <?php echo number_format($totalDebt, 2); ?></h3>
                                            <p class="mb-0 text-muted fw-semibold" style="font-size:0.85rem;">DEUDA TOTAL ACUMULADA</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover w-100" id="delinquencyTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Contribuyente</th>
                                        <th>Cédula</th>
                                        <th>Puesto</th>
                                        <th>Deuda Contratos</th>
                                        <th>Deuda Multas</th>
                                        <th>Total</th>
                                        <th>Mora</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($delinquentAccounts as $account): ?>
                                        <?php
                                        $d = (int)$account['days_overdue'];
                                        $cls = $d > 90 ? 'overdue-high' : ($d > 60 ? 'overdue-medium' : ($d > 30 ? 'overdue-low' : ''));
                                        ?>
                                        <tr class="<?php echo $cls; ?>">
                                            <td><strong><?php echo htmlspecialchars($account['first_name'] . ' ' . $account['last_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($account['id_number']); ?></td>
                                            <td><?php echo htmlspecialchars($account['stall_number']); ?> (<?php echo htmlspecialchars($account['sector_name']); ?>)</td>
                                            <td>Bs. <?php echo number_format($account['contracts_debt'], 2); ?></td>
                                            <td>Bs. <?php echo number_format($account['sanctions_debt'], 2); ?></td>
                                            <td><strong>Bs. <?php echo number_format($account['total_debt'], 2); ?></strong></td>
                                            <td>
                                                <span class="badge <?php echo $d > 90 ? 'bg-danger' : ($d > 60 ? 'bg-warning' : 'bg-info'); ?>">
                                                    <?php echo $d; ?> días
                                                </span>
                                            </td>
                                            <td>
                                                <?php $cleanId = preg_replace('/[^0-9]/', '', $account['id_number']); ?>
                                                <a href="receivable.php?search_term=<?php echo urlencode($cleanId); ?>&search_type=id_number" class="btn btn-sm btn-success">
                                                    <i class="ri-money-dollar-circle-line"></i> Cobrar
                                                </a>
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
    // Contenido del encabezado personalizado para la vista de Impresión
    const customHeader = `
        <div style="text-align: center; margin-bottom: 20px;">
            <h1 style="margin: 0; font-size: 1.5em; text-align: center;">Servicio Autonómo de Mercados de Bermúdez</h1>
            <h2 style="margin: 0; font-size: 1.2em; text-align: center;">Control de Morosidad</h2>
        </div>
    `;
    
    // Columnas a exportar (todas menos Acciones)
    const exportColumns = [0, 1, 2, 3, 4, 5, 6]; 
    
    if ($.fn.DataTable) {
        $('#delinquencyTable').DataTable({ 
            responsive: true,
            dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rtip',
            buttons: [
                {
                    extend: 'pdfHtml5',
                    text: '<i class="ri-file-pdf-line"></i> PDF',
                    className: 'btn btn-danger btn-sm me-1',
                    orientation: 'landscape',
                    pageSize: 'LETTER', 
                    exportOptions: { columns: exportColumns },
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
                            text: 'Control de Morosidad',
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
                    exportOptions: { columns: exportColumns },
                    title: 'Control_Morosidad_Seramer' 
                },
                {
                    extend: 'print',
                    text: '<i class="ri-printer-line"></i> Imprimir',
                    className: 'btn btn-info btn-sm',
                    exportOptions: { columns: exportColumns },
                    messageTop: customHeader,
                    customize: function (win) {
                        $(win.document.body).find('table').addClass('w-100').css('width', '100%');
                        $(win.document.body).find('head').append(
                            '<style>@media print { @page { size: letter; margin: 1cm; } } table thead th { background-color: #343a40 !important; color: white !important; -webkit-print-color-adjust: exact; text-align: left !important; }</style>'
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
                "paginate": { "first": "Primero", "last": "Último", "next": "Siguiente", "previous": "Anterior" },
                "aria": { "sortAscending": ": activar para ordenar la columna ascendente", "sortDescending": ": activar para ordenar la columna descendente" } 
            },
            order: [[6, 'desc']], // Order by Days Overdue desc
            columnDefs: [{ "orderable": false, "targets": 7 }]
        });
    }
});
</script>
