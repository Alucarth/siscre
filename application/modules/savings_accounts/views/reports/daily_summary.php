<?php $this->load->view('partial/header'); ?>

<div class="tabs-container">
    <ul class="nav nav-tabs nav-tabs-bordered">
        <li class="nav-item">
            <a class="nav-link <?= ($active_tab === 'daily' ? 'active' : '') ?>"
               href="<?= site_url('savings_accounts/savings_account_reports/daily_summary') ?>">
                Resumen diario
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($active_tab === 'interest' ? 'active' : '') ?>"
               href="<?= site_url('savings_accounts/savings_account_reports/interest_summary') ?>">
                Intereses
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($active_tab === 'statement' ? 'active' : '') ?>"
               href="<?= site_url('savings_accounts/savings_account_reports/account_statement') ?>">
                Estado de cuenta
            </a>
        </li>
    </ul>
</div>
<br>
<div class="title-block">
  <h3 class="title">Resumen diario de movimientos</h3>
</div>

<div class="section">
  <?php
    // Usar los filtros entregados por el controlador (coherente con daily_summary())
    $date      = $filters['date']      ?? date('Y-m-d');
    $branch_id = $filters['branch_id'] ?? '';

    // $branch_options llega como [id => branch_name]
    $branch_options = $branch_options ?? [];
  ?>
  <div class="section">
    <div class="card card-block">
      <?= form_open(current_url(), ['method'=>'get','class'=>'form-horizontal']) ?>
        <div class="form-group">
          <?= form_label('Fecha','date',['class'=>'col-sm-2 control-label']) ?>
          <div class="col-sm-2">
            <input type="date" name="date" value="<?= html_escape($filters['date'] ?? ($date ?? '')) ?>" class="form-control">
          </div>

          <?= form_label('Sucursal','branch_id',['class'=>'col-sm-2 control-label']) ?>
          <div class="col-sm-3">
            <?= form_dropdown('branch_id', $branch_options, $filters['branch_id'] ?? ($branch_id ?? ''), 'class="form-control"') ?>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-10">
            <button class="btn btn-primary"><span class="glyphicon glyphicon-search"></span>Filtrar</button>
            <a class="btn btn-default" href="<?= site_url('savings_accounts/savings_account_reports/daily_summary') ?>">Limpiar</a>
          </div>
        </div>
      </div>
      <?= form_close(); ?>
    </div>
  </div>

    <div class="inqbox-content table-responsive">
      <?php
        // Construir QS preservando filtros; si usas PHP < 7.4 y la arrow fn no te gusta, reemplázala por un foreach
        $qs = $this->input->get(NULL, TRUE);
        if (!is_array($qs)) { $qs = []; }
        $qs = array_filter($qs, function($v){ return $v !== '' && $v !== null; });
        $qs_str = http_build_query($qs);

        // Corregir path del controlador a savings_account_reports (singular)
        $csv_url = site_url('savings_accounts/savings_account_reports/daily_summary_export_csv') . ($qs_str ? '?'.$qs_str : '');
        $pdf_url = site_url('savings_accounts/savings_account_reports/daily_summary_export_pdf') . ($qs_str ? '?'.$qs_str : '');
      ?>
      <div class="text-right" style="margin:10px 0;">
          <a class="btn btn-default" href="<?= $csv_url ?>"><span class="glyphicon glyphicon-download"></span> Exportar CSV</a>
          <a class="btn btn-default" href="<?= $pdf_url ?>" target="_blank"><span class="glyphicon glyphicon-print"></span> Exportar PDF</a>
      </div>

    <table class="table table-striped table-bordered">
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Sucursal</th>
          <th class="text-right">Depósitos</th>
          <th class="text-right">Retiros</th>
          <th class="text-right">Transferencias</th>
          <th class="text-right">Neto de caja</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <?php
            // Obtener nombre de sucursal a partir del id (si no existe, mostrar id)
            $branch_label = isset($branch_options[$r->branch_id]) ? $branch_options[$r->branch_id] : (string)$r->branch_id;
          ?>
          <tr>
            <td><?= date('d/m/Y', strtotime($r->tx_date)) ?></td>
            <td><?= html_escape($branch_label) ?></td>
            <td class="text-right"><?= number_format((float)$r->deposits, 2) ?></td>
            <td class="text-right"><?= number_format((float)$r->withdraws, 2) ?></td>
            <td class="text-right"><?= number_format((float)$r->transfers, 2) ?></td>
            <td class="text-right"><strong><?= number_format((float)$r->net_cash, 2) ?></strong></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <th colspan="2" class="text-right">Totales</th>
          <th class="text-right"><?= number_format($totals['deposits'], 2) ?></th>
          <th class="text-right"><?= number_format($totals['withdraws'], 2) ?></th>
          <th class="text-right"><?= number_format($totals['transfers'], 2) ?></th>
          <th class="text-right"><?= number_format($totals['net_cash'], 2) ?></th>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<?php $this->load->view('partial/footer'); ?>

