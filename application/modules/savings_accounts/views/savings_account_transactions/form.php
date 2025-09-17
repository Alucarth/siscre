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
               'class="form-control" id="trans_type" required'
             ) ?>
        </div>
      </div>
      
      <?php $is_deposit = (set_value('trans_type', $tx->trans_type ?? 'deposit') === 'deposit'); ?>

      <!-- Datos del depositante (solo depósito) -->
      <div id="deposit-fields" style="<?= $is_deposit ? '' : 'display:none' ?>">
        <div class="form-group">
          <?= form_label('Depositante', 'depositor_name', ['class'=>'col-sm-2 control-label']) ?>
          <div class="col-sm-6">
            <?= form_input([
                'name'  =>'depositor_name',
                'id'    =>'depositor_name',
                'value' => set_value('depositor_name', $tx->depositor_name ?? ''),
                'class' =>'form-control'
            ]) ?>
          </div>
        </div>
        <div class="form-group">
          <?= form_label('Documento de identidad', 'depositor_document', ['class'=>'col-sm-2 control-label']) ?>
          <div class="col-sm-4">
            <?= form_input([
                'name'  =>'depositor_document',
                'id'    =>'depositor_document',
                'value' => set_value('depositor_document', $tx->depositor_document ?? ''),
                'class' =>'form-control'
            ]) ?>
          </div>
        </div>
      </div>

      <!-- Cuenta destino (solo para transferencias) -->
      <?php $is_transfer = (set_value('trans_type', $tx->trans_type ?? 'deposit') === 'transfer'); ?>

      <div class="form-group" id="dst-account-group" style="<?= $is_transfer ? '' : 'display:none' ?>">
        <?= form_label('Cuenta destino', 'dst_account_id', ['class'=>'col-sm-2 control-label']) ?>
        <div class="col-sm-6">
          <?= form_dropdown(
              'dst_account_id',
              $account_options,
              set_value('dst_account_id', $tx->counterparty_account_id ?? ''),
              'class="form-control" id="dst_account_id"'
          ) ?>
        </div>
      </div>

      <!-- Verificación del titular (visible en retiro/transferencia) -->
      <div id="owner-verify" style="display:none">
        <?php
          // Evita notices si $owner no está set
          $owner = isset($owner) && is_array($owner) ? $owner : ['full_name'=>'','id_no'=>'','photo_url'=>''];
          $owner_name = trim($owner['full_name'] ?? '');
          $owner_idno = trim($owner['id_no'] ?? '');
          $owner_photo = $owner['photo_url'] ?? '';
        ?>

        <div class="form-group">
          <label class="col-sm-2 control-label">Titular</label>
          <div class="col-sm-6" id="owner_name">
            <?= $owner_name !== '' ? htmlspecialchars($owner_name) : '<em>—</em>' ?>
          </div>
        </div>

        <div class="form-group">
          <label class="col-sm-2 control-label">Documento (ID)</label>
          <div class="col-sm-6" id="owner_idno">
            <?= $owner_idno !== '' ? htmlspecialchars($owner_idno) : '<em>—</em>' ?>
          </div>
        </div>

        <div class="form-group">
          <?= form_label('Foto del titular', '', ['class'=>'col-sm-2 control-label']) ?>
          <div class="col-sm-6">
            <img
              id="owner_photo_img"
              src="<?= $owner_photo ? htmlspecialchars($owner_photo) : '' ?>"
              alt="Foto del titular"
              style="max-height:120px;border-radius:6px;<?= $owner_photo ? '' : 'display:none' ?>"
            >
            <em id="owner_photo_na" style="<?= $owner_photo ? 'display:none' : '' ?>">Sin foto disponible</em>
          </div>
        </div>

        <?php if (!empty($require_owner_auth)): // <-- solo si está activo ?>
        <div class="form-group">
          <?= form_label('Contraseña del titular', 'owner_password', ['class'=>'col-sm-2 control-label']) ?>
          <div class="col-sm-4">
            <input type="password" name="owner_password" id="owner_password" class="form-control" autocomplete="off" />
          </div>
        </div>
        <?php endif; ?>
      </div>

      <script>
      (function(){
        function toggleOwnerVerify(){
          var t = $('#trans_type').val();
          var show = (t === 'withdraw' || t === 'transfer');
          $('#owner-verify').toggle(show);
        }

        function loadOwnerInfo(){
          var acc = $('[name="savings_account_id"]').val();
          if(!acc) return;
          $.getJSON('<?= site_url('savings_accounts/savings_account_transactions/owner_info') ?>/'+acc, function(r){
            if(!r || !r.ok){ 
              $('#owner_name').html('<em>—</em>');
              $('#owner_idno').html('<em>—</em>');
              $('#owner_photo_img').hide();
              $('#owner_photo_na').show();
              return;
            }
            $('#owner_name').text(r.full_name || '—');
            $('#owner_idno').text(r.id_no || '—');

            if(r.photo_url){
              $('#owner_photo_img').attr('src', r.photo_url).show();
              $('#owner_photo_na').hide();
            } else {
              $('#owner_photo_img').hide();
              $('#owner_photo_na').show();
            }
          });
        }

        // Cambios que activan el panel
        $('#trans_type').on('change select2:select', function(){
          toggleOwnerVerify();
          if ($('#trans_type').val()==='withdraw' || $('#trans_type').val()==='transfer') {
            loadOwnerInfo();
          }
        });

        $('[name="savings_account_id"]').on('change select2:select', function(){
          if ($('#trans_type').val()==='withdraw' || $('#trans_type').val()==='transfer') {
            loadOwnerInfo();
          }
        });

        // Estado inicial
        toggleOwnerVerify();
        if ($('#trans_type').val()==='withdraw' || $('#trans_type').val()==='transfer') {
          loadOwnerInfo();
        }
      })();
      </script>

      <script>
        (function() {
          function toggleExtras() {
            var v = $('#trans_type').val();
            $('#dst-account-group').toggle(v === 'transfer');
            $('#deposit-fields').toggle(v === 'deposit');
          }
          $('#trans_type').on('change select2:select', toggleExtras);
          toggleExtras(); // estado inicial
        })();
      </script>

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
