<?php
// Este archivo es un componente de vista que se incluirá en el dashboard.
// Se encarga de mostrar las notificaciones de citaciones programadas para hoy.

// Asegúrate de incluir el modelo de la base de datos y el nuevo controlador.

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/CitationsController.php';

// Conectar a la base de datos
$database = new Database();
$db = $database->getConnection();

// Inicializar el controlador de citaciones
$citationsController = new CitationsController();

// Obtener las citaciones para la fecha actual
$todayCitations = $citationsController->getTodayCitations();

if (!empty($todayCitations)): ?>
    <div class="card bg-warning text-white mb-4 shadow">
        <div class="card-body d-flex align-items-center">
            <i class="ri-alert-line me-3" style="font-size: 2.5rem;"></i>
            <div>
                <h5 class="card-title fw-bold mb-1">Citas programadas para hoy</h5>
                <p class="card-text">Tienes <strong class="fs-5"><?php echo count($todayCitations); ?></strong> citación(es) prevista(s) para el día de hoy.</p>
            </div>
            <a href="#today-citations-list" class="btn btn-light ms-auto d-none d-md-block">
                Ver detalles <i class="ri-arrow-right-s-line"></i>
            </a>
        </div>
    </div>
    
    <div class="card mb-4" id="today-citations-list">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0">Detalles de Citaciones de Hoy</h6>
            <span class="badge bg-primary rounded-pill"><?php echo count($todayCitations); ?> Cita(s)</span>
        </div>
        <ul class="list-group list-group-flush">
            <?php foreach ($todayCitations as $citation): ?>
                <li class="list-group-item">
                    <div class="d-flex w-100 justify-content-between">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($citation['infraction_description']); ?></h6>
                            <p class="mb-1 text-muted">Ubicación: <?php echo htmlspecialchars($citation['location']); ?></p>
                            <small class="text-muted">Mediador: <?php echo htmlspecialchars($citation['mediator_name']); ?></small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-secondary mb-1">
                                <?php echo date('h:i A', strtotime($citation['citation_datetime'])); ?>
                            </span>
                            <div class="mt-2">
                                <a href="view_citation.php?id=<?php echo htmlspecialchars($citation['citation_id']); ?>" class="btn btn-sm btn-outline-info">
                                    <i class="ri-eye-line"></i> Ver
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
