-- Script de instalación para el módulo independiente "Cajas de Ahorro"
-- Crea las tablas y registra el complemento en las tablas de configuración

-- 1) Crear tabla de tipos de cuentas si no existe
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

-- 2) Crear tabla de cuentas de ahorro individuales
CREATE TABLE IF NOT EXISTS `c19_savings_accounts` (
  `savings_account_id` INT(11) NOT NULL AUTO_INCREMENT,
  `person_id` INT(11) NOT NULL,
  `account_type_id` INT(11) NOT NULL,
  `account_number` VARCHAR(20) NOT NULL,
  `opening_date` DATE NOT NULL,
  `initial_balance` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `current_balance` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `interest_rate` DECIMAL(5,2) NOT NULL DEFAULT '0.00',
  `comments` TEXT,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `date_added` INT(11) NOT NULL DEFAULT 0,
  `added_by` INT(11) NOT NULL DEFAULT 0,
  `date_modified` INT(11) NOT NULL DEFAULT 0,
  `modified_by` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`savings_account_id`),
  KEY `idx_person` (`person_id`),
  KEY `idx_type` (`account_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3) Crear tabla de transacciones de ahorro
CREATE TABLE IF NOT EXISTS `c19_savings_transactions` (
  `transaction_id` INT(11) NOT NULL AUTO_INCREMENT,
  `savings_account_id` INT(11) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `trans_type` VARCHAR(20) NOT NULL COMMENT 'deposit, withdraw, transfer',
  `trans_date` DATETIME NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `date_modified` DATETIME NOT NULL,
  `branch_id` INT(11) NOT NULL,
  `person_id` INT(11) NOT NULL,
  PRIMARY KEY (`transaction_id`),
  KEY `idx_savings_account` (`savings_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4) Registrar el módulo en c19_modules como complemento
INSERT INTO `c19_modules`
  (`module_id`
  ,`name_lang_key`
  ,`desc_lang_key`
  ,`sort`
  ,`icons`
  ,`is_active`
  ,`label`
  ,`description`
  ,`sub_menus`)
VALUES
  (
    'savings_accounts'                         -- module_id
   ,'module_savings_accounts'                  -- clave de idioma para el nombre
   ,'module_savings_accounts_desc'             -- clave de idioma para la descripción
   ,60                                         -- orden en el menú
   ,'<i class="fa fa-university"></i>'         -- icono
   ,1                                          -- is_active (1=activo)
   ,'Cajas de Ahorro'                          -- etiqueta literal
   ,'Asignación de Cuentas de Ahorro'         -- descripción literal
   ,'{"Tipos de Cuentas":"savings_account_types","Cuentas":"savings_accounts"}'  -- JSON de submenús
  )
ON DUPLICATE KEY UPDATE
   `is_active`  = VALUES(`is_active`),
   `sort`       = VALUES(`sort`),
   `icons`      = VALUES(`icons`),
   `label`      = VALUES(`label`),
   `description`= VALUES(`description`),
   `sub_menus`  = VALUES(`sub_menus`);


-- 5) Registrar los métodos del controlador en c19_plugins
INSERT INTO `c19_plugins` 
  (`module_name`,                    `module_desc`,               `module_settings`,                                       `status_flag`)
VALUES
  (
    'savings_account_types',         -- el ID de tu módulo de Tipos
    'Plugin para Tipos de Cuentas de Ahorro', 
    '{"methods":["index","form","delete"]}', 
    'Active'
  ),
  (
    'savings_accounts',              -- el ID de tu módulo de Cuentas individuales
    'Plugin para Cuentas de Ahorro', 
    '{"methods":["index","form","delete"]}', 
    'Active'
  ),
  (
    'savings_account_transactions',  -- el ID de tu módulo de Transacciones
    'Plugin para Transacciones de Ahorro', 
    '{"methods":["index","form","delete"]}', 
    'Active'
  )
ON DUPLICATE KEY UPDATE
  `status_flag` = VALUES(`status_flag`);

