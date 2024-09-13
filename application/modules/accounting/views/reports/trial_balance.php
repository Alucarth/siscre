<style>
    #tbl-trial-balance td:nth-child(2),
    #tbl-trial-balance td:nth-child(3) 
    {
        text-align: center;
    }
</style>

<?php

$total_non_current_assets = 0;
foreach( $non_current_assets as $asset )
{
    $total_non_current_assets += $asset->amount;
}
$total_assets = $total_current_assets + $interest_on_current + $total_non_current_assets;

$loan_fund_capital = $total_assets;

$total_liability = 0;
foreach( $liability_accounts as $account )
{
    $total_liability += $account->amount;
}
$loan_fund_capital -= $total_liability;
foreach ( $equity_accounts as $account )
{
    $loan_fund_capital -= $account->amount;
}

?>

<div style="text-align:center">
    <h3>Balance de comprobacion</h3>
    Fechado: <?=date($this->config->item('date_format'), $date_from) . " - " . date($this->config->item('date_format'), $date_to)?>
    <br/>
    <br/>
</div>

<table width="100%" cellpadding="1" cellspacing="0" border="1" id="tbl-trial-balance">
    <tr>
        <td style="text-align:center"><b>Nombre de la cuenta</b></td>
        <td><b>Debito</b></td>
        <td><b>Credito</b></td>
    </tr>
    <?php $debit_total = $credit_total = 0;?>
    <?php foreach ( $accounts as $account ): ?>
    <tr>
        <td><?=$account->account_name;?></td>
        <td>
            <?php
            if (in_array($account->account_type, ['asset', 'expenses']))
            {
                echo to_currency($account->amount);
                $debit_total += $account->amount;
            }
            ?>
        </td>
        <td>
            <?php
            if (in_array($account->account_type, ['liability', 'equity', 'income']))
            {
                echo to_currency($account->amount);
                $credit_total += $account->amount;
            }
            ?>
        </td>
    </tr>
    <?php if (in_array($account->account_type, ['asset', 'expenses']) && $account->depreciation_amount > 0): ?>
        <tr>
            <td>Depreciacion acumulada: <?=$account->account_name;?></td>
            <td>
                &nbsp;
            </td>
            <td>
                <?php
                    echo to_currency($account->depreciation_amount);
                    $credit_total += $account->depreciation_amount;
                ?>
            </td>
        </tr>
    <?php endif; ?>
    <?php endforeach; ?>
    <tr>
        <td>Fondo de Prestamo de Patrimonio</td>
        <td></td>
        <td><?=to_currency($loan_fund_capital);?></td>
    </tr>
    <tr>
        <td>Interes por Cobrar</td>
        <td><?=to_currency($interest_on_current);?></td>
        <td></td>
    </tr>
    <tr>
        <td>Prestamo Neto Pendiente</td>
        <td><?=to_currency($net_loan_outstanding);?></td>
        <td></td>
    </tr>
    <tr>
        <td>Intereses sobre Prestamos Vigentes y Vencidos</td>
        <td></td>
        <td><?=to_currency($interest_on_current_and_past_due);?></td>
    </tr>
    
    <tr>
        <td><b>Total</b></td>
        <td><?=to_currency($debit_total+$net_loan_outstanding);?></td>
        <td><?=to_currency($credit_total+$loan_fund_capital+$interest_on_current_and_past_due);?></td>
    </tr>
    
</table>