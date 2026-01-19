<?php
session_start();
require_once __DIR__ . '/../../controllers/ConciliationReportsController.php';

$reportsController = new ConciliationReportsController();
$report = null;
$errors = [];

// Obtener el ID de la URL
$id = $_GET['id'] ?? null;

// Cargar los datos del reporte si existe un ID válido
if ($id) {
    $result = $reportsController->edit($id);
    if ($result['success']) {
        $report = $result['report'];
    } else {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => $result['message']];
        header("Location: index.php");
        exit;
    }
} else {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'ID de informe no proporcionado.'];
    header("Location: index.php");
    exit;
}

// Manejar la solicitud POST para actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
    $result = $reportsController->update($id, $data);

    if (isset($result['redirect'])) {
        header("Location: " . $result['redirect']);
        exit;
    }

    if (!$result['success']) {
        $errors = $result['errors'] ?? [$result['message']];
    } else {
        // Recargar el reporte con los nuevos datos después de la actualización exitosa
        $result = $reportsController->edit($id);
        $report = $result['report'];
    }
}

// Obtener datos iniciales para el formulario
$result = $reportsController->create();
$citations = $result['citations'];
$page_title = "Editar Informe de Conciliación";

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
                            <i class="ri-edit-box-line me-1"
                                style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="../citations/index.php" class="btn btn-secondary">
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
                            <input type="hidden" name="report_id"
                                value="<?php echo htmlspecialchars($report['report_id']); ?>">

                            <div class="mb-3">
                                <label for="citation_id" class="form-label">Citación</label>
                                <select class="form-control" id="citation_id" name="citation_id" required disabled>
                                    <option value="">Seleccione una citación</option>
                                    <?php foreach ($citations as $citation): ?>
                                        <option value="<?php echo htmlspecialchars($citation['citation_id']); ?>" <?php echo ($report['citation_id'] == $citation['citation_id']) ? 'selected' : ''; ?>>
                                            Citación #<?php echo htmlspecialchars($citation['citation_id']); ?> -
                                            <?php echo htmlspecialchars($citation['location']); ?>
                                            (<?php echo htmlspecialchars($citation['citation_datetime']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="citation_id" value="<?php echo htmlspecialchars($report['citation_id']); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="awardee_attendance" class="form-label">Asistencia del Citado</label>
                                <select class="form-control" id="awardee_attendance" name="awardee_attendance" required>
                                    <?php foreach ($attendance_options as $value => $label): ?>
                                        <option value="<?php echo htmlspecialchars($value); ?>" <?php echo ($report['awardee_attendance'] == $value) ? 'selected' : ''; ?>>
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
                                        <option value="<?php echo htmlspecialchars($value); ?>" <?php echo ($report['result'] === $value) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div id="reprogramming_fields" class="mb-3" style="display: none;">
                                <label for="reprogramming_datetime" class="form-label">Fecha y Hora de
                                    Reprogramación</label>
                                <input type="datetime-local" class="form-control" id="reprogramming_datetime"
                                    name="reprogramming_datetime" value="">
                            </div>

                            <div class="mb-3">
                                <label for="agreement_details" class="form-label">Detalles del Acuerdo
                                    (Opcional)</label>
                                <textarea onkeyup="validarText('agreement_details',8,'errorTextAgreementDetails')"
                                    class="form-control" id="agreement_details" name="agreement_details"
                                    rows="5"><?php echo htmlspecialchars($report['agreement_details'] ?? ''); ?></textarea>
                                <div id="errorTextAgreementDetails" style="color: red;"></div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line"></i> Actualizar Informe
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const resultSelect = document.getElementById('result');
        const reprogrammingFields = document.getElementById('reprogramming_fields');
        const reprogrammingDatetimeInput = document.getElementById('reprogramming_datetime');

        function toggleReprogrammingFields() {
            if (resultSelect.value === 'Case Postponed') {
                reprogrammingFields.style.display = 'block';
                reprogrammingDatetimeInput.required = true;
            } else {
                reprogrammingFields.style.display = 'none';
                reprogrammingDatetimeInput.required = false;
            }
        }

        // Ejecutar la función al cargar la página para el caso de edición
        toggleReprogrammingFields();

        // Agregar el listener para cambios futuros
        resultSelect.addEventListener('change', toggleReprogrammingFields);
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>