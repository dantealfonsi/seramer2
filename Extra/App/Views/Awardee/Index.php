<div class="card">
    <div class="card-datatable table-responsive">
        <table id="awardeesTable" class="datatables-customers table">
            <thead>
                <tr>
                    <th></th>
                    <th>Adjudicatario</th>
                    <th class="text-nowrap">Cédula</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th class="text-nowrap">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($awardees as $awardee): ?>
                <tr data-id="<?= $awardee['id'] ?>">
                    <td></td>
                    <td>
                        <div class="d-flex justify-content-start align-items-center customer-name">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded-circle bg-label-primary">
                                    <?= strtoupper(substr($awardee['first_name'], 0, 1) . substr($awardee['last_name'], 0, 1)) ?>
                                </span>
                            </div>
                            <div class="d-flex flex-column">
                                <h6 class="mb-0 text-truncate"><?= htmlspecialchars(\App\Models\AwardeeModel::getFullName($awardee)) ?></h6>
                                <?php if (!empty($awardee['email'])): ?>
                                <small class="text-muted text-truncate"><?= htmlspecialchars($awardee['email']) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-label-secondary"><?= htmlspecialchars($awardee['id_number']) ?></span>
                    </td>
                    <td>
                        <span class="text-nowrap"><?= htmlspecialchars($awardee['phone'] ?? '-') ?></span>
                    </td>
                    <td>
                        <?php if (!empty($awardee['email'])): ?>
                        <span class="text-truncate d-block" style="max-width: 200px;"><?= htmlspecialchars($awardee['email']) ?></span>
                        <?php else: ?>
                        <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <a href="<?= $app['url'] ?>/awardee/edit/<?= $awardee['id'] ?>" 
                               class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect" 
                               data-bs-toggle="tooltip" 
                               title="Editar">
                                <i class="ri ri-pencil-line ri-20px"></i>
                            </a>
                            <a href="<?= $app['url'] ?>/awardee/showContracts/<?= $awardee['id'] ?>" 
                               class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect" 
                               data-bs-toggle="tooltip" 
                               title="Ver Contratos">
                                <i class="ri ri-file-text-line ri-20px"></i>
                            </a>
                            <a href="javascript:void(0);" 
                               onclick="deleteRecord(<?= $awardee['id'] ?>, '<?= $app['url'] ?>/awardee/delete/:id', 'awardeesTable')" 
                               class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect delete-record" 
                               data-bs-toggle="tooltip" 
                               title="Eliminar">
                                <i class="ri ri-delete-bin-7-line ri-20px"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php ob_start(); ?>
<script>
$(document).ready(function() {
    // Inicializar DataTable con checkbox
    const dt = initDataTableWithCheckbox('awardeesTable', {
        createUrl: '<?= $app['url'] ?>/awardee/create',
        bulkDeleteUrl: '<?= $app['url'] ?>/awardee/bulkdelete'
    });
    
    // Configurar título de la tarjeta
    $('.head-label').html('<h5 class="card-title mb-0">Adjudicatarios</h5>');
    
    // Inicializar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
<?php $pageScripts = ob_get_clean(); ?>
