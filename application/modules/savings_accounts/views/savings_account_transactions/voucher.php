<?php
// Helpers sencillos
$fmt = function($n){ return number_format((float)$n, 2, '.', ','); };
$when = function($ts){ return date('Y-m-d H:i:s', strtotime($ts)); };

$label_tipo = ($tx->trans_type === 'deposit' ? 'Depósito' : ($tx->trans_type === 'withdraw' ? 'Retiro' : ($is_transfer ? 'Transferencia' : 'Transacción')));
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($voucher_title) ?> #<?= (int)$tx->transaction_id ?></title>
<style>
  body { font-family: Arial, Helvetica, sans-serif; color:#222; }
  .voucher { max-width: 760px; margin: 20px auto; border: 1px solid #ddd; border-radius: 6px; }
  .header { padding: 16px 20px; border-bottom: 1px solid #eee; display:flex; justify-content:space-between; align-items:center; }
  .brand h2 { margin:0; font-size: 20px; }
  .brand small { color:#666; }
  .meta { text-align:right; font-size: 13px; }
  .section { padding: 14px 20px; }
  .title { font-weight:bold; margin-bottom:6px; }
  .row { display:flex; gap:16px; }
  .col { flex:1; }
  .box { background:#fafafa; border:1px solid #eee; border-radius:6px; padding:12px; }
  .amount { font-size: 22px; font-weight: bold; }
  .muted { color:#777; }
  .table { width:100%; border-collapse: collapse; }
  .table th, .table td { border:1px solid #eee; padding:8px; font-size: 13px; }
  .footer { padding: 14px 20px; border-top:1px solid #eee; display:flex; justify-content:space-between; align-items:center; }
  .actions a { text-decoration:none; border:1px solid #ccc; border-radius:4px; padding:6px 10px; color:#333; }
  @media print {
    .actions { display:none; }
    .voucher { border:0; }
  }
</style>
</head>
<body>

<div class="voucher">
  <div class="header">
    <div class="brand">
      <h2><?= htmlspecialchars($company_name) ?></h2>
      <?php if (!empty($company_slogan)): ?>
      <small><?= htmlspecialchars($company_slogan) ?></small>
      <?php endif; ?>
    </div>
    <div class="meta">
      <div><strong><?= htmlspecialchars($label_tipo) ?></strong></div>
      <div>Comprobante #<?= (int)$tx->transaction_id ?></div>
      <div>Fecha: <?= $when($tx->trans_date) ?></div>
      <?php if (!empty($branch_name)): ?>
      <div>Sucursal: <?= htmlspecialchars($branch_name) ?></div>
      <?php endif; ?>
    </div>
  </div>

  <div class="section">
    <div class="title">Detalle de la operación</div>
    <div class="row">
      <div class="col">
        <div class="box">
          <div class="muted">Cuenta</div>
          <div><strong><?= htmlspecialchars($account_label) ?></strong></div>
          <div class="muted">Titular</div>
          <div><?= htmlspecialchars($owner_fullname) ?></div>
          <?php if (!empty($owner_idno)): ?>
          <div class="muted">Documento</div>
          <div><?= htmlspecialchars($owner_idno) ?></div>
          <?php endif; ?>
          <div class="muted">Tipo de cuenta</div>
          <div><?= htmlspecialchars($tx->account_type_name ?? '—') ?></div>
        </div>
      </div>

      <?php if ($is_transfer && $dst): ?>
      <div class="col">
        <div class="box">
          <div class="muted">Cuenta destino</div>
          <div><strong><?= htmlspecialchars($dst_label) ?></strong></div>
          <div class="muted">Beneficiario</div>
          <div><?= htmlspecialchars($dst_owner) ?></div>
          <?php if (!empty($dst_idno)): ?>
          <div class="muted">Documento</div>
          <div><?= htmlspecialchars($dst_idno) ?></div>
          <?php endif; ?>
          <div class="muted">Tipo de cuenta</div>
          <div><?= htmlspecialchars($dst->account_type_name ?? '—') ?></div>
        </div>
      </div>
      <?php endif; ?>

      <div class="col">
        <div class="box">
          <div class="muted">Importe</div>
          <div class="amount">Bs. <?= $fmt($tx->amount) ?></div>

          <?php if ($tx->trans_type === 'deposit' && (!empty($by_name) || !empty($by_doc))): ?>
            <div style="margin-top:10px" class="muted">Por cuenta de (depositante)</div>
            <div><?= htmlspecialchars($by_name ?: '—') ?></div>
            <?php if (!empty($by_doc)): ?><div>Doc: <?= htmlspecialchars($by_doc) ?></div><?php endif; ?>
          <?php endif; ?>

          <?php if (!is_null($before_balance) && !is_null($after_balance)): ?>
          <div style="margin-top:10px" class="muted">Saldo anterior / actual</div>
          <div>Bs. <?= $fmt($before_balance) ?> → <strong>Bs. <?= $fmt($after_balance) ?></strong></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($tx->description)): ?>
  <div class="section">
    <div class="title">Observaciones</div>
    <div class="box"><?= nl2br(htmlspecialchars($tx->description)) ?></div>
  </div>
  <?php endif; ?>

  <div class="section">
    <table class="table">
      <tr>
        <th style="width:35%">Transacción ID</th>
        <td>#<?= (int)$tx->transaction_id ?></td>
      </tr>
      <tr>
        <th>Registrado por</th>
        <td><?= htmlspecialchars($operator ?: '—') ?></td>
      </tr>
      <?php if (!empty($tx->ip_address)): ?>
      <tr>
        <th>IP Origen</th>
        <td><?= htmlspecialchars($tx->ip_address) ?></td>
      </tr>
      <?php endif; ?>
    </table>
  </div>

  <div class="footer">
    <div class="muted">Este comprobante es una constancia de la operación realizada.</div>
    <div class="actions">
      <a href="javascript:window.print()">Imprimir</a>
    </div>
  </div>
</div>

</body>
</html>
