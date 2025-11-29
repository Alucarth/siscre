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
              <a href="<?= site_url('savings_accounts/savings_account_types/form') ?>"
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
                  <th style="text-align: center">Plazo (días)</th> 
                  <th style="text-align: center">Estado</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($types as $t): ?>
                <tr>
                  <?php 
                    // Detectar correctamente el campo de estado:
                    $st = isset($t->status) 
                          ? (int)$t->status 
                          : (isset($t->status_flag) ? (int)$t->status_flag : 0);
                  ?>
                  <td class="text-center">
                    <!-- Botón Editar -->
                    <a href="<?= site_url('savings_accounts/savings_account_types/form/'.$t->savings_account_type_id) ?>"
                       class="btn btn-xs btn-warning" title="Editar">
                      <span class="fa fa-pencil"></span>
                    </a>

                    <?php if ($st === 1): ?>
                      <!-- Si está habilitado → mostrar botón para DESHABILITAR -->
                      <a href="<?= site_url('savings_accounts/savings_account_types/delete/'.$t->savings_account_type_id) ?>"
                         class="btn btn-xs btn-danger" title="Deshabilitar"
                         onclick="return confirm('¿Está seguro de deshabilitar este tipo de cuenta?');">
                        <span class="fa fa-ban"></span>
                      </a>
                    <?php else: ?>
                      <!-- Si está deshabilitado → mostrar botón para HABILITAR -->
                      <a href="<?= site_url('savings_accounts/savings_account_types/delete/'.$t->savings_account_type_id) ?>"
                         class="btn btn-xs btn-success" title="Habilitar"
                         onclick="return confirm('¿Desea habilitar nuevamente este tipo de cuenta?');">
                        <span class="fa fa-toggle-on"></span>
                      </a>
                    <?php endif; ?>
                  </td>

                  <td><?= htmlspecialchars($t->code) ?></td>
                  <td><?= htmlspecialchars($t->name) ?></td>
                  <td><?= number_format($t->interest_rate, 2) ?></td>
                  <td>
                    <?= $t->is_fixed_term
                        ? (int)$t->term_days
                        : '<em>Abierta</em>' ?>
                  </td>  
                  <td>
                    <?php if ($st === 1): ?>
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


