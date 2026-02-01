<style>
    .center-text { text-align:center; }
    .right-text { text-align:right; }
    .left-text { text-align:left; }
    .bold { font-weight:bold; }
    .border-bottom { border-bottom:1px solid #000; }
    .table-simple { border-collapse:collapse; width:100%; font-size: 11px; }
    .table-simple td, .table-simple th { padding:4px; border: 1px solid #ddd; }
    .empresa-info { font-size:12px; line-height:1.2; }
    .total-row { background-color: #f9f9f9; font-weight: bold; }
</style>

<div style="position:absolute; top:20px; left:40px;" class="empresa-info">
    <div class="bold">CREDISURGIR S.R.L.</div>
    <div>NIT: 485672023</div>
</div>

<div style="text-align:center; margin-top:40px;">
    <h2 style="margin-bottom: 5px;">BALANCE GENERAL</h2>
    <h4 style="margin-top: 0;">Del: <?=date($this->config->item('date_format'), $date_from)?> Al: <?=date($this->config->item('date_format'), $date_to)?></h4>
    <p style="font-size: 11px;">(Expresado en bolivianos)</p>
    <br/>
</div>

<table class="table-simple">
    <thead>
        <tr style="background-color:#f0f0f0;">
            <th width="15%">Código</th>
            <th width="55%">Cuenta</th>
            <th width="30%" class="right-text">Importe</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="3" class="center-text bold" style="background-color:#e8e8e8;">ACTIVOS</td>
        </tr>
        
        <tr class="total-row">
            <td>11</td>
            <td>ACTIVOS CORRIENTES</td>
            <td class="right-text border-bottom"><?=money($total_activos_corrientes)?></td>
        </tr>
        
        <?php foreach($activos_corrientes as $activo): ?>
            <?php if($activo->amount != 0): ?>
            <tr>
                <td><?=$activo->account_map?></td>
                <td class="left-text"><?=$activo->account_name?></td>
                <td class="right-text"><?=money($activo->amount)?></td>
            </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        
        <tr class="total-row">
            <td></td>
            <td class="right-text">TOTAL ACTIVOS CORRIENTES</td>
            <td class="right-text border-bottom"><?=money($total_activos_corrientes)?></td>
        </tr>
        
        <tr class="total-row">
            <td>12</td>
            <td>ACTIVOS NO CORRIENTES</td>
            <td class="right-text border-bottom"><?=money($total_activos_no_corrientes)?></td>
        </tr>
        
        <?php foreach($activos_no_corrientes as $activo): ?>
            <?php if($activo->amount != 0): ?>
            <tr>
                <td><?=$activo->account_map?></td>
                <td class="left-text"><?=$activo->account_name?></td>
                <td class="right-text"><?=money($activo->amount)?></td>
            </tr>
            <?php if($activo->depreciation_amount > 0): ?>
            <tr>
                <td></td>
                <td class="left-text" style="padding-left:20px;">(-) Depreciación Acumulada</td>
                <td class="right-text">(<?=money($activo->depreciation_amount)?>)</td>
            </tr>
            <?php endif; ?>
            <?php endif; ?>
        <?php endforeach; ?>
        
        <tr class="total-row">
            <td></td>
            <td class="right-text">TOTAL ACTIVOS NO CORRIENTES</td>
            <td class="right-text border-bottom"><?=money($total_activos_no_corrientes)?></td>
        </tr>
        
        <tr class="total-row" style="background-color:#e8f4f8;">
            <td></td>
            <td class="right-text">TOTAL ACTIVOS</td>
            <td class="right-text"><?=money($total_activos)?></td>
        </tr>
        
        <tr>
            <td colspan="3" class="center-text bold" style="background-color:#e8e8e8;">PASIVOS</td>
        </tr>

        <tr class="total-row">
            <td>21</td>
            <td>PASIVOS CORRIENTES</td>
            <td class="right-text border-bottom"><?=money($total_pasivos_corrientes)?></td>
        </tr>
        <?php foreach($pasivos_corrientes as $p): ?>
            <tr>
                <td><?=$p->account_map?></td>
                <td class="left-text"><?=$p->account_name?></td>
                <td class="right-text"><?=money($p->amount)?></td>
            </tr>
        <?php endforeach; ?>
        
        <tr class="total-row">
            <td>22</td>
            <td>PASIVOS NO CORRIENTES</td>
            <td class="right-text border-bottom"><?=money($total_pasivos_no_corrientes)?></td>
        </tr>
        <?php foreach($pasivos_no_corrientes as $p): ?>
            <tr>
                <td><?=$p->account_map?></td>
                <td class="left-text"><?=$p->account_name?></td>
                <td class="right-text"><?=money($p->amount)?></td>
            </tr>
        <?php endforeach; ?>
        
        <tr class="total-row" style="background-color:#f8e8e8;">
            <td></td>
            <td class="right-text">TOTAL PASIVOS</td>
            <td class="right-text"><?=money($total_pasivos)?></td>
        </tr>

        <tr>
            <td colspan="3" class="center-text bold" style="background-color:#e8e8e8;">PATRIMONIO</td>
        </tr>
        <tr class="total-row">
            <td>31</td>
            <td>PATRIMONIO NETO</td>
            <td class="right-text border-bottom"><?=money($total_patrimonio)?></td>
        </tr>
        <?php foreach($patrimonio as $pat): ?>
            <?php if($pat->amount != 0): ?>
            <tr>
                <td><?=$pat->account_map?></td>
                <td class="left-text"><?=$pat->account_name?></td>
                <td class="right-text"><?=money($pat->amount)?></td>
            </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        
        <tr class="total-row" style="background-color:#f8e8e8;">
            <td></td>
            <td class="right-text">TOTAL PASIVOS Y PATRIMONIO</td>
            <td class="right-text"><?=money($total_pasivos_patrimonio)?></td>
        </tr>
    </tbody>
</table>

<div style="margin-top:30px; padding:15px; border:1px solid #ccc; background-color:#f9f9f9;">
    <table width="100%" style="border-collapse:collapse;">
        <tr>
            <td width="70%" class="bold">TOTAL ACTIVOS:</td>
            <td width="30%" class="right-text bold"><?=money($total_activos)?></td>
        </tr>
        <tr>
            <td class="bold">TOTAL PASIVOS + PATRIMONIO:</td>
            <td class="right-text bold"><?=money($total_pasivos_patrimonio)?></td>
        </tr>
    </table>
</div>

<table style="width: 100%; margin-top: 80px; border: none;">
    <tr>
        <td style="width: 50%; text-align: center; border: none;">
            <div style="font-family: 'Courier New', monospace; font-size: 14px; margin-bottom: 10px;">________________________</div>
            <div style="font-size: 11px; font-weight: bold;">CONTADOR</div>
        </td>
        <td style="width: 50%; text-align: center; border: none;">
            <div style="font-family: 'Courier New', monospace; font-size: 14px; margin-bottom: 10px;">________________________</div>
            <div style="font-size: 11px; font-weight: bold;">GERENTE GENERAL</div>
        </td>
    </tr>
</table>