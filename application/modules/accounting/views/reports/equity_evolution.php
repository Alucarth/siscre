<style>
.table-patrimonio {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}
.table-patrimonio th, .table-patrimonio td {
    border: 1px solid #000;
    padding: 6px 8px;
    text-align: right;
}
.table-patrimonio th {
    background-color: #f0f0f0;
    font-weight: bold;
    text-align: center;
}
.table-patrimonio .code-number {
    text-align: left;
    font-weight: bold;
}
.table-patrimonio .account-name {
    text-align: left;
    font-weight: bold;
}
.table-patrimonio .subtotal {
    font-weight: bold;
    background-color: #e8e8e8;
}
.table-patrimonio .total {
    font-weight: bold;
    background-color: #d0d0d0;
    border-top: 2px solid #000;
}
.text-left { text-align: left; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.bold { font-weight: bold; }
</style>

<div style="position:absolute; top:20px; left:40px;" class="empresa-info">
    <div class="bold">CREDISURGIR S.R.L.</div>
    <div>NIT: 485672023</div>
</div>

<div style="text-align:center; margin-top:40px;">
    <h2>EVOLUCIÓN DEL PATRIMONIO</h2>
    <h4>Del: <?=date($this->config->item('date_format'), $date_from)?> Al: <?=date($this->config->item('date_format'), $date_to)?></h4>
    <p>(Expresado en bolivianos)</p>
    <br/>
</div>

<?php if (!empty($accounts['cuentas_patrimonio']) && !empty($accounts['periodos'])): ?>
<table class="table-patrimonio">
    <thead>
        <tr>
            <th style="width: 25%; text-align: left;">CÓDIGO</th>
            <th style="width: 25%; text-align: left;">CUENTA</th>
            <?php foreach ($accounts['periodos'] as $periodo): ?>
                <th style="width: 15%;"><?=DateTime::createFromFormat('Y-m', $periodo)->format('M Y')?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php 
        $saldos_finales = [];
        $total_patrimonio = [];
        
        // Inicializar arrays
        foreach ($accounts['periodos'] as $periodo) {
            $total_patrimonio[$periodo] = 0;
        }
        ?>
        
        <!-- Cuentas individuales -->
        <?php foreach ($accounts['cuentas_patrimonio'] as $cuenta): ?>
            <tr>
                <td class="text-left code-number">
                    <?=$cuenta->code_number?>
                </td>
                <td class="text-left account-name">
                    <?=$cuenta->account_name?>
                </td>
                <?php 
                $saldo_acumulado = 0;
                foreach ($accounts['periodos'] as $periodo): 
                    $saldo_periodo = isset($accounts['saldos_por_periodo'][$periodo][$cuenta->id]) ? 
                        $accounts['saldos_por_periodo'][$periodo][$cuenta->id]->saldo_periodo : 0;
                    
                    $saldo_acumulado += $saldo_periodo;
                    $total_patrimonio[$periodo] += $saldo_acumulado;
                ?>
                    <td class="text-right">
                        <?=to_currency($saldo_acumulado)?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        
        <!-- Línea separadora -->
        <tr>
            <td colspan="<?=count($accounts['periodos']) + 1?>" style="border-top: 2px solid #000;"></td>
        </tr>
        
        <!-- Total Patrimonio Neto -->
        <tr class="total">
            <td class="text-left bold" colspan="2">TOTAL PATRIMONIO NETO</td>
            <?php foreach ($accounts['periodos'] as $periodo): ?>
                <td class="text-right bold">
                    <?=to_currency($accounts['patrimonio_neto_por_periodo'][$periodo])?>
                </td>
            <?php endforeach; ?>
        </tr>
        
        <!-- Crecimiento Porcentual -->
        <tr class="subtotal">
            <td class="text-left" colspan="2">Crecimiento %</td>
            <td class="text-center">-</td>
            <?php for ($i = 1; $i < count($accounts['periodos']); $i++): 
                $periodo = $accounts['periodos'][$i];
                $crecimiento = isset($accounts['crecimiento_porcentual'][$periodo]) ? 
                    $accounts['crecimiento_porcentual'][$periodo] : 0;
            ?>
                <td class="text-right">
                    <?=number_format($crecimiento, 2)?>%
                </td>
            <?php endfor; ?>
        </tr>
    </tbody>
</table>
<?php else: ?>
<div style="text-align: center; margin-top: 50px;">
    <p>No hay datos de patrimonio para el período seleccionado.</p>
</div>
<?php endif; ?>

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