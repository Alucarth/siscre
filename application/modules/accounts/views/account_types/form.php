<?php $this->load->view('partial/header'); ?>

<div id="page-content" class="clearfix">
  <div class="panel panel-piluku">
    <div class="panel-heading">
      <h3 class="panel-title">
        <?= isset($type) ? 'Editar Tipo de Cuenta' : 'Nuevo Tipo de Cuenta' ?>
      </h3>
    </div>
    <div class="panel-body">

      <?php
      $action = isset($type)
        ? 'accounts/account_types/form/'.$type->account_type_id
        : 'accounts/account_types/form';
      echo form_open($action, ['class'=>'form-horizontal']);
      ?>

      <?php if (isset($type)): ?>
      <div class="form-group">
        <?= form_label('Código único', 'code', ['class'=>'col-sm-2 control-label']) ?>
        <div class="col-sm-6">
          <?= form_input([
                'name'=>'code',
                'id'=>'code',
                'value'=>set_value('code', $type->code ?? ''),
                'class'=>'form-control',
                'required'=>'required'
              ]) ?>
        </div>
      </div>
      <?php endif; ?>

      <div class="form-group">
        <?= form_label('Nombre', 'name', ['class'=>'col-sm-2 control-label']) ?>
        <div class="col-sm-6">
          <?= form_input([
                'name'=>'name',
                'id'=>'name',
                'value'=>set_value('name', $type->name ?? ''),
                'class'=>'form-control',
                'required'=>'required'
              ]) ?>
        </div>
      </div>

      <div class="form-group">
        <?= form_label('Tasa de interés (anual %)', 'interest_rate', ['class'=>'col-sm-2 control-label']) ?>
        <div class="col-sm-4">
          <?= form_input([
                'type'=>'number',
                'step'=>'0.01',
                'name'=>'interest_rate',
                'id'=>'interest_rate',
                'value'=>set_value('interest_rate', $type->interest_rate ?? '0.00'),
                'class'=>'form-control',
                'required'=>'required'
              ]) ?>
        </div>
      </div>

      <div class="form-group">
        <?= form_label('Descripción', 'description', ['class'=>'col-sm-2 control-label']) ?>
        <div class="col-sm-8">
          <?= form_textarea([
                'name'=>'description',
                'id'=>'description',
                'value'=>set_value('description', $type->description ?? ''),
                'class'=>'form-control',
                'rows'=>3
              ]) ?>
        </div>
      </div>

      <div class="form-group">
        <?= form_label('Estado', 'status', ['class'=>'col-sm-2 control-label']) ?>
        <div class="col-sm-3">
          <?= form_dropdown(
                'status',
                [1=>'Habilitado', 0=>'Deshabilitado'],
                set_value('status', $type->status ?? 1),
                ['class'=>'form-control']
              ) ?>
        </div>
      </div>

      <div class="panel-footer clearfix">
        <button type="submit" class="btn btn-success pull-right">
          <span class="glyphicon glyphicon-save"></span>
          Guardar
        </button>
        <a href="<?= site_url('accounts/account_types') ?>"
           class="btn btn-default pull-left">
          <span class="glyphicon glyphicon-arrow-left"></span>
          Cancelar
        </a>
      </div>

      <?= form_close(); ?>

    </div>
  </div>
</div>

<?php $this->load->view('partial/footer'); ?>

