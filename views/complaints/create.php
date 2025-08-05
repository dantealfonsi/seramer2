<?php
// Vista de creación de quejas

session_start();

// Incluir el controlador y los modelos para cargar los datos de las listas
require_once __DIR__ . '/../../controllers/ComplaintsController.php';
require_once __DIR__ . '/../../models/MarketStallsModel.php';

$complaintsController = new ComplaintsController();
$marketStallsModel = new MarketStallsModel();

$data = $complaintsController->create();
$page_title = 'Registrar Nueva Queja';
$errors = [];
$form_data = [
    'client_user_id' => $_SESSION['user_id'] ?? null,
    'contractor_id' => $_SESSION['contractor_id'] ?? null, // si aplica
    'client_name' => '',
    'client_phone' => '',
    'client_email' => '',
    'complaint_description' => '',
    'position_id' => '',
    'complaint_type' => '',
    'complaint_status' => 'Received',
    'complaint_priority' => 'Medium',
    'internal_observations' => '',
];

// Opciones para los select de estado y prioridad
$allowed_priority = [
    'Low' => 'Baja',
    'Medium' => 'Media',
    'High' => 'Alta',
    'Urgent' => 'Urgente'
];
$allowed_status = [
    'Received' => 'Recibido',
    'In Process' => 'En Proceso',
    'Resolved' => 'Resuelto',
    'Closed' => 'Cerrado'
];

// Cargar la lista de puestos
$stalls = $marketStallsModel->getAll();

$stallDict = [];
foreach ($stalls as $id => $code) {
    $stallDict[] = ['id_stall' => $id, 'stall_code' => $code];     
}
// Procesar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = [
        'client_user_id' => $_SESSION['user_id'] ?? null,
        'contractor_id' => $_SESSION['contractor_id'] ?? null,
        'client_name' => trim($_POST['client_name'] ?? ''),
        'client_phone' => trim($_POST['client_phone'] ?? ''),
        'client_email' => trim($_POST['client_email'] ?? ''),
        'complaint_description' => trim($_POST['complaint_description'] ?? ''),
        'position_id' => trim($_POST['position_id'] ?? ''),
        'complaint_type' => trim($_POST['complaint_type'] ?? ''),
        'complaint_status' => trim($_POST['complaint_status'] ?? 'Received'),
        'complaint_priority' => trim($_POST['complaint_priority'] ?? 'Medium'),
        'internal_observations' => trim($_POST['internal_observations'] ?? '')
    ];

    $result = $complaintsController->store($form_data);

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
                        <li class="breadcrumb-item"><a href="index.php">Quejas</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Registrar Nuevo</li>
                    </ol>
                </nav>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="font-size: 2rem;font-weight: 600;">
                            <i class="ri-chat-voice-line me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
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
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="client_name" class="form-label">
                                            Nombre del Cliente <span class="text-danger">*</span>
                                        </label>
                                        <input type="text"
                                               class="form-control"
                                               id="client_name"
                                               name="client_name"
                                               value="<?php echo htmlspecialchars($form_data['client_name']); ?>"
                                               required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="client_email" class="form-label">
                                            Email del Cliente <span class="text-danger">*</span>
                                        </label>
                                        <input type="email"
                                               class="form-control"
                                               id="client_email"
                                               name="client_email"
                                               value="<?php echo htmlspecialchars($form_data['client_email']); ?>"
                                               required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="client_phone" class="form-label">
                                            Teléfono
                                        </label>
                                        <input type="text"
                                               class="form-control"
                                               id="client_phone"
                                               name="client_phone"
                                               value="<?php echo htmlspecialchars($form_data['client_phone']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="complaint_type" class="form-label">
                                            Tipo de Queja <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="complaint_type" name="complaint_type" required>
                                            <option value="">Seleccione un tipo de queja</option>
                                            <option value="Suggestion" <?php echo ($form_data['complaint_type'] == 'Suggestion') ? 'selected' : ''; ?>>Sugerencia</option>
                                            <option value="Claim" <?php echo ($form_data['complaint_type'] == 'Claim') ? 'selected' : ''; ?>>Reclamo</option>
                                            <option value="Question" <?php echo ($form_data['complaint_type'] == 'Question') ? 'selected' : ''; ?>>Pregunta</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="position_id" class="form-label">
                                            Puesto del Mercado
                                        </label>
                                        <select class="form-select" id="position_id" name="position_id">
                                            <option value="">Seleccione un puesto (opcional)</option>
                                            <?php foreach ($stallDict as $stall): ?>
                                                <option value="<?php echo htmlspecialchars($stall['id_stall']); ?>"
                                                        <?php echo ((int)$form_data['position_id'] == (int)$stall['id_stall']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($stall['stall_code']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="complaint_description" class="form-label">
                                            Descripción <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control"
                                                  id="complaint_description"
                                                  name="complaint_description"
                                                  rows="5"
                                                  required><?php echo htmlspecialchars($form_data['complaint_description']); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="complaint_status" class="form-label">
                                            Estado de la Queja <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="complaint_status" name="complaint_status" required>
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($allowed_status as $value => $label): ?>
                                                <option value="<?php echo htmlspecialchars($value); ?>"
                                                        <?php echo ($form_data['complaint_status'] == $value) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($label); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Nivel de Proceso en la gestión.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="complaint_priority" class="form-label">
                                            Prioridad <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="complaint_priority" name="complaint_priority" required>
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($allowed_priority as $value => $label): ?>
                                                <option value="<?php echo htmlspecialchars($value); ?>"
                                                        <?php echo ($form_data['complaint_priority'] == $value) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($label); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">Prioridad de avance en la gestión.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="internal_observations" class="form-label">
                                            Observaciones Internas
                                        </label>
                                        <textarea class="form-control"
                                                  id="internal_observations"
                                                  name="internal_observations"
                                                  rows="3"><?php echo htmlspecialchars($form_data['internal_observations']); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-end gap-2">
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="ri-close-line"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-add-line"></i> Registrar Queja
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