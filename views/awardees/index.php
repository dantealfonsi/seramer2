<?php
require_once __DIR__ . '/../../controllers/AwardeeController.php';

$controller = new AwardeeController();
$data = $controller->index();
$awardees = $data['awardees'];
$page_title = $data['page_title'];

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
                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($page_title); ?></h5>
                        <a href="create.php" class="btn btn-primary btn-sm">
                            <i class="ri-user-add-line"></i> Registrar Adjudicatario
                        </a>
                    </div>
                    <div class="card-body">
                         <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Cédula</th>
                                        <th>Nombre Completo</th>
                                        <th>Contacto</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($awardees as $awardee): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($awardee['id_number']); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($awardee['first_name'] . ' ' . $awardee['last_name']); ?>
                                            </td>
                                            <td>
                                                <small>
                                                    T: <?php echo htmlspecialchars($awardee['phone'] ?? '-'); ?><br>
                                                    E: <?php echo htmlspecialchars($awardee['email'] ?? '-'); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <a href="edit.php?id=<?php echo $awardee['id']; ?>" class="btn btn-sm btn-info" title="Editar">
                                                    <i class="ri-pencil-line"></i>
                                                </a>
                                                <a href="show_contracts.php?id=<?php echo $awardee['id']; ?>" class="btn btn-sm btn-success" title="Ver Contratos">
                                                    <i class="ri-file-search-line"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
