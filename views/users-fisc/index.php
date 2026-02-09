<?php
// views/fiscalization_users/index.php

session_start();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/UsersFiscController.php';
require_once __DIR__ . '/../../controllers/RolesController.php';

$usersController = new UsersFiscController();
$rol = new RolesController();
$result = $usersController->indexFiscalizationUsers();

// Extraer variables para la vista
$users = $result['users'];
$roles = $result['roles']; // ⬅️ Nuevo: roles disponibles
$page_title = $result['page_title'];

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
                    <div class="card-header">
                        <h5 class="card-title" style="font-size: 2rem;font-weight: 600;">
                            <i class="ri-team-line me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                    </div>
                    
                    <div class="card-body">
                        <div class="alert alert-info mb-4 shadow-sm border-start border-info border-5">
                            <i class="ri-information-line"></i>
                            <div class="alert-content">
                                <h6 class="alert-heading fw-bold">Gestión de Usuarios de Fiscalización</h6>
                                <p class="mb-0">
                                    Este componente permite visualizar y administrar el personal asignado al departamento de Fiscalización. 
                                    Aquí puede gestionar los <strong>Niveles de Acceso</strong> (Administrador, Oficina, Inspector) para cada usuario, 
                                    lo que determina sus capacidades dentro del sistema, desde la generación de reportes hasta la auditoría de procesos.
                                </p>
                            </div>
                        </div>
                        <?php if (empty($users)): ?>
                            <div class="text-center py-5">
                                <i class="ri-alert-line text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-2">No hay usuarios activos registrados para Fiscalización.</h5>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="usersFiscTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Nombre Completo</th>
                                            <th>Cédula</th>
                                            <th>Usuario (Login)</th>
                                            <th>Email</th>
                                            <th>Estado</th>
                                            <?php if ($rol->hasPermission('USERS_AUDIT', 'w')): ?>
                                            <th class="text-center" style="min-width: 300px;">Nivel de Fiscalización</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($rol->hasPermission('USERS_AUDIT', 'r')): ?>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($user['last_name'] . ' ' . $user['first_name']); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['id_number']); ?></td>
                                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <?php 
                                                        $status = $user['user_status'];
                                                        $badge_color = ($status === 'active') ? 'success' : 'danger';
                                                    ?>
                                                    <span class="badge bg-<?php echo $badge_color; ?>">
                                                        <?php echo ($status === 'active') ? 'Activo' : 'Inactivo'; ?>
                                                    </span>
                                                </td>
                                                <?php if ($rol->hasPermission('USERS_AUDIT', 'w')): ?>
                                                <td class="text-center">
                                                    <?php foreach ($roles as $role): ?>
                                                        <?php if (strtolower($role['role_name']) === 'cobranzas') continue; ?>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input role-radio" 
                                                                type="radio" 
                                                                name="role_<?php echo htmlspecialchars($user['user_id']); ?>" 
                                                                id="role_<?php echo htmlspecialchars($user['user_id'] . '_' . $role['role_id']); ?>" 
                                                                value="<?php echo htmlspecialchars($role['role_id']); ?>"
                                                                data-user-id="<?php echo htmlspecialchars($user['user_id']); ?>"
                                                                data-role-name="<?php echo htmlspecialchars($role['role_name']); ?>"
                                                                data-is-admin-user="<?php echo ($user['role_name'] === 'administrador') ? '1' : '0'; ?>"
                                                                data-is-self="<?php echo ($user['user_id'] == $_SESSION['user_id']) ? '1' : '0'; ?>"
                                                                <?php echo ($user['role_id'] == $role['role_id']) ? 'checked' : ''; ?>
                                                                <?php echo ($user['role_id'] == null && $role['role_name'] == 'oficina') ? 'checked' : ''; ?> 
                                                                >
                                                            <label class="form-check-label" for="role_<?php echo htmlspecialchars($user['user_id'] . '_' . $role['role_id']); ?>">
                                                                <?php echo htmlspecialchars(ucfirst($role['role_name'])); ?>
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </td> 
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // DataTables Initialization
    if ($.fn.DataTable) {
         $('#usersFiscTable').DataTable({ 
            responsive: true,
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'pdfHtml5',
                    text: '<i class="ri-file-pdf-line"></i> PDF',
                    className: 'btn btn-danger btn-sm me-1',
                    orientation: 'landscape', 
                    pageSize: 'LETTER', 
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4] // Exclude Role Selection Column (index 5 now)
                    },
                    title: 'Usuarios_Fiscalizacion_Seramer',
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
                            canvas: [{ type: 'line', x1: 0, y1: 5, x2: 750, y2: 5, lineWidth: 1, lineColor: '#000000' }], // Adjusted x2 for landscape
                            margin: [0, 0, 0, 20]
                        });

                        // 4. Agregar Título Centrado
                        doc.content.splice(2, 0, {
                            text: 'Listado de Usuarios de Fiscalización',
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
                         columns: [0, 1, 2, 3, 4] 
                    },
                    title: 'Usuarios_Fiscalizacion_Seramer' 
                },
                {
                    extend: 'print',
                    text: '<i class="ri-printer-line"></i> Imprimir',
                    className: 'btn btn-info btn-sm',
                    exportOptions: {
                         columns: [0, 1, 2, 3, 4] 
                    },
                    messageTop: `
                        <div style="text-align: center; margin-bottom: 20px;">
                            <h1 style="margin: 0; font-size: 1.5em; text-align: center;">Servicio Autonómo de Mercados de Bermúdez</h1>
                            <h2 style="margin: 0; font-size: 1.2em; text-align: center;">Listado de Usuarios de Fiscalización</h2>
                        </div>`,
                    customize: function (win) {
                        $(win.document.body).find('table').addClass('w-100').css('width', '100%');
                        $(win.document.body).find('head').append(
                            '<style>@media print { @page { size: letter; margin: 1cm; } } table thead th { background-color: #343a40 !important; color: white !important; -webkit-print-color-adjust: exact; text-align: left !important;}</style>'
                        );
                    }
                },
                'colvis'
            ],
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
            "columnDefs": [
                { "orderable": false, "targets": 5 } // Disable sorting on Role Selection
            ]
        });
    }

    // Role Update Logic
    const radios = document.querySelectorAll('.role-radio');
    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            const radioElement = this;
            const userId = this.getAttribute('data-user-id');
            const roleId = this.value;
            const roleName = this.getAttribute('data-role-name');
            const isAdmin = this.getAttribute('data-is-admin-user') === '1';
            const isSelf = this.getAttribute('data-is-self') === '1';

            // Verificación: No bajar rango a un administrador
            if (isAdmin && roleName.toLowerCase() !== 'administrador') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Acción restringida',
                    text: 'No se puede bajar el rango a un administrador por seguridad. Esta acción debe ser supervisada.',
                    confirmButtonText: 'Entendido'
                });
                // Revertir el radio button sin recargar la página
                setTimeout(() => { location.reload(); }, 500); // Recargamos para estar seguros del estado actual
                return;
            }

            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas asignar el rol '${roleName.toUpperCase()}' al usuario?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, cambiar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('ajax_update_role.php', { 
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=update_role&user_id=${userId}&role_id=${roleId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Actualizado!',
                                text: '✅ ' + data.message,
                                confirmButtonText: 'Genial'
                            }).then(() => {
                                location.reload(); // Recargar para actualizar los data-attributes y el estado real
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: '❌ Error: ' + data.message,
                                confirmButtonText: 'Entendido'
                            }).then(() => {
                                location.reload();
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error en la conexión AJAX:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de Conexión',
                            text: '❌ Error de conexión al servidor.',
                            confirmButtonText: 'Entendido'
                        }).then(() => {
                            location.reload();
                        });
                    });
                } else {
                    // Si cancela, volvemos a cargar para resetear los radio buttons al estado real de la DB
                    location.reload();
                }
            });
        });
    });
});
</script>


<?php include __DIR__ . '/../layouts/footer.php'; ?>

<!-- DataTables includes -->
<script type="text/javascript" src="../../public/assets/js/pdf_logo.js"></script>
<script type="text/javascript" src="../../public/datatables/datatables.min.js"></script>
<script type="text/javascript" src="../../public/datatables/pdfmake.min.js"></script>
<script type="text/javascript" src="../../public/datatables/vfs_fonts.js"></script>
<link rel="stylesheet" type="text/css" href="../../public/datatables/datatables.min.css"/> 
<link rel="stylesheet" type="text/css" href="../../public/datatables/buttons.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="../../public/assets/css/dani-styles.css"/>