CREATE TABLE IF NOT EXISTS `c19_leads_application` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `leads_id` int(11) NOT NULL DEFAULT 0,
  `apply_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `loan_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `approve_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `loan_product_id` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL,
  `applied_date` datetime NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
