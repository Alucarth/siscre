<?php
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$owner_name  = trim(($tx->first_name ?? '').' '.($tx->last_name ?? '')) ?: '—';
$owner_idno  = $tx->id_no ?: '—';
$branch_name = $tx->branch_name ?: '—';
$op_name     = trim(($tx->op_first ?? '').' '.($tx->op_last ?? '')) ?: '—';
$amount_fmt  = number_format((float)$tx->amount, 2, '.', ',');
$date_fmt    = date('d/m/Y H:i', strtotime($tx->trans_date));
$acc_no      = $tx->account_number ?: ('CA-'.$tx->savings_account_id);
$type_label  = $tx->trans_type === 'deposit' ? 'Depósito' : ($tx->trans_type === 'withdraw' ? 'Retiro' : 'Transferencia');
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    *{ font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 11pt; }
    .title{ font-weight: bold; font-size: 14pt; text-align:center; margin-bottom:6px; }
    .meta{ width:100%; border-collapse: collapse; margin-bottom:10px; }
    .meta td{ padding:4px; vertical-align: top; }
    .box{ border:1px solid #444; padding:8px; margin-bottom:10px; }
    .lab{ color:#555; width:160px; }
    .amount{ font-size: 16pt; font-weight:bold; text-align:center; border:1px dashed #666; padding:8px; }
    .footer{ font-size:9pt; text-align:center; color:#666; margin-top:10px; }
    .muted{ color:#777; }
    .row{ display:flex; gap:10px; }
    .col{ flex:1; }
  </style>
</head>
<body>

  <div class="title"><?= h($title) ?></div>

  <table class="meta">
    <tr>
      <td class="lab">N° Transacción:</td><td><?= (int)$tx->transaction_id ?></td>
      <td class="lab">Fecha/Hora:</td><td><?= h($date_fmt) ?></td>
    </tr>
    <tr>
      <td class="lab">Tipo:</td><td><?= h($type_label) ?></td>
      <td class="lab">Agencia:</td><td><?= h($branch_name) ?></td>
    </tr>
  </table>

  <div class="box">
    <div><strong>Cuenta:</strong> <?= h($acc_no) ?> <span class="muted">— <?= h($tx->account_type_name ?: 'Cuenta de ahorro') ?></span></div>
    <div><strong>Titular:</strong> <?= h($owner_name) ?> &nbsp; <span class="muted">(ID: <?= h($owner_idno) ?>)</span></div>
  </div>

  <?php if ($tx->trans_type === 'transfer' && !empty($tx->counterparty_account_id) && !empty($counter)): ?>
  <div class="box">
    <div><strong>Cuenta destino:</strong> <?= h($counter->account_number ?: ('CA-'.$tx->counterparty_account_id)) ?></div>
    <div><strong>Beneficiario:</strong> <?= h(trim(($counter->first_name ?? '').' '.($counter->last_name ?? '')) ?: '—') ?></div>
  </div>
  <?php endif; ?>

  <?php if ($tx->trans_type === 'deposit'): ?>
  <div class="box">
    <div><strong>Depositante:</strong> <?= h($tx->depositor_name ?: '—') ?></div>
    <div><strong>Documento:</strong> <?= h($tx->depositor_document ?: '—') ?></div>
  </div>
  <?php endif; ?>

  <div class="amount">
    Monto: <?= h($amount_fmt) ?>
  </div>

  <div class="box">
    <div><strong>Descripción:</strong> <?= h($tx->description ?: '—') ?></div>
  </div>

  <div class="row">
    <div class="col">
      <div class="box">
        <div class="muted">Operador</div>
        <div><?= h($op_name) ?></div>
      </div>
    </div>
    <div class="col">
      <div class="box">
        <div class="muted">Firma</div>
        <div style="height:40px;"></div>
      </div>
    </div>
  </div>

  <div class="footer">
    Impreso el <?= h($printed_at) ?> — <?= h($type_label) ?> · Sistema de Cajas de Ahorro
  </div>

</body>
</html>
