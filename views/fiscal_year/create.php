<?php
require_once __DIR__ . '/../../controllers/FiscalYearController.php';

$controller = new FiscalYearController();
$data = $controller->create();
$page_title = $data['page_title'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->store($_POST);
    if ($result['success']) {
        header('Location: index.php');
        exit;
    } else {
        $error = $result['message'];
    }
}

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content" style="padding: 1.5rem;">
    <div class="container-xxl">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom py-3">
                        <h4 class="card-title mb-1 d-flex align-items-center">
                            <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;">
                                <i class="ri-calendar-add-line" style="color: #696cff; font-size: 1.5rem;"></i>
                            </div>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="index.php">Año Fiscal</a></li>
                                <li class="breadcrumb-item active">Nuevo</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="card-body p-4">
                         <?php if (isset($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Año Fiscal</label>
                                    <input type="number" name="year" class="form-control" value="<?php echo date('Y') + 1; ?>" required>
                                    <div class="form-text text-muted small">El año que representará este período fiscal.</div>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Fecha de Inicio</label>
                                    <input type="date" name="start_date" class="form-control" value="<?php echo (date('Y') + 1) . '-01-01'; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Fecha de Fin</label>
                                    <input type="date" name="end_date" class="form-control" value="<?php echo (date('Y') + 1) . '-12-31'; ?>" required>
                                </div>
                            </div>

                            <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                                <i class="ri-error-warning-line ri-24px me-3"></i>
                                <div>
                                    <strong>Nota:</strong> Al crear un nuevo año fiscal, se volverá automáticamente el año <strong>activo</strong> y se generarán las cuotas mensuales para todos los contratos vigentes.
                                </div>
                            </div>

                            <div class="text-end pt-3">
                                <a href="index.php" class="btn btn-outline-secondary px-4 me-2">
                                    <i class="ri-close-line me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="ri-save-line me-1"></i> Guardar y Generar Pagos
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
