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
                  ? 'savings_accounts/savings_account_types/form/'.$type->savings_account_type_id
                  : 'savings_accounts/savings_account_types/form';
                  echo form_open($action, ['class'=>'form-horizontal']);
                ?>

                <?php if (isset($type)): // solo en edición mostramos el código ?>
                <div class="form-group">
                    <?= form_label('Código único', 'code', ['class'=>'col-sm-2 control-label']) ?>
                    <div class="col-sm-6">
                        <?= form_input([
                        'name'=>'code',
                        'value'=>set_value('code', $type->code),
                        'class'=>'form-control',
                        'readonly'=>'readonly'
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
                    <?= form_label('¿Plazo Fijo?', 'is_fixed_term', ['class'=>'col-sm-2 control-label']) ?>
                    <div class="col-sm-2">
                        <?= form_dropdown(
                            'is_fixed_term',
                            [0 => 'No', 1 => 'Sí'],
                            set_value('is_fixed_term', $type->is_fixed_term ?? 0),
                            'class="form-control" id="is_fixed_term"'
                        ) ?>
                    </div>
                </div>

                <div class="form-group" id="term-days-group" style="<?= isset($type) && $type->is_fixed_term ? '' : 'display:none' ?>">
                    <?= form_label('Plazo (días)', 'term_days', ['class'=>'col-sm-2 control-label']) ?>
                    <div class="col-sm-2">
                        <?= form_input([
                            'type'=>'number',
                            'name'=>'term_days',
                            'value'=>set_value('term_days', $type->term_days ?? 0),
                            'class'=>'form-control',
                            'min'=>1
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
                    <a class="btn btn-default btn-secondary" href="<?= site_url('savings_accounts/savings_account_types') ?>"
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
<script>
  $(function() {
    var $sel = $('#is_fixed_term'),
        $grp = $('#term-days-group');
    if (!$sel.length || !$grp.length) return;

    // inicial: muestra u oculta según el valor actual
    $grp.toggle($sel.val() === '1');

    // al cambiar (Select2 dispara change en el <select>)
    $sel.on('change', function() {
      $grp.toggle($(this).val() === '1');
    });
  });
</script>

<?php $this->load->view('partial/footer'); ?>
