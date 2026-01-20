<div class="row">
    <div class="col-12">
        <!-- Información del Adjudicatario -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Información del Adjudicatario</h5>
                <a href="<?= $app['url'] ?>/cobro/index" class="btn btn-sm btn-secondary">
                    <i class="ri ri-arrow-left-line me-1"></i>
                    Nueva Búsqueda
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-semibold" width="150">Nombre:</td>
                                <td><?= htmlspecialchars($awardee_name) ?></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Cédula:</td>
                                <td><?= htmlspecialchars($awardee['id_number']) ?></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Teléfono:</td>
                                <td><?= htmlspecialchars($awardee['phone'] ?? 'No registrado') ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-semibold" width="150">Email:</td>
                                <td><?= htmlspecialchars($awardee['email'] ?? 'No registrado') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Dirección:</td>
                                <td><?= htmlspecialchars($awardee['address'] ?? 'No registrada') ?></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">Contratos:</td>
                                <td><span class="badge bg-label-primary"><?= count($contracts) ?> contrato(s)</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Registro de Pagos -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Registro de Pagos</h5>
                <p class="text-muted mb-0 mt-2">
                    Listado completo de todos los registros de pago con tasa de euro asignada<br>
                    <small><i class="ri ri-information-line"></i> Los pagos se actualizan automáticamente sin recargar la página</small>
                </p>
            </div>
            <div class="card-body">
                <?php if (empty($contracts)): ?>
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <i class="ri ri-alert-line me-2 ri-24px"></i>
                        <div>
                            <h6 class="alert-heading mb-1">El adjudicatario no tiene contratos</h6>
                            <p class="mb-0">No se encontraron contratos asociados a este adjudicatario. Debe crear un contrato primero.</p>
                        </div>
                    </div>
                <?php elseif (empty($allPayments)): ?>
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <i class="ri ri-information-line me-2 ri-24px"></i>
                        <div>
                            <h6 class="alert-heading mb-1">No hay registros de pago con tasa asignada</h6>
                            <p class="mb-0">El adjudicatario tiene <?= count($contracts) ?> contrato(s), pero no hay registros de pago que tengan una tasa de euro asignada.</p>
                            <p class="mb-0 mt-2"><small class="text-muted">Nota: Los pagos requieren tener una tasa de euro asignada para poder gestionarse.</small></p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="table-responsive text-nowrap">
                        <table id="paymentsTable" class="table table-hover table-nowrap">
                            <thead>
                                <tr>
                                    <th>N° Factura</th>
                                    <th>Contrato</th>
                                    <th>Fecha Pago</th>
                                    <th>Tasa Euro</th>
                                    <th>Monto (€)</th>
                                    <th>Monto (Bs.)</th>
                                    <th>Pagado</th>
                                    <th>Saldo</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allPayments as $payment): 
                                    // Determinar el estado real del pago basado ÚNICAMENTE en el saldo restante
                                    $isPaid = ($payment['remaining_balance'] <= 0.01); // Pagado solo si el saldo es 0
                                    $isOverdue = strtotime($payment['payment_date']) < strtotime(date('Y-m-d')) && !$isPaid;
                                    $isPending = !$isPaid && !$isOverdue;
                                ?>
                                <tr data-payment-id="<?= $payment['id'] ?>">
                                    <td><strong>#<?= $payment['id'] ?></strong></td>
                                    <td>
                                        <small class="text-muted">
                                            Contrato #<?= $payment['contract_id'] ?><br>
                                            Año: <?= $payment['fiscal_year'] ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($payment['payment_date'])) ?>
                                        <?php if ($isOverdue): ?>
                                            <br><small class="text-danger fw-semibold">Vencida</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        Bs. <?= number_format($payment['euro_rate_value'], 2) ?>
                                        <br><small class="text-muted"><?= htmlspecialchars(ucfirst($payment['euro_rate_date'])) ?></small>
                                    </td>
                                    <td>€ <?= number_format($payment['amount_eur'], 2) ?></td>
                                    <td>Bs. <?= number_format($payment['amount_bs'], 2) ?></td>
                                    <td class="payment-paid">
                                        <span class="<?= $payment['total_paid'] > 0 ? 'text-success fw-semibold' : '' ?>">
                                            Bs. <?= number_format($payment['total_paid'], 2) ?>
                                        </span>
                                    </td>
                                    <td class="payment-balance">
                                        <span class="<?= $payment['remaining_balance'] > 0 ? 'text-warning fw-semibold' : 'text-success fw-semibold' ?>">
                                            Bs. <?= number_format($payment['remaining_balance'], 2) ?>
                                        </span>
                                    </td>
                                    <td class="payment-status">
                                        <?php if ($isPaid): ?>
                                            <span class="badge bg-label-success">Pagada</span>
                                        <?php elseif ($isOverdue): ?>
                                            <span class="badge bg-label-danger">Vencida</span>
                                        <?php else: ?>
                                            <span class="badge bg-label-warning">Pendiente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <?php if (!$isPaid): ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-primary btn-registrar-pago"
                                                    onclick="openPaymentModal(<?= $payment['id'] ?>, <?= $payment['remaining_balance'] ?>, <?= $payment['amount_bs'] ?>, <?= $payment['total_paid'] ?>)"
                                                    title="Registrar Pago">
                                                <i class="ri ri-cash-line"></i>
                                            </button>
                                            <?php endif; ?>
                                            <a href="<?= $app['url'] ?>/cobro/verFactura/<?= $payment['id'] ?>" 
                                               class="btn btn-sm btn-secondary"
                                               title="Ver Detalle">
                                                <i class="ri ri-eye-line"></i>
                                            </a>
                                            <a href="<?= $app['url'] ?>/cobro/imprimirFactura/<?= $payment['id'] ?>" 
                                               target="_blank"
                                               class="btn btn-sm btn-info"
                                               title="Imprimir Factura">
                                                <i class="ri ri-printer-line"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Registro de Pago -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="paymentForm" onsubmit="return false;">
                <input type="hidden" name="payment_id" id="payment_id">
                <input type="hidden" name="max_amount" id="max_amount">
                <input type="hidden" name="payment_amount_bs" id="payment_amount_bs">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <div class="modal-body">
                    <!-- Alerta informativa sobre pagos parciales -->
                    <div class="alert alert-info d-flex align-items-start mb-3" role="alert">
                        <i class="ri ri-information-line me-2 mt-1"></i>
                        <div>
                            <strong>Pagos Parciales:</strong> Puede realizar pagos parciales utilizando diferentes métodos de pago. El saldo restante se actualizará automáticamente.
                        </div>
                    </div>
                    
                    <!-- Resumen de abonos previos -->
                    <div id="installments_summary" class="mb-3" style="display: none;">
                        <div class="alert alert-success">
                            <h6 class="alert-heading mb-2">
                                <i class="ri ri-money-dollar-circle-line me-2"></i>Abonos Registrados
                            </h6>
                            <div id="installments_list"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">Monto a Pagar (Bs.) <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="form-control" 
                               id="amount" 
                               name="amount" 
                               step="0.01" 
                               min="0.01" 
                               required>
                        <div class="form-text">
                            <strong>Monto Total:</strong> <span id="total_amount_text">Bs. 0.00</span><br>
                            <strong>Pagado:</strong> <span id="paid_amount_text">Bs. 0.00</span><br>
                            <strong>Saldo Pendiente:</strong> <span id="remaining_balance_text" class="text-warning fw-bold">Bs. 0.00</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_method_id" class="form-label">Método de Pago <span class="text-danger">*</span></label>
                        <select class="form-select" id="payment_method_id" name="payment_method_id" required>
                            <option value="">Seleccione un método</option>
                            <?php foreach ($paymentMethods as $method): ?>
                                <option value="<?= $method['id'] ?>"><?= htmlspecialchars($method['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="concept" class="form-label">Concepto</label>
                        <textarea class="form-control" 
                                  id="concept" 
                                  name="concept" 
                                  rows="2" 
                                  placeholder="Concepto del pago">Pago de mensualidad</textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnRegistrarPago" class="btn btn-primary">
                        <i class="ri ri-save-line me-1"></i>
                        Registrar Pago
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
    // Verificar que jQuery esté disponible
    if (typeof jQuery === 'undefined') {
        console.error('❌ jQuery no está cargado!');
        document.addEventListener('DOMContentLoaded', function() {
            alert('Error: jQuery no está cargado. Por favor, recargue la página.');
        });
    } else {
        console.log('✅ jQuery disponible:', jQuery.fn.jquery);
        
        // Todo el código debe estar dentro de $(document).ready()
        jQuery(document).ready(function($) {
        let currentPaymentData = {};
        
        // Prevenir CUALQUIER redirección accidental
        window.onbeforeunload = null;
        
        // Log de navegación para debugging
        console.log('📍 Vista cargada:', window.location.href);
        console.log('✅ jQuery versión:', $.fn.jquery);
        
        // Inicializar DataTable
        <?php if (!empty($allPayments)): ?>
        const table = $('#paymentsTable').DataTable({
            language: {
                url: '<?= $app['url'] ?>/public/assets/json/datatable-es.json'
            },
            order: [[2, 'desc']], // Ordenar por fecha de pago descendente
            pageLength: 25,
            columnDefs: [
                { orderable: false, targets: [9] } // Columna de acciones no ordenable
            ],
            initComplete: function() {
                console.log('✅ DataTable inicializado correctamente');
            }
        });
        <?php endif; ?>
        
        // Abrir modal de pago
        function openPaymentModal(paymentId, remainingBalance, totalAmount, totalPaid) {
            console.log('🔍 openPaymentModal llamado con:', {
                paymentId: paymentId,
                remainingBalance: remainingBalance,
                totalAmount: totalAmount,
                totalPaid: totalPaid
            });
            
            currentPaymentData = {
                paymentId: paymentId,
                remainingBalance: remainingBalance,
                totalAmount: totalAmount,
                totalPaid: totalPaid
            };
            
            console.log('📝 Estableciendo payment_id a:', paymentId);
            $('#payment_id').val(paymentId);
            console.log('✅ Valor establecido en el campo:', $('#payment_id').val());
            $('#max_amount').val(remainingBalance);
            $('#payment_amount_bs').val(totalAmount);
            $('#amount').attr('max', remainingBalance);
            $('#amount').val(remainingBalance);
            
            // Actualizar textos informativos
            $('#total_amount_text').text('Bs. ' + parseFloat(totalAmount).toFixed(2));
            $('#paid_amount_text').text('Bs. ' + parseFloat(totalPaid).toFixed(2));
            $('#remaining_balance_text').text('Bs. ' + parseFloat(remainingBalance).toFixed(2));
            
            // Cargar abonos previos si existen
            if (totalPaid > 0) {
                loadPaymentInstallments(paymentId);
            } else {
                $('#installments_summary').hide();
            }
            
            $('#paymentModal').modal('show');
        }
        
        // Exponer función openPaymentModal globalmente para onclick
        window.openPaymentModal = openPaymentModal;
        
        // Cargar abonos previos
        function loadPaymentInstallments(paymentId) {
            $.ajax({
                url: '<?= $app['url'] ?>/cobro/getInstallments/' + paymentId,
                type: 'GET',
                success: function(response) {
                    if (response.success && response.installments.length > 0) {
                        let html = '<ul class="mb-0">';
                        response.installments.forEach(function(inst) {
                            html += '<li>' + 
                                    '<strong>Bs. ' + parseFloat(inst.amount).toFixed(2) + '</strong> ' +
                                    '(' + inst.payment_method_name + ') - ' +
                                    new Date(inst.date).toLocaleDateString('es-VE') +
                                    '</li>';
                        });
                        html += '</ul>';
                        $('#installments_list').html(html);
                        $('#installments_summary').show();
                    } else {
                        $('#installments_summary').hide();
                    }
                },
                error: function() {
                    $('#installments_summary').hide();
                }
            });
        }
        
        // Enviar formulario de pago con botón
        $('#btnRegistrarPago').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            console.log('🚀 Iniciando registro de pago...');
            console.log('🔍 Ubicación actual:', window.location.href);
            
            // Validar que tenemos los datos necesarios
            const paymentId = $('#payment_id').val();
            const amount = $('#amount').val();
            const paymentMethodId = $('#payment_method_id').val();
            
            if (!paymentId || !amount || !paymentMethodId) {
                notyf.error('Por favor complete todos los campos requeridos');
                return false;
            }
            
            console.log('📋 Datos del formulario:', {
                payment_id: paymentId,
                amount: amount,
                payment_method_id: paymentMethodId
            });
            
            const formData = $('#paymentForm').serialize();
            const submitBtn = $(this);
            
            // Deshabilitar botón
            submitBtn.prop('disabled', true).html('<i class="ri ri-loader-4-line ri-spin me-1"></i>Procesando...');
            
            $.ajax({
                url: '<?= $app['url'] ?>/cobro/registrarPago',
                type: 'POST',
                data: formData,
                dataType: 'json',
                cache: false,
                async: true,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                beforeSend: function() {
                    console.log('📤 Enviando petición AJAX...');
                    console.log('🔍 URL destino:', '<?= $app['url'] ?>/cobro/registrarPago');
                },
            success: function(response) {
                console.log('✅ Respuesta recibida:', response);
                console.log('🔍 Ubicación después de respuesta:', window.location.href);
                
                // Verificar que es un objeto JSON válido
                if (typeof response !== 'object') {
                    console.error('❌ Respuesta no es JSON válido:', response);
                    notyf.error('Error: Respuesta inválida del servidor');
                    submitBtn.prop('disabled', false).html('<i class="ri ri-save-line me-1"></i>Registrar Pago');
                    return false;
                }
                
                if (response.success) {
                    console.log('✅ Pago registrado exitosamente');
                    notyf.success(response.message);
                    
                    // Cerrar modal
                    $('#paymentModal').modal('hide');
                    
                    // Recargar la página después de 1 segundo para mostrar los cambios
                    console.log('🔄 Recargando página en 1 segundo...');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    console.warn('⚠️ Error en respuesta:', response.message);
                    notyf.error(response.message);
                    
                    // Restaurar botón solo si hay error
                    submitBtn.prop('disabled', false).html('<i class="ri ri-save-line me-1"></i>Registrar Pago');
                }
                
                    // Prevenir cualquier acción adicional
                    return false;
                },
                error: function(xhr, status, error) {
                console.error('❌ Error AJAX:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error,
                    ajaxStatus: status
                });
                
                console.log('🔍 Ubicación en error:', window.location.href);
                
                let message = 'Error al procesar el pago';
                
                // Intentar obtener el mensaje del servidor
                try {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        // Intentar parsear como JSON
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            message = response.message;
                        }
                    }
                } catch (e) {
                    console.error('Error al parsear respuesta:', e);
                    // Si hay un error 403, probablemente sea el CSRF
                    if (xhr.status === 403) {
                        message = 'Token de seguridad inválido. Por favor, recargue la página e intente nuevamente.';
                    } else if (xhr.status === 0) {
                        message = 'Error de conexión. Verifique su conexión a internet.';
                    } else if (xhr.status >= 500) {
                        message = 'Error del servidor. Por favor, intente nuevamente.';
                    }
                }
                
                notyf.error(message);
                
                    // Restaurar botón
                    submitBtn.prop('disabled', false).html('<i class="ri ri-save-line me-1"></i>Registrar Pago');
                    
                    // Prevenir cualquier acción adicional
                    return false;
                },
                complete: function() {
                    console.log('🏁 Petición AJAX completada');
                    console.log('🔍 Ubicación final en complete:', window.location.href);
                }
            });
            
            // Prevenir cualquier comportamiento predeterminado
            return false;
        });
        
        // Nota: Ya no se necesita updatePaymentRow() porque recargamos la página completa
        // después de registrar un pago para mostrar todos los cambios actualizados
        
        // Exponer funciones globalmente
        window.loadPaymentInstallments = loadPaymentInstallments;
            
            console.log('✅ Todas las funciones inicializadas correctamente');
            
        }); // Fin de jQuery(document).ready()
    } // Fin de verificación de jQuery
</script>
<?php 
$pageScripts = ob_get_clean(); 
?>
