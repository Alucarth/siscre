<style>
    .tbl-ledger th {
        text-align: center;
    }
    .tbl-ledger-head td {
        padding:8px;        
    }
    .tbl-ledger td {
        width:20%;        
    }

    .tbl-ledger td:nth-child(1), 
    .tbl-ledger td:nth-child(3), 
    .tbl-ledger td:nth-child(4), 
    .tbl-ledger td:nth-child(5) 
    {
        text-align: center;
    }

    .tbl-ledger-head td:nth-child(1), 
    .tbl-ledger-head td:nth-child(3) 
    {
        width:150px;
        white-space: nowrap;
    }
    .tbl-ledger-head td:nth-child(2)
    {
        width:450px;
    }
    .tbl-ledger-head td:nth-child(2), 
    .tbl-ledger-head td:nth-child(4) 
    {
        text-align: left;
        white-space: nowrap;
    }
</style>

<div style="max-height: 450px; overflow: auto">

    <?php foreach ($accounting_transactions as $row): ?>

        <table style="width:100%" class="tbl-ledger-head">
            <tr>
                <td><b>Nombre de la Cuenta:</b></td>
                <td><?= $row->account_name; ?></td>
                <td><b>Numero de Cuenta:</b></td>
                <td><?= $row->account_number ?></td>
            </tr>
        </table>

        <table class="table table-bordered tbl-ledger">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Descripcion</th>
                    <th>Debito</th>
                    <th>Credito</th>
                    <th>Saldo</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= $row->date; ?></td>
                    <td></td>
                    <td><?= to_currency($row->debit); ?></td>
                    <td><?= to_currency($row->credit); ?></td>
                    <td><?= to_currency($row->balance); ?></td>
                </tr>
            </tbody>
        </table>

    <?php endforeach; ?>

    <?php foreach ($account_transactions as $row): ?>

        <table style="width:100%" class="tbl-ledger-head">
            <tr>
                <td><b>Nombre de Cuenta:</b></td>
                <td><?= $row->account_name; ?></td>
                <td><b>Numero de Cuenta:</b></td>
                <td><?= $row->account_number; ?></td>
            </tr>
        </table>

        <table class="table table-bordered tbl-ledger">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Descripcion</th>
                    <th>Debito</th>
                    <th>Credito</th>
                    <th>Saldo</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= $row->date; ?></td>
                    <td></td>
                    <td><?= to_currency($row->debit); ?></td>
                    <td><?= to_currency($row->credit); ?></td>
                    <td><?= to_currency($row->balance); ?></td>
                </tr>
            </tbody>
        </table>

    <?php endforeach; ?>


    <table style="width:100%" class="tbl-ledger-head">
        <tr>
            <td><b>Nombre de Cuenta:</b></td>
            <td>Prestamos</td>
            <td><b>Numero de Cuenta:</b></td>
            <td>1101</td>
        </tr>
    </table>

    <table class="table table-bordered tbl-ledger">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Descripcion</th>
                <th>Debito</th>
                <th>Credito</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($loan_transactions as $row): ?>
                <tr>
                    <td><?= $row->date ?></td>
                    <td><?= $row->explanation ?></td>
                    <td><?= to_currency($row->debit); ?></td>
                    <td>&nbsp;</td>
                    <td><?= to_currency($row->debit); ?></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>Repayments</td>
                    <td>&nbsp;</td>
                    <td><?= to_currency($row->credit); ?></td>
                    <td><?= to_currency($row->debit - $row->credit); ?></td>
                </tr>                
            <?php endforeach; ?>
                
            <?php if ( count($loan_transactions) <= 0 ): ?>
                <tr>
                    <td colspan="5" style="text-align:center">No se encontraron registros.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <table style="width:100%" class="tbl-ledger-head">
        <tr>
            <td><b>Nombre de Cuenta:</b></td>
            <td>Intereses de prestamos</td>
            <td><b>Numero de Cuenta:</b></td>
            <td>4001</td>
        </tr>
    </table>

    <table class="table table-bordered tbl-ledger">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Descripcion</th>
                <th>Debito</th>
                <th>Credito</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($loan_interest_transactions as $row): ?>

                <?php $balance = $row->credit - $row->debit; ?>

                <tr>
                    <td><?= $row->date; ?></td>
                    <td><?= $row->explanation; ?></td>
                    <td><?= to_currency($row->debit); ?></td>
                    <td><?= to_currency($row->credit); ?></td>
                    <td><?= to_currency($balance); ?></td>
                </tr>            
            <?php endforeach; ?>
                
            <?php if ( count($loan_interest_transactions) <= 0 ): ?>
                <tr>
                    <td colspan="5" style="text-align:center">No se encontraron registros.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>