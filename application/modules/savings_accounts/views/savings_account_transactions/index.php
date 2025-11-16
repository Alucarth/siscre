<?php $this->load->view('partial/header'); ?>

<div class="title-block">
  <h3 class="title">Transacciones de Cajas de Ahorro</h3>
  <div class="clearfix"></div>
</div>

<div class="section">
  <!-- Filtros -->
  <div class="card">
    <div class="form-group">
      <a href="<?= site_url('savings_accounts/savings_account_transactions/form') ?>" class="btn btn-success">
        <span class="glyphicon glyphicon-plus"></span> Nueva transacción
      </a>
    </div>
    
    <div class="card-block">
      
      <?= form_open(current_url(), ['method'=>'get','class'=>'form-horizontal']) ?>

        <div class="form-group row">
          <?= form_label('Cuenta:','account_id',['class'=>'col-sm-1 control-label']) ?>
          <div class="col-sm-5">
            <?= form_dropdown('account_id', $account_options, $filters['account_id'] ?? '', 'class="form-control"') ?>
          </div>
          <?= form_label('Tipo:','trans_type',['class'=>'col-sm-1 control-label']) ?>
          <div class="col-sm-2">
            <?= form_dropdown('trans_type', $type_options, $filters['trans_type'] ?? '', 'class="form-control"') ?>
          </div>
          <div class="col-sm-3"></div>
        </div>

        <div class="form-group row">
          <?= form_label('Desde:','date_from',['class'=>'col-sm-1 control-label']) ?>
          <div class="col-sm-2">
            <input type="date" name="date_from" value="<?= html_escape($filters['date_from'] ?? '') ?>" class="form-control">
          </div>

          <?= form_label('Hasta:','date_to',['class'=>'col-sm-1 control-label']) ?>
          <div class="col-sm-2">
            <input type="date" name="date_to" value="<?= html_escape($filters['date_to'] ?? '') ?>" class="form-control">
          </div>

          <?= form_label('Sucursal:','branch_id',['class'=>'col-sm-1 control-label']) ?>
          <div class="col-sm-2">
            <?= form_dropdown('branch_id', $branch_options, $filters['branch_id'] ?? '', 'class="form-control"') ?>
          </div>

          <div class="col-sm-3"></div>
        </div>

        <div class="form-group row">
          <?= form_label('Operador:','registered_by',['class'=>'col-sm-1 control-label']) ?>
          <div class="col-sm-2">
            <?= form_dropdown('registered_by', $operator_options, $filters['registered_by'] ?? '', 'class="form-control"') ?>
          </div>

          <?= form_label('Estado:','status',['class'=>'col-sm-1 control-label']) ?>
          <div class="col-sm-2">
            <?= form_dropdown('status', $status_options, $filters['status'] ?? '', 'class="form-control"') ?>
          </div>

          <?= form_label('Buscar:','q',['class'=>'col-sm-1 control-label']) ?>
          <div class="col-sm-2">
            <input type="text" name="q" value="<?= html_escape($filters['q'] ?? '') ?>" class="form-control" placeholder="Nombre, cuenta o ID">
          </div>

          <div class="col-sm-3"></div>
        </div>
        <div class="form-group col-sm-2">
          <button class="btn btn-primary"><span class="glyphicon glyphicon-search"></span> Filtrar</button>
          <a href="<?= site_url('savings_accounts/savings_account_transactions') ?>" class="btn btn-default">Limpiar</a>
        </div>

        <div class="form-group">
          <?= form_label('Mostrar','limit',['class'=>'col-sm-2 control-label']) ?>
          <div class="row">
            <div class="col-sm-2">
              <select name="limit" class="form-control">
                <?php foreach ([25,50,100,200] as $opt): ?>
                  <option value="<?= $opt ?>" <?= (int)$limit===$opt?'selected':'' ?>><?= $opt ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          
            <div class="col-sm-8">
              <a href="<?= $export_csv_url ?>" class="btn btn-default">
                <span class="glyphicon glyphicon-download"></span> Exportar CSV
             </a>
              <a href="<?= $export_pdf_url ?>" target="_blank" class="btn btn-default">
                <span class="glyphicon glyphicon-print"></span> Exportar PDF
              </a>
            </div>
          </div>
        </div>

      <?= form_close() ?>
      <?php $pdf_url = $this->session->flashdata('pdf_url'); ?>
      <?php if (!empty($pdf_url)): ?>
      <script>
        (function(){
          try { window.open('<?= $pdf_url ?>','_blank'); } catch(e){}
        })();
      </script>
      <?php endif; ?>

    </div>
  </div>

  <br>

  <!-- Tabla -->
  <div class="inqbox-content table-responsive">
    <table class="table table-striped table-bordered">
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Cuenta</th>
          <th>Cliente</th>
          <th>Tipo</th>
          <th class="text-right">Monto</th>
          <th class="text-right">Saldo Resultante</th>
          <th>Descripción</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
      <?php
        // Copiamos seeds para ir “consumiendo” saldo por cuenta
        $running = isset($running_seeds) ? $running_seeds : [];

        $tot_deposit  = 0.0;
        $tot_withdraw = 0.0;

        foreach($transactions as $t):
            $aid = (int)$t->savings_account_id;

            // Saldo resultante después de ESTA transacción (lista DESC)
            $saldo_resultante = isset($running[$aid]) ? $running[$aid] : 0.0;

            // Delta de esta transacción
            $delta = ($t->trans_type === 'deposit') ? +$t->amount : -$t->amount;

            if ($t->trans_type === 'deposit')  $tot_deposit  += (float)$t->amount;
            if ($t->trans_type !== 'deposit')  $tot_withdraw += (float)$t->amount;
      ?>
        <tr>
          <td><?= date('d/m/Y H:i', strtotime($t->trans_date)) ?></td>
          <td><?= htmlspecialchars($t->account_number) ?></td>
          <td><?= htmlspecialchars(trim(($t->first_name ?? '').' '.($t->last_name ?? ''))) ?></td>
          <td><?= htmlspecialchars($t->trans_type_label ?? ucfirst($t->trans_type)) ?></td>
          <td class="text-right"><?= number_format($t->amount,2) ?></td>
          <td class="text-right">
            <?= isset($t->running_balance)
                  ? number_format((float)$t->running_balance, 2)
                  : '—' ?>
          </td>
          <td><?= htmlspecialchars((string)$t->description) ?></td>
          <td>
            <a href="javascript:void(0)" onclick="openTxPreview(<?= (int)$t->transaction_id ?>)" class="btn btn-xs btn-success" title="Ver voucher">
              <span class="fa fa-eye"></span>
            </a>
            <a href="<?= site_url('savings_accounts/savings_account_transactions/voucher/'.$t->transaction_id) ?>" class="btn btn-xs btn-info" target="_blank" title="Imprimir voucher">
              <span class="fa fa-print"></span>
            </a>
            <a href="<?= site_url("savings_accounts/savings_account_transactions/delete/{$t->transaction_id}") ?>" class="btn btn-xs btn-danger" onclick="return confirm('¿Deshabilitar esta transacción?');" title="Deshabilitar">🗑</a>
          </td>
        </tr>
      <?php
            // Retrocedemos al saldo “anterior” para la siguiente fila (más antigua)
            $running[$aid] = $saldo_resultante - $delta;
        endforeach;
      ?>
      </tbody>

      <?php
        // si sólo hay una cuenta en la página, mostramos su saldo actual
        $saldo_actual_unica = null;
        if (!empty($running_seeds) && count($running_seeds) === 1) {
            $saldo_actual_unica = reset($running_seeds);
        }
      ?>
      <tfoot>
        <tr class="active">
          <td colspan="5" class="text-right"><strong>Totales (página):</strong></td>
          <td colspan="3">
            <strong>Depósitos: <?= number_format((float)($page_totals['deposit'] ?? 0), 2) ?></strong>
            &nbsp;&nbsp;
            <strong>Retiros: <?= number_format((float)($page_totals['withdraw'] ?? 0), 2) ?></strong>
          </td>
        </tr>
      </tfoot>
    </table>

    <?php if (!empty($filters['account_id']) && !empty($filters['date_from']) && !empty($filters['date_to'])): ?>
      <div class="alert alert-info" style="margin-top:10px">
        <strong>Resumen del período</strong><br>
        Saldo inicial al <?= html_escape($filters['date_from']) ?>:
          <strong><?= number_format((float)($opening_balance ?? 0),2) ?></strong><br>
        Depósitos: <strong><?= number_format((float)($period_totals['deposit'] ?? 0),2) ?></strong>  ·
        Retiros: <strong><?= number_format((float)($period_totals['withdraw'] ?? 0),2) ?></strong>  ·
        Neto: <strong><?= number_format((float)(($period_totals['deposit'] ?? 0) - ($period_totals['withdraw'] ?? 0)),2) ?></strong><br>
        <?php if (isset($period_interest)): ?>
          Interés estimado del período: <strong><?= number_format((float)$period_interest,2) ?></strong><br>
        <?php endif; ?>
        Saldo final al <?= html_escape($filters['date_to']) ?>:
          <strong><?= number_format((float)($closing_balance ?? 0),2) ?></strong>
      </div>
    <?php endif; ?>

    <div class="row">
      <div class="col-sm-6">
        <p>Mostrando <?= (int)$from_row ?>–<?= (int)$to_row ?> de <?= (int)$total_rows ?> registros</p>
      </div>
      <div class="col-sm-6 text-right">
        <?= $pagination_links ?>
      </div>
    </div>
  </div>
