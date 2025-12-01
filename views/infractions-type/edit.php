<?php
session_start();
require_once __DIR__ . '/../../controllers/InfractionTypesController.php';

$infractionTypesController = new InfractionTypesController();
$page_title = 'Editar Tipo de Infracción';

// Manejar la solicitud de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['infraction_type_id'] ?? null;
    if ($id) {
        $result = $infractionTypesController->update($id, $_POST);

        $_SESSION['flash_message'] = [
            'type' => $result['success'] ? 'success' : 'danger',
            'message' => $result['message']
        ];
    } else {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => 'Error: ID del tipo de infracción no proporcionado.'
        ];
    }

    header("Location: index.php");
    exit;
}

// Obtener los datos del tipo de infracción para precargar el formulario
$infractionType = null;
if (isset($_GET['id'])) {
    $infractionType = $infractionTypesController->getById($_GET['id']);
    if (!$infractionType) {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => 'Tipo de infracción no encontrado.'
        ];
        header("Location: index.php");
        exit;
    }
} else {
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'ID de tipo de infracción no especificado.'
    ];
    header("Location: index.php");
    exit;
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
                            <i class="ri-edit-line me-1"
                                style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i> Volver a Tipos de Infracción
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['flash_message'])): ?>
                            <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?> mt-2" role="alert">
                                <?php echo htmlspecialchars($_SESSION['flash_message']['message']); ?>
                            </div>
                            <?php unset($_SESSION['flash_message']); ?>
                        <?php endif; ?>

                        <form method="POST" action="edit.php">
                            <input type="hidden" name="infraction_type_id"
                                value="<?php echo htmlspecialchars($infractionType['infraction_type_id']); ?>">
                            <div class="mb-3">
                                <label for="infraction_type_name" class="form-label">Nombre del Tipo de
                                    Infracción</label>
                                <input onKeyup="validarText('infraction_type_name',3,'errorTextInfractionTypeName')"
                                    type="text" class="form-control" id="infraction_type_name"
                                    name="infraction_type_name"
                                    value="<?php echo htmlspecialchars($infractionType['infraction_type_name']); ?>"
                                    required>
                                <div id="errorTextInfractionTypeName" style="color: red;"></div>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Descripción</label>
                                <textarea onKeyup="validarText('description',8,'errorTextDescription')"
                                    class="form-control" id="description" name="description"
                                    rows="3"><?php echo htmlspecialchars($infractionType['description']); ?></textarea>
                                <div id="errorTextDescription" style="color: red;"></div>
                            </div>
                            <div class="mb-3">
                                <label for="violated_article" class="form-label">Artículo Violado</label>
                                <input onKeyup="validarText('violated_article',3,'errorTextViolatedArticle')"
                                    type="text" class="form-control" id="violated_article" name="violated_article"
                                    value="<?php echo htmlspecialchars($infractionType['violated_article']); ?>">
                                <div id="errorTextViolatedArticle" style="color: red;"></div>
                            </div>

                            <button type="submit" class="btn btn-warning">
                                <i class="ri-save-line"></i> Actualizar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>