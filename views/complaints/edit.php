<?php
// Vista de edición de quejas

// Incluir el controlador y los modelos necesarios
require_once __DIR__ . '/../../controllers/ComplaintsController.php';

$complaintsController = new ComplaintsController();

// Manejar la solicitud POST para actualizar la queja
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... logic ...
}

// RBAC: Solo RRHH puede editar
if (!isset($_SESSION['selected_department']) || $_SESSION['selected_department'] !== 'Recursos Humanos') {
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'No tiene permisos para acceder a esta sección.'
    ];
    header('Location: index.php');
    exit;
}

// Cargar la queja y datos para el formulario
$complaintId = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$complaintId) {
    header('Location: index.php?error=invalid_id');
    exit;
}

$data = $complaintsController->edit($complaintId);

$page_title = 'Editar Queja #' . htmlspecialchars($complaintId);

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
$stalls = $complaintsController->getStallsList();

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
                            <input type="hidden" name="complaint_id" value="<?php echo htmlspecialchars($data['complaint']['complaint_id']); ?>">

                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="fw-bold mb-3 border-bottom pb-2">Información del Cliente</h6>
                                    <div class="mb-3">
                                        <label for="client_name" class="form-label">Nombre del Cliente</label>
                                        <input onkeyup="validarText('client_name',8,'errorTextClientName')" type="text" class="form-control" id="client_name" name="client_name" value="<?php echo htmlspecialchars($data['complaint']['client_name']); ?>" required>
                                        <div id="errorTextClientName" style="color: red;"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="client_email" class="form-label">Email del Cliente</label>
                                        <input onkeyup="validarEmail('client_email')" type="email" class="form-control" id="client_email" name="client_email" value="<?php echo htmlspecialchars($data['complaint']['client_email']); ?>" required>
                                        <div id="errorEmail" style="color: red;"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="client_phone" class="form-label">Teléfono</label>
                                        <input onkeyup="validarTelefono('client_phone')" type="text" class="form-control" id="client_phone" name="client_phone" value="<?php echo htmlspecialchars($data['complaint']['client_phone']); ?>">
                                        <div id="errorTelefono" style="color: red;"></div>
                                    </div>

                                    <h6 class="fw-bold mt-4 mb-3 border-bottom pb-2">Asociación (Local / Adjudicatario)</h6>
                                    <?php
                                        // Buscar el número de puesto actual
                                        $currentStallNumber = '';
                                        foreach ($stalls as $stall) {
                                            if ($stall['id'] == $data['complaint']['stall_id']) {
                                                $currentStallNumber = $stall['stall_number'];
                                                break;
                                            }
                                        }

                                        // Buscar el nombre del adjudicatario actual
                                        $currentAwardeeName = '';
                                        if ($data['complaint']['awardee_id']) {
                                            foreach ($data['awardees'] as $awardee) {
                                                if ($awardee['id'] == $data['complaint']['awardee_id']) {
                                                    $currentAwardeeName = $awardee['first_name'] . ' ' . $awardee['last_name'];
                                                    break;
                                                }
                                            }
                                        }
                                    ?>
                                    <div class="mb-3">
                                        <label for="stall_search" class="form-label">Puesto del Mercado</label>
                                        <input list="stalls_datalist" id="stall_search" class="form-control" placeholder="Escriba el número de puesto..." onchange="onStallSelected()" value="<?php echo htmlspecialchars($currentStallNumber); ?>">
                                        <datalist id="stalls_datalist">
                                            <?php foreach ($stalls as $stall): ?>
                                                <option value="<?php echo htmlspecialchars($stall['stall_number']); ?>" data-id="<?php echo $stall['id']; ?>">
                                            <?php endforeach; ?>
                                        </datalist>
                                        <input type="hidden" id="position_id" name="position_id" value="<?php echo htmlspecialchars($data['complaint']['stall_id']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="awardee_name" class="form-label">Adjudicatario</label>
                                        <input type="text" id="awardee_name" class="form-control" readonly placeholder="Se autocompletará al seleccionar el puesto" value="<?php echo htmlspecialchars($currentAwardeeName); ?>">
                                        <input type="hidden" id="awardee_id" name="awardee_id" value="<?php echo htmlspecialchars($data['complaint']['awardee_id']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold mb-3 border-bottom pb-2">Detalles de la Queja</h6>
                                    <div class="mb-3">
                                        <label for="complaint_type" class="form-label">Tipo de Queja</label>
                                        <select class="form-select" id="complaint_type" name="complaint_type" required>
                                            <option value="Suggestion" <?php echo ($data['complaint']['complaint_type'] == 'Suggestion') ? 'selected' : ''; ?>>Sugerencia</option>
                                            <option value="Claim" <?php echo ($data['complaint']['complaint_type'] == 'Claim') ? 'selected' : ''; ?>>Reclamo</option>
                                            <option value="Question" <?php echo ($data['complaint']['complaint_type'] == 'Question') ? 'selected' : ''; ?>>Pregunta</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="complaint_priority" class="form-label">Prioridad</label>
                                        <select class="form-select" id="complaint_priority" name="complaint_priority" required>
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($allowed_priority as $value => $label): ?>
                                                <option value="<?php echo htmlspecialchars($value); ?>"
                                                        <?php echo ($data['complaint']['complaint_priority'] == $value) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($label); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="complaint_description" class="form-label">Descripción</label>
                                        <textarea onkeyup="validarText('complaint_description',8,'errorTextComplaintDescription')" class="form-control" id="complaint_description" name="complaint_description" rows="5" required><?php echo htmlspecialchars($data['complaint']['complaint_description']); ?></textarea>
                                        <div id="errorTextComplaintDescription" style="color: red;"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="complaint_status" class="form-label">Estado de la Queja</label>
                                        <select class="form-select" id="complaint_status" name="complaint_status" required>
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($allowed_status as $value => $label): ?>
                                                <option value="<?php echo htmlspecialchars($value); ?>"
                                                        <?php echo ($data['complaint']['complaint_status'] == $value) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($label); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="internal_observations" class="form-label">Observaciones Internas</label>
                                        <textarea onkeyup="validarText('internal_observations',8,'errorTextInternalObservations')" class="form-control" id="internal_observations" name="internal_observations" rows="3"><?php echo htmlspecialchars($data['complaint']['internal_observations']); ?></textarea>
                                        <div id="errorTextInternalObservations" style="color: red;"></div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-end gap-2">
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="ri-close-line"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line"></i> Actualizar Queja
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function onStallSelected() {
    const input = document.getElementById('stall_search');
    const datalist = document.getElementById('stalls_datalist');
    const option = Array.from(datalist.options).find(opt => opt.value === input.value);
    
    if (option) {
        const stallId = option.getAttribute('data-id');
        document.getElementById('position_id').value = stallId;
        
        // Fetch awardee data
        fetch(`api_complaints.php?getAwardeeByStall=1&stallId=${stallId}`)
            .then(response => response.json())
            .then(data => {
                if (data && data.id) {
                    document.getElementById('awardee_name').value = `${data.first_name} ${data.last_name}`;
                    document.getElementById('awardee_id').value = data.id;
                } else {
                    document.getElementById('awardee_name').value = 'Sin adjudicatario asignado';
                    document.getElementById('awardee_id').value = '';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('awardee_name').value = 'Error al cargar';
            });
    } else {
        document.getElementById('position_id').value = '';
        document.getElementById('awardee_name').value = '';
        document.getElementById('awardee_id').value = '';
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>