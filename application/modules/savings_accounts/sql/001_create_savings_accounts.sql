-- 001_create_savings_accounts.sql
CREATE TABLE IF NOT EXISTS `c19_savings_accounts` (
  `savings_account_id`       INT(11)       NOT NULL AUTO_INCREMENT,
  `person_id`                INT(11)       NOT NULL COMMENT 'FK → cliente',
  `savings_account_type_id`  INT(11)       NOT NULL COMMENT 'FK → tipo de cuenta',
  `account_number`           VARCHAR(30)   NOT NULL COMMENT 'Código único de cuenta',
  `opening_date`             DATE          NOT NULL COMMENT 'Fecha de apertura',
  `maturity_date`            DATE          NULL COMMENT 'Fecha de vencimiento (si aplica)',
  `initial_balance`          DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Saldo inicial',
  `current_balance`          DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Saldo actual',
  `interest_rate`            DECIMAL(5,2)  NOT NULL DEFAULT 0.00 COMMENT 'Tasa anual al momento de apertura',
  `comments`                 TEXT          NULL COMMENT 'Observaciones',
  `status`                   TINYINT(1)    NOT NULL DEFAULT 1 COMMENT '1=Activa, 0=Cerrada',
  `date_added`               INT(11)       NOT NULL DEFAULT 0,
  `added_by`                 INT(11)       NOT NULL DEFAULT 0,
  `date_modified`            INT(11)       NOT NULL DEFAULT 0,
  `modified_by`              INT(11)       NOT NULL DEFAULT 0,
  PRIMARY KEY (`savings_account_id`),
  KEY `idx_sav_acct_person` (`person_id`),
  KEY `idx_sav_acct_type`   (`savings_account_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
