CREATE TABLE IF NOT EXISTS `c19_savings_account_transactions` (
  `transaction_id`      INT(11)       NOT NULL AUTO_INCREMENT,
  `savings_account_id`  INT(11)       NOT NULL COMMENT 'FK → c19_savings_accounts.savings_account_id',
  `trans_type`          ENUM('deposit','withdraw','transfer') NOT NULL COMMENT 'Tipo de operación',
  `amount`              DECIMAL(12,2) NOT NULL COMMENT 'Monto de la transacción',
  `trans_date`          DATETIME      NOT NULL COMMENT 'Fecha y hora',
  `description`         VARCHAR(255)  DEFAULT NULL COMMENT 'Notas',
  `branch_id`           INT(11)       NOT NULL COMMENT 'FK → sucursal',
  `person_id`           INT(11)       NOT NULL COMMENT 'FK → quien registra',
  PRIMARY KEY (`transaction_id`),
  KEY `idx_sav_trans_account` (`savings_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
