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
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="card-title d-flex align-items-center mb-1" style="font-size: 1.4rem;font-weight: 600;">
                                <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;"><i class="ri-file-text-line" style="color: #696cff; font-size: 1.5rem;"></i></div>
                                Detalles del Informe de Conciliación
                            </h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item"><a href="../citations/index.php">Citaciones</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Informe de Conciliación</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="btn-group" role="group">
                            <a href="../citations/index.php" class="btn btn-outline-secondary">
                                <i class="ri-arrow-left-line"></i> Volver al listado
                            </a>
                            <a href="edit.php?id=<?php echo htmlspecialchars($report['report_id']); ?>" class="btn btn-warning">
                                <i class="ri-edit-2-line"></i> Editar
                            </a>
                            <a href="../reports/index.php?report=acta_conciliacion.rep&action=view&id=<?php echo $report['report_id']; ?>" class="btn btn-info">
                                <i class="ri-printer-line"></i> Generar Acta
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
