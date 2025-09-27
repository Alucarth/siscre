<?php

$out = '"Ingresos Financieros(' . date($this->config->item('date_format'), $date_from) . " - " . date($this->config->item('date_format'), $date_to) . ')"';
$out .= "\n\n";



$total_income = $interest_on_current;
$total_expenses = 0;

$out .= '"Intereses sobre Prestamos Vigentes y Vencidos",' . '"' . to_currency($interest_on_current) . '"';
$out .= "\n";

foreach ($accounts as $account)
{
    if ($account->account_type == 'expenses')
        continue;
    $out .= '"' . $account->account_name . '",' . '"' . to_currency($account->amount) . '"';
    $total_income += $account->amount;
    $out .= "\n";
}
$out .= "\n";

$out .= '"TOTAL INGRESO FINANCIERO",' . '"' . to_currency($total_income) . '"';
$out .= "\n\n";

$out .= '"GASTOS OPERACIONALES"';
$out .= "\n";

foreach ($accounts as $account)
{
    if ($account->account_type == 'income')
        continue;
    $out .= '"' . $account->account_name . '",' . '"' . to_currency($account->amount) . '"';
    $out .= "\n";
    $total_expenses += $account->amount;
}
$out .= "\n\n";
$out .= '"TOTAL GASTOS OPERACIONALES",' . '"' . to_currency($total_expenses) . '"';
$out .= "\n\n";

$out .= '"UTILIDAD NETA DE OPERACION",' . '"' . to_currency($total_income - $total_expenses) . '"';
echo $out;
