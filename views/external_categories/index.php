<?php
require_once __DIR__ . '/../../controllers/ExternalCategoryController.php';

$controller = new ExternalCategoryController();
$data = $controller->index();
$categories = $data['categories'];
$page_title = $data['page_title'];

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center border-bottom py-3">
                        <div class="d-flex align-items-center">
                            <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;">
                                <i class="ri-external-link-line" style="color: #696cff; font-size: 1.5rem;"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-0" style="font-size: 1.25rem; font-weight: 600; color: #43495b;">
                                    <?php echo htmlspecialchars($page_title); ?>
                                </h5>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0" style="font-size: 0.8rem;">
                                        <li class="breadcrumb-item text-muted">Liquidación</li>
                                        <li class="breadcrumb-item active">Rubros Externos</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                        <a href="create.php" class="btn btn-primary px-4 shadow-sm" style="background-color: #696cff; border-color: #696cff; font-weight: 500;">
                            <i class="ri-add-line me-1"></i> Nuevo Rubro
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($categories)): ?>
                            <div class="alert alert-info">No hay rubros externos registrados.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="externalCategoriesTable">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Tipo Instalación</th>
                                            <th class="text-center">Pagos anuales</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categories as $cat): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($cat['name']); ?></td>
                                                <td><?php echo htmlspecialchars($cat['installation_type'] ?? '-'); ?></td>
                                                <td class="text-center"><?php echo htmlspecialchars($cat['payment_count']); ?></td>
                                                <td class="text-center">
                                                    <a href="edit.php?id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-warning" style="padding: 0.4rem; border-radius: 0.5rem;" title="Editar">
                                                        <i class="ri-pencil-line"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
        if ($.fn.DataTable) {
            $('#externalCategoriesTable').DataTable({
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
                                            { text: 'RUBROS EXTERNOS', fontSize: 12, bold: true }
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
                        title: 'Rubros Externos'
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
