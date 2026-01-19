<?php
session_start();
require_once __DIR__ . '/../../controllers/ConciliationReportsController.php';

$reportsController = new ConciliationReportsController();
$report = null;

// Obtener el ID de la URL
$id = $_GET['id'] ?? null;

// Cargar los datos del reporte si existe un ID válido
if ($id) {
    $result = $reportsController->show($id);
    if ($result['success']) {
        $report = $result['report'];
    } else {
        $_SESSION['flash_message'] = ['type' => 'danger', 'message' => $result['message']];
        header("Location: index.php");
        exit;
    }
} else {
    $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'ID de informe no proporcionado.'];
    header("Location: index.php");
    exit;
}

// Opciones para los menús desplegables
$attendance_options = [
    1 => 'Presente',
    0 => 'Ausente'
];

$result_options = [
    'Agreement Reached' => 'Acuerdo alcanzado',
    'No Agreement' => 'Sin acuerdo',
    'Case Postponed' => 'Caso pospuesto',
    'Absent Party' => 'Parte ausente'
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
                            Detalles del Informe
                        </h5>
                        <div class="btn-group" role="group">
                            <a href="../citations/index.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Volver
                            </a>
                            <a href="edit.php?id=<?php echo htmlspecialchars($report['report_id']); ?>" class="btn btn-warning">
                                <i class="ri-edit-line"></i> Editar
                            </a>
                            <a href="../reports/index.php?report=acta_conciliacion.rep&action=view&id=<?php echo $report['report_id']; ?>" class="btn btn-info">
                                <i class="ri-printer-line"></i> Generar Reporte
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>ID del Informe:</strong></p>
                                <p class="form-control-plaintext"><?php echo htmlspecialchars($report['report_id']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Fecha del Informe:</strong></p>
                                <p class="form-control-plaintext">
                                    <?php 
                                    $date = new DateTime($report['report_date']);
                                    echo $date->format('d/m/Y H:i'); 
                                    ?>
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>ID de la Citación:</strong></p>
                                <p class="form-control-plaintext"><?php echo htmlspecialchars($report['citation_id']); ?></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Asistencia del Citado:</strong></p>
                                <p class="form-control-plaintext">
                                    <?php 
                                    $attendanceBadge = $report['awardee_attendance'] == 1 ? 'success' : 'danger';
                                    $attendanceText = $attendance_options[$report['awardee_attendance']];
                                    ?>
                                    <span class="badge bg-<?php echo $attendanceBadge; ?>"><?php echo htmlspecialchars($attendanceText); ?></span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Resultado:</strong></p>
                                <p class="form-control-plaintext">
                                    <?php 
                                    $resultBadge = 'secondary';
                                    switch($report['result']) {
                                        case 'Agreement Reached': $resultBadge = 'success'; break;
                                        case 'No Agreement': $resultBadge = 'warning'; break;
                                        case 'Case Postponed': $resultBadge = 'info'; break;
                                        case 'Absent Party': $resultBadge = 'danger'; break;
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $resultBadge; ?>"><?php echo htmlspecialchars($result_options[$report['result']]); ?></span>
                                </p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <p class="mb-1"><strong>Detalles del Acuerdo:</strong></p>
                            <div class="form-control-plaintext border p-2 rounded" style="min-height: 100px;">
                                <?php echo nl2br(htmlspecialchars($report['agreement_details'])); ?>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
