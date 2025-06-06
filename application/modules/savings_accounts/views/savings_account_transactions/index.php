<?php $this->load->view('partial/header'); ?>

<div class="title-block">
  <h3 class="title">Transacciones de Cajas de Ahorro</h3>
  <div class="clearfix"></div>
</div>

<div class="section">
  <a href="<?= site_url('savings_accounts/savings_account_transactions/form') ?>"
     class="btn btn-primary">
    <span class="glyphicon glyphicon-plus"></span> Nueva transacción
  </a>
  <br><br>
  <div class="inqbox-content table-responsive">
    <table class="table table-striped table-bordered">
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Cuenta</th>
          <th>Cliente</th>
          <th>Tipo</th>
          <th>Monto</th>
          <th>Saldo Resultante</th>
          <th>Descripción</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($transactions as $t): ?>
        <tr>
          <td><?= date('d/m/Y H:i',strtotime($t->trans_date)) ?></td>
          <td><?= htmlspecialchars($t->account_number) ?></td>
          <td><?= htmlspecialchars($t->first_name.' '.$t->last_name) ?></td>
          <td><?= ucfirst($t->trans_type) ?></td>
          <td class="text-right"><?= number_format($t->amount,2) ?></td>
          <td class="text-right">
            <?php 
              // calcular saldo histórico: fecha‐saldo no guardado; mostramos actual
              $acct = $this->Savings_accounts_model->get($t->savings_account_id);
              echo number_format($acct->current_balance,2);
            ?>
          </td>
          <td><?= htmlspecialchars($t->description) ?></td>
          <td>
            <a href="<?= site_url("savings_accounts/savings_account_transactions/form/{$t->transaction_id}") ?>"
               class="btn btn-xs btn-warning">✎</a>
            <a href="<?= site_url("savings_accounts/savings_account_transactions/delete/{$t->transaction_id}") ?>"
               class="btn btn-xs btn-danger"
               onclick="return confirm('¿Deshabilitar esta transacción?');">🗑</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php $this->load->view('partial/footer'); ?>
