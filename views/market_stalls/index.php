<?php
// Vista de listado de locales
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controllers/MarketStallController.php';
require_once __DIR__ . '/../../controllers/RolesController.php';

$controller = new MarketStallController();
$rol = new RolesController();

$data = $controller->index();
$stalls = $data['stalls'];
$zones = $data['zones'];
$sectors = $data['sectors'];
$page_title = $data['page_title'];
$filters = $data['filters'];
$totalStalls = count($stalls);

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<style>
    .bg-gradient-secondary {
        background: linear-gradient(135deg, #8592a3 0%, #bdc3c7 100%);
        color: white;
    }
    .main-container {
        padding: 1.5rem;
        background-color: #f5f5f9;
    }
    #stallsTable thead th {
        background-color: #000000 !important;
        color: white !important;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
        border: none;
        padding: 1.25rem 1rem;
    }
    #stallsTable thead th:first-child {
        border-top-left-radius: 8px;
    }
    #stallsTable thead th:last-child {
        border-top-right-radius: 8px;
    }
    .card-inside {
        background-color: #fff;
        border: 1px solid #d9dee3;
        border-radius: 0.5rem;
    }
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
                                    <i class="ri-store-2-line" style="color: #696cff; font-size: 1.5rem;"></i>
                                </div>
                                <?php echo htmlspecialchars($page_title); ?>
                            </h5>
                            <a href="create.php" class="btn btn-primary px-4 shadow-sm" style="background-color: #696cff; border-color: #696cff; font-weight: 500;">
                                <i class="ri-add-line me-1"></i> Nuevo Local
                            </a>
                        </div>

                        <!-- Filtros Avanzados -->
                        <div class="card-inside p-4 mb-4">
                            <form method="GET" action="index.php" class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small text-uppercase">Número de Local</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0"><i class="ri-hashtag text-muted"></i></span>
                                        <input type="text" name="stall_number" class="form-control border-start-0" placeholder="Ej: 101-A" value="<?php echo htmlspecialchars($filters['stall_number']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small text-uppercase">Zona</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0"><i class="ri-map-pin-line text-muted"></i></span>
                                        <select name="zone_id" class="form-select border-start-0" id="zone_select">
                                            <option value="">Todas las Zonas</option>
                                            <?php foreach ($zones as $z): ?>
                                                <option value="<?php echo $z['id']; ?>" <?php echo $filters['zone_id'] == $z['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($z['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small text-uppercase">Sector</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0"><i class="ri-building-line text-muted"></i></span>
                                        <select name="sector_id" class="form-select border-start-0" id="sector_select">
                                            <option value="">Todos los Sectores</option>
                                            <?php foreach ($sectors as $s): ?>
                                                <option value="<?php echo $s['id']; ?>" <?php echo $filters['sector_id'] == $s['id'] ? 'selected' : ''; ?> data-zone="<?php echo $s['zone_id']; ?>">
                                                    <?php echo htmlspecialchars($s['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small text-uppercase">Estado</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0"><i class="ri-checkbox-circle-line text-muted"></i></span>
                                        <select name="status" class="form-select border-start-0">
                                            <option value="">Todos los Estados</option>
                                            <option value="vacant" <?php echo $filters['status'] === 'vacant' ? 'selected' : ''; ?>>Disponible</option>
                                            <option value="occupied" <?php echo $filters['status'] === 'occupied' ? 'selected' : ''; ?>>Ocupado</option>
                                            <option value="maintenance" <?php echo $filters['status'] === 'maintenance' ? 'selected' : ''; ?>>Mantenimiento</option>
                                            <option value="closed" <?php echo $filters['status'] === 'closed' ? 'selected' : ''; ?>>Clausurado</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 filter-card-actions">
                                    <a href="index.php" class="btn btn-outline-secondary px-4" title="Limpiar"><i class="ri-refresh-line me-1"></i> Limpiar</a>
                                    <button type="submit" class="btn btn-info px-4 text-white" title="Buscar"><i class="ri-search-line me-1"></i> Buscar</button>
                                </div>
                            </form>
                        </div>

                        <!-- Métrica Rápida -->
                        <div class="card border-0 bg-gradient-secondary overflow-hidden mb-4" style="border-radius: 0.5rem; box-shadow: 0 4px 15px rgba(133, 146, 163, 0.2);">
                            <div class="card-body p-4 position-relative">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg bg-white bg-opacity-25 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                                        <i class="ri-layout-grid-line ri-2x text-white"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 text-white fw-bold"><?php echo number_format($totalStalls); ?></h3>
                                        <p class="mb-0 text-white-50 fw-semibold">Locales Registrados</p>
                                    </div>
                                </div>
                                <div class="position-absolute" style="right: -10px; bottom: -20px; opacity: 0.1;">
                                    <i class="ri-store-2-line text-white" style="font-size: 6rem;"></i>
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

                        <!-- Tabla -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle w-100" id="stallsTable">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Sector / Zona</th>
                                        <th>Tipo / Uso</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stalls as $stall): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm bg-label-secondary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; background-color: #f2f2f7 !important; color: #43495b !important;">
                                                        <i class="ri-hashtag"></i>
                                                    </div>
                                                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($stall['stall_number']); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold text-dark small"><?php echo htmlspecialchars($stall['sector_name'] ?? 'S/S'); ?></span>
                                                    <small class="text-muted"><?php echo htmlspecialchars($stall['zone_name'] ?? 'S/Z'); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted small"><?php echo htmlspecialchars($stall['type'] ?? 'Genérico'); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <?php 
                                                $statusClass = 'secondary';
                                                $statusText = 'Desconocido';
                                                switch($stall['status']) {
                                                    case 'vacant': $statusClass = 'success'; $statusText = 'Disponible'; break;
                                                    case 'occupied': $statusClass = 'info'; $statusText = 'Ocupado'; break;
                                                    case 'maintenance': $statusClass = 'warning'; $statusText = 'Mantenimiento'; break;
                                                    case 'closed': $statusClass = 'danger'; $statusText = 'Clausurado'; break;
                                                }
                                                ?>
                                                <span class="badge bg-label-<?php echo $statusClass; ?> px-3 py-2" style="font-size: 0.8rem; font-weight: 600;">
                                                    <?php echo $statusText; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a href="edit.php?id=<?php echo $stall['id']; ?>" class="btn btn-sm btn-outline-warning" style="padding: 0.4rem; border-radius: 0.5rem;" title="Editar">
                                                        <i class="ri-pencil-line"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete" 
                                                            style="padding: 0.4rem; border-radius: 0.5rem;" 
                                                            title="Eliminar" 
                                                            data-id="<?php echo $stall['id']; ?>" 
                                                            data-number="<?php echo htmlspecialchars($stall['stall_number']); ?>">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
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

<form id="deleteForm" method="POST" action="delete.php" style="display: none;">
    <input type="hidden" name="id" id="deleteId">
</form>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<!-- DataTables Dependencies -->
<script type="text/javascript" src="../../public/assets/js/pdf_logo.js"></script>
<script type="text/javascript" src="../../public/datatables/jszip.min.js"></script>
<script type="text/javascript" src="../../public/datatables/datatables.min.js"></script>
<script type="text/javascript" src="../../public/datatables/pdfmake.min.js"></script>
<script type="text/javascript" src="../../public/datatables/vfs_fonts.js"></script>
<link rel="stylesheet" type="text/css" href="../../public/datatables/datatables.min.css"/> 

<script>
    $(document).ready(function() {
        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            const number = $(this).data('number');

            Swal.fire({
                title: '¿Estás seguro?',
                text: `Vas a eliminar el local: ${number}. Esta acción no se puede deshacer si tiene ocupantes o contratos vinculados.`,
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
                    $('#deleteId').val(id);
                    $('#deleteForm').submit();
                }
            });
        });

        if ($.fn.DataTable) {
            $('#stallsTable').DataTable({
                responsive: true,
                dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rtip',
                buttons: [
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="ri-file-pdf-line me-1"></i> PDF',
                        className: 'btn btn-danger btn-sm me-1',
                        exportOptions: { columns: [0, 1, 2, 3] },
                        customize: function (doc) {
                            doc.content.splice(0, 1);
                            doc.content.unshift({
                                columns: [
                                    { image: commonPdfLogo, width: 50 },
                                    {
                                        text: [
                                            { text: 'SERVICIO AUTÓNOMO DE MERCADO MUNICIPAL DE BERMÚDEZ\n', fontSize: 10, bold: true },
                                            { text: 'GESTIÓN DE LOCALES', fontSize: 12, bold: true }
                                        ],
                                        margin: [10, 0, 0, 0]
                                    }
                                ],
                                margin: [0, 0, 0, 10]
                            });
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="ri-file-excel-line me-1"></i> Excel',
                        className: 'btn btn-success btn-sm me-1',
                        exportOptions: { columns: [0, 1, 2, 3] },
                        title: 'Locales Registrados'
                    },
                    {
                        extend: 'print',
                        text: '<i class="ri-printer-line me-1"></i> Imprimir',
                        className: 'btn btn-info btn-sm',
                        exportOptions: { columns: [0, 1, 2, 3] }
                    }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                }
            });
        }
    });
</script>
