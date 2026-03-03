<?php
// Vista para crear un nuevo inspector

session_start();
// Incluir el controlador necesario
require_once __DIR__ . '/../../controllers/InspectorsController.php';

$inspectorsController = new InspectorsController();
$result = $inspectorsController->create();

// Manejar la solicitud POST para crear el inspector
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'inspector_code' => trim($_POST['inspector_code'] ?? ''),
        'full_name' => trim($_POST['full_name'] ?? ''),
        'phone_number' => trim($_POST['phone_number'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'hire_date' => $_POST['hire_date'] ?? null,
        'is_active' => 1, // Nuevo inspector activo por defecto
    ];

    $result = $inspectorsController->store($data);

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
        header("Location: create.php");
        exit;
    }
}

$page_title = $result['page_title'] ?? "Crear Nuevo Inspector";
$users = $result['users'] ?? [];

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
                                <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="ri-user-add-line" style="color: #696cff; font-size: 1.5rem;"></i></div>
                                <?php echo htmlspecialchars($page_title); ?>
                            </h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="index.php">Inspectores</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Crear Inspector</li>
                                </ol>
                            </nav>
                        </div>
                        <a href="index.php" class="btn btn-outline-secondary">
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

                        <form action="create.php" method="POST">
                            <div class="mb-3">
                                <label for="inspector_code" class="form-label">Código de Inspector <span
                                        class="text-danger">*</span></label>
                                <input onKeyup="validarLocation('inspector_code', 3)" type="text" class="form-control"
                                    id="inspector_code" name="inspector_code" required>
                                <div id="errorTextLocation" style="color: red;"></div>
                            </div>
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Nombre Completo <span
                                        class="text-danger">*</span></label>
                                <input onKeyup="validarNombre('full_name')" type="text" class="form-control"
                                    id="full_name" name="full_name" required>
                                <div id="errorNombre" style="color: red;"></div>
                            </div>
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">Número de Teléfono</label>
                                <input onKeyup="validarTelefono('phone_number')" type="text" class="form-control"
                                    id="phone_number" name="phone_number">
                                <div id="errorTelefono" style="color: red;"></div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input onKeyup="validarEmail('email')" type="email" class="form-control" id="email"
                                    name="email">
                                <div id="errorEmail" style="color: red;"></div>
                            </div>
                            <div class="mb-3">
                                <label for="hire_date" class="form-label">Fecha de Contratación</label>
                                <input type="date" class="form-control" id="hire_date" name="hire_date">
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line"></i> Guardar Inspector
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>