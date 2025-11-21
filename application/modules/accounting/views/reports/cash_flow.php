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
    @media print {
        .no-print {
            display: none;
        }
        .cash-flow-table {
            font-size: 10px;
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

<?php
if (!empty($accounts)) {
    // Agrupar por tipo de cuenta para los headers
    $current_group = '';
    $group_totals = [
        'ACTIVO' => 0,
        'PASIVO' => 0, 
        'PATRIMONIO' => 0
    ];
    ?>
    
    <table class="cash-flow-table">
        <thead>
            <tr>
                <th style="width: 10%;">CÓDIGO</th>
                <th style="width: 12%;">VARIACIÓN</th>
                <th style="width: 23%;">CUENTA</th>
                <th style="width: 10%;">TIPO</th>
                <th style="width: 15%;">CLASIFICACIÓN</th>
                <th style="width: 10%;" class="text-right">SALDO INICIAL</th>
                <th style="width: 10%;" class="text-right">SALDO FINAL</th>
                <th style="width: 10%;" class="text-right">DIFERENCIA</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $grand_total_diferencia = 0;
            
            foreach ($accounts as $account) {
                // Mostrar header de grupo si cambia
                if ($current_group != $account->tipo_cuenta) {
                    $current_group = $account->tipo_cuenta;
                    ?>
                    <tr class="group-header">
                        <td colspan="8" style="font-size: 12px; padding: 8px;">
                            <?php echo $current_group; ?>
                        </td>
                    </tr>
                    <?php
                }
                
                // Determinar clase CSS para variación
                $variacion_class = '';
                switch($account->tipo_variacion) {
                    case 'AUMENTO': $variacion_class = 'aumento'; break;
                    case 'DISMINUCIÓN': $variacion_class = 'disminucion'; break;
                    case 'SIN DIFERENCIA': $variacion_class = 'sin-diferencia'; break;
                }
                
                // Traducir clasificación
                $clasificacion_display = '';
                switch($account->clasificacion) {
                    case 'operating': $clasificacion_display = 'OPERACIÓN'; break;
                    case 'investing': $clasificacion_display = 'INVERSIÓN'; break;
                    case 'financing': $clasificacion_display = 'FINANCIACIÓN'; break;
                    default: $clasificacion_display = strtoupper($account->clasificacion);
                }
                ?>
                <tr>
                    <td><?php echo $account->code_number; ?></td>
                    <td class="text-center <?php echo $variacion_class; ?>">
                        <?php echo $account->tipo_variacion; ?>
                    </td>
                    <td><?php echo $account->account_name; ?></td>
                    <td class="text-center"><?php echo $account->tipo_cuenta; ?></td>
                    <td class="text-center"><?php echo $clasificacion_display; ?></td>
                    <td class="text-right"><?php echo to_currency($account->saldo_inicial); ?></td>
                    <td class="text-right"><?php echo to_currency($account->saldo_final); ?></td>
                    <td class="text-right <?php echo $variacion_class; ?>">
                        <?php echo to_currency($account->diferencia); ?>
                    </td>
                </tr>
                <?php
                
                $grand_total_diferencia += $account->diferencia;
                $group_totals[$account->tipo_cuenta] += $account->diferencia;
            }
            ?>
            
            <!-- Totales por grupo -->
            <?php foreach ($group_totals as $grupo => $total): ?>
                <?php if ($total != 0): ?>
                <tr style="background-color: #f0f0f0; font-weight: bold;">
                    <td colspan="5" class="text-right">Total <?php echo $grupo; ?>:</td>
                    <td colspan="2"></td>
                    <td class="text-right <?php echo $total >= 0 ? 'aumento' : 'disminucion'; ?>">
                        <?php echo to_currency($total); ?>
                    </td>
                </tr>
                <?php endif; ?>
            <?php endforeach; ?>
            
            <!-- Total General -->
            <tr style="background-color: #d0d0d0; font-weight: bold; font-size: 12px;">
                <td colspan="5" class="text-right">VARIACIÓN TOTAL DE EFECTIVO:</td>
                <td colspan="2"></td>
                <td class="text-right <?php echo $grand_total_diferencia >= 0 ? 'aumento' : 'disminucion'; ?>">
                    <?php echo to_currency($grand_total_diferencia); ?>
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