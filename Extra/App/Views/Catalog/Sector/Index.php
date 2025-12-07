<div class="card">
    <div class="card-datatable table-responsive">
        <table id="sectorTable" class="datatables-customers table">
            <thead>
                <tr>
                    <th></th>
                    <th>Nombre</th>
                    <th>Zona</th>
                    <th>Descripción</th>
                    <th class="text-nowrap">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sectors as $sector): ?>
                <tr data-id="<?= $sector['id'] ?>">
                    <td></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="ri ri-layout-grid-line"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0"><?= htmlspecialchars($sector['name']) ?></h6>
                                <small class="text-muted">Sector</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-label-warning"><?= htmlspecialchars($sector['zone_name']) ?></span>
                    </td>
                    <td>
                        <?php if (!empty($sector['description'])): ?>
                        <span class="text-truncate d-block" style="max-width: 250px;"><?= htmlspecialchars($sector['description']) ?></span>
                        <?php else: ?>
                        <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <a href="<?= $app['url'] ?>/sector/edit/<?= $sector['id'] ?>" 
                               class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect" 
                               data-bs-toggle="tooltip" 
                               title="Editar">
                                <i class="ri ri-pencil-line ri-20px"></i>
                            </a>
                            <a href="javascript:void(0);" 
                               onclick="deleteRecord(<?= $sector['id'] ?>, '<?= $app['url'] ?>/sector/delete/:id', 'sectorTable')" 
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
    initDataTableWithCheckbox('sectorTable', {
        createUrl: '<?= $app['url'] ?>/sector/create',
        bulkDeleteUrl: '<?= $app['url'] ?>/sector/bulkdelete'
    });
    
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
<?php $pageScripts = ob_get_clean(); ?>
