<!-- Métricas de Contratos -->
<div class="row mb-4">
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="card-info">
                        <p class="card-text mb-1">Total de Contratos</p>
                        <div class="d-flex align-items-end mb-1">
                            <h4 class="card-title mb-0 me-2"><?= number_format($metrics['total'] ?? 0) ?></h4>
                        </div>
                    </div>
                    <div class="card-icon">
                        <span class="badge bg-label-primary rounded-circle p-2">
                            <i class="ri ri-file-list-line ri-24px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="card-info">
                        <p class="card-text mb-1">Contratos Activos</p>
                        <div class="d-flex align-items-end mb-1">
                            <h4 class="card-title mb-0 me-2"><?= number_format($metrics['active'] ?? 0) ?></h4>
                        </div>
                    </div>
                    <div class="card-icon">
                        <span class="badge bg-label-success rounded-circle p-2">
                            <i class="ri ri-checkbox-circle-line ri-24px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="card-info">
                        <p class="card-text mb-1">Contratos Cancelados</p>
                        <div class="d-flex align-items-end mb-1">
                            <h4 class="card-title mb-0 me-2"><?= number_format($metrics['canceled'] ?? 0) ?></h4>
                        </div>
                    </div>
                    <div class="card-icon">
                        <span class="badge bg-label-danger rounded-circle p-2">
                            <i class="ri ri-close-circle-line ri-24px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

        <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex gap-3 align-items-center flex-wrap">
            
            
            <!-- Acciones masivas -->
            <div id="bulkActionsContainer" class="d-none align-items-center gap-2">
                <span class="badge bg-label-primary" id="selectedCount">0 seleccionados</span>
                
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ri ri-settings-3-line me-1"></i>
                        Acciones
                    </button>
                    <ul class="dropdown-menu">
                        <li><h6 class="dropdown-header">Cambiar Estado del Contrato</h6></li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0);" onclick="bulkChangeStatus('active')">
                                <i class="ri ri-checkbox-circle-line text-success me-2"></i>
                                <span>Marcar como Activo</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0);" onclick="bulkChangeStatus('renewed')">
                                <i class="ri ri-refresh-line text-info me-2"></i>
                                <span>Marcar como Renovado</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0);" onclick="bulkChangeStatus('canceled')">
                                <i class="ri ri-close-circle-line text-danger me-2"></i>
                                <span>Marcar como Cancelado</span>
                            </a>
                        </li>
                        
                        <li><hr class="dropdown-divider"></li>
                        
                        <li><h6 class="dropdown-header">Cambiar Estado de Pago</h6></li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0);" onclick="bulkChangePaymentStatus('up to date')">
                                <i class="ri ri-check-line text-success me-2"></i>
                                <span>Marcar Al día</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0);" onclick="bulkChangePaymentStatus('delinquent')">
                                <i class="ri ri-alert-line text-warning me-2"></i>
                                <span>Marcar Moroso</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="javascript:void(0);" onclick="bulkChangePaymentStatus('unable to pay')">
                                <i class="ri ri-error-warning-line text-danger me-2"></i>
                                <span>Marcar Insolvente</span>
                            </a>
                        </li>
                        
                        <li><hr class="dropdown-divider"></li>
                        
                        <li>
                            <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="bulkDeleteContracts()">
                                <i class="ri ri-delete-bin-7-line me-2"></i>
                                <span>Eliminar Contratos</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
                    </div>
    <div class="card-datatable table-responsive">
        <table id="contractsTable" class="datatables-customers table">
                            <thead>
                                <tr>
                    <th></th>
                                    <th>Adjudicatario</th>
                                    <th>Año Fiscal</th>
                                    <th>Tipo</th>
                    <th class="text-nowrap">Categorías</th>
                    <th class="text-nowrap">Locales</th>
                    <th class="text-nowrap">Fecha Inicio</th>
                    <th class="text-nowrap">Estatus</th>
                    <th class="text-nowrap">Estatus de Pago</th>
                    <th class="text-nowrap">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contracts as $contract): ?>
                <tr data-id="<?= $contract['id'] ?>" data-fiscal-year="<?= $contract['fiscal_year'] ?>">
                    <td>
                        <input type="checkbox" class="form-check-input contract-checkbox" value="<?= $contract['id'] ?>">
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div>
                                <h6 class="mb-0 text-nowrap"><?= htmlspecialchars($contract['awardee_name']) ?></h6>
                                <small class="text-muted">Contrato #<?= $contract['id'] ?></small>
                            </div>
                        </div>
                    </td>
                                    <td>
                                        <span class="badge bg-label-primary"><?= $contract['fiscal_year'] ?></span>
                                    </td>
                                    <td>
                                        <?php if ($contract['type'] === 'simultaneous'): ?>
                                            <span class="badge bg-label-info">Simultáneo</span>
                                        <?php else: ?>
                                            <span class="badge bg-label-warning">Anticipado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                        <?php if ($contract['categories_count'] > 0 && !empty($contract['categories_list'])): ?>
                            <?php 
                            $categories = explode('||', $contract['categories_list']);
                            foreach ($categories as $category): 
                                if (strpos($category, 'INT:') === 0):
                                    $catName = substr($category, 4);
                                    ?>
                                    <span class="badge bg-label-primary me-1 mb-1"><?= htmlspecialchars($catName) ?></span>
                                <?php elseif (strpos($category, 'EXT:') === 0):
                                    $catName = substr($category, 4);
                                    ?>
                                    <span class="badge bg-label-info me-1 mb-1"><?= htmlspecialchars($catName) ?></span>
                                <?php endif;
                            endforeach; ?>
                        <?php else: ?>
                            <span class="badge bg-label-secondary">Sin categorías</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($contract['locations_count'] > 0 && !empty($contract['locations_list'])): ?>
                            <ul class="list-unstyled mb-0">
                                <?php 
                                $locations = explode('||', $contract['locations_list']);
                                foreach ($locations as $location): 
                                    // Parsear el formato: "001 (ZONA A-SECTOR 1)"
                                    if (preg_match('/^(.*?)\s*\((.*?)\)$/', trim($location), $matches)) {
                                        $stallNumber = trim($matches[1]);
                                        $ubicacion = trim($matches[2]);
                                    } else {
                                        $stallNumber = trim($location);
                                        $ubicacion = '';
                                    }
                                ?>
                                    <li class="d-inline-block me-1 mb-1">
                                        <span class="badge bg-label-secondary" 
                                              data-bs-toggle="tooltip" 
                                              data-bs-placement="top" 
                                              title="<?= htmlspecialchars($ubicacion) ?>">
                                            <i class="ri ri-map-pin-line me-1"></i><?= htmlspecialchars($stallNumber) ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <span class="badge bg-label-secondary">Sin locales</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="text-nowrap"><?= date('d/m/Y', strtotime($contract['start_date'])) ?></span>
                    </td>
                    <td>
                        <?php
                        $statusConfig = [
                            'active' => ['label' => 'Activo', 'color' => 'success'],
                            'renewed' => ['label' => 'Renovado', 'color' => 'info'],
                            'canceled' => ['label' => 'Cancelado', 'color' => 'danger']
                        ];
                        $status = $contract['status'] ?? 'active';
                        $statusInfo = $statusConfig[$status] ?? ['label' => ucfirst($status), 'color' => 'secondary'];
                        ?>
                        <span class="badge bg-<?= $statusInfo['color'] ?> text-dark"><?= $statusInfo['label'] ?></span>
                    </td>
                    <td>
                        <?php
                        $paymentStatusConfig = [
                            'up to date' => ['label' => 'Al día', 'color' => 'success'],
                            'delinquent' => ['label' => 'Moroso', 'color' => 'warning'],
                            'unable to pay' => ['label' => 'Insolvente', 'color' => 'danger']
                        ];
                        $paymentStatus = $contract['status_payment'] ?? 'up to date';
                        $paymentStatusInfo = $paymentStatusConfig[$paymentStatus] ?? ['label' => ucfirst($paymentStatus), 'color' => 'secondary'];
                        ?>
                        <span class="badge bg-<?= $paymentStatusInfo['color'] ?> text-dark"><?= $paymentStatusInfo['label'] ?></span>
                                    </td>
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow waves-effect" 
                                    data-bs-toggle="dropdown" 
                                    data-bs-auto-close="true"
                                    data-bs-boundary="viewport"
                                    aria-expanded="false">
                                <i class="ri ri-more-2-line ri-20px"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <!-- Ver Detalle -->
                                <li>
                                    <a class="dropdown-item" href="<?= $app['url'] ?>/contract/detail/<?= $contract['id'] ?>">
                                        <i class="ri ri-eye-line me-2"></i>
                                        <span>Ver Detalle</span>
                                    </a>
                                </li>
                                
                                <!-- Editar -->
                                <li>
                                    <a class="dropdown-item" href="<?= $app['url'] ?>/contract/edit/<?= $contract['id'] ?>">
                                        <i class="ri ri-edit-line me-2"></i>
                                        <span>Editar Contrato</span>
                                    </a>
                                </li>
                                
                                <li><hr class="dropdown-divider"></li>
                                
                                <!-- Cambiar Estado -->
                                <li>
                                    <h6 class="dropdown-header">Cambiar Estado del Contrato</h6>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" 
                                       onclick="changeContractStatus(<?= $contract['id'] ?>, 'active')">
                                        <i class="ri ri-checkbox-circle-line text-success me-2"></i>
                                        <span>Marcar como Activo</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" 
                                       onclick="changeContractStatus(<?= $contract['id'] ?>, 'renewed')">
                                        <i class="ri ri-refresh-line text-info me-2"></i>
                                        <span>Marcar como Renovado</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" 
                                       onclick="changeContractStatus(<?= $contract['id'] ?>, 'canceled')">
                                        <i class="ri ri-close-circle-line text-danger me-2"></i>
                                        <span>Marcar como Cancelado</span>
                                    </a>
                                </li>
                                
                                <li><hr class="dropdown-divider"></li>
                                
                                <!-- Cambiar Estado de Pago -->
                                <li>
                                    <h6 class="dropdown-header">Cambiar Estado de Pago</h6>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" 
                                       onclick="changeContractPaymentStatus(<?= $contract['id'] ?>, 'up to date')">
                                        <i class="ri ri-check-line text-success me-2"></i>
                                        <span>Marcar Al día</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" 
                                       onclick="changeContractPaymentStatus(<?= $contract['id'] ?>, 'delinquent')">
                                        <i class="ri ri-alert-line text-warning me-2"></i>
                                        <span>Marcar Moroso</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" 
                                       onclick="changeContractPaymentStatus(<?= $contract['id'] ?>, 'unable to pay')">
                                        <i class="ri ri-error-warning-line text-danger me-2"></i>
                                        <span>Marcar Insolvente</span>
                                    </a>
                                </li>
                                
                                <li><hr class="dropdown-divider"></li>
                                
                                <!-- Eliminar -->
                                <li>
                                    <a class="dropdown-item text-danger" href="javascript:void(0);" 
                                       onclick="deleteRecord(<?= $contract['id'] ?>, '<?= $app['url'] ?>/contract/delete/:id', 'contractsTable')">
                                        <i class="ri ri-delete-bin-7-line me-2"></i>
                                        <span>Eliminar Contrato</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
    </div>
