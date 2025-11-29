<?php
$tz = $this->config->item('timezone');
if ($tz) {
    date_default_timezone_set($tz);
}
$owner    = trim(($tx->first_name ?? '').' '.($tx->last_name ?? ''));
$operator = trim(($tx->op_first ?? '').' '.($tx->op_last ?? ''));
$map_type = ['deposit'=>'Depósito','withdraw'=>'Retiro','transfer'=>'Transferencia'];
$type_es  = $map_type[strtolower((string)$tx->trans_type)] ?? ucfirst((string)$tx->trans_type);
$printed  = date('d/m/Y H:i');

$__is_deposit   = (strtolower((string)$tx->trans_type) === 'deposit');
$__is_withdraw  = (strtolower((string)$tx->trans_type) === 'withdraw');
$__has_dep_name = isset($tx->depositor_name) && trim((string)$tx->depositor_name) !== '';
$__has_dep_doc  = isset($tx->depositor_document) && trim((string)$tx->depositor_document) !== '';

$mov_label = '';
$cp_label  = '';
if (!empty($tx->transfer_group_id)) {
  $k = strtolower((string)($tx->transfer_kind ?? ''));
  if ($k === 'withdraw') { $mov_label = 'RETIRO';   $cp_label = 'DEPÓSITO'; }
  elseif ($k === 'deposit'){ $mov_label = 'DEPÓSITO'; $cp_label = 'RETIRO'; }
}

