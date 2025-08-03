<?php
// Vista de edición/creación de infracciones

session_start();

// Incluir el controlador y el modelo para cargar los datos de las listas
require_once __DIR__ . '/../../controllers/InfractionsController.php';
require_once __DIR__ . '/../../models/Adjudicator.php';
require_once __DIR__ . '/../../models/Stall.php';
require_once __DIR__ . '/../../models/InfractionType.php';

$infractionsController = new InfractionsController();

$id = $_GET['id'] ?? null;
$is_edit = !empty($id);
$infraction = null;
$page_title = 'Registrar Nueva Infracción';
$errors = [];
$form_data = [
    'adjudicatory_id' => '',
    'stall_id' => '',
    'infraction_type_id' => '',
    'infraction_date' => date('Y-m-d'), // Fecha por defecto
    'status' => 'Reported' // Estado por defecto
];

// Cargar las listas de selección para los campos del formulario
$adjudicators = Adjudicator::getAll(); // Asumiendo que existe un método para obtenerlos
$stalls = Stall::getAll(); // Asumiendo que existe un método para obtenerlos
$infraction_types = InfractionType::getAll(); // Asumiendo que existe un método para obtenerlos

// Si estamos editando, obtener los datos de la infracción
if ($is_edit) {
    $result = $infractionsController->edit($id);
    
    if (!$result['success']) {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => $result['message']
        ];
        header('Location: index.php');
        exit;
    }
    
    $infraction = $result['infraction'];
    $page_title = $result['page_title'];
    $form_data['adjudicatory_id'] = $infraction['adjudicatory_id'];
    $form_data['stall_id'] = $infraction['stall_id'];
    $form_data['infraction_type_id'] = $infraction['infraction_type_id'];
    $form_data['infraction_date'] = $infraction['infraction_date'];
    $form_data['status'] = $infraction['status'];
}

// Procesar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = [
        'adjudicatory_id' => trim($_POST['adjudicatory_id'] ?? ''),
        'stall_id' => trim($_POST['stall_id'] ?? ''),
        'infraction_type_id' => trim($_POST['infraction_type_id'] ?? ''),
        'infraction_date' => trim($_POST['infraction_date'] ?? ''),
        'status' => trim($_POST['status'] ?? '')
    ];
    
    if ($is_edit) {
        $result = $infractionsController->update($id, $form_data);
    } else {
        $result = $infractionsController->store($form_data);
    }
    
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
                        <li class="breadcrumb-item"><a href="index.php">Infracciones</a></li>
                        <?php if ($is_edit): ?>
                        <li class="breadcrumb-item">
                            <a href="view.php?id=<?php echo $infraction['id']; ?>">Infracción #<?php echo htmlspecialchars($infraction['id']); ?></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Editar</li>
                        <?php else: ?>
                        <li class="breadcrumb-item active" aria-current="page">Registrar Nuevo</li>
                        <?php endif; ?>
                    </ol>
                </nav>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="ri-alert-line me-1"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="<?php echo $is_edit ? 'view.php?id=' . $infraction['id'] : 'index.php'; ?>" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i> 
                            <?php echo $is_edit ? 'Volver a detalles' : 'Volver al listado'; ?>
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

                        <form method="POST" novalidate>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="adjudicatory_id" class="form-label">
                                            Adjudicatario <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="adjudicatory_id" name="adjudicatory_id" required>
                                            <option value="">Seleccione un adjudicatario</option>
                                            <?php foreach ($adjudicators as $adj): ?>
                                            <option value="<?php echo htmlspecialchars($adj['id']); ?>" 
                                                    <?php echo ($form_data['adjudicatory_id'] == $adj['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($adj['name'] . ' ' . $adj['lastname']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="stall_id" class="form-label">
                                            Puesto <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="stall_id" name="stall_id" required>
                                            <option value="">Seleccione un puesto</option>
                                            <?php foreach ($stalls as $stall): ?>
                                            <option value="<?php echo htmlspecialchars($stall['id']); ?>"
                                                    <?php echo ($form_data['stall_id'] == $stall['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($stall['name'] . ' (' . $stall['code'] . ')'); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="infraction_type_id" class="form-label">
                                            Tipo de Infracción <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="infraction_type_id" name="infraction_type_id" required>
                                            <option value="">Seleccione un tipo de infracción</option>
                                            <?php foreach ($infraction_types as $type): ?>
                                            <option value="<?php echo htmlspecialchars($type['id']); ?>"
                                                    <?php echo ($form_data['infraction_type_id'] == $type['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($type['name']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="infraction_date" class="form-label">
                                            Fecha de la Infracción <span class="text-danger">*</span>
                                        </label>
                                        <input type="date"
                                               class="form-control"
                                               id="infraction_date"
                                               name="infraction_date"
                                               value="<?php echo htmlspecialchars($form_data['infraction_date']); ?>"
                                               required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="status" class="form-label">
                                            Estado <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="status" name="status" required <?php echo !$is_edit ? 'disabled' : ''; ?>>
                                            <option value="Reported" <?php echo ($form_data['status'] == 'Reported') ? 'selected' : ''; ?>>Reportada</option>
                                            <option value="In Process" <?php echo ($form_data['status'] == 'In Process') ? 'selected' : ''; ?>>En Proceso</option>
                                            <option value="Resolved" <?php echo ($form_data['status'] == 'Resolved') ? 'selected' : ''; ?>>Resuelta</option>
                                            <option value="Cancelled" <?php echo ($form_data['status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelada</option>
                                        </select>
                                        <?php if (!$is_edit): ?>
                                        <div class="form-text">
                                            El estado inicial es "Reportada" y no se puede cambiar al crear.
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($is_edit): ?>
                                    <div class="mb-3">
                                        <label for="details" class="form-label">Detalles (opcional)</label>
                                        <textarea class="form-control" id="details" name="details" rows="3"><?php echo htmlspecialchars($infraction['details'] ?? ''); ?></textarea>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?php echo $is_edit ? 'view.php?id=' . $infraction['id'] : 'index.php'; ?>" 
                                   class="btn btn-outline-secondary">
                                    <i class="ri-close-line"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-<?php echo $is_edit ? 'warning' : 'primary'; ?>">
                                    <i class="ri-<?php echo $is_edit ? 'save' : 'add'; ?>-line"></i> 
                                    <?php echo $is_edit ? 'Actualizar Infracción' : 'Registrar Infracción'; ?>
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
    // Se podría agregar validación del lado del cliente aquí, similar a la del ejemplo de cargos.
    // Por simplicidad, se omite en esta respuesta, pero debería implementarse.
    // document.querySelector('form').addEventListener('submit', function(e) { ... });
</script>