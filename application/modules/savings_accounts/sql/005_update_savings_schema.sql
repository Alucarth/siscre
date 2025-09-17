-- 005_update_savings_schema.sql
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;

-- =========================================================
-- 1) Ampliar esquema de c19_savings_account_transactions
--    (campos usados por depósitos/retiros/vouchers)
-- =========================================================
ALTER TABLE `c19_savings_account_transactions`
  ADD COLUMN IF NOT EXISTS `depositor_name`      VARCHAR(150) NOT NULL DEFAULT '' COMMENT 'Nombre de quien deposita' AFTER `description`,
  ADD COLUMN IF NOT EXISTS `depositor_document`  VARCHAR(50)  NOT NULL DEFAULT '' COMMENT 'Doc. de ID del depositante' AFTER `depositor_name`,
  ADD COLUMN IF NOT EXISTS `registered_by`       INT(11)      NOT NULL DEFAULT 0  COMMENT 'FK → c19_people.person_id (cajero)' AFTER `person_id`,
  ADD COLUMN IF NOT EXISTS `ip_address`          VARCHAR(45)  NOT NULL DEFAULT '' COMMENT 'IP/terminal' AFTER `registered_by`,
  ADD COLUMN IF NOT EXISTS `status`              TINYINT(1)   NOT NULL DEFAULT 1  COMMENT '1=Activo, 0=Inactivo (soft-delete)' AFTER `ip_address`,
  ADD COLUMN IF NOT EXISTS `date_added`          INT(11)      NOT NULL DEFAULT 0  COMMENT 'timestamp creación' AFTER `status`,
  ADD COLUMN IF NOT EXISTS `date_modified`       INT(11)      NOT NULL DEFAULT 0  COMMENT 'timestamp última mod.' AFTER `date_added`,
  ADD COLUMN IF NOT EXISTS `modified_by`         INT(11)      NOT NULL DEFAULT 0  COMMENT 'FK → quien modificó' AFTER `date_modified`;

-- =========================================================
-- 2) Índices para listados/búsquedas rápidas
-- =========================================================
DROP INDEX IF EXISTS idx_sat_acc_date        ON `c19_savings_account_transactions`;
CREATE INDEX        idx_sat_acc_date        ON `c19_savings_account_transactions` (`savings_account_id`,`trans_date`);

DROP INDEX IF EXISTS idx_sat_type_date       ON `c19_savings_account_transactions`;
CREATE INDEX        idx_sat_type_date       ON `c19_savings_account_transactions` (`trans_type`,`trans_date`);

DROP INDEX IF EXISTS idx_sat_branch_date     ON `c19_savings_account_transactions`;
CREATE INDEX        idx_sat_branch_date     ON `c19_savings_account_transactions` (`branch_id`,`trans_date`);

DROP INDEX IF EXISTS idx_sat_registered_date ON `c19_savings_account_transactions`;
CREATE INDEX        idx_sat_registered_date ON `c19_savings_account_transactions` (`registered_by`,`trans_date`);

DROP INDEX IF EXISTS idx_sat_status_date     ON `c19_savings_account_transactions`;
CREATE INDEX        idx_sat_status_date     ON `c19_savings_account_transactions` (`status`,`trans_date`);

-- Índices de apoyo en tablas relacionadas
DROP INDEX IF EXISTS idx_sa_person  ON `c19_savings_accounts`;
CREATE INDEX        idx_sa_person  ON `c19_savings_accounts` (`person_id`);

DROP INDEX IF EXISTS idx_people_name ON `c19_people`;
CREATE INDEX        idx_people_name ON `c19_people` (`last_name`,`first_name`);

DROP INDEX IF EXISTS idx_leads_idno ON `c19_leads`;
CREATE INDEX        idx_leads_idno ON `c19_leads` (`id_no`);

-- =========================================================
-- 3) Campos para cálculo de intereses
-- =========================================================
ALTER TABLE `c19_savings_account_types`
  ADD COLUMN IF NOT EXISTS `interest_rate_apy` DECIMAL(6,4) NOT NULL DEFAULT 0.0000 COMMENT 'Tasa efectiva anual';

ALTER TABLE `c19_savings_accounts`
  ADD COLUMN IF NOT EXISTS `last_interest_calc` DATE NULL COMMENT 'Último día liquidado';

-- =========================================================
-- 4) Vista consolidada para reportes/vouchers/listados
--    (nota: branches PK = c19_branches.id)
-- =========================================================
DROP VIEW IF EXISTS `v_savings_tx`;
CREATE OR REPLACE VIEW `v_savings_tx` AS
SELECT 
  tx.`transaction_id`,
  tx.`savings_account_id`,
  tx.`trans_type`,
  tx.`amount`,
  tx.`trans_date`,
  tx.`description`,
  tx.`depositor_name`,
  tx.`depositor_document`,
  tx.`branch_id`,
  tx.`person_id`      AS `owner_person_id`,
  tx.`registered_by`,
  tx.`ip_address`,
  tx.`status`,
  tx.`date_added`,
  tx.`date_modified`,
  tx.`modified_by`,
  sa.`account_number`,
  sa.`person_id`      AS `owner_id`,
  sat.`name`          AS `account_type_name`,
  CONCAT(p.`first_name`,' ',p.`last_name`) AS `owner_name`,
  l.`id_no`,
  b.`branch_name`,
  CONCAT(op.`first_name`,' ',op.`last_name`) AS `operator_name`
FROM `c19_savings_account_transactions` tx
LEFT JOIN `c19_savings_accounts`         sa  ON sa.`savings_account_id` = tx.`savings_account_id`
LEFT JOIN `c19_savings_account_types`    sat ON sat.`savings_account_type_id` = sa.`savings_account_type_id`
LEFT JOIN `c19_people`                   p   ON p.`person_id` = sa.`person_id`
LEFT JOIN `c19_leads`                    l   ON l.`customer_id` = sa.`person_id`
LEFT JOIN `c19_branches`                 b   ON b.`id` = tx.`branch_id`
LEFT JOIN `c19_people`                   op  ON op.`person_id` = tx.`registered_by`;

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;
