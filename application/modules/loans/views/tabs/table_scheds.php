<table class="table table-bordered">
    <thead>
        <tr>
            <th style="text-align: center">Fecha</th>
            <th style="text-align: center">Monto a pagar</th>
            <th style="text-align: center">Capital</th>
            <th style="text-align: center">Interés (<?=$this->config->item("currency_symbol");?>)</th>
            <th style="text-align: center">Ahorro</th>
            <th style="text-align: center">Saldo</th>
            
        </tr>
    </thead>    
    <tbody>
        <?php foreach( $scheds as $sched ): ?>
            <tr>
                <td style="text-align: center;"><?=$sched["payment_date"];?></td>
                <td style="text-align: right;"><?=to_currency($sched["payment_amount"]);?></td>
                <td style="text-align: right;"><?=to_currency($sched["payment_amount_capital"]); ?></td>
                <td style="text-align: right;"><?=to_currency($sched["interest"])?></td>
                <td style="text-align: right;"><?=to_currency($sched["operating_expenses_amount"]); ?></td>
                <td style="text-align: right;"><?=to_currency($sched["payment_balance"])?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>