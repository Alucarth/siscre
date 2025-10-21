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

$out = '"CREDISURGIR S.R.L."' . "\n";
$out .= '"NIT: 485672023"' . "\n";
$out .= '"BALANCE DE COMPROBACIÓN"' . "\n";
$out .= '"Del: ' . date($this->config->item('date_format'), $date_from) . ' Al: ' . date($this->config->item('date_format'), $date_to) . '"' . "\n";
$out .= '"(Expresado en bolivianos)"' . "\n\n";

// Encabezados de columnas
$out .= '"Código";"Nombre de la cuenta";"Débito";"Crédito"' . "\n";

$debit_total = $credit_total = 0;
foreach ($accounts as $account)
{
    $codigo = isset($account->code_number) ? $account->code_number : (isset($account->account_map) ? $account->account_map : '');
    
    $out .= '"' . $codigo . '";"' . $account->account_name . '";';

    if (in_array($account->account_type, ['asset', 'expenses']))
    {
        $out .= '"' . to_currency($account->amount) . '";""';
        $debit_total += $account->amount;
    }
    else if (in_array($account->account_type, ['liability', 'equity', 'income']))
    {
        $out .= '"";"' . to_currency($account->amount) . '"';
        $credit_total += $account->amount;
    }
    else
    {
        $out .= '"";""';
    }

    $out .= "\n";
    
    if (in_array($account->account_type, ['asset', 'expenses']) && $account->depreciation_amount > 0)
    {
        $out .= '"";"Depreciación acumulada: ' . $account->account_name . '";"";"' . to_currency($account->depreciation_amount) . '"';
        $credit_total += $account->depreciation_amount;
        $out .= "\n";
    }
}

// CUENTAS ESPECIALES
$out .= '"";"Fondo de Préstamo de Patrimonio";"";"' . to_currency($loan_fund_capital) . '"' . "\n";
$out .= '"";"Interés por Cobrar";"' . to_currency($interest_on_current) . '";""' . "\n";
$out .= '"";"Préstamo Neto Pendiente";"' . to_currency($net_loan_outstanding-$interest_on_current) . '";""' . "\n";
$out .= '"";"Intereses sobre Préstamos Vigentes y Vencidos";"";"' . to_currency($interest_on_current_and_past_due) . '"' . "\n\n";

// TOTALES
$out .= '"";"TOTAL";"' . to_currency($debit_total + $net_loan_outstanding) . '";"' . to_currency($credit_total + $loan_fund_capital + $interest_on_current_and_past_due) . '"' . "\n";

echo $out;
?>