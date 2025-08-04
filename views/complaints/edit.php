<?php
require_once __DIR__ . '/../../controllers/ComplaintsController.php';

$complaintsController = new ComplaintsController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'complaint_id' => $_POST['complaint_id'] ?? null,
        'client_name' => $_POST['client_name'] ?? '',
        'client_phone' => $_POST['client_phone'] ?? '',
        'client_email' => $_POST['client_email'] ?? '',
        'complaint_description' => $_POST['complaint_description'] ?? '',
        'complaint_type' => $_POST['complaint_type'] ?? '',
        'complaint_status' => $_POST['complaint_status'] ?? 'Received',
        'complaint_priority' => $_POST['complaint_priority'] ?? 'Medium',
        'internal_observations' => $_POST['internal_observations'] ?? ''
    ];

    $result = $complaintsController->update($_POST['complaint_id'],$data);    

    if (isset($result['success']) && $result['success']) {
        $ruta = "Location: {$result['redirect']}";
        header($ruta);
        exit;
    } else {
        header ("Location: edit.php?id={$_POST['complaint_id']}&error=" . urlencode($result['message']));
    }
}
// Asegúrate de incluir el layout principal
require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';

$complaintId = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$complaintId) {
    header('Location: index.php?error=invalid_id');
    exit;
}
$data = $complaintsController->edit($complaintId);

?>

<div class="main-content">
    <div class="card">
        <div class="card-header">
            <h1><?php echo htmlspecialchars($data['page_title']); ?></h1>
        </div>
        <div class="card-body">
            <?php if (isset($data['errors'])): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($data['errors'] as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="edit.php?id=<?php echo htmlspecialchars($data['complaint']['complaint_id']); ?>" method="POST">
                <input type="hidden" name="complaint_id" value="<?php echo htmlspecialchars($data['complaint']['complaint_id']); ?>">

                <div class="form-group mb-3">
                    <label for="client_name">Nombre del Cliente</label>
                    <input type="text" class="form-control" id="client_name" name="client_name" 
                           value="<?php echo htmlspecialchars($data['complaint']['client_name']); ?>" required>
                </div>

                <div class="form-group mb-3">
                    <label for="client_email">Email del Cliente</label>
                    <input type="email" class="form-control" id="client_email" name="client_email" 
                           value="<?php echo htmlspecialchars($data['complaint']['client_email']); ?>" required>
                </div>
                
                <div class="form-group mb-3">
                    <label for="client_phone">Teléfono</label>
                    <input type="text" class="form-control" id="client_phone" name="client_phone" 
                           value="<?php echo htmlspecialchars($data['complaint']['client_phone']); ?>">
                </div>

                <div class="form-group mb-3">
                    <label for="complaint_type">Tipo de Queja</label>
                    <select class="form-control" id="complaint_type" name="complaint_type" required>
                        <option value="Suggestion" <?php echo ($data['complaint']['complaint_type'] == 'Suggestion') ? 'selected' : ''; ?>>Sugerencia</option>
                        <option value="Claim" <?php echo ($data['complaint']['complaint_type'] == 'Claim') ? 'selected' : ''; ?>>Reclamo</option>
                        <option value="Question" <?php echo ($data['complaint']['complaint_type'] == 'Question') ? 'selected' : ''; ?>>Pregunta</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label for="complaint_status">Estado</label>
                    <select class="form-control" id="complaint_status" name="complaint_status" required>
                        <option value="Received" <?php echo ($data['complaint']['complaint_status'] == 'Received') ? 'selected' : ''; ?>>Recibida</option>
                        <option value="In Process" <?php echo ($data['complaint']['complaint_status'] == 'In Process') ? 'selected' : ''; ?>>En Proceso</option>
                        <option value="Resolved" <?php echo ($data['complaint']['complaint_status'] == 'Resolved') ? 'selected' : ''; ?>>Resuelta</option>
                        <option value="Closed" <?php echo ($data['complaint']['complaint_status'] == 'Closed') ? 'selected' : ''; ?>>Cerrada</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label for="complaint_status">Prioridad</label>
                    <select class="form-control" id="complaint_priority" name="complaint_priority" required>
                        <option value="Low" <?php echo ($data['complaint']['complaint_priority'] == 'Low') ? 'selected' : ''; ?>>Baja</option>
                        <option value="Medium" <?php echo ($data['complaint']['complaint_priority'] == 'Medium') ? 'selected' : ''; ?>>Media</option>
                        <option value="High" <?php echo ($data['complaint']['complaint_priority'] == 'High') ? 'selected' : ''; ?>>Alta</option>
                        <option value="Urgent" <?php echo ($data['complaint']['complaint_priority'] == 'Urgent') ? 'selected' : ''; ?>>Urgente</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label for="complaint_description">Descripción</label>
                    <textarea class="form-control" id="complaint_description" name="complaint_description" rows="5" required><?php echo htmlspecialchars($data['complaint']['complaint_description']); ?></textarea>
                </div>
                <div class="form-group mb-3">
                    <label for="complaint_description">Observaciones Internas</label>
                    <textarea class="form-control" id="internal_observations" name="internal_observations" rows="5" required><?php echo htmlspecialchars($data['complaint']['internal_observations']); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Actualizar Queja</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>