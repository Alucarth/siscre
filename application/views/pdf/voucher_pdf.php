<!DOCTYPE html>
<html>
<head>
    <title>VOUCHER <?= !empty($voucher_info->voucher_number) ? $voucher_info->voucher_number : $voucher_info->id ?></title>
    <meta charset="utf-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
            background: #ffffff;
            color: #333;
            padding: 15px;
        }
        
        /* Botones de control (solo para navegador) */
        .no-print {
            margin-bottom: 20px;
            text-align: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .no-print button {
            padding: 8px 16px;
            margin: 0 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
        }
        
        .btn-print {
            background: #28a745;
            color: white;
        }
        
        .btn-close {
            background: #dc3545;
            color: white;
        }
        
        /* Encabezado de la empresa */
        .company-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px double #2c3e50;
        }
        
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .company-info {
            font-size: 11px;
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .document-title {
            font-size: 16px;
            color: #e74c3c;
            font-weight: bold;
            margin: 10px 0;
            text-transform: uppercase;
        }
        
        /* Contenedor principal del voucher */
        .voucher-container {
            border: 2px solid #85ce37;
            border-radius: 8px;
            margin-bottom: 20px;
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        /* Encabezado del voucher */
        .voucher-header {
            background:#85ce37;
            color: white;
            padding: 20px;
            border-radius: 6px 6px 0 0;
        }
        
        .voucher-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .voucher-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            font-size: 11px;
        }
        
        .meta-item {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
        }
        
        .meta-label {
            font-weight: bold;
            min-width: 100px;
        }
        
        .meta-value {
            text-align: right;
            flex-grow: 1;
            margin-left: 10px;
        }
        
        /* Tabla de transacciones */
        .transactions-section {
            padding: 20px;
        }
        
        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10px;
            background: white;
        }
        
        .transactions-table th {
            background: #85ce37;
            color: white;
            padding: 10px 6px;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
            border: 1px solid #bdc3c7;
        }
        
        .transactions-table td {
            padding: 8px 6px;
            border: 1px solid #ecf0f1;
            vertical-align: top;
        }
        
        .account-cell {
            width: 25%;
        }
        
        .description-cell {
            width: 35%;
        }
        
        .type-cell {
            width: 12%;
            text-align: center;
        }
        
        .amount-cell {
            width: 14%;
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        
        /* Estilos para tipos de transacción */
        .transaction-debit {
            background: #ffeaa7;
        }
        
        .transaction-credit {
            background: #d1f7ff;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-debit {
            background: #e74c3c;
            color: white;
        }
        
        .badge-credit {
            background: #27ae60;
            color: white;
        }
        
        /* Totales */
        .totals-row {
            background: #85ce37 !important;
            color: white;
            font-weight: bold;
        }
        
        .totals-row td {
            border: 1px solid #7f8c8d !important;
            padding: 12px 6px !important;
        }
        
        /* Estados de diferencia */
        .difference-balanced {
            color: #27ae60;
            font-weight: bold;
        }
        
        .difference-unbalanced {
            color: #e74c3c;
            font-weight: bold;
        }
        
        /* Sin transacciones */
        .no-transactions {
            text-align: center;
            padding: 40px 20px;
            color: #7f8c8d;
            font-style: italic;
            background: #f8f9fa;
            border-radius: 5px;
            margin: 20px 0;
        }
        
        /* Pie de página */
        .voucher-footer {
            padding: 15px 20px;
            background: #ecf0f1;
            border-radius: 0 0 6px 6px;
            border-top: 1px solid #bdc3c7;
        }
        
        .footer-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            font-size: 10px;
        }
        
        .footer-item {
            display: flex;
            justify-content: space-between;
        }
        
        .footer-label {
            font-weight: bold;
            color: #000000ff;
        }
        
        .signature-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px dashed #bdc3c7;
            text-align: center;
        }
        
        .signature-line {
            margin: 40px 0 10px 0;
            border-bottom: 1px solid #7f8c8d;
            width: 200px;
            display: inline-block;
        }
        
        .signature-label {
            font-size: 10px;
            color: #7f8c8d;
            text-transform: uppercase;
        }
        
        /* Estilos para impresión */
        @media print {
            .no-print { display: none; }
            body { 
                padding: 10px; 
                margin: 0;
            }
            .voucher-container { 
                border: 1px solid #000;
                box-shadow: none;
            }
        }
        
        /* Utilidades */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .mb-10 { margin-bottom: 10px; }
        .mt-20 { margin-top: 20px; }
    </style>
