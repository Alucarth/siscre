<?php $this->load->view('partial/header'); ?>

<div class="title-block">
  <h3 class="title"><?= isset($account) ? 'Editar Cuenta' : 'Nueva Cuenta' ?></h3>
  <div class="clearfix"></div>
</div>

<div class="section">
  <div class="row sameheight-container">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-block">
          <?php
            $action = 'savings_accounts/savings_accounts/form'
                    . (isset($account) ? "/{$account->savings_account_id}" : '');
            echo form_open($action, ['class'=>'form-horizontal']);
          ?>

          <!-- Cliente -->
          <div class="form-group">
            <?= form_label('Cliente', 'person_id', ['class'=>'col-sm-2 control-label']) ?>
            <div class="col-sm-6">
              <?= form_dropdown(
                    'person_id',
                    $customer_options,
                    set_value('person_id',$account->person_id ?? ''),
                    'class="form-control" id="person_id" required'
                ) ?>
            </div>
          </div>

          <!-- Tipo de Cuenta -->
          <div class="form-group">
            <?= form_label('Tipo', 'savings_account_type_id', ['class'=>'col-sm-2 control-label']) ?>
            <div class="col-sm-6">
              <?= form_dropdown(
                   'savings_account_type_id',
                   array_column($types,'name','savings_account_type_id'),
                   set_value('savings_account_type_id', $account->savings_account_type_id ?? ''),
                   'class="form-control" required'
                 ) ?>
            </div>
          </div>

          <!-- Fecha de Apertura -->
          <div class="form-group">
            <?= form_label('Apertura', 'opening_date', ['class'=>'col-sm-2 control-label']) ?>
            <div class="col-sm-3">
              <?= form_input([
                   'type'=>'date',
                   'name'=>'opening_date',
                   'value'=>set_value('opening_date',$account->opening_date ?? date('Y-m-d')),
                   'class'=>'form-control',
                   'required'=>'required'
                 ]) ?>
            </div>
          </div>

          <!-- Saldo Inicial -->
          <div class="form-group">
            <?= form_label('Saldo Inicial', 'initial_balance', ['class'=>'col-sm-2 control-label']) ?>
            <div class="col-sm-3">
              <?= form_input([
                   'type'=>'number','step'=>'0.01',
                   'name'=>'initial_balance',
                   'value'=>set_value('initial_balance',$account->initial_balance ?? '0.00'),
                   'class'=>'form-control','required'=>'required'
                 ]) ?>
            </div>
          </div>

          <!-- Observaciones -->
          <div class="form-group">
            <?= form_label('Comentarios', 'comments', ['class'=>'col-sm-2 control-label']) ?>
            <div class="col-sm-8">
              <?= form_textarea([
                   'name'=>'comments',
                   'value'=>set_value('comments',$account->comments ?? ''),
                   'class'=>'form-control','rows'=>3
                 ]) ?>
            </div>
          </div>

          <!-- Botones -->
          <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
              <button type="submit" class="btn btn-success">
                <span class="glyphicon glyphicon-save"></span>
                Guardar
              </button>
              <a href="<?= site_url('savings_accounts/savings_accounts') ?>"
                 class="btn btn-default">
                <span class="glyphicon glyphicon-arrow-left"></span>
                Cancelar
              </a>
            </div>
          </div>

          <?= form_close(); ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php $this->load->view('partial/footer'); ?>
