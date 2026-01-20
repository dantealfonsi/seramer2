<div class="card">
    <div class="card-datatable table-responsive">
        <table id="internalCategoryTable" class="datatables-customers table">
            <thead>
                <tr>
                    <th></th>
                    <th>Nombre</th>
                    <th class="text-nowrap">N° Cobros</th>
                    <th class="text-nowrap">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                <tr data-id="<?= $category['id'] ?>">
                    <td></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="ri ri-store-2-line"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0"><?= htmlspecialchars($category['name']) ?></h6>
                                <small class="text-muted">Rubro Interno</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-label-primary"><?= isset($category['payment_count']) ? number_format($category['payment_count'], 2) : '0.00' ?> </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <a href="<?= $app['url'] ?>/internalcategory/edit/<?= $category['id'] ?>" 
                               class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect" 
                               data-bs-toggle="tooltip" 
                               title="Editar">
                                <i class="ri ri-pencil-line ri-20px"></i>
                            </a>
                            <a href="javascript:void(0);" 
                               onclick="deleteRecord(<?= $category['id'] ?>, '<?= $app['url'] ?>/internalcategory/delete/:id', 'internalCategoryTable')" 
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
    initDataTableWithCheckbox('internalCategoryTable', {
        createUrl: '<?= $app['url'] ?>/internalcategory/create',
        bulkDeleteUrl: '<?= $app['url'] ?>/internalcategory/bulkdelete'
    });
    
    // Inicializar tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
<?php $pageScripts = ob_get_clean(); ?>
