<?php $this->load->view('partial/header'); ?>
<?php $active_tab = isset($active_tab) ? $active_tab : ''; ?>

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
  <h3 class="title">Intereses por cuenta (período)</h3>
</div>

<div class="section">
  <?= form_open(current_url(), ['method'=>'get','class'=>'form-horizontal']) ?>
    <div class="form-group">
      <?= form_label('Desde','date_from',['class'=>'col-sm-2 control-label']) ?>
      <div class="col-sm-2"><input type="date" name="date_from" value="<?= html_escape($filters['date_from'] ?? '') ?>" class="form-control"></div>

      <?= form_label('Hasta','date_to',['class'=>'col-sm-1 control-label']) ?>
      <div class="col-sm-2"><input type="date" name="date_to" value="<?= html_escape($filters['date_to'] ?? '') ?>" class="form-control"></div>

      <?= form_label('Sucursal','branch_id',['class'=>'col-sm-2 control-label']) ?>
      <div class="col-sm-3"><?= form_dropdown('branch_id', $branch_options, $filters['branch_id'] ?? '', 'class="form-control"') ?></div>
    </div>

    <div class="form-group">
      <div class="col-sm-offset-2 col-sm-10">
        <button class="btn btn-primary"><span class="glyphicon glyphicon-search"></span> Filtrar</button>
        <a class="btn btn-default" href="<?= site_url('savings_accounts/savings_account_reports/interest_summary') ?>">Limpiar</a>
      </div>
    </div>
  <?= form_close(); ?>

  <div class="inqbox-content table-responsive">
    <?php
        $qs = $this->input->get(NULL, TRUE);
        $qs_str = http_build_query(array_filter($qs, fn($v) => $v !== '' && $v !== null));
        $csv_url = site_url('savings_accounts/savings_account_reports/interest_summary_export_csv') . ($qs_str ? '?'.$qs_str : '');
        $pdf_url = site_url('savings_accounts/savings_account_reports/interest_summary_export_pdf') . ($qs_str ? '?'.$qs_str : '');
    ?>
    <div class="text-right" style="margin:10px 0;">
        <a class="btn btn-default" href="<?= $csv_url ?>"><span class="glyphicon glyphicon-download"></span> Exportar CSV</a>
        <a class="btn btn-default" href="<?= $pdf_url ?>" target="_blank"><span class="glyphicon glyphicon-print"></span> Exportar PDF</a>
    </div>

    <table class="table table-striped table-bordered">
      <thead>
        <tr>
          <th>Cuenta</th>
          <th>Cliente</th>
          <th>Tipo</th>
          <th class="text-right">APY</th>
          <th class="text-right">Interés (período)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r->account_number) ?></td>
            <td><?= htmlspecialchars($r->owner) ?></td>
            <td><?= htmlspecialchars($r->type_name) ?></td>
            <td class="text-right"><?= number_format((float)$r->apy * 100, 2) ?>%</td>
            <td class="text-right"><strong><?= number_format((float)$r->interest, 2) ?></strong></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <th colspan="4" class="text-right">Total intereses</th>
          <th class="text-right"><?= number_format((float)$total_interest, 2) ?></th>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<?php $this->load->view('partial/footer'); ?>
