set foreign_key_checks=0;

-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.11-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             11.3.0.6295
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table matthias.c19_accounting_accounts
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
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table matthias.c19_accounting_accounts: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_accounting_accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `c19_accounting_accounts` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_accounting_transactions
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
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table matthias.c19_accounting_transactions: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_accounting_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `c19_accounting_transactions` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_accounts
CREATE TABLE IF NOT EXISTS `c19_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `date_added` int(11) NOT NULL DEFAULT 0,
  `date_modified` int(11) NOT NULL DEFAULT 0,
  `added_by` int(11) NOT NULL DEFAULT 0,
  `account_type` varchar(50) NOT NULL,
  `modified_by` int(11) NOT NULL DEFAULT 0,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table matthias.c19_accounts: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `c19_accounts` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_account_transactions
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

-- Dumping data for table matthias.c19_account_transactions: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_account_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `c19_account_transactions` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_activity_log
CREATE TABLE IF NOT EXISTS `c19_activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `foreign_id` int(11) DEFAULT NULL,
  `activity_type` varchar(500) NOT NULL,
  `description` varchar(500) NOT NULL,
  `log_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table matthias.c19_activity_log: ~7 rows (approximately)
/*!40000 ALTER TABLE `c19_activity_log` DISABLE KEYS */;
INSERT INTO `c19_activity_log` (`id`, `user_id`, `foreign_id`, `activity_type`, `description`, `log_date`) VALUES
	(1, 1, NULL, 'Branches', 'Viewed branches list', 1641647011),
	(2, 1, NULL, 'Branches', 'Viewed branches list', 1641647042),
	(3, 1, NULL, 'Branches', 'Added branch details #1', 1641647067),
	(4, 1, NULL, 'Branches', 'Viewed loan product details #1', 1641647068),
	(5, 1, NULL, 'Employees', 'Viewed employee: 1', 1641647074),
	(6, 1, NULL, 'Employees', 'Updated employee #1', 1641647081),
	(7, 1, NULL, 'Employees', 'Viewed employee: 1', 1641647082);
/*!40000 ALTER TABLE `c19_activity_log` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_app_config
CREATE TABLE IF NOT EXISTS `c19_app_config` (
  `key` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table matthias.c19_app_config: ~38 rows (approximately)
/*!40000 ALTER TABLE `c19_app_config` DISABLE KEYS */;
INSERT INTO `c19_app_config` (`key`, `value`) VALUES
	('address', '8F Orient Square Bldg. Ortigas, Pasig City'),
	('app_brand_name', 'Softreliance'),
	('app_brand_url', 'https://softreliance.com'),
	('app_logo', ''),
	('company', 'Softreliance'),
	('currency_decimal_separator', ''),
	('currency_num_decimal', ''),
	('currency_side', ''),
	('currency_symbol', 'Php'),
	('currency_thousand_separator', ''),
	('custom10_name', '0'),
	('custom1_name', '0'),
	('custom2_name', '0'),
	('custom3_name', '0'),
	('custom4_name', '0'),
	('custom5_name', '0'),
	('custom6_name', '0'),
	('custom7_name', '0'),
	('custom8_name', '0'),
	('custom9_name', '0'),
	('date_format', 'm/d/Y'),
	('default_tax_1_name', '0'),
	('default_tax_1_rate', '0'),
	('default_tax_2_name', '0'),
	('default_tax_2_rate', '0'),
	('email', 'support@softreliance.com'),
	('fax', ''),
	('language', 'en'),
	('language_used', 'au'),
	('logo', ''),
	('phone', '09989987626'),
	('print_after_sale', '0'),
	('recv_invoice_format', '0'),
	('return_policy', '0'),
	('sales_invoice_format', '0'),
	('tax_included', '0'),
	('timezone', 'Asia/Hong_Kong'),
	('website', 'https://softreliance.com');
/*!40000 ALTER TABLE `c19_app_config` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_attachments
CREATE TABLE IF NOT EXISTS `c19_attachments` (
  `attachment_id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `filename` varchar(100) NOT NULL,
  `descriptions` varchar(100) NOT NULL,
  `session_id` varchar(100) NOT NULL,
  PRIMARY KEY (`attachment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table matthias.c19_attachments: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `c19_attachments` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_branches
CREATE TABLE IF NOT EXISTS `c19_branches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch_name` varchar(200) NOT NULL,
  `descriptions` varchar(5000) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `modified_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `modified_by` int(11) DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table matthias.c19_branches: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_branches` DISABLE KEYS */;
INSERT INTO `c19_branches` (`id`, `branch_name`, `descriptions`, `created_date`, `created_by`, `modified_date`, `modified_by`) VALUES
	(1, 'Main Branch', NULL, NULL, NULL, '2022-01-08 21:12:43', 0);
/*!40000 ALTER TABLE `c19_branches` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_customers
CREATE TABLE IF NOT EXISTS `c19_customers` (
  `person_id` int(11) NOT NULL,
  `account_number` varchar(255) DEFAULT NULL,
  `credit_limit` decimal(10,2) NOT NULL,
  `taxable` int(11) NOT NULL DEFAULT 1,
  `deleted` int(11) NOT NULL DEFAULT 0,
  `added_by` int(11) DEFAULT NULL,
  `leads_id` int(11) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_account_num` varchar(100) DEFAULT NULL,
  `date_of_birth` int(11) DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  `modified_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `parent_id` int(11) DEFAULT 0,
  `added_date` datetime DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  UNIQUE KEY `account_number` (`account_number`),
  KEY `person_id` (`person_id`),
  CONSTRAINT `c19_customers_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `c19_people` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table matthias.c19_customers: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_customers` DISABLE KEYS */;
/*!40000 ALTER TABLE `c19_customers` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_customer_fields
CREATE TABLE IF NOT EXISTS `c19_customer_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `label` varchar(200) NOT NULL,
  `show_to_list` tinyint(1) NOT NULL DEFAULT 0,
  `data_type` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table matthias.c19_customer_fields: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_customer_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `c19_customer_fields` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_documents
CREATE TABLE IF NOT EXISTS `c19_documents` (
  `document_id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(100) NOT NULL,
  `document_name` varchar(100) DEFAULT NULL,
  `document_path` varchar(100) DEFAULT NULL,
  `descriptions` varchar(100) DEFAULT NULL,
  `session_id` varchar(100) NOT NULL,
  `added_date` datetime DEFAULT NULL,
  `modified_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `document_type` varchar(100) DEFAULT NULL,
  `foreign_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`document_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table matthias.c19_documents: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `c19_documents` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_emails
CREATE TABLE IF NOT EXISTS `c19_emails` (
  `email_id` int(11) NOT NULL AUTO_INCREMENT,
  `template_name` varchar(300) DEFAULT NULL,
  `templates` text DEFAULT NULL,
  `descriptions` text NOT NULL,
  `person_id` int(11) NOT NULL,
  PRIMARY KEY (`email_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- Dumping data for table matthias.c19_emails: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_emails` DISABLE KEYS */;
/*!40000 ALTER TABLE `c19_emails` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_employees
CREATE TABLE IF NOT EXISTS `c19_employees` (
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `person_id` int(11) NOT NULL,
  `added_by` int(11) NOT NULL,
  `deleted` int(11) NOT NULL DEFAULT 0,
  `can_approve_loan` tinyint(1) NOT NULL DEFAULT 0,
  `branch_ids` varchar(200) DEFAULT NULL,
  UNIQUE KEY `username` (`username`),
  KEY `person_id` (`person_id`),
  CONSTRAINT `c19_employees_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `c19_people` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table matthias.c19_employees: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_employees` DISABLE KEYS */;
INSERT INTO `c19_employees` (`username`, `password`, `person_id`, `added_by`, `deleted`, `can_approve_loan`, `branch_ids`) VALUES
	('admin', '0192023a7bbd73250516f069df18b500', 1, 1, 0, 0, '["1"]');
/*!40000 ALTER TABLE `c19_employees` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_financial_status
CREATE TABLE IF NOT EXISTS `c19_financial_status` (
  `financial_status_id` int(11) NOT NULL AUTO_INCREMENT,
  `person_id` int(11) NOT NULL,
  `income_sources` text NOT NULL,
  PRIMARY KEY (`financial_status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table matthias.c19_financial_status: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_financial_status` DISABLE KEYS */;
/*!40000 ALTER TABLE `c19_financial_status` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_grants
CREATE TABLE IF NOT EXISTS `c19_grants` (
  `permission_id` varchar(255) NOT NULL,
  `person_id` int(11) NOT NULL,
  PRIMARY KEY (`permission_id`,`person_id`),
  KEY `ospos_grants_ibfk_2` (`person_id`),
  CONSTRAINT `c19_grants_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `c19_permissions` (`permission_id`),
  CONSTRAINT `c19_grants_ibfk_2` FOREIGN KEY (`person_id`) REFERENCES `c19_employees` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table matthias.c19_grants: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_grants` DISABLE KEYS */;
/*!40000 ALTER TABLE `c19_grants` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_guarantee
CREATE TABLE IF NOT EXISTS `c19_guarantee` (
  `guarantee_id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(300) NOT NULL,
  `type` varchar(300) NOT NULL,
  `brand` varchar(50) NOT NULL,
  `make` varchar(50) NOT NULL,
  `serial` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `proof` varchar(1000) NOT NULL,
  `images` varchar(1000) NOT NULL,
  `observations` varchar(1000) NOT NULL,
  PRIMARY KEY (`guarantee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table matthias.c19_guarantee: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_guarantee` DISABLE KEYS */;
INSERT INTO `c19_guarantee` (`guarantee_id`, `loan_id`, `name`, `type`, `brand`, `make`, `serial`, `price`, `proof`, `images`, `observations`) VALUES
	(43, 1, '', '', '', '', '', 0.00, 'null', 'null', '');
/*!40000 ALTER TABLE `c19_guarantee` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_holidays
CREATE TABLE IF NOT EXISTS `c19_holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(500) NOT NULL,
  `description` text NOT NULL,
  `event_date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table matthias.c19_holidays: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_holidays` DISABLE KEYS */;
/*!40000 ALTER TABLE `c19_holidays` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_leads
CREATE TABLE IF NOT EXISTS `c19_leads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(300) NOT NULL,
  `email` varchar(300) NOT NULL,
  `id_type` varchar(300) DEFAULT NULL,
  `id_no` varchar(300) DEFAULT NULL,
  `gender` varchar(300) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `city` varchar(300) DEFAULT NULL,
  `address1` varchar(300) DEFAULT NULL,
  `street_no` varchar(300) DEFAULT NULL,
  `occupation` varchar(300) DEFAULT NULL,
  `company_name` varchar(300) DEFAULT NULL,
  `work_term` varchar(300) DEFAULT NULL,
  `net_monthly_income` decimal(10,2) DEFAULT NULL,
  `company_phone` varchar(300) DEFAULT NULL,
  `guarantor_phone` varchar(300) DEFAULT NULL,
  `pass_code` varchar(300) DEFAULT NULL,
  `added_date` datetime DEFAULT NULL,
  `modified_date` datetime DEFAULT NULL,
  `apply_amount` decimal(10,2) DEFAULT NULL,
  `default_interest_rate` decimal(10,2) DEFAULT NULL,
  `first_payment_date` date DEFAULT NULL,
  `service_fee` decimal(10,2) DEFAULT NULL,
  `service_fee_type` varchar(50) DEFAULT 'percentage',
  `completed_step` tinyint(4) DEFAULT 0,
  `active_step` tinyint(4) DEFAULT 0,
  `receive_promo_notifications` tinyint(1) DEFAULT 0,
  `front_side_img` varchar(200) DEFAULT NULL,
  `selfie_with_img` varchar(200) DEFAULT NULL,
  `bank_name` varchar(200) DEFAULT NULL,
  `account_holder_name` varchar(200) DEFAULT NULL,
  `account_number` varchar(200) DEFAULT NULL,
  `application_status` varchar(200) DEFAULT 'pending' COMMENT 'pending, approved, paid',
  `unpaid_amount` decimal(10,2) DEFAULT NULL,
  `term` int(11) DEFAULT NULL,
  `term_period` varchar(100) DEFAULT NULL,
  `signup_complete` tinyint(1) DEFAULT 0,
  `password` varchar(100) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `ubo` varchar(200) DEFAULT NULL,
  `cc_number` varchar(200) DEFAULT NULL,
  `vat_number` varchar(200) DEFAULT NULL,
  `back_side_img` varchar(200) DEFAULT NULL,
  `cc_img` varchar(200) DEFAULT NULL,
  `employer_id` int(11) DEFAULT NULL,
  `business_name` varchar(200) DEFAULT NULL,
  `business_address` varchar(200) DEFAULT NULL,
  `business_type` varchar(200) DEFAULT NULL,
  `business_nif` varchar(200) DEFAULT NULL,
  `business_legal_structure` varchar(200) DEFAULT NULL,
  `business_financial_institution` varchar(200) DEFAULT NULL,
  `business_account_name` varchar(200) DEFAULT NULL,
  `business_phone` varchar(200) DEFAULT NULL,
  `business_payroll_date` varchar(200) DEFAULT NULL,
  `business_total_employees` varchar(200) DEFAULT NULL,
  `business_agent_record` varchar(200) DEFAULT NULL,
  `business_patent_file` varchar(200) DEFAULT NULL,
  `country` varchar(200) DEFAULT NULL,
  `nationality` varchar(200) DEFAULT NULL,
  `marital_status` varchar(200) DEFAULT NULL,
  `gcash_num` varchar(200) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `first_name` varchar(300) NOT NULL,
  `last_name` varchar(300) NOT NULL,
  `middle_name` varchar(300) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table matthias.c19_leads: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_leads` DISABLE KEYS */;
/*!40000 ALTER TABLE `c19_leads` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_leads_application
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

-- Dumping data for table matthias.c19_leads_application: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_leads_application` DISABLE KEYS */;
/*!40000 ALTER TABLE `c19_leads_application` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_leads_articles
CREATE TABLE IF NOT EXISTS `c19_leads_articles` (
  `article_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `content` text NOT NULL,
  `added_date` int(11) NOT NULL,
  `added_by` int(11) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table matthias.c19_leads_articles: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_leads_articles` DISABLE KEYS */;
INSERT INTO `c19_leads_articles` (`article_id`, `title`, `content`, `added_date`, `added_by`, `published`) VALUES
	(12, 'About us', 'Type about us decription here', 1641095506, 1, 1);
/*!40000 ALTER TABLE `c19_leads_articles` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_loans
CREATE TABLE IF NOT EXISTS `c19_loans` (
  `loan_id` int(11) NOT NULL AUTO_INCREMENT,
  `account` varchar(50) NOT NULL,
  `description` varchar(300) NOT NULL,
  `remarks` varchar(300) NOT NULL,
  `loan_type_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `loan_amount` decimal(10,2) NOT NULL,
  `loan_balance` decimal(10,2) NOT NULL,
  `loan_status` varchar(50) DEFAULT NULL,
  `loan_agent_id` int(11) NOT NULL,
  `loan_approved_by_id` int(11) NOT NULL,
  `loan_reviewed_by_id` int(11) NOT NULL,
  `loan_applied_date` int(11) NOT NULL,
  `loan_payment_date` int(11) NOT NULL,
  `misc_fees` text NOT NULL,
  `delete_flag` int(11) NOT NULL,
  `payment_scheds` text NOT NULL,
  `periodic_loan_table` text NOT NULL,
  `apply_amount` decimal(10,2) NOT NULL,
  `interest_rate` decimal(10,2) NOT NULL,
  `interest_type` varchar(100) NOT NULL,
  `term_period` varchar(100) DEFAULT NULL,
  `payment_term` int(11) DEFAULT NULL,
  `payment_start_date` int(11) NOT NULL,
  `loan_approved_date` int(11) NOT NULL,
  `exclude_sundays` tinyint(4) NOT NULL DEFAULT 0,
  `penalty_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `penalty_type` varchar(50) DEFAULT 'percentage',
  `loan_product_id` int(11) NOT NULL,
  `net_proceeds` decimal(10,2) DEFAULT 0.00,
  `grace_periods` text DEFAULT NULL,
  `add_fees` text NOT NULL,
  `exclude_schedules` text NOT NULL,
  `exclude_additional_fees` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`loan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table matthias.c19_loans: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_loans` DISABLE KEYS */;
/*!40000 ALTER TABLE `c19_loans` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_loan_payments
CREATE TABLE IF NOT EXISTS `c19_loan_payments` (
  `loan_payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `account` varchar(50) NOT NULL DEFAULT '0',
  `loan_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `balance_amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL,
  `teller_id` int(11) NOT NULL,
  `date_paid` int(11) NOT NULL,
  `date_modified` int(11) NOT NULL,
  `modified_by` int(11) NOT NULL,
  `remarks` varchar(2000) NOT NULL,
  `delete_flag` int(11) NOT NULL DEFAULT 0,
  `payment_due` int(11) NOT NULL DEFAULT 0,
  `lpp_amount` decimal(10,2) DEFAULT 0.00 COMMENT 'Late Payment Penalty Amount',
  PRIMARY KEY (`loan_payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table matthias.c19_loan_payments: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_loan_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `c19_loan_payments` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_loan_products
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
  `grace_periods` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table matthias.c19_loan_products: ~3 rows (approximately)
/*!40000 ALTER TABLE `c19_loan_products` DISABLE KEYS */;
INSERT INTO `c19_loan_products` (`id`, `product_name`, `interest_rate`, `interest_type`, `term`, `term_period`, `penalty_value`, `penalty_type`, `date_added`, `date_modified`, `added_by`, `modified_by`, `description`, `grace_periods`) VALUES
	(3, 'SLPF30', 30.00, 'flat', 1, 'month', 10.00, 'percentage', 1638941679, 1638941836, 1, 1, 'Loan Term: 30 days\r\nInterest Type: Daily\r\nInterest Daily: 1', '{"1":{"period":"1","qty":"5","unit":"Days"}}'),
	(4, 'EmpLoan20%', 20.00, 'flat', 9, 'month', 5.00, 'percentage', 1639132110, NULL, 1, NULL, 'Employee Loan of 20% monthly interest', NULL),
	(5, '1dayLoan-PF(Test)', 10.00, 'flat', 1, 'day', 5.00, 'percentage', 1639239029, 1639239042, 1, 1, 'test loan with 1 day loan term', NULL);
/*!40000 ALTER TABLE `c19_loan_products` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_loan_types
CREATE TABLE IF NOT EXISTS `c19_loan_types` (
  `loan_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `term` int(11) NOT NULL,
  `term_period_type` varchar(50) NOT NULL,
  `payment_schedule` varchar(50) NOT NULL,
  `percent_charge1` decimal(10,2) NOT NULL,
  `period_charge1` int(11) NOT NULL,
  `period_type1` varchar(50) NOT NULL,
  `percent_charge2` decimal(10,2) NOT NULL,
  `period_charge2` int(11) NOT NULL,
  `period_type2` varchar(50) NOT NULL,
  `percent_charge3` decimal(10,2) NOT NULL,
  `period_charge3` int(11) NOT NULL,
  `period_type3` varchar(50) NOT NULL,
  `percent_charge4` decimal(10,2) NOT NULL,
  `period_charge4` int(11) NOT NULL,
  `period_type4` varchar(50) NOT NULL,
  `percent_charge5` decimal(10,2) NOT NULL,
  `period_charge5` int(11) NOT NULL,
  `period_type5` varchar(50) NOT NULL,
  `percent_charge6` decimal(10,2) NOT NULL,
  `period_charge6` int(11) NOT NULL,
  `period_type6` varchar(50) NOT NULL,
  `percent_charge7` decimal(10,2) NOT NULL,
  `period_charge7` int(11) NOT NULL,
  `period_type7` varchar(50) NOT NULL,
  `percent_charge8` decimal(10,2) NOT NULL,
  `period_charge8` int(11) NOT NULL,
  `period_type8` varchar(50) NOT NULL,
  `percent_charge9` decimal(10,2) NOT NULL,
  `period_charge9` int(11) NOT NULL,
  `period_type9` varchar(50) NOT NULL,
  `percent_charge10` decimal(10,2) NOT NULL,
  `period_charge10` int(11) NOT NULL,
  `period_type10` varchar(50) NOT NULL,
  `added_by` int(11) NOT NULL,
  `date_added` int(11) NOT NULL,
  `modified_by` int(11) NOT NULL,
  `date_modified` int(11) NOT NULL,
  PRIMARY KEY (`loan_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table matthias.c19_loan_types: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_loan_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `c19_loan_types` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_messages
CREATE TABLE IF NOT EXISTS `c19_messages` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL DEFAULT 0,
  `recipient_id` int(11) NOT NULL DEFAULT 0,
  `mark_as_read` tinyint(1) NOT NULL DEFAULT 0,
  `header` varchar(300) NOT NULL,
  `body` text NOT NULL,
  `send_date` date NOT NULL,
  `receive_date` date NOT NULL,
  `sender_delete_flag` tinyint(4) NOT NULL DEFAULT 0,
  `recipient_delete_flag` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table matthias.c19_messages: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_messages` DISABLE KEYS */;
INSERT INTO `c19_messages` (`message_id`, `sender_id`, `recipient_id`, `mark_as_read`, `header`, `body`, `send_date`, `receive_date`, `sender_delete_flag`, `recipient_delete_flag`) VALUES
	(1, 1, 17, 0, 'Testing', 'Hello, how are you?', '2021-12-29', '0000-00-00', 0, 0);
/*!40000 ALTER TABLE `c19_messages` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_modules
CREATE TABLE IF NOT EXISTS `c19_modules` (
  `module_id` varchar(255) NOT NULL,
  `name_lang_key` varchar(255) NOT NULL,
  `desc_lang_key` varchar(255) NOT NULL,
  `sort` int(11) NOT NULL,
  `icons` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `label` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `sub_menus` varchar(2000) NOT NULL,
  PRIMARY KEY (`module_id`),
  UNIQUE KEY `desc_lang_key` (`desc_lang_key`),
  UNIQUE KEY `name_lang_key` (`name_lang_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table matthias.c19_modules: ~23 rows (approximately)
/*!40000 ALTER TABLE `c19_modules` DISABLE KEYS */;
INSERT INTO `c19_modules` (`module_id`, `name_lang_key`, `desc_lang_key`, `sort`, `icons`, `is_active`, `label`, `description`, `sub_menus`) VALUES
	('accounting', 'module_accounting', 'module_accounting_desc', 60, '<i class=\'fa fa-balance-scale\'></i>', 1, 'Accounting', '', '{"Accounts":"accounts","Transactions":"transactions","Reports":"reports"}'),
	('accounts', 'module_accounts', 'module_accounts_desc', 60, '<i class=\'fa fa-list-alt\'></i>', 1, 'Accounts', '', '{"New Account":"view\\/-1","Account List":"index","Deposit":"deposit","Withdraw":"withdraw","My Transactions":"transactions"}'),
	('activity_log', 'module_activity_log', 'module_activity_log_desc', 60, '<i class=\'fa fa-file-o\'></i>', 1, 'Activity Log', '', 'null'),
	('branches', 'module_branches', 'module_branches_desc', 60, '<i class=\'fa fa-th-list\'></i>', 1, 'Branches', '', '{"New Branch":"view\\/-1","Branch List":"index"}'),
	('config', 'module_config', 'module_config_desc', 100, '<i class="fa fa-cogs"></i>', 1, '', '', ''),
	('customers', 'module_customers', 'module_customers_desc', 10, '<i class="fa fa-smile-o"></i>', 1, '', '', '{"New Item":"view/-1","Item List":"index"}'),
	('emails', 'module_email', 'module_email_desc', 0, '<i class="fa fa-envelope"></i>', 1, '', '', '{"New Item":"view/-1","Item List":"index"}'),
	('employees', 'module_employees', 'module_employees_desc', 80, '<i class="fa fa-users"></i>', 1, '', '', '{"New Item":"view/-1","Item List":"index"}'),
	('general_ledger', 'module_general_ledger', 'module_general_ledger_desc', 60, '<i class=\'fa fa-book\'></i>', 1, 'General Ledger', '', 'null'),
	('home', 'module_home', 'module_home_desc', 0, '<i class="fa fa-home"></i>', 1, 'Dashboard', '', ''),
	('leads', 'module_leads', 'module_leads_desc', 60, '<i class=\'fa fa-random\'></i>', 1, 'Leads', '', '{"View Site":"index","Articles":"articles","Registered Members":"members"}'),
	('loans', 'module_loans', 'module_loans_desc', 80, '<i class="fa fa-money"></i>', 1, '', '', '{"New Item":"view/-1","Item List":"index"}'),
	('loan_products', 'module_loan_products', 'module_loan_products_desc', 60, '<i class=\'fa fa-table\'></i>', 1, 'Loan Products', '', '{"New Product":"view\\/-1","Product List":"index"}'),
	('loan_types', 'module_loan_types', 'module_loan_types_desc', 79, '<i class="fa fa-sitemap"></i>', 0, '', '', '{"New Item":"view/-1","Item List":"index"}'),
	('messages', 'module_messages', 'module_messages_desc', 80, '<i class="fa fa-envelope"></i>', 1, '', '', '{"Compose":"view/-1","Inbox":"inbox","Outbox":"outbox"}'),
	('my_wallets', 'module_my_wallets', 'module_my_wallets_desc', 79, '<i class="fa fa-briefcase"></i>', 1, '', '', ''),
	('notifications', 'module_notifications', 'module_notifications_desc', 60, '<i class=\'fa fa-bell\'></i>', 1, 'Notifications', '', '{"New Notification":"view\\/-1","List":"index"}'),
	('overdues', 'module_overdues', 'module_overdues_desc', 80, '<i class="fa fa-file"></i>', 1, '', '', ''),
	('payments', 'module_payments', 'module_payments_desc', 80, '<i class="fa fa-paypal"></i>', 1, '', '', '{"New Item":"view/-1","Item List":"index"}'),
	('plugins', 'module_plugins', 'module_plugin_desc', 90, '<i class="fa fa-wrench"></i>', 1, '', '', ''),
	('reports', 'module_reports', 'module_reports_desc', 60, '<i class=\'fa fa-search\'></i>', 1, 'Reports', '', '{"Transaction Period":"index","Lost Payments":"lost_payments"}'),
	('roles', 'module_roles', 'module_roles_desc', 79, '<i class="fa fa-cogs"></i>', 1, '', '', '{"New Item":"view/-1","Item List":"index"}'),
	('support', 'module_support', 'module_support_desc', 60, '<i class=\'fa fa-bookmark\'></i>', 1, 'Support', '', '{"Support List":"main"}');
/*!40000 ALTER TABLE `c19_modules` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_notifications
CREATE TABLE IF NOT EXISTS `c19_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(300) DEFAULT NULL,
  `action_type` varchar(150) NOT NULL,
  `subject` varchar(300) DEFAULT NULL,
  `recipients` text DEFAULT NULL COMMENT 'user_type_ids',
  `from_name` varchar(200) DEFAULT NULL,
  `from_email` varchar(200) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `person_id` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- Dumping data for table matthias.c19_notifications: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `c19_notifications` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_payment_schedules
CREATE TABLE IF NOT EXISTS `c19_payment_schedules` (
  `payment_schedule_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `recurrence` int(11) NOT NULL DEFAULT 0,
  `delete_flag` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`payment_schedule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table matthias.c19_payment_schedules: ~4 rows (approximately)
/*!40000 ALTER TABLE `c19_payment_schedules` DISABLE KEYS */;
INSERT INTO `c19_payment_schedules` (`payment_schedule_id`, `name`, `recurrence`, `delete_flag`) VALUES
	(1, 'weekly', 0, 0),
	(2, 'biweekly', 0, 0),
	(3, 'monthly', 0, 0),
	(4, 'bimonthly', 0, 0);
/*!40000 ALTER TABLE `c19_payment_schedules` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_people
CREATE TABLE IF NOT EXISTS `c19_people` (
  `person_id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `photo_url` varchar(255) NOT NULL,
  `phone_number` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address_1` varchar(255) NOT NULL,
  `address_2` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `zip` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `comments` text NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table matthias.c19_people: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_people` DISABLE KEYS */;
INSERT INTO `c19_people` (`person_id`, `first_name`, `last_name`, `photo_url`, `phone_number`, `email`, `address_1`, `address_2`, `city`, `state`, `zip`, `country`, `comments`, `role_id`) VALUES
	(1, 'Admin', 'Admin', '', '', 'admin@softreliance.com', '4851 Valderama St. Brgy. Pio del Pilar', '', '', '', '', '', '', 13);
/*!40000 ALTER TABLE `c19_people` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_permissions
CREATE TABLE IF NOT EXISTS `c19_permissions` (
  `permission_id` varchar(255) NOT NULL,
  `module_id` varchar(255) NOT NULL,
  `location_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`permission_id`),
  KEY `module_id` (`module_id`),
  KEY `ospos_permissions_ibfk_2` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table matthias.c19_permissions: ~11 rows (approximately)
/*!40000 ALTER TABLE `c19_permissions` DISABLE KEYS */;
INSERT INTO `c19_permissions` (`permission_id`, `module_id`, `location_id`) VALUES
	('config', 'config', NULL),
	('customers', 'customers', NULL),
	('emails', 'emails', NULL),
	('employees', 'employees', NULL),
	('loans', 'loans', NULL),
	('loan_types', 'loan_types', NULL),
	('messages', 'messages', NULL),
	('my_wallets', 'my_wallets', NULL),
	('overdues', 'overdues', NULL),
	('payments', 'payments', NULL),
	('roles', 'roles', NULL);
/*!40000 ALTER TABLE `c19_permissions` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_plugins
CREATE TABLE IF NOT EXISTS `c19_plugins` (
  `plugin_id` int(11) NOT NULL AUTO_INCREMENT,
  `module_name` varchar(200) NOT NULL,
  `module_desc` varchar(200) NOT NULL,
  `module_settings` text NOT NULL,
  `status_flag` enum('Active','Inactive') NOT NULL DEFAULT 'Inactive',
  PRIMARY KEY (`plugin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table matthias.c19_plugins: ~17 rows (approximately)
/*!40000 ALTER TABLE `c19_plugins` DISABLE KEYS */;
INSERT INTO `c19_plugins` (`plugin_id`, `module_name`, `module_desc`, `module_settings`, `status_flag`) VALUES
	(1, 'klanguage', 'This plugin translate text to any languages', '{"addToPermissions":false,"addToGrants":false,"addToModules":false}', 'Active'),
	(2, 'accounting', 'Accounting plugin - integrate accounting on your Loan Management System', '{"addToPermissions":false,"addToGrants":false,"addToModules":true,"icon":"<i class=\'fa fa-balance-scale\'><\\/i>","sub_menus":{"Accounts":"accounts","Transactions":"transactions","Reports":"reports"},"label":"Accounting"}', 'Active'),
	(3, 'accounts', 'Accounts plugin allows your user to make deposit, withdraw to a pre-defined accounts on your Loan System.', '{"addToPermissions":false,"addToGrants":false,"addToModules":true,"icon":"<i class=\'fa fa-list-alt\'><\\/i>","sub_menus":{"New Account":"view\\/-1","Account List":"index","Deposit":"deposit","Withdraw":"withdraw","My Transactions":"transactions"},"label":"Accounts"}', 'Active'),
	(4, 'activity_log', 'Track the user activities', '{"addToPermissions":false,"addToGrants":false,"addToModules":true,"icon":"<i class=\'fa fa-file-o\'><\\/i>","label":"Activity Log"}', 'Active'),
	(5, 'branches', 'Branches plugin enables you to add branches for your store or company.', '{"addToPermissions":false,"addToGrants":false,"addToModules":true,"icon":"<i class=\'fa fa-th-list\'><\\/i>","sub_menus":{"New Branch":"view\\/-1","Branch List":"index"},"label":"Branches"}', 'Active'),
	(6, 'currency_formatter', 'This plugin allows you to have custom format of your currency', '{"addToPermissions":false,"addToGrants":false,"addToModules":false}', 'Active'),
	(7, 'general_ledger', 'General Ledger plugin - supplement of the accounting plugin', '{"addToPermissions":false,"addToGrants":false,"addToModules":true,"icon":"<i class=\'fa fa-book\'><\\/i>","label":"General Ledger"}', 'Active'),
	(8, 'holidays', 'This plugin gives you the option to exclude specific dates or holidays in the payment schedules', '{"addToPermissions":false,"addToGrants":false,"addToModules":true,"icon":"<i class=\'fa fa-gift\'><\\/i>","sub_menus":{"New Holiday":"view\\/-1","Holiday List":"index"},"label":"Holidays"}', 'Active'),
	(9, 'home', 'Advance dashboard plugin for K-Loans', '{"addToPermissions":false,"addToGrants":false,"addToModules":false}', 'Active'),
	(10, 'leads', 'Customer Dashboard Plugin', '{"addToPermissions":false,"addToGrants":false,"addToModules":true,"icon":"<i class=\'fa fa-random\'><\\/i>","sub_menus":{"View Site":"index","Articles":"articles","Registered Members":"members"},"label":"Leads"}', 'Active'),
	(11, 'loan_products', 'Loan products plugin enables you to predefined terms and interest rate etc.', '{"addToPermissions":false,"addToGrants":false,"addToModules":true,"icon":"<i class=\'fa fa-table\'><\\/i>","sub_menus":{"New Product":"view\\/-1","Product List":"index"},"label":"Loan Products"}', 'Active'),
	(12, 'loans', 'This plugin enables using different type of interest rate computations', '{"addToPermissions":false,"addToGrants":false,"addToModules":true,"icon":"<i class=\'fa fa-money\'><\\/i>","sub_menus":{"New Loan":"view\\/-1","Transactions":"index"},"label":"Loans"}', 'Active'),
	(13, 'notifications', 'Email notification plugin - setup notification template for your scheduler or cron job', '{"addToPermissions":false,"addToGrants":false,"addToModules":true,"icon":"<i class=\'fa fa-bell\'><\\/i>","sub_menus":{"New Notification":"view\\/-1","List":"index"},"label":"Notifications"}', 'Active'),
	(14, 'payments', 'This plugin overrides the old K-Loans payment functionality with much more added functionality', '{"addToPermissions":false,"addToGrants":false,"addToModules":false}', 'Active'),
	(15, 'reports', 'Reports plugin for K-Loans', '{"addToPermissions":false,"addToGrants":false,"addToModules":true,"icon":"<i class=\'fa fa-search\'><\\/i>","sub_menus":{"Transaction Period":"index","Lost Payments":"lost_payments"},"label":"Reports"}', 'Active'),
	(16, 'roles', 'Improved User Type permissions for K-loans', '{"addToPermissions":false,"addToGrants":false,"addToModules":true,"icon":"<i class=\'fa fa-lock\'><\\/i>","sub_menus":{"New Item":"view\\/-1","Item List":"index"}}', 'Active'),
	(17, 'support', 'Help your customer with Leads Support System plugin', '{"addToPermissions":false,"addToGrants":false,"addToModules":true,"icon":"<i class=\'fa fa-bookmark\'><\\/i>","sub_menus":{"Support List":"main"},"label":"Support"}', 'Active');
/*!40000 ALTER TABLE `c19_plugins` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_roles
CREATE TABLE IF NOT EXISTS `c19_roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `low_level` varchar(200) NOT NULL,
  `rights` text NOT NULL,
  `write_access` text NOT NULL,
  `added_by` int(11) NOT NULL,
  `edit_access` text NOT NULL,
  `delete_access` text NOT NULL,
  `approve_loan` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table matthias.c19_roles: ~3 rows (approximately)
/*!40000 ALTER TABLE `c19_roles` DISABLE KEYS */;
INSERT INTO `c19_roles` (`role_id`, `name`, `low_level`, `rights`, `write_access`, `added_by`, `edit_access`, `delete_access`, `approve_loan`) VALUES
	(13, 'admin', '["13","15"]', '["home","emails","customers","leads","reports","support","accounts","branches","accounting","activity_log","loan_products","notifications","general_ledger","roles","loan_types","my_wallets","loans","messages","overdues","payments","employees","plugins","config"]', '["home","emails","customers","leads","reports","support","accounts","branches","accounting","activity_log","loan_products","notifications","general_ledger","roles","loan_types","my_wallets","loans","messages","overdues","payments","employees","plugins","config"]', 1, '["home","emails","customers","leads","reports","support","accounts","branches","accounting","activity_log","loan_products","notifications","general_ledger","roles","loan_types","my_wallets","loans","messages","overdues","payments","employees","plugins","config"]', '["home","emails","customers","leads","reports","support","accounts","branches","accounting","activity_log","loan_products","notifications","general_ledger","roles","loan_types","my_wallets","loans","messages","overdues","payments","employees","plugins","config"]', 1),
	(15, 'Staff', 'false', '["customers","roles","loan_types","loan_products","branches"]', '', 1, '', '', 0),
	(16, 'Customer', 'null', '["home","customers","leads","branches"]', '["home"]', 1, '["home"]', '["home"]', 0);
/*!40000 ALTER TABLE `c19_roles` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_sessions
CREATE TABLE IF NOT EXISTS `c19_sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) NOT NULL DEFAULT '0',
  `user_agent` varchar(120) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT 0,
  `user_data` text DEFAULT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table matthias.c19_sessions: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `c19_sessions` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_smtp
CREATE TABLE IF NOT EXISTS `c19_smtp` (
  `smtp_id` int(11) NOT NULL AUTO_INCREMENT,
  `smtp_host` varchar(300) NOT NULL,
  `smtp_port` int(11) NOT NULL,
  `smtp_user` varchar(300) NOT NULL,
  `smtp_pass` varchar(300) NOT NULL,
  PRIMARY KEY (`smtp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table matthias.c19_smtp: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_smtp` DISABLE KEYS */;
INSERT INTO `c19_smtp` (`smtp_id`, `smtp_host`, `smtp_port`, `smtp_user`, `smtp_pass`) VALUES
	(1, '', 0, '', '');
/*!40000 ALTER TABLE `c19_smtp` ENABLE KEYS */;

-- Dumping structure for table matthias.c19_wallets
CREATE TABLE IF NOT EXISTS `c19_wallets` (
  `wallet_id` int(11) NOT NULL AUTO_INCREMENT,
  `amount` decimal(10,2) NOT NULL,
  `descriptions` varchar(200) NOT NULL,
  `wallet_type` enum('debit','credit','transfer') NOT NULL,
  `trans_date` int(11) NOT NULL,
  `added_by` int(11) NOT NULL,
  `transfer_to` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`wallet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table matthias.c19_wallets: ~0 rows (approximately)
/*!40000 ALTER TABLE `c19_wallets` DISABLE KEYS */;
/*!40000 ALTER TABLE `c19_wallets` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
