<style>
    .report-container { padding: 20px; background: white; }
    .report-header { text-align: center; margin-bottom: 30px; }
    .report-title { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
    .report-period { font-size: 14px; color: #666; }
    .cash-flow-table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 11px; }
    .cash-flow-table th { background-color: #f5f5f5; padding: 10px 6px; text-align: left; border: 1px solid #ddd; font-weight: bold; }
    .cash-flow-table td { padding: 6px; border: 1px solid #ddd; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .total-section { background-color: #f9f9f9; font-weight: bold; }
    .empresa-info { font-size:12px; line-height:1.2; }
</style>

<div style="position:absolute; top:20px; left:40px;" class="empresa-info">
    <div class="bold">CREDISURGIR S.R.L.</div>
    <div>NIT: 485672023</div>
</div>
<div class="report-container">
    <div class="report-header">
        <div class="report-title">ESTADO DE FLUJO DE EFECTIVO</div>
        <div class="report-period">
            PERÍODO: <?php echo date($this->config->item('date_format'), $date_from); ?> 
            AL <?php echo date($this->config->item('date_format'), $date_to); ?>
        </div>
    </div>

<?php
if (isset($cash_flow_result) && !empty($cash_flow_result['accounts'])) {
    $total_operating = 0; $total_investing = 0; $total_financing = 0;
    ?>

    <table class="cash-flow-table">
        <thead>
            <tr>
                <th>CÓDIGO</th>
                <th>CUENTA</th>
                <th class="text-right">S. INICIAL</th>
                <th class="text-right">S. FINAL</th>
                <th class="text-right">VARIACIÓN</th>
                <th class="text-center">TIPO ACTIVIDAD</th>
                <th class="text-right">EFECTO EFECTIVO</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cash_flow_result['accounts'] as $row): ?>
                <?php if ($row->es_primera_cuenta): 
                    // Sumamos a los totales internos según el tipo
                    if($row->activity_type == 'operating') $total_operating += $row->monto_variacion;
                    if($row->activity_type == 'investing') $total_investing += $row->monto_variacion;
                    if($row->activity_type == 'financing') $total_financing += $row->monto_variacion;

                    // Traducción de etiquetas
                    $tipo_label = '';
                    switch($row->activity_type) {
                        case 'operating': $tipo_label = 'OPERACIÓN'; break;
                        case 'investing': $tipo_label = 'INVERSIÓN'; break;
                        case 'financing': $tipo_label = 'FINANCIACIÓN'; break;
                        default: $tipo_label = 'N/A';
                    }
                ?>
                <tr>
                    <td><?php echo $row->code_number; ?></td>
                    <td><?php echo $row->account_name; ?></td>
                    <td class="text-right"><?php echo money($row->saldo_inicial); ?></td>
                    <td class="text-right"><?php echo money($row->saldo_final); ?></td>
                    <td class="text-right"><?php echo money($row->variacion); ?></td>
                    <td class="text-center"><strong><?php echo $tipo_label; ?></strong></td>
                    <td class="text-right <?php echo $row->monto_variacion >= 0 ? 'aumento' : 'disminucion'; ?>">
                        <?php echo money($row->monto_variacion); ?>
                    </td>
                </tr>
                <?php endif; ?>
            <?php endforeach; ?>

            <tr class="total-section">
                <td colspan="6" class="text-right">TOTAL ACTIVIDADES DE OPERACIÓN:</td>
                <td class="text-right"><?php echo money($total_operating); ?></td>
            </tr>
            <tr class="total-section">
                <td colspan="6" class="text-right">TOTAL ACTIVIDADES DE INVERSIÓN:</td>
                <td class="text-right"><?php echo money($total_investing); ?></td>
            </tr>
            <tr class="total-section">
                <td colspan="6" class="text-right">TOTAL ACTIVIDADES DE FINANCIACIÓN:</td>
                <td class="text-right"><?php echo money($total_financing); ?></td>
            </tr>

            <tr class="total-section" style="font-size: 13px; background-color: #eee;">
                <td colspan="6" class="text-right">EFECTIVO INICIAL:</td>
                <td class="text-right"><?php echo money(0); ?></td>
            </tr>
            <tr class="total-section" style="font-size: 13px; background-color: #eee;">
                <td colspan="6" class="text-right">EFECTIVO FINAL:</td>
                <td class="text-right"><?php echo money($total_operating - $total_investing + $total_financing); ?></td>
            </tr>
        </tbody>
    </table>

<?php } ?>

<table style="width: 100%; margin-top: 80px; border: none;">
    <tr>
        <td style="width: 50%; text-align: center; border: none;">
            <div>________________________</div>
            <div style="font-size: 11px; font-weight: bold;">CONTADOR</div>
        </td>
        <td style="width: 50%; text-align: center; border: none;">
            <div>________________________</div>
            <div style="font-size: 11px; font-weight: bold;">GERENTE GENERAL</div>
        </td>
    </tr>
</table>
</div>