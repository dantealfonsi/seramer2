<?php
require_once __DIR__ . '/../../controllers/ComplaintsController.php';
$complaintsController = new ComplaintsController();
$data = $complaintsController->create();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'client_user_id' => $_SESSION['user_id'] ?? null,
        'contractor_id' => $_SESSION['contractor_id'] ?? null, // si aplica
        'client_name' => $_POST['client_name'] ?? '',
        'client_phone' => $_POST['client_phone'] ?? '',
        'client_email' => $_POST['client_email'] ?? '',
        'complaint_description' => $_POST['complaint_description'] ?? '',
        'position_id' => $_POST['position_id'] ?? null,
        'complaint_type' => $_POST['complaint_type'] ?? '',
        'complaint_status' => $_POST['complaint_status'] ?? 'Received',
        'complaint_priority' => $_POST['complaint_priority'] ?? 'Medium',
        'internal_observations' => $_POST['internal_observations'] ?? ''
    ];

    $result = $complaintsController->store($data);    

    if (isset($result['success']) && $result['success']) {
        $ruta = "Location: {$result['redirect']}";
        header($ruta);
        exit;
    } else {
        $data['errors'] = $result['errors'];
    }
}

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
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

?>

<div class="main-content">
    <div class="card">
        <div class="card-header">
            <h1><?php echo htmlspecialchars($data['page_title']); ?></h1>
        </div>
        <div class="card-body">
            <!-- Muestra los errores de validación si existen -->
            <?php if (isset($data['errors'])): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($data['errors'] as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="create.php" method="POST">
                <!-- Campos del formulario -->
                <div class="form-group mb-3">
                    <label for="client_name">Nombre del Cliente</label>
                    <input type="text" class="form-control" id="client_name" name="client_name" required>
                </div>

                <div class="form-group mb-3">
                    <label for="client_email">Email del Cliente</label>
                    <input type="email" class="form-control" id="client_email" name="client_email" required>
                </div>
                
                <div class="form-group mb-3">
                    <label for="client_phone">Teléfono</label>
                    <input type="text" class="form-control" id="client_phone" name="client_phone">
                </div>

                <div class="form-group mb-3">
                    <label for="complaint_type">Tipo de Queja</label>
                    <select class="form-control" id="complaint_type" name="complaint_type" required>
                        <option value="">Seleccione...</option>
                        <option value="Suggestion">Sugerencia</option>
                        <option value="Claim">Reclamo</option>
                        <option value="Question">Pregunta</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label for="complaint_description">Descripción</label>
                    <textarea class="form-control" id="complaint_description" name="complaint_description" rows="5" required></textarea>
                </div>

                <div class="form-group mb-3">
                    <label for="position_id">Puesto del Mercado</label>
                    <select class="form-control" id="position_id" name="position_id">
                        <option value="">Seleccione...</option>
                        <?php foreach ($data['market_stalls'] as $position): ?>
                            <option value="<?php echo htmlspecialchars($position['id_stall']); ?>">
                                <?php echo htmlspecialchars($position['stall_code']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">Opcional: ID de la posición de mercado asociada.</small>
                </div>

                <div class="form-group mb-3">
                    <label for="internal_observations">Observaciones Internas</label>
                    <textarea class="form-control" id="internal_observations" name="internal_observations" rows="3"></textarea>
                </div>

                <div class="form-group mb-3">
                    <label for="position_id">Estado de la Queja</label>
                    <select class="form-control" id="complaint_status" name="complaint_status" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($allowed_status as $value => $label): ?>
                            <option value="<?php echo htmlspecialchars($value); ?>">
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">Nivel de Proceso en la negociacion</small>
                </div>
                <div class="form-group mb-3">
                    <label for="position_id">Prioridad</label>                
                    <select class="form-control" id="complaint_priority" name="complaint_priority" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($allowed_priority as $value => $label): ?>
                            <option value="<?php echo htmlspecialchars($value); ?>">
                                <?php echo htmlspecialchars($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">Prioridad de Avance en la negociacion</small>
                </div>                    

                <button type="submit" class="btn btn-primary">Registrar Queja</button>
                <a href="/complaints/index" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>