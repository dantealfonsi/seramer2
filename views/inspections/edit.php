<?php
// Vista de edición de reportes de inspección

session_start();

// Incluir el controlador y los modelos necesarios
require_once __DIR__ . '/../../controllers/InspectionController.php';

$inspectionReportsController = new InspectionController();

// Manejar la solicitud POST para actualizar el reporte
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'report_id' => $_POST['report_id'] ?? null,
        'scheduled_inspection_id' => trim($_POST['scheduled_inspection_id'] ?? ''),
        'main_inspector_id' => trim($_POST['main_inspector_id'] ?? ''),
        'assistant_inspector_id' => trim($_POST['assistant_inspector_id'] ?? ''),
        'stall_id' => trim($_POST['stall_id'] ?? ''),
        'awardee_id' => trim($_POST['awardee_id'] ?? ''),
        'general_observations' => trim($_POST['general_observations'] ?? ''),
        'inspector_signature_url' => trim($_POST['inspector_signature_url'] ?? ''),
        'assistant_signature_url' => trim($_POST['assistant_signature_url'] ?? ''),
        'scheduled_date' => trim($_POST['scheduled_date'] ?? ''),
        'inspection_status' => trim($_POST['inspection_status'] ?? ''),
    ];

    $result = $inspectionReportsController->update($data['report_id'], $data);

    if (isset($result['success']) && $result['success']) {
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => $result['message']
        ];
        header('Location: ' . $result['redirect']);
        exit;
    } else {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => $result['message']
        ];
        header("Location: edit.php?id=" . urlencode($data['report_id']));
        exit;
    }
}

// Cargar el reporte y datos para el formulario
$reportId = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$reportId) {
    header('Location: index.php?error=invalid_id');
    exit;
}

$data = $inspectionReportsController->edit($reportId);

if (!$data['report']) {
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'Reporte de inspección no encontrado.'
    ];
    header('Location: index.php');
    exit;
}

$page_title = 'Editar Reporte de Inspección #' . htmlspecialchars($reportId);
extract($data); // Extrae $report, $inspectors, $stalls, $awardees, etc.

// Incluir header y layouts
require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Reportes de Inspección</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Editar</li>
                    </ol>
                </nav>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="font-size: 2rem;font-weight: 600;">
                            <i class="ri-edit-2-line me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i> Volver al listado
                        </a>
                    </div>

                    <div class="card-body">
                        <?php if (isset($data['errors']) && !empty($data['errors'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h6 class="alert-heading">
                                    <i class="ri-error-warning-line"></i> Se encontraron errores:
                                </h6>
                                <ul class="mb-0">
                                    <?php foreach ($data['errors'] as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="edit.php" novalidate>
                            <input type="hidden" name="report_id" value="<?php echo htmlspecialchars($report['report_id']); ?>">
                            <input type="hidden" name="scheduled_inspection_id" value="<?php echo htmlspecialchars($report['scheduled_inspection_id']); ?>">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="main_inspector_id" class="form-label">
                                            Inspector Principal <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="main_inspector_id" name="main_inspector_id" required>
                                            <option value="">Seleccione un inspector</option>
                                            <?php foreach ($inspectors as $inspector): ?>
                                                <option value="<?php echo htmlspecialchars($inspector['inspector_id']); ?>"
                                                        <?php echo ((int)$report['main_inspector_id'] == (int)$inspector['inspector_id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($inspector['full_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="assistant_inspector_id" class="form-label">
                                            Inspector Auxiliar
                                        </label>
                                        <select class="form-select" id="assistant_inspector_id" name="assistant_inspector_id">
                                            <option value="">Seleccione un inspector (opcional)</option>
                                            <?php foreach ($inspectors as $inspector): ?>
                                                <option value="<?php echo htmlspecialchars($inspector['inspector_id']); ?>"
                                                        <?php echo ((int)$report['assistant_inspector_id'] == (int)$inspector['inspector_id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($inspector['full_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="stall_id" class="form-label">
                                            Puesto del Mercado <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="stall_id" name="stall_id" required>
                                            <option value="">Seleccione un puesto</option>
                                            <?php foreach ($stalls as $stall): ?>
                                                <option value="<?php echo htmlspecialchars($stall['id']); ?>"
                                                        <?php echo ((int)$report['stall_id'] == (int)$stall['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($stall['stall_number']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="awardee_id_display" class="form-label">
                                            Adjudicatario <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="awardee_id_display" disabled>
                                            <option value="">Seleccione un adjudicatario</option>
                                            <?php foreach ($awardees as $awardee): ?>
                                                <option value="<?php echo htmlspecialchars($awardee['id']); ?>"
                                                        <?php echo ((int)$report['awardee_id'] == (int)$awardee['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($awardee['full_name'] ?? $awardee['first_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" name="awardee_id" id="awardee_id" value="<?php echo htmlspecialchars($report['awardee_id']); ?>">
                                        <small class="text-muted">Se asigna automáticamente al seleccionar el puesto.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="general_observations" class="form-label">
                                            Observaciones Generales
                                        </label>
                                        <textarea onKeyup="validarLocation('general_observations', 8)" class="form-control"
                                                  id="general_observations"
                                                  name="general_observations"
                                                  rows="5"><?php echo htmlspecialchars($report['general_observations']); ?>
                                        </textarea>
                                        <div id="errorTextLocation" style="color: red;"></div>
                                    </div>
                                </div>
                                <h6 class="text-success mb-3"><i class="ri-calendar-check-line"></i> Detalles de la Cita Programada</h6>
                                    <div class="col-md-6">
                                        <label for="scheduled_date" class="form-label">Fecha Programada <span class="text-danger">*</span></label>
                                        <input value="<?php echo htmlspecialchars($report['scheduled_date']); ?>" type="date" class="form-control" id="scheduled_date" name="scheduled_date" min="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="inspection_status" class="form-label">Estado de la Inspección <span class="text-danger">*</span></label>
                                        <select class="form-select" id="inspection_status" name="inspection_status" required>
                                            <option value="Pending" <?php echo ($report['inspection_status'] == 'Pending') ? 'selected' : ''; ?>>Pendiente</option>
                                            <option value="In Progress" <?php echo ($report['inspection_status'] == 'In Progress') ? 'selected' : ''; ?>>En Progreso</option>
                                            <option value="Completed" <?php echo ($report['inspection_status'] == 'Completed') ? 'selected' : ''; ?>>Completada</option>
                                            <option value="Cancelled" <?php echo ($report['inspection_status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelada</option>
                                        </select>
                                    </div>                                
                            </div>
                            
                            <hr class="my-4">

                            <div class="d-flex justify-content-end gap-2">
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="ri-close-line"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line"></i> Actualizar Reporte
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

<script>
// Mapeo de Puesto -> Adjudicatario
const stallAwardeeMapping = <?php echo json_encode($stallAwardeeMapping); ?>;

document.getElementById('stall_id').addEventListener('change', function() {
    const stallId = this.value;
    const awardeeIdDisplay = document.getElementById('awardee_id_display');
    const awardeeIdHidden = document.getElementById('awardee_id');
    
    if (stallId && stallAwardeeMapping[stallId]) {
        const mapping = stallAwardeeMapping[stallId];
        awardeeIdDisplay.value = mapping.id;
        awardeeIdHidden.value = mapping.id;
    } else {
        awardeeIdDisplay.value = "";
        awardeeIdHidden.value = "";
    }
});
</script>