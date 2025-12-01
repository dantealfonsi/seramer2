<?php
session_start();
require_once __DIR__ . '/../../controllers/CitationsController.php';

$citationsController = new CitationsController();
$page_title = 'Editar Citación';

$errors = [];
$formData = [];
$citation_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $citation_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    if (!$citation_id) {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => 'ID de citación no especificado.'
        ];
        header("Location: index.php");
        exit;
    }
    
    $citation = $citationsController->getById($citation_id);

    if (!$citation) {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => 'Citación no encontrada.'
        ];
        header("Location: index.php");
        exit;
    }

    // Formatear la fecha y hora para el campo input[type="datetime-local"]
    $citation['citation_datetime'] = (new DateTime($citation['citation_datetime']))->format('Y-m-d\TH:i');
    
    $formData = $citation;
}

// Opciones en español para el select de estado de citación
$allowed_status = [
    'Scheduled' => 'Programada',
    'Rescheduled' => 'Reprogramada',
    'Completed' => 'Completada',
    'Canceled' => 'Cancelada'
];

// Obtener las listas de infracciones y mediadores para los select
$infractions = $citationsController->getInfractionsList();
$mediators = $citationsController->getMediatorsList();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $citation_id = filter_input(INPUT_POST, 'citation_id', FILTER_SANITIZE_NUMBER_INT);
    
    // Sanear y validar los datos de entrada
    $formData = [
        'infraction_id' => filter_input(INPUT_POST, 'infraction_id', FILTER_SANITIZE_NUMBER_INT),
        'citation_datetime' => filter_input(INPUT_POST, 'citation_datetime', FILTER_SANITIZE_SPECIAL_CHARS),
        'location' => filter_input(INPUT_POST, 'location', FILTER_SANITIZE_SPECIAL_CHARS),
        'mediator_user_id' => filter_input(INPUT_POST, 'mediator_user_id', FILTER_SANITIZE_NUMBER_INT),
        'citation_status' => filter_input(INPUT_POST, 'citation_status', FILTER_SANITIZE_SPECIAL_CHARS)
    ];

    // Validar que los campos no estén vacíos
    if (empty($formData['infraction_id'])) {
        $errors['infraction_id'] = 'Debe seleccionar una infracción.';
    }
    if (empty($formData['citation_datetime'])) {
        $errors['citation_datetime'] = 'Debe ingresar la fecha y hora de la citación.';
    }
    if (empty($formData['location'])) {
        $errors['location'] = 'Debe ingresar la ubicación.';
    }
    if (empty($formData['mediator_user_id'])) {
        $errors['mediator_user_id'] = 'Debe seleccionar un mediador.';
    }
    if (!array_key_exists($formData['citation_status'], $allowed_status)) {
        $errors['citation_status'] = 'El estado de la citación no es válido.';
    }
    
    if (empty($errors)) {
        // Si no hay errores, intentar actualizar la citación
        $updateResult = $citationsController->update($citation_id, $formData);

        $_SESSION['flash_message'] = [
            'type' => $updateResult['success'] ? 'success' : 'danger',
            'message' => $updateResult['message']
        ];
        
        // Redirigir a la página de índice después de la actualización
        header("Location: index.php");
        exit;
    } else {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => 'Por favor, corrija los errores en el formulario.'
        ];
    }
}

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
                            <i class="ri-edit-line me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['flash_message'])): ?>
                            <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?> mt-2" role="alert">
                                <?php echo htmlspecialchars($_SESSION['flash_message']['message']); ?>
                            </div>
                            <?php unset($_SESSION['flash_message']); ?>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <input type="hidden" name="citation_id" value="<?php echo htmlspecialchars($citation_id); ?>">
                            <div class="row g-3">
                                <!-- Campo Infracción -->
                                <div class="col-md-6">
                                    <label for="infraction_id" class="form-label">Infracción</label>
                                    <select id="infraction_id" name="infraction_id" class="form-select <?php echo isset($errors['infraction_id']) ? 'is-invalid' : ''; ?>">
                                        <option value="">Seleccione una infracción...</option>
                                        <?php foreach ($infractions as $infraction): ?>
                                            <option value="<?php echo htmlspecialchars($infraction['infraction_id']); ?>"
                                                <?php echo ($formData['infraction_id'] == $infraction['infraction_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($infraction['infraction_description']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errors['infraction_id'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['infraction_id']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Campo Fecha y Hora -->
                                <div class="col-md-6">
                                    <label for="citation_datetime" class="form-label">Fecha y Hora</label>
                                    <input type="datetime-local" class="form-control <?php echo isset($errors['citation_datetime']) ? 'is-invalid' : ''; ?>" id="citation_datetime" name="citation_datetime" value="<?php echo htmlspecialchars($formData['citation_datetime']); ?>">
                                    <?php if (isset($errors['citation_datetime'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['citation_datetime']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Campo Ubicación -->
                                <div class="col-md-6">
                                    <label for="location" class="form-label">Ubicación</label>
                                    <input onKeyup="validarLocation('location', 8)" type="text" class="form-control <?php echo isset($errors['location']) ? 'is-invalid' : ''; ?>" id="location" name="location" value="<?php echo htmlspecialchars($formData['location']); ?>">
                                    <div id="errorTextLocation" style="color: red;"></div>
                                    <?php if (isset($errors['location'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['location']; ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Campo Mediador -->
                                <div class="col-md-6">
                                    <label for="mediator_user_id" class="form-label">Mediador</label>
                                    <select id="mediator_user_id" name="mediator_user_id" class="form-select <?php echo isset($errors['mediator_user_id']) ? 'is-invalid' : ''; ?>">
                                        <option value="">Seleccione un mediador...</option>
                                        <?php foreach ($mediators as $mediator): ?>
                                            <option value="<?php echo htmlspecialchars($mediator['inspector_id']); ?>"
                                                <?php echo ($formData['mediator_user_id'] == $mediator['inspector_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($mediator['full_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errors['mediator_user_id'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['mediator_user_id']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Campo Estado -->
                                <div class="col-md-6">
                                    <label for="citation_status" class="form-label">Estado</label>
                                    <select id="citation_status" name="citation_status" class="form-select <?php echo isset($errors['citation_status']) ? 'is-invalid' : ''; ?>">
                                        <?php foreach ($allowed_status as $key => $value): ?>
                                            <option value="<?php echo htmlspecialchars($key); ?>"
                                                <?php echo ($formData['citation_status'] == $key) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($value); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errors['citation_status'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['citation_status']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="d-flex justify-content-start gap-2 mt-4">
                                <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i>Actualizar</button>
                                <a href="index.php" class="btn btn-secondary"><i class="ri-close-line me-1"></i>Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>