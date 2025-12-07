<div class="card">
    <div class="card-datatable table-responsive">
        <table id="zoneTable" class="datatables-customers table">
            <thead>
                <tr>
                    <th></th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th class="text-nowrap">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($zones as $zone): ?>
                <tr data-id="<?= $zone['id'] ?>">
                    <td></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="ri ri-map-pin-2-line"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0"><?= htmlspecialchars($zone['name']) ?></h6>
                                <small class="text-muted">Zona</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if (!empty($zone['description'])): ?>
                        <span class="text-truncate d-block" style="max-width: 300px;"><?= htmlspecialchars($zone['description']) ?></span>
                        <?php else: ?>
                        <span class="text-muted">Sin descripción</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <a href="<?= $app['url'] ?>/zone/edit/<?= $zone['id'] ?>" 
                               class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect" 
                               data-bs-toggle="tooltip" 
                               title="Editar">
                                <i class="ri ri-pencil-line ri-20px"></i>
                            </a>
                            <a href="javascript:void(0);" 
                               onclick="deleteRecord(<?= $zone['id'] ?>, '<?= $app['url'] ?>/zone/delete/:id', 'zoneTable')" 
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
    initDataTableWithCheckbox('zoneTable', {
        createUrl: '<?= $app['url'] ?>/zone/create',
        bulkDeleteUrl: '<?= $app['url'] ?>/zone/bulkdelete'
    });
    
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
<?php $pageScripts = ob_get_clean(); ?>
