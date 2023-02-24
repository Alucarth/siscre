CREATE TABLE IF NOT EXISTS `c19_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `date_added` int(11) NOT NULL DEFAULT 0,
  `date_modified` int(11) NOT NULL DEFAULT 0,
  `added_by` int(11) NOT NULL DEFAULT 0,
  `account_type` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `modified_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
