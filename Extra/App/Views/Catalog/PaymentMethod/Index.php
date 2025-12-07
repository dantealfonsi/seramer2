<div class="card">
    <div class="card-datatable table-responsive">
        <table id="paymentMethodTable" class="datatables-customers table">
            <thead>
                <tr>
                    <th></th>
                    <th>Nombre</th>
                    <th>Estado</th>
                    <th class="text-nowrap">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($paymentMethods as $method): ?>
                <tr data-id="<?= $method['id'] ?>">
                    <td></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-<?= $method['is_active'] ? 'success' : 'secondary' ?>">
                                    <i class="ri <?= $method['is_active'] ? 'ri-check-line' : 'ri-close-line' ?>"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0"><?= htmlspecialchars($method['name']) ?></h6>
                                <small class="text-muted">Método de Pago</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if ($method['is_active']): ?>
                        <span class="badge bg-label-success">Activo</span>
                        <?php else: ?>
                        <span class="badge bg-label-secondary">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <button 
                               onclick="toggleActive(<?= $method['id'] ?>, <?= $method['is_active'] ? 'false' : 'true' ?>)" 
                               class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect" 
                               data-bs-toggle="tooltip" 
                               title="<?= $method['is_active'] ? 'Desactivar' : 'Activar' ?>">
                                <i class="ri <?= $method['is_active'] ? 'ri-eye-off-line' : 'ri-eye-line' ?> ri-20px"></i>
                            </button>
                            <a href="<?= $app['url'] ?>/paymentmethod/edit/<?= $method['id'] ?>" 
                               class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect" 
                               data-bs-toggle="tooltip" 
                               title="Editar">
                                <i class="ri ri-pencil-line ri-20px"></i>
                            </a>
                            <a href="javascript:void(0);" 
                               onclick="deleteRecord(<?= $method['id'] ?>, '<?= $app['url'] ?>/paymentmethod/delete/:id', 'paymentMethodTable')" 
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
    initDataTableWithCheckbox('paymentMethodTable', {
        createUrl: '<?= $app['url'] ?>/paymentmethod/create',
        bulkDeleteUrl: '<?= $app['url'] ?>/paymentmethod/bulkdelete'
    });
    
    $('[data-bs-toggle="tooltip"]').tooltip();
});

// Función para cambiar el estado activo/inactivo
function toggleActive(id, activate) {
    const action = activate ? 'activar' : 'desactivar';
    
    Swal.fire({
        title: '¿Estás seguro?',
        text: `¿Deseas ${action} este método de pago?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, ' + action,
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: 'btn btn-primary me-3',
            cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('<?= $app['url'] ?>/paymentmethod/toggleactive/' + id, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'csrf_token=' + encodeURIComponent(document.querySelector('meta[name="csrf-token"]').content)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    notyf.success(data.message);
                    // Recargar la página directamente
                    setTimeout(() => location.reload(), 800);
                } else {
                    notyf.error(data.message);
                }
            })
            .catch(error => {
                notyf.error('Error al procesar la solicitud');
                console.error('Error:', error);
            });
        }
    });
}
</script>
<?php $pageScripts = ob_get_clean(); ?>

