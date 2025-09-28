<?php
$map_type = ['deposit'=>'Depósito','withdraw'=>'Retiro'];
$printed  = date('d/m/Y H:i');

function box($title, $tx) {
  $owner = trim(($tx->first_name ?? '').' '.($tx->last_name ?? ''));
  $operator = trim(($tx->op_first ?? '').' '.($tx->op_last ?? ''));
  ob_start(); ?>
  <div style="border:1px solid #ddd;padding:10px;border-radius:6px;">
    <div style="text-align:center;font-weight:bold;font-size:18px;text-decoration:underline;margin-bottom:6px;">CREDISURGIR</div>
    <div style="font-weight:bold;font-size:16px;margin:6px 0 10px 0;">Comprobante de Transferencia — <?= $title ?></div>
    <table width="100%" cellpadding="0" cellspacing="0" style="font-size:12px;">
      <tr><td><b>Fecha:</b> <?= date('d/m/Y H:i', strtotime($tx->trans_date)) ?></td>
          <td align="right"><b>Sucursal:</b> <?= htmlspecialchars($tx->branch_name ?? '—') ?></td></tr>
      <tr><td><b>Cuenta:</b> <?= htmlspecialchars($tx->account_number) ?></td>
          <td align="right"><b>Monto:</b> <?= number_format((float)$tx->amount,2) ?></td></tr>
      <tr><td colspan="2"><b>Titular:</b> <?= htmlspecialchars($owner) ?> <span style="opacity:.9;">(ID <?= htmlspecialchars($tx->id_no ?? '—') ?>)</span></td></tr>
      <tr><td colspan="2"><b>Operador:</b> <?= htmlspecialchars($operator) ?></td></tr>
      <?php if (!empty($tx->description)): ?>
      <tr><td colspan="2"><b>Descripción:</b> <?= nl2br(htmlspecialchars($tx->description)) ?></td></tr>
      <?php endif; ?>
    </table>
    <table width="100%" style="margin-top:12px;">
      <tr>
        <td style="width:50%;text-align:center;border-top:1px dashed #333;padding-top:12px;"><br><br><small>Cajero(a): <?= htmlspecialchars($operator) ?></small></td>
        <td style="width:50%;text-align:center;border-top:1px dashed #333;padding-top:12px;"><br><br><small>Cliente: <?= htmlspecialchars($owner) ?></small></td>
      </tr>
    </table>
    <div style="margin-top:8px;font-size:11px;">
      <em>Este comprobante es una constancia de la operación realizada.</em><br>
      <small>Impreso el <?= $printed ?> · Sistema de Cajas de Ahorro</small>
    </div>
  </div>
  <?php return ob_get_clean();
}
?>
<table width="100%" cellspacing="0" cellpadding="0" style="font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#000;">
  <tr>
    <td width="50%" valign="top" style="padding:8px;"><?= box('ORIGEN (Retiro)', $w) ?></td>
    <td width="50%" valign="top" style="padding:8px;"><?= box('DESTINO (Depósito)', $d) ?></td>
  </tr>
</table>
