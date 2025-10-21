<style>
    .tbl-ledger th {
        text-align: center;
    }
    .tbl-ledger-head td {
        padding:8px;        
    }
    .tbl-ledger td {
        width:20%;        
    }

    .tbl-ledger td:nth-child(1), 
    .tbl-ledger td:nth-child(3), 
    .tbl-ledger td:nth-child(4), 
    .tbl-ledger td:nth-child(5) 
    {
        text-align: center;
    }

    .tbl-ledger-head td:nth-child(1), 
    .tbl-ledger-head td:nth-child(3) 
    {
        width:150px;
        white-space: nowrap;
    }
    .tbl-ledger-head td:nth-child(2)
    {
        width:450px;
    }
    .tbl-ledger-head td:nth-child(2), 
    .tbl-ledger-head td:nth-child(4) 
    {
        text-align: left;
        white-space: nowrap;
    }
    
    .voucher-section {
        margin-bottom: 30px;
        border: 2px solid #85ce37;
        border-radius: 5px;
        padding: 10px;
        background-color: #f8f9fa;
    }
    
    .voucher-header {
        background-color: #85ce37;
        color: white;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 15px;
    }
    
    .voucher-totals {
        background-color: #e9ecef;
        font-weight: bold;
    }
    
    .no-transactions {
        text-align: center;
        padding: 20px;
        font-style: italic;
        color: #6c757d;
        background-color: #f8f9fa;
        border-radius: 5px;
    }
    
    .transaction-row:hover {
        background-color: #f1f3f4;
    }
</style>

