<?php
require_once __DIR__ . '/../../controllers/ContractController.php';

$controller = new ContractController();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->store($_POST);
    if ($result['success']) {
        header('Location: ' . $result['redirect']);
        exit;
    } else {
        $error = $result['message'];
    }
}

$data = $controller->create();
$page_title = $data['page_title'];
$awardees = $data['awardees'];
$fiscalYears = $data['fiscalYears'];
$categories = $data['internalCategories']; // Simplification for now, maybe merge both
$externalCategories = $data['externalCategories'];
$internalCategories = $data['internalCategories'];
$zones = $data['zones'];

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
                        <h5 class="card-title"><?php echo htmlspecialchars($page_title); ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Adjudicatario</label>
                                    <select name="awardee_id" class="form-control" required>
                                        <option value="">Seleccione...</option>
                                        <?php foreach ($awardees as $awardee): ?>
                                            <option value="<?php echo $awardee['id']; ?>">
                                                <?php echo htmlspecialchars($awardee['first_name'] . ' ' . $awardee['last_name'] . ' (' . $awardee['id_number'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Año Fiscal</label>
                                    <select name="fiscal_year_id" class="form-control" required>
                                        <?php foreach ($fiscalYears as $year): ?>
                                            <option value="<?php echo $year['id']; ?>" <?php echo ($year['is_active'] ? 'selected' : ''); ?>>
                                                <?php echo htmlspecialchars($year['year']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha Inicio</label>
                                    <input type="date" name="start_date" class="form-control" required value="<?php echo date('Y-01-01'); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha Fin</label>
                                    <input type="date" name="end_date" class="form-control" required value="<?php echo date('Y-12-31'); ?>">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tipo de Contrato</label>
                                    <select name="type" class="form-control" required>
                                        <option value="temporary">Temporal</option>
                                        <option value="permanent">Permanente</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Modo de Contrato</label>
                                    <select name="contract_mode" class="form-control" required>
                                        <option value="lease">Arrendamiento</option>
                                        <option value="concession">Concesión</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">Crear Contrato</button>
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
