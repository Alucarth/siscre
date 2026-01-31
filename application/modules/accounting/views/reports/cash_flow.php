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
        font-size: 11px;
    }
    .cash-flow-table th {
        background-color: #f5f5f5;
        padding: 10px 6px;
        text-align: left;
        border: 1px solid #ddd;
        font-weight: bold;
    }
    .cash-flow-table td {
        padding: 6px;
        border: 1px solid #ddd;
    }
    .text-right {
        text-align: right;
    }
    .text-center {
        text-align: center;
    }
    .group-header {
        background-color: #e8e8e8;
        font-weight: bold;
    }
    .aumento {
        color: #008000;
        font-weight: bold;
    }
    .disminucion {
        color: #ff0000;
        font-weight: bold;
    }
    .total-section {
        background-color: #f9f9f9;
        font-weight: bold;
    }
</style>

<div class="report-container">
    <div class="report-header">
        <div class="report-title">ESTADO DE FLUJO DE EFECTIVO</div>
        <div class="report-period">
            PERÍODO: <?php echo date($this->config->item('date_format'), $date_from); ?> 
            AL <?php echo date($this->config->item('date_format'), $date_to); ?>
        </div>
    </div>

<?php
if (isset($cash_flow_result) && !empty($cash_flow_result['summary'])) {
    $summary = $cash_flow_result['summary'];
    $totals = $cash_flow_result['totals'];
    ?>

    <table class="cash-flow-table">
        <thead>
            <tr>
                <th>CÓDIGO</th>
                <th>CUENTA</th>
                <th class="text-center">ACTIVIDAD</th>
                <th class="text-right">SALDO INICIAL</th>
                <th class="text-right">SALDO FINAL</th>
                <th class="text-center">TIPO</th>
                <th class="text-right">VARIACIÓN</th>
                <th class="text-right">IMPACTO EFECTIVO</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $current_activity = '';
            foreach ($summary as $row): 
                // FILTRO: Excluir cuentas que empiezan con 4, 5 o 6
                $primer_digito = substr($row['code_number'], 0, 1);
                if (in_array($primer_digito, ['4', '5', '6'])) continue;

                if ($current_activity != $row['activity_type']): 
                    $current_activity = $row['activity_type'];
                    $activity_label = '';
                    switch($current_activity) {
                        case 'operating': $activity_label = 'ACTIVIDADES DE OPERACIÓN'; break;
                        case 'investing': $activity_label = 'ACTIVIDADES DE INVERSIÓN'; break;
                        case 'financing': $activity_label = 'ACTIVIDADES DE FINANCIACIÓN'; break;
                    }
            ?>
                <tr class="group-header">
                    <td colspan="8"><?php echo $activity_label; ?></td>
                </tr>
            <?php endif; ?>

            <tr>
                <td><?php echo $row['code_number']; ?></td>
                <td><?php echo $row['account_name']; ?></td>
                <td class="text-center"><?php echo strtoupper($row['activity_type']); ?></td>
                <td class="text-right"><?php echo money($row['saldo_inicial']); ?></td>
                <td class="text-right"><?php echo money($row['saldo_final']); ?></td>
                <td class="text-center">
                    <?php echo $row['impacto_efectivo'] >= 0 ? 'ORIGEN' : 'APLICACIÓN'; ?>
                </td>
                <td class="text-right"><?php echo money($row['variacion']); ?></td>
                
                <td class="text-right <?php echo $row['impacto_efectivo'] >= 0 ? 'aumento' : 'disminucion'; ?>">
                    <?php echo money($row['impacto_efectivo']); ?>
                </td>
            </tr>
            <?php endforeach; ?>

            <tr class="total-section">
                <td colspan="7" class="text-right">TOTAL ACTIVIDADES DE OPERACIÓN:</td>
                <td class="text-right"><?php echo money($totals['operating']); ?></td>
            </tr>
            <tr class="total-section">
                <td colspan="7" class="text-right">TOTAL ACTIVIDADES DE INVERSIÓN:</td>
                <td class="text-right"><?php echo money($totals['investing']); ?></td>
            </tr>
            <tr class="total-section">
                <td colspan="7" class="text-right">TOTAL ACTIVIDADES DE FINANCIACIÓN:</td>
                <td class="text-right"><?php echo money($totals['financing']); ?></td>
            </tr>

            <tr class="group-header" style="font-size: 13px;">
                <td colspan="7" class="text-right">AUMENTO (DISMINUCIÓN) NETO DE EFECTIVO:</td>
                <td class="text-right" style="border-top: 2px solid #000;">
                    <?php echo money($totals['net_cash_flow']); ?>
                </td>
            </tr>
        </tbody>
    </table>

<?php
} else {
    ?>
    <div style="text-align: center; padding: 40px; color: #666;">
        <h3>No hay movimientos de efectivo en el período seleccionado</h3>
    </div>
    <?php
}
?>

<br><br><br>
<table style="width: 100%; margin-top: 80px; border: none;">
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
</div>