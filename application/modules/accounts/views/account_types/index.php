<?php $this->load->view('partial/header'); ?>

<style>
    td:nth-child(1) {
        white-space: nowrap;
    }

    td:nth-child(3),
    td:nth-child(4),
    td:nth-child(5),
    td:nth-child(6), 
    td:nth-child(7) {
        text-align: center;
    }
    .dataTables_info {
        float:left;
    }
</style>

<script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/3.2.3/js/dataTables.fixedColumns.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/fixedheader/3.1.3/js/dataTables.fixedHeader.min.js"></script>

<div class="title-block">
    <h3 class="title"> 
        <span style="float:left">Tipo de cuentas de ahorro</span>
    </h3>

    <div style="clear:both;"></div>

    <p class="title-description">
        Añadir, actualizar o borrar cuentas
    </p>
</div>

<div class="section">
  <div class="row sameheight-container">
    <div class="col-lg-12">
      <div class="card" style="width:100%">
        <div class="card-block">
          <div class="row">
            <div class="col-md-12">
              <a href="<?= site_url('accounts/account_types/form') ?>"
                class="btn btn-primary pull-left">
                <span class="glyphicon glyphicon-plus"></span>
                Nuevo Tipo
              </a>
            </div>
          </div>
          <div class="inqbox-content table-responsive">
            <table class="table table-hover table-bordered">
              <thead>
                <tr>
                  <th style="text-align: center; width: 1%">Acciones</th>                            
                  <th style="text-align: center">Código</th>
                  <th style="text-align: center">Nombre</th>
                  <th style="text-align: center">Tasa (%)</th> 
                  <th style="text-align: center">Estado</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($types as $t): ?>
                <tr>
                  <td class="text-center">
                    <a href="<?= site_url('accounts/account_types/form/'.$t->account_type_id) ?>"
                       class="btn btn-xs btn-warning" title="Editar">
                      <span class="fa fa-pencil"></span>
                    </a>
                    <a href="<?= site_url('accounts/account_types/delete/'.$t->account_type_id) ?>"
                       class="btn btn-xs btn-danger" title="Deshabilitar"
                       onclick="return confirm('¿Eliminar este tipo de cuenta?');">
                      <span class="fa fa-trash"></span>
                    </a>
                  </td>
                  <td><?= htmlspecialchars($t->code) ?></td>
                  <td><?= htmlspecialchars($t->name) ?></td>
                  <td><?= number_format($t->interest_rate, 2) ?></td>
                  <td>
                    <?php 
                    // Detectar correctamente el campo de estado:
                    $st = isset($t->status) 
                          ? $t->status 
                          : (isset($t->status_flag) ? $t->status_flag : 0);
                    ?>
                    <?php if ($st): ?>
                      <span class="label label-success">Habilitado</span>
                    <?php else: ?>
                      <span class="label label-default">Deshabilitado</span>
                    <?php endif; ?>
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

