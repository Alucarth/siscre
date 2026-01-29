<style>
    .center-text { text-align:center; }
    .right-text { text-align:right; }
    .left-text { text-align:left; }
    .bold { font-weight:bold; }
    .border-bottom { border-bottom:1px solid #000; }
    .border-top { border-top:1px solid #000; }
    .table-simple { border-collapse:collapse; width:100%; }
    .table-simple td { padding:4px; vertical-align:top; }
    .empresa-info { font-size:12px; line-height:1.2; }
    .subtotal-row { background-color:#f5f5f5; }
</style>

<div style="position:absolute; top:20px; left:40px;" class="empresa-info">
    <div class="bold">CREDISURGIR S.R.L.</div>
    <div>NIT: 485672023</div>
</div>

<div style="text-align:center; margin-top:40px;">
    <h2>ESTADO DE RESULTADOS</h2>
    <h4>Del: <?=date($this->config->item('date_format'), $date_from)?> Al: <?=date($this->config->item('date_format'), $date_to)?></h4>
    <p>(Expresado en bolivianos)</p>
    <br/>
</div>

<table class="table-simple" border="1" cellpadding="4" cellspacing="0">
    <tr>
        <th width="15%">Código</th>
        <th width="55%">Cuenta</th>
        <th width="30%">Importe</th>
    </tr>
    
    <tr>
        <td colspan="3" class="center-text bold" style="background-color:#e8f4f8;">INGRESOS</td>
    </tr>
    
    <?php 
    $ingresos_encontrados = false;
    foreach($accounts as $account): 
        if(strtolower($account->account_type) == 'income' && $account->amount != 0): 
            $ingresos_encontrados = true;
            $codigo = (string)$account->code_number;
            $nivel = strlen($codigo);
            
            $padding = 0;
            if($nivel == 2) $padding = 10;
            if($nivel == 4) $padding = 20;
            if($nivel == 6) $padding = 30;
            if($nivel == 8) $padding = 40;

            $es_contenedor = ($nivel < 8);
    ?>
        <tr class="<?= $es_contenedor ? 'bold' : '' ?>">
            <td><?= $account->code_number ?></td>
            <td class="left-text" style="padding-left: <?= $padding ?>px;">
                <?= $account->account_name ?>
            </td>
            <td class="right-text"><?= money($account->amount) ?></td>
        </tr>
    <?php endif; endforeach; ?>

    <?php if(!$ingresos_encontrados): ?>
        <tr><td colspan="3" class="center-text">No hay ingresos registrados</td></tr>
    <?php endif; ?>
    
    <tr class="subtotal-row">
        <td></td>
        <td class="bold right-text">TOTAL INGRESOS</td>
        <td class="right-text bold border-top"><?=money($total_income)?></td>
    </tr>
    
    <tr><td colspan="3" style="height:20px;"></td></tr>
    
    <tr>
        <td colspan="3" class="center-text bold" style="background-color:#f8e8e8;">GASTOS</td>
    </tr>
    
    <?php 
    $gastos_encontrados = false;
    foreach($accounts as $account): 
        if(strtolower($account->account_type) == 'expenses' && $account->amount != 0): 
            $gastos_encontrados = true;
            $codigo = (string)$account->code_number;
            $nivel = strlen($codigo);
            
            $padding = 0;
            if($nivel == 2) $padding = 10;
            if($nivel == 4) $padding = 20;
            if($nivel == 6) $padding = 30;
            if($nivel == 8) $padding = 40;

            $es_contenedor = ($nivel < 8);
    ?>
        <tr class="<?= $es_contenedor ? 'bold' : '' ?>">
            <td><?= $account->code_number ?></td>
            <td class="left-text" style="padding-left: <?= $padding ?>px;">
                <?= $account->account_name ?>
            </td>
            <td class="right-text"><?= money($account->amount) ?></td>
        </tr>
    <?php endif; endforeach; ?>
    
    <?php if(!$gastos_encontrados): ?>
        <tr><td colspan="3" class="center-text">No hay gastos registrados</td></tr>
    <?php endif; ?>
    
    <tr class="subtotal-row">
        <td></td>
        <td class="bold right-text">TOTAL GASTOS</td>
        <td class="right-text bold border-top"><?=money($total_expenses)?></td>
    </tr>
</table>

<div style="margin-top:30px; padding:15px; border:1px solid #ccc; background-color:#f9f9f9;">
    <?php $color_fondo = $net_income >= 0 ? '#e8f8e8' : '#f8e8e8'; ?>
    <table width="100%" style="border-collapse:collapse;">
        <tr>
            <td width="70%" class="bold">Total Ingresos:</td>
            <td width="30%" class="right-text bold"><?=money($total_income)?></td>
        </tr>
        <tr>
            <td class="bold">Total Gastos:</td>
            <td class="right-text bold"><?=money($total_expenses)?></td>
        </tr>
        <tr style="background-color:<?=$color_fondo?>;">
            <td class="bold">Resultado Neto:</td>
            <td class="right-text bold"><?=money($net_income)?></td>
        </tr>
        <tr>
            <td class="bold">IUE (25%):</td>
            <td class="right-text bold"><?=money($iue)?></td>
        </tr>
        <tr style="background-color:#D5F5E3;">
            <td class="bold">UTILIDAD DEL EJERCICIO:</td>
            <td class="right-text bold"><?=money($utilidad)?></td>
        </tr>
    </table>
</div>

<table style="width: 100%; margin-top: 60px; border: none;">
    <tr>
        <td style="width: 50%; text-align: center; border: none;">
            <div style="margin-bottom: 15px;">________________________</div>
            <div style="font-size: 11px; font-weight: bold;">CONTADOR</div>
        </td>
        <td style="width: 50%; text-align: center; border: none;">
            <div style="margin-bottom: 15px;">________________________</div>
            <div style="font-size: 11px; font-weight: bold;">GERENTE GENERAL</div>
        </td>
    </tr>
</table>