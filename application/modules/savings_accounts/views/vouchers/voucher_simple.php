<?php
$owner    = trim(($tx->first_name ?? '').' '.($tx->last_name ?? ''));
$operator = trim(($tx->op_first ?? '').' '.($tx->op_last ?? ''));
$map_type = ['deposit'=>'Depósito','withdraw'=>'Retiro','transfer'=>'Transferencia'];
$type_es  = $map_type[strtolower($tx->trans_type)] ?? ucfirst($tx->trans_type);
$printed  = date('d/m/Y H:i');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
/* --- Reset mínimo --- */
* { box-sizing: border-box; }
html, body { margin:0; padding:0; }
body { font-family: Arial, Helvetica, sans-serif; color:#000; font-size:12px; }

/* Evitar cortes absurdos */
table { border-collapse: collapse; width:100%; }
table, tr, td, th { page-break-inside: avoid; }

/* Cabecera */
.brand{
  text-align:center; font-weight:bold; font-size:18px; text-decoration:underline;
  letter-spacing:.5px; margin: 0 0 6px 0;
}
.title{
  text-align:center; font-weight:bold; font-size:15px; text-decoration:underline;
  margin: 6px 0 10px 0;
}

/* Cuerpo */
.kv td{ padding:2px 0; vertical-align:top; }
.right{ text-align:right; }

/* Caja (sin bordes gruesos para no forzar alturas) */
.wrap { padding:8px 10px; border:1px solid #ddd; border-radius:6px; }

/* Firmas (sin alturas fijas) */
.sign { margin-top:10px; }
.sign td { width:50%; text-align:center; padding-top:18px; }
.line { border-top:1px dashed #333; padding-top:8px; }

/* Pie */
.foot { margin-top:8px; font-size:11px; }
</style>
</head>
<body>

<div class="brand">CREDISURGIR</div>
<div class="title">Comprobante de <?= htmlspecialchars($type_es) ?></div>

<div class="wrap">
  <table class="kv">
    <tr>
      <td><b>Fecha:</b> <?= date('d/m/Y H:i', strtotime($tx->trans_date)) ?></td>
      <td class="right"><b>Sucursal:</b> <?= htmlspecialchars($tx->branch_name ?? '—') ?></td>
    </tr>
    <tr>
      <td><b>Cuenta:</b> <?= htmlspecialchars($tx->account_number) ?></td>
      <td class="right"><b>Tipo:</b> <?= htmlspecialchars($tx->account_type_name ?? '—') ?></td>
    </tr>
    <tr>
      <td colspan="2">
        <b>Titular:</b> <?= htmlspecialchars($owner) ?>
        &nbsp; <span>(ID <?= htmlspecialchars($tx->id_no ?? '—') ?>)</span>
      </td>
    </tr>
    <tr>
      <td><b>Monto:</b> <?= number_format((float)$tx->amount, 2) ?></td>
      <td class="right"><b>Operador:</b> <?= htmlspecialchars($operator) ?></td>
    </tr>

    <?php if (strtolower($tx->trans_type) === 'deposit'): ?>
    <tr>
      <td><b>Depositante:</b> <?= htmlspecialchars($tx->depositor_name ?? '—') ?></td>
      <td class="right"><b>Documento:</b> <?= htmlspecialchars($tx->depositor_document ?? '—') ?></td>
    </tr>
    <?php endif; ?>

    <?php if (!empty($tx->description)): ?>
    <tr>
      <td colspan="2"><b>Descripción:</b> <?= nl2br(htmlspecialchars($tx->description)) ?></td>
    </tr>
    <?php endif; ?>
  </table>
</div><br><br>

<table class="sign">
  <tr>
    <td>
      <div class="line"><small>Cajero(a): <?= htmlspecialchars($operator) ?></small></div>
    </td>
    <td>
      <div class="line"><small>Cliente: <?= htmlspecialchars($owner) ?></small></div>
    </td>
  </tr>
</table>

<div class="foot">
  <em>Este comprobante es una constancia de la operación realizada.</em><br>
  <small>Impreso el <?= $printed ?> · Sistema de Cajas de Ahorro</small>
</div>

</body>
</html>








