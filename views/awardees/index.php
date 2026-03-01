<?php
// Vista de listado de adjudicatarios
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir controladores necesarios
require_once __DIR__ . '/../../controllers/AwardeeController.php';
require_once __DIR__ . '/../../controllers/RolesController.php';
require_once __DIR__ . '/../../models/StatisticalReportModel.php';

$awardeeController = new AwardeeController();
$rol = new RolesController();
$statsModel = new StatisticalReportModel();

// Obtener estadísticas para el dashboard superior
$dashboardStats = $statsModel->getDashboardStats();
$totalAwardees = $dashboardStats['awardees'] ?? 0;

// Configurar filtros
$filters = [
    'search' => $_GET['search'] ?? null,
    'id_number' => $_GET['id_number'] ?? null,
    'name' => $_GET['name'] ?? null,
    'phone' => $_GET['phone'] ?? null,
    'email' => $_GET['email'] ?? null,
    'address' => $_GET['address'] ?? null,
];

// Limpiar el arreglo eliminando valores nulos o vacíos
$activeFilters = array_filter($filters);

$params = [
    'filters' => $activeFilters,
    'search' => $_GET['search'] ?? '' 
];

// Obtener datos del controlador
$result = $awardeeController->index($params);
$awardees = $result['awardees'];
$page_title = $result['page_title'];
$search = $result['search'];
$has_search = !empty($activeFilters) || !empty($search);

// Incluir header y layouts
require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<style>
    /* Estilos específicos de adjudicatarios — el sistema Metro UI global lo maneja */
    .card-inside {
        box-shadow: none !important;
        border: 1px solid var(--metro-border) !important;
        background-color: var(--metro-surface-alt) !important;
        margin-bottom: 1.5rem;
    }
</style>


