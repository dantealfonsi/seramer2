<?php
require_once __DIR__ . '/../../controllers/ContractController.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$controller = new ContractController();
$result = $controller->edit((int)$_GET['id']);

if (!$result['success']) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => $result['message']];
    header('Location: index.php');
    exit;
}

$contract = $result['contract'];
$awardees = $result['awardees'];
$fiscalYears = $result['fiscalYears'];
$page_title = $result['page_title'];

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-8 mx-auto">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Editar Contrato #<?= $contract['id'] ?></h5>
                        <a href="detail.php?id=<?= $contract['id'] ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="ri-arrow-left-line"></i> Volver al Detalle
                        </a>
                    </div>
                    <div class="card-body">
                        <form action="update.php" method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="id" value="<?= $contract['id'] ?>">
                            
                            <!-- Adjudicatario y Año Fiscal -->
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Adjudicatario</label>
                                    <select class="form-select select2" name="awardee_id" required>
                                        <?php foreach ($awardees as $awardee): ?>
                                        <option value="<?= $awardee['id'] ?>" <?= $awardee['id'] == $contract['awardee_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($awardee['last_name'] . ' ' . $awardee['first_name']) ?> (<?= $awardee['id_number'] ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Año Fiscal</label>
                                    <select class="form-select" name="fiscal_year_id" required>
                                        <?php foreach ($fiscalYears as $fy): ?>
                                        <option value="<?= $fy['id'] ?>" <?= $fy['id'] == $contract['fiscal_year_id'] ? 'selected' : '' ?>>
                                            <?= $fy['year'] ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Fechas y Tipos -->
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Fecha Inicio</label>
                                    <input type="date" class="form-control" name="start_date" value="<?= $contract['start_date'] ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha Fin</label>
                                    <input type="date" class="form-control" name="end_date" value="<?= $contract['end_date'] ?>" required>
                                </div>
                            </div>

                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Tipo de Contrato</label>
                                    <select class="form-select" name="type" required>
                                        <option value="simultaneous" <?= $contract['type'] === 'simultaneous' ? 'selected' : '' ?>>Simultáneo</option>
                                        <option value="advance" <?= $contract['type'] === 'advance' ? 'selected' : '' ?>>Anticipado</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Modalidad</label>
                                    <select class="form-select" name="contract_mode" required>
                                        <option value="monthly" <?= $contract['contract_mode'] === 'monthly' ? 'selected' : '' ?>>Mensual</option>
                                        <option value="weekly" <?= $contract['contract_mode'] === 'weekly' ? 'selected' : '' ?>>Semanal</option>
                                    </select>
                                </div>
                            </div>

                            <div class="alert alert-info border-info d-flex align-items-center mb-4">
                                <i class="ri-information-line ri-24px me-2"></i>
                                <span>Note que para modificar rubros o locales asignados, debe hacerlo desde la vista de <strong>Detalle del Contrato</strong>.</span>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg d-flex align-items-center justify-content-center">
                                    <i class="ri-refresh-line me-2"></i> Actualizar Información
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
