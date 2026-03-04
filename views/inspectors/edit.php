<?php
// Vista para editar un inspector existente

session_start();

// Incluir el controlador necesario
require_once __DIR__ . '/../../controllers/InspectorsController.php';

$inspectorsController = new InspectorsController();

// Manejar la solicitud POST para actualizar el inspector
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'inspector_id' => $_POST['inspector_id'] ?? null,
        'inspector_code' => trim($_POST['inspector_code'] ?? ''),
        'full_name' => trim($_POST['full_name'] ?? ''),
        'phone_number' => trim($_POST['phone_number'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'is_active' => (bool) ($_POST['is_active'] ?? 1),
    ];

    $result = $inspectorsController->update($data['inspector_id'], $data);

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
        header("Location: edit.php?id=" . urlencode($data['inspector_id']));
        exit;
    }
}

// Cargar el inspector y datos para el formulario
$inspectorId = isset($_GET['id']) ? (int) $_GET['id'] : null;
if (!$inspectorId) {
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'ID de inspector no válido.'
    ];
    header('Location: index.php');
    exit;
}

$data = $inspectorsController->edit($inspectorId);

if (!$data['success']) {
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => $data['message']
    ];
    header('Location: index.php');
    exit;
}

$inspector = $data['inspector'];
$page_title = $data['page_title'];

// Incluir header y layouts
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
                                    <li class="breadcrumb-item"><a href="index.php">Inspectores</a></li>
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
                            <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: '<?php echo $_SESSION['flash_message']['type'] === 'success' ? 'success' : 'error'; ?>',
                                    title: '<?php echo addslashes($_SESSION['flash_message']['message']); ?>',
                                    showConfirmButton: false,
                                    timer: 4000,
                                    timerProgressBar: true,
                                    width: '450px'
                                });
                            });
                            </script>
                            <?php unset($_SESSION['flash_message']); ?>
                        <?php endif; ?>

                        <form method="POST" action="edit.php">
                            <input type="hidden" name="inspector_id"
                                value="<?php echo htmlspecialchars($inspector['inspector_id']); ?>">

                            <div class="mb-3">
                                <label for="inspector_code_display" class="form-label">Código de Inspector</label>
                                <input type="text" class="form-control" id="inspector_code_display" 
                                    value="<?php echo htmlspecialchars($inspector['inspector_code']); ?>" disabled>
                                <input type="hidden" name="inspector_code" value="<?php echo htmlspecialchars($inspector['inspector_code']); ?>">
                                <small class="text-muted">El código de inspector no puede ser modificado.</small>
                            </div>
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Nombre Completo <span
                                        class="text-danger">*</span></label>
                                <input onKeyup="validarNombre('full_name')" type="text" class="form-control"
                                    id="full_name" name="full_name"
                                    value="<?php echo htmlspecialchars($inspector['full_name']); ?>" required>
                                <div id="errorNombre" style="color: red;"></div>
                            </div>
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Número de Teléfono</label>
                                <input onKeyup="validarTelefono('phone_number')" type="text" class="form-control"
                                    id="phone_number" name="phone_number"
                                    value="<?php echo htmlspecialchars($inspector['phone_number']); ?>">
                                <div id="errorTelefono" style="color: red;"></div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input onKeyup="validarEmail('email')" type="email" class="form-control" id="email"
                                    name="email" value="<?php echo htmlspecialchars($inspector['email']); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="is_active" class="form-label">Estado</label>
                                <select class="form-select" id="is_active" name="is_active" required>
                                    <option value="1" <?php echo ($inspector['is_active'] == 1) ? 'selected' : ''; ?>>
                                        Activo</option>
                                    <option value="0" <?php echo ($inspector['is_active'] == 0) ? 'selected' : ''; ?>>
                                        Inactivo</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line"></i> Actualizar Inspector
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>