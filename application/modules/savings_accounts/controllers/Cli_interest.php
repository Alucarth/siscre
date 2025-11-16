<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Cli_interest extends MX_Controller {
  public function __construct() {
    parent::__construct();
    if (!$this->input->is_cli_request()) show_404();
    $this->load->model('Savings_account_transactions_model','tx');
    $this->load->database();
    $this->config->load('savings', TRUE); // para day_count / rounding
  }

  // Vista previa (no escribe)
  public function preview($year=null,$month=null) {
    $dt = new DateTime('now');
    $y = $year  ? (int)$year  : (int)$dt->format('Y');
    $m = $month ? (int)$month : (int)$dt->format('n');

    $accs = $this->db->select('savings_account_id, account_number, status')
                 ->from($this->db->dbprefix('savings_accounts'))
                 ->where('status',1)->get()->result();

    foreach ($accs as $a) {
      $calc = $this->tx->calc_month_interest($a->savings_account_id, $y, $m);
      $amt = (float)($calc['amount'] ?? 0);
      if ($amt > 0) {
        echo "ACC {$a->savings_account_id} [{$a->account_number}] -> ".number_format($amt,2,'.','')."\n";
      }
    }
  }

  // Abono último día del mes
  public function run_month_end() {
    $tom = (new DateTime('tomorrow'))->format('d');
    if ($tom !== '01') { echo "Not month-end. Exit.\n"; return; }

    $now = new DateTime('now'); $y=(int)$now->format('Y'); $m=(int)$now->format('n');

    $lock = sys_get_temp_dir().'/siscre_interest.lock';
    $fp = fopen($lock, 'c');
    if (!$fp || !flock($fp, LOCK_EX|LOCK_NB)) { echo "Another run active\n"; return; }

    $accs = $this->db->select('savings_account_id, account_number, status')
                 ->from($this->db->dbprefix('savings_accounts'))
                 ->where('status',1)->get()->result();

    $ok=0;$skip=0;$err=0;
    foreach ($accs as $a) {
      $calc = $this->tx->calc_month_interest($a->savings_account_id, $y, $m);
      $amt  = (float)($calc['amount'] ?? 0);
      if ($amt <= 0) { $skip++; continue; }

      // Evitar duplicado del mismo mes
      $monthStr = sprintf('%04d-%02d', $y, $m);
      $dup = $this->db->select('COUNT(1) c')
        ->from($this->db->dbprefix('savings_account_transactions'))
        ->where('savings_account_id', (int)$a->savings_account_id)
        ->where("DATE_FORMAT(trans_date, '%Y-%m') =", $monthStr)
        ->like('description','Interés mensual')
        ->get()->row();
      if ((int)($dup->c ?? 0) > 0) { $skip++; continue; }

      // Inserta como depósito (flujo estándar → persiste balance_after)
      $payload = [
        'savings_account_id' => (int)$a->savings_account_id,
        'trans_type'         => 'deposit',
        'amount'             => $amt,
        'description'        => "Interés mensual {$monthStr}",
        'registered_by'      => 1,
        'status'             => 1,
        'trans_date'         => (new DateTime("last day of {$y}-{$m}"))->format('Y-m-d 23:59:59'),
        ];

      try {
        $this->tx->post_simple($payload);
        $ok++; echo "OK acc {$a->savings_account_id} +".number_format($amt,2,'.','')."\n";
      } catch (Throwable $e) {
        $err++; log_message('error','interest acc '.$a->savings_account_id.' -> '.$e->getMessage());
      }
    }

    @flock($fp, LOCK_UN); @fclose($fp); @unlink($lock);
    echo "DONE ok={$ok} skipped={$skip} err={$err}\n";
  }

  // Ejecuta el abono para el mes/año indicados (sin chequear fin de mes)
  public function run_for($year = null, $month = null) {
    $dt = new DateTime('now');
    $y = $year  ? (int)$year  : (int)$dt->format('Y');
    $m = $month ? (int)$month : (int)$dt->format('n');

    $accs = $this->db->select('savings_account_id, account_number, status')
                    ->from($this->db->dbprefix('savings_accounts'))
                    ->where('status',1)->get()->result();
    $ok=0;$skip=0;$err=0;
    foreach ($accs as $a) {
        $calc = $this->tx->calc_month_interest($a->savings_account_id, $y, $m);
        $amt  = (float)($calc['amount'] ?? 0);
        if ($amt <= 0) { $skip++; continue; }

        $monthStr = sprintf('%04d-%02d', $y, $m);
        $dup = $this->db->select('COUNT(1) c')
        ->from($this->db->dbprefix('savings_account_transactions'))
        ->where('savings_account_id', (int)$a->savings_account_id)
        ->where("DATE_FORMAT(trans_date, '%Y-%m') =", $monthStr)
        ->like('description','Interés mensual')
        ->get()->row();
        if ((int)($dup->c ?? 0) > 0) { $skip++; continue; }

        $payload = [
        'savings_account_id' => (int)$a->savings_account_id,
        'trans_type'         => 'deposit',
        'amount'             => $amt,
        'description'        => "Interés mensual {$monthStr}",
        'registered_by'      => 1,
        'status'             => 1,
        'trans_date'         => (new DateTime("last day of {$y}-{$m}"))->format('Y-m-d 23:59:59'),
        ];
        try { $this->tx->post_simple($payload); $ok++; echo "OK {$a->savings_account_id} +".number_format($amt,2,'.','')."\n"; }
        catch (Throwable $e) { $err++; log_message('error','interest acc '.$a->savings_account_id.' -> '.$e->getMessage()); }
    }
    echo "DONE ok={$ok} skipped={$skip} err={$err}\n";
    }

    // Refluir balances para todas las cuentas (o una cuenta específica)
    public function reflow($account_id = null) {
    $this->load->model('Savings_account_transactions_model','tx');

    if ($account_id) {
        $this->tx->rebuild_balance_after((int)$account_id);
        echo "Reflow OK account {$account_id}\n";
        return;
    }

    $accs = $this->db->select('savings_account_id')
            ->from($this->db->dbprefix('savings_accounts'))
            ->where('status',1)->get()->result();
    foreach ($accs as $a) {
        $this->tx->rebuild_balance_after((int)$a->savings_account_id);
        echo "Reflow OK account {$a->savings_account_id}\n";
    }
    echo "DONE\n";
    }

}
