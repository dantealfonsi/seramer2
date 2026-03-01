<?php
require_once __DIR__ . '/../../controllers/EuroRateController.php';

$controller = new EuroRateController();
$data = $controller->index();
$rates = $data['rates'];
$page_title = $data['page_title'];

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';

$months = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
?>

<style>
    .bg-gradient-warning {
        background: linear-gradient(135deg, #ffab00 0%, #ffcf50 100%);
        color: white;
    }
    .main-container {
        padding: 1.5rem;
        background-color: #f5f5f9;
    }
    #ratesTable thead th {
        background-color: #000000 !important;
        color: white !important;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
        border: none;
        padding: 1.25rem 1rem;
    }
    #ratesTable thead th:first-child {
        border-top-left-radius: 8px;
    }
    #ratesTable thead th:last-child {
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
                                    <i class="ri-money-euro-box-line" style="color: #696cff; font-size: 1.5rem;"></i>
                                </div>
                                <?php echo htmlspecialchars($page_title); ?>
                            </h5>
                            <a href="create.php" class="btn btn-warning text-white px-4 shadow-sm">
                                <i class="ri-add-line me-1"></i> Registrar Tasa
                            </a>
                        </div>

                        <!-- Métrica Rápida Estilo Metro -->
                        <div class="card card-status-warning mb-4" style="background-color: var(--metro-warning-light);">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="page-icon me-3" style="width:52px;height:52px;font-size:1.6rem; color: var(--metro-warning) !important; background-color: transparent !important;">
                                    <i class="ri-exchange-funds-line"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0 fw-bold" style="color: var(--metro-warning);"><?php echo !empty($rates) ? number_format($rates[0]['bs_value'], 2) . ' Bs.' : 'No establecida'; ?></h3>
                                    <p class="mb-0 text-muted fw-semibold" style="font-size:0.8rem;">TASA VIGENTE (EURO)</p>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle w-100" id="ratesTable">
                                <thead>
                                    <tr>
                                        <th>Período</th>
                                        <th>Valor del Euro (Bs.)</th>
                                        <th>Fecha Registro</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rates as $rate): ?>
                                        <tr>
                                            <td>
                                                <span class="fw-bold text-dark">
                                                    <?php echo ($months[$rate['month']] ?? $rate['month']) . ' ' . $rate['year']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-label-warning p-2" style="font-size: 1rem;">
                                                    <?php echo number_format($rate['bs_value'], 2); ?> Bs.
                                                </span>
                                            </td>
                                            <td><?php echo isset($rate['created_at']) ? date('d/m/Y H:i', strtotime($rate['created_at'])) : 'N/A'; ?></td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a href="edit.php?id=<?php echo $rate['id']; ?>" class="btn btn-sm btn-outline-warning" title="Editar">
                                                        <i class="ri-pencil-line"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="confirmDelete(<?php echo $rate['id']; ?>)">
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
     function confirmDelete(id) {
         Swal.fire({
             title: '¿Estás seguro?',
             text: "Esta acción no se puede deshacer.",
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
             $('#ratesTable').DataTable({
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
                                             { text: 'HISTORIAL DE TASA DEL EURO', fontSize: 12, bold: true }
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
                         title: 'Tasas_Euro'
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
                 },
                 order: [[2, 'desc']]
             });
         }
     });
 </script>
