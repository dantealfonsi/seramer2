<?php
session_start();
require_once __DIR__ . '/../../controllers/SanctionTypesController.php';

$sanctionTypesController = new SanctionTypesController();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'ID de tipo de sanción no válido.'
    ];
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];
$result = $sanctionTypesController->view($id);
extract($result);

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
                            <i class="ri-eye-line me-1" style="font-size: 2rem;background: #837aff;color: white;font-weight: 100 !important;padding: .24rem;border-radius: .7rem;"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                    </div>
                    
                    <div class="card-body">
                        <?php if (!$success): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <p class="form-label"><strong>ID:</strong></p>
                                    <p><?php echo htmlspecialchars($sanction_type['sanction_type_id']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="form-label"><strong>Nombre del Tipo de Sanción:</strong></p>
                                    <p><?php echo htmlspecialchars($sanction_type['sanction_type_name']); ?></p>
                                </div>
                                <div class="col-12">
                                    <p class="form-label"><strong>Descripción:</strong></p>
                                    <p><?php echo htmlspecialchars($sanction_type['description']); ?></p>
                                </div>
                            </div>
                            <div class="col-12 mt-4">
                                <a href="edit.php?id=<?php echo htmlspecialchars($sanction_type['sanction_type_id']); ?>" class="btn btn-warning me-2">
                                    <i class="ri-edit-line"></i> Editar
                                </a>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="ri-arrow-go-back-line"></i> Regresar
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
