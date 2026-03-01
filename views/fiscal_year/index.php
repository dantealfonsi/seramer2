<?php
require_once __DIR__ . '/../../controllers/FiscalYearController.php';

$controller = new FiscalYearController();
$data = $controller->index();
$fiscalYears = $data['fiscalYears'];
$page_title = $data['page_title'];

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #696cff 0%, #7172ff 100%);
        color: white;
    }
    .main-container {
        padding: 1.5rem;
        background-color: #f5f5f9;
    }
    #fiscalYearTable thead th {
        background-color: #000000 !important;
        color: white !important;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
        border: none;
        padding: 1.25rem 1rem;
    }
    #fiscalYearTable thead th:first-child {
        border-top-left-radius: 8px;
    }
    #fiscalYearTable thead th:last-child {
        border-top-right-radius: 8px;
    }
</style>

<div class="main-content main-container">
    <div class="container-xxl">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        
                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-center mb-5">
                            <h5 class="mb-0 d-flex align-items-center" style="font-size: 1.75rem; font-weight: 600; color: #43495b;">
                                <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;">
                                    <i class="ri-calendar-line" style="color: #696cff; font-size: 1.5rem;"></i>
                                </div>
                                <?php echo htmlspecialchars($page_title); ?>
                            </h5>
                            <a href="create.php" class="btn btn-primary px-4 shadow-sm">
                                <i class="ri-add-line me-1"></i> Nuevo Año Fiscal
                            </a>
                        </div>

                        <?php
                        $currentFY = 'No establecido';
                        foreach ($fiscalYears as $fy) {
                            if ($fy['status'] === 'active') {
                                $currentFY = $fy['year'];
                                break;
                            }
                        }
                        ?>
                        <!-- Info Card Estilo Metro -->
                        <div class="card card-status-primary mb-4" style="background-color: var(--metro-primary-light);">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="page-icon me-3" style="width:52px;height:52px;font-size:1.6rem; color: var(--metro-primary) !important; background-color: transparent !important;">
                                    <i class="ri-calendar-check-line"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0 fw-bold" style="color: var(--metro-primary);"><?php echo htmlspecialchars($currentFY); ?></h3>
                                    <p class="mb-0 text-muted fw-semibold" style="font-size:0.8rem;">AÑO FISCAL ACTUAL</p>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle w-100" id="fiscalYearTable">
                                <thead>
                                    <tr>
                                        <th>Año</th>
                                        <th>Fecha Inicio</th>
                                        <th>Fecha Fin</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($fiscalYears as $fy): ?>
                                        <tr>
                                            <td>
                                                <span class="fw-bold text-dark" style="font-size: 1.1rem;"><?php echo htmlspecialchars($fy['year']); ?></span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($fy['start_date'])); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($fy['end_date'])); ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-label-<?php echo $fy['status'] === 'active' ? 'success' : 'secondary'; ?> px-3 py-2">
                                                    <?php echo $fy['status'] === 'active' ? 'Activo' : 'Inactivo'; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a href="edit.php?id=<?php echo $fy['id']; ?>" class="btn btn-sm btn-outline-warning" title="Editar">
                                                        <i class="ri-pencil-line"></i>
                                                    </a>
                                                    <?php if ($fy['status'] !== 'active'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="confirmDelete(<?php echo $fy['id']; ?>, '<?php echo $fy['year']; ?>')">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                    <?php endif; ?>
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

 <script>
     function confirmDelete(id, year) {
         Swal.fire({
             title: '¿Estás seguro?',
             text: `Vas a eliminar el año fiscal ${year}. Esta acción no se puede deshacer.`,
             icon: 'warning',
             showCancelButton: true,
             confirmButtonColor: '#ff3e1d',
             cancelButtonColor: '#8592a3',
             confirmButtonText: 'Sí, eliminar',
             cancelButtonText: 'Cancelar'
         }).then((result) => {
             if (result.isConfirmed) {
                 document.getElementById('deleteId').value = id;
                 document.getElementById('deleteForm').submit();
             }
         });
     }

     $(document).ready(function() {
         if ($.fn.DataTable) {
             $('#fiscalYearTable').DataTable({
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
                                             { text: 'GESTIÓN DE AÑOS FISCALES', fontSize: 12, bold: true }
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
                         title: 'Años Fiscales'
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
                 },
                 order: [[0, 'desc']]
             });
         }
     });
 </script>
