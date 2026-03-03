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
// Opciones en español para el select de estado de citación
// "Cuando editas una citacion, no deberias de poder seleccionar programada, solo reprogramada"
$allowed_status = [
    'Rescheduled' => 'Reprogramada',
    'Completed' => 'Completada',
    'Canceled' => 'Cancelada',
    'In Process' => 'En Proceso' // Agregamos En Proceso por si acaso se necesita manual
];
// Si el estado actual es 'Scheduled', lo agregamos solo para que se vea, pero la idea es que cambie a Reprogramada
if ($citation['citation_status'] === 'Scheduled') {
    // Opción: No agregarlo para forzar el cambio, o agregarlo.
    // El requerimiento dice "no deberias de poder seleccionar programada".
    // Si no lo agrego, el select seleccionará 'Reprogramada' por defecto.
}

// Obtener las listas de mediadores para los select
$mediators = $citationsController->getMediatorsList();
$stalls = $citationsController->getStallsList();

// Convertir puestos a JS para el buscador
$stalls_js = array_map(function($s){
    return [
        'id' => (int)($s['id'] ?? 0),
        'stall_number' => $s['stall_number'] ?? '',
        'location' => $s['location_description'] ?? '',
        'awardee_id' => (int)($s['awardee_id'] ?? 0),
        'awardee_name' => trim(($s['awardee_full_name'] ?? ''))
    ];
}, $stalls);

