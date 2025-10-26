<?php
session_start();
require_once __DIR__ . '/../../controllers/InfractionTypesController.php';

$infractionTypesController = new InfractionTypesController();
$page_title = 'Crear Nuevo Tipo de Infracción';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $infractionTypesController->create($_POST);
    
    $_SESSION['flash_message'] = [
        'type' => $result['success'] ? 'success' : 'danger',
        'message' => $result['message']
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
                            <i class="ri-add-line me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
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

                        <form method="POST" action="create.php">
                            <div class="mb-3">
                                <label for="infraction_type_name" class="form-label">Nombre del Tipo de Infracción</label>
                                <input type="text" class="form-control" id="infraction_type_name" name="infraction_type_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Descripción</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="violated_article" class="form-label">Artículo Violado</label>
                                <input type="text" class="form-control" id="violated_article" name="violated_article">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line"></i> Guardar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
