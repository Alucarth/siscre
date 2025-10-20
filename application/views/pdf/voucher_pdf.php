<!DOCTYPE html>
<html>
<head>
    <title>VOUCHER <?= !empty($voucher_info->voucher_number) ? $voucher_info->voucher_number : $voucher_info->id ?></title>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 15px;
        }
        
        @page {
            size: landscape;
            margin: 15mm;
        }
        
        /* ENCABEZADO SUPERIOR IZQUIERDA */
        .header-top-left {
            text-align: left;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .company-name {
            font-size: 14px;
        }
        
        .company-nit {
            font-size: 11px;
        }
        
        /* CONTENIDO CENTRAL */
        .content {
            text-align: center;
        }
        
        .document-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .voucher-info {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 15px;
            text-align: left;
            background-color: #f0f0f0;
        }
        
        .info-row {
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        
        /* TABLA */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 10px;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        
        th {
            background: #f0f0f0;
            font-weight: bold;
        }
        
        .amount {
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        
        .totals {
            background: #f0f0f0;
            font-weight: bold;
        }
        
        .status {
            text-align: center;
            padding: 10px;
            border: 1px solid #000;
            margin: 15px 0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header-top-left">
        <div class="company-name">CREDISURGIR S.R.L.</div>
        <div class="company-nit">NIT: 485672023</div>
    </div>
    
    <div class="content">
        <!-- TÍTULO DINÁMICO SEGÚN TIPO DE COMPROBANTE -->
        <div class="document-title">
            <?php
            $titles = [
                'ingreso' => 'COMPROBANTE DE INGRESO',
                'egreso' => 'COMPROBANTE DE EGRESO',
                'traspaso' => 'COMPROBANTE DE TRASPASO'
            ];
            
            $voucher_type = !empty($voucher_info->voucher_type) ? $voucher_info->voucher_type : 'contable';
            $document_title = isset($titles[$voucher_type]) ? $titles[$voucher_type] : 'COMPROBANTE CONTABLE';
            
            echo $document_title;
            ?>
        </div>
        
        <div class="voucher-info">
            <div class="info-row">
                <span class="info-label">N°:</span>
                <?= !empty($voucher_info->voucher_number) ? $voucher_info->voucher_number : $voucher_info->id ?>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha:</span>
                <?= !empty($voucher_info->voucher_date) ? date('d/m/Y', strtotime($voucher_info->voucher_date)) : 'No especificada' ?>
            </div>
            <div class="info-row">
                <span class="info-label">Glosa:</span>
                <?= !empty($voucher_info->description) ? $voucher_info->description : 'Sin descripción' ?>
            </div>
            <div class="info-row">
                <span class="info-label">Registrado por:</span>
                <?= $voucher_info->added_by_name ?>
            </div>
        </div>

        <?php if (!empty($transactions)): ?>
            <?php
            $total_debit = 0;
            $total_credit = 0;
            
            $debit_transactions = [];
            $credit_transactions = [];
            
            foreach ($transactions as $transaction) {
                if ($transaction->movement_type == 'debit') {
                    $debit_transactions[] = $transaction;
                    $total_debit += $transaction->amount;
                } else {
                    $credit_transactions[] = $transaction;
                    $total_credit += $transaction->amount;
                }
            }
            
            $debit_transactions = array_reverse($debit_transactions);
            ?>
            
            <table>
                <thead>
                    <tr>
                        <th width="12%">CÓDIGO</th>
                        <th width="25%">CUENTA</th>
                        <th width="38%">GLOSA</th>
                        <th width="12%">DEBE</th>
                        <th width="12%">HABER</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ($debit_transactions as $transaction): ?>
                        <tr>
                            <td><?= !empty($transaction->code_number) ? $transaction->code_number : 'N/A' ?></td>
                            <td><?= !empty($transaction->account_name) ? $transaction->account_name : 'Cuenta no especificada' ?></td>
                            <td><?= !empty($transaction->description) ? $transaction->description : '' ?></td>
                            <td class="amount"><?= to_currency($transaction->amount) ?></td>
                            <td class="amount">0,00</td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <?php 
                    foreach ($credit_transactions as $transaction): ?>
                        <tr>
                            <td><?= !empty($transaction->code_number) ? $transaction->code_number : 'N/A' ?></td>
                            <td><?= !empty($transaction->account_name) ? $transaction->account_name : 'Cuenta no especificada' ?></td>
                            <td><?= !empty($transaction->description) ? $transaction->description : '' ?></td>
                            <td class="amount">0,00</td>
                            <td class="amount"><?= to_currency($transaction->amount) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <tr class="totals">
                        <td colspan="3" style="text-align: right;">TOTAL</td>
                        <td class="amount"><?= to_currency($total_debit) ?></td>
                        <td class="amount"><?= to_currency($total_credit) ?></td>
                    </tr>
                </tbody>
            </table>
            
        <?php else: ?>
            <p>No hay transacciones registradas</p>
        <?php endif; ?>
    </div>

    <table style="width: 100%; margin-top: 60px; border: none;">
        <tr>
            <td style="width: 50%; text-align: center; border: none;">
                <div style="font-family: 'Courier New', monospace; font-size: 14px; letter-spacing: 0px; margin: 0 auto 15px auto;">________________________</div>
                <div style="font-size: 11px; font-weight: bold;">CONTADOR</div>
            </td>
            <td style="width: 50%; text-align: center; border: none;">
                <div style="font-family: 'Courier New', monospace; font-size: 14px; letter-spacing: 0px; margin: 0 auto 15px auto;">________________________</div>
                <div style="font-size: 11px; font-weight: bold;">GERENTE GENERAL</div>
            </td>
        </tr>
    </table>
</body>
</html>