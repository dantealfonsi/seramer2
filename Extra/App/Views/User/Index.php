<div class="card">
    <div class="card-datatable table-responsive">
        <table id="usersTable" class="datatables-customers table">
            <thead>
                <tr>
                    <th></th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Último Acceso</th>
                    <th>Estado</th>
                    <th class="text-nowrap">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr data-id="<?= $user['id'] ?>">
                    <td></td>
                    <td>
                        <div class="d-flex justify-content-start align-items-center customer-name">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded-circle bg-label-success">
                                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                </span>
                            </div>
                            <div class="d-flex flex-column">
                                <h6 class="mb-0 text-truncate"><?= htmlspecialchars($user['username']) ?></h6>
                                <?php if (!empty($user['staff_first_name'])): ?>
                                <small class="text-muted text-truncate"><?= htmlspecialchars($user['staff_first_name'] . ' ' . $user['staff_last_name']) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="text-truncate d-block" style="max-width: 250px;"><?= htmlspecialchars($user['email']) ?></span>
                    </td>
                    <td>
                        <?php if (!empty($user['last_login'])): ?>
                        <span class="text-nowrap"><?= date('d/m/Y H:i', strtotime($user['last_login'])) ?></span>
                        <?php else: ?>
                        <span class="text-muted">Nunca</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($user['status'] === 'active'): ?>
                        <span class="badge bg-label-success">Activo</span>
                        <?php else: ?>
                        <span class="badge bg-label-secondary">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <a href="<?= $app['url'] ?>/user/edit/<?= $user['id'] ?>" 
                               class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect" 
                               data-bs-toggle="tooltip" 
                               title="Editar">
                                <i class="ri ri-pencil-line ri-20px"></i>
                            </a>
                            <a href="javascript:void(0);" 
                               onclick="deleteRecord(<?= $user['id'] ?>, '<?= $app['url'] ?>/user/delete/:id', 'usersTable')" 
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
    const dt = initDataTableWithCheckbox('usersTable', {
        createUrl: '<?= $app['url'] ?>/user/create',
        bulkDeleteUrl: '<?= $app['url'] ?>/user/bulkdelete'
    });
    
    // Configurar título de la tarjeta
    $('.head-label').html('<h5 class="card-title mb-0">Usuarios</h5>');
    
    // Inicializar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
<?php $pageScripts = ob_get_clean(); ?>