<div style="max-height: 450px; overflow: auto">

    <!-- SECCIÓN DE VOUCHERS CONTABLES -->
    <?php 
    $has_accounting_data = false;
    if (!empty($accounting_transactions)) {
        if (is_array($accounting_transactions) && count($accounting_transactions) > 0) {
            $has_accounting_data = true;
        } elseif (is_object($accounting_transactions)) {
            $temp_array = (array)$accounting_transactions;
            if (count($temp_array) > 0) {
                $has_accounting_data = true;
            }
        }
    }
    ?>
    
    <?php if ($has_accounting_data): ?>
        <h3 style="color: #3b4753; margin-bottom: 20px;">Vouchers Contables</h3>
        
        <?php 
        $vouchers_to_display = is_object($accounting_transactions) ? (array)$accounting_transactions : $accounting_transactions;
        ?>
        
        <?php foreach ($vouchers_to_display as $voucher_id => $voucher_data): ?>
            <?php 
            if (is_object($voucher_data) && isset($voucher_data->voucher_info)) {
                $voucher_info = $voucher_data->voucher_info;
                $transactions = isset($voucher_data->transactions) ? $voucher_data->transactions : [];
            } else {
                continue;
            }
            ?>
        
        <div class="voucher-section">
            <div class="voucher-header">
                <h4 style="margin: 0; color: white;">
                    Voucher: <?= $voucher_id ?>
                </h4>
                <div style="display: flex; gap: 10px;">
                    <a href="<?= site_url('general_ledger/print_voucher/' . $voucher_id) ?>" 
                       class="btn btn-sm" 
                       style="background-color: #3a4652; color: white;"
                       target="_blank"
                       title="Imprimir Voucher">
                       <i class="fa fa-print"></i> Imprimir
                    </a>
                </div>
                <table style="width: 100%; color: white; margin-top: 10px;">
                    <tr>
                        <td style="width: 120px;"><strong>Fecha:</strong></td>
                        <td style="width: 200px;"><?= date($this->config->item('date_format'), strtotime($voucher_info->voucher_date)) ?></td>
                        <td style="width: 120px;"><strong>Total Debe:</strong></td>
                        <td><?= to_currency($voucher_info->total_debit) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Descripción:</strong></td>
                        <td colspan="3"><?= !empty($voucher_info->voucher_description) ? $voucher_info->voucher_description : 'Sin descripción' ?></td>
                    </tr>
                    <tr>
                        <td><strong>Total Haber:</strong></td>
                        <td><?= to_currency($voucher_info->total_credit) ?></td>
                        <td><strong>Diferencia:</strong></td>
                        <td>
                            <?php 
                            $diferencia = $voucher_info->total_debit - $voucher_info->total_credit;
                            $color = ($diferencia == 0) ? '#28a745' : '#dc3545';
                            ?>
                            <span style="color: <?= $color ?>;"><?= to_currency($diferencia) ?></span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- TABLA DE TRANSACCIONES DEL VOUCHER -->
            <?php if (!empty($transactions)): ?>
                <?php 
                $debitos = [];
                $creditos = [];
                
                foreach ($transactions as $transaction) {
                    if ($transaction->debit > 0) {
                        $debitos[] = $transaction;
                    } else {
                        $creditos[] = $transaction;
                    }
                }
                ?>
                <table class="table table-bordered tbl-ledger">
                    <thead>
                        <tr>
                            <th>Cuenta</th>
                            <th>Descripción</th>
                            <th>Tipo</th>
                            <th>Debe</th>
                            <th>Haber</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($debitos as $transaction): ?>
                            <tr class="transaction-row">
                                <td>
                                    <strong><?= $transaction->account_number ?></strong><br>
                                    <small><?= $transaction->account_name ?></small><br>
                                    <small style="color: #6c757d;"><?= $transaction->account_type ?></small>
                                </td>
                                <td><?= !empty($transaction->explanation) ? $transaction->explanation : '---' ?></td>
                                <td>
                                    <span class="badge badge-danger">DEBE</span>
                                </td>
                                <td style="text-align: right; font-weight: bold;">
                                    <?= to_currency($transaction->debit) ?>
                                </td>
                                <td style="text-align: right;">
                                    ---
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php foreach ($creditos as $transaction): ?>
                            <tr class="transaction-row">
                                <td>
                                    <strong><?= $transaction->account_number ?></strong><br>
                                    <small><?= $transaction->account_name ?></small><br>
                                    <small style="color: #6c757d;"><?= $transaction->account_type ?></small>
                                </td>
                                <td><?= !empty($transaction->explanation) ? $transaction->explanation : '---' ?></td>
                                <td>
                                    <span class="badge badge-success">HABER</span>
                                </td>
                                <td style="text-align: right;">
                                    ---
                                </td>
                                <td style="text-align: right; font-weight: bold;">
                                    <?= to_currency($transaction->credit) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <!-- FILA DE TOTALES -->
                        <tr class="voucher-totals">
                            <td colspan="3" style="text-align: right;"><strong>TOTALES DEL VOUCHER:</strong></td>
                            <td style="text-align: right;"><strong><?= to_currency($voucher_info->total_debit) ?></strong></td>
                            <td style="text-align: right;"><strong><?= to_currency($voucher_info->total_credit) ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-transactions">
                    Este voucher no tiene transacciones asociadas.
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="no-transactions">
        No se encontraron vouchers contables en el período seleccionado.
    </div>
<?php endif; ?>

    <!-- SECCIÓN DE TRANSACCIONES DE CUENTAS -->
    <?php if (!empty($account_transactions) && count($account_transactions) > 0): ?>
        <h3 style="color: #28a745; margin-bottom: 20px; margin-top: 40px;">Transacciones de Cuentas</h3>
        <?php foreach ($account_transactions as $row): ?>
            <div class="voucher-section">
                <table style="width:100%" class="tbl-ledger-head">
                    <tr>
                        <td><b>Nombre de Cuenta:</b></td>
                        <td><?= $row->account_name; ?></td>
                        <td><b>Numero de Cuenta:</b></td>
                        <td><?= $row->account_number; ?></td>
                    </tr>
                </table>

                <table class="table table-bordered tbl-ledger">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Descripción</th>
                            <th>Debe</th>
                            <th>Haber</th>
                            <th>Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= $row->date; ?></td>
                            <td><?= $row->explanation; ?></td>
                            <td><?= to_currency($row->debit); ?></td>
                            <td><?= to_currency($row->credit); ?></td>
                            <td><?= to_currency($row->balance); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>        
    <?php else: ?>
    <?php endif; ?>
</div>