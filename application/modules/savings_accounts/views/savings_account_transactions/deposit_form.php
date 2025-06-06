<?php $this->load->view('partial/header'); ?>

<div class="title-block">
  <h3 class="title"><?= isset($tx) ? 'Editar Depósito' : 'Nuevo Depósito' ?></h3>
</div>

<div class="section">
  <?= form_open(
      'savings_accounts/savings_account_transactions/deposit'
      . (isset($tx)?" /{$tx->transaction_id}":''),
      ['class'=>'form-horizontal']
  ) ?>

  <!-- Cuenta -->
  <div class="form-group">
    <?= form_label('Cuenta', 'savings_account_id', ['class'=>'col-sm-2 control-label']) ?>
    <div class="col-sm-6">
      <?= form_dropdown(
        'savings_account_id',
        $account_options,
        set_value('savings_account_id', $tx->savings_account_id ?? ''),
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
        'value'=>set_value('amount', $tx->amount ?? ''),
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
        'value'=>set_value('trans_date',
          isset($tx)
            ? date('Y-m-d\TH:i',strtotime($tx->trans_date))
            : date('Y-m-d\TH:i')
        ),
        'class'=>'form-control','required'=>'required'
      ]) ?>
    </div>
  </div>

  <!-- Nombre del depositante -->
  <div class="form-group">
    <?= form_label('Depositante', 'depositor_name', ['class'=>'col-sm-2 control-label']) ?>
    <div class="col-sm-6">
      <?= form_input([
        'name'=>'depositor_name',
        'value'=>set_value('depositor_name', $tx->depositor_name ?? ''),
        'class'=>'form-control','required'=>'required'
      ]) ?>
    </div>
  </div>

  <!-- Documento -->
  <div class="form-group">
    <?= form_label('Documento ID', 'depositor_document', ['class'=>'col-sm-2 control-label']) ?>
    <div class="col-sm-4">
      <?= form_input([
        'name'=>'depositor_document',
        'value'=>set_value('depositor_document', $tx->depositor_document ?? ''),
        'class'=>'form-control','required'=>'required'
      ]) ?>
    </div>
  </div>

  <!-- Sucursal -->
  <div class="form-group">
    <?= form_label('Agencia', 'branch_id', ['class'=>'col-sm-2 control-label']) ?>
    <div class="col-sm-4">
      <?php
      // si tienes un model Branch que da dropdown:
      $this->load->model('Branch');
      $b = $this->Branch->get_all();
      $branches = [];
      foreach ($b->result() as $br) {
        $branches[$br->branch_id] = $br->name;
      }
      ?>
      <?= form_dropdown(
        'branch_id',
        $branches,
        set_value('branch_id', $tx->branch_id ?? ''),
        'class="form-control" required'
      ) ?>
    </div>
  </div>

  <!-- Descripción (opcional) -->
  <div class="form-group">
    <?= form_label('Notas', 'description', ['class'=>'col-sm-2 control-label']) ?>
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
        <span class="glyphicon glyphicon-save"></span> Guardar Depósito
      </button>
      <a href="<?= site_url('savings_accounts/savings_account_transactions') ?>"
         class="btn btn-default">Cancelar</a>
    </div>
  </div>

  <?= form_close(); ?>
</div>

<?php $this->load->view('partial/footer'); ?>
