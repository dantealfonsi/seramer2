<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Cajas</h5>
        <a href="<?= $app['url'] ?>/cashregister/create" class="btn btn-primary">
            <i class="ri ri-add-line me-1"></i>
            Crear Caja
        </a>
    </div>
    <div class="card-datatable table-responsive">
        <table id="cashRegistersTable" class="datatables-customers table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Usuario Asignado</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th class="text-nowrap">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($cashRegisters)): ?>
                <tr class="no-data-row">
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($cashRegisters as $cashRegister): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($cashRegister['name']) ?></strong>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                        <?= strtoupper(substr($cashRegister['username'] ?? 'U', 0, 1)) ?>
                                    </span>
                                </div>
                                <div>
                                    <div class="fw-semibold"><?= htmlspecialchars($cashRegister['assigned_user_name'] ?? $cashRegister['username'] ?? 'N/A') ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($cashRegister['username'] ?? '') ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?= !empty($cashRegister['description']) ? htmlspecialchars($cashRegister['description']) : '<span class="text-muted">-</span>' ?>
                        </td>
                        <td>
                            <?php if ($cashRegister['status'] === 'active'): ?>
                                <span class="badge bg-label-success">Activa</span>
                            <?php else: ?>
                                <span class="badge bg-label-secondary">Inactiva</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <a href="<?= $app['url'] ?>/cashregister/edit/<?= $cashRegister['id'] ?>" 
                                   class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect" 
                                   data-bs-toggle="tooltip" 
                                   title="Editar">
                                    <i class="ri ri-pencil-line ri-20px"></i>
                                </a>
                                <a href="javascript:void(0);" 
                                   onclick="deleteCashRegister(<?= $cashRegister['id'] ?>)" 
                                   class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect" 
                                   data-bs-toggle="tooltip" 
                                   title="Eliminar">
                                    <i class="ri ri-delete-bin-7-line ri-20px"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php ob_start(); ?>
<script>
$(document).ready(function() {
    // Inicializar DataTable solo si hay datos
    var table = $('#cashRegistersTable');
    var hasData = table.find('tbody tr:not(.no-data-row)').length > 0;
    
    if ($.fn.DataTable && hasData) {
        table.DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            columnDefs: [
                { orderable: false, targets: [4] } // Desactivar ordenamiento en columna de acciones
            ]
        });
    } else if (!hasData) {
        // Si no hay datos, mostrar mensaje
        table.find('tbody tr.no-data-row').html('<td colspan="5" class="text-center py-4"><span class="text-muted">No hay cajas registradas</span></td>');
    }
    
    // Configurar título de la tarjeta
    $('.head-label').html('<h5 class="card-title mb-0">Gestión de Cajas</h5>');
    
    // Inicializar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

function deleteCashRegister(id) {
    Swal.fire({
        title: '¿Eliminar Caja?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: 'btn btn-primary me-3',
            cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= $app['url'] ?>/cashregister/delete/' + id,
                method: 'POST',
                data: {
                    csrf_token: document.querySelector('meta[name="csrf-token"]')?.content
                },
                success: function(response) {
                    if (response.success) {
                        if (typeof notyf !== 'undefined') {
                            notyf.success(response.message || 'Caja eliminada exitosamente');
                        }
                        location.reload();
                    } else {
                        if (typeof notyf !== 'undefined') {
                            notyf.error(response.message || 'Error al eliminar la caja');
                        } else {
                            alert(response.message || 'Error al eliminar la caja');
                        }
                    }
                },
                error: function() {
                    if (typeof notyf !== 'undefined') {
                        notyf.error('Error de conexión');
                    } else {
                        alert('Error de conexión');
                    }
                }
            });
        }
    });
}
</script>
<?php $pageScripts = ob_get_clean(); ?>

