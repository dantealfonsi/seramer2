<?php
require_once __DIR__ . '/../../controllers/ContractController.php';

$controller = new ContractController();
$id = $_GET['id'] ?? 0;
$result = $controller->detail((int)$id);

if (!$result['success']) {
    header('Location: ' . ($result['redirect'] ?? 'index.php'));
    exit;
}

$contract = $result['contract'];
$page_title = $result['page_title'];
$categories = $result['categories'];
$locations = $result['locations'];
$payments = $result['payments'];

require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                 <?php if (isset($_SESSION['flash_message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?>">
                        <?php echo $_SESSION['flash_message']['message']; ?>
                        <?php unset($_SESSION['flash_message']); ?>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title">Contrato #<?php echo $contract['id']; ?> - <?php echo htmlspecialchars($contract['awardee_name']); ?></h5>
                        <div>
                             <a href="index.php" class="btn btn-secondary btn-sm">Volver</a>
                             <a href="edit.php?id=<?php echo $contract['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                        </div>
                    </div>
                    <div class="card-body">
                         <div class="row">
                             <div class="col-md-6">
                                 <p><strong>Adjudicatario:</strong> <?php echo htmlspecialchars($contract['awardee_name']); ?> (<?php echo htmlspecialchars($contract['awardee_id_number']); ?>)</p>
                                 <p><strong>Tipo:</strong> <?php echo ucfirst($contract['type']); ?></p>
                                 <p><strong>Modo:</strong> <?php echo ucfirst($contract['contract_mode']); ?></p>
                             </div>
                             <div class="col-md-6">
                                 <p><strong>Fecha Inicio:</strong> <?php echo date('d/m/Y', strtotime($contract['start_date'])); ?></p>
                                 <p><strong>Fecha Fin:</strong> <?php echo date('d/m/Y', strtotime($contract['end_date'])); ?></p>
                                 <p><strong>Status:</strong> <?php echo ucfirst($contract['status']); ?></p>
                             </div>
                         </div>
                         
                         <hr>
                         
                         <h5>Locales Asignados</h5>
                         <?php if (empty($locations)): ?>
                             <div class="alert alert-info">No hay locales asignados.</div>
                         <?php else: ?>
                             <ul>
                             <?php foreach ($locations as $loc): ?>
                                 <li>
                                     <strong>Local <?php echo htmlspecialchars($loc['stall_number']); ?></strong> 
                                     (Sector: <?php echo htmlspecialchars($loc['sector_name']); ?>)
                                 </li>
                             <?php endforeach; ?>
                             </ul>
                         <?php endif; ?>
                         
                         <hr>
                         <h5>Categorías</h5>
                          <?php if (empty($categories)): ?>
                             <div class="alert alert-info">No hay categorías asignadas.</div>
                         <?php else: ?>
                             <ul>
                             <?php foreach ($categories as $cat): ?>
                                 <li><?php echo htmlspecialchars($cat['category_name']); ?> (<?php echo ucfirst($cat['category_type'] ?? 'T'); ?>)</li>
                             <?php endforeach; ?>
                             </ul>
                         <?php endif; ?>
                         
                         <hr>
                         <h5>Pagos</h5>
                         <?php if (empty($payments)): ?>
                             <div class="alert alert-info">No hay pagos registrados.</div>
                         <?php else: ?>
                             <table class="table table-sm">
                                 <thead>
                                     <tr>
                                         <th>Referencia</th>
                                         <th>Periodo</th>
                                         <th>Monto Euro</th>
                                         <th>Monto Bs</th>
                                         <th>Estado</th>
                                     </tr>
                                 </thead>
                                 <tbody>
                                 <?php foreach ($payments as $pay): ?>
                                     <tr>
                                         <td><?php echo $pay['payment_reference']; ?></td>
                                         <td><?php echo $pay['month_name'] . ' ' . $pay['year']; ?></td>
                                         <td><?php echo number_format($pay['amount_euro'], 2); ?></td>
                                         <td><?php echo number_format($pay['amount_bs'], 2); ?></td>
                                         <td><?php echo ucfirst($pay['status']); ?></td>
                                     </tr>
                                 <?php endforeach; ?>
                                 </tbody>
                             </table>
                         <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