// Para el edit, necesitamos cargar las infracciones del adjudicatario actual inicialmente
$initial_infractions = [];
if (!empty($formData['awardee_id'])) {
    $initial_infractions = $citationsController->getInfractionsByAwardee($formData['awardee_id']);
}

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
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="card-title d-flex align-items-center mb-1" style="font-size: 1.4rem;font-weight: 600;">
                                <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="ri-edit-2-line" style="color: #696cff; font-size: 1.5rem;"></i></div>
                                <?php echo htmlspecialchars($page_title); ?>
                            </h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="index.php">Citaciones</a></li>
                                    <li class="breadcrumb-item">
                                        <a href="view.php?id=<?php echo htmlspecialchars($citation_id); ?>">Citación #<?php echo htmlspecialchars($citation_id); ?></a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">Editar</li>
                                </ol>
                            </nav>
                        </div>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="ri-arrow-left-line"></i> Volver al listado
                        </a>
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
                                <!-- Campo Puesto (Buscador) -->
                                <div class="col-md-6">
                                    <label for="stall_search" class="form-label">Puesto / Local <span class="text-danger">*</span></label>
                                    <input list="stalls_datalist" id="stall_search" class="form-control" placeholder="Escriba el número de puesto..." onchange="onStallSelected()" value="<?php echo htmlspecialchars($formData['stall_number'] ?? ''); ?>">
                                    <datalist id="stalls_datalist">
                                        <?php foreach ($stalls as $stall): ?>
                                            <option value="<?php echo htmlspecialchars($stall['stall_number']); ?>" data-id="<?php echo $stall['id']; ?>">
                                        <?php endforeach; ?>
                                    </datalist>
                                </div>

                                <!-- Campo Adjudicatario (Auto-llenado) -->
                                <div class="col-md-6">
                                    <label for="awardee_name" class="form-label">Adjudicatario</label>
                                    <input type="text" id="awardee_name" class="form-control" readonly placeholder="Se autocompletará al seleccionar el puesto" value="<?php echo htmlspecialchars($formData['awardee_full_name'] ?? ''); ?>">
                                    <input type="hidden" id="awardee_id" name="awardee_id" value="<?php echo htmlspecialchars($formData['awardee_id'] ?? ''); ?>">
                                </div>

                                <!-- Campo Infracción (Dinámico) -->
                                <div class="col-md-6">
                                    <label for="infraction_id" class="form-label">Infracción <span class="text-danger">*</span></label>
                                    <select id="infraction_id" name="infraction_id" class="form-select <?php echo isset($errors['infraction_id']) ? 'is-invalid' : ''; ?>" required>
                                        <option value="">Seleccione un puesto primero...</option>
                                        <?php foreach ($initial_infractions as $inf): ?>
                                            <?php 
                                            $date = (new DateTime($inf['infraction_datetime']))->format('d/m/Y');
                                            $descSnippet = $inf['infraction_description'] ? (substr($inf['infraction_description'], 0, 50) . '...') : 'Sin descripción';
                                            $label = "[" . strtoupper($inf['infraction_type_name']) . "] - Fecha: " . $date . " - (" . $descSnippet . ")";
                                            ?>
                                            <option value="<?php echo htmlspecialchars($inf['infraction_id']); ?>" <?php echo ($formData['infraction_id'] == $inf['infraction_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errors['infraction_id'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['infraction_id']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Campo Fecha y Hora -->
                                <div class="col-md-6">
                                    <label for="citation_datetime" class="form-label">Fecha y Hora <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control <?php echo isset($errors['citation_datetime']) ? 'is-invalid' : ''; ?>" id="citation_datetime" name="citation_datetime" value="<?php echo htmlspecialchars($formData['citation_datetime']); ?>" min="<?php echo date('Y-m-d\TH:i'); ?>" required>
                                    <?php if (isset($errors['citation_datetime'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['citation_datetime']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Campo Ubicación (Auto-llenado / Manual) -->
                                <div class="col-md-6">
                                    <label for="location" class="form-label">Ubicación <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?php echo isset($errors['location']) ? 'is-invalid' : ''; ?>" id="location" name="location" value="<?php echo htmlspecialchars($formData['location']); ?>" required>
                                    <?php if (isset($errors['location'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['location']; ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Campo Mediador -->
                                <div class="col-md-6">
                                    <label for="mediator_user_id" class="form-label">Mediador <span class="text-danger">*</span></label>
                                    <select id="mediator_user_id" name="mediator_user_id" class="form-select <?php echo isset($errors['mediator_user_id']) ? 'is-invalid' : ''; ?>" required>
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
                                    <label for="citation_status" class="form-label">Estado <span class="text-danger">*</span></label>
                                    <select id="citation_status" name="citation_status" class="form-select <?php echo isset($errors['citation_status']) ? 'is-invalid' : ''; ?>" required>
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

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="ri-close-line"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line"></i> Actualizar Citación
                                </button>
                            </div>
                        </form>

<script>
const STALLS = <?php echo json_encode($stalls_js); ?>;

function onStallSelected() {
    const searchValue = document.getElementById('stall_search').value;
    const stall = STALLS.find(s => s.stall_number === searchValue);
    
    if (stall) {
        document.getElementById('awardee_name').value = stall.awardee_name;
        document.getElementById('awardee_id').value = stall.awardee_id;
        document.getElementById('location').value = stall.location;
        loadInfractions(stall.awardee_id);
    } else {
        resetForm();
    }
}

function loadInfractions(awardeeId) {
    const infractionSelect = document.getElementById('infraction_id');
    infractionSelect.disabled = true;
    infractionSelect.innerHTML = '<option value="">Cargando infracciones...</option>';

    fetch(`../infractions/api_infractions.php?getInfractionsByAwardee=1&awardeeId=${awardeeId}`)
        .then(response => response.json())
        .then(data => {
            infractionSelect.innerHTML = '<option value="">Seleccione una infracción...</option>';
            if (data.length > 0) {
                data.forEach(inf => {
                    const date = new Date(inf.infraction_datetime).toLocaleDateString();
                    const descSnippet = inf.infraction_description ? (inf.infraction_description.substring(0, 50) + '...') : 'Sin descripción';
                    infractionSelect.innerHTML += `<option value="${inf.infraction_id}">[${inf.infraction_type_name.toUpperCase()}] - Fecha: ${date} - (${descSnippet})</option>`;
                });
                infractionSelect.disabled = false;
            } else {
                infractionSelect.innerHTML = '<option value="">No hay infracciones reportadas para este adjudicatario.</option>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            infractionSelect.innerHTML = '<option value="">Error al cargar infracciones.</option>';
        });
}

function resetForm() {
    document.getElementById('awardee_name').value = '';
    document.getElementById('awardee_id').value = '';
    document.getElementById('location').value = '';
    const infractionSelect = document.getElementById('infraction_id');
    infractionSelect.disabled = true;
    infractionSelect.innerHTML = '<option value="">Seleccione un puesto primero...</option>';
}
</script>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>