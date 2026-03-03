<?php
// Vista de listado de departamentos
session_start();

require_once __DIR__ . '/../../controllers/DepartmentController.php';
require_once __DIR__ . '/../../controllers/RolesController.php';

$controller = new DepartmentController();
$rol = new RolesController();

// Si no vienen datos del controlador (acceso directo), redireccionar a través del controlador
// Aunque en este proyecto las vistas parecen ser puntos de entrada que instancian controladores.
$all_departments_raw = (new DepartmentModel())->getAllWithManager();

// Filtro simple de búsqueda por nombre
$search_name = trim($_GET['name'] ?? '');
if ($search_name !== '') {
    $departments = array_filter($all_departments_raw, function($d) use ($search_name) {
        return stripos($d['name'], $search_name) !== false;
    });
} else {
    $departments = $all_departments_raw;
}

$page_title = 'Gestión de Departamentos';

require_once __DIR__ . '/../../views/layouts/header.php';
include __DIR__ . '/../../views/layouts/navigation.php';
include __DIR__ . '/../../views/layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="card-title d-flex align-items-center mb-0" style="font-size: 1.4rem;font-weight: 600;">
                                <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="ri-building-line" style="color: #696cff; font-size: 1.5rem;"></i></div>
                                <?php echo htmlspecialchars($page_title); ?>
                            </h5>
                        </div>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri-add-line"></i> Nuevo Departamento
                        </a>
                    </div>

                    <?php if (isset($_SESSION['flash_message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['flash_message']['type'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show mx-3 mt-3" role="alert">
                        <?php echo $_SESSION['flash_message']['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['flash_message']); ?>
                    <?php endif; ?>

                    <div class="card-body">
                        <!-- Filtro por Nombre -->
                        <div class="filter-card">
                            <div class="filter-card-title">
                                <i class="ri-filter-2-line"></i> Opciones de Filtrado Avanzado
                            </div>
                            <div class="filter-card-body">
                                <form method="GET" action="index.php">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label small fw-bold text-uppercase">Nombre del Departamento</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="ri-building-line text-muted"></i></span>
                                                <input type="text" name="name" class="form-control" placeholder="Buscar por nombre..." value="<?php echo htmlspecialchars($_GET['name'] ?? ''); ?>">
                                            </div>
                                        </div>
                                        <div class="col-12 filter-card-actions">
                                            <a href="index.php" class="btn btn-filter-clear"><i class="ri-refresh-line me-1"></i> Limpiar</a>
                                            <button type="submit" class="btn btn-filter-apply"><i class="ri-search-line me-1"></i> Filtrar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <?php if (empty($departments)): ?>
                            <div class="text-center py-4">
                                <i class="ri-building-line text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-2">No hay departamentos registrados</h5>
                                <p class="text-muted">Comienza creando el primer departamento.</p>
                                <a href="create.php" class="btn btn-primary">
                                    <i class="ri-add-line"></i> Crear Primer Departamento
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle" id="departmentsTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Manager (Jefe)</th>
                                            <th>Turno</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($departments as $dept): ?>
                                        <tr>
                                            <td><?php echo $dept['id']; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-3">
                                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                                            <i class="ri-building-line"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 text-truncate"><?php echo htmlspecialchars($dept['name']); ?></h6>
                                                        <small class="text-muted"><?php echo htmlspecialchars($dept['description'] ?? ''); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (!empty($dept['first_name'])): ?>
                                                    <div class="d-flex align-items-center">
                                                        <i class="ri-user-star-line me-2 text-primary"></i>
                                                        <div>
                                                            <span class="fw-medium"><?php echo htmlspecialchars($dept['first_name'] . ' ' . $dept['last_name']); ?></span>
                                                            <br>
                                                            <small class="text-muted">ID: <?php echo htmlspecialchars($dept['id_number'] ?? ''); ?></small>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="badge bg-label-secondary">Sin Asignar</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $shift = $dept['shift_type'] ?? 'Day';
                                                $shift_map = ['Day' => 'Diurno', 'Night' => 'Nocturno', 'Mixed' => 'Mixto'];
                                                $shift_color = ['Day' => 'info', 'Night' => 'primary', 'Mixed' => 'warning'];
                                                ?>
                                                <span class="badge bg-<?php echo $shift_color[$shift] ?? 'secondary'; ?>">
                                                    <?php echo $shift_map[$shift] ?? $shift; ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="edit.php?id=<?php echo $dept['id']; ?>" class="btn btn-sm btn-outline-warning" title="Editar">
                                                    <i class="ri-edit-line"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        title="Eliminar"
                                                        onclick="confirmDelete(<?php echo $dept['id']; ?>, '<?php echo addslashes($dept['name']); ?>')">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
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

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar el departamento <strong id="deleteDeptName"></strong>?</p>
                <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="delete.php" method="POST" id="deleteForm">
                    <input type="hidden" name="id" id="deleteDeptId">
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
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
function confirmDelete(id, name) {
    document.getElementById('deleteDeptId').value = id;
    document.getElementById('deleteDeptName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

$(document).ready(function() {
    if ($.fn.DataTable) {
        $('#departmentsTable').DataTable({
            responsive: true,
            dom: '<"d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3"Bf>rtip',
            buttons: [
                {
                    extend: 'pdfHtml5',
                    text: '<i class="ri-file-pdf-line"></i> PDF',
                    className: 'btn btn-danger btn-sm me-1',
                    exportOptions: { columns: [0, 1, 2, 3] },
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
                                        { text: 'REPÚBLICA BOLIVARIANA DE VENEZUELA\\n', fontSize: 10, bold: true },
                                        { text: 'GOBIERNO BOLIVARIANA DE VENEZUELA\\n', fontSize: 10, bold: true },
                                        { text: 'SERVICIO AUTÓNOMO DE MERCADO MUNICIPAL DE BERMÚDEZ\\n', fontSize: 10, bold: true },
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
                            text: 'Listado de Departamentos',
                            style: 'header',
                            alignment: 'center',
                            margin: [0, 0, 0, 15]
                        });

                        // 5. Estilo de tabla
                        doc.styles.header = { fontSize: 14, bold: true };
                        const table = doc.content.find(content => content.table);
                        if (table && table.table.body.length > 0) {
                            const headerRow = table.table.body[0];
                            headerRow.forEach(cell => {
                                cell.fillColor = '#2d4154';
                                cell.color = '#ffffff';
                                cell.bold = true;
                            });
                            
                            // Zebra striping
                            for (let i = 1; i < table.table.body.length; i++) {
                                if (i % 2 === 0) {
                                    table.table.body[i].forEach(cell => {
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
                    text: '<i class="ri-file-excel-line"></i> Excel',
                    className: 'btn btn-success btn-sm me-1',
                    exportOptions: { columns: [0, 1, 2, 3] }
                },
                {
                    extend: 'print',
                    text: '<i class="ri-printer-line"></i> Imprimir',
                    className: 'btn btn-info btn-sm',
                    exportOptions: { columns: [0, 1, 2, 3] }
                },
                'colvis'
            ],
            language: {
                "url": "../../public/datatables/Spanish.json" // Pruébamos si tienen el json o usamos el manual
            },
            // Fallback si el json no carga
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
                }
            },
            columnDefs: [
                { visible: false, targets: 0 },
                { orderable: false, targets: 4 }
            ]
        });
    }
});
</script>

