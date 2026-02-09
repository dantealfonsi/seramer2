<?php
require_once __DIR__ . '/../../controllers/DailyCashController.php';

$controller = new DailyCashController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->storeClose($_POST);
    if ($result['success']) {
        header('Location: ' . $result['redirect']);
        exit;
    }
    $error = $result['message'];
}

$id = $_GET['id'] ?? 0;
// Note: In controller `closeForm` expects dailyCashId (the logic table ID), but link might be passing something else?
// In index.php link was: `close.php?id=<?php echo $register['open_cash']['id']; ?>`. Correct.
$data = $controller->closeForm($id);

if (!$data['success']) {
    header('Location: ' . ($data['redirect'] ?? 'index.php'));
    exit;
}

$page_title = $data['page_title'];
$dailyCash = $data['dailyCash'];
$totalInstallments = $data['totalInstallments'];
$calculatedFinal = $data['calculatedFinal'];
$installments = $data['installments'];

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Resumen de Movimientos</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($installments)): ?>
                            <div class="alert alert-info">No se registraron movimientos en este turno.</div>
                        <?php else: ?>
                            <table class="table table-sm table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Hora</th>
                                        <th>Ref</th>
                                        <th>Concepto</th>
                                        <th>Método</th>
                                        <th>Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($installments as $ins): ?>
                                        <tr>
                                            <td><?php echo date('H:i', strtotime($ins['created_at'] ?? $ins['date'])); ?></td>
                                            <td><?php echo $ins['payment_reference']; ?></td>
                                            <td><?php echo $ins['concept']; ?></td>
                                            <td><?php echo $ins['payment_method_name']; ?></td>
                                            <td><?php echo number_format($ins['amount'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo htmlspecialchars($page_title); ?></h5>
                    </div>
                    <div class="card-body">
                         <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="daily_cash_id" value="<?php echo $dailyCash['id']; ?>">
                            
                            <div class="mb-3">
                                <label class="label">Monto Inicial:</label>
                                <input type="text" class="form-control" value="<?php echo number_format($dailyCash['initial_amount'], 2); ?>" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label class="label">Total Recaudado:</label>
                                <input type="text" class="form-control" value="<?php echo number_format($totalInstallments, 2); ?>" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label class="label">Total Esperado:</label>
                                <input type="text" class="form-control font-weight-bold" value="<?php echo number_format($calculatedFinal, 2); ?>" readonly>
                            </div>
                            
                            <hr>
                            
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Monto Final en Caja (Cierre)</label>
                                <input type="number" step="0.01" name="final_amount" class="form-control form-control-lg" required value="<?php echo $calculatedFinal; ?>">
                                <small class="text-muted">Confirme el monto físico contado en caja.</small>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-danger">Cerrar Turno</button>
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
