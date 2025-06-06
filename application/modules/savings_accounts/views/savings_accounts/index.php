<?php $this->load->view('partial/header'); ?>

<style>
    td:nth-child(1) { white-space: nowrap; }
    td:nth-child(5),
    td:nth-child(6),
    td:nth-child(7) { text-align: center; }
    .dataTables_info { float: left; }
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
            <h3 class="title">
                <?= empty($show_inactive) ? 'Cuentas Activas' : 'Cuentas Inactivas' ?>
            </h3>
          </div>
          <div class="inqbox-content table-responsive">
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
              <?php foreach($accounts as $a): ?>
                <tr>
                  <td class="text-center">
                    <a href="<?= site_url("savings_accounts/savings_accounts/form/{$a->savings_account_id}") ?>"
                       class="btn btn-xs btn-warning" title="Editar">
                      <span class="fa fa-pencil"></span>
                    </a>
                    <a href="<?= site_url("savings_accounts/savings_accounts/delete/{$a->savings_account_id}") ?>"
                       class="btn btn-xs btn-danger" title="Deshabilitar"
                       onclick="return confirm('¿Eliminar esta cuenta?');">
                      <span class="fa fa-trash"></span>
                    </a>
                  </td>
                  <td><?= htmlspecialchars($a->account_number) ?></td>
                  <td><?= htmlspecialchars($a->first_name . ' ' . $a->last_name) ?></td>
                  <td><?= htmlspecialchars($a->type_name) ?></td>
                  <td><?= number_format($a->current_balance, 2) ?></td>
                  <td><?= date('d/m/Y', strtotime($a->opening_date)) ?></td>
                  <td>
                    <?= $a->maturity_date
                         ? date('d/m/Y', strtotime($a->maturity_date))
                         : '<em>—</em>' ?>
                  </td>
                  <td class="text-center">
                    <?= $a->status
                         ? '<span class="label label-success">Activo</span>'
                         : '<span class="label label-default">Cerrada</span>' ?>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php $this->load->view('partial/footer'); ?>
