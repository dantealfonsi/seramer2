<?php
require_once __DIR__ . '/../../models/SanctionsModel.php';
require_once __DIR__ . '/../../models/FinePaymentModel.php';

$sanctionsModel = new SanctionsModel();
$finePaymentModel = new FinePaymentModel();

// Get filter parameters
$statusFilter = $_GET['status'] ?? 'pending';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$searchTerm = $_GET['search_term'] ?? '';

$filters = [];
if ($statusFilter !== 'all') {
    $filters['sanction_status'] = $statusFilter === 'pending' ? 'Imposed' : 'Paid';
}
if (!empty($dateFrom)) $filters['date_from'] = $dateFrom;
if (!empty($dateTo)) $filters['date_to'] = $dateTo;

$result = $sanctionsModel->index($filters);
$sanctions = $result['sanctions'] ?? [];

// Final Filtering for Search Term (ID, Name, Stall)
if (!empty($searchTerm)) {
    $sanctions = array_filter($sanctions, function($s) use ($searchTerm, $sanctionsModel) {
        $infractionQuery = "SELECT i.*, a.first_name, a.last_name, a.id_number, ms.stall_number
                           FROM infractions i
                           JOIN awardees a ON i.awardee_id = a.id
                           LEFT JOIN market_stalls ms ON i.stall_id = ms.id
                           WHERE i.infraction_id = :infraction_id";
        $stmt = $sanctionsModel->query($infractionQuery, ['infraction_id' => $s['infraction_id']]);
        $data = $stmt[0] ?? null;
        
        if (!$data) return false;
        
        $fullName = strtolower($data['first_name'] . ' ' . $data['last_name']);
        $search = strtolower($searchTerm);
        
        return strpos($data['id_number'], $searchTerm) !== false || 
               strpos($fullName, $search) !== false || 
               (isset($data['stall_number']) && strpos($data['stall_number'], $searchTerm) !== false);
    });
}

