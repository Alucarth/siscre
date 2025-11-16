<?php $this->load->view('partial/header'); ?>

<div class="title-block">
  <h3 class="title"><?= isset($tx) ? 'Editar Transacción' : 'Nueva Transacción' ?></h3>
  <div class="clearfix"></div>
</div>

<div class="section col-12">
  <div class="card">
    <div class="card-block">
      <?php
        $action = 'savings_accounts/savings_account_transactions/form'
                . (isset($tx) ? "/{$tx->transaction_id}" : '');
        echo form_open($action, ['class'=>'form-horizontal', 'id'=>'tx_form']);
      ?>
      
      <!-- Cuenta + Tipo en la misma fila (2 columnas iguales) -->
      <div class="row">
        <div class="col-sm-5">
          <div class="form-group" style="margin-bottom:12px">
            <label for="savings_account_id" class="control-label">Cuenta</label>
            <?= form_dropdown(
                'savings_account_id',
                $account_options,
                set_value('savings_account_id',$tx->savings_account_id ?? ''),
                'class="form-control" id="savings_account_id" required'
              ) ?>
          </div>
        </div>

        <div class="col-sm-2">
          <div class="form-group" style="margin-bottom:12px">
            <label for="trans_type" class="control-label">Tipo</label>
            <?= form_dropdown(
                'trans_type',
                $type_options,
                set_value('trans_type',$tx->trans_type ?? 'deposit'),
                'class="form-control" id="trans_type" required'
              ) ?>
          </div>
        </div>

        <div class="col-sm-4"></div>
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

      <!-- Verificación del titular (lado izq: datos | lado der: foto grande) -->
      <div id="owner-verify" class="row" style="display:none; margin-top:10px">
        <?php
          $owner = isset($owner) && is_array($owner) ? $owner : ['full_name'=>'','id_no'=>'','photo_url'=>''];
          $owner_name  = trim($owner['full_name'] ?? '');
          $owner_idno  = trim($owner['id_no'] ?? '');
          $owner_photo = $owner['photo_url'] ?? '';
        ?>

        <!-- IZQUIERDA: datos del titular -->
        <div class="col-sm-4">
          <dl class="dl-horizontal" style="margin-bottom:10px">
            <dt style="width:140px">Titular</dt>
            <dd id="owner_name">
              <?= $owner_name !== '' ? htmlspecialchars($owner_name) : '<em>—</em>' ?>
            </dd>

            <dt style="width:140px">Documento (ID)</dt>
            <dd id="owner_idno">
              <?= $owner_idno !== '' ? htmlspecialchars($owner_idno) : '<em>—</em>' ?>
            </dd>
          </dl>

          <?php if (!empty($require_owner_auth)): ?>
            <div class="form-group" style="margin-bottom:0">
              <?= form_label('Contraseña del titular', 'owner_password', ['class'=>'control-label', 'style'=>'width:140px']) ?>
              <div style="margin-left:160px">
                <input type="password" name="owner_password" id="owner_password" class="form-control" autocomplete="off" />
              </div>
            </div>
          <?php endif; ?>
        </div>

        <!-- DERECHA: foto ocupando toda la altura -->
        <div class="col-sm-4">
          <div style="
            border:1px solid #e5e5e5; border-radius:6px; background:#fafafa;
            padding:10px; min-height:260px;
            display:flex; align-items:center; justify-content:center;">
            <img
              id="owner_photo_img"
              src="<?= $owner_photo ? htmlspecialchars($owner_photo) : '' ?>"
              alt="Foto del titular"
              style="max-width:100%; max-height:240px; border-radius:6px; <?= $owner_photo ? '' : 'display:none' ?>"
            >
            <em id="owner_photo_na" style="<?= $owner_photo ? 'display:none' : '' ?>">Sin foto disponible</em>
          </div>
        </div>

        <div class="col-sm-4"></div>
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
        (function(){
          var $type   = $('#trans_type');
          var $dstGrp = $('#dst-account-group');
          var $dst    = $('#dst_account_id');
          var $form   = $('#tx_form');
          var $btn    = $('#btn-save');
          var $src    = $('[name="savings_account_id"]');

          function toggleExtrasRequired(){
            var v = $type.val();

            // Mostrar / ocultar secciones
            $dstGrp.toggle(v === 'transfer');
            $('#deposit-fields').toggle(v === 'deposit');

            // Required dinámico
            if (v === 'transfer') {
              $dst.prop('required', true);
            } else {
              $dst.prop('required', false);
              // opcional: limpia valor destino si no es transferencia
              // $dst.val('');
            }
          }

          // Evita que la cuenta origen sea igual a la destino
          function validateSrcDst(){
            if ($type.val() === 'transfer') {
              var src = String($src.val() || '');
              var dst = String($dst.val() || '');
              if (src && dst && src === dst) {
                alert('La cuenta de origen y destino no pueden ser la misma.');
                return false;
              }
            }
            return true;
          }

          // Anti doble submit (sin cambiar controlador)
          $form.on('submit', function(e){
            // Validación rápida de transferencia
            if (!validateSrcDst()) {
              e.preventDefault();
              return;
            }
            // Deshabilita botón para evitar doble envío
            $btn.prop('disabled', true);
            // Si tu controlador redirige correctamente, no hace falta re-habilitar.
            // Si vuelve a la misma vista por un error, el botón se habilitará por recarga.
          });

          // Reaccionar a cambios
          $type.on('change select2:select', toggleExtrasRequired);
          toggleExtrasRequired(); // estado inicial
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
          <button type="submit" id="btn-save" class="btn btn-success">
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

<!-- ============ Modal Preview de Transacción ============ -->
<div id="txPreviewModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="txPreviewTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document" style="max-width:940px;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="txPreviewTitle">Confirmación de transacción</h5>
        <div class="btn-group" role="group" aria-label="Acciones">
          <a id="txPrintBtn" href="#" target="_blank" class="btn btn-sm btn-primary">
            <span class="glyphicon glyphicon-print"></span> Imprimir
          </a>
          <a id="txPdfBtn" href="#" target="_blank" class="btn btn-sm btn-default">
            <span class="glyphicon glyphicon-download"></span> PDF
          </a>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" style="margin-left:10px;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" style="background:#f8f8f8;">
        <div id="txPreviewBody" style="background:#fff; padding:10px; border:1px solid #ddd;">
          <!-- Aquí inyectamos el HTML del voucher -->
          <div class="text-center text-muted">Cargando vista previa…</div>
        </div>
      </div>
      <div class="modal-footer">
        <a id="txEditBtn" href="#" class="btn btn-warning">Editar</a>
        <a id="txNewBtn" href="<?= site_url('savings_accounts/savings_account_transactions/form') ?>" class="btn btn-default">Registrar otra</a>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  // Utilidad: abre modal con el voucher de la transacción
  window.openTxPreview = function(txId, pdfUrl){
    var previewUrl = '<?= site_url('savings_accounts/savings_account_transactions/voucher_preview') ?>/' + txId;
    var printUrl   = '<?= site_url('savings_accounts/savings_account_transactions/voucher_print') ?>/'   + txId;
    var editUrl    = '<?= site_url('savings_accounts/savings_account_transactions/form') ?>/'           + txId;

    // Botones
    document.getElementById('txPrintBtn').href = printUrl;
    document.getElementById('txEditBtn').href  = editUrl;
    // Si tienes un generador PDF, usa su URL; si no, oculta el botón PDF
    var pdfBtn = document.getElementById('txPdfBtn');
    if (pdfUrl && pdfUrl.length) {
      pdfBtn.href = pdfUrl;
      pdfBtn.style.display = '';
    } else {
      pdfBtn.style.display = 'none';
    }

    // Carga HTML del voucher
    var body = document.getElementById('txPreviewBody');
    body.innerHTML = '<div class="text-center text-muted">Cargando vista previa…</div>';

    fetch(previewUrl, { credentials: 'same-origin' })
      .then(function(r){ return r.text(); })
      .then(function(html){ body.innerHTML = html; })
      .catch(function(){ body.innerHTML = '<div class="text-danger">No se pudo cargar la vista previa.</div>'; });

    // Mostrar modal (Bootstrap)
    $('#txPreviewModal').modal('show');
  };

  // Auto-abrir si vienes de un POST exitoso (usa flashdata si ya la usas)
  <?php
    $tx_id  = (int)($this->session->flashdata('print_tx_id') ?: 0);
    $pdf_url = (string)$this->session->flashdata('pdf_url');
    if ($tx_id > 0):
  ?>
    window.addEventListener('load', function(){
      openTxPreview(<?= $tx_id ?>, <?= json_encode($pdf_url) ?>);
    });
  <?php endif; ?>
})();
</script>
<script>
(function () {
  // IDs/names esperados en tu form:
  var $selCuenta = document.querySelector('select[name="savings_account_id"]');
  var $selTipo   = document.querySelector('select[name="trans_type"]');

  // Destinos donde pintamos los datos (ajusta si tus IDs/clases cambian)
  var $titular   = document.getElementById('owner_name');   // <span id="owner_name">—</span>
  var $doc       = document.getElementById('owner_idno');   // <span id="owner_idno">—</span>
  var $foto      = document.getElementById('owner_photo');  // <img id="owner_photo" ...>

  function setOwnerUI(obj) {
    if (!obj || obj.ok !== true) {
      if ($titular) $titular.textContent = '—';
      if ($doc)     $doc.textContent     = '—';
      if ($foto)    $foto.src            = '<?= base_url('uploads/people/placeholder-80x80.png') ?>';
      return;
    }
    if ($titular) $titular.textContent = obj.full_name || '—';
    if ($doc)     $doc.textContent     = (obj.id_no && obj.id_no.trim() !== '') ? obj.id_no : '—';
    if ($foto)    $foto.src            = obj.photo_url || '<?= base_url('uploads/people/placeholder-80x80.png') ?>';
  }

  function shouldLoadOwner() {
    // Para depósito también puede servir, pero tu requerimiento fue retiro/transfer
    var t = ($selTipo && $selTipo.value) ? $selTipo.value.toLowerCase() : '';
    return (t === 'withdraw' || t === 'transfer' || t === 'deposit'); // deja 'deposit' si quieres ver al titular igual
  }

  function loadOwner() {
    var accId = $selCuenta ? parseInt($selCuenta.value || '0', 10) : 0;
    if (!accId || !shouldLoadOwner()) { setOwnerUI(null); return; }

    var url = '<?= site_url('savings_accounts/savings_account_transactions/owner_info') ?>' + '/' + accId;
    fetch(url, { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (j) { setOwnerUI(j); })
      .catch(function () { setOwnerUI(null); });
  }

  if ($selCuenta)  $selCuenta.addEventListener('change', loadOwner);
  if ($selTipo)    $selTipo.addEventListener('change',   loadOwner);

  // dispara al cargar (por si el form ya trae valores)
  loadOwner();
})();
</script>

<?php $this->load->view('partial/footer'); ?>
