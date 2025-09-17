<?php
$owner = trim(($tx->first_name ?? '').' '.($tx->last_name ?? ''));
?>
<div class="h1">Comprobante de <?= ucfirst($tx->trans_type) ?></div>
<div class="box">
  <table width="100%" cellspacing="0" cellpadding="4">
    <tr>
      <td><b>Fecha:</b> <?= date('d/m/Y H:i', strtotime($tx->trans_date)) ?></td>
      <td class="right"><b>Sucursal:</b> <?= htmlspecialchars($tx->branch_name ?? '—') ?></td>
    </tr>
    <tr>
      <td><b>Cuenta:</b> <?= htmlspecialchars($tx->account_number) ?></td>
      <td class="right"><b>Tipo de cuenta:</b> <?= htmlspecialchars($tx->account_type_name ?? '—') ?></td>
    </tr>
    <tr>
      <td colspan="2"><b>Titular:</b> <?= htmlspecialchars($owner) ?> &nbsp; <span class="muted">(ID <?= htmlspecialchars($tx->id_no ?? '—') ?>)</span></td>
    </tr>
    <tr>
      <td><b>Monto:</b> <?= number_format($tx->amount, 2) ?></td>
      <td class="right"><b>Operador:</b> <?= htmlspecialchars(trim(($tx->op_first ?? '').' '.($tx->op_last ?? ''))) ?></td>
    </tr>
    <?php if ($tx->trans_type === 'deposit'): ?>
    <tr>
      <td colspan="2">
        <b>Depositante:</b> <?= htmlspecialchars($tx->depositor_name ?? '—') ?>
        &nbsp;&nbsp; <b>Documento:</b> <?= htmlspecialchars($tx->depositor_document ?? '—') ?>
      </td>
    </tr>
    <?php endif; ?>
    <?php if (!empty($tx->description)): ?>
    <tr><td colspan="2"><b>Descripción:</b> <?= nl2br(htmlspecialchars($tx->description)) ?></td></tr>
    <?php endif; ?>
  </table>
</div>