<div class="main-content" style="padding: 1.5rem;">
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <!-- Contenedor Blanco Principal -->
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        
                        <!-- 1. Encabezado (Título y Botón) -->
                        <div class="card-header d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title d-flex align-items-center" style="font-size: 1.4rem;font-weight: 600;">
                                <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="ri-group-line" style="color: #696cff; font-size: 1.5rem;"></i></div>
                                Gestión de Adjudicatarios
                            </h5>
                            <a href="create.php" class="btn btn-primary">
                                <i class="ri-add-line"></i> Registrar Adjudicatario
                            </a>
                        </div>

                        <!-- 2. Filtros Avanzados -->
                        <div class="card card-inside">
                            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0">
                                <h6 class="card-title mb-0" style="font-weight: 600; color: #43495b;">
                                    <i class="ri-filter-2-line me-1 text-muted"></i> Opciones de Filtrado Avanzado
                                </h6>
                            </div>
                            <div class="card-body">
                                <form action="index.php" method="GET">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label">Búsqueda General</label>
                                            <input type="text" class="form-control" name="search" placeholder="BUSCAR..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Cédula</label>
                                            <input type="text" class="form-control" name="id_number" placeholder="CéDULA" value="<?php echo htmlspecialchars($_GET['id_number'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Nombre</label>
                                            <input type="text" class="form-control" name="name" placeholder="NOMBRE/APELLIDO" value="<?php echo htmlspecialchars($_GET['name'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Teléfono</label>
                                            <input type="text" class="form-control" name="phone" placeholder="TELÉFONO" value="<?php echo htmlspecialchars($_GET['phone'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Email</label>
                                            <input type="text" class="form-control" name="email" placeholder="CORREO ELECTRÓNICO" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Dirección</label>
                                            <input type="text" class="form-control" name="address" placeholder="DIRECCIÓN" value="<?php echo htmlspecialchars($_GET['address'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <div class="d-flex gap-2 w-100">
                                                <a href="index.php" class="btn btn-filter-clear w-50" title="Limpiar Filtros">
                                                    <i class="ri-refresh-line"></i>
                                                </a>
                                                <button type="submit" class="btn btn-filter-apply w-50" title="Aplicar Filtros">
                                                    <i class="ri-search-line"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- 3. Tarjeta de Métrica (Total) -->
                        <div class="card card-status-success mb-4" style="background-color: var(--metro-primary-light);">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="page-icon me-3" style="width:52px;height:52px;font-size:1.6rem;">
                                    <i class="ri-group-line"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0 fw-bold" style="color: var(--metro-primary);"><?php echo number_format($totalAwardees); ?></h3>
                                    <p class="mb-0 text-muted fw-semibold" style="font-size:0.8rem;">ADJUDICATARIOS REGISTRADOS</p>
                                </div>
                            </div>
                        </div>

                        <!-- Mensajes Flash -->
                        <?php if (isset($_SESSION['flash_message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['flash_message']['type'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show mb-4" role="alert">
                            <?php echo $_SESSION['flash_message']['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['flash_message']); ?>
                        <?php endif; ?>

                        <!-- 4. Tabla de Datos -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle w-100" id="awardeesTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Cédula</th>
                                        <th>Nombre Completo</th>
                                        <th>Contacto</th>
                                        <th>Dirección</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($awardees as $awardee): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-label-secondary px-3 py-2" style="background-color: #f2f2f7; color: #43495b; font-size: 0.9rem; font-weight: 600;">
                                                    <?php echo htmlspecialchars($awardee['id_number']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm bg-label-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background-color: #e7e7ff !important; color: #696cff !important;">
                                                        <span class="fw-bold"><?php echo strtoupper(substr($awardee['first_name'], 0, 1)); ?></span>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark" style="font-size: 0.95rem;"><?php echo htmlspecialchars($awardee['first_name'] . ' ' . $awardee['last_name']); ?></div>
                                                        <small class="text-muted">Adjudicatario</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column small">
                                                    <span class="mb-1"><i class="ri-phone-line me-1" style="color: #696cff;"></i> <?php echo htmlspecialchars($awardee['phone'] ?? 'N/A'); ?></span>
                                                    <span class="text-muted"><i class="ri-mail-line me-1"></i> <?php echo htmlspecialchars($awardee['email'] ?? 'N/A'); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted small" title="<?php echo htmlspecialchars($awardee['address'] ?? ''); ?>">
                                                    <?php echo htmlspecialchars(strlen($awardee['address'] ?? '') > 40 ? substr($awardee['address'], 0, 40) . '...' : ($awardee['address'] ?? 'N/A')); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a href="show_contracts.php?id=<?php echo $awardee['id']; ?>" class="btn btn-sm btn-outline-info" style="padding: 0.4rem; border-radius: 0.5rem;" title="Ver Expediente">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                    <a href="edit.php?id=<?php echo $awardee['id']; ?>" class="btn btn-sm btn-outline-warning" style="padding: 0.4rem; border-radius: 0.5rem;" title="Editar">
                                                        <i class="ri-pencil-line"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete" 
                                                            style="padding: 0.4rem; border-radius: 0.5rem;" 
                                                            title="Eliminar" 
                                                            data-id="<?php echo $awardee['id']; ?>" 
                                                            data-name="<?php echo htmlspecialchars($awardee['first_name'] . ' ' . $awardee['last_name']); ?>">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                    </div> <!-- End Main Card Body -->
                </div> <!-- End Main Card -->
            </div>
        </div>
    </div>
</div>

<!-- Formulario oculto para eliminación -->
<form id="deleteForm" method="POST" action="delete.php" style="display: none;">
    <input type="hidden" name="id" id="deleteAwardeeId">
</form>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<!-- DataTables Dependencies (CDN for full Buttons support) -->
<script type="text/javascript" src="../../public/assets/js/pdf_logo.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="../../public/assets/css/dani-styles.css"/>

<script>
    $(document).ready(function() {
        // Manejador para el botón eliminar con SweetAlert2
        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');

            Swal.fire({
                title: '¿Estás seguro?',
                text: `Vas a eliminar al adjudicatario: ${name}. Esta acción no se puede deshacer si tiene registros vinculados.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff3e1d',
                cancelButtonColor: '#8592a3',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-danger me-3',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#deleteAwardeeId').val(id);
                    $('#deleteForm').submit();
                }
            });
        });

        if ($.fn.DataTable) {
            const table = $('#awardeesTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="ri-file-pdf-line me-1"></i> PDF',
                        className: 'btn btn-danger btn-sm me-1',
                        pageSize: 'LETTER',
                        exportOptions: { columns: [0, 1, 2, 3] },
                        customize: function (doc) {
                            doc.content.splice(0, 1);
                            doc.content.unshift({
                                columns: [
                                    { image: commonPdfLogo, width: 50 },
                                    {
                                        text: [
                                            { text: 'REPÚBLICA BOLIVARIANA DE VENEZUELA\n', fontSize: 10, bold: true },
                                            { text: 'GOBIERNO BOLIVARIANA DE VENEZUELA\n', fontSize: 10, bold: true },
                                            { text: 'SERVICIO AUTÓNOMO DE MERCADO MUNICIPAL DE BERMÚDEZ\n', fontSize: 10, bold: true },
                                            { text: 'DIRECCIÓN DE ADMINISTRACIÓN "SERAMER"', fontSize: 10, bold: true }
                                        ],
                                        margin: [10, 0, 0, 0]
                                    }
                                ],
                                margin: [0, 0, 0, 10]
                            });

                            doc.content.splice(1, 0, {
                                canvas: [{ type: 'line', x1: 0, y1: 5, x2: 515, y2: 5, lineWidth: 1, lineColor: '#000000' }],
                                margin: [0, 0, 0, 20]
                            });

                            doc.content.splice(2, 0, {
                                text: 'Listado de Adjudicatarios',
                                style: 'header',
                                alignment: 'center',
                                margin: [0, 0, 0, 15]
                            });

                            const table = doc.content.find(content => content.table);
                            if (table) {
                                table.table.body[0].forEach(function(cell) {
                                    cell.fillColor = '#2d4154';
                                    cell.color = 'white';
                                    cell.bold = true;
                                    cell.alignment = 'center';
                                });

                                for (let i = 1; i < table.table.body.length; i++) {
                                    if (i % 2 === 0) {
                                        table.table.body[i].forEach(function(cell) {
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
                        text: '<i class="ri-file-excel-line me-1"></i> Excel',
                        className: 'btn btn-success btn-sm me-1',
                        exportOptions: { columns: [0, 1, 2, 3] },
                        title: 'Listado de Adjudicatarios'
                    },
                    {
                        extend: 'print',
                        text: '<i class="ri-printer-line me-1"></i> Imprimir',
                        className: 'btn btn-info btn-sm me-1',
                        exportOptions: { columns: [0, 1, 2, 3] }
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="ri-eye-line me-1"></i> Visibilidad',
                        className: 'btn btn-outline-secondary btn-sm'
                    }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                },
                order: [[1, 'asc']],
                columnDefs: [
                    { orderable: false, targets: 4 }
                ]
            });

            const initialSearchValue = '<?php echo addslashes($search); ?>';
            if (initialSearchValue) {
                table.search(initialSearchValue).draw();
            }
        }
    });
</script>