$is_preview = !empty($is_preview);
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <style>
      * { box-sizing: border-box; }
      html, body { margin:0; padding:0; }
      body { font-family: Arial, Helvetica, sans-serif; color:#000; font-size:13px;}

      table { border-collapse: collapse; width:100%; }
      table, tr, td, th { page-break-inside: avoid; }

      /* contenedor general del voucher, pegado a la izquierda */
      .voucher-left {
        /* ajusta esto si quieres más o menos ancho */
        width: 160mm;          /* o 75mm / 90mm, según como lo vea el cliente */
        padding: 10px 0;      /* aire vertical */
        margin: 0;            /* nada de centrado, pegado al borde izquierdo */
      }

      /* caja con borde y aire interno */
      .wrap {
        padding:10px 14px;
        border:1px solid #ddd;
        border-radius:6px;
      }

      /* encabezados */
      .brand{
        text-align:center;     /* si quieres que se vea distinto a otros vouchers */
        font-weight:bold;
        font-size:16px;
        text-decoration:underline;
        margin:0 0 4px 0;
      }
      .title{
        text-align:center;     /* también a la izquierda para reforzar diferencia */
        font-weight:bold;
        font-size:16px;
        text-decoration:underline;
        margin:2px 0 8px 0;
      }

      /* filas */
      .kv td{ padding:3px 0; vertical-align:top; }
      .right{ text-align:right; }

      /* firmas */
      .sign { margin-top:14px; }
      .sign td {
        width:50%;
        text-align:center;
        padding-top:14px;
        font-size:16px;
        vertical-align: top;
      }
      .line {
        border-top:1px dashed #333;
        padding-top:6px;
        padding-bottom:10px;
        height:55px;
      }

      .foot { margin-top:6px; font-size:11px; }

      <?php if ($is_preview): ?>
      body { font-size:14px; }
      .brand{ font-size:16px; }
      .title{ font-size:15px; }
      .sign,
      .foot {
        display: none !important;
      }
      <?php endif; ?>
    </style>
  </head>
  <body>
    <!-- AQUÍ reemplazamos el col-10/col-2 por un contenedor propio -->
    <div class="voucher-left">

      <div class="brand">CREDISURGIR</div>
      <div class="title">Comprobante de <?= htmlspecialchars($type_es) ?></div>

      <div class="wrap">
        <table class="kv">
          <tr>
            <td><b>Transacción Nº:</b> <?= (int)$tx->transaction_id ?></td>
            <td class="right"><b>Fecha:</b> <?= date('d/m/Y H:i', strtotime($tx->trans_date)) ?></td>
          </tr>
          <tr>
            <td><b>Sucursal:</b> <?= htmlspecialchars($tx->branch_name ?? '—') ?></td>
            <td class="right"><b>Cajero:</b> <?= htmlspecialchars($operator) ?></td>
          </tr>
          <tr>
            <td>
              <b>Cuenta<?= ($mov_label ? " {$mov_label}" : "") ?>:</b>
              <?= htmlspecialchars((string)$tx->account_number) ?>
            </td>
            <td class="right"><b>Tipo:</b> <?= htmlspecialchars($tx->account_type_name ?? '—') ?></td>
          </tr>
          <tr>
            <td colspan="2">
              <b>Titular:</b> <?= htmlspecialchars($owner) ?>
              <?php if (!empty($tx->id_no)): ?>
                &nbsp; <span>(CI <?= htmlspecialchars($tx->id_no) ?>)</span>
              <?php endif; ?>
            </td>
          </tr>

          <?php if (!empty($tx->transfer_group_id) && !empty($tx->counterparty_account_number)): ?>
          <tr>
            <td colspan="2">
              <b>Cuenta <?= htmlspecialchars($cp_label ?: '') ?>:</b>
              <?= htmlspecialchars($tx->counterparty_account_number) ?>
              <?php if (!empty($tx->counterparty_owner)): ?>
                &nbsp; <span>(<?= htmlspecialchars($tx->counterparty_owner) ?>)</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endif; ?>

          <tr><td colspan="2">&nbsp;</td></tr>

          <tr>
          <td colspan="2"><b>Monto:</b> <?= number_format((float)$tx->amount, 2) ?> Bs.</td>
        </tr>

        <?php if (!empty($tx->amount_literal)): ?>
        <tr>
          <td colspan="2"><b>Son:</b> <?= htmlspecialchars($tx->amount_literal) ?></td>
        </tr>
        <?php endif; ?>

        <?php if ($__is_deposit && ($__has_dep_name || $__has_dep_doc)): ?>
          <!-- Depósito: info del depositante -->
          <tr>
            <td><b>Depositante:</b> <?= htmlspecialchars($tx->depositor_name ?? '—') ?></td>
            <td class="right"><b>Documento:</b> <?= htmlspecialchars($tx->depositor_document ?? '—') ?></td>
          </tr>

        <?php elseif ($__is_withdraw): ?>
          <!-- Retiro: mostrar dueño de la cuenta con CI -->
          <tr>
            <td colspan="2">
              <b>Propietario de la cuenta:</b> <?= htmlspecialchars($owner) ?>
              <?php if (!empty($tx->id_no)): ?>
                &nbsp; <span>(CI <?= htmlspecialchars($tx->id_no) ?>)</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endif; ?>

        <?php if (!empty($tx->description)): ?>
        <tr>
          <td colspan="2"><b>Descripción:</b> <?= nl2br(htmlspecialchars($tx->description)) ?></td>
        </tr>
        <?php endif; ?>

        </table>
      </div>

      <?php
      // firmas
      if ($__is_deposit && $__has_dep_name) {
          $sig_label = 'Depositante';
          $sig_name  = trim((string)$tx->depositor_name);
          $sig_ci    = trim((string)($tx->depositor_document ?: $tx->id_no ?: '—'));
      } else {
          $sig_label = 'Cliente';
          $sig_name  = $owner;
          $sig_ci    = trim((string)($tx->id_no ?: '—'));
      }
      ?>
      <div style="height:32px;"></div>
      <table class="sign">
        <tr>
          <td>
            <div class="line">
              <small><?= htmlspecialchars($sig_label) ?>: <?= htmlspecialchars($sig_name) ?></small><br>
            </div>
            <div>
              <small>CI: <?= htmlspecialchars($sig_ci) ?></small>
            </div>
          </td>
          <td>
            <div class="line">
              <small>Firma y sello del cajero(a): </small>
            </div>
          </td>
        </tr>
      </table>

      <div class="foot">
        <em>Este comprobante acredita la operación realizada y <b>solo es válido con las firmas correspondientes.</b></em><br>
        <small>Impreso el <?= $printed ?> · SISCRE - Cajas de Ahorro</small>
      </div>
    </div>
  </body>
</html>

