<?php $this->load->view('partial/header'); ?>

<div id="page-content" class="clearfix">
  <div class="panel panel-piluku">
    <div class="panel-heading">
      <h3 class="panel-title">Tipos de Cuentas de Ahorro</h3>
    </div>
    <div class="panel-body">

      <div class="row">
        <div class="col-md-12">
          <a href="<?= site_url('accounts/account_types/form') ?>"
             class="btn btn-primary btn-sm pull-right">
            <span class="glyphicon glyphicon-plus"></span>
            Nuevo Tipo
          </a>
        </div>
      </div>

      <br/>

      <div class="row">
        <div class="col-md-12">
          <table class="table table-striped table-bordered table-hover">
            <thead>
              <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Tasa (%)</th>
                <th>Estado</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($types as $t): ?>
              <tr>
                <td><?= htmlspecialchars($t->code) ?></td>
                <td><?= htmlspecialchars($t->name) ?></td>
                <td><?= number_format($t->interest_rate, 2) ?></td>
                <td>
                  <?php if ($t->status): ?>
                    <span class="label label-success">Habilitado</span>
                  <?php else: ?>
                    <span class="label label-default">Deshabilitado</span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <a href="<?= site_url('accounts/account_types/form/'.$t->account_type_id) ?>"
                     class="btn btn-xs btn-warning" title="Editar">
                    <span class="glyphicon glyphicon-pencil"></span>
                  </a>
                  <a href="<?= site_url('accounts/account_types/delete/'.$t->account_type_id) ?>"
                     class="btn btn-xs btn-danger" title="Eliminar"
                     onclick="return confirm('¿Eliminar este tipo de cuenta?');">
                    <span class="glyphicon glyphicon-trash"></span>
                  </a>
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

<?php $this->load->view('partial/footer'); ?>

