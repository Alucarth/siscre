<table class="table table-bordered">
    <thead>
        <tr>
            <th style="text-align: center">Fecha</th>
            <th style="text-align: center">Periodo de gracia</th>
            <th style="text-align: center">Monto a pagar</th>
            <th style="text-align: center">Penalidad</th>
            <th style="text-align: center">Capital</th>
            <th style="text-align: center">Interés (<?=$this->config->item("currency_symbol");?>)</th>
            <th style="text-align: center">Ahorro</th>
            <th style="text-align: center">Balance</th>
            <th style="text-align: center">Estado</th>
        </tr>
    </thead>    
    <tbody>
        <?php foreach( $scheds as $sched ): ?>
        
            <?php

                $payment_date = strtotime($this->config->item('date_format') == 'd/m/Y' ? uk_to_isodate(strip_tags($sched["payment_date"])) : strip_tags($sched["payment_date"]));

                $status = '';
                if ( in_array($payment_date, $due_date_paids) )
                {
                    $status = 'Paid';
                }

            ?>
        
        
            <tr>
                <td style="text-align: center;"><?=$sched["payment_date"];?></td>
                <td style="text-align: center;"><?=isset($sched["grace_period"]) ? $sched["grace_period"] : '';?></td>
                <td style="text-align: right;"><?=to_currency($sched["payment_amount"]);?></td>
                <td style="text-align: right;"><?=to_currency($sched["penalty_amount"]);?></td>
                <!--<td style="text-align: right;"><?=to_currency($sched["payment_amount"] - $sched["interest"]); ?></td>-->
                <td style="text-align: right;"><?=to_currency($sched["payment_amount"] - $sched["interest"] -$sched["operating_expenses_amount"]); ?></td>
                <td style="text-align: right;"><?=to_currency($sched["interest"])?></td>
                <td style="text-align: right;"><?=to_currency($sched["operating_expenses_amount"])?></td>
                <td style="text-align: right;"><?=to_currency($sched["payment_balance"])?></td>
                <td style="text-align: right;"><?=$status;?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="row">
    <div class="col-lg-6"><strong>Balance de préstamo:</strong></div>
    <div class="col-lg-6">
        <span class="pull-right"><?= to_currency($loan_balance)?></span>
    </div>
</div>