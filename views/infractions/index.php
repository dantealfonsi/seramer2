<?php
// Vista de listado de infracciones
session_start();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/InfractionsController.php';
require_once __DIR__ . '/../../controllers/RolesController.php';


$infractionsController = new InfractionsController();
$rol = new RolesController();

require_once __DIR__ . '/../../models/StatisticalReportModel.php';
$statsModel = new StatisticalReportModel();
$dashboardStats = $statsModel->getDashboardStats();
$infractionsThisMonth = $dashboardStats['infractions_this_month'] ?? 0;

// 1. Obtener la tasa actual (Asume que el controlador tiene un método para esto)
$economicIndicators = $infractionsController->getLatestEconomicIndicators();

// 2. Verificar si no existen indicadores (o si la función devuelve null/false)
if (is_null($economicIndicators) || empty($economicIndicators)) {
    // Almacenar el mensaje de error para mostrarlo en esta misma página
    if (basename($_SERVER['PHP_SELF']) == 'index.php') {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => '🚨 **ATENCIÓN:** No existen indicadores económicos (UT/Euro) registrados. **No puede crear nuevas infracciones** hasta que se establezcan las tasas vigentes. Por favor, regístrelas primero.'
        ];
        
        $can_create_infraction = false;
        
    } else {
        $can_create_infraction = false; 
    }
} else {
    $can_create_infraction = true; // Todo bien, puede crear infracciones
}

// 🚨 CAMBIO CLAVE: Eliminar la paginación y búsqueda por servidor.
// Ahora solo se usan los filtros de columna/estado y se obtiene TODO el listado.
$filters = [
    // El filtro 'search' se elimina de aquí, lo manejará DataTables.
    'infraction_date' => $_GET['infraction_date'] ?? null,
    'infraction_status' => $_GET['infraction_status'] ?? null,
    'infraction_type_id' => $_GET['infraction_type_id'] ?? null,
    'stall_id' => $_GET['stall_id'] ?? null,
    'awardee_id' => $_GET['awardee_id'] ?? null,
];

// Limpiar el arreglo eliminando valores nulos o vacíos
$activeFilters = array_filter($filters);

$params = [
    // La paginación y el límite se eliminan.
    'filters' => $activeFilters,
    // El 'search' aquí es solo para pre-llenar el input, no para filtrar el resultado de la DB.
    'search' => $_GET['search'] ?? '' 
];

// Usar el controlador para obtener TODOS los datos (sin paginación de servidor)
// NOTA: Para conjuntos de datos muy grandes, se debe implementar Server-Side Processing
// con DataTables, pero para un listado simple, obtener todos es el camino más fácil.
$result = $infractionsController->index($params); 

// Extraer variables para la vista
$infractions = $result['infractions'];
// 🚨 CAMBIO: Se eliminan $current_page, $total_pages, $total_records. DataTables los maneja.
$search = $result['search']; // Se mantiene para pre-llenar el input
$page_title = $result['page_title'];
// 🚨 CAMBIO: Se ajusta $has_search para la lógica de la vista (opcional, DataTables tiene su propia info)
$has_search = !empty($activeFilters) || !empty($search); 
$stalls = $infractionsController->getStallsList();
$infraction_types = $infractionsController->getInfractionTypesList();

// Mapa para traducir los estados
$status_text_map = [
    'Reported' => 'Reportada',
    'In Process' => 'En Proceso',
    'Resolved' => 'Resuelta',
    'Cancelled' => 'Cancelada'
];

