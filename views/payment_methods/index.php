<?php
// Vista de listado de métodos de pago
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controllers/PaymentMethodController.php';
require_once __DIR__ . '/../../controllers/RolesController.php';

$controller = new PaymentMethodController();
$rol = new RolesController();

$data = $controller->index();
$methods = $data['methods'];
$page_title = $data['page_title'];
$filters = $data['filters'];
$totalMethods = count($methods);

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<style>
    .bg-gradient-success {
        background: linear-gradient(135deg, #71dd37 0%, #32c682 100%);
        color: white;
    }
    .main-container {
        padding: 1.5rem;
        background-color: #f5f5f9;
    }
    #methodsTable thead th {
        background-color: #000000 !important;
        color: white !important;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
        border: none;
        padding: 1.25rem 1rem;
    }
    #methodsTable thead th:first-child {
        border-top-left-radius: 8px;
    }
    #methodsTable thead th:last-child {
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
                                    <i class="ri-bank-card-line" style="color: #696cff; font-size: 1.5rem;"></i>
                                </div>
                                <?php echo htmlspecialchars($page_title); ?>
                            </h5>
                            <a href="create.php" class="btn btn-primary px-4 shadow-sm" style="background-color: #696cff; border-color: #696cff; font-weight: 500;">
                                <i class="ri-add-line me-1"></i> Nuevo Método
                            </a>
                        </div>

                        <!-- Filtros Avanzados -->
                        <div class="card-inside p-4 mb-4">
                            <form method="GET" action="index.php" class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label fw-bold small text-uppercase">Nombre del Método</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0"><i class="ri-bank-card-line text-muted"></i></span>
                                        <input type="text" name="name" class="form-control border-start-0" placeholder="Buscar por nombre..." value="<?php echo htmlspecialchars($filters['name']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-bold small text-uppercase">Estado</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0"><i class="ri-checkbox-circle-line text-muted"></i></span>
                                        <select name="status" class="form-select border-start-0">
                                            <option value="">Todos los Estados</option>
                                            <option value="active" <?php echo $filters['status'] === 'active' ? 'selected' : ''; ?>>Activo</option>
                                            <option value="inactive" <?php echo $filters['status'] === 'inactive' ? 'selected' : ''; ?>>Inactivo</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end gap-2">
                                    <a href="index.php" class="btn btn-outline-secondary w-50 d-flex align-items-center justify-content-center" title="Limpiar">
                                        <i class="ri-refresh-line me-1"></i> Limpiar
                                    </a>
                                    <button type="submit" class="btn btn-info w-50 text-white d-flex align-items-center justify-content-center" title="Buscar">
                                        <i class="ri-search-line me-1"></i> Buscar
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Métrica Rápida Estilo Metro -->
                        <div class="card card-status-success mb-4" style="background-color: var(--metro-success-light);">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="page-icon me-3" style="width:52px;height:52px;font-size:1.6rem; color: var(--metro-success) !important; background-color: transparent !important;">
                                    <i class="ri-bank-card-line"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0 fw-bold" style="color: var(--metro-success);"><?php echo number_format($totalMethods); ?></h3>
                                    <p class="mb-0 text-muted fw-semibold" style="font-size:0.8rem;">MÉTODOS DE PAGO HABILITADOS</p>
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
                            <table class="table table-hover align-middle w-100" id="methodsTable">
                                <thead>
                                    <tr>
                                        <th>Nombre del Método</th>
                                        <th>Descripción / Referencia</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($methods as $method): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm bg-label-success rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; background-color: #e8fadf !important; color: #71dd37 !important;">
                                                        <i class="ri-bank-card-line"></i>
                                                    </div>
                                                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($method['name']); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted small">
                                                    <?php echo htmlspecialchars($method['description'] ?? 'Método de pago estándar'); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php 
                                                $isActive = ($method['status'] ?? 'active') === 'active';
                                                ?>
                                                <span class="badge bg-label-<?php echo $isActive ? 'success' : 'danger'; ?> px-3 py-2" style="font-size: 0.8rem; font-weight: 600;">
                                                    <?php echo $isActive ? 'Activo' : 'Inactivo'; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a href="edit.php?id=<?php echo $method['id']; ?>" class="btn btn-sm btn-outline-warning" style="padding: 0.4rem; border-radius: 0.5rem;" title="Editar">
                                                        <i class="ri-pencil-line"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete" 
                                                            style="padding: 0.4rem; border-radius: 0.5rem;" 
                                                            title="Eliminar" 
                                                            data-id="<?php echo $method['id']; ?>" 
                                                            data-name="<?php echo htmlspecialchars($method['name']); ?>">
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
                text: `Vas a eliminar el método de pago: ${name}. Esta acción no se puede deshacer si tiene transacciones vinculadas.`,
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
            $('#methodsTable').DataTable({
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
                                            { text: 'MÉTODOS DE PAGO', fontSize: 12, bold: true }
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
                        title: 'Métodos de Pago'
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
