<style>
    .center-text { text-align:center; }
    .right-text { text-align:right; }
    .left-text { text-align:left; }
</style>

<div style="position:absolute; top:20px; left:40px;" class="empresa-info">
    <div class="bold">CREDISURGIR S.R.L.</div>
    <div>NIT: 485672023</div>
</div>  
<div style="text-align:center">
    <h3>BALANCE DE SUMAS Y SALDOS</h3>
    Del: <?=date($this->config->item('date_format'), $date_from)?> 
    Al: <?=date($this->config->item('date_format'), $date_to)?>
    <br/><h4>(Expresado en Bolivianos)</h4><br/>
</div>

<table width="100%" cellpadding="4" cellspacing="0" border="1">
    <tr>
        <th width="12%">Código</th>
        <th width="40%">Cuenta</th>
        <th width="12%">Debe</th>
        <th width="12%">Haber</th>
        <th width="12%">Saldo Debe</th>
        <th width="12%">Saldo Haber</th>
    </tr>
    
    <?php 
    $total_debito = 0;
    $total_credito = 0;
    $total_saldo_debe = 0;
    $total_saldo_haber = 0;
    ?>
    
    <?php foreach($accounts as $account): ?>
    <?php
    $debito = isset($account->debit_amount) ? $account->debit_amount : 0;
    $credito = isset($account->credit_amount) ? $account->credit_amount : 0;
    
    // Calcular saldos según lógica de Balance de Comprobación
    $saldo_debe = $debito - $credito;
    $saldo_haber = $credito - $debito;
    
    $show_saldo_debe = $saldo_debe > 0 ? $saldo_debe : 0;
    $show_saldo_haber = $saldo_haber > 0 ? $saldo_haber : 0;
    
    $total_debito += $debito;
    $total_credito += $credito;
    $total_saldo_debe += $show_saldo_debe;
    $total_saldo_haber += $show_saldo_haber;
    ?>
    <tr>
        <td class="left-text"><?=isset($account->code_number) ? $account->code_number : ''?></td>
        <td class="left-text"><?=isset($account->account_name) ? $account->account_name : ''?></td>
        <td class="right-text"><?=money($debito)?></td>
        <td class="right-text"><?=money($credito)?></td>
        <td class="right-text"><?=money($show_saldo_debe)?></td>
        <td class="right-text"><?=money($show_saldo_haber)?></td>
    </tr>
    <?php endforeach; ?>
    
    <!-- TOTALES -->
    <tr style="background-color:#f0f0f0;">
        <td colspan="2" class="right-text"><b>TOTALES</b></td>
        <td class="right-text"><b><?=money($total_debito)?></b></td>
        <td class="right-text"><b><?=money($total_credito)?></b></td>
        <td class="right-text"><b><?=money($total_saldo_debe)?></b></td>
        <td class="right-text"><b><?=money($total_saldo_haber)?></b></td>
    </tr>
</table>

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