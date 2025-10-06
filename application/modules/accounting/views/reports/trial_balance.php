<style>
    .center-text { text-align:center; }
    .right-text { text-align:right; }
    .left-text { text-align:left; }
</style>

<div style="position:absolute; top:20px; left:40px;" class="empresa-info">
    <div class="bold">CREDISURGIR</div>
    <div>NIT: 485672023</div>
</div>  
<div style="text-align:center">
    <h3>BALANCE DE COMPROBACIÓN</h3>
    Del: <?=date($this->config->item('date_format'), $date_from)?> 
    Al: <?=date($this->config->item('date_format'), $date_to)?>
    <br/><h4>(Expresado en Bolivianos)</h4><br/>
</div>

<table width="100%" cellpadding="4" cellspacing="0" border="1">
    <tr>
        <th width="40%">Cuenta</th>
        <th width="20%">Tipo</th>
        <th width="20%">Débito</th>
        <th width="20%">Crédito</th>
    </tr>
    
    <?php 
    $total_debito = 0;
    $total_credito = 0;
    ?>
    
    <?php foreach($accounts as $account): ?>
    <tr>
        <td class="left-text"><?=$account->account_name?></td>
        <td class="center-text">
            <?=ucfirst($account->account_type)?>
        </td>
        <td class="right-text">
            <?php 
            if(isset($account->debit_amount) && $account->debit_amount > 0) {
                echo to_currency($account->debit_amount);
                $total_debito += $account->debit_amount;
            } else {
                echo to_currency(0);
            }
            ?>
        </td>
        <td class="right-text">
            <?php 
            if(isset($account->credit_amount) && $account->credit_amount > 0) {
                echo to_currency($account->credit_amount);
                $total_credito += $account->credit_amount;
            } else {
                echo to_currency(0);
            }
            ?>
        </td>
    </tr>
    <?php endforeach; ?>
    
    <!-- TOTALES -->
    <tr style="background-color:#f0f0f0;">
        <td colspan="2" class="right-text"><b>TOTALES</b></td>
        <td class="right-text"><b><?=to_currency($total_debito)?></b></td>
        <td class="right-text"><b><?=to_currency($total_credito)?></b></td>
    </tr>
    
    <!-- VERIFICACIÓN -->
    <tr>
        <td colspan="4" class="center-text" style="<?=($total_debito == $total_credito) ? 'color:green;' : 'color:red;'?>">
            <?php if($total_debito == $total_credito): ?>
                <b>✓ CUADRADO - Débitos (<?=to_currency($total_debito)?>) = Créditos (<?=to_currency($total_credito)?>)</b>
            <?php else: ?>
                <b>✗ NO CUADRADO - Diferencia: <?=to_currency(abs($total_debito - $total_credito))?></b>
            <?php endif; ?>
        </td>
    </tr>
</table>