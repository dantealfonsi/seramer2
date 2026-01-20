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
    .card-title-premium {
        font-size: 2rem !important;
        font-weight: 600 !important;
    }
    .icon-premium {
        font-size: 2rem !important;
        background: #837aff;
        color: white;
        font-weight: 100 !important;
        padding: .24rem;
        border-radius: .7rem;
        margin-right: 1rem;
    }
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
                        <h5 class="mb-0 card-title-premium d-flex align-items-center">
                            <i class="ri-alarm-warning-line icon-premium"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <button class="btn btn-primary" onclick="window.location.href='receivable.php'">
                            <i class="ri-money-dollar-circle-line me-1"></i> Ir a Cobranza
                        </button>
                    </div>
                    
                    <div class="card-body">
                        <!-- Filters -->
                        <form method="GET" action="" id="filterForm" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Zona</label>
                                    <select name="zone_id" class="form-select">
                                        <option value="">Todas las Zonas</option>
                                        <?php foreach ($zones as $zone): ?>
                                            <option value="<?php echo $zone['id']; ?>" <?php echo $zoneFilter == $zone['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($zone['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Sector</label>
                                    <select name="sector_id" class="form-select">
                                        <option value="">Todos los Sectores</option>
                                        <?php foreach ($sectors as $sector): ?>
                                            <option value="<?php echo $sector['id']; ?>" <?php echo $sectorFilter == $sector['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($sector['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Antigüedad (Días)</label>
                                    <select name="min_days" class="form-select">
                                        <option value="1" <?php echo $minDaysOverdue == '1' ? 'selected' : ''; ?>>Cualquier retraso</option>
                                        <option value="30" <?php echo $minDaysOverdue == '30' ? 'selected' : ''; ?>>30+ días</option>
                                        <option value="60" <?php echo $minDaysOverdue == '60' ? 'selected' : ''; ?>>60+ días</option>
                                        <option value="90" <?php echo $minDaysOverdue == '90' ? 'selected' : ''; ?>>90+ días</option>
                                    </select>
                                </div>
                                <div class="col-12 d-flex justify-content-end gap-2">
                                    <button type="submit" class="btn btn-info btn-sm text-white" style="background-color: #0dcaf0; border-color: #0dcaf0;">
                                        <i class="ri-search-line me-1"></i> Filtrar Morosos
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="window.location.href='delinquency.php'">
                                        <i class="ri-refresh-line"></i> Limpiar
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Summary -->
                        <div class="row mb-4">
                            <?php
                            $totalDebt = 0;
                            foreach ($delinquentAccounts as $acc) $totalDebt += $acc['total_debt'];
                            ?>
                            <div class="col-md-6 mb-2">
                                <div class="p-3 bg-label-warning rounded d-flex justify-content-between align-items-center h-100">
                                    <span class="fw-semibold">Cuentas en Mora:</span>
                                    <span class="h4 mb-0"><?php echo count($delinquentAccounts); ?></span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="p-3 bg-label-danger rounded d-flex justify-content-between align-items-center h-100">
                                    <span class="fw-semibold">Deuda Total Acumulada:</span>
                                    <span class="h4 mb-0 text-danger">Bs. <?php echo number_format($totalDebt, 2); ?></span>
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
                                                <a href="receivable.php?search_term=<?php echo urlencode($account['id_number']); ?>&search_type=id_number" class="btn btn-sm btn-success">
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
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'pdfHtml5',
                    text: '<i class="ri-file-pdf-line"></i> PDF',
                    className: 'btn btn-danger btn-sm me-1',
                    orientation: 'landscape',
                    pageSize: 'LETTER', 
                    exportOptions: { columns: exportColumns },
                    customize: function (doc) {
                        doc.content.splice(0, 0, {
                            text: 'Servicio Autonómo de Mercados de Bermúdez', 
                            alignment: 'center', style: 'header1'
                        }, {
                            text: 'Control de Morosidad', 
                            alignment: 'center', style: 'header2'
                        }, { text: '', margin: [0, 0, 0, 10] });

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
                            '<style>table thead th { background-color: #343a40 !important; color: white !important; -webkit-print-color-adjust: exact; text-align: left !important; }</style>'
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
