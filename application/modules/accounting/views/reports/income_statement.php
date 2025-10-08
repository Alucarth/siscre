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
    // Filtrar y mostrar ingresos
    $ingresos_encontrados = false;
    foreach($accounts as $account): 
        if(strtolower($account->account_type) == 'income' && $account->amount != 0): 
            $ingresos_encontrados = true;
    ?>
        <tr>
            <td><?=isset($account->code_number) ? $account->code_number : (isset($account->account_map) ? $account->account_map : '')?></td>
            <td class="left-text"><?=$account->account_name?></td>
            <td class="right-text"><?=to_currency($account->amount)?></td>
        </tr>
    <?php 
        endif;
    endforeach; 
    
    if(!$ingresos_encontrados): 
    ?>
        <tr>
            <td colspan="3" class="center-text">No hay ingresos registrados en el período</td>
        </tr>
    <?php endif; ?>
    
    <!-- TOTAL INGRESOS -->
    <tr class="subtotal-row">
        <td></td>
        <td class="bold right-text">TOTAL INGRESOS</td>
        <td class="right-text bold border-top"><?=to_currency($total_income)?></td>
    </tr>
    
    <!-- ESPACIO -->
    <tr><td colspan="3" style="height:20px;"></td></tr>
    
    <!-- GASTOS -->
    <tr>
        <td colspan="3" class="center-text bold" style="background-color:#f8e8e8;">GASTOS</td>
    </tr>
    
    <?php 
    // Filtrar y mostrar gastos
    $gastos_encontrados = false;
    foreach($accounts as $account): 
        if(strtolower($account->account_type) == 'expenses' && $account->amount != 0): 
            $gastos_encontrados = true;
    ?>
        <tr>
            <td><?=isset($account->code_number) ? $account->code_number : (isset($account->account_map) ? $account->account_map : '')?></td>
            <td class="left-text"><?=$account->account_name?></td>
            <td class="right-text"><?=to_currency($account->amount)?></td>
        </tr>
    <?php 
        endif;
    endforeach; 
    
    if(!$gastos_encontrados): 
    ?>
        <tr>
            <td colspan="3" class="center-text">No hay gastos registrados en el período</td>
        </tr>
    <?php endif; ?>
    
    <!-- TOTAL GASTOS -->
    <tr class="subtotal-row">
        <td></td>
        <td class="bold right-text">TOTAL GASTOS</td>
        <td class="right-text bold border-top"><?=to_currency($total_expenses)?></td>
    </tr>
    
    <!-- ESPACIO -->
    <tr><td colspan="3" style="height:20px;"></td></tr>
    
    <!-- RESULTADO FINAL -->
    <?php 
    $color_fondo = $net_income >= 0 ? '#e8f8e8' : '#f8e8e8';
    $color_texto = $net_income >= 0 ? 'green' : 'red';
    ?>
    
    <tr style="background-color:<?=$color_fondo?>;">
        <td></td>
        <td class="bold right-text">UTILIDAD (PÉRDIDA) NETA</td>
        <td class="right-text bold border-top" style="color:<?=$color_texto?>; font-size:14px;">
            <?=to_currency($net_income)?>
        </td>
    </tr>
</table>

<br/><br/><br/>

<!-- RESUMEN EJECUTIVO -->
<div style="margin-top:30px; padding:15px; border:1px solid #ccc; background-color:#f9f9f9;">
    <h4 class="center-text">RESUMEN EJECUTIVO</h4>
    <table width="100%" style="border-collapse:collapse;">
        <tr>
            <td width="70%" class="bold">Total Ingresos:</td>
            <td width="30%" class="right-text bold"><?=to_currency($total_income)?></td>
        </tr>
        <tr>
            <td class="bold">Total Gastos:</td>
            <td class="right-text bold"><?=to_currency($total_expenses)?></td>
        </tr>
        <tr style="background-color:<?=$color_fondo?>;">
            <td class="bold">Utilidad (Pérdida) Neta:</td>
            <td class="right-text bold" style="color:<?=$color_texto?>;"><?=to_currency($net_income)?></td>
        </tr>
    </table>
</div>

<br/><br/>

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

<!-- ESTADÍSTICAS ADICIONALES -->
<div style="margin-top:40px; font-size:11px; color:#666;">
    <p class="center-text">
        Estado de Resultados generado el: <?=date('d/m/Y H:i:s')?> | 
        Período: <?=date($this->config->item('date_format'), $date_from)?> - <?=date($this->config->item('date_format'), $date_to)?>
    </p>
</div>