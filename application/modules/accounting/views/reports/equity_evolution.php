<style>
.table-patrimonio {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
    margin-top: 20px;
}
.table-patrimonio th, .table-patrimonio td {
    border: 1px solid #000;
    padding: 6px 8px;
    text-align: right;
}
.table-patrimonio th {
    background-color: #f0f0f0;
    font-weight: bold;
    text-align: center;
}
.table-patrimonio .codigo {
    text-align: center;
    width: 12%;
}
.table-patrimonio .cuenta {
    text-align: left;
    width: 25%;
    font-weight: bold;
}
.table-patrimonio .columna-cuenta {
    width: 12%;
    text-align: right;
}
.table-patrimonio .total-col {
    width: 12%;
    text-align: right;
    font-weight: bold;
}
.table-patrimonio .total-row {
    font-weight: bold;
    background-color: #d0d0d0;
    border-top: 2px solid #000;
}
.table-patrimonio .fila-cuenta td {
    background-color: #f8f8f8;
}
.text-left { text-align: left; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.bold { font-weight: bold; }
.page-landscape {
    width: 100%;
    margin: 0;
    padding: 0;
}
</style>

<div class="page-landscape">
    <div style="position:absolute; top:20px; left:40px;" class="empresa-info">
        <div class="bold">CREDISURGIR S.R.L.</div>
        <div>NIT: 485672023</div>
    </div>

    <div style="text-align:center; margin-top:40px;">
        <h2>EVOLUCIÓN DEL PATRIMONIO</h2>
        <h4>Del: <?=date($this->config->item('date_format'), $date_from)?> Al: <?=date($this->config->item('date_format'), $date_to)?></h4>
        <p>(Expresado en bolivianos)</p>
        <br/>
    </div>

    <?php if (!empty($accounts['cuentas_con_saldo'])): ?>
    <table class="table-patrimonio">
        <thead>
            <tr>
                <th class="codigo">CÓDIGO</th>
                <th class="cuenta">CUENTA</th>
                <?php foreach ($accounts['cuentas_con_saldo'] as $cuenta_col): ?>
                    <th class="columna-cuenta"><?=html_escape($cuenta_col->account_name)?></th>
                <?php endforeach; ?>
                <th class="total-col">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            // Inicializar array para totales por columna
            $totales_columnas = [];
            foreach ($accounts['cuentas_con_saldo'] as $cuenta_col) {
                $totales_columnas[$cuenta_col->id] = 0;
            }
            ?>
            
            <!-- Filas para cada cuenta -->
            <?php foreach ($accounts['cuentas_con_saldo'] as $cuenta_fila): ?>
                <tr class="fila-cuenta">
                    <td class="text-center codigo"><?=$cuenta_fila->code_number?></td>
                    <td class="text-left cuenta"><?=$cuenta_fila->account_name?></td>
                    
                    <?php 
                    $total_fila = 0;
                    foreach ($accounts['cuentas_con_saldo'] as $cuenta_col): 
                        if ($cuenta_col->id == $cuenta_fila->id) {
                            $valor = $cuenta_fila->saldo_total;
                            $total_fila += $valor;
                            $totales_columnas[$cuenta_col->id] += $valor;
                        } else {
                            $valor = 0;
                        }
                    ?>
                        <td class="text-right">
                            <?=($valor != 0) ? to_currency($valor) : '-'?>
                        </td>
                    <?php endforeach; ?>
                    
                    <td class="text-right total-col"><?=to_currency($total_fila)?></td>
                </tr>
            <?php endforeach; ?>
            
            <!-- Fila de totales -->
            <tr class="total-row">
                <td class="text-center bold">TOTAL</td>
                <td class="text-left bold"></td>
                
                <?php 
                $total_general = 0;
                foreach ($accounts['cuentas_con_saldo'] as $cuenta_col): 
                    $total_columna = $totales_columnas[$cuenta_col->id];
                    $total_general += $total_columna;
                ?>
                    <td class="text-right bold"><?=to_currency($total_columna)?></td>
                <?php endforeach; ?>
                
                <td class="text-right bold"><?=to_currency($total_general)?></td>
            </tr>
        </tbody>
    </table>
    <?php else: ?>
    <div style="text-align: center; margin-top: 50px;">
        <p>No hay movimientos en cuentas patrimoniales para el período seleccionado.</p>
    </div>
    <?php endif; ?>

    <!-- FIRMAS -->
    <br><br>
    <table style="width: 100%; margin-top: 60px; border: none;">
        <tr>
            <td style="width: 50%; text-align: center; border: none;">
                <div style="font-family: 'Courier New', monospace; font-size: 14px; letter-spacing: 0px; margin: 0 auto 15px auto;">________________________</div>
                <div style="font-size: 11px; font-weight: bold;">CONTADOR</div>
            </td>
            <td style="width: 50%; text-align: center; border: none;">
                <div style="font-family: 'Courier New', monospace; font-size: 14px; letter-spacing: 0px; margin: 0 auto 15px auto;">________________________</div>
                <div style="font-size: 11px; font-weight: bold;">GERENTE GENERAL</div>
            </td>
        </tr>
    </table>
</div>