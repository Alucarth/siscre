-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 21-02-2022 a las 16:01:18
-- Versión del servidor: 5.7.36
-- Versión de PHP: 7.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `loans`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_app_config`
--

DROP TABLE IF EXISTS `c19_app_config`;
CREATE TABLE IF NOT EXISTS `c19_app_config` (
  `key` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `c19_app_config`
--

INSERT INTO `c19_app_config` (`key`, `value`) VALUES
('address', ''),
('app_brand_name', 'Sol Crédito'),
('app_brand_url', 'https://solcreditobo.com'),
('app_logo', 'Isologo_Solcrédito_color.png'),
('company', 'Sol Crédito'),
('currency_decimal_separator', ''),
('currency_num_decimal', ''),
('currency_side', 'currency_side'),
('currency_symbol', 'Bs.'),
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
('date_format', 'd/m/Y'),
('default_tax_1_name', '0'),
('default_tax_1_rate', '0'),
('default_tax_2_name', '0'),
('default_tax_2_rate', '0'),
('email', 'info@solcreditobo.com'),
('fax', ''),
('language', 'en'),
('language_used', 'es'),
('logo', 'o_1frabmo5n1tv0ged1cg4iuf1deu7.png'),
('phone', ''),
('print_after_sale', '0'),
('recv_invoice_format', '0'),
('return_policy', '0'),
('sales_invoice_format', '0'),
('tax_included', '0'),
('timezone', 'America/La_Paz'),
('website', 'https://solcreditobo.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_attachments`
--

