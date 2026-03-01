<?php
session_start();
require_once __DIR__ . '/../../controllers/InfractionTypesController.php';

$infractionTypesController = new InfractionTypesController();

$params = [
    'page' => $_GET['page'] ?? 1,
    'search' => $_GET['search'] ?? ''
];

$result = $infractionTypesController->index($params);

// Extrae $infraction_types, $current_page, $total_pages, etc.
extract($result);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];
    $deleteResult = $infractionTypesController->delete($deleteId);

    $_SESSION['flash_message'] = [
        'type' => $deleteResult['success'] ? 'success' : 'danger',
        'message' => $deleteResult['message']
    ];

    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';

?>
<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?> mt-2" role="alert">
        <?php echo htmlspecialchars($_SESSION['flash_message']['message']); ?>
    </div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title d-flex align-items-center" style="font-size: 1.4rem;font-weight: 600;">
                            <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="ri-alert-line" style="color: #696cff; font-size: 1.5rem;"></i></div>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri-add-line"></i> Nuevo Tipo de Infracción
                        </a>
                    </div>
                    
                    <div class="card-body border-bottom">
                         <!-- El formulario de búsqueda manual se elimina o refactoriza para DataTables -->
                         <!-- DataTables tiene su propio buscador, así que podemos simplificar esto -->
                        <div class="alert alert-info border-start border-info border-5 shadow-sm">
                            <i class="ri-information-line"></i>
                            <div class="alert-content">
                                <strong>Información:</strong> Utilice el cuadro de "Buscar" en la tabla para filtrar por cualquier columna. Use los botones para Exportar los resultados.
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <?php if (empty($infraction_types)): ?>
                            <div class="text-center py-4">
                                <i class="ri-file-search-line text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-2">
                                    No hay tipos de infracción registrados
                                </h5>
                                <a href="create.php" class="btn btn-primary mt-2">
                                    <i class="ri-add-line"></i> Registrar Primer Tipo de Infracción
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="infractionTypesTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Tipo de Infracción</th>
                                            <th>Descripción</th>
                                            <th>Artículo Violado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($infraction_types as $infractionType): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($infractionType['infraction_type_name']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($infractionType['description']); ?></td>
                                            <td><?php echo htmlspecialchars($infractionType['violated_article']); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="edit.php?id=<?php echo $infractionType['infraction_type_id']; ?>" class="btn btn-sm btn-outline-warning" title="Editar"><i class="ri-edit-line"></i></a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo $infractionType['infraction_type_id']; ?>)" title="Eliminar"><i class="ri-delete-bin-line"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- Paginación manual eliminada, DataTables se encarga -->
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar el tipo de infracción con ID: <strong id="infractionTypeId"></strong>?</p>
                <p class="text-danger"><small>Esta acción es permanente y puede afectar a otros registros que hagan referencia a este tipo de infracción.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<!-- DataTables includes -->
<script type="text/javascript" src="../../public/datatables/datatables.min.js"></script>
<script type="text/javascript" src="../../public/datatables/pdfmake.min.js"></script>
<script type="text/javascript" src="../../public/datatables/vfs_fonts.js"></script>
<link rel="stylesheet" type="text/css" href="../../public/datatables/datatables.min.css"/> 
<link rel="stylesheet" type="text/css" href="../../public/datatables/buttons.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="../../public/assets/css/dani-styles.css"/>

<script>
let deleteInfractionTypeId = null;

function confirmDelete(id) {
    deleteInfractionTypeId = id;
    document.getElementById('infractionTypeId').textContent = id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteInfractionTypeId) {
        window.location.href = 'index.php?delete_id=' + deleteInfractionTypeId; 
    }
});

// DataTables Initialization
$(document).ready(function() {
    if ($.fn.DataTable) {
         $('#infractionTypesTable').DataTable({ 
            responsive: true,
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'pdfHtml5',
                    text: '<i class="ri-file-pdf-line"></i> PDF',
                    className: 'btn btn-danger btn-sm me-1',
                    orientation: 'portrait', 
                    pageSize: 'LETTER', 
                    exportOptions: {
                        columns: [0, 1, 2] // Exclude Actions
                    },
                    title: 'Tipos de Infracción - Seramer'
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="ri-file-excel-line"></i> Excel',
                    className: 'btn btn-success btn-sm me-1',
                    exportOptions: {
                        columns: [0, 1, 2] 
                    },
                    title: 'Tipos_Infraccion_Seramer' 
                },
                {
                    extend: 'print',
                    text: '<i class="ri-printer-line"></i> Imprimir',
                    className: 'btn btn-info btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2] 
                    }
                },
                'colvis'
            ],
            language: {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json",
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
            "columnDefs": [
                { "orderable": false, "targets": 3 } // Disable sorting on Actions
            ]
        });
    }
});
</script>
