<style>
    .center-text { text-align:center; }
    .right-text { text-align:right; }
    .left-text { text-align:left; }
    .bold { font-weight:bold; }
    
    /* Estilos para asegurar que todo quepa en una línea */
    .table-balances { 
        border-collapse:collapse; 
        width:100%; 
        font-size: 10px; /* Tamaño de fuente base ligeramente menor */
    }
    .table-balances th, .table-balances td { 
        padding: 4px 2px; /* Reducimos padding lateral */
    }
    
    /* Clase crítica para evitar el salto de línea en los números */
    .no-wrap { 
        white-space: nowrap; 
    }

    .total-row {
        background-color: #f0f0f0;
        font-weight: bold;
        font-size: 10px; /* Aseguramos tamaño consistente */
    }
</style>

<div style="position:absolute; top:20px; left:40px;" class="empresa-info">
    <div class="bold">CREDISURGIR S.R.L.</div>
    <div>NIT: 485672023</div>
</div>   

<div style="text-align:center">
    <h3>BALANCE DE SUMAS Y SALDOS</h3>
    Del: <?=date($this->config->item('date_format'), $date_from)?> 
    Al: <?=date($this->config->item('date_format'), $date_to)?>
    <br/><h4>(Expresado en Bolivianos)</h4><br/>
</div>

<table class="table-balances" border="1">
    <thead>
        <tr>
            <th width="10%">Código</th>
            <th width="34%">Cuenta</th>
            <th width="14%">Debe</th>
            <th width="14%">Haber</th>
            <th width="14%">Saldo Debe</th>
            <th width="14%">Saldo Haber</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $total_debito = 0;
        $total_credito = 0;
        $total_saldo_debe = 0;
        $total_saldo_haber = 0;
        ?>
        
        <?php foreach($accounts as $account): ?>
        <?php
            $debito = isset($account->debit_amount) ? $account->debit_amount : 0;
            $credito = isset($account->credit_amount) ? $account->credit_amount : 0;
            
            $saldo_debe = $debito - $credito;
            $saldo_haber = $credito - $debito;
            
            $show_saldo_debe = $saldo_debe > 0 ? $saldo_debe : 0;
            $show_saldo_haber = $saldo_haber > 0 ? $saldo_haber : 0;
            
            $total_debito += $debito;
            $total_credito += $credito;
            $total_saldo_debe += $show_saldo_debe;
            $total_saldo_haber += $show_saldo_haber;
        ?>
        <tr>
            <td class="left-text"><?=isset($account->code_number) ? $account->code_number : ''?></td>
            <td class="left-text"><?=isset($account->account_name) ? $account->account_name : ''?></td>
            <td class="right-text no-wrap"><?=money($debito)?></td>
            <td class="right-text no-wrap"><?=money($credito)?></td>
            <td class="right-text no-wrap"><?=money($show_saldo_debe)?></td>
            <td class="right-text no-wrap"><?=money($show_saldo_haber)?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    
    <tfoot>
        <tr class="total-row">
            <td colspan="2" class="right-text">TOTALES</td>
            <td class="right-text no-wrap"><?=money($total_debito)?></td>
            <td class="right-text no-wrap"><?=money($total_credito)?></td>
            <td class="right-text no-wrap"><?=money($total_saldo_debe)?></td>
            <td class="right-text no-wrap"><?=money($total_saldo_haber)?></td>
        </tr>
    </tfoot>
</table>

<br><br>
<table style="width: 100%; margin-top: 60px; border: none;">
    <tr>
        <td style="width: 50%; text-align: center; border: none;">
            <div style="font-family: 'Courier New', monospace; font-size: 14px; margin-bottom: 15px;">________________________</div>
            <div style="font-size: 11px; font-weight: bold;">CONTADOR</div>
        </td>
        <td style="width: 50%; text-align: center; border: none;">
            <div style="font-family: 'Courier New', monospace; font-size: 14px; margin-bottom: 15px;">________________________</div>
            <div style="font-size: 11px; font-weight: bold;">GERENTE GENERAL</div>
        </td>
    </tr>
</table>