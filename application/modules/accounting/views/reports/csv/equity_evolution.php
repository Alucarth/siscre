<?php
// Encabezado del reporte
echo '"CREDISURGIR S.R.L."' . "\n";
echo '"NIT: 485672023"' . "\n";
echo '"EVOLUCIÓN DEL PATRIMONIO"' . "\n";
echo '"Del: ' . date($this->config->item('date_format'), $date_from) . ' Al: ' . date($this->config->item('date_format'), $date_to) . '"' . "\n";
echo '"(Expresado en bolivianos)"' . "\n\n";

if (!empty($accounts['cuentas_con_saldo'])): 
    // Preparar encabezados de columnas
    $headers = ['"Código"', '"Cuenta"'];
    foreach ($accounts['cuentas_con_saldo'] as $cuenta_col) {
        $headers[] = '"' . str_replace('"', '""', $cuenta_col->account_name) . '"';
    }
    $headers[] = '"Total"';
    
    echo implode(';', $headers) . "\n";
    
    // Inicializar array para totales por columna
    $totales_columnas = [];
    foreach ($accounts['cuentas_con_saldo'] as $cuenta_col) {
        $totales_columnas[$cuenta_col->id] = 0;
    }
    
    // Filas para cada cuenta
    foreach ($accounts['cuentas_con_saldo'] as $cuenta_fila):
        $fila = [
            '"' . $cuenta_fila->code_number . '"',
            '"' . str_replace('"', '""', $cuenta_fila->account_name) . '"'
        ];
        
        $total_fila = 0;
        foreach ($accounts['cuentas_con_saldo'] as $cuenta_col): 
            if ($cuenta_col->id == $cuenta_fila->id) {
                $valor = $cuenta_fila->saldo_total;
                $total_fila += $valor;
                $totales_columnas[$cuenta_col->id] += $valor;
                $fila[] = '"' . number_format($valor, 2, '.', '') . ' Bs."';
            } else {
                $fila[] = '""';
            }
        endforeach;
        
        $fila[] = '"' . number_format($total_fila, 2, '.', '') . ' Bs."';
        
        echo implode(';', $fila) . "\n";
    endforeach;
    
    // Línea separadora
    echo "\n";
    
    // Fila de totales
    $fila_total = ['"TOTAL"', '""'];
    $total_general = 0;
    
    foreach ($accounts['cuentas_con_saldo'] as $cuenta_col): 
        $total_columna = $totales_columnas[$cuenta_col->id];
        $total_general += $total_columna;
        $fila_total[] = '"' . number_format($total_columna, 2, '.', '') . ' Bs."';
    endforeach;
    
    $fila_total[] = '"' . number_format($total_general, 2, '.', '') . ' Bs."';
    
    echo implode(';', $fila_total) . "\n";
    
else:
    echo '"No hay movimientos en cuentas patrimoniales para el período seleccionado."' . "\n";
endif;
?>