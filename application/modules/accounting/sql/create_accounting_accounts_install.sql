CREATE TABLE IF NOT EXISTS `c19_accounting_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code_number` varchar(100) NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `description` varchar(500) NOT NULL,
  `account_type` varchar(50) NOT NULL,
  `added_by` int(11) NOT NULL DEFAULT 0,
  `added_date` datetime NOT NULL,
  `modified_by` int(11) NOT NULL DEFAULT 0,
  `modified_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `account_map` varchar(50) DEFAULT NULL,
  `branch_id` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

