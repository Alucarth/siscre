<?php
?>

<style>
    .report-container {
        padding: 20px;
        background: white;
    }
    .report-header {
        text-align: center;
        margin-bottom: 30px;
    }
    .report-title {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 10px;
    }
    .report-period {
        font-size: 14px;
        color: #666;
    }
    .cash-flow-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .cash-flow-table th {
        background-color: #f5f5f5;
        padding: 12px;
        text-align: left;
        border: 1px solid #ddd;
        font-weight: bold;
    }
    .cash-flow-table td {
        padding: 10px 12px;
        border: 1px solid #ddd;
    }
    .cash-flow-table tr:hover {
        background-color: #f9f9f9;
    }
    .text-right {
        text-align: right;
    }
    .account-type-header {
        background-color: #e8e8e8;
        font-weight: bold;
        text-transform: uppercase;
    }
    @media print {
        .no-print {
            display: none;
        }
    }
</style>

<div style="position:absolute; top:20px; left:40px;" class="empresa-info">
    <div class="bold">CREDISURGIR S.R.L.</div>
    <div>NIT: 485672023</div>
</div>

<div style="text-align:center; margin-top:40px;">
    <h2>FLUJO DE EFECTIVO</h2>
    <h4>Del: <?=date($this->config->item('date_format'), $date_from)?> Al: <?=date($this->config->item('date_format'), $date_to)?></h4>
    <p>(Expresado en bolivianos)</p>
    <br/>
</div>

    <table class="cash-flow-table">
        <thead>
            <tr>
                <th style="width: 10%;">Código</th>
                <th style="width: 10%;">Variación</th>
                <th style="width: 10%;">Cuenta</th>
                <th style="width: 10%;">Tipo</th>
                <th style="width: 10%;">Clasificación</th>
                <th style="width: 10%;">Saldo Inicial</th>
                <th style="width: 10%;">Saldo Final</th>
                <th style="width: 10%;">Diferencia</th>
                <th style="width: 10%;" class="text-right">Monto</th>
                <th style="width: 10%;">Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (!empty($accounts)) {
                $current_type = '';
                $type_total = 0;
                $grand_total = 0;
                
                foreach ($accounts as $account) {
                    // Si cambia el tipo de cuenta, mostrar subtotal
                    if ($current_type != '' && $current_type != $account->account_type) {
                        ?>
                        <tr style="background-color: #f0f0f0; font-weight: bold;">
                            <td colspan="3" class="text-right">Subtotal <?php echo ucfirst($current_type); ?>:</td>
                            <td class="text-right"><?php echo to_currency($type_total); ?></td>
                            <td></td>
                        </tr>
                        <?php
                        $type_total = 0;
                    }
                    
                    // Mostrar header del tipo de cuenta
                    if ($current_type != $account->account_type) {
                        $current_type = $account->account_type;
                        ?>
                        <tr class="account-type-header">
                            <td colspan="5"><?php echo ucfirst($account->account_type); ?></td>
                        </tr>
                        <?php
                    }
                    
                    // Calcular el monto según el tipo de movimiento
                    $amount_display = $account->amount;
                    if ($account->movement_type == 'credit') {
                        $amount_display = -$account->amount;
                    }
                    
                    $type_total += $amount_display;
                    $grand_total += $amount_display;
                    ?>
                    <tr>
                        <td><?php echo $account->code_number; ?></td>
                        <td></td>
                        <td><?php echo $account->account_name; ?></td>
                        <td><?php echo ucfirst($account->account_type); ?></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="text-right"><?php echo to_currency($amount_display); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($account->added_date)); ?></td>
                    </tr>
                    <?php
                }
                
                // Mostrar último subtotal
                if ($current_type != '') {
                    ?>
                    <tr style="background-color: #f0f0f0; font-weight: bold;">
                        <td colspan="3" class="text-right">Subtotal <?php echo ucfirst($current_type); ?>:</td>
                        <td class="text-right"><?php echo to_currency($type_total); ?></td>
                        <td></td>
                    </tr>
                    <?php
                }
                ?>
                
                <!-- Total General -->
                <tr style="background-color: #d0d0d0; font-weight: bold; font-size: 16px;">
                    <td colspan="3" class="text-right">TOTAL GENERAL:</td>
                    <td class="text-right"><?php echo to_currency($grand_total); ?></td>
                    <td></td>
                </tr>
                <?php
            } else {
                ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">
                        No hay transacciones en el período seleccionado
                    </td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
</div>

<!-- FIRMAS -->
<br><br>
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