</div>

<style>
/* Solución para dropdowns en tablas con overflow */
.table-responsive {
    overflow-x: auto;
    overflow-y: visible !important;
}

.card-datatable {
    overflow: visible !important;
}

/* Asegurar que los dropdowns se muestren por encima de todo */
.dropdown-menu {
    z-index: 1050 !important;
}

/* Fix para que el dropdown no genere scroll horizontal */
.table-responsive .dropdown {
    position: static !important;
}
</style>

<?php ob_start(); ?>
<script>
$(document).ready(function() {
    // Función para inicializar tooltips
    function initTooltips() {
        // Destruir tooltips existentes primero
        $('[data-bs-toggle="tooltip"]').each(function() {
            const tooltip = bootstrap.Tooltip.getInstance(this);
            if (tooltip) {
                tooltip.dispose();
            }
        });
        
        // Inicializar nuevos tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Inicializar DataTable con checkbox (sin botón eliminar automático)
    const table = initDataTableWithCheckbox('contractsTable', {
        createUrl: '<?= $app['url'] ?>/contract/create',
        bulkDeleteUrl: false, // Desactivar botón de eliminar, se usa el del dropdown de acciones
        order: [[1, 'asc']], // Ordenar por Adjudicatario
        pageLength: 25,
        drawCallback: function() {
            // Reinicializar tooltips después de cada redibujado de la tabla
            initTooltips();
        }
    });
    
    // Inicializar tooltips en carga inicial
    initTooltips();
    
    // Manejar selección de checkboxes para mostrar acciones masivas
    table.on('select deselect', function() {
        const selectedCount = table.rows({ selected: true }).count();
        
        if (selectedCount > 0) {
            $('#bulkActionsContainer').removeClass('d-none').addClass('d-flex');
            $('#selectedCount').text(selectedCount + ' seleccionado' + (selectedCount > 1 ? 's' : ''));
        } else {
            $('#bulkActionsContainer').removeClass('d-flex').addClass('d-none');
        }
    });
    
    // Fix para dropdowns en tablas con scroll
    // Usar Popper.js para posicionar dropdowns fuera del contenedor
    $(document).on('show.bs.dropdown', '.table-responsive .dropdown', function() {
        const $dropdown = $(this);
        const $menu = $dropdown.find('.dropdown-menu');
        
        // Mover el dropdown-menu al body temporalmente
        $menu.appendTo('body');
        
        // Calcular posición
        const $toggle = $dropdown.find('.dropdown-toggle');
        const offset = $toggle.offset();
        const height = $toggle.outerHeight();
        
        $menu.css({
            'position': 'absolute',
            'top': (offset.top + height) + 'px',
            'left': (offset.left - $menu.outerWidth() + $toggle.outerWidth()) + 'px',
            'display': 'block'
        });
    });
    
    $(document).on('hide.bs.dropdown', '.table-responsive .dropdown', function() {
        const $dropdown = $(this);
        const $menu = $dropdown.find('.dropdown-menu');
        
        // Devolver el dropdown-menu a su contenedor original
        $menu.appendTo($dropdown);
        $menu.css({
            'position': '',
            'top': '',
            'left': '',
            'display': ''
        });
    });
});

// ==========================================
// Funciones para Cambiar Estado Individual
// ==========================================

/**
 * Cambia el estado de un contrato individual
 * @param {number} contractId - ID del contrato
 * @param {string} newStatus - Nuevo estado (active, renewed, canceled)
 */
function changeContractStatus(contractId, newStatus) {
    const statusLabels = {
        'active': 'Activo',
        'renewed': 'Renovado',
        'canceled': 'Cancelado'
    };
    
    Swal.fire({
        title: '¿Cambiar Estado?',
        text: `¿Estás seguro de cambiar el estado de este contrato a "${statusLabels[newStatus]}"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: 'btn btn-primary me-3',
            cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= $app['url'] ?>/contract/bulkUpdateStatus',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    ids: [contractId],
                    status: newStatus,
                    csrf_token: document.querySelector('meta[name="csrf-token"]')?.content
                }),
                success: function(response) {
                    if (response.success) {
                        if (typeof notyf !== 'undefined') {
                            notyf.success('Estado actualizado exitosamente');
                        }
                        location.reload();
                    } else {
                        if (typeof notyf !== 'undefined') {
                            notyf.error(response.message || 'Error al actualizar el estado');
                        } else {
                            alert(response.message || 'Error al actualizar el estado');
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

/**
 * Cambia el estado de pago de un contrato individual
 * @param {number} contractId - ID del contrato
 * @param {string} newStatus - Nuevo estado de pago (up to date, delinquent, unable to pay)
 */
function changeContractPaymentStatus(contractId, newStatus) {
    const statusLabels = {
        'up to date': 'Al día',
        'delinquent': 'Moroso',
        'unable to pay': 'Insolvente'
    };
    
    Swal.fire({
        title: '¿Cambiar Estado de Pago?',
        text: `¿Estás seguro de cambiar el estado de pago de este contrato a "${statusLabels[newStatus]}"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: 'btn btn-primary me-3',
            cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?= $app['url'] ?>/contract/bulkUpdatePaymentStatus',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    ids: [contractId],
                    status_payment: newStatus,
                    csrf_token: document.querySelector('meta[name="csrf-token"]')?.content
                }),
                success: function(response) {
                    if (response.success) {
                        if (typeof notyf !== 'undefined') {
                            notyf.success('Estado de pago actualizado exitosamente');
                        }
                        location.reload();
                    } else {
                        if (typeof notyf !== 'undefined') {
                            notyf.error(response.message || 'Error al actualizar el estado de pago');
                        } else {
                            alert(response.message || 'Error al actualizar el estado de pago');
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

// ==========================================
// Funciones para Cambiar Estado Masivo (Bulk)
// ==========================================

/**
 * Obtiene los IDs de los contratos seleccionados en el DataTable
 * @returns {array} Array de IDs seleccionados
 */
function getSelectedContractIds() {
    const table = $('#contractsTable').DataTable();
    const selectedRows = table.rows({ selected: true }).nodes();
    const ids = [];
    
    $(selectedRows).each(function() {
        const id = $(this).attr('data-id');
        if (id) ids.push(id);
    });
    
    return ids;
}

/**
 * Cambia el estado de múltiples contratos (bulk)
 * @param {string} newStatus - Nuevo estado (active, renewed, canceled)
 */
function bulkChangeStatus(newStatus) {
    const ids = getSelectedContractIds();
    
    if (ids.length === 0) {
        Swal.fire({
            title: 'Sin Selección',
            text: 'Por favor selecciona al menos un contrato',
            icon: 'warning',
            confirmButtonText: 'Entendido',
            customClass: {
                confirmButton: 'btn btn-primary'
            },
            buttonsStyling: false
        });
        return;
    }
    
    const statusLabels = {
        'active': 'Activo',
        'renewed': 'Renovado',
        'canceled': 'Cancelado'
    };
    
    Swal.fire({
        title: '¿Cambiar Estado?',
        html: `¿Estás seguro de cambiar el estado de <strong>${ids.length}</strong> contrato(s) a "<strong>${statusLabels[newStatus]}</strong>"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: 'btn btn-primary me-3',
            cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Procesando...',
                html: 'Actualizando contratos, por favor espera',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: '<?= $app['url'] ?>/contract/bulkUpdateStatus',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    ids: ids,
                    status: newStatus,
                    csrf_token: document.querySelector('meta[name="csrf-token"]')?.content
                }),
                success: function(response) {
                    Swal.close();
                    
                    if (response.success) {
                        if (typeof notyf !== 'undefined') {
                            notyf.success(`${response.updated} contrato(s) actualizado(s) exitosamente`);
                        }
                        
                        // Recargar después de un breve delay
                        setTimeout(function() {
                            location.reload();
                        }, 500);
                    } else {
                        if (typeof notyf !== 'undefined') {
                            notyf.error(response.message || 'Error al actualizar los contratos');
                        }
                        
                        Swal.fire({
                            title: 'Error',
                            text: response.message || 'No se pudieron actualizar los contratos',
                            icon: 'error',
                            confirmButtonText: 'Entendido',
                            customClass: {
                                confirmButton: 'btn btn-danger'
                            },
                            buttonsStyling: false
                        });
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    
                    if (typeof notyf !== 'undefined') {
                        notyf.error('Error de conexión al actualizar los contratos');
                    }
                    
                    Swal.fire({
                        title: 'Error de Conexión',
                        text: 'No se pudo conectar con el servidor',
                        icon: 'error',
                        confirmButtonText: 'Entendido',
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    });
                }
            });
        }
    });
}

/**
 * Cambia el estado de pago de múltiples contratos (bulk)
 * @param {string} newStatus - Nuevo estado de pago (up to date, delinquent, unable to pay)
 */
function bulkChangePaymentStatus(newStatus) {
    const ids = getSelectedContractIds();
    
    if (ids.length === 0) {
        Swal.fire({
            title: 'Sin Selección',
            text: 'Por favor selecciona al menos un contrato',
            icon: 'warning',
            confirmButtonText: 'Entendido',
            customClass: {
                confirmButton: 'btn btn-primary'
            },
            buttonsStyling: false
        });
        return;
    }
    
    const statusLabels = {
        'up to date': 'Al día',
        'delinquent': 'Moroso',
        'unable to pay': 'Insolvente'
    };
    
    Swal.fire({
        title: '¿Cambiar Estado de Pago?',
        html: `¿Estás seguro de cambiar el estado de pago de <strong>${ids.length}</strong> contrato(s) a "<strong>${statusLabels[newStatus]}</strong>"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: 'btn btn-primary me-3',
            cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Procesando...',
                html: 'Actualizando estados de pago, por favor espera',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: '<?= $app['url'] ?>/contract/bulkUpdatePaymentStatus',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    ids: ids,
                    status_payment: newStatus,
                    csrf_token: document.querySelector('meta[name="csrf-token"]')?.content
                }),
                success: function(response) {
                    Swal.close();
                    
                    if (response.success) {
                        if (typeof notyf !== 'undefined') {
                            notyf.success(`${response.updated} estado(s) de pago actualizado(s) exitosamente`);
                        }
                        
                        // Recargar después de un breve delay
                        setTimeout(function() {
                            location.reload();
                        }, 500);
                    } else {
                        if (typeof notyf !== 'undefined') {
                            notyf.error(response.message || 'Error al actualizar los estados de pago');
                        }
                        
                        Swal.fire({
                            title: 'Error',
                            text: response.message || 'No se pudieron actualizar los estados de pago',
                            icon: 'error',
                            confirmButtonText: 'Entendido',
                            customClass: {
                                confirmButton: 'btn btn-danger'
                            },
                            buttonsStyling: false
                        });
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    
                    if (typeof notyf !== 'undefined') {
                        notyf.error('Error de conexión al actualizar los estados de pago');
                    }
                    
                    Swal.fire({
                        title: 'Error de Conexión',
                        text: 'No se pudo conectar con el servidor',
                        icon: 'error',
                        confirmButtonText: 'Entendido',
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    });
                }
            });
        }
    });
}

/**
 * Elimina múltiples contratos (bulk delete)
 */
function bulkDeleteContracts() {
    const ids = getSelectedContractIds();
    
    if (ids.length === 0) {
        Swal.fire({
            title: 'Sin Selección',
            text: 'Por favor selecciona al menos un contrato para eliminar',
            icon: 'warning',
            confirmButtonText: 'Entendido',
            customClass: {
                confirmButton: 'btn btn-primary'
            },
            buttonsStyling: false
        });
        return;
    }
    
    Swal.fire({
        title: '¿Eliminar Contratos?',
        html: `¿Estás seguro de eliminar <strong>${ids.length}</strong> contrato(s)?<br><br>` +
              `<span class="text-danger"><i class="ri ri-alert-line"></i> Esta acción no se puede deshacer.</span><br><br>` +
              `<small class="text-muted">Nota: Solo se eliminarán los contratos que no tengan abonos registrados.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: 'btn btn-danger me-3',
            cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Procesando...',
                html: 'Eliminando contratos, por favor espera',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: '<?= $app['url'] ?>/contract/bulkdelete',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    ids: ids,
                    csrf_token: document.querySelector('meta[name="csrf-token"]')?.content
                }),
                success: function(response) {
                    Swal.close();
                    
                    if (response.success) {
                        let message = `${response.deleted} contrato(s) eliminado(s) exitosamente`;
                        
                        // Mostrar errores si los hay
                        if (response.errors && response.errors.length > 0) {
                            message += `\n\nAdvertencias:\n${response.errors.join('\n')}`;
                            
                            Swal.fire({
                                title: 'Eliminación Parcial',
                                html: message.replace(/\n/g, '<br>'),
                                icon: 'warning',
                                confirmButtonText: 'Entendido',
                                customClass: {
                                    confirmButton: 'btn btn-warning'
                                },
                                buttonsStyling: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            if (typeof notyf !== 'undefined') {
                                notyf.success(message);
                            }
                            
                            setTimeout(function() {
                                location.reload();
                            }, 500);
                        }
                    } else {
                        if (typeof notyf !== 'undefined') {
                            notyf.error(response.message || 'Error al eliminar los contratos');
                        }
                        
                        Swal.fire({
                            title: 'Error',
                            html: response.message || 'No se pudieron eliminar los contratos',
                            icon: 'error',
                            confirmButtonText: 'Entendido',
                            customClass: {
                                confirmButton: 'btn btn-danger'
                            },
                            buttonsStyling: false
                        });
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    
                    let errorMessage = 'No se pudo conectar con el servidor';
                    
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        // Mantener mensaje por defecto
                    }
                    
                    if (typeof notyf !== 'undefined') {
                        notyf.error('Error al eliminar los contratos');
                    }
                    
                    Swal.fire({
                        title: 'Error de Conexión',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'Entendido',
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    });
                }
            });
        }
    });
}
</script>
<?php $pageScripts = ob_get_clean(); ?>
