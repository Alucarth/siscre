CREATE TABLE IF NOT EXISTS `c19_account_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `added_by` int(11) NOT NULL DEFAULT 0,
  `modified_by` int(11) NOT NULL DEFAULT 0,
  `account_id` int(11) NOT NULL DEFAULT 0,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `description` varchar(255) NOT NULL,
  `trans_type` varchar(50) NOT NULL COMMENT 'Deposit or Withdraw',
  `trans_date` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;