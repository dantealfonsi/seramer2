<?php
require_once __DIR__ . '/../../controllers/CashRegisterController.php';

$controller = new CashRegisterController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->store($_POST);
    if ($result['success']) {
        header('Location: ' . $result['redirect']);
        exit;
    }
    $error = $result['message'];
}

$data = $controller->create();
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
            
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom py-3">
                        <h4 class="card-title mb-1 d-flex align-items-center">
                            <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;">
                                <i class="ri-add-circle-line" style="color: #696cff; font-size: 1.5rem;"></i>
                            </div>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="index.php">Gestión de Cajas</a></li>
                                <li class="breadcrumb-item active">Nueva</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="ri-error-warning-line me-2"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Nombre de Caja</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ri-archive-drawer-line"></i></span>
                                    <input type="text" name="name" class="form-control" required placeholder="Ej: Caja Principal 1">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Usuario Asignado</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ri-user-line"></i></span>
                                    <select name="user_id" class="form-control" required>
                                        <option value="">Seleccione Usuario...</option>
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?php echo $user['id']; ?>">
                                                <?php echo htmlspecialchars($user['username'] . ' (' . ($user['staff_first_name'] ?? 'Sin nombre') . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <small class="text-muted">El usuario solo puede tener una caja activa asignada.</small>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Guardar Caja</button>
                                <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
