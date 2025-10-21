<?php

$out = '"CREDISURGIR S.R.L."' . "\n";
$out .= '"NIT: 485672023"' . "\n";
$out .= '"ESTADO DE RESULTADOS"' . "\n";
$out .= '"Del: ' . date($this->config->item('date_format'), $date_from) . ' Al: ' . date($this->config->item('date_format'), $date_to) . '"' . "\n";
$out .= '"(Expresado en bolivianos)"' . "\n\n";

// Encabezados de columnas
$out .= '"Código";"Cuenta";"Importe"' . "\n";

// INGRESOS
$out .= '"";"INGRESOS";""' . "\n";

$ingresos_encontrados = false;
foreach($accounts as $account): 
    if(strtolower($account->account_type) == 'income' && $account->amount != 0): 
        $ingresos_encontrados = true;
        $out .= '"' . (isset($account->code_number) ? $account->code_number : (isset($account->account_map) ? $account->account_map : '')) . '";"' . $account->account_name . '";"' . number_format($account->amount, 2, '.', '') . '"' . "\n";
    endif;
endforeach; 

if(!$ingresos_encontrados): 
    $out .= '"";"No hay ingresos registrados en el período";""' . "\n";
endif; 

// TOTAL INGRESOS
$out .= '"";"TOTAL INGRESOS";"' . number_format($total_income, 2, '.', '') . '"' . "\n\n";

// GASTOS
$out .= '"";"GASTOS";""' . "\n";

$gastos_encontrados = false;
foreach($accounts as $account): 
    if(strtolower($account->account_type) == 'expenses' && $account->amount != 0): 
        $gastos_encontrados = true;
        $out .= '"' . (isset($account->code_number) ? $account->code_number : (isset($account->account_map) ? $account->account_map : '')) . '";"' . $account->account_name . '";"' . number_format($account->amount, 2, '.', '') . '"' . "\n";
    endif;
endforeach; 

if(!$gastos_encontrados): 
    $out .= '"";"No hay gastos registrados en el período";""' . "\n";
endif; 

// TOTAL GASTOS
$out .= '"";"TOTAL GASTOS";"' . number_format($total_expenses, 2, '.', '') . '"' . "\n\n";

// RESULTADO FINAL
$resultado_texto = $net_income >= 0 ? 'UTILIDAD NETA' : 'PÉRDIDA NETA';
$out .= '"";"' . $resultado_texto . '";"' . number_format($net_income, 2, '.', '') . '"' . "\n\n";

// RESUMEN EJECUTIVO
$out .= '"RESUMEN EJECUTIVO";"";""' . "\n";
$out .= '"";"Total Ingresos";"' . number_format($total_income, 2, '.', '') . '"' . "\n";
$out .= '"";"Total Gastos";"' . number_format($total_expenses, 2, '.', '') . '"' . "\n";
$out .= '"";"' . $resultado_texto . '";"' . number_format($net_income, 2, '.', '') . '"' . "\n\n";

// INFORMACIÓN ADICIONAL
$out .= '"Generado el: ' . date('d/m/Y H:i:s') . ' (UTC-4)";"";""' . "\n";
$out .= '"Total cuentas: ' . ($ingresos_encontrados ? count($accounts) : 0) . '";"";""' . "\n";

echo $out;
?>