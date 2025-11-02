<div class="table-responsive">
    <div class="row">
        <div class="col-md-12">
            <h4 class="text-center">LIBRO MAYOR</h4>
            <p class="text-center">
                <strong>Del <?= $date_from ?> al <?= $date_to ?></strong>
                <?php if(!empty($selected_account_info)): ?>
                    <br><strong>Cuenta: <?= $selected_account_info->code_number ?> - <?= $selected_account_info->account_name ?></strong>
                <?php else: ?>
                    <br><strong>Todas las cuentas</strong>
                <?php endif; ?>
                <?php if(!empty($selected_branch_info)): ?>
                    <br><strong>Sucursal: <?= $selected_branch_info->branch_name ?></strong>
                <?php else: ?>
                    <br><strong>Todas las sucursales</strong>
                <?php endif; ?>
                <?php if(!empty($totals) && $totals->total_transactions > 0): ?>
                    - <strong><?= $totals->total_transactions ?></strong> transacciones encontradas
                <?php endif; ?>
            </p>
        </div>
    </div>
    
    <table class="table table-bordered table-striped" id="tbl-general-book" style="font-size: 12px;">
        <thead class="thead-dark">
            <tr>
                <th width="5%" class="text-center">N°</th>
                <th width="8%" class="text-center">N° Transacción</th>
                <th width="10%" class="text-center">Fecha</th>
                <th width="17%" class="text-center">Razón Social</th>
                <th width="35%" class="text-center">Glosa</th>
                <th width="10%" class="text-center">Debe</th>
                <th width="10%" class="text-center">Haber</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if(!empty($transactions)) {
                $counter = 1;
                foreach($transactions as $transaction): 
            ?>
                <tr>
                    <td class="text-center"><?= $counter ?></td>
                    <td class="text-center"><?= $transaction->transaction_id ?></td>
                    <td class="text-center"><?= date('d/m/Y', strtotime($transaction->added_date)) ?></td>
                    <td class="text-center">CREDISURGIR S.R.L.</td>
                    <td><?= $transaction->description ?: 'Sin descripción' ?></td>
                    <td class="text-right"><?= $transaction->movement_type == 'debit' ? number_format($transaction->amount, 2) : '0.00' ?></td>
                    <td class="text-right"><?= $transaction->movement_type == 'credit' ? number_format($transaction->amount, 2) : '0.00' ?></td>
                </tr>
            <?php 
                    $counter++;
                endforeach; 
            } else {
                echo '<tr><td colspan="7" class="text-center">No se encontraron transacciones para el período seleccionado</td></tr>';
            }
            ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td colspan="5" class="text-right"><strong>TOTALES:</strong></td>
                <td class="text-right"><?= number_format($total_debit, 2) ?></td>
                <td class="text-right"><?= number_format($total_credit, 2) ?></td>
            </tr>
        </tfoot>
    </table>
</div>

<?php if(!empty($transactions)): ?>
<script>
    $(document).ready(function() {
        $('#tbl-general-book').DataTable({
            dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>t<"row"<"col-sm-6"i><"col-sm-6"p>>',
            pageLength: 100,
            lengthMenu: [[10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, "Todos"]],
            responsive: true,
            order: [[0, 'asc']],
            language: {
                url: '<?= base_url() ?>assets/js/plugins/dataTables/Spanish.json'
            },
            columnDefs: [
                { 
                    targets: [0, 1, 2, 5, 6], 
                    className: 'text-center' 
                },
                { 
                    targets: [5, 6], 
                    className: 'text-right' 
                }
            ]
        });
    });
</script>
<style>
    #tbl-general-book th {
        background-color: #ffffffff;
        color: black;
        font-weight: bold;
        
    }
    #tbl-general-book tfoot tr:first-child td {
        background-color: #e9ecef;
        font-weight: bold;
    }
</style>
<?php endif; ?>