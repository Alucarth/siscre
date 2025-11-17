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

  <form method="get" class="form-inline" style="margin-bottom:10px">
    <label>Fecha:&nbsp;</label>
    <input type="date" name="date" value="<?= html_escape($date) ?>" class="form-control input-sm">

    &nbsp;&nbsp;
    <label>Sucursal:&nbsp;</label>
    <select name="branch_id" class="form-control input-sm">
      <option value="">Todas</option>
      <?php foreach ($branch_options as $id => $name): ?>
        <option value="<?= html_escape($id) ?>" <?= ((string)$branch_id === (string)$id) ? 'selected' : '' ?>>
          <?= html_escape($name) ?>
        </option>
      <?php endforeach; ?>
    </select>

    &nbsp;&nbsp;
    <button class="btn btn-primary btn-sm">Aplicar</button>
    <?php // Mantén aquí tus botones de Exportar CSV/PDF si ya los tienes ?>
  </form>

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