</head>
<body>
    <!-- Botones de control (no se imprimen) -->
    <div class="no-print">
        <button onclick="window.print()" class="btn-print">
             Imprimir Voucher
        </button>
        <button onclick="window.close()" class="btn-close">
             Cerrar Ventana
        </button>
    </div>
    
    <!-- CALCULAR TOTALES -->
    <?php
    // Calcular totales desde las transacciones
    $calculated_debit = 0;
    $calculated_credit = 0;
    
    if (!empty($transactions)) {
        foreach ($transactions as $transaction) {
            $is_debit = ($transaction->movement_type == 'debit');
            $debit_amount = $is_debit ? $transaction->amount : 0;
            $credit_amount = !$is_debit ? $transaction->amount : 0;
            
            $calculated_debit += $debit_amount;
            $calculated_credit += $credit_amount;
        }
    }
    ?>
    <!-- Encabezado de la empresa -->   
    <div class="company-header">
        <div class="company-name"><?= $this->config->item('company') ?></div>
        <div class="company-info">Sistema Contable Integral</div>
        <div class="document-title">Comprobante Contable - Voucher</div>
        <div class="company-info">Generado el: <?= date('d/m/Y \a \l\a\s H:i:s') ?></div>
    </div>

    <!-- Contenedor principal del voucher -->
    <div class="voucher-container">
        
        <!-- Encabezado del voucher -->
        <div class="voucher-header">
            <div class="voucher-title">
                VOUCHER: <?= !empty($voucher_info->voucher_number) ? $voucher_info->voucher_number : 'N° ' . $voucher_info->id ?>
            </div>
            
            <div class="voucher-meta">
                <div class="meta-item">
                    <span class="meta-label">Fecha del Voucher:</span>
                    <span class="meta-value">
                        <?= !empty($voucher_info->voucher_date) ? date('d/m/Y', strtotime($voucher_info->voucher_date)) : 'No especificada' ?>
                    </span>
                </div>
                
                <div class="meta-item">
                    <span class="meta-label">Total Debe:</span>
                    <span class="meta-value"><?= to_currency($calculated_debit) ?></span>
                </div>
                
                <div class="meta-item">
                    <span class="meta-label">Descripción:</span>
                    <span class="meta-value"><?= !empty($voucher_info->description) ? $voucher_info->description : 'Sin descripción' ?></span>
                </div>
                
                <div class="meta-item">
                    <span class="meta-label">Total Haber:</span>
                    <span class="meta-value"><?= to_currency($calculated_credit) ?></span>
                </div>
                
                <div class="meta-item">
                    <span class="meta-label">Registrado por:</span>
                    <span class="meta-value"><?= $voucher_info->added_by_name ?></span>
                </div>
                
                <div class="meta-item">
                    <span class="meta-label">Diferencia:</span>
                    <span class="meta-value <?= ($calculated_debit == $calculated_credit) ? 'difference-balanced' : 'difference-unbalanced' ?>">
                        <?= to_currency($calculated_debit - $calculated_credit) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Sección de transacciones -->
        <div class="transactions-section">
            <?php 
            $calculated_debit = 0;
            $calculated_credit = 0;
            ?>
            
            <?php if (!empty($transactions)): ?>
                <table class="transactions-table">
                    <thead>
                        <tr>
                            <th class="account-cell">Cuenta Contable</th>
                            <th class="description-cell">Descripción</th>
                            <th class="type-cell">Tipo</th>
                            <th class="amount-cell">Debe</th>
                            <th class="amount-cell">Haber</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach ($transactions as $index => $transaction): 
                            $is_debit = ($transaction->movement_type == 'debit');
                            $debit_amount = $is_debit ? $transaction->amount : 0;
                            $credit_amount = !$is_debit ? $transaction->amount : 0;
                            
                            $calculated_debit += $debit_amount;
                            $calculated_credit += $credit_amount;
                            
                            $row_class = $is_debit ? 'transaction-debit' : 'transaction-credit';
                        ?>
                            <tr class="<?= $row_class ?>">
                                <td class="account-cell">
                                    <div class="text-bold"><?= !empty($transaction->code_number) ? $transaction->code_number : 'N/A' ?></div>
                                    <div style="font-size: 8px; color: #7f8c8d; margin-top: 2px;">
                                        <?= !empty($transaction->account_name) ? $transaction->account_name : 'Cuenta no especificada' ?>
                                    </div>
                                    <?php if (!empty($transaction->account_type)): ?>
                                        <div style="font-size: 7px; color: #95a5a6; font-style: italic;">
                                            <?= $transaction->account_type ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="description-cell">
                                    <?= !empty($transaction->description) ? $transaction->description : 'Transacción sin descripción' ?>
                                </td>
                                <td class="type-cell">
                                    <span class="badge <?= $is_debit ? 'badge-debit' : 'badge-credit' ?>">
                                        <?= $is_debit ? 'Debe' : 'Haber' ?>
                                    </span>
                                </td>
                                <td class="amount-cell text-bold">
                                    <?= $is_debit ? to_currency($transaction->amount) : '---' ?>
                                </td>
                                <td class="amount-cell text-bold">
                                    <?= !$is_debit ? to_currency($transaction->amount) : '---' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <!-- Fila de totales -->
                        <tr class="totals-row">
                            <td colspan="3" class="text-right text-bold">TOTALES GENERALES:</td>
                            <td class="amount-cell"><?= to_currency($calculated_debit) ?></td>
                            <td class="amount-cell"><?= to_currency($calculated_credit) ?></td>
                        </tr>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-transactions">
                    <h3>No hay transacciones registradas</h3>
                    <p>Este voucher no contiene movimientos contables.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pie del voucher -->
        <div class="voucher-footer">
            <div class="footer-info">
                <div class="footer-item">
                    <span class="footer-label">Estado:</span>
                    <span class="<?= ($calculated_debit == $calculated_credit) ? 'difference-balanced' : 'difference-unbalanced' ?>">
                        <?= ($calculated_debit == $calculated_credit) ? 'BALANCEADO' : 'DESBALANCEADO' ?>
                    </span>
                </div>
                <div class="footer-item">
                    <span class="footer-label">Código Voucher:</span>
                    <span><?= $voucher_info->id ?></span>
                </div>  
            </div>
            
            <div class="signature-section">
                <div class="signature-line"></div>
                <div class="signature-label">Firma Autorizada</div>
            </div>
        </div>
    </div>

    <script>
        // Auto-impresión opcional (descomenta si lo necesitas)
        // window.onload = function() { 
        //     setTimeout(function() {
        //         window.print();
        //     }, 1000);
        // }
    </script>
</body>
</html>