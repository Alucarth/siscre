<?php $this->load->view('partial/header'); ?>
<link href="css/plugins/datapicker/datepicker3.css" rel="stylesheet">

<style>
    #drop-target {
        border: 10px dashed #999;
        text-align: center;
        color: #999;
        font-size: 20px;
        width: 600px;
        height: 300px;
        line-height: 300px;
        cursor: pointer;
    }

    #drop-target.dragover {
        background: rgba(255, 255, 255, 0.4);
        border-color: green;
    }

    .kl-plugin {
        display: inline-block;
        padding: 2px;
        border-radius: 6px;
        border: 1px solid #ccc;
        background-color: #f3e798;
    }

    .autocomplete-suggestions {
        overflow: auto;
    }
</style>

<div class="title-block">
    <h3 class="title"> 
      <?= isset($type) ? 'Editar Tipo de Cuenta' : 'Nuevo Tipo de Cuenta' ?>
    </h3>

    <div class="clearfix"></div>

    <p class="title-description">
        Información básica de tipos de cuentas de ahorro
    </p>
</div>

<div class="section">
  <div class="row sameheight-container">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-block">
          <div class="inqbox float-e-margins">            
            <div class="inqbox-content">
              <div>
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
                  <div class="col-sm-6">
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
                  <div class="col-sm-6">
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

                <div class="col-lg-12">
                  <div class="form-group">
                    <a class="btn btn-default btn-secondary" href="<?= site_url('accounts/account_types') ?>"
                      id="btn-close">
                      Cancelar
                    </a>
                    <input type="submit" class="btn btn-primary" value="Guardar" id="btn-save" />              
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?= form_close(); ?>
      </div>
    </div>
  </div>    
</div>

<?php $this->load->view('partial/footer'); ?>

