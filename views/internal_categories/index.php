<?php
// Vista de listado de rubros internos
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controllers/InternalCategoryController.php';
require_once __DIR__ . '/../../controllers/RolesController.php';

$controller = new InternalCategoryController();
$rol = new RolesController();

$data = $controller->index();
$categories = $data['categories'];
$page_title = $data['page_title'];
$filters = $data['filters'];
$totalCategories = count($categories);

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #696cff 0%, #32c682 100%);
        color: white;
    }
    .main-container {
        padding: 1.5rem;
        background-color: #f5f5f9;
    }
    #categoriesTable thead th {
        background-color: #000000 !important;
        color: white !important;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
        border: none;
        padding: 1.25rem 1rem;
    }
    #categoriesTable thead th:first-child {
        border-top-left-radius: 8px;
    }
    #categoriesTable thead th:last-child {
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
                                    <i class="ri-menu-line" style="color: #696cff; font-size: 1.5rem;"></i>
                                </div>
                                <?php echo htmlspecialchars($page_title); ?>
                            </h5>
                            <a href="create.php" class="btn btn-primary px-4 shadow-sm" style="background-color: #696cff; border-color: #696cff; font-weight: 500;">
                                <i class="ri-add-line me-1"></i> Nuevo Rubro
                            </a>
                        </div>

                        <!-- Filtros Avanzados -->
                        <div class="filter-card">
                            <div class="filter-card-title">
                                <i class="ri-filter-2-line"></i> Opciones de Filtrado Avanzado
                            </div>
                            <div class="filter-card-body">
                                <form method="GET" action="index.php">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label fw-bold small text-uppercase">Nombre del Rubro</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="ri-price-tag-3-line text-muted"></i></span>
                                                <input type="text" name="name" class="form-control" placeholder="Buscar por nombre..." value="<?php echo htmlspecialchars($filters['name']); ?>">
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

                        <!-- Métrica Rápida -->
                        <div class="card border-0 bg-gradient-primary overflow-hidden mb-4" style="border-radius: 0.5rem; box-shadow: 0 4px 15px rgba(105, 108, 255, 0.2);">
                            <div class="card-body p-4 position-relative">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg bg-white bg-opacity-25 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                                        <i class="ri-price-tag-3-line ri-2x text-white"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 text-white fw-bold"><?php echo number_format($totalCategories); ?></h3>
                                        <p class="mb-0 text-white-50 fw-semibold">Rubros Internos Registrados</p>
                                    </div>
                                </div>
                                <div class="position-absolute" style="right: -10px; bottom: -20px; opacity: 0.1;">
                                    <i class="ri-price-tag-3-line text-white" style="font-size: 6rem;"></i>
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
                            <table class="table table-hover align-middle w-100" id="categoriesTable">
                                <thead>
                                    <tr>
                                        <th>Nombre del Rubro</th>
                                        <th class="text-center">Pagos Anuales</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $cat): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm bg-label-info rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; background-color: #d7f5fc !important; color: #03c3ec !important;">
                                                        <i class="ri-price-tag-3-line"></i>
                                                    </div>
                                                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($cat['name']); ?></span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-label-primary px-3 py-2" style="background-color: #e7e7ff; color: #696cff; font-size: 0.9rem; font-weight: 600;">
                                                    <?php echo $cat['payment_count']; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a href="edit.php?id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-warning" style="padding: 0.4rem; border-radius: 0.5rem;" title="Editar">
                                                        <i class="ri-pencil-line"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete" 
                                                            style="padding: 0.4rem; border-radius: 0.5rem;" 
                                                            title="Eliminar" 
                                                            data-id="<?php echo $cat['id']; ?>" 
                                                            data-name="<?php echo htmlspecialchars($cat['name']); ?>">
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
                text: `Vas a eliminar el rubro: ${name}. Esta acción no se puede deshacer.`,
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
            $('#categoriesTable').DataTable({
                responsive: true,
                dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rtip',
                buttons: [
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="ri-file-pdf-line me-1"></i> PDF',
                        className: 'btn btn-danger btn-sm me-1',
                        exportOptions: { columns: [0, 1] },
                        customize: function (doc) {
                            doc.content.splice(0, 1);
                            doc.content.unshift({
                                columns: [
                                    { image: commonPdfLogo, width: 50 },
                                    {
                                        text: [
                                            { text: 'SERVICIO AUTÓNOMO DE MERCADO MUNICIPAL DE BERMÚDEZ\n', fontSize: 10, bold: true },
                                            { text: 'RUBROS INTERNOS', fontSize: 12, bold: true }
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
                        exportOptions: { columns: [0, 1] },
                        title: 'Rubros Internos'
                    },
                    {
                        extend: 'print',
                        text: '<i class="ri-printer-line me-1"></i> Imprimir',
                        className: 'btn btn-info btn-sm',
                        exportOptions: { columns: [0, 1] }
                    }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                }
            });
        }
    });
</script>
