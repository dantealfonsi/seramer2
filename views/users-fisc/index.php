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
                                            <th>ID de Usuario</th>
                                            <th>Nombre Completo</th>
                                            <th>Cédula</th>
                                            <th>Usuario (Login)</th>
                                            <th>Email</th>
                                            <th>Estado</th>
                                            <th class="text-center" style="min-width: 300px;">Nivel de Fiscalización</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <?php if ($rol->hasPermission('USERS_AUDIT', 'r')): ?>
                                                <td><?php echo htmlspecialchars($user['user_id']); ?></td>
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
                                                        <?php echo htmlspecialchars(ucfirst($status)); ?>
                                                    </span>
                                                </td>
                                                <?php if ($rol->hasPermission('USERS_AUDIT', 'w')): ?>
                                                <td class="text-center">
                                                    <?php foreach ($roles as $role): ?>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input role-radio" 
                                                                type="radio" 
                                                                name="role_<?php echo htmlspecialchars($user['user_id']); ?>" 
                                                                id="role_<?php echo htmlspecialchars($user['user_id'] . '_' . $role['role_id']); ?>" 
                                                                value="<?php echo htmlspecialchars($role['role_id']); ?>"
                                                                data-user-id="<?php echo htmlspecialchars($user['user_id']); ?>"
                                                                data-role-name="<?php echo htmlspecialchars($role['role_name']); ?>"
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
                                                <?php endif; ?> 
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
                        columns: [0, 1, 2, 3, 4, 5] // Exclude Role Selection Column (index 6)
                    },
                    title: 'Usuarios_Fiscalizacion_Seramer'
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="ri-file-excel-line"></i> Excel',
                    className: 'btn btn-success btn-sm me-1',
                    exportOptions: {
                         columns: [0, 1, 2, 3, 4, 5] 
                    },
                    title: 'Usuarios_Fiscalizacion_Seramer' 
                },
                {
                    extend: 'print',
                    text: '<i class="ri-printer-line"></i> Imprimir',
                    className: 'btn btn-info btn-sm',
                    exportOptions: {
                         columns: [0, 1, 2, 3, 4, 5] 
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
                { "orderable": false, "targets": 6 } // Disable sorting on Role Selection
            ]
        });
    }

    // Role Update Logic
    const radios = document.querySelectorAll('.role-radio');
    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            const userId = this.getAttribute('data-user-id');
            const roleId = this.value;
            const roleName = this.getAttribute('data-role-name');

            if (!confirm(`¿Estás seguro de que quieres asignar el rol '${roleName.toUpperCase()}' al usuario ID ${userId}?`)) {
                location.reload(); 
                return;
            }

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
                        title: 'Éxito',
                        text: '✅ ' + data.message,
                        confirmButtonText: 'Aceptar'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: '❌ Error: ' + data.message,
                        confirmButtonText: 'Entendido'
                    });
                    location.reload(); 
                }
            })
            .catch(error => {
                console.error('Error en la conexión AJAX:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: '❌ Error de conexión al servidor.',
                    confirmButtonText: 'Entendido'
                });
                location.reload();
            });
        });
    });
});
</script>


<?php include __DIR__ . '/../layouts/footer.php'; ?>

<!-- DataTables includes -->
<script type="text/javascript" src="../../public/datatables/datatables.min.js"></script>
<script type="text/javascript" src="../../public/datatables/pdfmake.min.js"></script>
<script type="text/javascript" src="../../public/datatables/vfs_fonts.js"></script>
<link rel="stylesheet" type="text/css" href="../../public/datatables/datatables.min.css"/> 
<link rel="stylesheet" type="text/css" href="../../public/datatables/buttons.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="../../public/assets/css/dani-styles.css"/>