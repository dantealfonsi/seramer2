<?php
require_once __DIR__ . '/../../controllers/PlanningController.php';

$controller = new PlanningController();
$data = $controller->anticipados();

extract($data);

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content" style="padding: 1.5rem;">
    <div class="container-fluid">
        
        <!-- Estadísticas del mes Estilo Premium -->
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card card-status-primary" style="background-color: var(--metro-primary-light);">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="page-icon me-3" style="width:52px;height:52px;font-size:1.6rem; color: var(--metro-primary) !important;">
                            <i class="ri-file-list-3-line"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold" style="color: var(--metro-primary);"><?= number_format($statistics['total_contracts'] ?? 0) ?></h4>
                            <p class="mb-0 text-muted fw-semibold" style="font-size:0.75rem; text-transform: uppercase;">Total Contratos</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card card-status-success" style="background-color: var(--metro-success-light);">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="page-icon me-3" style="width:52px;height:52px;font-size:1.6rem; color: var(--metro-success) !important;">
                            <i class="ri-money-dollar-circle-line"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold" style="color: var(--metro-success);">Bs. <?= number_format($statistics['total_amount'] ?? 0, 2) ?></h4>
                            <p class="mb-0 text-muted fw-semibold" style="font-size:0.75rem; text-transform: uppercase;">Monto Proyectado</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card card-status-warning" style="background-color: var(--metro-warning-light);">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="page-icon me-3" style="width:52px;height:52px;font-size:1.6rem; color: var(--metro-warning) !important;">
                            <i class="ri-time-line"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold" style="color: var(--metro-warning);"><?= number_format($statistics['pending_payments'] ?? 0) ?></h4>
                            <p class="mb-0 text-muted fw-semibold" style="font-size:0.75rem; text-transform: uppercase;">Pagos Pendientes</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card card-status-danger" style="background-color: var(--metro-danger-light);">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="page-icon me-3" style="width:52px;height:52px;font-size:1.6rem; color: var(--metro-danger) !important;">
                            <i class="ri-error-warning-line"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold" style="color: var(--metro-danger);"><?= number_format($statistics['delinquent_payments'] ?? 0) ?></h4>
                            <p class="mb-0 text-muted fw-semibold" style="font-size:0.75rem; text-transform: uppercase;">Pagos Morosos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <!-- 1. Encabezado (Título y Botón) -->
            <div class="card-header d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title mb-0 d-flex align-items-center" style="font-size: 1.4rem; font-weight: 600;">
                    <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;">
                        <i class="ri-calendar-check-line" style="color: #696cff; font-size: 1.5rem;"></i>
                    </div>
                    <?= htmlspecialchars($page_title) ?> - <?= htmlspecialchars($current_month_spanish) ?> <?= $current_year ?>
                </h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" onclick="location.reload()">
                        <i class="ri-refresh-line me-1"></i> Actualizar
                    </button>
                </div>
            </div>

            <div class="card-body p-4 pt-0">
                
                <!-- Filtros Avanzados -->
                <div class="filter-card mb-4">
                    <div class="filter-card-title">
                        <i class="ri-filter-2-line"></i> Opciones de Filtrado de Planificación
                    </div>
                    <div class="filter-card-body">
                        <form method="GET" id="filtersForm">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label class="form-label fw-bold small text-uppercase">Año</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-calendar-line text-muted"></i></span>
                                        <select class="form-select" name="year">
                                            <?php foreach ($fiscal_years as $fy): 
                                                $fyYear = date('Y', strtotime($fy['start_date'] ?? $fy['year'] . '-01-01'));
                                            ?>
                                                <option value="<?= $fyYear ?>" <?= ($current_year == $fyYear) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($fyYear) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label fw-bold small text-uppercase">Mes</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-calendar-event-line text-muted"></i></span>
                                        <select class="form-select" name="month">
                                            <?php foreach ($months as $num => $name): ?>
                                                <option value="<?= $num ?>" <?= ($current_month == $num) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($name) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small text-uppercase">Zona</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-map-pin-line text-muted"></i></span>
                                        <select class="form-select" name="zone_id" id="zoneSelect" onchange="loadSectors()">
                                            <option value="">Todas las Zonas</option>
                                            <?php foreach ($zones as $zone): ?>
                                                <option value="<?= $zone['id'] ?>" <?= ($filters['zone_id'] == $zone['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($zone['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-bold small text-uppercase">Sector</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-building-line text-muted"></i></span>
                                        <select class="form-select" name="sector_id" id="sectorSelect">
                                            <option value="">Todos los Sectores</option>
                                            <?php foreach ($sectors as $sector): ?>
                                                <option value="<?= $sector['id'] ?>" <?= ($filters['sector_id'] == $sector['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($sector['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-2 d-flex align-items-end">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="show_delinquent" value="1" id="showDelinquent" <?= ($filters['show_delinquent'] == '1') ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-bold small text-uppercase" for="showDelinquent">Solo morosos</label>
                                    </div>
                                </div>

                                <div class="col-12 filter-card-actions">
                                    <a href="planning_anticipados.php" class="btn btn-filter-clear">
                                        <i class="ri-refresh-line me-1"></i> Limpiar
                                    </a>
                                    <button type="submit" class="btn btn-filter-apply">
                                        <i class="ri-search-line me-1"></i> Filtrar Planificación
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla de contratos -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle w-100" id="planningTable">
                        <thead class="table-dark">
                            <tr>
                                <th># Contrato</th>
                                <th>Adjudicatario</th>
                                <th>Zona / Sector</th>
                                <th class="text-center">Rubros</th>
                                <th class="text-center">Locales</th>
                                <th>Monto Proyectado</th>
                                <th>Estado de Pago</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($contracts)): ?>
                                <?php foreach ($contracts as $contract): 
                                    $statusClass = [
                                        'Pendiente' => 'bg-label-warning',
                                        'Pagado' => 'bg-label-success',
                                        'Moroso' => 'bg-label-danger',
                                        'Cancelado' => 'bg-label-secondary'
                                    ];
                                    $badgeClass = $statusClass[$contract['payment_status_text']] ?? 'bg-label-secondary';
                                ?>
                                <tr>
                                    <td><span class="fw-bold text-primary">#<?= $contract['contract_id'] ?></span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3">
                                                <span class="avatar-initial rounded-circle bg-label-primary"><?= strtoupper(substr($contract['first_name'], 0, 1)) ?></span>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($contract['awardee_name']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($contract['awardee_id_number']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark small"><?= htmlspecialchars($contract['zone_name']) ?></span>
                                            <small class="text-muted"><?= htmlspecialchars($contract['sector_name']) ?></small>
                                        </div>
                                    </td>
                                    <td class="text-center"><span class="badge rounded-pill bg-label-info px-3"><?= $contract['total_categories'] ?></span></td>
                                    <td class="text-center"><span class="badge rounded-pill bg-label-secondary px-3"><?= $contract['total_locations'] ?></span></td>
                                    <td>
                                        <div class="fw-bold text-dark">Bs. <?= number_format($contract['calculated_amount'], 2) ?></div>
                                        <?php if ($contract['multiplier_factor'] > 0): ?>
                                            <small class="text-muted"><?= number_format($contract['multiplier_factor'], 2) ?> × €<?= number_format($contract['euro_rate_value'], 2) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $badgeClass ?> px-3 py-2" style="font-size: 0.8rem; font-weight: 600;"><?= $contract['payment_status_text'] ?></span>
                                        <?php if ($contract['payment_date']): ?>
                                            <div class="text-muted mt-1" style="font-size: 0.75rem;"><i class="ri-calendar-line me-1"></i>Vence: <?= date('d/m/Y', strtotime($contract['payment_date'])) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="detail.php?id=<?= $contract['contract_id'] ?>" class="btn btn-sm btn-outline-primary" style="padding: 0.4rem; border-radius: 0.5rem;" title="Ver Detalle">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="ri-information-line ri-48px mb-2"></i>
                                            <p>No se encontraron contratos para la planificación seleccionada.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

 <!-- DataTables Dependencies (CDN for full Buttons support) -->
 <script type="text/javascript" src="../../public/assets/js/pdf_logo.js"></script>
 <script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
 <script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
 <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
 <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
 <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
 <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
 <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
 <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
 <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
 <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js"></script>
 <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css"/>
 <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css"/>

 <script>
 $(document).ready(function() {
     if ($.fn.DataTable) {
         $('#planningTable').DataTable({
             responsive: true,
             dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rtip',
             buttons: [
                 {
                     extend: 'pdfHtml5',
                     text: '<i class="ri-file-pdf-line me-1"></i> PDF',
                     className: 'btn btn-danger btn-sm me-1',
                     orientation: 'landscape',
                     pageSize: 'LETTER',
                     exportOptions: { columns: [0, 1, 2, 5, 6] },
                     customize: function (doc) {
                         doc.content.splice(0, 1);
                         doc.content.unshift({
                             columns: [
                                 { image: commonPdfLogo, width: 50 },
                                 {
                                     text: [
                                         { text: 'SERVICIO AUTÓNOMO DE MERCADO MUNICIPAL DE BERMÚDEZ\n', fontSize: 10, bold: true },
                                         { text: 'PLANIFICACIÓN DE PAGOS ANTICIPADOS', fontSize: 12, bold: true },
                                         { text: '<?= htmlspecialchars($current_month_spanish) ?> <?= $current_year ?>', fontSize: 10 }
                                     ],
                                     margin: [10, 0, 0, 0]
                                 }
                             ],
                             margin: [0, 0, 0, 10]
                         });
                     }
                 },
                 {
                     extend: 'excelHtml5',
                     text: '<i class="ri-file-excel-line me-1"></i> Excel',
                     className: 'btn btn-success btn-sm me-1',
                     exportOptions: { columns: [0, 1, 2, 5, 6] },
                     title: 'Planificacion_Anticipados_<?= $current_month_spanish ?>'
                 },
                 {
                     extend: 'print',
                     text: '<i class="ri-printer-line me-1"></i> Imprimir',
                     className: 'btn btn-info btn-sm',
                     exportOptions: { columns: [0, 1, 2, 5, 6] }
                 }
             ],
             language: {
                 url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
             },
             order: [[6, 'asc']]
         });
     }
 });

 function loadSectors() {
     const zoneId = document.getElementById('zoneSelect').value;
     const sectorSelect = document.getElementById('sectorSelect');
     
     sectorSelect.innerHTML = '<option value="">Cargando...</option>';
     
     if (!zoneId) {
         sectorSelect.innerHTML = '<option value="">Todos</option>';
         return;
     }

     fetch(`ajax.php?action=get_sectors&zone_id=${zoneId}`)
         .then(response => response.json())
         .then(data => {
             let html = '<option value="">Todos</option>';
             data.forEach(s => {
                 html += `<option value="${s.id}">${s.name}</option>`;
             });
             sectorSelect.innerHTML = html;
         });
 }

 // Auto-submit en cambio de morosos
 document.getElementById('showDelinquent').addEventListener('change', () => {
     document.getElementById('filtersForm').submit();
 });
 </script>

 <?php include __DIR__ . '/../layouts/footer.php'; ?>
