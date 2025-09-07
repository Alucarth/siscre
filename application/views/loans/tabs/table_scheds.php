<table class="table table-bordered">
    <thead>
        <tr>
            <th style="text-align: center">Date</th>
            <th style="text-align: center">Grace Period</th>
            <th style="text-align: center">Amount to Pay</th>
            <th style="text-align: center">Penalty</th>
            <th style="text-align: center">Principal Amount</th>
            <th style="text-align: center">Interest (<?=$this->config->item("currency_symbol");?>)</th>
            <th style="text-align: center">Balance</th>
        </tr>
    </thead>    
    <tbody>
        <?php foreach( $scheds as $sched ): ?>
            <tr>
                <td style="text-align: center;"><?=$sched["payment_date"];?></td>
                <td style="text-align: center;"><?=isset($sched["grace_period"]) ? $sched["grace_period"] : '';?></td>
                <td style="text-align: right;"><?=to_currency($sched["payment_amount"]);?></td>
                <td style="text-align: right;"><?=to_currency($sched["penalty_amount"]);?></td>
                <td style="text-align: right;"><?=to_currency($sched["payment_amount"] - $sched["interest"]); ?></td>
                <td style="text-align: right;"><?=to_currency($sched["interest"])?></td>
                <td style="text-align: right;"><?=to_currency($sched["payment_balance"])?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>