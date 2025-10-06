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
    <h2>BALANCE GENERAL</h2>
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
    
    <!-- ACTIVOS -->
    <tr>
        <td colspan="3" class="center-text bold" style="background-color:#f0f0f0;">ACTIVOS</td>
    </tr>
    
    <!-- ACTIVOS CORRIENTES -->
    <tr>
        <td class="bold">11</td>
        <td class="bold">ACTIVOS CORRIENTES</td>
        <td class="right-text"></td>
    </tr>
    
    <?php foreach($activos_corrientes as $activo): ?>
    <?php if($activo->amount != 0): ?>
    <tr>
        <td><?=$activo->account_map?></td>
        <td class="left-text"><?=$activo->account_name?></td>
        <td class="right-text"><?=to_currency($activo->amount)?></td>
    </tr>
    <?php endif; ?>
    <?php endforeach; ?>
    
    <tr>
        <td></td>
        <td class="bold right-text">TOTAL ACTIVOS CORRIENTES</td>
        <td class="right-text bold border-bottom"><?=to_currency($total_activos_corrientes)?></td>
    </tr>
    
    <!-- ACTIVOS NO CORRIENTES -->
    <tr>
        <td class="bold">12</td>
        <td class="bold">ACTIVOS NO CORRIENTES</td>
        <td class="right-text"></td>
    </tr>
    
    <?php foreach($activos_no_corrientes as $activo): ?>
    <?php if($activo->amount != 0): ?>
    <tr>
        <td><?=$activo->account_map?></td>
        <td class="left-text"><?=$activo->account_name?></td>
        <td class="right-text"><?=to_currency($activo->amount)?></td>
    </tr>
    <?php if($activo->depreciation_amount > 0): ?>
    <tr>
        <td></td>
        <td class="left-text" style="padding-left:20px;">(-) Depreciación Acumulada</td>
        <td class="right-text">(<?=to_currency($activo->depreciation_amount)?>)</td>
    </tr>
    <?php endif; ?>
    <?php endif; ?>
    <?php endforeach; ?>
    
    <tr>
        <td></td>
        <td class="bold right-text">TOTAL ACTIVOS NO CORRIENTES</td>
        <td class="right-text bold border-bottom"><?=to_currency($total_activos_no_corrientes)?></td>
    </tr>
    
    <!-- TOTAL ACTIVOS -->
    <tr>
        <td></td>
        <td class="bold right-text">TOTAL ACTIVOS</td>
        <td class="right-text bold" style="background-color:#e8f4f8;"><?=to_currency($total_activos)?></td>
    </tr>
    
    <!-- ESPACIO -->
    <tr><td colspan="3" style="height:20px;"></td></tr>
    
    <!-- PASIVOS -->
    <tr>
        <td colspan="3" class="center-text bold" style="background-color:#f0f0f0;">PASIVOS</td>
    </tr>
    
    <?php foreach($pasivos as $pasivo): ?>
    <?php if($pasivo->amount != 0): ?>
    <tr>
        <td><?=$pasivo->account_map?></td>
        <td class="left-text"><?=$pasivo->account_name?></td>
        <td class="right-text"><?=to_currency($pasivo->amount)?></td>
    </tr>
    <?php endif; ?>
    <?php endforeach; ?>
    
    <tr>
        <td></td>
        <td class="bold right-text">TOTAL PASIVOS</td>
        <td class="right-text bold border-bottom"><?=to_currency($total_pasivos)?></td>
    </tr>
    
    <!-- ESPACIO -->
    <tr><td colspan="3" style="height:20px;"></td></tr>
    
    <!-- PATRIMONIO -->
    <tr>
        <td colspan="3" class="center-text bold" style="background-color:#f0f0f0;">PATRIMONIO</td>
    </tr>
    
    <?php foreach($patrimonio as $pat): ?>
    <?php if($pat->amount != 0): ?>
    <tr>
        <td><?=$pat->account_map?></td>
        <td class="left-text"><?=$pat->account_name?></td>
        <td class="right-text"><?=to_currency($pat->amount)?></td>
    </tr>
    <?php endif; ?>
    <?php endforeach; ?>
    
    <tr>
        <td></td>
        <td class="bold right-text">TOTAL PATRIMONIO</td>
        <td class="right-text bold border-bottom"><?=to_currency($total_patrimonio)?></td>
    </tr>
    
    <!-- TOTAL PASIVOS + PATRIMONIO -->
    <tr>
        <td></td>
        <td class="bold right-text">TOTAL PASIVOS Y PATRIMONIO</td>
        <td class="right-text bold" style="background-color:#f8e8e8;"><?=to_currency($total_pasivos_patrimonio)?></td>
    </tr>
</table>

<br/><br/>

<!-- VERIFICACIÓN DEL BALANCE -->
<div style="text-align:center; <?=$balance_cuadra ? 'color:green;' : 'color:red;'?>">
    <?php if($balance_cuadra): ?>
        <h4>✓ BALANCE CUADRADO - ACTIVOS = PASIVOS + PATRIMONIO</h4>
    <?php else: ?>
        <h4>✗ DESCUADRE DETECTADO: <?=to_currency(abs($total_activos - $total_pasivos_patrimonio))?></h4>
    <?php endif; ?>
</div>

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