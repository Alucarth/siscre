<style>
    .center-text { text-align:center; }
    .right-text { text-align:right; }
    .left-text { text-align:left; }
    .bold { font-weight:bold; }
    .border-bottom { border-bottom:1px solid #000; }
    .table-simple { border-collapse:collapse; width:100%; }
    .table-simple td { padding:4px; vertical-align:top; }
    .empresa-info { font-size:12px; line-height:1.2; }
</style>

<!-- INFORMACIÓN DE LA EMPRESA - SUPERIOR IZQUIERDA -->
<div style="position:absolute; top:20px; left:40px;" class="empresa-info">
    <div class="bold">CREDISURGIR</div>
    <div>NIT: 485672023</div>
</div>

<div style="text-align:center; margin-top:40px;">
    <h2>ESTADO DE RESULTADOS</h2>
    <h4>Del: <?=date($this->config->item('date_format'), $date_from)?> Al: <?=date($this->config->item('date_format'), $date_to)?></h4>
    <p>(Expresado en bolivianos)</p>
    <br/>
</div>

<table class="table-simple" border="1" cellpadding="4" cellspacing="0">
    <!-- Cabecera de columnas -->
    <tr>
        <th width="15%">Código</th>
        <th width="55%">Cuenta</th>
        <th width="30%">Importe</th>
    </tr>
    
    <!-- INGRESOS -->
    <tr>
        <td colspan="3" class="center-text bold" style="background-color:#e8f4f8;">INGRESOS</td>
    </tr>
    
    <?php 
    $total_ingresos = 0;
    $total_gastos = 0;
    ?>
    
    <?php foreach($accounts as $account): ?>
        <?php if($account->account_type == 'income' && $account->amount > 0): ?>
        <tr>
            <td><?=$account->account_map?></td>
            <td class="left-text"><?=$account->account_name?></td>
            <td class="right-text"><?=to_currency($account->amount)?></td>
        </tr>
        <?php $total_ingresos += $account->amount; ?>
        <?php endif; ?>
    <?php endforeach; ?>
    
    <tr>
        <td></td>
        <td class="bold right-text">TOTAL INGRESOS</td>
        <td class="right-text bold border-bottom"><?=to_currency($total_ingresos)?></td>
    </tr>
    
    <!-- ESPACIO -->
    <tr><td colspan="3" style="height:20px;"></td></tr>
    
    <!-- GASTOS -->
    <tr>
        <td colspan="3" class="center-text bold" style="background-color:#f8e8e8;">GASTOS</td>
    </tr>
    
    <?php foreach($accounts as $account): ?>
        <?php if($account->account_type == 'expenses' && $account->amount > 0): ?>
        <tr>
            <td><?=$account->account_map?></td>
            <td class="left-text"><?=$account->account_name?></td>
            <td class="right-text"><?=to_currency($account->amount)?></td>
        </tr>
        <?php $total_gastos += $account->amount; ?>
        <?php endif; ?>
    <?php endforeach; ?>
    
    <tr>
        <td></td>
        <td class="bold right-text">TOTAL GASTOS</td>
        <td class="right-text bold border-bottom"><?=to_currency($total_gastos)?></td>
    </tr>
    
    <!-- ESPACIO -->
    <tr><td colspan="3" style="height:20px;"></td></tr>
    
    <!-- RESULTADO -->
    <?php 
    $utilidad_neta = $total_ingresos - $total_gastos;
    $color_fondo = $utilidad_neta >= 0 ? '#e8f8e8' : '#f8e8e8';
    $color_texto = $utilidad_neta >= 0 ? 'green' : 'red';
    ?>
    
    <tr>
        <td></td>
        <td class="bold right-text">UTILIDAD (PÉRDIDA) NETA</td>
        <td class="right-text bold" style="background-color:<?=$color_fondo?>; color:<?=$color_texto?>;">
            <?=to_currency($utilidad_neta)?>
        </td>
    </tr>
</table>

<br/><br/><br/>

<!-- FIRMAS -->
<table width="100%" style="margin-top:50px;">
    <tr>
        <td width="50%" class="center-text">
            <div class="border-bottom" style="width:200px; margin:0 auto; padding-bottom:5px;">
                CONTADOR
            </div>
            <br/>
            <div>Nombre y Firma</div>
        </td>
        <td width="50%" class="center-text">
            <div class="border-bottom" style="width:200px; margin:0 auto; padding-bottom:5px;">
                GERENTE GENERAL
            </div>
            <br/>
            <div>Nombre y Firma</div>
        </td>
    </tr>
</table>