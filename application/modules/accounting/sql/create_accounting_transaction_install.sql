CREATE TABLE IF NOT EXISTS `c19_accounting_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_methods` varchar(100) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `added_date` datetime DEFAULT NULL,
  `purchased_date` datetime DEFAULT NULL,
  `purchased_amount` decimal(10,2) DEFAULT NULL,
  `added_by` int(11) DEFAULT NULL,
  `modified_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `modified_by` int(11) DEFAULT NULL,
  `transaction_type` varchar(100) DEFAULT NULL,
  `depreciate_amount` decimal(10,2) DEFAULT NULL,
  `branch_id` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;