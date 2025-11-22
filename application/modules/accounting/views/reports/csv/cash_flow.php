<?php
// Encabezado de la empresa y título
echo '"CREDISURGIR S.R.L."' . "\n";
echo '"NIT: 485672023"' . "\n";
echo '"FLUJO DE EFECTIVO"' . "\n";
echo '"Del: ' . date($this->config->item('date_format'), $date_from) . ' Al: ' . date($this->config->item('date_format'), $date_to) . '"' . "\n";
echo '"(Expresado en bolivianos)"' . "\n";
echo "\n";

// Encabezados de columnas
echo '"Código";"Variación";"Cuenta";"Tipo";"Clasificación";"Saldo Inicial";"Saldo Final";"Diferencia"' . "\n";

if (!empty($accounts)) {
    $current_group = '';
    $group_totals = [
        'ACTIVO' => 0,
        'PASIVO' => 0, 
        'PATRIMONIO' => 0
    ];
    
    foreach ($accounts as $account) {
        // Mostrar header de grupo si cambia
        if ($current_group != $account->tipo_cuenta) {
            $current_group = $account->tipo_cuenta;
            echo '"";"";"";"";"";"";"";""' . "\n";
            echo '"' . $current_group . '";"";"";"";"";"";"";""' . "\n";
        }
        
        // Traducir clasificación
        $clasificacion_display = '';
        switch($account->clasificacion) {
            case 'operating': $clasificacion_display = 'OPERACIÓN'; break;
            case 'investing': $clasificacion_display = 'INVERSIÓN'; break;
            case 'financing': $clasificacion_display = 'FINANCIACIÓN'; break;
            default: $clasificacion_display = strtoupper($account->clasificacion);
        }
        
        // Formatear números sin separadores de miles
        $saldo_inicial = number_format($account->saldo_inicial, 2, '.', '');
        $saldo_final = number_format($account->saldo_final, 2, '.', '');
        $diferencia = number_format($account->diferencia, 2, '.', '');
        
        echo '"' . $account->code_number . '";';
        echo '"' . $account->tipo_variacion . '";';
        echo '"' . $account->account_name . '";';
        echo '"' . $account->tipo_cuenta . '";';
        echo '"' . $clasificacion_display . '";';
        echo '"' . $saldo_inicial . '";';
        echo '"' . $saldo_final . '";';
        echo '"' . $diferencia . '"';
        echo "\n";
        
        $group_totals[$account->tipo_cuenta] += $account->diferencia;
    }
    
    // Línea en blanco antes de totales
    echo '"";"";"";"";"";"";"";""' . "\n";
    
    // Totales por grupo
    $grand_total = 0;
    foreach ($group_totals as $grupo => $total) {
        if ($total != 0) {
            $total_formatted = number_format($total, 2, '.', '');
            echo '"Total ' . $grupo . '";"";"";"";"";"";"";"' . $total_formatted . '"' . "\n";
            $grand_total += $total;
        }
    }
    
    // Total General
    $grand_total_formatted = number_format($grand_total, 2, '.', '');
    echo '"VARIACIÓN TOTAL DE EFECTIVO";"";"";"";"";"";"";"' . $grand_total_formatted . '"' . "\n";
    
} else {
    echo '"No hay movimientos de efectivo en el período seleccionado";"";"";"";"";"";"";""' . "\n";
}
?>