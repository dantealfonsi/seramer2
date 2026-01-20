<!-- Filtros -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Filtros</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="<?= $app['url'] ?>/activitylog/index" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Usuario</label>
                    <select class="form-select" name="user_id" id="user_id">
                        <option value="">Todos los usuarios</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= (isset($filters['user_id']) && $filters['user_id'] == $user['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['username']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Acción</label>
                    <select class="form-select" name="action" id="action">
                        <option value="">Todas las acciones</option>
                        <?php foreach ($actions as $action): ?>
                            <option value="<?= htmlspecialchars($action) ?>" <?= (isset($filters['action']) && $filters['action'] == $action) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($action)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Tabla</label>
                    <select class="form-select" name="table_affected" id="table_affected">
                        <option value="">Todas las tablas</option>
                        <?php foreach ($tables as $table): ?>
                            <option value="<?= htmlspecialchars($table) ?>" <?= (isset($filters['table_affected']) && $filters['table_affected'] == $table) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($table) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Fecha Desde</label>
                    <input type="date" class="form-control" name="date_from" id="date_from" value="<?= $filters['date_from'] ?? '' ?>">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Fecha Hasta</label>
                    <input type="date" class="form-control" name="date_to" id="date_to" value="<?= $filters['date_to'] ?? '' ?>">
                </div>
                
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ri ri-search-line me-1"></i>
                        Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Actividades -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Historial de Actividades</h5>
        <div class="text-muted">
            Total: <?= number_format($pagination['total_records']) ?> registros
        </div>
    </div>
    <div class="card-datatable table-responsive">
        <table id="activityLogTable" class="datatables-customers table">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>Tabla</th>
                    <th>Registro ID</th>
                    <th>Fecha y Hora</th>
                    <th>IP</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Debug temporal
                $activitiesCount = is_array($activities) ? count($activities) : 0;
                if (empty($activities) || $activitiesCount === 0): 
                ?>
                <tr class="no-data-row">
                    <td colspan="7" class="text-center py-4">
                        <span class="text-muted">No se encontraron actividades</span>
                        <?php if (isset($pagination['total_records']) && $pagination['total_records'] > 0): ?>
                            <br><small class="text-warning">Total en BD: <?= $pagination['total_records'] ?>, pero no se pudieron cargar.</small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($activities as $activity): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                        <?= strtoupper(substr($activity['username'] ?? 'U', 0, 1)) ?>
                                    </span>
                                </div>
                                <div>
                                    <div class="fw-semibold"><?= htmlspecialchars($activity['user_name'] ?? $activity['username'] ?? 'N/A') ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($activity['username'] ?? '') ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php
                            $actionColors = [
                                'login' => 'success',
                                'logout' => 'secondary',
                                'insert' => 'primary',
                                'update' => 'warning',
                                'delete' => 'danger'
                            ];
                            $color = $actionColors[$activity['action'] ?? ''] ?? 'info';
                            ?>
                            <span class="badge bg-label-<?= $color ?>"><?= htmlspecialchars(ucfirst($activity['action'] ?? 'N/A')) ?></span>
                        </td>
                        <td>
                            <code><?= htmlspecialchars($activity['table_affected'] ?? '-') ?></code>
                        </td>
                        <td>
                            <?= !empty($activity['record_id']) ? '#' . $activity['record_id'] : '-' ?>
                        </td>
                        <td>
                            <?php if (!empty($activity['created_at'])): ?>
                                <span class="text-nowrap"><?= date('d/m/Y H:i:s', strtotime($activity['created_at'])) ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small class="text-muted"><?= htmlspecialchars($activity['ip_address'] ?? '-') ?></small>
                        </td>
                        <td>
                            <?php if (!empty($activity['id'])): ?>
                            <button type="button" 
                                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#activityDetailModal<?= $activity['id'] ?>"
                                    title="Ver detalles">
                                <i class="ri ri-eye-line ri-20px"></i>
                            </button>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    
                    <!-- Modal de Detalles -->
                    <div class="modal fade" id="activityDetailModal<?= $activity['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Detalles de Actividad</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong>Usuario:</strong><br>
                                            <?= htmlspecialchars($activity['user_name'] ?? $activity['username'] ?? 'N/A') ?>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Acción:</strong><br>
                                            <span class="badge bg-label-<?= $color ?>"><?= htmlspecialchars(ucfirst($activity['action'])) ?></span>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong>Tabla:</strong><br>
                                            <code><?= htmlspecialchars($activity['table_affected']) ?></code>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Registro ID:</strong><br>
                                            <?= $activity['record_id'] ? '#' . $activity['record_id'] : '-' ?>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong>Fecha y Hora:</strong><br>
                                            <?= date('d/m/Y H:i:s', strtotime($activity['created_at'])) ?>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>IP:</strong><br>
                                            <?= htmlspecialchars($activity['ip_address'] ?? '-') ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($activity['old_values']) || !empty($activity['new_values'])): ?>
                                    <div class="row">
                                        <div class="col-12">
                                            <hr>
                                            <?php if (!empty($activity['old_values'])): ?>
                                            <div class="mb-3">
                                                <strong>Valores Anteriores:</strong>
                                                <?php
                                                $oldValues = $activity['old_values'];
                                                // Si es string JSON, decodificarlo y volver a codificarlo con formato
                                                if (is_string($oldValues)) {
                                                    $decoded = json_decode($oldValues, true);
                                                    $oldValues = $decoded !== null ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $oldValues;
                                                } else {
                                                    $oldValues = json_encode($oldValues, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                                                }
                                                ?>
                                                <pre class="bg-light p-2 rounded mt-2" style="max-height: 200px; overflow-y: auto; font-size: 12px;"><?= htmlspecialchars($oldValues) ?></pre>
                                            </div>
                                            <?php endif; ?>
                                            <?php if (!empty($activity['new_values'])): ?>
                                            <div>
                                                <strong>Valores Nuevos:</strong>
                                                <?php
                                                $newValues = $activity['new_values'];
                                                // Si es string JSON, decodificarlo y volver a codificarlo con formato
                                                if (is_string($newValues)) {
                                                    $decoded = json_decode($newValues, true);
                                                    $newValues = $decoded !== null ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $newValues;
                                                } else {
                                                    $newValues = json_encode($newValues, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                                                }
                                                ?>
                                                <pre class="bg-light p-2 rounded mt-2" style="max-height: 200px; overflow-y: auto; font-size: 12px;"><?= htmlspecialchars($newValues) ?></pre>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Paginación -->
    <?php if ($pagination['total_pages'] > 1): ?>
    <div class="card-footer">
        <nav aria-label="Paginación">
            <ul class="pagination justify-content-center mb-0">
                <?php if ($pagination['current_page'] > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['current_page'] - 1])) ?>">
                        <i class="ri ri-arrow-left-s-line"></i>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <?php if ($i == 1 || $i == $pagination['total_pages'] || ($i >= $pagination['current_page'] - 2 && $i <= $pagination['current_page'] + 2)): ?>
                    <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php elseif ($i == $pagination['current_page'] - 3 || $i == $pagination['current_page'] + 3): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                <li class="page-item">
                    <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $pagination['current_page'] + 1])) ?>">
                        <i class="ri ri-arrow-right-s-line"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<?php ob_start(); ?>
<script>
$(document).ready(function() {
    // Inicializar DataTable solo si hay datos y no es la fila de "no hay datos"
    var table = $('#activityLogTable');
    var hasData = table.find('tbody tr:not(.no-data-row)').length > 0;
    
    if ($.fn.DataTable && hasData) {
        table.DataTable({
            order: [[4, 'desc']], // Ordenar por fecha descendente (columna 4 = Fecha y Hora)
            pageLength: <?= $pagination['per_page'] ?>,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            dom: 'rt',
            paging: false, // Desactivar paginación de DataTable ya que usamos paginación personalizada
            searching: false, // Desactivar búsqueda ya que tenemos filtros personalizados
            info: false, // Ocultar información de DataTables ya que tenemos nuestro propio contador
            columnDefs: [
                { orderable: false, targets: [6] } // Desactivar ordenamiento en columna de acciones
            ]
        });
    }
    
    // Configurar título de la tarjeta
    $('.head-label').html('<h5 class="card-title mb-0">Historial de Actividades</h5>');
});
</script>
<?php $pageScripts = ob_get_clean(); ?>

