<?php

$out = '"Hoja de saldos (' . date($this->config->item('date_format'), $date_from) . " - " . date($this->config->item('date_format'), $date_to) . ')"';
$out .= "\n\n";

$out .= '"ACTIVO"';
$out .= "\n";

$out .= '"Activos Corrientes"';
$out .= "\n";
$out .= '"Efectivo",' . '"' . to_currency($cash_amount) . '"';
$out .= "\n";
$out .= '"Efectivo en bancos",' . '"' . to_currency($cash_amount_bank) . '"';
$out .= "\n";
$out .= '"Prestamos pendientes"';
$out .= "\n";
$out .= '"Prestamo actual",' . '"' . to_currency($current_loan_amount) . '"';
$out .= "\n";
$out .= '"Interes por cobrar",' . '"' . to_currency($interest_on_current) . '"';
$out .= "\n";
$out .= '"MENOS: Reservas por Prestamos Incobrables",' . '"' . to_currency($loan_loss_reserve) . '"';
$out .= "\n";
$out .= '"Prestamos Netos Pendientes",' . '"' . to_currency($net_loan_outstanding) . '"';
$out .= "\n";
$out .= '"TOTAL ACTIVOS ACTUALES",' . '"' . to_currency($total_current_assets) . '"';
$out .= "\n\n";

$out .= '"ACTIVOS No Corrientes"';
$out .= "\n";


$total_non_current_assets = 0;

foreach ($non_current_assets as $asset)
{
    $out .= '"' . $asset->account_name . '",' . '"' . to_currency($asset->amount) . '"';

    $total_non_current_assets += $asset->amount;
    $out .= "\n";
    
    if ( $asset->depreciation_amount > 0 )
    {
        $out .= '"Menos: Depreciacion Acumulada",' . '"(' . to_currency($asset->depreciation_amount) . ')"';
        $total_non_current_assets -= $asset->depreciation_amount;
        $out .= "\n";
    }
}

$out .= '"TOTAL ACTIVOS NO CORRIENTES",' . '"' . to_currency($total_non_current_assets) . '"';

$out .= "\n";
$out .= '"TOTAL ACTIVOS",';

$total_assets = $total_current_assets + $total_non_current_assets;



$out .= '"' . to_currency($total_assets) . '"';
$out .= "\n\n";



$out .= '"PASIVOS"';
$out .= "\n\n";

$loan_fund_capital = $total_assets;
$total_liability = 0;

foreach ($liability_accounts as $account)
{

    $out .= '"' . $account->account_name . '",' . '"' . to_currency($account->amount) . '"';

    $total_liability += $account->amount;
    $out .= "\n";
}

$out .= '"TOTAL PASIVOS",' . '"' . to_currency($total_liability) . '"';
$out .= "\n\n";

$loan_fund_capital -= $total_liability;
foreach ($equity_accounts as $account)
{
    $loan_fund_capital -= $account->amount;
}


$out .= '"PATRIMONIO"';
$out .= "\n";

$out .= '"Fondo de Capital",' . '"' . to_currency($loan_fund_capital) . '"';
$out .= "\n";

$total_capital = 0;
foreach ($equity_accounts as $account)
{
    $out .= '"' . $account->account_name . '",' . '"' . to_currency($account->amount) . '"';
    $out .= "\n";
    $total_capital += $account->amount;
}

$out .= '"TOTAL PATRIMONIO",' . '"' . to_currency($total_capital + $loan_fund_capital) . '"';
$out .= "\n";

$out .= '"TOTAL PASIVO Y CAPITAL",' . '"' . to_currency($total_liability + $total_capital + $loan_fund_capital) . '"';

echo $out;
?>
