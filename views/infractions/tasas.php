<?php
// Vista para la gestión de tasas económicas (UT y Euro)
session_start();

require_once __DIR__ . '/../../controllers/InfractionsController.php';
require_once __DIR__ . '/../../controllers/RolesController.php';

$infractionsController = new InfractionsController();
$rol = new RolesController();

// 1. Obtener la tasa actual para mostrarla
$economicIndicators = $infractionsController->getLatestEconomicIndicators();
$ut_actual = $economicIndicators['ut_value'] ?? 'N/A';
$euro_actual = $economicIndicators['euro_bcv_rate'] ?? 'N/A';
$effective_date = isset($economicIndicators['effective_date']) ? date('d/m/Y', strtotime($economicIndicators['effective_date'])) : 'Ninguna';

$page_title = "Gestión de Tasas Económicas";

// Incluir header y layouts
require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-lg-8 offset-lg-2">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title" style="font-size: 2rem;font-weight: 600;">
                            <i class="ri-money-dollar-circle-line me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                    </div>

                    <?php if (isset($_SESSION['flash_message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['flash_message']['type'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show mx-3 mt-3" role="alert">
                        <?php echo htmlspecialchars($_SESSION['flash_message']['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['flash_message']); ?>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <div class="alert alert-info border-start border-info border-5 shadow-sm mb-4">
                            <i class="ri-information-line"></i>
                            <div class="alert-content">
                                <h6 class="fw-bold">Tasas Vigentes Actualmente</h6>
                                <p class="mb-1"><strong>Unidad Tributaria (UT):</strong> <?php echo htmlspecialchars($ut_actual); ?></p>
                                <p class="mb-1"><strong>Tasa del Euro (BCV):</strong> <?php echo htmlspecialchars($euro_actual); ?></p>
                                <small class="d-block text-muted">Última fecha de vigencia registrada: <?php echo htmlspecialchars($effective_date); ?></small>
                            </div>
                        </div>

                        <form action="save_rates.php" method="POST">
                            <h6 class="mb-3">Actualizar Tasas para la Fecha de Hoy (<?php echo date('d/m/Y'); ?>)</h6>

                            <?php if ($rol->hasPermission('CONFIG_RATES', 'r')): ?>
                            <div class="mb-3">
                                <label for="ut_value" class="form-label">Valor de la Unidad Tributaria (UT)</label>
                                <input type="number" step="0.000001" class="form-control" id="ut_value" name="ut_value" 
                                       placeholder="Ej: 0.400000" min="0.000001" required>
                                <div class="form-text">Usar punto como separador decimal.</div>
                            </div>

                            <div class="mb-4">
                                <label for="euro_bcv_rate" class="form-label">Tasa del Euro (BCV)</label>
                                <input type="number" step="0.000001" class="form-control" id="euro_bcv_rate" name="euro_bcv_rate" 
                                       placeholder="Ej: 38.543210" value="<?php echo htmlspecialchars($euro_actual); ?>" disabled style="background-color: #e9ecef;">
                                <div class="form-text">Este valor se actualiza automáticamente desde el módulo de Tasas de Euro.</div>
                            </div>
                            <?php endif; ?>
                            <?php if ($rol->hasPermission('CONFIG_RATES', 'w')): ?>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ri-refresh-line"></i> Actualizar / Registrar Tasas de Hoy
                            </button>
                            <?php endif;?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>