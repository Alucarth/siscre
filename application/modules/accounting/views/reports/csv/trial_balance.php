<?php

$total_non_current_assets = 0;
foreach ($non_current_assets as $asset)
{
    $total_non_current_assets += $asset->amount;
}
$total_assets = $total_current_assets + $total_non_current_assets;

$loan_fund_capital = $total_assets;

$total_liability = 0;
foreach ($liability_accounts as $account)
{
    $total_liability += $account->amount;
}
$loan_fund_capital -= $total_liability;
foreach ($equity_accounts as $account)
{
    $loan_fund_capital -= $account->amount;
}
?>

<?php

$out = '"Balance de comprobacion(' . date($this->config->item('date_format'), $date_from) . " - " . date($this->config->item('date_format'), $date_to) . ')",';
$out .= "\n\n";

$out .= '"Nombre de la cuenta",';
$out .= '"Debito",';
$out .= '"Credito"';
$out .= "\n";

$debit_total = $credit_total = 0;
foreach ($accounts as $account)
{

    $out .= '"' . $account->account_name . '",';

    if (in_array($account->account_type, ['asset', 'expenses']))
    {
        $out .= '"' . to_currency($account->amount) . '",';
        $out .= '"",';
        $debit_total += $account->amount;
    }

    if (in_array($account->account_type, ['liability', 'equity', 'income']))
    {
        $out .= '"",';
        $out .= '"' . to_currency($account->amount) . '",';
        $credit_total += $account->amount;
    }

    $out .= "\n";
    
    if (in_array($account->account_type, ['asset', 'expenses']) && $account->depreciation_amount > 0)
    {
        $out .= '"Depreciacion acumulada: ' . $account->account_name . '",';
        $out .= '"",';
        $out .= '"' . to_currency($account->depreciation_amount) . '",';
        $credit_total += $account->depreciation_amount;
        $out .= "\n";
    }
}

$out .= '"Fondo de Prestamo de Patrimonio",';
$out .= '"",';
$out .= '"' . to_currency($loan_fund_capital) . '",';
$out .= "\n";

$out .= '"Interes por Cobrar",';
$out .= '"' . to_currency($interest_on_current) . '",';
$out .= "\n";
$out .= '"Prestamo Neto Pendiente",';
$out .= '"' . to_currency($net_loan_outstanding-$interest_on_current) . '",';
$out .= "\n";
$out .= '"Intereses sobre Prestamos Vigentes y Vencidos",';
$out .= '"",';
$out .= '"' . to_currency($interest_on_current_and_past_due) . '",';
$out .= "\n";

$out .= '"Total",';
$out .= '"' . to_currency($debit_total + $net_loan_outstanding) . '",';
$out .= '"' . to_currency($credit_total + $loan_fund_capital + $interest_on_current_and_past_due) . '"';

$out .= "\n";

echo $out;
?>