DROP TABLE IF EXISTS `c19_attachments`;
CREATE TABLE IF NOT EXISTS `c19_attachments` (
  `attachment_id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `filename` varchar(100) NOT NULL,
  `descriptions` varchar(100) NOT NULL,
  `session_id` varchar(100) NOT NULL,
  PRIMARY KEY (`attachment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_customers`
--

DROP TABLE IF EXISTS `c19_customers`;
CREATE TABLE IF NOT EXISTS `c19_customers` (
  `person_id` int(11) NOT NULL,
  `account_number` varchar(255) DEFAULT NULL,
  `credit_limit` decimal(10,2) NOT NULL,
  `taxable` int(11) NOT NULL DEFAULT '1',
  `deleted` int(11) NOT NULL DEFAULT '0',
  `added_by` int(11) DEFAULT NULL,
  `leads_id` int(11) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_account_num` varchar(100) DEFAULT NULL,
  `date_of_birth` int(11) DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  `modified_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `parent_id` int(11) DEFAULT '0',
  `added_date` datetime DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `verificación_buró` varchar(255) DEFAULT NULL,
  UNIQUE KEY `account_number` (`account_number`),
  KEY `person_id` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `c19_customers`
--

INSERT INTO `c19_customers` (`person_id`, `account_number`, `credit_limit`, `taxable`, `deleted`, `added_by`, `leads_id`, `bank_name`, `bank_account_num`, `date_of_birth`, `date_added`, `modified_date`, `parent_id`, `added_date`, `password`, `branch_id`, `verificación_buró`) VALUES
(2, NULL, '0.00', 0, 0, 1, NULL, '', NULL, 0, '2022-01-31 20:41:40', '2022-01-31 20:44:10', -1, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_customer_fields`
--

DROP TABLE IF EXISTS `c19_customer_fields`;
CREATE TABLE IF NOT EXISTS `c19_customer_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `label` varchar(200) NOT NULL,
  `show_to_list` tinyint(1) NOT NULL DEFAULT '0',
  `data_type` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `c19_customer_fields`
--

INSERT INTO `c19_customer_fields` (`id`, `name`, `label`, `show_to_list`, `data_type`) VALUES
(1, 'verificación_buró', 'info_center_soap', 1, 'text');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_documents`
--

DROP TABLE IF EXISTS `c19_documents`;
CREATE TABLE IF NOT EXISTS `c19_documents` (
  `document_id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(100) NOT NULL,
  `document_name` varchar(100) DEFAULT NULL,
  `document_path` varchar(100) DEFAULT NULL,
  `descriptions` varchar(100) DEFAULT NULL,
  `session_id` varchar(100) NOT NULL,
  `added_date` datetime DEFAULT NULL,
  `modified_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `document_type` varchar(100) DEFAULT NULL,
  `foreign_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`document_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `c19_documents`
--

INSERT INTO `c19_documents` (`document_id`, `filename`, `document_name`, `document_path`, `descriptions`, `session_id`, `added_date`, `modified_date`, `document_type`, `foreign_id`) VALUES
(1, '', 'n_logo_sol_2.png', '/uploads/profile-2/n_logo_sol_2.png', 'Profile Photo', '', '2022-01-31 20:41:40', '2022-02-01 00:41:40', 'profile_photo', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_emails`
--

DROP TABLE IF EXISTS `c19_emails`;
CREATE TABLE IF NOT EXISTS `c19_emails` (
  `email_id` int(11) NOT NULL AUTO_INCREMENT,
  `template_name` varchar(300) DEFAULT NULL,
  `templates` text,
  `descriptions` text NOT NULL,
  `person_id` int(11) NOT NULL,
  PRIMARY KEY (`email_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_employees`
--

DROP TABLE IF EXISTS `c19_employees`;
CREATE TABLE IF NOT EXISTS `c19_employees` (
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `person_id` int(11) NOT NULL,
  `added_by` int(11) NOT NULL,
  `deleted` int(11) NOT NULL DEFAULT '0',
  `can_approve_loan` tinyint(1) NOT NULL DEFAULT '0',
  `branch_ids` varchar(200) DEFAULT NULL,
  UNIQUE KEY `username` (`username`),
  KEY `person_id` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `c19_employees`
--

INSERT INTO `c19_employees` (`username`, `password`, `person_id`, `added_by`, `deleted`, `can_approve_loan`, `branch_ids`) VALUES
('admin', '0192023a7bbd73250516f069df18b500', 1, 1, 0, 0, '[\"1\"]');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_financial_status`
--

DROP TABLE IF EXISTS `c19_financial_status`;
CREATE TABLE IF NOT EXISTS `c19_financial_status` (
  `financial_status_id` int(11) NOT NULL AUTO_INCREMENT,
  `person_id` int(11) NOT NULL,
  `income_sources` text NOT NULL,
  PRIMARY KEY (`financial_status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `c19_financial_status`
--

INSERT INTO `c19_financial_status` (`financial_status_id`, `person_id`, `income_sources`) VALUES
(1, 2, '[\"=\"]');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_grants`
--

DROP TABLE IF EXISTS `c19_grants`;
CREATE TABLE IF NOT EXISTS `c19_grants` (
  `permission_id` varchar(255) NOT NULL,
  `person_id` int(11) NOT NULL,
  PRIMARY KEY (`permission_id`,`person_id`),
  KEY `ospos_grants_ibfk_2` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_guarantee`
--

DROP TABLE IF EXISTS `c19_guarantee`;
CREATE TABLE IF NOT EXISTS `c19_guarantee` (
  `guarantee_id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_id` int(11) NOT NULL DEFAULT '0',
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
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `c19_guarantee`
--

INSERT INTO `c19_guarantee` (`guarantee_id`, `loan_id`, `name`, `type`, `brand`, `make`, `serial`, `price`, `proof`, `images`, `observations`) VALUES
(43, 1, '', '', '', '', '', '0.00', 'null', 'null', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_leads`
--

DROP TABLE IF EXISTS `c19_leads`;
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
  `completed_step` tinyint(4) DEFAULT '0',
  `active_step` tinyint(4) DEFAULT '0',
  `receive_promo_notifications` tinyint(1) DEFAULT '0',
  `front_side_img` varchar(200) DEFAULT NULL,
  `selfie_with_img` varchar(200) DEFAULT NULL,
  `bank_name` varchar(200) DEFAULT NULL,
  `account_holder_name` varchar(200) DEFAULT NULL,
  `account_number` varchar(200) DEFAULT NULL,
  `application_status` varchar(200) DEFAULT 'pending' COMMENT 'pending, approved, paid',
  `unpaid_amount` decimal(10,2) DEFAULT NULL,
  `term` int(11) DEFAULT NULL,
  `term_period` varchar(100) DEFAULT NULL,
  `signup_complete` tinyint(1) DEFAULT '0',
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `c19_leads`
--

INSERT INTO `c19_leads` (`id`, `full_name`, `email`, `id_type`, `id_no`, `gender`, `birth_date`, `city`, `address1`, `street_no`, `occupation`, `company_name`, `work_term`, `net_monthly_income`, `company_phone`, `guarantor_phone`, `pass_code`, `added_date`, `modified_date`, `apply_amount`, `default_interest_rate`, `first_payment_date`, `service_fee`, `service_fee_type`, `completed_step`, `active_step`, `receive_promo_notifications`, `front_side_img`, `selfie_with_img`, `bank_name`, `account_holder_name`, `account_number`, `application_status`, `unpaid_amount`, `term`, `term_period`, `signup_complete`, `password`, `customer_id`, `ubo`, `cc_number`, `vat_number`, `back_side_img`, `cc_img`, `employer_id`, `business_name`, `business_address`, `business_type`, `business_nif`, `business_legal_structure`, `business_financial_institution`, `business_account_name`, `business_phone`, `business_payroll_date`, `business_total_employees`, `business_agent_record`, `business_patent_file`, `country`, `nationality`, `marital_status`, `gcash_num`, `payment_method`, `first_name`, `last_name`, `middle_name`) VALUES
(1, 'Prueba Prueba apellido', 'gonzofranco@gmail.com', '1', '6098453', 'male', '0000-00-00', '', '', '', '', '', '', '0.00', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'percentage', 0, 0, 0, NULL, NULL, '', '', '', 'pending', NULL, NULL, NULL, 1, 'e10adc3949ba59abbe56e057f20f883e', 2, NULL, NULL, NULL, NULL, NULL, -1, '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, '', '', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_leads_application`
--

DROP TABLE IF EXISTS `c19_leads_application`;
CREATE TABLE IF NOT EXISTS `c19_leads_application` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `leads_id` int(11) NOT NULL DEFAULT '0',
  `apply_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `loan_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `approve_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `loan_product_id` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL,
  `applied_date` datetime NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `c19_leads_application`
--

INSERT INTO `c19_leads_application` (`id`, `leads_id`, `apply_amount`, `loan_amount`, `approve_amount`, `loan_product_id`, `loan_id`, `applied_date`, `status`) VALUES
(1, 2, '0.00', '0.00', '0.00', 0, 0, '2022-01-31 21:34:18', 'pending');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_leads_articles`
--

DROP TABLE IF EXISTS `c19_leads_articles`;
CREATE TABLE IF NOT EXISTS `c19_leads_articles` (
  `article_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `content` text NOT NULL,
  `added_date` int(11) NOT NULL,
  `added_by` int(11) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_loans`
--

DROP TABLE IF EXISTS `c19_loans`;
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
  `exclude_sundays` tinyint(4) NOT NULL DEFAULT '0',
  `penalty_value` decimal(10,2) NOT NULL DEFAULT '0.00',
  `penalty_type` varchar(50) DEFAULT 'percentage',
  `loan_product_id` int(11) NOT NULL,
  `net_proceeds` decimal(10,2) DEFAULT '0.00',
  `grace_periods` text,
  `add_fees` text NOT NULL,
  `exclude_schedules` text NOT NULL,
  `exclude_additional_fees` tinyint(4) DEFAULT '0',
  `notify_off` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`loan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_loan_payments`
--

DROP TABLE IF EXISTS `c19_loan_payments`;
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
  `delete_flag` int(11) NOT NULL DEFAULT '0',
  `payment_due` int(11) NOT NULL DEFAULT '0',
  `lpp_amount` decimal(10,2) DEFAULT '0.00' COMMENT 'Late Payment Penalty Amount',
  PRIMARY KEY (`loan_payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_loan_products`
--

DROP TABLE IF EXISTS `c19_loan_products`;
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
  `grace_periods` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `c19_loan_products`
--

INSERT INTO `c19_loan_products` (`id`, `product_name`, `interest_rate`, `interest_type`, `term`, `term_period`, `penalty_value`, `penalty_type`, `date_added`, `date_modified`, `added_by`, `modified_by`, `description`, `grace_periods`) VALUES
(3, 'SolCredito', '3.00', 'sol_credito', 6, 'month', '0.00', 'percentage', 1644350788, NULL, 1, NULL, '', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_loan_types`
--

DROP TABLE IF EXISTS `c19_loan_types`;
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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_messages`
--

DROP TABLE IF EXISTS `c19_messages`;
CREATE TABLE IF NOT EXISTS `c19_messages` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL DEFAULT '0',
  `recipient_id` int(11) NOT NULL DEFAULT '0',
  `mark_as_read` tinyint(1) NOT NULL DEFAULT '0',
  `header` varchar(300) NOT NULL,
  `body` text NOT NULL,
  `send_date` date NOT NULL,
  `receive_date` date NOT NULL,
  `sender_delete_flag` tinyint(4) NOT NULL DEFAULT '0',
  `recipient_delete_flag` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`message_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `c19_messages`
--

INSERT INTO `c19_messages` (`message_id`, `sender_id`, `recipient_id`, `mark_as_read`, `header`, `body`, `send_date`, `receive_date`, `sender_delete_flag`, `recipient_delete_flag`) VALUES
(1, 1, 17, 1, 'Testing', 'Hello, how are you?', '2021-12-29', '0000-00-00', 0, 0),
(2, 1, 0, 1, 'Prueba', 'Prueba 28012022', '2022-01-28', '0000-00-00', 0, 0),
(3, 1, 1, 1, 'RE: Prueba', '\n\n\n------ \n\nPrueba 28012022', '2022-01-31', '0000-00-00', 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_modules`
--

DROP TABLE IF EXISTS `c19_modules`;
CREATE TABLE IF NOT EXISTS `c19_modules` (
  `module_id` varchar(255) NOT NULL,
  `name_lang_key` varchar(255) NOT NULL,
  `desc_lang_key` varchar(255) NOT NULL,
  `sort` int(11) NOT NULL,
  `icons` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `label` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `sub_menus` varchar(2000) NOT NULL,
  PRIMARY KEY (`module_id`),
  UNIQUE KEY `desc_lang_key` (`desc_lang_key`),
  UNIQUE KEY `name_lang_key` (`name_lang_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `c19_modules`
--

INSERT INTO `c19_modules` (`module_id`, `name_lang_key`, `desc_lang_key`, `sort`, `icons`, `is_active`, `label`, `description`, `sub_menus`) VALUES
('config', 'module_config', 'module_config_desc', 100, '<i class=\"fa fa-cogs\"></i>', 1, '', '', ''),
('customers', 'module_customers', 'module_customers_desc', 10, '<i class=\"fa fa-smile-o\"></i>', 1, '', '', '{\"New Item\":\"view/-1\",\"Item List\":\"index\"}'),
('emails', 'module_email', 'module_email_desc', 0, '<i class=\"fa fa-envelope\"></i>', 1, '', '', '{\"New Item\":\"view/-1\",\"Item List\":\"index\"}'),
('employees', 'module_employees', 'module_employees_desc', 80, '<i class=\"fa fa-users\"></i>', 1, '', '', '{\"New Item\":\"view/-1\",\"Item List\":\"index\"}'),
('home', 'module_home', 'module_home_desc', 0, '<i class=\"fa fa-home\"></i>', 1, 'Dashboard', '', ''),
('leads', 'module_leads', 'module_leads_desc', 60, '<i class=\'fa fa-random\'></i>', 1, 'Leads', '', '{\"View Site\":\"index\",\"Articles\":\"articles\",\"Registered Members\":\"members\"}'),
('loans', 'module_loans', 'module_loans_desc', 80, '<i class=\"fa fa-money\"></i>', 1, '', '', '{\"New Item\":\"view/-1\",\"Item List\":\"index\"}'),
('loan_products', 'module_loan_products', 'module_loan_products_desc', 60, '<i class=\'fa fa-table\'></i>', 1, 'Loan Products', '', '{\"New Product\":\"view\\/-1\",\"Product List\":\"index\"}'),
('loan_types', 'module_loan_types', 'module_loan_types_desc', 79, '<i class=\"fa fa-sitemap\"></i>', 0, '', '', '{\"New Item\":\"view/-1\",\"Item List\":\"index\"}'),
('messages', 'module_messages', 'module_messages_desc', 80, '<i class=\"fa fa-envelope\"></i>', 1, '', '', '{\"Compose\":\"view/-1\",\"Inbox\":\"inbox\",\"Outbox\":\"outbox\"}'),
('my_wallets', 'module_my_wallets', 'module_my_wallets_desc', 79, '<i class=\"fa fa-briefcase\"></i>', 1, '', '', ''),
('overdues', 'module_overdues', 'module_overdues_desc', 80, '<i class=\"fa fa-file\"></i>', 1, '', '', ''),
('payments', 'module_payments', 'module_payments_desc', 80, '<i class=\"fa fa-paypal\"></i>', 1, '', '', '{\"New Item\":\"view/-1\",\"Item List\":\"index\"}'),
('plugins', 'module_plugins', 'module_plugin_desc', 90, '<i class=\"fa fa-wrench\"></i>', 1, '', '', ''),
('roles', 'module_roles', 'module_roles_desc', 79, '<i class=\"fa fa-cogs\"></i>', 1, '', '', '{\"New Item\":\"view/-1\",\"Item List\":\"index\"}');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_payment_schedules`
--

DROP TABLE IF EXISTS `c19_payment_schedules`;
CREATE TABLE IF NOT EXISTS `c19_payment_schedules` (
  `payment_schedule_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `recurrence` int(11) NOT NULL DEFAULT '0',
  `delete_flag` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`payment_schedule_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `c19_payment_schedules`
--

INSERT INTO `c19_payment_schedules` (`payment_schedule_id`, `name`, `recurrence`, `delete_flag`) VALUES
(1, 'weekly', 0, 0),
(2, 'biweekly', 0, 0),
(3, 'monthly', 0, 0),
(4, 'bimonthly', 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_people`
--

DROP TABLE IF EXISTS `c19_people`;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `c19_people`
--

INSERT INTO `c19_people` (`person_id`, `first_name`, `last_name`, `photo_url`, `phone_number`, `email`, `address_1`, `address_2`, `city`, `state`, `zip`, `country`, `comments`, `role_id`) VALUES
(1, 'Admin', 'Admin', '', '', 'admin@softreliance.com', '4851 Valderama St. Brgy. Pio del Pilar', '', '', '', '', '', '', 13),
(2, 'Prueba', 'Prueba apellido', 'n_logo_sol_2.png', '', 'gonzofranco@gmail.com', '', '', '', '', '', '', '', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_permissions`
--

DROP TABLE IF EXISTS `c19_permissions`;
CREATE TABLE IF NOT EXISTS `c19_permissions` (
  `permission_id` varchar(255) NOT NULL,
  `module_id` varchar(255) NOT NULL,
  `location_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`permission_id`),
  KEY `module_id` (`module_id`),
  KEY `ospos_permissions_ibfk_2` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `c19_permissions`
--

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_plugins`
--

DROP TABLE IF EXISTS `c19_plugins`;
CREATE TABLE IF NOT EXISTS `c19_plugins` (
  `plugin_id` int(11) NOT NULL AUTO_INCREMENT,
  `module_name` varchar(200) NOT NULL,
  `module_desc` varchar(200) NOT NULL,
  `module_settings` text NOT NULL,
  `status_flag` enum('Active','Inactive') NOT NULL DEFAULT 'Inactive',
  PRIMARY KEY (`plugin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `c19_plugins`
--

INSERT INTO `c19_plugins` (`plugin_id`, `module_name`, `module_desc`, `module_settings`, `status_flag`) VALUES
(1, 'klanguage', 'This plugin translate text to any languages', '{\"addToPermissions\":false,\"addToGrants\":false,\"addToModules\":false}', 'Active'),
(18, 'leads', 'Customer Dashboard Plugin', '{\"addToPermissions\":false,\"addToGrants\":false,\"addToModules\":true,\"icon\":\"<i class=\'fa fa-random\'><\\/i>\",\"sub_menus\":{\"View Site\":\"index\",\"Articles\":\"articles\",\"Registered Members\":\"members\"},\"label\":\"Leads\"}', 'Active'),
(19, 'loan_products', 'Loan products plugin enables you to predefined terms and interest rate etc.', '{\"addToPermissions\":false,\"addToGrants\":false,\"addToModules\":true,\"icon\":\"<i class=\'fa fa-table\'><\\/i>\",\"sub_menus\":{\"New Product\":\"view\\/-1\",\"Product List\":\"index\"},\"label\":\"Loan Products\"}', 'Active'),
(20, 'loans', 'This plugin enables using different type of interest rate computations', '{\"addToPermissions\":false,\"addToGrants\":false,\"addToModules\":true,\"icon\":\"<i class=\'fa fa-money\'><\\/i>\",\"sub_menus\":{\"New Loan\":\"view\\/-1\",\"Transactions\":\"index\"},\"label\":\"Loans\"}', 'Active');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_roles`
--

DROP TABLE IF EXISTS `c19_roles`;
CREATE TABLE IF NOT EXISTS `c19_roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `low_level` varchar(200) NOT NULL,
  `rights` text NOT NULL,
  `write_access` text NOT NULL,
  `added_by` int(11) NOT NULL,
  `edit_access` text NOT NULL,
  `delete_access` text NOT NULL,
  `approve_loan` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `c19_roles`
--

INSERT INTO `c19_roles` (`role_id`, `name`, `low_level`, `rights`, `write_access`, `added_by`, `edit_access`, `delete_access`, `approve_loan`) VALUES
(13, 'super admin', '[\"13\",\"15\",\"16\"]', '[\"home\",\"emails\",\"customers\",\"roles\",\"loan_types\",\"my_wallets\",\"loans\",\"messages\",\"overdues\",\"payments\",\"employees\",\"plugins\",\"config\",\"loan_products\"]', '[\"home\",\"emails\",\"customers\",\"leads\",\"reports\",\"support\",\"accounts\",\"branches\",\"accounting\",\"activity_log\",\"loan_products\",\"notifications\",\"general_ledger\",\"roles\",\"loan_types\",\"my_wallets\",\"loans\",\"messages\",\"overdues\",\"payments\",\"employees\",\"plugins\",\"config\"]', 1, '[\"home\",\"emails\",\"customers\",\"leads\",\"reports\",\"support\",\"accounts\",\"branches\",\"accounting\",\"activity_log\",\"loan_products\",\"notifications\",\"general_ledger\",\"roles\",\"loan_types\",\"my_wallets\",\"loans\",\"messages\",\"overdues\",\"payments\",\"employees\",\"plugins\",\"config\"]', '[\"home\",\"emails\",\"customers\",\"leads\",\"reports\",\"support\",\"accounts\",\"branches\",\"accounting\",\"activity_log\",\"loan_products\",\"notifications\",\"general_ledger\",\"roles\",\"loan_types\",\"my_wallets\",\"loans\",\"messages\",\"overdues\",\"payments\",\"employees\",\"plugins\",\"config\"]', 1),
(15, 'Cajero', 'null', '[\"home\",\"overdues\",\"payments\",\"loan_products\"]', '', 1, '', '', 0),
(16, 'Asesor', 'null', '[\"home\",\"customers\",\"leads\",\"loan_types\",\"my_wallets\",\"messages\",\"loan_products\"]', '[\"home\"]', 1, '[\"home\"]', '[\"home\"]', 0),
(17, 'Administrador', 'null', '[\"home\",\"emails\",\"customers\",\"loan_types\",\"my_wallets\",\"loans\",\"messages\",\"overdues\",\"payments\",\"employees\",\"plugins\",\"loan_products\"]', '', 1, '', '', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_sessions`
--

DROP TABLE IF EXISTS `c19_sessions`;
CREATE TABLE IF NOT EXISTS `c19_sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) NOT NULL DEFAULT '0',
  `user_agent` varchar(120) NOT NULL,
  `last_activity` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `user_data` text,
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_smtp`
--

DROP TABLE IF EXISTS `c19_smtp`;
CREATE TABLE IF NOT EXISTS `c19_smtp` (
  `smtp_id` int(11) NOT NULL AUTO_INCREMENT,
  `smtp_host` varchar(300) NOT NULL,
  `smtp_port` int(11) NOT NULL,
  `smtp_user` varchar(300) NOT NULL,
  `smtp_pass` varchar(300) NOT NULL,
  PRIMARY KEY (`smtp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `c19_smtp`
--

INSERT INTO `c19_smtp` (`smtp_id`, `smtp_host`, `smtp_port`, `smtp_user`, `smtp_pass`) VALUES
(1, 'mail.solcreditobo.com', 465, 'info@solcreditobo.com', 'mdLE2nI0Ub0o');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `c19_wallets`
--

DROP TABLE IF EXISTS `c19_wallets`;
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

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `c19_customers`
--
ALTER TABLE `c19_customers`
  ADD CONSTRAINT `c19_customers_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `c19_people` (`person_id`);

--
-- Filtros para la tabla `c19_employees`
--
ALTER TABLE `c19_employees`
  ADD CONSTRAINT `c19_employees_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `c19_people` (`person_id`);

--
-- Filtros para la tabla `c19_grants`
--
ALTER TABLE `c19_grants`
  ADD CONSTRAINT `c19_grants_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `c19_permissions` (`permission_id`),
  ADD CONSTRAINT `c19_grants_ibfk_2` FOREIGN KEY (`person_id`) REFERENCES `c19_employees` (`person_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