// Incluir header y layouts
require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>
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
                        <?php if ($can_create_infraction && $rol->hasPermission('INFRACTIONS', 'w')): ?>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri-add-line"></i> Nueva Infracción
                        </a>
                        <?php else: ?>
                        <a href="#" class="btn btn-secondary disabled" 
                        title="Debe registrar tasas económicas antes de crear una infracción">
                            <i class="ri-alert-line"></i> Nueva Infracción
                        </a>
                        <?php endif; ?>        
                    </div>
                    
                    <div class="card-body border-bottom">
                        <div class="filter-card">
                            <div class="filter-card-title">
                                <i class="ri-filter-2-line"></i> Opciones de Filtrado Avanzado
                            </div>
                            <div class="filter-card-body">
                                <form action="index.php" method="GET">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label for="search" class="form-label small">Búsqueda General</label>
                                            <input type="text" class="form-control" id="search" name="search" 
                                                placeholder="Nombre, Puesto, Tipo..." 
                                                value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="infraction_status" class="form-label small">Estado</label>
                                            <select class="form-select" id="infraction_status" name="infraction_status">
                                                <option value="">-- Todos los Estados --</option>
                                                <?php 
                                                $current_status = $_GET['infraction_status'] ?? '';
                                                foreach ($status_text_map as $key => $value): ?>
                                                    <option value="<?php echo $key; ?>" 
                                                                    <?php echo ($current_status === $key) ? 'selected' : ''; ?>>
                                                        <?php echo $value; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="infraction_type_id" class="form-label small">Tipo de Infracción</label>
                                            <select class="form-select" id="infraction_type_id" name="infraction_type_id">
                                                <option value="">-- Todos los Tipos --</option>
                                                <?php 
                                                $current_type = $_GET['infraction_type_id'] ?? '';
                                                if (isset($infraction_types) && is_array($infraction_types)) {
                                                    foreach ($infraction_types as $type) {
                                                        $id = $type['infraction_type_id'];
                                                        $name = $type['infraction_type_name'];
                                                        echo "<option value=\"$id\" " . (($current_type == $id) ? 'selected' : '') . ">$name</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="infraction_date" class="form-label small">Fecha Específica</label>
                                            <input type="date" class="form-control" id="infraction_date" name="infraction_date" 
                                                value="<?php echo htmlspecialchars($_GET['infraction_date'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="stall_id" class="form-label small">Puesto</label>
                                            <select class="form-select" id="stall_id" name="stall_id">
                                                <option value="">-- Todos los Puestos --</option>
                                                <?php 
                                                $current_stall = $_GET['stall_id'] ?? '';
                                                if (isset($stalls) && is_array($stalls)) {
                                                    foreach ($stalls as $stall) {
                                                        $id = $stall['id'];
                                                        $number = $stall['stall_number'];
                                                        $selected = ($current_stall == $id) ? 'selected' : '';
                                                        echo "<option value=\"$id\" $selected>$number</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="awardee_id" class="form-label small">Adjudicatario</label>
                                            <select class="form-select" id="awardee_id" name="awardee_id">
                                                <option value="">-- Todos los Adjudicatarios --</option>
                                                <?php 
                                                $current_awardee = $_GET['awardee_id'] ?? '';
                                                if (isset($awardees) && is_array($awardees)) {
                                                    foreach ($awardees as $awardee) {
                                                        $id = $awardee['id'];
                                                        $name = $awardee['full_name'] ?? $awardee['first_name'];
                                                        $selected = ($current_awardee == $id) ? 'selected' : '';
                                                        echo "<option value=\"$id\" $selected>$name</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-12 filter-card-actions">
                                            <a href="index.php" class="btn btn-filter-clear"><i class="ri-refresh-line me-1"></i> Limpiar</a>
                                            <button type="submit" class="btn btn-filter-apply"><i class="ri-search-line me-1"></i> Filtrar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Tarjeta de Métrica (Infracciones del Mes) -->
                        <div class="row g-3 mt-4 mb-2">
                            <div class="col-12">
                                <div class="card card-status-danger" style="background-color: #ffffff; border: 1px solid #eee; border-radius: 12px; box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);">
                                    <div class="card-body p-3 d-flex align-items-center">
                                        <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; background-color: #ffe5e5 !important; color: #ff3e1d;">
                                            <i class="ri-alert-line" style="font-size: 1.6rem;"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-0 fw-bold" style="color: #ff3e1d;"><?php echo number_format($infractionsThisMonth); ?></h4>
                                            <p class="mb-0 text-muted fw-semibold" style="font-size:0.75rem; text-transform: uppercase;">Infracciones reportadas este mes</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($has_search && empty($activeFilters)): // Ahora $has_search solo indica si se usó el input de búsqueda global o filtros de columna ?>
                        <div class="mt-2">
                            <small class="text-muted">
                                Mostrando todos los registros (DataTables se encargará de la paginación).
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>

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

                    <div class="card-body">
                        <?php if (empty($infractions)): ?>
                            <div class="text-center py-4">
                                <i class="ri-alert-line text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-2">
                                    <?php echo $has_search ? 'No se encontraron infracciones con ese criterio' : 'No hay infracciones registradas'; ?>
                                </h5>
                                <?php if (!$has_search): ?>
                                <p class="text-muted">Comienza creando la primera infracción.</p>
                                <a href="create.php" class="btn btn-primary">
                                    <i class="ri-add-line"></i> Crear Primera Infracción
                                </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle" id="infractionsTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th> <th>Adjudicatario</th>
                                            <th>Puesto</th>
                                            <th>Tipo</th>
                                            <th>Fecha</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($infractions as $infraction): ?>
                                        <tr>
                                            <td class="d-none d-print-table-row"><?php echo htmlspecialchars($infraction['infraction_id']); ?></td>
                                            <td>
                                                <strong>
                                                    <?php echo htmlspecialchars($infraction['adjudicatory_name']); ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php echo htmlspecialchars($infraction['stall_number'] ?? 'N/A'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo htmlspecialchars($infraction['infraction_type_name']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                // Convertir la fecha para un formato legible en la tabla, pero usar el formato ISO para ordenar
                                                $infraction_date = new DateTime($infraction['infraction_datetime']);
                                                // Se añade data-order para que DataTables ordene correctamente por fecha
                                                echo '<span data-order="' . $infraction_date->format('Y-m-d H:i:s') . '">';
                                                echo $infraction_date->format('d/m/Y'); 
                                                echo '</span>';
                                                ?>
                                            </td>
                                            <td>
                                            <?php
                                            $status_colors = [
                                                'Reported' => 'warning',
                                                'In Process' => 'primary',
                                                'Resolved' => 'success',
                                                'Cancelled' => 'danger'
                                            ];
                                            $raw_status = $infraction['infraction_status'];
                                            
                                            $color = $status_colors[$raw_status] ?? 'secondary';
                                            $translated_status = $status_text_map[$raw_status] ?? 'Desconocido';
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?>">
                                            <?php echo htmlspecialchars($translated_status); // Muestra el estado traducido ?>
                                            </span>
                                            </td>
                                                <td class="text-center">
                                                    <?php if ($rol->hasPermission('INFRACTIONS', 'r')): ?>
                                                    <a href="view.php?id=<?php echo $infraction['infraction_id']; ?>" class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                    <?php if ($rol->hasPermission('INFRACTIONS', 'w')): ?>
                                                    <a href="edit.php?id=<?php echo $infraction['infraction_id']; ?>" class="btn btn-sm btn-outline-warning" title="Editar">
                                                        <i class="ri-edit-line"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Eliminar"
                                                            onclick="confirmDelete(<?php echo $infraction['infraction_id']; ?>)">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </td>
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

<script type="text/javascript" src="../../public/assets/js/pdf_logo.js"></script>
<script type="text/javascript" src="../../public/datatables/datatables.min.js"></script>
<script type="text/javascript" src="../../public/datatables/pdfmake.min.js"></script>
<script type="text/javascript" src="../../public/datatables/vfs_fonts.js"></script>
<link rel="stylesheet" type="text/css" href="../../public/datatables/datatables.min.css"/> 
<link rel="stylesheet" type="text/css" href="../../public/datatables/buttons.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="../../public/assets/css/dani-styles.css"/>

<script>
function confirmDelete(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Vas a eliminar la infracción #' + id + '. Esta acción no se puede deshacer.',
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
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'delete.php';

            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;
            form.appendChild(idInput);

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Inicialización de DataTables 🚀
$(document).ready(function() {
    
    // Contenido del encabezado personalizado para la vista de Impresión
    const customHeader = `
        <div style="text-align: center; margin-bottom: 20px;">
            <h1 style="margin: 0; font-size: 1.5em; text-align: center;">Servicio Autonómo de Mercados de Bermúdez</h1>
            <h2 style="margin: 0; font-size: 1.2em; text-align: center;">Listado de Infracciones</h2>
        </div>
    `;
    
    // Columnas a exportar
    // Columnas: ID (0, oculta), Adjudicatario (1), Puesto (2), Tipo (3), Fecha (4), Estado (5)
    // Se excluye la Columna 6 (Acciones)
    const exportColumns = [1, 2, 3, 4, 5]; 
    
    if ($.fn.DataTable) {
        // DataTables se inicializa ahora con todas las funcionalidades (dom: 'Bfrtip')
        const infractionsTable = $('#infractionsTable').DataTable({ 
            // Habilita la extensión Responsive
            responsive: true,
            
            // Configuración de los botones de exportación (B), Filtro (f), Tabla (t), Información (i), Paginación (p)
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'pdfHtml5',
                    text: '<i class="ri-file-pdf-line"></i> PDF',
                    className: 'btn btn-danger btn-sm me-1',
                    orientation: 'portrait', // Vertical
                    pageSize: 'LETTER', 
                    exportOptions: {
                        columns: exportColumns // Exportar solo las columnas visibles de datos
                    },
                    // Personalización del PDF
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

                        // 3. Agregar Línea Horizontal
                        doc.content.splice(1, 0, {
                            canvas: [{ type: 'line', x1: 0, y1: 5, x2: 515, y2: 5, lineWidth: 1, lineColor: '#000000' }],
                            margin: [0, 0, 0, 20]
                        });

                        // 4. Agregar Título Centrado
                        doc.content.splice(2, 0, {
                            text: 'Listado de Infracciones',
                            style: 'header',
                            alignment: 'center',
                            margin: [0, 0, 0, 15]
                        });

                        // 5. Estilo de la Tabla
                        const table = doc.content.find(content => content.table);
                        if (table) {
                            // Estilo de la cabecera
                            table.table.body[0].forEach(function(cell) {
                                cell.fillColor = '#2d4154';
                                cell.color = 'white';
                                cell.bold = true;
                                cell.alignment = 'center';
                            });

                            // Zebra striping
                            for (let i = 1; i < table.table.body.length; i++) {
                                if (i % 2 === 0) {
                                    table.table.body[i].forEach(function(cell) {
                                        cell.fillColor = '#f2f2f2';
                                    });
                                }
                            }
                            
                            // Ajustar anchos
                            table.table.widths = Array(table.table.body[0].length).fill('*');
                        }
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="ri-file-excel-line"></i> Excel',
                    className: 'btn btn-success btn-sm me-1',
                    exportOptions: {
                        columns: exportColumns 
                    },
                    title: 'Listado_Infracciones_Seramer' 
                },
                {
                    extend: 'print',
                    text: '<i class="ri-printer-line"></i> Imprimir',
                    className: 'btn btn-info btn-sm',
                    exportOptions: {
                        columns: exportColumns 
                    },
                    messageTop: customHeader, 
                    customize: function (win) {
                        $(win.document.body).find('table').addClass('w-100').css('width', '100%');
                        
                        $(win.document.body).find('head').append(
                            '<style>' +
                                '@media print { @page { size: letter; margin: 1cm; } } ' +
                                'table thead th { ' + 
                                '   background-color: #343a40 !important; ' + 
                                '   color: white !important; ' + 
                                '   -webkit-print-color-adjust: exact; ' + 
                                '   text-align: left !important;' + 
                                '}' +
                            '</style>'
                        );
                    }
                },
                'colvis' // Botón para visibilidad de columnas
            ],
            // Configuración de idioma a español
            language: {
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
            // Orden por defecto: Fecha (Columna 4, índice 4), descendente
            order: [[4, 'desc']], 
             // Deshabilitar el ordenamiento en las columnas de ID y Acciones
            "columnDefs": [
                { "visible": false, "targets": 0 }, // Ocultar columna ID
                { "orderable": false, "targets": 6 } // Columna Acciones
            ]
        });

        // 🚨 CAMBIO: Mover el valor del filtro 'search' (si existe) a la caja de búsqueda de DataTables
        const initialSearchValue = '<?php echo addslashes($_GET['search'] ?? ''); ?>';
        if (initialSearchValue) {
            // Aplica el filtro global del DataTables con el valor de la búsqueda general del GET
            infractionsTable.search(initialSearchValue).draw();
        }
    } else {
        console.error("DataTables no está cargado.");
    }
});
</script>