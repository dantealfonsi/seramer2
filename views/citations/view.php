<?php
session_start();
require_once __DIR__ . '/../../controllers/CitationsController.php';

$citationsController = new CitationsController();
$page_title = 'Detalles de la Citación';

$citation_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (!$citation_id) {
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'ID de citación no especificado.'
    ];
    header("Location: index.php");
    exit;
}

$citation = $citationsController->getById($citation_id);

if (!$citation) {
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'message' => 'Citación no encontrada.'
    ];
    header("Location: index.php");
    exit;
}

// Obtener las listas de infracciones y mediadores para mostrar nombres en lugar de IDs
$infractions = $citationsController->getInfractionsList();
$mediators = $citationsController->getMediatorsList();

// Mapear los IDs a nombres
$infractionDescription = 'No disponible';
foreach ($infractions as $infraction) {
    if ($infraction['infraction_id'] == $citation['infraction_id']) {
        $infractionDescription = $infraction['infraction_description'];
        break;
    }
}

$mediatorName = 'No disponible';
foreach ($mediators as $mediator) {
    if ($mediator['inspector_id'] == $citation['mediator_user_id']) {
        $mediatorName = $mediator['full_name'];
        break;
    }
}

// Opciones en español para el select de estado de citación
$allowed_status = [
    'Scheduled' => 'Programada',
    'Rescheduled' => 'Reprogramada',
    'Completed' => 'Completada',
    'Canceled' => 'Cancelada'
];

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
                        <div class="btn-group" role="group">
                            <a href="edit.php?id=<?php echo htmlspecialchars($citation['citation_id']); ?>" class="btn btn-warning" title="Editar"><i class="ri-edit-line me-1"></i>Editar</a>
                            <a href="index.php" class="btn btn-secondary" title="Volver a la lista"><i class="ri-arrow-left-line me-1"></i>Volver</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- ID Citación -->
                            <div class="col-md-6">
                                <label class="form-label">ID Citación</label>
                                <p class="form-control-static fw-bold">#<?php echo htmlspecialchars($citation['citation_id']); ?></p>
                            </div>

                            <!-- Infracción -->
                            <div class="col-md-6">
                                <label class="form-label">Infracción Asociada</label>
                                <p class="form-control-static"><?php echo htmlspecialchars($infractionDescription); ?></p>
                            </div>

                            <!-- Fecha y Hora -->
                            <div class="col-md-6">
                                <label class="form-label">Fecha y Hora</label>
                                <p class="form-control-static"><?php echo (new DateTime($citation['citation_datetime']))->format('d/m/Y H:i'); ?></p>
                            </div>
                            
                            <!-- Ubicación -->
                            <div class="col-md-6">
                                <label class="form-label">Ubicación</label>
                                <p class="form-control-static"><?php echo htmlspecialchars($citation['location']); ?></p>
                            </div>
                            
                            <!-- Mediador -->
                            <div class="col-md-6">
                                <label class="form-label">Mediador</label>
                                <p class="form-control-static"><?php echo htmlspecialchars($mediatorName); ?></p>
                            </div>
                            
                            <!-- Estado -->
                            <div class="col-md-6">
                                <label class="form-label">Estado</label>
                                <p class="form-control-static">
                                    <?php
                                    $status_colors = ['Scheduled' => 'primary', 'Rescheduled' => 'info', 'Completed' => 'success', 'Canceled' => 'dark'];
                                    $s_color = $status_colors[$citation['citation_status']] ?? 'light';
                                    ?>
                                    <span class="badge bg-<?php echo $s_color; ?>"><?php echo htmlspecialchars($allowed_status[$citation['citation_status']]); ?></span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
