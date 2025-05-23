-- /application/modules/accounts/sql/001_create_account_types.sql
CREATE TABLE IF NOT EXISTS `c19_account_types` (
  `account_type_id` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `interest_rate` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `description` TEXT,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `date_added` INT(11) NOT NULL DEFAULT 0,
  `date_modified` INT(11) NOT NULL DEFAULT 0,
  `added_by` INT(11) NOT NULL DEFAULT 0,
  `modified_by` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`account_type_id`),
  UNIQUE KEY `ux_account_types_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
