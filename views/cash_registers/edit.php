<?php
require_once __DIR__ . '/../../controllers/CashRegisterController.php';

$controller = new CashRegisterController();
$id = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->update($id, $_POST);
    if ($result['success']) {
        header('Location: ' . $result['redirect']);
        exit;
    }
    $error = $result['message'];
}

$data = $controller->edit($id);
if (!$data['success']) {
    header('Location: index.php');
    exit;
}

$cashRegister = $data['cashRegister'];
$users = $data['users'];
$page_title = $data['page_title'];

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
             <div class="col-md-6">
                 <div class="card">
                     <div class="card-header">
                         <h5 class="card-title"><?php echo htmlspecialchars($page_title); ?></h5>
                     </div>
                     <div class="card-body">
                         <?php if (isset($error)): ?>
                             <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                         <?php endif; ?>
                         
                         <form method="POST" action="">
                             <div class="mb-3">
                                 <label class="form-label">Nombre de Caja</label>
                                 <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($cashRegister['name']); ?>">
                             </div>
                             
                             <div class="mb-3">
                                 <label class="form-label">Usuario Asignado</label>
                                 <select name="user_id" class="form-control" required>
                                     <option value="">Seleccione Usuario...</option>
                                     <?php foreach ($users as $user): ?>
                                         <option value="<?php echo $user['id']; ?>" <?php echo ($user['id'] == $cashRegister['user_id'] ? 'selected' : ''); ?>>
                                             <?php echo htmlspecialchars($user['username'] . ' (' . ($user['staff_first_name'] ?? 'Sin nombre') . ')'); ?>
                                         </option>
                                     <?php endforeach; ?>
                                 </select>
                             </div>
                             
                             <div class="mb-3">
                                 <label class="form-label">Estado</label>
                                 <select name="status" class="form-control">
                                     <option value="active" <?php echo ($cashRegister['status'] == 'active' ? 'selected' : ''); ?>>Activo</option>
                                     <option value="inactive" <?php echo ($cashRegister['status'] == 'inactive' ? 'selected' : ''); ?>>Inactivo</option>
                                 </select>
                             </div>
                             
                             <button type="submit" class="btn btn-primary">Actualizar</button>
                             <a href="index.php" class="btn btn-secondary">Cancelar</a>
                         </form>
                     </div>
                 </div>
             </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
