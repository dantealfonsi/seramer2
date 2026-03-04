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

<?php if (isset($_SESSION['flash_message'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: '<?php echo $_SESSION['flash_message']['type'] === 'success' || $_SESSION['flash_message']['type'] === 'primary' ? 'success' : 'error'; ?>',
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

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="card-title d-flex align-items-center mb-1" style="font-size: 1.4rem;font-weight: 600;">
                                <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="ri-file-search-line" style="color: #696cff; font-size: 1.5rem;"></i></div>
                                <?php echo htmlspecialchars($page_title); ?>
                            </h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="index.php">Citaciones</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Detalles</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="btn-group" role="group">
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="ri-arrow-left-line"></i> Volver al listado
                            </a>
                            <a href="edit.php?id=<?php echo htmlspecialchars($citation['citation_id']); ?>" class="btn btn-warning">
                                <i class="ri-edit-2-line"></i> Editar
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- ID Citación -->
                            <div class="col-md-6">
                                <label class="form-label">ID Citación</label>
                                <p class="form-control-static fw-bold">#<?php echo htmlspecialchars($citation['citation_id']); ?></p>
                            </div>

                            <!-- Puesto -->
                            <div class="col-md-6">
                                <label class="form-label">Puesto / Local</label>
                                <p class="form-control-static">
                                    <span class="badge bg-light text-dark border">
                                        <?php echo htmlspecialchars($citation['stall_number'] ?? 'N/A'); ?>
                                    </span>
                                </p>
                            </div>

                            <!-- Adjudicatario -->
                            <div class="col-md-6">
                                <label class="form-label">Adjudicatario</label>
                                <p class="form-control-static"><?php echo htmlspecialchars($citation['awardee_full_name'] ?? 'N/A'); ?></p>
                            </div>

                            <!-- Infracción -->
                            <div class="col-md-6">
                                <label class="form-label">Infracción Asociada</label>
                                <p class="form-control-static"><?php echo htmlspecialchars($citation['infraction_description'] ?? 'No disponible'); ?></p>
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
                                    $status_colors = [
                                        'Scheduled' => 'primary', 
                                        'Rescheduled' => 'info', 
                                        'Completed' => 'success', 
                                        'Canceled' => 'dark', 
                                        'Resuelta' => 'success', 
                                        'In Process' => 'warning'
                                    ];
                                    $s_color = $status_colors[$citation['citation_status']] ?? 'secondary';
                                    
                                    // Traducción manual si no está en el array allowed_status
                                    $display_status = $allowed_status[$citation['citation_status']] ?? $citation['citation_status'];
                                    if ($citation['citation_status'] === 'Resuelta') $display_status = 'Resuelta';
                                    if ($citation['citation_status'] === 'In Process') $display_status = 'En Proceso';
                                    ?>
                                    <span class="badge bg-<?php echo $s_color; ?>"><?php echo htmlspecialchars($display_status); ?></span>
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
