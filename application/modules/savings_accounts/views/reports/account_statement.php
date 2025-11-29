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
  <h3 class="title">Extracto de Caja de Ahorros (Estado de cuenta)</h3>
</div>

<div class="section">
  <?php
    $person_q       = $filters['person_q']       ?? '';
    $account_number = $filters['account_number'] ?? '';
    $date_from      = $filters['date_from']      ?? '';
    $date_to        = $filters['date_to']        ?? '';
  ?>

  <div class="card">
    <div class="card-block">
      <?= form_open(current_url(), ['method'=>'get','class'=>'form-horizontal']) ?>
        <div class="form-group">
          <label class="col-sm-2 control-label">Cliente</label>
          <div class="col-sm-4">
            <input
              type="text"
              id="client_search"
              class="form-control"
              placeholder="Escriba nombre del cliente"
              autocomplete="off"
            >
            <div id="client_results" class="list-group" style="position:absolute; z-index:1000; width:100%; display:none;"></div>
          </div>

          <label class="col-sm-2 control-label">N° de cuenta</label>
          <div class="col-sm-4">
            <input type="text"
                  name="account_number"
                  value="<?= html_escape($account_number) ?>"
                  class="form-control"
                  placeholder="Seleccione desde la lista de cuentas"
            >
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Desde</label>
          <div class="col-sm-2">
            <input type="date" name="date_from" value="<?= html_escape($date_from) ?>" class="form-control">
          </div>

          <label class="col-sm-2 control-label">Hasta</label>
          <div class="col-sm-2">
            <input type="date" name="date_to" value="<?= html_escape($date_to) ?>" class="form-control">
          </div>
        </div>

        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-10">
            <button class="btn btn-primary"><span class="glyphicon glyphicon-search"></span> Filtrar</button>
            <a class="btn btn-default" href="<?= site_url('savings_accounts/savings_account_reports/account_statement') ?>">Limpiar</a>
          </div>
        </div>
      <?= form_close(); ?>
    </div>

    <div class="card-block">
      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= html_escape($error) ?></div>
      <?php endif; ?>

      <?php if (!empty($accounts_list)): ?>
        <div class="title-block">
          <h4 class="title">Cuentas del cliente encontrado</h4>
        </div>

        <div class="inqbox-content table-responsive">
          <table class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>Cliente</th>
                <th>N° de cuenta</th>
                <th>Tipo de cuenta</th>
                <th>Estado</th>
                <th class="text-center">Acción</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($accounts_list as $a): ?>
                <?php
                  $full_name = trim($a->first_name.' '.$a->last_name);
                  // Mantener fechas al seleccionar cuenta
                  $qs = [
                    'person_q'       => $person_q,
                    'account_number' => $a->account_number,
                    'date_from'      => $date_from,
                    'date_to'        => $date_to,
                  ];
                  $url_view = site_url('savings_accounts/savings_account_reports/account_statement').'?'.http_build_query($qs);
                ?>
                <tr>
                  <td><?= html_escape($full_name) ?></td>
                  <td><?= html_escape($a->account_number) ?></td>
                  <td><?= html_escape($a->type_name) ?></td>
                  <td><?= (int)$a->status === 1 ? 'Activa' : 'Inactiva' ?></td>
                  <td class="text-center">
                    <a href="<?= $url_view ?>" class="btn btn-xs btn-primary">
                      Ver extracto
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>

      <?php if (!empty($header)): ?>
        <?php
          // Query string para exportar con los mismos filtros
          $qs = $this->input->get(NULL, TRUE);
          if (!is_array($qs)) { $qs = []; }
          $qs = array_filter($qs, function($v){ return $v !== '' && $v !== null; });
          $qs_str = http_build_query($qs);
          $pdf_url = site_url('savings_accounts/savings_account_reports/account_statement_export_pdf') . ($qs_str ? '?'.$qs_str : '');
        ?>

        <div class="row" style="margin-bottom:15px">
          <div class="col-sm-6">
            <p><strong>Titular:</strong> <?= html_escape($header->owner) ?></p>
            <p><strong>Producto:</strong> <?= html_escape($header->product) ?></p>
            <p><strong>Estado:</strong> <?= html_escape($header->status) ?></p>
          </div>
          <div class="col-sm-6 text-right">
            <p><strong>Cuenta:</strong> <?= html_escape($header->account_number) ?></p>
            <p><strong>Período:</strong> <?= html_escape($date_from) ?> al <?= html_escape($date_to) ?></p>
            <p><strong>Saldo inicial:</strong> <?= number_format((float)($totals['opening'] ?? 0), 2) ?></p>
          </div>
        </div>

        <div class="row" style="margin-bottom:10px">
          <div class="col-sm-12 text-right">
            <a href="<?= $pdf_url ?>" class="btn btn-default btn-sm" target="_blank">
              <span class="glyphicon glyphicon-print"></span> Imprimir / PDF
            </a>
          </div>
        </div>
    </div>


  </div>

    <div class="inqbox-content table-responsive">
      <table class="table table-striped table-bordered">
        <thead>
          <tr>
            <th style="width:140px">Fecha y hora</th>
            <th style="width:120px">Tipo</th>   <!-- NUEVO -->
            <th>Descripción de la transacción</th>
            <th style="width:150px" class="text-right">Monto</th>
            <th style="width:150px" class="text-right">Saldo</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rows)): ?>
            <tr>
              <td colspan="4" class="text-center">No hay movimientos en el período seleccionado.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($rows as $r): ?>
              <?php $amt = (float)$r->amount; ?>
              <tr>
                <td><?= date('d/m/Y H:i', strtotime($r->trans_date)) ?></td>
                <td><?= html_escape($r->type_label ?? $r->trans_type) ?></td>  <!-- NUEVO -->
                <td>
                  <?php
                    $desc = trim((string)($r->ui_description ?? ''));
                    if ($desc === '') { $desc = (string)($r->description ?? ''); }
                    echo nl2br(html_escape($desc));
                  ?>
                </td>
                <td class="text-right"><?= ($amt >= 0 ? '+' : '').number_format($amt, 2) ?></td>
                <td class="text-right"><?= number_format((float)$r->balance, 2) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
        <?php if (!empty($rows)): ?>
        <tfoot>
          <tr>
            <th colspan="2" class="text-right">Totales</th>
            <th class="text-right">
              <?= number_format((float)($totals['credit'] ?? 0) - (float)($totals['debit'] ?? 0), 2) ?>
            </th>
            <th class="text-right">
              <?= number_format((float)($totals['closing'] ?? 0), 2) ?>
            </th>
          </tr>
        </tfoot>
        <?php endif; ?>
      </table>
    </div>
  <?php endif; ?>

<script>
  const accounts = <?= json_encode($account_options) ?>;

  const input = document.getElementById('client_search');
  const results = document.getElementById('client_results');
  const accountField = document.querySelector('input[name="account_number"]');

  input.addEventListener('input', function() {
    const q = this.value.trim().toLowerCase();
    if (!q) { results.style.display = 'none'; return; }

    let html = '';
    for (const [acc_id, label] of Object.entries(accounts)) {
      if (label.toLowerCase().includes(q)) {
        html += `
          <a href="#" class="list-group-item list-group-item-action" 
             data-id="${acc_id}" data-label="${label}">
             ${label}
          </a>`;
      }
    }

    if (html === '') { results.style.display = 'none'; return; }

    results.innerHTML = html;
    results.style.display = 'block';
  });

  results.addEventListener('click', function(e) {
  e.preventDefault();

  const link = e.target.closest('a[data-id]');
  if (!link) return;

  const accId = link.dataset.id;
  const label = link.dataset.label;

  input.value = label;

  const match = label.match(/\[(.*?)\]/);
  if (match) {
    accountField.value = match[1];
  }

  results.style.display = 'none';
});

</script>

</div>

<?php $this->load->view('partial/footer'); ?>