$page_title = 'Gestión de Multas';

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
</style>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 card-title-premium d-flex align-items-center">
                            <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; background-color: #e7e7ff !important;">
                                <i class="ri-alert-line" style="color: #696cff; font-size: 2rem;"></i>
                            </div>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <div>
                            <button class="btn btn-primary" onclick="window.location.href='../billing/receivable.php'">
                                <i class="ri-money-dollar-circle-line me-1"></i>
                                Procesar Pago
                            </button>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <!-- Filters -->
                        <form method="GET" action="" id="filterForm" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Búsqueda Rápida</label>
                                    <input type="text" name="search_term" class="form-control" placeholder="Cédula, Nombre o Puesto..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Estado</label>
                                    <select name="status" class="form-select">
                                        <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>Todos</option>
                                        <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pendientes</option>
                                        <option value="paid" <?php echo $statusFilter === 'paid' ? 'selected' : ''; ?>>Pagadas</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Desde</label>
                                    <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($dateFrom); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Hasta</label>
                                    <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($dateTo); ?>">
                                </div>
                                <div class="col-12 d-flex justify-content-end gap-2">
                                    <button type="submit" class="btn btn-info btn-sm text-white" style="background-color: #0dcaf0; border-color: #0dcaf0;">
                                        <i class="ri-filter-3-line me-1"></i> Filtrar Multas
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="window.location.href='fines.php'">
                                        <i class="ri-refresh-line"></i> Limpiar
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Sanctions Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover w-100" id="finesTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Adjudicatario</th>
                                        <th>Cédula</th>
                                        <th>Tipo de Infracción</th>
                                        <th>Fecha</th>
                                        <th>Severidad</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sanctions as $sanction): ?>
                                        <?php
                                        $infractionQuery = "SELECT i.*, a.first_name, a.last_name, a.id_number
                                                           FROM infractions i
                                                           JOIN awardees a ON i.awardee_id = a.id
                                                           WHERE i.infraction_id = :infraction_id";
                                        $stmt = $sanctionsModel->query($infractionQuery, ['infraction_id' => $sanction['infraction_id']]);
                                        $inf = $stmt[0] ?? null;
                                        ?>
                                        <tr>
                                            <td><?php echo $inf ? htmlspecialchars($inf['first_name'] . ' ' . $inf['last_name']) : 'N/A'; ?></td>
                                            <td><?php echo $inf ? htmlspecialchars($inf['id_number']) : 'N/A'; ?></td>
                                            <td><?php echo htmlspecialchars($sanction['infraction_description'] ?? 'N/A'); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($sanction['imposition_date'])); ?></td>
                                            <td>
                                                <?php 
                                                $severityRaw = strtolower($sanction['severity_name'] ?? '');
                                                $severity = ucfirst($severityRaw);
                                                $badgeClass = 'bg-label-info'; 
                                                if ($severityRaw === 'moderada') $badgeClass = 'bg-label-warning';
                                                if ($severityRaw === 'grave') $badgeClass = 'bg-label-danger';
                                                ?>
                                                <span class="badge <?php echo $badgeClass; ?>">
                                                    <?php echo htmlspecialchars($severity); ?>
                                                </span>
                                            </td>
                                            <td><strong>Bs. <?php echo number_format($sanction['fine_amount'], 2); ?></strong></td>
                                            <td>
                                                <?php
                                                $status = $sanction['sanction_status'];
                                                $statusText = $status === 'Imposed' ? 'Impuesta' : 
                                                             ($status === 'Paid' ? 'Pagada' : 
                                                             ($status === 'Pending' ? 'Pendiente' : 
                                                             ($status === 'Canceled' ? 'Cancelada' : $status)));
                                                $badgeClass = $status === 'Paid' ? 'bg-success' : 
                                                             ($status === 'Imposed' ? 'bg-warning' : 
                                                             ($status === 'Canceled' ? 'bg-danger' : 'bg-primary'));
                                                ?>
                                                <span class="badge <?php echo $badgeClass; ?>">
                                                    <?php echo htmlspecialchars($statusText); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                        <i class="ri-more-2-fill"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <?php if ($sanction['sanction_status'] !== 'Paid'): ?>
                                                            <?php 
                                                            $cleanId = preg_replace('/[^0-9]/', '', $sanction['id_number'] ?? ''); 
                                                            ?>
                                                            <a class="dropdown-item" href="receivable.php?search_term=<?php echo urlencode($cleanId); ?>&search_type=id_number">
                                                                <i class="ri-money-dollar-circle-line me-2 text-success"></i> Cobrar
                                                            </a>
                                                        <?php endif; ?>
                                                        <a class="dropdown-item" href="fine_details.php?id=<?php echo $sanction['sanction_id']; ?>">
                                                            <i class="ri-eye-line me-2 text-primary"></i> Ver Detalle
                                                        </a>
                                                    </div>
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
            <h2 style="margin: 0; font-size: 1.2em; text-align: center;">Listado de Multas</h2>
        </div>
    `;
    
    // Columnas a exportar (Adjudicatario, Cédula, Tipo, Fecha, Severidad, Monto, Estado)
    const exportColumns = [0, 1, 2, 3, 4, 5, 6]; 
    
    if ($.fn.DataTable) {
        $('#finesTable').DataTable({ 
            responsive: true,
            dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rtip',
            buttons: [
                {
                    extend: 'pdfHtml5',
                    text: '<i class="ri-file-pdf-line"></i> PDF',
                    className: 'btn btn-danger btn-sm me-1',
                    orientation: 'landscape',
                    pageSize: 'LETTER', 
                    exportOptions: {
                        columns: exportColumns 
                    },
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
                            text: 'Listado de Multas',
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
                    exportOptions: {
                        columns: exportColumns 
                    },
                    title: 'Listado_Multas_Seramer' 
                },
                {
                    extend: 'print',
                    text: '<i class="ri-printer-line"></i> Imprimir',
                    className: 'btn btn-info btn-sm',
                    exportOptions: {
                        columns: exportColumns 
                    },
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
            order: [[3, 'desc']], // Order by Date desc (index 3 now)
            "columnDefs": [
                { "orderable": false, "targets": 7 } 
            ]
        });
    }
});
</script>
