<?php
require_once __DIR__ . '/../../controllers/RolesController.php';

$rolesController = new RolesController();

$result = $rolesController->index();

if (!$result['success'] && isset($result['redirect'])) {
    header('Location: ' . $result['redirect']);
    exit;
}

$roles = $result['roles'];
$all_departments = $result['all_departments'] ?? [];
$dept_filter = $result['dept_filter'] ?? '';
$is_superadmin = $result['is_superadmin'];
$manager_info = $result['manager_info'];
$page_title = $result['page_title'];

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
                        <h5 class="card-title mb-0 d-flex align-items-center" style="font-size: 2rem;font-weight: 600;">
                            <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; background-color: #e7e7ff !important;">
                                <i class="ri-shield-keyhole-line" style="color: #696cff; font-size: 2rem;"></i>
                            </div>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <div class="card-tools">
                            <?php if ($is_superadmin): ?>
                                <a href="create.php" class="btn btn-primary btn-sm">
                                    <i class="ri-add-line mr-1"></i> Nuevo Rol
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Mensajes de estado -->
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success alert-dismissible" role="alert">
                                <?php
                                switch ($_GET['success']) {
                                    case 'role_created':
                                        echo 'Rol creado exitosamente';
                                        break;
                                    case 'role_updated':
                                        echo 'Rol actualizado exitosamente';
                                        break;
                                    case 'role_deleted':
                                        echo 'Rol eliminado exitosamente';
                                        break;
                                    default:
                                        echo 'Operación realizada exitosamente';
                                }
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <?php
                                switch ($_GET['error']) {
                                    case 'role_not_found':
                                        echo 'Rol no encontrado';
                                        break;
                                    case 'no_permission':
                                        echo 'No tiene permisos para realizar esta acción';
                                        break;
                                    case 'cannot_delete_admin':
                                        echo 'No se puede eliminar el rol de administrador';
                                        break;
                                    case 'cannot_modify_admin':
                                        echo 'No se pueden modificar las propiedades base del rol de administrador';
                                        break;
                                    default:
                                        echo htmlspecialchars($_GET['error']);
                                }
                                ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>


                        <?php if ($is_superadmin): ?>
                        <div class="filter-card">
                            <div class="filter-card-title">
                                <i class="ri-filter-2-line"></i> Opciones de Filtrado Avanzado
                            </div>
                            <div class="filter-card-body">
                                <form action="index.php" method="GET">
                                    <div class="row g-3">
                                        <div class="col-md-9">
                                            <label for="department" class="form-label small">Filtrar por Departamento</label>
                                            <select class="form-select" id="department" name="department">
                                                <option value="">-- Todos los Departamentos --</option>
                                                <?php foreach ($all_departments as $dept): ?>
                                                    <option value="<?php echo htmlspecialchars($dept['id']); ?>" 
                                                            <?php echo ($dept_filter == $dept['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($dept['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
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
                        <?php endif; ?>

                        <!-- Información Útil -->
                        <div class="alert alert-info d-flex align-items-center mx-3 mb-4 border-0 shadow-sm" role="alert">
                            <i class="ri-information-line me-3 fs-3 text-info"></i>
                            <div>
                                <h6 class="alert-heading mb-1 fw-bold text-info">Información de Gestión de Roles</h6>
                                <p class="mb-0 small">Los roles definen el nivel de acceso (Lectura, Escritura, Modificación, Eliminación) de los usuarios en sus respectivos departamentos. 
                                Recuerde que los roles de tipo <strong>Administrador</strong> tienen restricciones de seguridad y solo pueden ser modificados por personal autorizado. 
                                <strong>Si tiene problemas con la asignación, contacte con el director del área.</strong></p>
                            </div>
                        </div>

                        <!-- Tabla de roles -->
                        <?php if (empty($roles)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-shield-alt fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No hay roles configurados</h5>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="rolesTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Nombre del Rol</th>
                                            <?php if ($is_superadmin): ?>
                                            <th>Departamento</th>
                                            <?php endif; ?>
                                            <th>Descripción</th>
                                            <th class="text-center">Permisos (Nivel de Confianza)</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($roles as $role): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($role['name']); ?></strong>
                                                    <?php if ($role['name'] === 'admin'): ?>
                                                        <span class="badge bg-danger ms-2">Admin Sistema</span>
                                                    <?php endif; ?>
                                                </td>
                                                <?php if ($is_superadmin): ?>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo htmlspecialchars($role['department_name'] ?? 'Global?'); ?>
                                                    </span>
                                                </td>
                                                <?php endif; ?>
                                                <td>
                                                    <?php echo htmlspecialchars($role['description']); ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($role['can_read']): ?>
                                                        <span class="badge bg-secondary" title="Lectura">R</span>
                                                    <?php endif; ?>
                                                    <?php if ($role['can_write']): ?>
                                                        <span class="badge bg-success" title="Escritura">W</span>
                                                    <?php endif; ?>
                                                    <?php if ($role['can_modify']): ?>
                                                        <span class="badge bg-warning text-dark" title="Modificación">M</span>
                                                    <?php endif; ?>
                                                    <?php if ($role['can_delete']): ?>
                                                        <span class="badge bg-danger" title="Eliminación">D</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                     <a href="<?php echo ($role['name'] !== 'admin' || $is_superadmin) ? 'edit.php?id=' . $role['id'] : 'javascript:void(0);'; ?>" 
                                                        class="btn btn-sm btn-outline-warning <?php echo ($role['name'] === 'admin' && !$is_superadmin) ? 'disabled' : ''; ?>" 
                                                        title="<?php echo ($role['name'] === 'admin' && !$is_superadmin) ? 'No Editable' : 'Editar'; ?>"
                                                        <?php echo ($role['name'] === 'admin' && !$is_superadmin) ? 'aria-disabled="true"' : ''; ?>>
                                                         <i class="ri-edit-line"></i>
                                                     </a>
                                                     
                                                     <?php if ($is_superadmin && $role['name'] !== 'admin'): ?>
                                                         <a href="delete.php?id=<?php echo $role['id']; ?>" 
                                                            class="btn btn-sm btn-outline-danger" title="Eliminar"
                                                            onclick="return confirm('¿Está seguro de eliminar este rol? Los usuarios asignados a este rol perderán sus permisos.')">
                                                             <i class="ri-delete-bin-line"></i>
                                                         </a>
                                                     <?php endif; ?>
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

<!-- DataTables Scripts -->
<script type="text/javascript" src="../../public/datatables/datatables.min.js"></script>
<script type="text/javascript" src="../../public/datatables/pdfmake.min.js"></script>
<script type="text/javascript" src="../../public/datatables/vfs_fonts.js"></script>
<link rel="stylesheet" type="text/css" href="../../public/datatables/datatables.min.css"/> 

<script>
$(document).ready(function() {
    if ($.fn.DataTable) {
        $('#rolesTable').DataTable({
            responsive: true,
            dom: '<"d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3"Bf>rtip',
            buttons: [
                {
                    extend: 'pdfHtml5',
                    text: '<i class="ri-file-pdf-line"></i> PDF',
                    className: 'btn btn-danger btn-sm me-1',
                    exportOptions: {
                        columns: ':visible:not(:last-child)'
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="ri-file-excel-line"></i> Excel',
                    className: 'btn btn-success btn-sm me-1',
                    exportOptions: {
                        columns: ':visible:not(:last-child)'
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="ri-printer-line"></i> Imprimir',
                    className: 'btn btn-info btn-sm',
                    exportOptions: {
                        columns: ':visible:not(:last-child)' // Exclude last column (Actions)
                    }
                }
            ],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            pageLength: 10,
            order: []
        });
    } else {
        console.error("DataTables no cargado");
    }
});
</script>
