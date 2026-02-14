/*
 Navicat Premium Data Transfer

 Source Server         : siscre
 Source Server Type    : MySQL
 Source Server Version : 101111 (10.11.11-MariaDB-0+deb12u1)
 Source Host           : localhost:3306
 Source Schema         : credisurgir_db

 Target Server Type    : MySQL
 Target Server Version : 101111 (10.11.11-MariaDB-0+deb12u1)
 File Encoding         : 65001

 Date: 08/02/2026 16:42:12
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for c19_accounting_accounts
-- ----------------------------
DROP TABLE IF EXISTS `c19_accounting_accounts`;
CREATE TABLE `c19_accounting_accounts`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `code_number` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `account_name` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `description` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `account_type` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `added_by` int NOT NULL DEFAULT 0,
  `added_date` datetime NOT NULL,
  `modified_by` int NOT NULL DEFAULT 0,
  `modified_date` timestamp NOT NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  `account_map` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `branch_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 385 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = DYNAMIC;


INSERT INTO `credisurgir_db`.`c19_modules` (`module_id`, `name_lang_key`, `desc_lang_key`, `sort`, `icons`, `is_active`, `label`, `description`, `sub_menus`) VALUES ('general_book', 'module_general_book', 'module_general_book_desc', 61, '<i class=\"fa fa-book\"></i>', 1, 'Libro Mayor', 'Módulo para gestionar el libro mayor contable', '');

-- ----------------------------
-- Records of c19_accounting_accounts
-- ----------------------------
INSERT INTO `c19_accounting_accounts` VALUES (1, '1', 'ACTIVO', '', 'asset', 1, '2025-10-14 09:30:17', 1, '2025-10-14 09:30:17', '1', 1);
INSERT INTO `c19_accounting_accounts` VALUES (2, '11', 'ACTIVO CORRIENTE', '', 'asset', 1, '2025-10-14 09:30:17', 1, '2025-10-14 09:30:17', '11', 1);
INSERT INTO `c19_accounting_accounts` VALUES (3, '1101', 'DISPONIBLE', '', 'asset', 1, '2025-10-14 09:30:17', 99, '2026-01-27 12:47:16', '1101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (4, '110101', 'CAJA', '', 'asset', 1, '2025-10-14 09:30:17', 99, '2026-01-27 15:22:00', '110101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (5, '11010101', 'Caja moneda nacional', '', 'asset', 1, '2025-10-14 09:30:17', 99, '2026-01-27 15:26:12', '11010101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (7, '110104', 'BANCOS', '', 'asset', 1, '2025-10-14 09:30:17', 99, '2026-01-27 16:40:25', '110104', 5);
INSERT INTO `c19_accounting_accounts` VALUES (9, '1102', 'EXIGIBLE', '', 'asset', 1, '2025-10-14 09:30:17', 1, '2026-01-23 19:21:18', '1102', 1);
INSERT INTO `c19_accounting_accounts` VALUES (15, '1103', 'REALIZABLE', '', 'asset', 1, '2025-10-14 09:30:17', 1, '2026-01-23 19:21:18', '1103', 1);
INSERT INTO `c19_accounting_accounts` VALUES (17, '1104', 'GASTOS ANTICIPADOS', '', 'asset', 1, '2025-10-14 09:30:17', 1, '2026-01-23 19:21:18', '1104', 1);
INSERT INTO `c19_accounting_accounts` VALUES (20, '12', 'ACTIVO NO CORRIENTE', '', 'asset', 1, '2025-10-14 09:30:17', 1, '2025-10-14 09:30:17', '12', 1);
INSERT INTO `c19_accounting_accounts` VALUES (21, '1201', 'ACTIVO FIJO', '', 'asset', 1, '2025-10-14 09:30:17', 1, '2026-01-23 19:21:18', '1201', 1);
INSERT INTO `c19_accounting_accounts` VALUES (22, '12020401', 'Depreciación acumulada equipos de computación', '', 'asset', 1, '2025-10-14 09:30:17', 99, '2026-01-27 17:27:55', '12020401', 5);
INSERT INTO `c19_accounting_accounts` VALUES (58, '11060101', 'Préstamos amortizables vigentes moneda nacional', '', 'asset', 1, '2025-10-14 09:30:17', 99, '2026-01-27 15:44:56', '11060101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (69, '2', 'PASIVO', '', 'liability', 1, '2025-10-14 09:30:17', 1, '2026-01-31 11:53:11', '2', 1);
INSERT INTO `c19_accounting_accounts` VALUES (70, '21', 'PASIVO CORRIENTE', '', 'liability', 1, '2025-10-14 09:30:17', 1, '2026-01-31 11:53:17', '21', 1);
INSERT INTO `c19_accounting_accounts` VALUES (84, '21020101', 'Débito fiscal - IVA', '', 'liability', 1, '2025-10-14 09:30:17', 99, '2026-01-27 17:37:15', '21020101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (85, '21020102', 'Impuesto a las transacciones por pagar', '', 'liability', 1, '2025-10-14 09:30:17', 99, '2026-01-27 17:37:38', '21020102', 5);
INSERT INTO `c19_accounting_accounts` VALUES (86, '21020103', 'IUE por pagar', '', 'liability', 1, '2025-10-14 09:30:17', 99, '2026-01-27 17:38:38', '21020103', 5);
INSERT INTO `c19_accounting_accounts` VALUES (88, '21020104', 'Impuestos por pagar', '', 'liability', 1, '2025-10-14 09:30:17', 99, '2026-01-27 17:39:20', '21020104', 5);
INSERT INTO `c19_accounting_accounts` VALUES (97, '22', 'PASIVO NO CORRIENTE', '', 'liability', 1, '2025-10-14 09:30:17', 1, '2026-01-31 11:53:24', '22', 1);
INSERT INTO `c19_accounting_accounts` VALUES (98, '2201', 'DEUDAS A LARGO PLAZO', '', 'liability', 1, '2025-10-14 09:30:17', 99, '2026-01-27 16:34:15', '2201', 5);
INSERT INTO `c19_accounting_accounts` VALUES (110, '3', 'PATRIMONIO', '', 'equity', 1, '2025-10-14 09:30:17', 1, '2025-10-14 09:30:17', '3', 1);
INSERT INTO `c19_accounting_accounts` VALUES (111, '3101', 'CAPITAL', '', 'equity', 1, '2025-10-14 09:30:17', 99, '2026-01-27 16:18:50', '3101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (112, '310101', 'CAPITAL SOCIAL', '', 'equity', 1, '2025-10-14 09:30:17', 99, '2026-01-27 16:19:32', '310101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (114, '31010401', 'Ajuste de capital', '', 'equity', 1, '2025-10-14 09:30:17', 99, '2026-01-27 16:22:29', '31010401', 5);
INSERT INTO `c19_accounting_accounts` VALUES (115, '31010101', 'Capital social', '', 'equity', 1, '2025-10-14 09:30:17', 99, '2026-01-27 16:20:08', '31010101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (116, '3102', 'RESERVAS', '', 'equity', 1, '2025-10-14 09:30:17', 99, '2026-01-27 16:24:00', '3102', 5);
INSERT INTO `c19_accounting_accounts` VALUES (120, '3103', 'RESULTADOS', '', 'equity', 1, '2025-10-14 09:30:17', 99, '2026-01-27 16:27:02', '3103', 5);
INSERT INTO `c19_accounting_accounts` VALUES (121, '31030101', 'Resultados acumulados', '', 'equity', 1, '2025-10-14 09:30:17', 99, '2026-01-27 16:30:32', '31030101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (122, '31030201', 'Resultados de la gestión', '', 'equity', 1, '2025-10-14 09:30:17', 99, '2026-01-27 16:32:02', '31030201', 5);
INSERT INTO `c19_accounting_accounts` VALUES (124, '4', 'INGRESOS', '', 'income', 1, '2025-10-14 09:30:17', 1, '2025-10-14 09:30:17', '4', 1);
INSERT INTO `c19_accounting_accounts` VALUES (136, '5', 'EGRESOS', '', 'expenses', 1, '2025-10-14 09:30:17', 1, '2025-10-14 09:30:17', '5', 1);
INSERT INTO `c19_accounting_accounts` VALUES (147, '52', 'GASTOS OPERATIVOS', '', 'expenses', 1, '2025-10-14 09:30:17', 99, '2026-01-27 19:06:27', '52', 5);
INSERT INTO `c19_accounting_accounts` VALUES (148, '52010101', 'Sueldos y salarios', '', 'expenses', 1, '2025-10-14 09:30:17', 99, '2026-01-27 19:17:10', '52010101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (168, '54', 'GASTOS FINANCIEROS', '', 'expenses', 1, '2025-10-14 09:30:17', 99, '2026-01-27 19:07:09', '54', 5);
INSERT INTO `c19_accounting_accounts` VALUES (174, '53', 'OTROS GASTOS', '', 'expenses', 1, '2025-10-14 09:30:17', 99, '2026-01-27 19:07:18', '53', 5);
INSERT INTO `c19_accounting_accounts` VALUES (184, '55', 'IMPUESTO SOBRE LAS UTILIDADES', '', 'expenses', 1, '2025-10-14 09:30:17', 99, '2026-01-27 19:08:19', '55', 5);
INSERT INTO `c19_accounting_accounts` VALUES (190, '11020201', 'Crédito Fiscal IVA', 'Impuestos nacionales el 13%', 'asset', 99, '2025-10-28 12:59:40', 99, '2026-01-27 17:03:12', '11020201', 5);
INSERT INTO `c19_accounting_accounts` VALUES (191, '12010301', 'Muebles y Enseres', 'mesa, silla', 'asset', 99, '2025-10-28 13:21:19', 99, '2026-01-27 17:13:23', '12010301', 5);
INSERT INTO `c19_accounting_accounts` VALUES (195, '12010401', 'Equipos de computación', 'equipos de computacion de oficina', 'asset', 99, '2025-11-21 17:28:20', 99, '2026-01-27 17:13:44', '12010401', 5);
INSERT INTO `c19_accounting_accounts` VALUES (208, '11020202', 'IUE por compensar', '', 'asset', 1, '2026-01-23 19:21:14', 99, '2026-01-27 17:03:31', '11020202', 5);
INSERT INTO `c19_accounting_accounts` VALUES (215, '12010201', 'Vehículos', '', 'asset', 1, '2026-01-23 19:21:14', 99, '2026-01-27 17:14:15', '12010201', 5);
INSERT INTO `c19_accounting_accounts` VALUES (219, '12020101', 'Depreciación acumulada edificios', '', 'asset', 1, '2026-01-23 19:21:14', 99, '2026-01-27 17:26:11', '12020101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (220, '12020201', 'Depreciación acumulada vehículos', '', 'asset', 1, '2026-01-23 19:21:14', 99, '2026-01-27 17:26:33', '12020201', 5);
INSERT INTO `c19_accounting_accounts` VALUES (221, '12020301', 'Depreciación acumulada muebles y enseres', '', 'asset', 1, '2026-01-23 19:21:14', 99, '2026-01-27 17:27:02', '12020301', 5);
INSERT INTO `c19_accounting_accounts` VALUES (251, '52010102', 'Honorarios profesionales', '', 'expenses', 1, '2026-01-23 19:21:14', 99, '2026-01-27 19:17:45', '52010102', 5);
INSERT INTO `c19_accounting_accounts` VALUES (267, '1105', 'INVERSIONES', '', 'asset', 1, '2026-01-23 19:21:18', 1, '2026-01-23 19:21:18', '1105', 1);
INSERT INTO `c19_accounting_accounts` VALUES (272, '1106', 'CARTERA VIGENTE', '', 'asset', 99, '2026-01-27 15:42:33', 0, '2026-01-27 15:42:33', '1106', 5);
INSERT INTO `c19_accounting_accounts` VALUES (273, '110601', 'PRÉSTAMOS AMORTIZABLES VIGENTES', '', 'asset', 99, '2026-01-27 15:44:23', 0, '2026-01-27 15:44:23', '110601', 5);
INSERT INTO `c19_accounting_accounts` VALUES (275, '31', 'PATRIMONIO NETO', '', 'equity', 99, '2026-01-27 16:15:20', 0, '2026-01-27 16:15:20', '31', 5);
INSERT INTO `c19_accounting_accounts` VALUES (276, '310104', 'AJUSTE DE CAPITAL', '', 'equity', 99, '2026-01-27 16:22:08', 0, '2026-01-27 16:22:08', '310104', 5);
INSERT INTO `c19_accounting_accounts` VALUES (277, '310301', 'RESULTADOS ACUMULADOS', '', 'equity', 99, '2026-01-27 16:30:12', 0, '2026-01-27 16:30:12', '310301', 5);
INSERT INTO `c19_accounting_accounts` VALUES (278, '310302', 'RESULTADOS DE LA GESTIÓN', '', 'equity', 99, '2026-01-27 16:31:33', 0, '2026-01-27 16:31:33', '310302', 5);
INSERT INTO `c19_accounting_accounts` VALUES (282, '110201', 'CUENTAS POR COBRAR', '', 'asset', 99, '2026-01-27 17:01:46', 0, '2026-01-27 17:01:46', '110201', 5);
INSERT INTO `c19_accounting_accounts` VALUES (283, '110202', 'IMPUESTOS ANTICIPADOS', '', 'asset', 99, '2026-01-27 17:02:34', 0, '2026-01-27 17:02:34', '110202', 5);
INSERT INTO `c19_accounting_accounts` VALUES (284, '120101', 'EDIFICIOS', '', 'asset', 99, '2026-01-27 17:10:19', 0, '2026-01-27 17:10:19', '120101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (285, '120102', 'VEHICULOS', '', 'asset', 99, '2026-01-27 17:10:51', 0, '2026-01-27 17:10:51', '120102', 5);
INSERT INTO `c19_accounting_accounts` VALUES (286, '120103', 'MUEBLES Y ENSERES', '', 'asset', 99, '2026-01-27 17:11:30', 0, '2026-01-27 17:11:30', '120103', 5);
INSERT INTO `c19_accounting_accounts` VALUES (287, '120104', 'EQUIPOS DE COMPUTACION', '', 'asset', 99, '2026-01-27 17:12:20', 0, '2026-01-27 17:12:20', '120104', 5);
INSERT INTO `c19_accounting_accounts` VALUES (288, '120105', 'TERRENOS', '', 'asset', 99, '2026-01-27 17:12:39', 0, '2026-01-27 17:12:39', '120105', 5);
INSERT INTO `c19_accounting_accounts` VALUES (289, '12010101', 'Edificios', '', 'asset', 99, '2026-01-27 17:20:24', 0, '2026-01-27 17:20:24', '12010101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (290, '12010501', 'Terrenos', '', 'asset', 99, '2026-01-27 17:20:56', 0, '2026-01-27 17:20:56', '12010501', 5);
INSERT INTO `c19_accounting_accounts` VALUES (291, '1202', 'DEPRECIACION ACUMULADA DE ACTIVOS FIJOS', '', 'asset', 99, '2026-01-27 17:22:33', 0, '2026-01-27 17:22:33', '1202', 5);
INSERT INTO `c19_accounting_accounts` VALUES (292, '120201', 'DEPRECIACION ACUMULADA EDIFICIOS', '', 'asset', 99, '2026-01-27 17:24:01', 0, '2026-01-27 17:24:01', '120201', 5);
INSERT INTO `c19_accounting_accounts` VALUES (293, '120202', 'DEPRECIACION ACUMULADA VEHICULOS', '', 'asset', 99, '2026-01-27 17:24:27', 0, '2026-01-27 17:24:27', '120202', 5);
INSERT INTO `c19_accounting_accounts` VALUES (294, '120203', 'DEPRECIACION ACUMULADA DE MUEBLES Y ENSERES', '', 'asset', 99, '2026-01-27 17:24:49', 0, '2026-01-27 17:24:49', '120203', 5);
INSERT INTO `c19_accounting_accounts` VALUES (295, '120204', 'DEPRECIACION ACUMULADA EQUIPOS DE COMPUTACION', '', 'asset', 99, '2026-01-27 17:25:35', 0, '2026-01-27 17:25:35', '120204', 5);
INSERT INTO `c19_accounting_accounts` VALUES (296, '2101', 'DEUDAS COMERCIALES', '', 'liability', 99, '2026-01-27 17:32:33', 0, '2026-01-27 17:32:33', '2101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (297, '2102', 'DEUDAS FISCALES', '', 'liability', 99, '2026-01-27 17:33:14', 0, '2026-01-27 17:33:14', '2102', 5);
INSERT INTO `c19_accounting_accounts` VALUES (298, '2103', 'DEUDAS SOCIALES', '', 'liability', 99, '2026-01-27 17:33:38', 0, '2026-01-27 17:33:38', '2103', 5);
INSERT INTO `c19_accounting_accounts` VALUES (299, '210201', 'OBLIGACIONES TRIBUTARIAS', '', 'liability', 99, '2026-01-27 17:36:14', 0, '2026-01-27 17:36:14', '210201', 5);
INSERT INTO `c19_accounting_accounts` VALUES (300, '210202', 'RETENCIONES TRIBUTARIAS', '', 'liability', 99, '2026-01-27 17:36:46', 0, '2026-01-27 17:36:46', '210202', 5);
INSERT INTO `c19_accounting_accounts` VALUES (301, '21020201', 'Retenciones RC IVA Alquiler IVA 13%', '', 'liability', 99, '2026-01-27 17:46:36', 99, '2026-01-28 08:41:32', '21020201', 5);
INSERT INTO `c19_accounting_accounts` VALUES (302, '21020202', 'Retención RC IVA Alquiler IT 3%', '', 'liability', 99, '2026-01-27 17:47:01', 99, '2026-01-28 08:41:41', '21020202', 5);
INSERT INTO `c19_accounting_accounts` VALUES (303, '210301', 'OBLIGACIONES CON EL PERSONAL', '', 'liability', 99, '2026-01-27 17:47:50', 0, '2026-01-27 17:47:50', '210301', 5);
INSERT INTO `c19_accounting_accounts` VALUES (304, '21030101', 'Sueldo y salarios por pagar', '', 'liability', 99, '2026-01-27 17:48:37', 0, '2026-01-27 17:48:37', '21030101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (305, '21030102', 'Aguinaldos por pagar', '', 'liability', 99, '2026-01-27 17:49:03', 0, '2026-01-27 17:49:03', '21030102', 5);
INSERT INTO `c19_accounting_accounts` VALUES (306, '210302', 'APORTES LABORALES POR PAGAR', '', 'liability', 99, '2026-01-27 17:50:25', 99, '2026-01-27 17:51:15', '210302', 5);
INSERT INTO `c19_accounting_accounts` VALUES (307, '210303', 'APORTES PATRONALES POR PAGAR', '', 'liability', 99, '2026-01-27 17:50:55', 0, '2026-01-27 17:50:55', '210303', 5);
INSERT INTO `c19_accounting_accounts` VALUES (308, '21030201', 'Cuenta individual A.F.P. por pagar', '', 'liability', 99, '2026-01-27 17:55:02', 0, '2026-01-27 17:55:02', '21030201', 5);
INSERT INTO `c19_accounting_accounts` VALUES (309, '21030202', 'Riesgo comun por pagar', '', 'liability', 99, '2026-01-27 17:55:37', 0, '2026-01-27 17:55:37', '21030202', 5);
INSERT INTO `c19_accounting_accounts` VALUES (310, '21030203', 'Comision A.F.P. por pagar', '', 'liability', 99, '2026-01-27 17:56:19', 0, '2026-01-27 17:56:19', '21030203', 5);
INSERT INTO `c19_accounting_accounts` VALUES (311, '21030204', 'Aporte laboral solidario por pagar', '', 'liability', 99, '2026-01-27 17:56:51', 0, '2026-01-27 17:56:51', '21030204', 5);
INSERT INTO `c19_accounting_accounts` VALUES (312, '21030301', 'Caja nacional de salud por pagar', '', 'liability', 99, '2026-01-27 17:57:17', 0, '2026-01-27 17:57:17', '21030301', 5);
INSERT INTO `c19_accounting_accounts` VALUES (313, '21030302', 'Riesgo profesional por pagar', '', 'liability', 99, '2026-01-27 17:57:49', 0, '2026-01-27 17:57:49', '21030302', 5);
INSERT INTO `c19_accounting_accounts` VALUES (314, '21030303', 'pro vivienda por pagar', '', 'liability', 99, '2026-01-27 17:58:08', 0, '2026-01-27 17:58:08', '21030303', 5);
INSERT INTO `c19_accounting_accounts` VALUES (315, '21030304', 'Aporte patronal solidario por pagar', '', 'liability', 99, '2026-01-27 17:58:51', 0, '2026-01-27 17:58:51', '21030304', 5);
INSERT INTO `c19_accounting_accounts` VALUES (316, '2202', 'RESERVAS Y PROVISIONES', '', 'liability', 99, '2026-01-27 18:00:43', 0, '2026-01-27 18:00:43', '2202', 5);
INSERT INTO `c19_accounting_accounts` VALUES (317, '220201', 'PREVISIONES', '', 'liability', 99, '2026-01-27 18:01:16', 0, '2026-01-27 18:01:16', '220201', 5);
INSERT INTO `c19_accounting_accounts` VALUES (318, '22020101', 'Provisiones para indeminizacion', '', 'liability', 99, '2026-01-27 18:02:01', 0, '2026-01-27 18:02:01', '22020101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (319, '41', 'INGRESOS', '', 'income', 99, '2026-01-27 18:05:44', 0, '2026-01-27 18:05:44', '41', 5);
INSERT INTO `c19_accounting_accounts` VALUES (320, '4101', 'INGRESOS ORDINARIOS', '', 'income', 99, '2026-01-27 18:06:33', 99, '2026-01-27 19:50:10', '4101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (326, '51', 'COSTOS', '', 'expenses', 99, '2026-01-27 19:00:52', 0, '2026-01-27 19:00:52', '51', 5);
INSERT INTO `c19_accounting_accounts` VALUES (327, '5201', 'GASTOS  DE OPERACION', '', 'expenses', 99, '2026-01-27 19:08:56', 99, '2026-01-27 19:35:05', '5201', 5);
INSERT INTO `c19_accounting_accounts` VALUES (330, '520101', 'SERVICIOS PROFECIONALES', '', 'expenses', 99, '2026-01-27 19:12:39', 0, '2026-01-27 19:12:39', '520101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (331, '520102', 'CARGAS SOCIALES', '', 'expenses', 99, '2026-01-27 19:13:01', 0, '2026-01-27 19:13:01', '520102', 5);
INSERT INTO `c19_accounting_accounts` VALUES (332, '520103', 'SERVICIOS BASICOS', '', 'expenses', 99, '2026-01-27 19:13:43', 0, '2026-01-27 19:13:43', '520103', 5);
INSERT INTO `c19_accounting_accounts` VALUES (333, '520104', 'GASTOS ADMINISTRATIVOS', '', 'expenses', 99, '2026-01-27 19:14:28', 0, '2026-01-27 19:14:28', '520104', 5);
INSERT INTO `c19_accounting_accounts` VALUES (334, '520105', 'IMPOSITIVOS', '', 'expenses', 99, '2026-01-27 19:14:58', 0, '2026-01-27 19:14:58', '520105', 5);
INSERT INTO `c19_accounting_accounts` VALUES (335, '520106', 'MANTENIMIENTO DE VALOR', '', 'expenses', 99, '2026-01-27 19:15:18', 99, '2026-01-27 19:15:46', '520106', 5);
INSERT INTO `c19_accounting_accounts` VALUES (336, '520107', 'DEPRECIACIONES', '', 'expenses', 99, '2026-01-27 19:16:21', 0, '2026-01-27 19:16:21', '520107', 5);
INSERT INTO `c19_accounting_accounts` VALUES (337, '52010201', 'Cargas sociales', '', 'expenses', 99, '2026-01-27 19:23:09', 0, '2026-01-27 19:23:09', '52010201', 5);
INSERT INTO `c19_accounting_accounts` VALUES (338, '52010202', 'Aguinaldo', '', 'expenses', 99, '2026-01-27 19:23:30', 0, '2026-01-27 19:23:30', '52010202', 5);
INSERT INTO `c19_accounting_accounts` VALUES (339, '52010203', 'Indemnizacion', '', 'expenses', 99, '2026-01-27 19:23:59', 0, '2026-01-27 19:23:59', '52010203', 5);
INSERT INTO `c19_accounting_accounts` VALUES (340, '52010301', 'Energía eléctrica', '', 'expenses', 99, '2026-01-27 19:24:33', 0, '2026-01-27 19:24:33', '52010301', 5);
INSERT INTO `c19_accounting_accounts` VALUES (341, '52010302', 'Agua y alcantarillado', '', 'expenses', 99, '2026-01-27 19:24:59', 0, '2026-01-27 19:24:59', '52010302', 5);
INSERT INTO `c19_accounting_accounts` VALUES (342, '52010303', 'servicios telefónicos', '', 'expenses', 99, '2026-01-27 19:25:33', 0, '2026-01-27 19:25:33', '52010303', 5);
INSERT INTO `c19_accounting_accounts` VALUES (343, '52010304', 'Servicios de internet', '', 'expenses', 99, '2026-01-27 19:26:03', 0, '2026-01-27 19:26:03', '52010304', 5);
INSERT INTO `c19_accounting_accounts` VALUES (344, '52010401', 'Gastos generales', '', 'expenses', 99, '2026-01-27 19:26:28', 0, '2026-01-27 19:26:28', '52010401', 5);
INSERT INTO `c19_accounting_accounts` VALUES (345, '52010402', 'Material de escritorio', '', 'expenses', 99, '2026-01-27 19:27:00', 0, '2026-01-27 19:27:00', '52010402', 5);
INSERT INTO `c19_accounting_accounts` VALUES (346, '52010403', 'Servicios de alquiler', '', 'expenses', 99, '2026-01-27 19:27:25', 0, '2026-01-27 19:27:25', '52010403', 5);
INSERT INTO `c19_accounting_accounts` VALUES (347, '52010404', 'Servicios de buro de información Infocenter', '', 'expenses', 99, '2026-01-27 19:28:24', 0, '2026-01-27 19:28:24', '52010404', 5);
INSERT INTO `c19_accounting_accounts` VALUES (348, '52010501', 'Impuesto a las transacciones', '', 'expenses', 99, '2026-01-27 19:28:47', 0, '2026-01-27 19:28:47', '52010501', 5);
INSERT INTO `c19_accounting_accounts` VALUES (349, '52010601', 'Mantenimiento de valor', '', 'expenses', 99, '2026-01-27 19:29:12', 0, '2026-01-27 19:29:12', '52010601', 5);
INSERT INTO `c19_accounting_accounts` VALUES (350, '52010602', 'Diferencia de cambio', '', 'expenses', 99, '2026-01-27 19:29:36', 0, '2026-01-27 19:29:36', '52010602', 5);
INSERT INTO `c19_accounting_accounts` VALUES (351, '52010701', 'depreciación edificios', '', 'expenses', 99, '2026-01-27 19:29:59', 99, '2026-01-27 19:30:38', '52010701', 5);
INSERT INTO `c19_accounting_accounts` VALUES (352, '52010702', 'deprecación vehículos', '', 'expenses', 99, '2026-01-27 19:30:24', 0, '2026-01-27 19:30:24', '52010702', 5);
INSERT INTO `c19_accounting_accounts` VALUES (353, '52010703', 'deprecación muebles y enseres', '', 'expenses', 99, '2026-01-27 19:31:03', 0, '2026-01-27 19:31:03', '52010703', 5);
INSERT INTO `c19_accounting_accounts` VALUES (354, '52010704', 'depreciación equipos de computación', '', 'expenses', 99, '2026-01-27 19:31:35', 0, '2026-01-27 19:31:35', '52010704', 5);
INSERT INTO `c19_accounting_accounts` VALUES (355, '5301', 'GASTOS NO  OPERATIVOS', '', 'expenses', 99, '2026-01-27 19:32:57', 99, '2026-01-27 19:37:17', '5301', 5);
INSERT INTO `c19_accounting_accounts` VALUES (356, '530101', 'GASTOS NO MONETARIOS', '', 'expenses', 99, '2026-01-27 19:38:01', 0, '2026-01-27 19:38:01', '530101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (357, '53010101', 'Ajuste por inflación y tenencia de bienes', '', 'expenses', 99, '2026-01-27 19:38:45', 0, '2026-01-27 19:38:45', '53010101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (358, '5401', 'GASTOS FINACIEROS', '', 'expenses', 99, '2026-01-27 19:40:01', 0, '2026-01-27 19:40:01', '5401', 5);
INSERT INTO `c19_accounting_accounts` VALUES (359, '540101', 'INTERESES PAGADOS', '', 'expenses', 99, '2026-01-27 19:40:26', 0, '2026-01-27 19:40:26', '540101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (360, '54010101', 'Intereses pagados por prestamos', '', 'expenses', 99, '2026-01-27 19:41:10', 0, '2026-01-27 19:41:10', '54010101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (361, '5501', 'IMPUESTO SOBRE LAS UTILIDADES', '', 'expenses', 99, '2026-01-27 19:42:01', 0, '2026-01-27 19:42:01', '5501', 5);
INSERT INTO `c19_accounting_accounts` VALUES (362, '550101', 'IMPUESTO SOBRE LAS UTILIDADES', '', 'expenses', 99, '2026-01-27 19:42:31', 0, '2026-01-27 19:42:31', '550101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (363, '55010101', 'Impuesto sobre las utilidades', '', 'expenses', 99, '2026-01-27 19:43:12', 0, '2026-01-27 19:43:12', '55010101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (364, '42', 'OTROS INGRESOS', '', 'income', 99, '2026-01-27 19:48:50', 0, '2026-01-27 19:48:50', '42', 5);
INSERT INTO `c19_accounting_accounts` VALUES (365, '43', 'INGRESOS FINANCIEROS', '', 'income', 99, '2026-01-27 19:49:44', 0, '2026-01-27 19:49:44', '43', 5);
INSERT INTO `c19_accounting_accounts` VALUES (367, '41010101', 'Ventas', '', 'income', 99, '2026-01-27 19:51:44', 99, '2026-01-27 20:01:53', '41010101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (368, '41010102', 'descuentos sobre ventas', '', 'income', 99, '2026-01-27 19:52:18', 99, '2026-01-27 20:02:06', '41010102', 5);
INSERT INTO `c19_accounting_accounts` VALUES (369, '4201', 'INGRESOS NO OPERATIVOS', '', 'income', 99, '2026-01-27 19:55:38', 0, '2026-01-27 19:55:38', '4201', 5);
INSERT INTO `c19_accounting_accounts` VALUES (370, '420101', 'INGRSOS NO MONETARIOS', '', 'income', 99, '2026-01-27 19:56:10', 0, '2026-01-27 19:56:10', '420101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (371, '42010101', 'Ajuste por inflación y tenencia de bienes', '', 'income', 99, '2026-01-27 19:56:48', 0, '2026-01-27 19:56:48', '42010101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (372, '4301', 'INGRESOS FINANCIEROS', '', 'income', 99, '2026-01-27 19:57:35', 0, '2026-01-27 19:57:35', '4301', 5);
INSERT INTO `c19_accounting_accounts` VALUES (373, '430101', 'INTERESES', '', 'income', 99, '2026-01-27 19:57:59', 0, '2026-01-27 19:57:59', '430101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (374, '430102', 'PRODUCTOS POR CARTERA VIGENTE', '', 'income', 99, '2026-01-27 19:59:11', 0, '2026-01-27 19:59:11', '430102', 5);
INSERT INTO `c19_accounting_accounts` VALUES (375, '43010201', 'Interese sobre prestamos amortizables', '', 'income', 99, '2026-01-27 19:59:51', 0, '2026-01-27 19:59:51', '43010201', 5);
INSERT INTO `c19_accounting_accounts` VALUES (376, '410101', 'VENTAS NETAS', '', 'income', 99, '2026-01-27 20:01:34', 0, '2026-01-27 20:01:34', '410101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (377, '43010101', 'INTERESES BANCARIOS', '', 'income', 99, '2026-01-27 20:03:04', 0, '2026-01-27 20:03:04', '43010101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (378, '11010102', 'Caja moneda extranjera', '', 'asset', 99, '2026-01-28 08:30:58', 0, '2026-01-28 08:30:58', '11010102', 5);
INSERT INTO `c19_accounting_accounts` VALUES (379, '11020101', 'Cuentas por cobrar', '', 'asset', 99, '2026-01-28 08:31:37', 0, '2026-01-28 08:31:37', '11020101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (380, '110401', 'GASTOS ANTICIPADOS', '', 'asset', 99, '2026-01-28 08:32:43', 0, '2026-01-28 08:32:43', '110401', 5);
INSERT INTO `c19_accounting_accounts` VALUES (381, '11040101', 'Alquileres pagados por adelantado', '', 'asset', 99, '2026-01-28 08:33:29', 0, '2026-01-28 08:33:29', '11040101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (382, '11040102', 'Publicidad pagados por adelantado', '', 'asset', 99, '2026-01-28 08:34:22', 0, '2026-01-28 08:34:22', '11040102', 5);
INSERT INTO `c19_accounting_accounts` VALUES (383, '210101', 'CUENTAS POR PAGAR', '', 'liability', 99, '2026-01-28 08:40:19', 0, '2026-01-28 08:40:19', '210101', 5);
INSERT INTO `c19_accounting_accounts` VALUES (384, '21010101', 'Cuentas por pagar', '', 'liability', 99, '2026-01-28 08:40:47', 0, '2026-01-28 08:40:47', '21010101', 5);

SET FOREIGN_KEY_CHECKS = 1;

DROP TABLE IF EXISTS `c19_accounting_transactions`;
CREATE TABLE `c19_accounting_transactions`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `account_id` int NULL DEFAULT NULL,
  `amount` decimal(10, 2) NULL DEFAULT NULL,
  `payment_methods` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `description` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `invoice_number` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `added_date` datetime NULL DEFAULT NULL,
  `purchased_date` datetime NULL DEFAULT NULL,
  `purchased_amount` decimal(10, 2) NULL DEFAULT NULL,
  `added_by` int NULL DEFAULT NULL,
  `modified_date` timestamp NOT NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  `modified_by` int NULL DEFAULT NULL,
  `transaction_type` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `depreciate_amount` decimal(10, 2) NULL DEFAULT NULL,
  `branch_id` int NULL DEFAULT NULL,
  `voucher_id` int NULL DEFAULT NULL,
  `movement_type` enum('debit','credit') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `transaction_order` int NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1387 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for c19_accounting_vouchers
-- ----------------------------
DROP TABLE IF EXISTS `c19_accounting_vouchers`;
CREATE TABLE `c19_accounting_vouchers`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `voucher_date` date NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL,
  `total_debit` decimal(15, 2) NOT NULL DEFAULT 0.00,
  `total_credit` decimal(15, 2) NOT NULL DEFAULT 0.00,
  `branch_id` int NULL DEFAULT NULL,
  `added_by` int NOT NULL,
  `added_date` datetime NOT NULL,
  `modified_by` int NULL DEFAULT NULL,
  `modified_date` datetime NULL DEFAULT NULL,
  `voucher_number` int NULL DEFAULT NULL,
  `voucher_type` enum('ingreso','egreso','traspaso') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `branch_id`(`branch_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 269 CHARACTER SET = utf8mb3 COLLATE = utf8mb3_general_ci ROW_FORMAT = Dynamic;
SET FOREIGN_KEY_CHECKS = 1;