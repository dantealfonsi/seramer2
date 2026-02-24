<?php
require_once __DIR__ . '/../../controllers/PlanningController.php';

$controller = new PlanningController();
$data = $controller->simultaneos();

extract($data);

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-xxl flex-grow-1 container-p-y">
        
        <!-- Estadísticas del mes -->
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span>Total Contratos</span>
                                <div class="d-flex align-items-end mt-2">
                                    <h4 class="mb-0 me-2"><?= number_format($statistics['total_contracts'] ?? 0) ?></h4>
                                </div>
                                <small>Registrados este mes</small>
                            </div>
                            <span class="badge bg-label-primary rounded p-2">
                                <i class="ri-file-list-3-line ri-24px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span>Monto Proyectado</span>
                                <div class="d-flex align-items-end mt-2">
                                    <h4 class="mb-0 me-2">Bs. <?= number_format($statistics['total_amount'] ?? 0, 2) ?></h4>
                                </div>
                                <small>Total a recaudar</small>
                            </div>
                            <span class="badge bg-label-success rounded p-2">
                                <i class="ri-money-dollar-circle-line ri-24px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span>Pendientes</span>
                                <div class="d-flex align-items-end mt-2">
                                    <h4 class="mb-0 me-2"><?= number_format($statistics['pending_payments'] ?? 0) ?></h4>
                                </div>
                                <small>Pagos por cobrar</small>
                            </div>
                            <span class="badge bg-label-warning rounded p-2">
                                <i class="ri-time-line ri-24px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span>Morosos</span>
                                <div class="d-flex align-items-end mt-2">
                                    <h4 class="mb-0 me-2"><?= number_format($statistics['delinquent_payments'] ?? 0) ?></h4>
                                </div>
                                <small>Pagos vencidos</small>
                            </div>
                            <span class="badge bg-label-danger rounded p-2">
                                <i class="ri-error-warning-line ri-24px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center border-bottom">
                <h5 class="mb-0 d-flex align-items-center">
                    <div class="p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #e7e7ff !important;">
                        <i class="ri-calendar-check-line" style="color: #696cff; font-size: 1.5rem;"></i>
                    </div>
                    <?= htmlspecialchars($page_title) ?> - <?= htmlspecialchars($current_month_spanish) ?> <?= $current_year ?>
                </h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="location.reload()">
                        <i class="ri-refresh-line me-1"></i> Actualizar
                    </button>
                </div>
            </div>

            <div class="card-body pt-4">
                <!-- Filtros -->
                <form method="GET" class="row g-3 mb-4" id="filtersForm">
                    <div class="col-md-2">
                        <label class="form-label small text-uppercase">Año</label>
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
                    
                    <div class="col-md-2">
                        <label class="form-label small text-uppercase">Mes</label>
                        <select class="form-select" name="month">
                            <?php foreach ($months as $num => $name): ?>
                                <option value="<?= $num ?>" <?= ($current_month == $num) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label small text-uppercase">Zona</label>
                        <select class="form-select" name="zone_id" id="zoneSelect" onchange="loadSectors()">
                            <option value="">Todas</option>
                            <?php foreach ($zones as $zone): ?>
                                <option value="<?= $zone['id'] ?>" <?= ($filters['zone_id'] == $zone['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($zone['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small text-uppercase">Sector</label>
                        <select class="form-select" name="sector_id" id="sectorSelect">
                            <option value="">Todos</option>
                            <?php foreach ($sectors as $sector): ?>
                                <option value="<?= $sector['id'] ?>" <?= ($filters['sector_id'] == $sector['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sector['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="show_delinquent" value="1" id="showDelinquent" <?= ($filters['show_delinquent'] == '1') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="showDelinquent">Solo morosos</label>
                        </div>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ri-filter-line me-1"></i> Filtrar
                        </button>
                    </div>
                </form>

                <!-- Tabla de contratos -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="planningTable">
                        <thead class="table-dark">
                            <tr>
                                <th># Contrato</th>
                                <th>Adjudicatario</th>
                                <th>Zona/Sector</th>
                                <th class="text-center">Rubros</th>
                                <th class="text-center">Locales</th>
                                <th>Monto</th>
                                <th>Estado</th>
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
                                    <td><strong>#<?= $contract['contract_id'] ?></strong></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-xs me-2">
                                                <span class="avatar-initial rounded-circle bg-label-primary"><?= strtoupper(substr($contract['first_name'], 0, 1)) ?></span>
                                            </div>
                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars($contract['awardee_name']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($contract['awardee_id_number']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($contract['zone_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($contract['sector_name']) ?></small>
                                    </td>
                                    <td class="text-center"><span class="badge rounded-pill bg-label-info"><?= $contract['total_categories'] ?></span></td>
                                    <td class="text-center"><span class="badge rounded-pill bg-label-secondary"><?= $contract['total_locations'] ?></span></td>
                                    <td>
                                        <div class="fw-bold">Bs. <?= number_format($contract['calculated_amount'], 2) ?></div>
                                        <?php if ($contract['multiplier_factor'] > 0): ?>
                                            <small class="text-muted"><?= number_format($contract['multiplier_factor'], 2) ?> × €<?= number_format($contract['euro_rate_value'], 2) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $badgeClass ?>"><?= $contract['payment_status_text'] ?></span>
                                        <?php if ($contract['payment_date']): ?>
                                            <div class="text-muted" style="font-size: 0.75rem;">Vence: <?= date('d/m/Y', strtotime($contract['payment_date'])) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="detail.php?id=<?= $contract['contract_id'] ?>" class="btn btn-sm btn-icon btn-label-primary shadow-sm" title="Ver Detalle">
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
                                         { text: 'PLANIFICACIÓN DE PAGOS SIMULTÁNEOS', fontSize: 12, bold: true },
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
                     title: 'Planificacion_Simultaneos_<?= $current_month_spanish ?>'
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
