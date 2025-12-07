<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura <?= htmlspecialchars($payment['payment_reference'] ?? '') ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier', monospace;
            font-size: 10px;
            line-height: 1.3;
            color: #000;
            background: white;
            padding: 10px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 2px dashed #000;
            padding-bottom: 8px;
        }
        
        .header h1 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .header p {
            font-size: 9px;
            margin: 2px 0;
        }
        
        .section {
            margin: 8px 0;
            padding: 5px 0;
        }
        
        .section-title {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 4px;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
        }
        
        .info-row {
            margin: 2px 0;
            font-size: 9px;
            padding: 2px 0;
        }
        
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 40%;
        }
        
        .info-value {
            display: inline-block;
            width: 58%;
            text-align: right;
        }
        
        .amount-section {
            background-color: #f5f5f5;
            padding: 8px;
            margin: 8px 0;
            border: 1px solid #000;
        }
        
        .amount-row {
            margin: 3px 0;
            font-size: 10px;
            padding: 2px 0;
        }
        
        .amount-label {
            font-weight: bold;
            display: inline-block;
            width: 48%;
        }
        
        .amount-value {
            display: inline-block;
            width: 48%;
            text-align: right;
        }
        
        .amount-row.total {
            font-size: 11px;
            font-weight: bold;
            border-top: 2px solid #000;
            padding-top: 4px;
            margin-top: 6px;
        }
        
        .installments-section {
            margin: 8px 0;
            border: 1px solid #ccc;
            padding: 6px;
            background-color: #f9f9f9;
        }
        
        .installment-title {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        
        .installment-item {
            font-size: 8px;
            margin: 2px 0;
            padding: 2px 0;
            border-bottom: 1px dotted #ccc;
        }
        
        .overdue-section {
            background-color: #fff9e6;
            border: 1px solid #856404;
            padding: 6px;
            margin: 8px 0;
        }
        
        .overdue-title {
            font-size: 10px;
            font-weight: bold;
            color: #856404;
            margin-bottom: 4px;
        }
        
        .overdue-item {
            font-size: 8px;
            margin: 2px 0;
            padding-left: 8px;
        }
        
        .footer {
            margin-top: 12px;
            text-align: center;
            border-top: 2px dashed #000;
            padding-top: 8px;
            font-size: 8px;
        }
        
        .footer p {
            margin: 2px 0;
        }
        
        .paid-stamp {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: #28a745;
            border: 3px solid #28a745;
            padding: 8px;
            margin: 8px 0;
        }
        
        .pending-stamp {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            color: #ffc107;
            border: 3px solid #ffc107;
            padding: 6px;
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <div class="header">
        <h1>SISTEMA DE GESTIÓN MUNICIPAL DE MERCADO SERAMER</h1>
        <p>GESTIÓN DE COBROS</p>
        <p>FACTURA DE PAGO</p>
        <p>Fecha: <?= date('d/m/Y H:i') ?></p>
    </div>
    
    <!-- Información de la Factura -->
    <div class="section">
        <div class="section-title">Datos de la Factura</div>
        <div class="info-row">
            <span class="info-label">N° Factura:</span>
            <span class="info-value"><?= htmlspecialchars($payment['payment_reference'] ?? 'N/A') ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">N° Interno:</span>
            <span class="info-value">#<?= $payment['id'] ?? '' ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Contrato:</span>
            <span class="info-value">#<?= $payment['contract_id'] ?? '' ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Año Fiscal:</span>
            <span class="info-value"><?= $payment['fiscal_year'] ?? '' ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Fecha Pago:</span>
            <span class="info-value"><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Mes Facturado:</span>
            <span class="info-value">
                <?php 
                    $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                    $mes = date('n', strtotime($payment['payment_date'])) - 1;
                    echo $meses[$mes] . ' ' . date('Y', strtotime($payment['payment_date']));
                ?>
            </span>
        </div>
    </div>
    
    <!-- Información del Cliente -->
    <div class="section">
        <div class="section-title">Datos del Cliente</div>
        <div class="info-row">
            <span class="info-label">Nombre:</span>
            <span class="info-value"><?= htmlspecialchars($awardee_name ?? '') ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Cédula:</span>
            <span class="info-value"><?= htmlspecialchars($payment['awardee_id_number'] ?? '') ?></span>
        </div>
        <?php if (!empty($payment['awardee_phone'])): ?>
        <div class="info-row">
            <span class="info-label">Teléfono:</span>
            <span class="info-value"><?= htmlspecialchars($payment['awardee_phone']) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($payment['awardee_address'])): ?>
        <div class="info-row">
            <span class="info-label">Dirección:</span>
            <span class="info-value"><?= htmlspecialchars(substr($payment['awardee_address'], 0, 35)) ?><?= strlen($payment['awardee_address']) > 35 ? '...' : '' ?></span>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Tasa del Euro -->
    <div class="section">
        <div class="section-title">Tasa del Euro</div>
        <div class="info-row">
            <span class="info-label">Tasa:</span>
            <span class="info-value">Bs. <?= number_format($payment['euro_rate_value'] ?? 0, 2, ',', '.') ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Fecha Tasa:</span>
            <span class="info-value"><?= htmlspecialchars(ucfirst($payment['euro_rate_date'] ?? '')) ?></span>
        </div>
    </div>
    
    <!-- Detalle de Montos -->
    <div class="amount-section">
        <div class="amount-row">
            <span class="amount-label">Monto (EUR):</span>
            <span class="amount-value">€ <?= number_format($payment['amount_eur'] ?? 0, 2, ',', '.') ?></span>
        </div>
        <div class="amount-row">
            <span class="amount-label">MONTO TOTAL (Bs.):</span>
            <span class="amount-value">Bs. <?= number_format($payment['amount_bs'] ?? 0, 2, ',', '.') ?></span>
        </div>
        <div class="amount-row">
            <span class="amount-label">Total Pagado:</span>
            <span class="amount-value">Bs. <?= number_format($payment['total_paid'] ?? 0, 2, ',', '.') ?></span>
        </div>
        <div class="amount-row total" style="color: <?= ($payment['remaining_balance'] ?? 0) > 0 ? '#856404' : '#28a745' ?>;">
            <span class="amount-label">SALDO PENDIENTE:</span>
            <span class="amount-value">Bs. <?= number_format($payment['remaining_balance'] ?? 0, 2, ',', '.') ?></span>
        </div>
    </div>
    
    <!-- Abonos Realizados -->
    <?php if (!empty($installments) && count($installments) > 0): ?>
    <div class="installments-section">
        <div class="installment-title">ABONOS REALIZADOS</div>
        <?php foreach ($installments as $inst): ?>
        <div class="installment-item">
            <?= date('d/m/Y', strtotime($inst['date'])) ?> - <?= htmlspecialchars($inst['payment_method_name'] ?? 'N/A') ?> - Bs. <?= number_format($inst['amount'], 2, ',', '.') ?>
            <br><?= htmlspecialchars($inst['concept'] ?? '') ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- Facturas Vencidas -->
    <?php if (!empty($overduePayments) && count($overduePayments) > 0): ?>
    <div class="overdue-section">
        <div class="overdue-title">FACTURAS VENCIDAS PENDIENTES</div>
        <?php foreach ($overduePayments as $overdue): 
            if ($overdue['id'] == $payment['id']) continue; // No mostrar la factura actual
        ?>
        <div class="overdue-item">
            Factura #<?= $overdue['id'] ?> - Vencimiento: <?= date('d/m/Y', strtotime($overdue['payment_date'])) ?> - Saldo: Bs. <?= number_format($overdue['amount_bs'] - $overdue['total_paid'], 2, ',', '.') ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- Sello de Estado -->
    <?php if (($payment['remaining_balance'] ?? 0) <= 0.01): ?>
    <div class="paid-stamp">
        [PAGADA]
    </div>
    <?php else: ?>
    <div class="pending-stamp">
        [PENDIENTE DE PAGO]
    </div>
    <?php endif; ?>
    
    <!-- Pie de página -->
    <div class="footer">
        <p><strong>GRACIAS POR SU PAGO</strong></p>
        <p>Este documento es válido como comprobante de pago</p>
        <p>Sistema de Gestión Municipal - <?= date('Y') ?></p>
    </div>
</body>
</html>

