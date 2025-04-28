CREATE TABLE IF NOT EXISTS `c19_loan_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_name` varchar(100) NOT NULL,
  `interest_rate` decimal(10,2) DEFAULT NULL,
  `interest_type` varchar(100) DEFAULT NULL,
  `term` int(11) DEFAULT NULL,
  `term_period` varchar(100) DEFAULT NULL,
  `penalty_value` decimal(10,2) DEFAULT NULL,
  `penalty_type` varchar(50) DEFAULT NULL,
  `date_added` int(11) DEFAULT NULL,
  `date_modified` int(11) DEFAULT NULL,
  `added_by` int(11) DEFAULT NULL,
  `modified_by` int(11) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `grace_periods` TEXT(65535) NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;