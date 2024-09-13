<style>
    #tbl-financial-income td:nth-child(2) {
        text-align: right;
        padding-right:5px;
    }
</style>

<div style="text-align:center">
    <h3>Ingresos Financieros</h3>
    Fechado: <?=date($this->config->item('date_format'), $date_from) . " - " . date($this->config->item('date_format'), $date_to)?>
    <br/>
    <br/>
</div>

<table width="100%" cellpadding="1" cellspacing="0" border="1" id="tbl-financial-income">
    <tr>
        <td><b>INGRESO FINANCIERO</b></td>
        <td></td>
    </tr>
    <?php 
        $total_income = $interest_on_current;
        $total_expenses = 0; 
    ?>
    <tr>
        <td>Intereses sobre Prestamos Vigentes y Vencidos</td>
        <td><?=to_currency($interest_on_current);?></td>
    </tr>
    <?php foreach ($accounts as $account): ?>
        <?php if ($account->account_type == 'expenses') continue; ?>
        <tr>
            <td><?= $account->account_name; ?></td>
            <td><?= to_currency($account->amount); ?></td>
        </tr>
        <?php $total_income += $account->amount; ?>
    <?php endforeach; ?>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td><b>TOTAL INGRESO FINANCIERO</b></td>
        <td><b><?= to_currency($total_income); ?></b></td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td><b>GASTOS OPERACIONALES</b></td>
        <td></td>
    </tr>
    <?php foreach ($accounts as $account): ?>
        <?php if ($account->account_type == 'income') continue; ?>
        <tr>
            <td><?= $account->account_name; ?></td>
            <td><?= to_currency($account->amount); ?></td>
        </tr>
        <?php $total_expenses += $account->amount; ?>
    <?php endforeach; ?>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td><b>TOTAL GASTOS OPERACIONALES</b></td>
        <td><b><?=to_currency($total_expenses);?></b></td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td><b>UTILIDAD NETA DE OPERACION</b></td>
        <td><b><?=to_currency($total_income - $total_expenses);?></b></td>
    </tr>
</table>