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
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Inspectores</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Editar</li>
                    </ol>
                </nav>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0" style="font-size: 2rem;font-weight: 600;">
                            <i class="ri-edit-line me-1"
                                style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i> Volver al listado
                        </a>
                    </div>

                    <div class="card-body">
                        <?php
                        if (isset($_SESSION['flash_message'])) {
                            $alert_type = $_SESSION['flash_message']['type'] === 'success' ? 'success' : 'danger';
                            echo '<div class="alert alert-' . $alert_type . ' alert-dismissible fade show" role="alert">';
                            echo htmlspecialchars($_SESSION['flash_message']['message']);
                            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                            echo '</div>';
                            unset($_SESSION['flash_message']);
                        }
                        ?>

                        <form method="POST" action="edit.php">
                            <input type="hidden" name="inspector_id"
                                value="<?php echo htmlspecialchars($inspector['inspector_id']); ?>">

                            <div class="mb-3">
                                <label for="inspector_code" class="form-label">Código de Inspector <span
                                        class="text-danger">*</span></label>
                                <input onKeyup="validarLocation('inspector_code', 3)" type="text" class="form-control"
                                    id="inspector_code" name="inspector_code"
                                    value="<?php echo htmlspecialchars($inspector['inspector_code']); ?>" required>
                                <div id="errorTextLocation" style="color: red;"></div>
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