<?php $this->load->view('partial/header'); ?>

<div class="title-block">
  <h3 class="title"><?= isset($tx) ? 'Editar Transacción' : 'Nueva Transacción' ?></h3>
  <div class="clearfix"></div>
</div>

<div class="section">
  <div class="card">
    <div class="card-block">
      <?php
        $action = 'savings_accounts/savings_account_transactions/form'
                . (isset($tx) ? "/{$tx->transaction_id}" : '');
        echo form_open($action, ['class'=>'form-horizontal']);
      ?>

      <!-- Cuenta -->
      <div class="form-group">
        <?= form_label('Cuenta', 'savings_account_id', ['class'=>'col-sm-2 control-label']) ?>
        <div class="col-sm-6">
          <?= form_dropdown(
               'savings_account_id',
               $account_options,
               set_value('savings_account_id',$tx->savings_account_id ?? ''),
               'class="form-control" required'
             ) ?>
        </div>
      </div>

      <!-- Tipo -->
      <div class="form-group">
        <?= form_label('Tipo', 'trans_type', ['class'=>'col-sm-2 control-label']) ?>
        <div class="col-sm-4">
          <?= form_dropdown(
               'trans_type',
               $type_options,
               set_value('trans_type',$tx->trans_type ?? 'deposit'),
               'class="form-control" required'
             ) ?>
        </div>
      </div>

      <!-- Monto -->
      <div class="form-group">
        <?= form_label('Monto', 'amount', ['class'=>'col-sm-2 control-label']) ?>
        <div class="col-sm-3">
          <?= form_input([
               'type'=>'number','step'=>'0.01',
               'name'=>'amount',
               'value'=>set_value('amount',$tx->amount ?? ''),
               'class'=>'form-control','required'=>'required'
             ]) ?>
        </div>
      </div>

      <!-- Fecha/Hora -->
      <div class="form-group">
        <?= form_label('Fecha', 'trans_date', ['class'=>'col-sm-2 control-label']) ?>
        <div class="col-sm-4">
          <?= form_input([
               'type'=>'datetime-local',
               'name'=>'trans_date',
               'value'=>set_value('trans_date', isset($tx) 
                        ? date('Y-m-d\TH:i',strtotime($tx->trans_date))
                        : date('Y-m-d\TH:i')),
               'class'=>'form-control','required'=>'required'
             ]) ?>
        </div>
      </div>

      <!-- Descripción -->
      <div class="form-group">
        <?= form_label('Descripción', 'description', ['class'=>'col-sm-2 control-label']) ?>
        <div class="col-sm-8">
          <?= form_textarea([
               'name'=>'description',
               'value'=>set_value('description',$tx->description ?? ''),
               'class'=>'form-control','rows'=>2
             ]) ?>
        </div>
      </div>

      <!-- Botones -->
      <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
          <button type="submit" class="btn btn-success">
            <span class="glyphicon glyphicon-save"></span> Guardar
          </button>
          <a href="<?= site_url('savings_accounts/savings_account_transactions') ?>"
             class="btn btn-default">
            <span class="glyphicon glyphicon-arrow-left"></span> Cancelar
          </a>
        </div>
      </div>

      <?= form_close(); ?>
    </div>
  </div>
</div>

<?php $this->load->view('partial/footer'); ?>
