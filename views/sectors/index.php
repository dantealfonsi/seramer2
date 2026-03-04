<?php
// Vista de listado de sectores
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controllers/SectorController.php';
require_once __DIR__ . '/../../controllers/RolesController.php';

$controller = new SectorController();
$rol = new RolesController();

$data = $controller->index();
$sectors = $data['sectors'];
$zones = $data['zones'];
$page_title = $data['page_title'];
$filters = $data['filters'];
$totalSectors = count($sectors);

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content" style="padding: 1.5rem;">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Contenedor Blanco Principal -->
                <div class="card shadow-sm border-0">
                    <!-- 1. Encabezado (Título y Botón) -->
                    <div class="card-header d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0 d-flex align-items-center" style="font-size: 1.4rem; font-weight: 600;">
                            <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;">
                                <i class="ri-community-line" style="color: #696cff; font-size: 1.5rem;"></i>
                            </div>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri-add-line"></i> Nuevo Sector
                        </a>
                    </div>

                    <div class="card-body p-4 pt-0">
                        
                        <!-- Filtros Avanzados -->
                        <div class="filter-card mb-4">
                            <div class="filter-card-title">
                                <i class="ri-filter-2-line"></i> Opciones de Filtrado Avanzado
                            </div>
                            <div class="filter-card-body">
                                <form method="GET" action="index.php">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small text-uppercase">Nombre del Sector</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="ri-building-line text-muted"></i></span>
                                                <input type="text" name="name" class="form-control" placeholder="Buscar por nombre..." value="<?php echo htmlspecialchars($filters['name']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small text-uppercase">Zona</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="ri-map-pin-line text-muted"></i></span>
                                                <select name="zone_id" class="form-select">
                                                    <option value="">Todas las Zonas</option>
                                                    <?php foreach ($zones as $z): ?>
                                                        <option value="<?php echo $z['id']; ?>" <?php echo $filters['zone_id'] == $z['id'] ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($z['name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 filter-card-actions">
                                            <a href="index.php" class="btn btn-filter-clear">
                                                <i class="ri-refresh-line me-1"></i> Limpiar
                                            </a>
                                            <button type="submit" class="btn btn-filter-apply">
                                                <i class="ri-search-line me-1"></i> Filtrar
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Métrica Rápida Estilo Metro -->
                        <div class="row g-3 mt-4 mb-2">
                            <div class="col-12">
                                <div class="card card-status-primary" style="background-color: #ffffff; border: 1px solid #eee; border-radius: 12px; box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);">
                                    <div class="card-body p-3 d-flex align-items-center">
                                        <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background-color: #e7e7ff !important; color: #696cff;">
                                            <i class="ri-community-line" style="font-size: 1.6rem;"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-0 fw-bold" style="color: #696cff;"><?php echo number_format($totalSectors); ?></h4>
                                            <p class="mb-0 text-muted fw-semibold" style="font-size:0.75rem; text-transform: uppercase;">SECTORES REGISTRADOS</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mensajes Flash -->
                        <?php if (isset($_SESSION['flash_message'])): ?>
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: '<?php echo $_SESSION['flash_message']['type'] === 'success' ? 'success' : 'error'; ?>',
                                title: '<?php echo addslashes($_SESSION['flash_message']['message']); ?>',
                                showConfirmButton: false,
                                timer: 4000,
                                timerProgressBar: true,
                                width: '450px'
                            });
                        });
                        </script>
                        <?php unset($_SESSION['flash_message']); ?>
                        <?php endif; ?>

                        <!-- Tabla -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle w-100" id="sectorsTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Cód. Sector</th>
                                        <th>Nombre del Sector</th>
                                        <th>Zona Perteneciente</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sectors as $sector): ?>
                                        <tr>
                                            <td class="fw-bold">S-<?php echo str_pad($sector['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm bg-label-info rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                        <i class="ri-building-line"></i>
                                                    </div>
                                                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($sector['name']); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-label-warning px-3 py-2" style="font-size: 0.85rem; font-weight: 600;">
                                                    <i class="ri-map-pin-line me-1"></i>
                                                    <?php echo htmlspecialchars($sector['zone_name'] ?? 'N/A'); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a href="edit.php?id=<?php echo $sector['id']; ?>" class="btn btn-sm btn-outline-warning" style="padding: 0.4rem; border-radius: 0.5rem;" title="Editar">
                                                        <i class="ri-pencil-line"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete" 
                                                            style="padding: 0.4rem; border-radius: 0.5rem;" 
                                                            title="Eliminar" 
                                                            data-id="<?php echo $sector['id']; ?>" 
                                                            data-name="<?php echo htmlspecialchars($sector['name']); ?>">
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
            const name = $(this).data('name');

            Swal.fire({
                title: '¿Estás seguro?',
                text: `Vas a eliminar el sector: ${name}. Esta acción no se puede deshacer si tiene locales asociados.`,
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
            $('#sectorsTable').DataTable({
                responsive: true,
                dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rtip',
                buttons: [
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="ri-file-pdf-line me-1"></i> PDF',
                        className: 'btn btn-danger btn-sm me-1',
                        exportOptions: { columns: [0, 1, 2] },
                        customize: function (doc) {
                            doc.content.splice(0, 1);
                            doc.content.unshift({
                                columns: [
                                    { image: commonPdfLogo, width: 50 },
                                    {
                                        text: [
                                            { text: 'SERVICIO AUTÓNOMO DE MERCADO MUNICIPAL DE BERMÚDEZ\n', fontSize: 10, bold: true },
                                            { text: 'GESTIÓN DE SECTORES', fontSize: 12, bold: true }
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
                        exportOptions: { columns: [0, 1, 2] },
                        title: 'Sectores Registrados'
                    },
                    {
                        extend: 'print',
                        text: '<i class="ri-printer-line me-1"></i> Imprimir',
                        className: 'btn btn-info btn-sm',
                        exportOptions: { columns: [0, 1, 2] }
                    }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                }
            });
        }
    });
</script>
