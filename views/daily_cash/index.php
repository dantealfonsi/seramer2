<?php
require_once __DIR__ . '/../../controllers/DailyCashController.php';

$controller = new DailyCashController();
$data = $controller->index();
$reportData = $data['reportData'];
$filters = $data['filters'];
$summary = $data['summary'];
$page_title = $data['page_title'];

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <?php if (isset($_SESSION['flash_message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?> alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['flash_message']['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <?php unset($_SESSION['flash_message']); ?>
                    </div>
                <?php endif; ?>

                <!-- 1. Header & Title -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="p-3 rounded-3 me-4 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #e7e7ff !important;">
                                <i class="ri-history-line" style="color: #696cff; font-size: 2rem;"></i>
                            </div>
                            <div>
                                <h3 class="mb-1 fw-bold text-dark"><?php echo htmlspecialchars($page_title); ?></h3>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Inicio</a></li>
                                        <li class="breadcrumb-item"><a href="../billing/index.php">Facturación</a></li>
                                        <li class="breadcrumb-item active"><?php echo htmlspecialchars($page_title); ?></li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Filters (below title) -->
                <div class="filter-card mb-4 mt-0">
                    <div class="filter-card-title">
                        <i class="ri-filter-2-line"></i> Opciones de Filtrado de Actividad
                    </div>
                    <div class="filter-card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold small text-uppercase">Desde</label>
                                <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($filters['date_from']); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small text-uppercase">Hasta</label>
                                <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($filters['date_to']); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small text-uppercase">Estatus Caja</label>
                                <select name="status" class="form-select">
                                    <option value="">Todas las Cajas</option>
                                    <option value="active" <?php echo $filters['status'] === 'active' ? 'selected' : ''; ?>>Solo Activas</option>
                                    <option value="inactive" <?php echo $filters['status'] === 'inactive' ? 'selected' : ''; ?>>Solo Inactivas</option>
                                </select>
                            </div>
                            <div class="col-md-3 filter-card-actions mt-auto d-flex gap-2">
                                <a href="index.php" class="btn btn-filter-clear flex-grow-1">
                                    <i class="ri-refresh-line me-1"></i> Limpiar
                                </a>
                                <button type="submit" class="btn btn-filter-apply flex-grow-1">
                                    <i class="ri-search-line me-1"></i> Filtrar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- 3. Summary Cards (below filters) -->
                <div class="row mb-4 g-3">
                    <div class="col-md-4">
                        <div class="card card-status-primary h-100 shadow-sm border-0" style="border-left: 4px solid #696cff !important;">
                            <div class="card-body d-flex align-items-center p-4">
                                <div class="p-3 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background-color: #e7e7ff !important;">
                                    <i class="ri-skip-back-line" style="color: #696cff; font-size: 1.6rem;"></i>
                                </div>
                                <div>
                                    <p class="mb-1 text-muted fw-semibold small text-uppercase">Primeros Pagos (Total)</p>
                                    <h3 class="mb-0 fw-bold" style="color: #696cff;">Bs. <?php echo number_format($summary['total_initial'], 2, ',', '.'); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-status-success h-100 shadow-sm border-0" style="border-left: 4px solid #71dd37 !important;">
                            <div class="card-body d-flex align-items-center p-4">
                                <div class="p-3 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background-color: #e8fadf !important;">
                                    <i class="ri-money-dollar-circle-line" style="color: #71dd37; font-size: 1.6rem;"></i>
                                </div>
                                <div>
                                    <p class="mb-1 text-muted fw-semibold small text-uppercase">Total Recaudado</p>
                                    <h3 class="mb-0 fw-bold" style="color: #71dd37;">Bs. <?php echo number_format($summary['total_final'], 2, ',', '.'); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-status-warning h-100 shadow-sm border-0" style="border-left: 4px solid #ffab00 !important;">
                            <div class="card-body d-flex align-items-center p-4">
                                <div class="p-3 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background-color: #fff2d6 !important;">
                                    <i class="ri-funds-line" style="color: #ffab00; font-size: 1.6rem;"></i>
                                </div>
                                <div>
                                    <p class="mb-1 text-muted fw-semibold small text-uppercase">Pago más Alto</p>
                                    <h3 class="mb-0 fw-bold" style="color: #ffab00;">Bs. <?php echo number_format($summary['total_max'], 2, ',', '.'); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Report Table -->
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <div class="page-icon me-3">
                            <i class="ri-file-list-3-line"></i>
                        </div>
                        <h5 class="card-title mb-0">Historial de Actividad por Caja</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover w-100" id="dailyReportTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Nombre de Caja</th>
                                        <th>Cant. Inicial</th>
                                        <th>Cant. Máxima</th>
                                        <th>Total Recaudado</th>
                                        <th>Estatus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reportData as $row): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($row['open_date'])); ?></td>
                                            <td><span class="fw-bold"><?php echo htmlspecialchars($row['register_name']); ?></span></td>
                                            <td>Bs. <?php echo number_format($row['initial_amount'], 2, ',', '.'); ?></td>
                                            <td>Bs. <?php echo number_format($row['max_amount'], 2, ',', '.'); ?></td>
                                            <td class="fw-bold text-success">Bs. <?php echo number_format($row['total_collected'], 2, ',', '.'); ?></td>
                                            <td>
                                                <?php if ($row['total_collected'] > 0): ?>
                                                    <span class="badge rounded-pill bg-success">Con Actividad</span>
                                                <?php else: ?>
                                                    <span class="badge rounded-pill bg-light text-dark border">Sin Actividad</span>
                                                <?php endif; ?>
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
    $('#dailyReportTable').DataTable({
        responsive: true,
        dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="ri-file-excel-line"></i> Excel',
                className: 'btn btn-success btn-sm me-1',
                exportOptions: { columns: ':visible' }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="ri-file-pdf-line"></i> PDF',
                className: 'btn btn-danger btn-sm me-1',
                orientation: 'landscape',
                pageSize: 'LETTER',
                exportOptions: { columns: ':visible' },
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
                        text: 'Historial de Actividad por Caja',
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
                extend: 'print',
                text: '<i class="ri-printer-line"></i> Imprimir',
                className: 'btn btn-info btn-sm me-1',
                exportOptions: { columns: ':visible' },
                messageTop: `
                    <div style="text-align: center; margin-bottom: 20px;">
                        <h1 style="margin: 0; font-size: 1.5em;">Servicio Autónomo de Mercados de Bermúdez</h1>
                        <h2 style="margin: 0; font-size: 1.2em;">Historial de Actividad por Caja</h2>
                    </div>`,
                customize: function (win) {
                    $(win.document.body).find('table').addClass('w-100').css('width', '100%');
                    $(win.document.body).find('head').append(
                        '<style>@media print { @page { size: letter; margin: 1cm; } } table thead th { background-color: #343a40 !important; color: white !important; -webkit-print-color-adjust: exact; text-align: left !important; }</style>'
                    );
                }
            }
        ],
        order: [[0, 'desc']],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
        }
    });
});
</script>
