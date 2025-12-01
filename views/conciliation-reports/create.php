<?php
session_start();
require_once __DIR__ . '/../../controllers/ConciliationReportsController.php';

$reportsController = new ConciliationReportsController();
$data = [];
$errors = [];

// Manejar la solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
    $result = $reportsController->store($data);

    if (isset($result['redirect'])) {
        header("Location: " . $result['redirect']);
        exit;
    }

    if (!$result['success']) {
        $errors = $result['errors'] ?? [$result['message']];
    }
}

// Obtener datos iniciales para el formulario
$result = $reportsController->create();
$citations = $result['citations'];
$page_title = $result['page_title'];

// Opciones para los menús desplegables
$attendance_options = [
    1 => 'Presente',
    0 => 'Ausente'
];

$result_options = [
    'Agreement Reached' => 'Acuerdo alcanzado',
    'No Agreement' => 'Sin acuerdo',
    'Case Postponed' => 'Caso pospuesto',
    'Absent Party' => 'Parte ausente'
];

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
                        <h5 class="card-title" style="font-size: 2rem;font-weight: 600;">
                            <i class="ri-add-box-line me-1"
                                style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i> Volver
                        </a>
                    </div>

                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger" role="alert">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="citation_id" class="form-label">Citación</label>
                                <select class="form-control" id="citation_id" name="citation_id" required>
                                    <option value="">Seleccione una citación</option>
                                    <?php foreach ($citations as $citation): ?>
                                        <option value="<?php echo htmlspecialchars($citation['citation_id']); ?>">
                                            Citación #<?php echo htmlspecialchars($citation['citation_id']); ?> -
                                            <?php echo htmlspecialchars($citation['location']); ?>
                                            (<?php echo htmlspecialchars($citation['citation_datetime']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="awardee_attendance" class="form-label">Asistencia del Citado</label>
                                <select class="form-control" id="awardee_attendance" name="awardee_attendance" required>
                                    <?php foreach ($attendance_options as $value => $label): ?>
                                        <option value="<?php echo htmlspecialchars($value); ?>" <?php echo (isset($data['awardee_attendance']) && $data['awardee_attendance'] == $value) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="result" class="form-label">Resultado de la Conciliación</label>
                                <select class="form-control" id="result" name="result" required>
                                    <option value="">Seleccione un resultado</option>
                                    <?php foreach ($result_options as $value => $label): ?>
                                        <option value="<?php echo htmlspecialchars($value); ?>" <?php echo (isset($data['result']) && $data['result'] == $value) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="agreement_details" class="form-label">Detalles del Acuerdo
                                    (Opcional)</label>
                                <textarea onkeyup="validarText('agreement_details',8,'errorTextAgreementDetails')"
                                    class="form-control" id="agreement_details" name="agreement_details"
                                    rows="5"><?php echo htmlspecialchars($data['agreement_details'] ?? ''); ?></textarea>
                                <div id="errorTextAgreementDetails" style="color: red;"></div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line"></i> Guardar Informe
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>