<div class="card">
    <div class="card-datatable table-responsive">
        <table id="stallTable" class="datatables-customers table">
            <thead>
                <tr>
                    <th></th>
                    <th class="text-nowrap">N° Local</th>
                    <th>Zona</th>
                    <th>Sector</th>
                    <th>Ubicación</th>
                    <th class="text-nowrap">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stalls as $stall): ?>
                <tr data-id="<?= $stall['id'] ?>">
                    <td></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="ri ri-store-line"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0"><?= htmlspecialchars($stall['stall_number']) ?></h6>
                                <small class="text-muted">Local</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-label-warning"><?= htmlspecialchars($stall['zone_name']) ?></span>
                    </td>
                    <td>
                        <span class="badge bg-label-primary"><?= htmlspecialchars($stall['sector_name']) ?></span>
                    </td>
                    <td>
                        <?php if (!empty($stall['location_description'])): ?>
                        <span class="text-truncate d-block" style="max-width: 200px;"><?= htmlspecialchars($stall['location_description']) ?></span>
                        <?php else: ?>
                        <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <a href="<?= $app['url'] ?>/marketstall/edit/<?= $stall['id'] ?>" 
                               class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect" 
                               data-bs-toggle="tooltip" 
                               title="Editar">
                                <i class="ri ri-pencil-line ri-20px"></i>
                            </a>
                            <a href="javascript:void(0);" 
                               onclick="deleteRecord(<?= $stall['id'] ?>, '<?= $app['url'] ?>/marketstall/delete/:id', 'stallTable')" 
                               class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect" 
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
    initDataTableWithCheckbox('stallTable', {
        createUrl: '<?= $app['url'] ?>/marketstall/create',
        bulkDeleteUrl: '<?= $app['url'] ?>/marketstall/bulkdelete'
    });
    
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
<?php $pageScripts = ob_get_clean(); ?>
