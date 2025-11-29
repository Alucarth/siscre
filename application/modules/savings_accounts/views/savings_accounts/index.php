<?php $this->load->view('partial/header'); ?>

<style>
    td:nth-child(1) { white-space: nowrap; }
    td:nth-child(5),
    td:nth-child(6),
    td:nth-child(7) { text-align: center; }
    .dataTables_info { float: left; }

    /* Estilo fijo para el buscador */
    .search-header {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 12px;
        flex-wrap: nowrap;
        margin-top: 10px;
    }
    .search-header h3 {
        margin: 0;
        white-space: nowrap;
    }
    .search-header .form-inline {
        display: flex;
        align-items: center;
        margin: 0;
        gap: 6px;
    }
</style>

<div class="title-block">
    <h3 class="title">
        <span style="float:left">Cuentas de Ahorro</span>
    </h3>
    <div style="clear:both;"></div>
    <p class="title-description">
        Añadir, actualizar o borrar cuentas
    </p>
</div>

<div class="section">
  <div class="row sameheight-container">
    <div class="col-lg-12">
      <div class="card row" style="width:100%">
        <div class="card-block">

          <!-- ENCABEZADO: Botones + Título + Buscador -->
          <div class="row">
            <div class="col-md-6">
              <a href="<?= site_url('savings_accounts/savings_accounts/form') ?>"
                 class="btn btn-primary pull-left">
                <span class="glyphicon glyphicon-plus"></span>
                Nueva cuenta
              </a>

              <?php if (empty($show_inactive)): ?>
                <a href="<?= site_url('savings_accounts/savings_accounts/inactive') ?>"
                   class="btn btn-default">
                    Ver inactivas
                </a>
              <?php else: ?>
                <a href="<?= site_url('savings_accounts/savings_accounts') ?>"
                   class="btn btn-default">
                    Ver activas
                </a>
              <?php endif; ?>
            </div>

            <div class="col-md-6">

              <?php
                $search_action = empty($show_inactive)
                    ? site_url('savings_accounts/savings_accounts')
                    : site_url('savings_accounts/savings_accounts/inactive');

                $q_value = isset($q) ? trim($q) : '';
              ?>

              <div class="search-header">
                <h3 class="title">
                  <?= empty($show_inactive) ? 'Cuentas Activas' : 'Cuentas Inactivas' ?>
                </h3>

                <form method="get" action="<?= $search_action ?>" class="form-inline">
                  <input type="text"
                         name="q"
                         class="form-control input-sm"
                         placeholder="Buscar por # cuenta o cliente..."
                         value="<?= htmlspecialchars($q_value, ENT_QUOTES, 'UTF-8') ?>">

                  <button type="submit" class="btn btn-sm btn-default">
                    <span class="fa fa-search"></span> Buscar
                  </button>

                  <?php if ($q_value !== ''): ?>
                    <a href="<?= $search_action ?>" class="btn btn-sm btn-link">
                      Limpiar
                    </a>
                  <?php endif; ?>
                </form>
              </div>

            </div>
          </div>

          <!-- TABLA -->
          <div class="inqbox-content table-responsive" style="margin-top:15px;">
            <table class="table table-hover table-bordered">
              <thead>
                <tr>
                  <th style="text-align:center; width:1%">Acciones</th>
                  <th style="text-align:center"># Cuenta</th>
                  <th style="text-align:center">Cliente</th>
                  <th style="text-align:center">Tipo</th>
                  <th style="text-align:center">Saldo</th>
                  <th style="text-align:center">Apertura</th>
                  <th style="text-align:center">Vencimiento</th>
                  <th style="text-align:center">Estado</th>
                </tr>
              </thead>
              <tbody>
              <?php if (!empty($accounts)): ?>
                <?php foreach($accounts as $a): ?>
                  <tr>
                    <td class="text-center">
                      <a href="<?= site_url("savings_accounts/savings_accounts/form/{$a->savings_account_id}") ?>"
                        class="btn btn-xs btn-warning" title="Editar">
                        <span class="fa fa-pencil"></span>
                      </a>

                      <?php if (empty($show_inactive)): ?>
                        <!-- Listado de ACTIVAS: deshabilitar -->
                        <a href="<?= site_url("savings_accounts/savings_accounts/delete/{$a->savings_account_id}") ?>"
                          class="btn btn-xs btn-danger" title="Deshabilitar"
                          onclick="return confirm('¿Deshabilitar esta cuenta?');">
                          <span class="fa fa-trash"></span>
                        </a>
                      <?php else: ?>
                        <!-- Listado de INACTIVAS: reactivar -->
                        <a href="<?= site_url("savings_accounts/savings_accounts/reactivate/{$a->savings_account_id}") ?>"
                          class="btn btn-xs btn-success" title="Reactivar">
                          <span class="fa fa-undo"></span>
                        </a>
                      <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($a->account_number) ?></td>
                    <td><?= htmlspecialchars($a->first_name . ' ' . $a->last_name) ?></td>
                    <td><?= htmlspecialchars($a->type_name) ?></td>
                    <td><?= number_format($a->current_balance, 2) ?></td>
                    <td><?= date('d/m/Y', strtotime($a->opening_date)) ?></td>
                    <td><?= $a->maturity_date ? date('d/m/Y', strtotime($a->maturity_date)) : '<em>—</em>' ?></td>
                    <td class="text-center">
                      <?= $a->status
                           ? '<span class="label label-success">Activa</span>'
                           : '<span class="label label-default">Inactiva</span>' ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="text-center">
                    No se encontraron cuentas.
                  </td>
                </tr>
              <?php endif; ?>
              </tbody>
            </table>

            <!-- PAGINACIÓN -->
            <?php
              $page        = $page        ?? 1;
              $per_page    = $per_page    ?? count($accounts);
              $total       = $total       ?? count($accounts);
              $total_pages = $total_pages ?? 1;

              if ($per_page <= 0)    $per_page    = 1;
              if ($total_pages <= 0) $total_pages = 1;

              if ($total > 0) {
                  $start = ($page - 1) * $per_page + 1;
                  $end   = min($total, $page * $per_page);
              } else {
                  $start = 0;
                  $end   = 0;
              }

              $base_url = empty($show_inactive)
                  ? site_url('savings_accounts/savings_accounts')
                  : site_url('savings_accounts/savings_accounts/inactive');

              $build_url = function($page_number) use ($base_url, $q_value) {
                  $params = ['page' => $page_number];
                  if ($q_value !== '') {
                      $params['q'] = $q_value;
                  }
                  return $base_url . '?' . http_build_query($params);
              };
            ?>

            <div class="row" style="margin-top:10px;">
              <div class="col-md-6">
                <p class="dataTables_info">
                  Mostrando <?= $start ?>–<?= $end ?> de <?= $total ?> cuentas
                </p>
              </div>

              <div class="col-md-6 text-right">
                <ul class="pagination pagination-sm" style="margin:0;">
                  <li class="<?= $page <= 1 ? 'disabled' : '' ?>">
                    <a href="<?= $page > 1 ? $build_url($page - 1) : '#' ?>">&laquo; Anterior</a>
                  </li>

                  <?php
                    $window = 3;
                    $from = max(1, $page - $window);
                    $to   = min($total_pages, $page + $window);

                    for ($i = $from; $i <= $to; $i++):
                  ?>
                    <li class="<?= $i == $page ? 'active' : '' ?>">
                      <a href="<?= $build_url($i) ?>"><?= $i ?></a>
                    </li>
                  <?php endfor; ?>

                  <li class="<?= $page >= $total_pages ? 'disabled' : '' ?>">
                    <a href="<?= $page < $total_pages ? $build_url($page + 1) : '#' ?>">Siguiente &raquo;</a>
                  </li>
                </ul>
              </div>
            </div>

          </div><!-- ./table-responsive -->

        </div><!-- ./card-block -->
      </div><!-- ./card -->
    </div>
  </div>
</div>

<?php $this->load->view('partial/footer'); ?>

