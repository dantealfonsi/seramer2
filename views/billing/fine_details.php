<?php
// Vista de detalles de una sanción (Versión Facturación)
session_start();
require_once __DIR__ . '/../../controllers/SanctionsController.php';

$sanctionsController = new SanctionsController();
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: fines.php');
    exit;
}

$result = $sanctionsController->view($id);

if (!$result['success']) {
    header('Location: fines.php');
    exit;
}

$sanction = $result['sanction'];
$page_title = 'Detalle de Multa #' . $sanction['sanction_id'];

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';

$allowed_sanction_status = [
    'Imposed' => 'Impuesta',
    'Paid' => 'Pagada',
    'Pending' => 'Pendiente',
    'Canceled' => 'Cancelada'
];

$fullName = ($sanction['first_name'] ?? '') . ' ' . ($sanction['last_name'] ?? '');
?>

<style>
</style>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="fines.php">Gestión de Multas</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detalles de Multa</li>
                    </ol>
                </nav>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 card-title d-flex align-items-center" style="font-size: 1.4rem;font-weight: 600;">
                            <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="ri-file-list-3-line" style="color: #696cff; font-size: 1.5rem;"></i></div>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <div class="btn-group" role="group">
                            <a href="fines.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Volver al listado
                            </a>
                            <?php if ($sanction['sanction_status'] !== 'Paid'): ?>
                                <a href="receivable.php?search_term=<?php echo urlencode($sanction['id_number'] ?? ''); ?>&search_type=id_number" class="btn btn-success">
                                    <i class="ri-money-dollar-circle-line"></i> Procesar Pago
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Información del Contribuyente</h6>
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <th width="40%">Contribuyente:</th>
                                            <td><?php echo htmlspecialchars($fullName); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Cédula/RIF:</th>
                                            <td><?php echo htmlspecialchars($sanction['id_number'] ?? 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Teléfono:</th>
                                            <td><?php echo htmlspecialchars($sanction['phone'] ?? 'N/A'); ?></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <h6 class="fw-bold mb-3 mt-4">Detalles de la Infracción</h6>
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <th width="40%">Infracción:</th>
                                            <td>
                                                <span class="badge bg-primary fs-6">
                                                    <?php echo htmlspecialchars($sanction['infraction_description']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Severidad:</th>
                                            <td>
                                                <?php 
                                                $severityRaw = strtolower($sanction['severity_name'] ?? '');
                                                $severity = ucfirst($severityRaw);
                                                $badgeClass = 'bg-info'; 
                                                if ($severityRaw === 'moderada') $badgeClass = 'bg-warning';
                                                if ($severityRaw === 'grave') $badgeClass = 'bg-danger';
                                                ?>
                                                <span class="badge <?php echo $badgeClass; ?> fs-6">
                                                    <?php echo htmlspecialchars($severity); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Fecha de Imposición:</th>
                                            <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($sanction['imposition_date']))); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Usuario Responsable:</th>
                                            <td><?php echo htmlspecialchars($sanction['imposed_by_user_id']); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-label-secondary border-0 mb-4">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 fw-bold text-dark"><i class="ri-money-dollar-circle-line"></i> Estado Financiero</h6>
                                        
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-semibold">Monto de Multa:</span>
                                            <span class="fs-4 fw-bold">Bs. <?php echo number_format($sanction['fine_amount'], 2); ?></span>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-semibold">Estado Actual:</span>
                                            <?php
                                                $status_colors = [
                                                    'Imposed' => 'warning',
                                                    'Paid' => 'success',
                                                    'Pending' => 'secondary',
                                                    'Canceled' => 'danger'
                                                ];
                                                $color = $status_colors[$sanction['sanction_status']] ?? 'info';
                                                $statusText = $allowed_sanction_status[$sanction['sanction_status']] ?? $sanction['sanction_status'];
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?> fs-5">
                                                <?php echo htmlspecialchars($statusText); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="card bg-light border">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted"><i class="ri-file-text-line"></i> Observaciones</h6>
                                        <p class="card-text mb-0"><?php echo nl2br(htmlspecialchars($sanction['sanction_observations'] ?? 'No hay observaciones registradas.')); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
