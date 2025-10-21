<?php

$out = '"CREDISURGIR S.R.L."' . "\n";
$out .= '"NIT: 485672023"' . "\n";
$out .= '"BALANCE GENERAL"' . "\n";
$out .= '"Del: ' . date($this->config->item('date_format'), $date_from) . ' Al: ' . date($this->config->item('date_format'), $date_to) . '"' . "\n";
$out .= '"(Expresado en bolivianos)"' . "\n\n";

// Encabezados de columnas - usando punto y coma como separador
$out .= '"Código";"Cuenta' . str_repeat(' ', 50) . '";"Importe"' . "\n";

// ACTIVOS
$out .= '"";"ACTIVOS' . str_repeat(' ', 50) . '";""' . "\n";

// ACTIVOS CORRIENTES
$out .= '"11";"ACTIVOS CORRIENTES' . str_repeat(' ', 50) . '";""' . "\n";

foreach($activos_corrientes as $activo):
    if($activo->amount != 0):
        $out .= '"' . $activo->account_map . '";"' . $activo->account_name . str_repeat(' ', 50) . '";"' . to_currency($activo->amount) . '"' . "\n";
    endif;
endforeach;

$out .= '"";"TOTAL ACTIVOS CORRIENTES' . str_repeat(' ', 50) . '";"' . to_currency($total_activos_corrientes) . '"' . "\n";

// ACTIVOS NO CORRIENTES
$out .= '"12";"ACTIVOS NO CORRIENTES' . str_repeat(' ', 50) . '";""' . "\n";

foreach($activos_no_corrientes as $activo):
    if($activo->amount != 0):
        $out .= '"' . $activo->account_map . '";"' . $activo->account_name . str_repeat(' ', 50) . '";"' . to_currency($activo->amount) . '"' . "\n";
        
        if($activo->depreciation_amount > 0):
            $out .= '"";"(-) Depreciación Acumulada' . str_repeat(' ', 50) . '";"(' . to_currency($activo->depreciation_amount) . ')"' . "\n";
        endif;
    endif;
endforeach;

$out .= '"";"TOTAL ACTIVOS NO CORRIENTES' . str_repeat(' ', 50) . '";"' . to_currency($total_activos_no_corrientes) . '"' . "\n";

$out .= '"";"TOTAL ACTIVOS' . str_repeat(' ', 50) . '";"' . to_currency($total_activos) . '"' . "\n\n";

// PASIVOS
$out .= '"";"PASIVOS' . str_repeat(' ', 50) . '";""' . "\n";

foreach($pasivos as $pasivo):
    if($pasivo->amount != 0):
        $out .= '"' . $pasivo->account_map . '";"' . $pasivo->account_name . str_repeat(' ', 50) . '";"' . to_currency($pasivo->amount) . '"' . "\n";
    endif;
endforeach;

$out .= '"";"TOTAL PASIVOS' . str_repeat(' ', 50) . '";"' . to_currency($total_pasivos) . '"' . "\n\n";

// PATRIMONIO
$out .= '"";"PATRIMONIO' . str_repeat(' ', 50) . '";""' . "\n";

foreach($patrimonio as $pat):
    if($pat->amount != 0):
        $out .= '"' . $pat->account_map . '";"' . $pat->account_name . str_repeat(' ', 50) . '";"' . to_currency($pat->amount) . '"' . "\n";
    endif;
endforeach;

$out .= '"";"TOTAL PATRIMONIO' . str_repeat(' ', 50) . '";"' . to_currency($total_patrimonio) . '"' . "\n";

$out .= '"";"TOTAL PASIVOS Y PATRIMONIO' . str_repeat(' ', 50) . '";"' . to_currency($total_pasivos_patrimonio) . '"' . "\n\n";

// RESUMEN FINAL
$out .= '"RESUMEN:";"";""' . "\n";
$out .= '"";"TOTAL ACTIVOS' . str_repeat(' ', 50) . '";"' . to_currency($total_activos) . '"' . "\n";
$out .= '"";"TOTAL PASIVOS' . str_repeat(' ', 50) . '";"' . to_currency($total_pasivos) . '"' . "\n";
$out .= '"";"TOTAL PATRIMONIO' . str_repeat(' ', 50) . '";"' . to_currency($total_patrimonio) . '"' . "\n";
$out .= '"";"TOTAL PASIVOS + PATRIMONIO' . str_repeat(' ', 50) . '";"' . to_currency($total_pasivos_patrimonio) . '"' . "\n";

echo $out;
?>