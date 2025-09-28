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
  <h3 class="title"><?= isset($type) ? 'Editar Tipo de Cuenta' : 'Nuevo Tipo de Cuenta' ?></h3>
  <p class="title-description">Información básica de tipos de cuentas de ahorro</p>
</div>

<div class="section">
  <div class="row sameheight-container">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-block">

          <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger"><?= $this->session->flashdata('error'); ?></div>
          <?php endif; ?>
          <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success"><?= $this->session->flashdata('success'); ?></div>
          <?php endif; ?>

          <?php
            $action = isset($type)
              ? 'savings_accounts/savings_account_types/form/'.$type->savings_account_type_id
              : 'savings_accounts/savings_account_types/form';
            echo form_open($action, ['class'=>'form-horizontal']);
          ?>

          <?php if (isset($type)): ?>
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
                'value'=>set_value('name', $type->name ?? ''),
                'class'=>'form-control',
                'required'=>'required'
              ]) ?>
            </div>
          </div>

          <div class="form-group">
            <?= form_label('Tasa de interés (anual %)', 'interest_rate', ['class'=>'col-sm-2 control-label']) ?>
            <div class="col-sm-2">
              <?= form_input([
                'type'=>'number','step'=>'0.01',
                'name'=>'interest_rate',
                'value'=>set_value('interest_rate', $type->interest_rate ?? '0.00'),
                'class'=>'form-control',
                'required'=>'required'
              ]) ?>
            </div>
          </div>
          <?php if (isset($type)): ?>
            <div class="form-group">
              <label class="col-sm-2 control-label">APY estimada</label>
              <div class="col-sm-2" style="padding-top:7px">
                <?= number_format((float)($type->interest_rate_apy ?? 0) * 100, 2) ?>%
              </div>
            </div>
          <?php endif; ?>

          <div class="form-group">
            <?= form_label('¿Plazo Fijo?', 'is_fixed_term', ['class'=>'col-sm-2 control-label']) ?>
            <div class="col-sm-2">
              <?= form_dropdown(
                'is_fixed_term',
                [0=>'No', 1=>'Sí'],
                set_value('is_fixed_term', $type->is_fixed_term ?? 0),
                ['class'=>'form-control select2', 'id'=>'is_fixed_term']
              ) ?>
            </div>
          </div>

          <div class="form-group" id="term-days-group" style="<?= ((int)set_value('is_fixed_term', $type->is_fixed_term ?? 0) === 1) ? '' : 'display:none' ?>">
            <?= form_label('Plazo (días)', 'term_days', ['class'=>'col-sm-2 control-label']) ?>
            <div class="col-sm-2">
              <?= form_input([
                'type'=>'number','min'=>1,
                'name'=>'term_days',
                'value'=>set_value('term_days', $type->term_days ?? ''),
                'class'=>'form-control'
              ]) ?>
            </div>
          </div>

          <script>
            (function(){
              function toggleTermDays(){
                var val = document.getElementById('is_fixed_term')?.value;
                var group = document.getElementById('term-days-group');
                if(!group) return;
                group.style.display = (val === '1') ? '' : 'none';

                // (opcional) forzar requerido cuando es plazo fijo
                var termInput = document.querySelector('[name="term_days"]');
                if (termInput) termInput.required = (val === '1');
              }

              // Estado inicial
              toggleTermDays();

              // Escuchar cambios normales…
              var sel = document.getElementById('is_fixed_term');
              if (sel) sel.addEventListener('change', toggleTermDays);

              // …y también eventos de Select2 (si está activo)
              if (window.jQuery) {
                var $sel = jQuery('#is_fixed_term');
                if ($sel && $sel.on) {
                  $sel.on('select2:select', toggleTermDays);
                }
              }
            })();
          </script>

          <div class="form-group">
            <?= form_label('Descripción', 'description', ['class'=>'col-sm-2 control-label']) ?>
            <div class="col-sm-6">
              <?= form_textarea([
                'name'=>'description',
                'value'=>set_value('description', $type->description ?? ''),
                'class'=>'form-control','rows'=>3
              ]) ?>
            </div>
          </div>

          <div class="form-group">
            <?= form_label('Estado', 'status', ['class'=>'col-sm-2 control-label']) ?>
            <div class="col-sm-2">
              <?= form_dropdown(
                'status',
                [1=>'Habilitado', 0=>'Deshabilitado'],
                set_value('status', $type->status ?? 1),
                ['class'=>'form-control']
              ) ?>
            </div>
          </div>

          <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
              <a class="btn btn-default btn-secondary" href="<?= site_url('savings_accounts/savings_account_types') ?>">Cancelar</a>
              <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
          </div>

          <?= form_close(); ?>

        </div>
      </div>
    </div>
  </div>
</div>

<?php $this->load->view('partial/footer'); ?>