</div>

<?php
// Estos valores los setea el controlador al guardar:
$print_tx_id     = $this->session->flashdata('print_tx_id');      // depósito/retiro (un solo ID)
$print_transfer  = $this->session->flashdata('print_transfer');   // transferencia (array con 2 IDs)
?>

<?php if (!empty($print_tx_id)): ?>
<script>
  window.addEventListener('load', function () {
    var url = '<?= site_url('savings_accounts/savings_account_transactions/voucher') ?>' + '/<?= (int)$print_tx_id ?>';
    window.open(url, '_blank');
  });
</script>
<?php endif; ?>

<?php if (is_array($print_transfer) && !empty($print_transfer['withdraw_id']) && !empty($print_transfer['deposit_id'])): ?>
<script>
  window.addEventListener('load', function () {
    var url = '<?= site_url('savings_accounts/savings_account_transactions/voucher_transfer') ?>'
            + '/<?= (int)$print_transfer['withdraw_id'] ?>'
            + '/<?= (int)$print_transfer['deposit_id'] ?>';
    window.open(url, '_blank');
  });
</script>
<?php endif; ?>

<!-- ============ Modal Voucher (Listado) ============ -->
<div id="txPreviewModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="txPreviewTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document" style="max-width:940px;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="txPreviewTitle">Comprobante de transacción</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body" style="background:#f8f8f8;">
        <div style="background:#fff; padding:10px; border:1px solid #ddd;">
          <iframe id="txPreviewFrame"
                  title="Voucher preview"
                  style="width:100%; height:40vh; border:0; display:block;"></iframe>
        </div>
      </div>

      <div class="modal-footer">
        <a id="txPrintBtn" href="#" target="_blank" rel="noopener" class="btn btn-primary">
          <span class="glyphicon glyphicon-print"></span> Imprimir
        </a>
        <!-- Si luego quieres PDF directo, lo mostramos; por ahora oculto -->
        <a id="txPdfBtn" href="#" target="_blank" rel="noopener" class="btn btn-default" style="display:none">
          <span class="glyphicon glyphicon-download"></span> PDF
        </a>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  window.openTxPreview = function(txId){
    var previewUrl = '<?= site_url('savings_accounts/savings_account_transactions/voucher_preview') ?>/' + txId;
    var printUrl   = '<?= site_url('savings_accounts/savings_account_transactions/voucher') ?>/' + txId;

    // Botón imprimir → al voucher real (PDF por mPDF en una pestaña nueva)
    var printBtn = document.getElementById('txPrintBtn');
    if (printBtn) printBtn.href = printUrl;

    // Cargar la preview directamente en el iframe
    var frame = document.getElementById('txPreviewFrame');
    if (frame) frame.src = previewUrl;

    // Mostrar modal
    $('#txPreviewModal').modal('show');
  };
})();
</script>


<?php $this->load->view('partial/footer'); ?>
