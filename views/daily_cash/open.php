<?php
require_once __DIR__ . '/../../controllers/DailyCashController.php';

$controller = new DailyCashController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->storeOpen($_POST);
    if ($result['success']) {
        header('Location: ' . $result['redirect']);
        exit;
    }
    $error = $result['message'];
}

$id = $_GET['id'] ?? 0;
$data = $controller->openForm($id);

if (!$data['success']) {
    header('Location: ' . ($data['redirect'] ?? 'index.php'));
    exit;
}

$page_title = $data['page_title'];
$cashRegister = $data['cashRegister'];

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row justify-content-center">
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
                            <input type="hidden" name="cash_register_id" value="<?php echo $cashRegister['id']; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Monto Inicial (Bs)</label>
                                <input type="number" step="0.01" name="initial_amount" class="form-control" required value="0.00">
                                <small class="text-muted">Ingrese el monto de dinero base en caja al iniciar el turno.</small>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success">Confirmar Apertura</button>
                                <a href="index.php" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
