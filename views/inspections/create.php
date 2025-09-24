<?php
// Vista de creación de reportes de inspección

session_start();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/InspectionController.php';

$inspectionReportsController = new InspectionController();

$data = $inspectionReportsController->create();
extract($data); // Extrae $inspectors, $stalls, $awardees

$page_title = 'Registrar Nuevo Reporte de Inspección';
$errors = [];
$form_data = [
    'scheduled_inspection_id' => '',
    'main_inspector_id' => $_SESSION['user_id'] ?? '',
    'assistant_inspector_id' => '',
    'stall_id' => '',
    'awardee_id' => '',
    'general_observations' => '',
    'inspector_signature_url' => '', // Este campo podría requerir una lógica de subida de archivos
    'assistant_signature_url' => '', // Este campo podría requerir una lógica de subida de archivos
];

// Procesar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = [
        'main_inspector_id' => trim($_POST['main_inspector_id'] ?? ''),
        'assistant_inspector_id' => trim($_POST['assistant_inspector_id'] ?? ''),
        'stall_id' => trim($_POST['stall_id'] ?? ''),
        'awardee_id' => trim($_POST['awardee_id'] ?? ''),
        'general_observations' => trim($_POST['general_observations'] ?? ''),
        'inspector_signature_url' => trim($_POST['inspector_signature_url'] ?? ''),
        'assistant_signature_url' => trim($_POST['assistant_signature_url'] ?? ''),
        'scheduled_date' => trim($_POST['scheduled_date'] ?? ''), // Suponiendo que viene del formulario
        'inspection_type' => trim($_POST['inspection_type'] ?? ''), // Suponiendo que viene del formulario
        'assigned_responsible_id' => trim($_POST['assigned_responsible_id'] ?? ''), // Suponiendo que viene del formulario
        'inspection_status' => trim($_POST['inspection_status'] ?? ''), // Suponiendo que viene del formulario
        'observations' => trim($_POST['observations'] ?? ''), // Suponiendo que viene del formulario
    ];

    $inspection_report_data = [
        'main_inspector_id' => $form_data['main_inspector_id'],
        'assistant_inspector_id' => $form_data['assistant_inspector_id'],
        'stall_id' => $form_data['stall_id'],
        'awardee_id' => $form_data['awardee_id'],
        'general_observations' => $form_data['general_observations'],
        'inspector_signature_url' => $form_data['inspector_signature_url'],
        'assistant_signature_url' => $form_data['assistant_signature_url'],
    ];

    // Array con los datos para la tabla 'scheduled_inspections'
    $scheduled_inspection_data = [
        'scheduled_date' => $form_data['scheduled_date'],
        'inspection_type' => $form_data['inspection_type'],
        'assigned_responsible_id' => $form_data['assigned_responsible_id'],
        'inspection_status' => $form_data['inspection_status'],
        'observations' => $form_data['observations'],
    ];

    $data = array_merge($inspection_report_data, $scheduled_inspection_data);

    $result = $inspectionReportsController->store($data);

    if ($result['success']) {
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => $result['message']
        ];
        header('Location: ' . $result['redirect']);
        exit;
    } else {
        $errors = $result['errors'] ?? [$result['message']];
    }
}

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
                        <li class="breadcrumb-item active" aria-current="page">Registrar Nuevo</li>
                    </ol>
                </nav>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="font-size: 2rem;font-weight: 600;">
                            <i class="ri-file-add-line me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i> Volver al listado
                        </a>
                    </div>

                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h6 class="alert-heading">
                                    <i class="ri-error-warning-line"></i> Se encontraron errores:
                                </h6>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="create.php" novalidate>
                            <div id="inspection-form">
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
                                                            <?php echo ((int)$form_data['main_inspector_id'] == (int)$inspector['inspector_id']) ? 'selected' : ''; ?>>
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
                                                            <?php echo ((int)$form_data['assistant_inspector_id'] == (int)$inspector['inspector_id']) ? 'selected' : ''; ?>>
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
                                                            <?php echo ((int)$form_data['stall_id'] == (int)$stall['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($stall['stall_number']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="awardee_id" class="form-label">
                                                Adjudicatario <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="awardee_id" name="awardee_id" required>
                                                <option value="">Seleccione un adjudicatario</option>
                                                <?php foreach ($awardees as $awardee): ?>
                                                    <option value="<?php echo htmlspecialchars($awardee['id']); ?>"
                                                            <?php echo ((int)$form_data['awardee_id'] == (int)$awardee['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($awardee['first_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="general_observations" class="form-label">
                                                Observaciones Generales
                                            </label>
                                            <textarea class="form-control"
                                                    id="general_observations"
                                                    name="general_observations"
                                                    rows="5"><?php echo htmlspecialchars($form_data['general_observations']); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr class="my-4">

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="index.php" class="btn btn-outline-secondary">
                                        <i class="ri-close-line"></i> Cancelar
                                    </a>
                                    <button type="button" onclick="next()" class="btn btn-primary">
                                        <i class="ri-walk-fill"></i> Programar Inspección
                                    </button>
                                </div>
                            </div>
                            <div id="scheduled-inspections" style="display: none;">
                                <h4 class="mb-3">Programar Inspección</h4>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="scheduled_date" class="form-label">Fecha Programada <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="scheduled_date" name="scheduled_date" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="inspection_type" class="form-label">Tipo de Inspección <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="inspection_type" name="inspection_type" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="assigned_responsible_id" class="form-label">Responsable Asignado <span class="text-danger">*</span></label>
                                        <select class="form-select" id="assigned_responsible_id" name="assigned_responsible_id" required>
                                            <option value="">Seleccione un usuario...</option>
                                            <?php foreach ($users as $user): ?>
                                                <option value="<?php echo htmlspecialchars($user['id']); ?>">
                                                    <?php echo htmlspecialchars($user['username']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="inspection_status" class="form-label">Estado de la Inspección <span class="text-danger">*</span></label>
                                        <select class="form-select" id="inspection_status" name="inspection_status" required>
                                            <option value="Pending" selected>Pendiente</option>
                                            <option value="In Progress">En Progreso</option>
                                            <option value="Completed">Completada</option>
                                            <option value="Cancelled">Cancelada</option>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label for="observations" class="form-label">Observaciones</label>
                                        <textarea class="form-control" id="observations" name="observations" rows="4"></textarea>
                                    </div>
                                </div>
                                <hr class="my-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="index.php" class="btn btn-outline-secondary">
                                        <i class="ri-close-line"></i> Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-save-line"></i> Guardar Reporte
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function next() {
    // Referencias a los campos del formulario
    const mainInspector = document.getElementById('main_inspector_id').value;
    const stall = document.getElementById('stall_id').value;
    const awardee = document.getElementById('awardee_id').value;
    const observations = document.getElementById('general_observations').value;

    // Validación de campos
    if (!mainInspector || !stall || !awardee || !observations) {
        alert('Por favor, complete todos los campos obligatorios antes de continuar.');
        return; // Detiene la función si la validación falla
    }

    // Si la validación es exitosa, muestra la siguiente sección
    document.getElementById('inspection-form').style.display = 'none';
    document.getElementById('scheduled-inspections').style.display = 'block';
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>