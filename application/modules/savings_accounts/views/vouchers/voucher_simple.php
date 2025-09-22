<?php
$owner    = trim(($tx->first_name ?? '').' '.($tx->last_name ?? ''));
$operator = trim(($tx->op_first ?? '').' '.($tx->op_last ?? ''));
$map_type = ['deposit'=>'Depósito','withdraw'=>'Retiro','transfer'=>'Transferencia'];
$type_es  = $map_type[strtolower($tx->trans_type)] ?? ucfirst($tx->trans_type);
$printed  = date('d/m/Y H:i');

function voucher_html($badge, $tx, $owner, $operator, $type_es, $printed) {
  ob_start(); ?>
  <div>
    <!-- Encabezado: CREDISURGIR centrado, grande, negrita y subrayado -->
    <div style="text-align:center;font-weight:bold;font-size:18px;text-decoration:underline;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">
      CREDISURGIR
    </div>

    <!-- Título centrado, grande, negrita y subrayado -->
    <div style="text-align:left;font-weight:bold;font-size:16px;margin:6px 0 10px 0;">
      Comprobante de <?= htmlspecialchars($type_es) ?>
    </div>
    <br>

    <table width="100%" cellspacing="0" cellpadding="0" style="font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#000;">
      <tr>
        <td><b>Fecha:</b> <?= date('d/m/Y H:i', strtotime($tx->trans_date)) ?></td>
        <td align="right"><b>Sucursal:</b> <?= htmlspecialchars($tx->branch_name ?? '—') ?></td>
      </tr>
      <tr>
        <td><b>Cuenta:</b> <?= htmlspecialchars($tx->account_number) ?></td>
        <td align="right"><b>Tipo:</b> <?= htmlspecialchars($tx->account_type_name ?? '—') ?></td>
      </tr>
      <tr>
        <td colspan="2">
          <b>Titular:</b> <?= htmlspecialchars($owner) ?>
          &nbsp; <span style="opacity:.9;">(ID <?= htmlspecialchars($tx->id_no ?? '—') ?>)</span>
        </td>
      </tr>
      <tr>
        <td><b>Monto:</b> <?= number_format($tx->amount, 2) ?></td>
        <td align="right"><b>Operador:</b> <?= htmlspecialchars($operator) ?></td>
      </tr>
      <?php if (strtolower($tx->trans_type) === 'deposit'): ?>
      <tr>
        <td colspan="2">
          <b>Depositante:</b> <?= htmlspecialchars($tx->depositor_name ?? '—') ?><br>
          <b>Documento:</b> <?= htmlspecialchars($tx->depositor_document ?? '—') ?>
        </td>
      </tr>
      <?php endif; ?>
      <?php if (!empty($tx->description)): ?>
      <tr><td colspan="2"><b>Descripción:</b> <?= nl2br(htmlspecialchars($tx->description)) ?></td></tr>
      <?php endif; ?>
    </table>

    <!-- Firmas -->
    <table width="100%" cellspacing="0" cellpadding="0" style="margin-top:10px;">
      <tr>
        <td style="width:50%;text-align:center;border-top:1px dashed #333;padding-top:12px;">
          <br><br><br>
          <small>Cajero(a): <?= htmlspecialchars($operator) ?></small>
        </td>
        <td style="width:50%;text-align:center;border-top:1px dashed #333;padding-top:12px;">
          <br><br><br>
          <small>Cliente: <?= htmlspecialchars($owner) ?></small>
        </td>
      </tr>
    </table><br>

    <!-- Leyenda -->
    <div style="margin-top:8px;font-size:11px;">
      <em>Este comprobante es una constancia de la operación realizada.</em><br>
      <small>Impreso el <?= $printed ?> · Sistema de Cajas de Ahorro</small>
    </div>

    <!-- Badge al pie, derecha -->
    <div style="text-align:right;font-weight:bold;margin-top:4px;"><?= $badge ?></div>
  </div>
  <?php return ob_get_clean();
}
?>

<!-- Disposición en DOS COLUMNAS usando tabla (evita flex) -->
<table width="100%" cellspacing="0" cellpadding="0" style="font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#000;">
  <tr>
    <td width="50%" valign="top" style="padding:10px 12px 10px 10px;">
      <?= voucher_html('ORIGINAL', $tx, $owner, $operator, $type_es, $printed); ?>
    </td>
    <td width="50%" valign="top" style="padding:10px 10px 10px 12px;">
      <?= voucher_html('COPIA', $tx, $owner, $operator, $type_es, $printed); ?>
    </td>
  </tr>
</table>





