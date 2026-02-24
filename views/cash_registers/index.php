<?php
require_once __DIR__ . '/../../controllers/CashRegisterController.php';

$controller = new CashRegisterController();

// Prepare filters
$filters = [
    'name' => $_GET['name'] ?? '',
    'user_id' => $_GET['user_id'] ?? '',
    'status' => $_GET['status'] ?? '',
];

$data = $controller->index(['filters' => $filters]);
$cashRegisters = $data['cashRegisters'];
$users = $data['users'];
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
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 d-flex align-items-center" style="font-size: 2rem;font-weight: 600;">
                            <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; background-color: #e7e7ff !important;">
                                <i class="ri-file-list-3-line" style="color: #696cff; font-size: 2rem;"></i>
                            </div>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <div class="card-tools">
                            <a href="create.php" class="btn btn-primary">
                                <i class="ri-add-line me-1"></i> Nueva Caja
                            </a>
                        </div>
                    </div>
                    
                    <!-- Filters -->
                    <div class="card-body border-bottom">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small">Nombre de Caja</label>
                                <input type="text" name="name" class="form-control" placeholder="Buscar por nombre..." value="<?php echo htmlspecialchars($filters['name']); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Usuario Asignado</label>
                                <select name="user_id" class="form-select">
                                    <option value="">-- Todos los Usuarios --</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?php echo $user['id']; ?>" <?php echo (string)$filters['user_id'] === (string)$user['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['username'] . ' (' . ($user['staff_first_name'] ?? 'Sin nombre') . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Estatus</label>
                                <select name="status" class="form-select">
                                    <option value="">-- Todos --</option>
                                    <option value="active" <?php echo $filters['status'] === 'active' ? 'selected' : ''; ?>>Activa</option>
                                    <option value="inactive" <?php echo $filters['status'] === 'inactive' ? 'selected' : ''; ?>>Inactiva</option>
                                </select>
                            </div>
                            <div class="col-12 d-flex justify-content-end gap-2">
                                <button type="submit" class="btn btn-info btn-sm text-white" style="background-color: #0dcaf0; border-color: #0dcaf0;">
                                    <i class="ri-search-line me-1"></i> Filtrar 
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="window.location.href='index.php'">
                                    <i class="ri-refresh-line"></i> Limpiar
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="card-body">
                        <?php if (isset($_SESSION['flash_message'])): 
                            $msg = $_SESSION['flash_message'];
                            unset($_SESSION['flash_message']);
                        ?>
                            <div class="alert alert-<?php echo $msg['type']; ?> alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($msg['message']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover w-100" id="cashRegistersTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Usuario Asignado</th>
                                        <th>Email</th>
                                        <th>Estatus</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cashRegisters as $cr): ?>
                                        <tr>
                                            <td><?php echo $cr['id']; ?></td>
                                            <td><strong><?php echo htmlspecialchars($cr['name']); ?></strong></td>
                                            <td>
                                                <i class="ri-user-line text-muted me-1"></i>
                                                <?php echo htmlspecialchars($cr['assigned_user_name'] ?? 'N/A'); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($cr['email'] ?? '-'); ?></td>
                                            <td>
                                                <?php if ($cr['status'] === 'active'): ?>
                                                    <span class="badge bg-success">Activa</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inactiva</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                 <div class="dropdown">
                                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                        <i class="ri-more-2-fill"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="edit.php?id=<?php echo $cr['id']; ?>">
                                                            <i class="ri-pencil-line me-2 text-warning"></i> Editar
                                                        </a>
                                                    </div>
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

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<!-- DataTables Scripts -->
<script type="text/javascript" src="../../public/datatables/datatables.min.js"></script>
<script type="text/javascript" src="../../public/datatables/pdfmake.min.js"></script>
<script type="text/javascript" src="../../public/datatables/vfs_fonts.js"></script>
<link rel="stylesheet" type="text/css" href="../../public/datatables/datatables.min.css"/> 
<link rel="stylesheet" type="text/css" href="../../public/datatables/buttons.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="../../public/assets/css/dani-styles.css"/>

<script>
$(document).ready(function() {
    $('#cashRegistersTable').DataTable({
        responsive: true,
        dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="ri-file-excel-line"></i> Excel',
                className: 'btn btn-success btn-sm me-1'
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="ri-file-pdf-line"></i> PDF',
                className: 'btn btn-danger btn-sm me-1',
                orientation: 'portrait',
                pageSize: 'LETTER'
            },
            {
                extend: 'print',
                text: '<i class="ri-printer-line"></i> Imprimir',
                className: 'btn btn-info btn-sm me-1'
            }
        ],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
        },
        order: [[0, 'desc']]
    });
});
</script>
