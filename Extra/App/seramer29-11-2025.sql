-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.4.5 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.13.0.7147
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Volcando estructura para tabla seramermvc.academic_degrees
CREATE TABLE IF NOT EXISTS `academic_degrees` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Catálogo de grados académicos (licenciatura, maestría, etc.)';

-- Volcando datos para la tabla seramermvc.academic_degrees: ~5 rows (aproximadamente)
DELETE FROM `academic_degrees`;
INSERT INTO `academic_degrees` (`id`, `name`) VALUES
	(1, 'Bachiller'),
	(2, 'Técnico Superior'),
	(3, 'Licenciatura'),
	(4, 'Maestría'),
	(5, 'Doctorado');

-- Volcando estructura para tabla seramermvc.academic_specializations
CREATE TABLE IF NOT EXISTS `academic_specializations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Catálogo de especializaciones o menciones académicas';

-- Volcando datos para la tabla seramermvc.academic_specializations: ~5 rows (aproximadamente)
DELETE FROM `academic_specializations`;
INSERT INTO `academic_specializations` (`id`, `name`) VALUES
	(1, 'Administración'),
	(2, 'Contabilidad'),
	(3, 'Derecho'),
	(4, 'Informática'),
	(5, 'Recursos Humanos');

-- Volcando estructura para tabla seramermvc.attendance
CREATE TABLE IF NOT EXISTS `attendance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `staff_id` int NOT NULL,
  `date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `is_special` tinyint(1) DEFAULT '0' COMMENT 'Para marcar asistencias especiales o excepcionales',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_staff_date` (`staff_id`,`date`),
  KEY `idx_staff_id` (`staff_id`),
  KEY `idx_date` (`date`),
  KEY `idx_staff_date` (`staff_id`,`date`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registra las asistencias del personal';

-- Volcando datos para la tabla seramermvc.attendance: ~0 rows (aproximadamente)
DELETE FROM `attendance`;

-- Volcando estructura para tabla seramermvc.audit_log
CREATE TABLE IF NOT EXISTS `audit_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Tipo de acción realizada (login, insert, update, delete)',
  `table_affected` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `record_id` int DEFAULT NULL COMMENT 'ID del registro afectado',
  `old_values` json DEFAULT NULL COMMENT 'Valores anteriores en formato JSON',
  `new_values` json DEFAULT NULL COMMENT 'Valores nuevos en formato JSON',
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_table_affected` (`table_affected`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_record_id` (`record_id`),
  CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registra todas las acciones importantes realizadas por los usuarios en el sistema';

-- Volcando datos para la tabla seramermvc.audit_log: ~82 rows (aproximadamente)
DELETE FROM `audit_log`;
INSERT INTO `audit_log` (`id`, `user_id`, `action`, `table_affected`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
	(1, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 11:47:56'),
	(2, 1, 'insert', 'awardees', 4, NULL, '{"email": "", "phone": "", "address": "", "id_number": "26119392", "last_name": "Mendoza", "first_name": "Rosanyelis", "middle_name": "Andreina", "second_last_name": "Bellorin"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 12:34:46'),
	(3, 1, 'delete', 'awardees', 4, '{"id": 4, "email": "", "phone": "", "address": "", "id_number": "26119392", "last_name": "Mendoza", "first_name": "Rosanyelis", "middle_name": "Andreina", "second_last_name": "Bellorin"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 14:01:51'),
	(4, 1, 'delete', 'awardees', 3, '{"id": 3, "email": null, "phone": "+584248556985", "address": null, "id_number": "V26119395", "last_name": "rest", "first_name": "test", "middle_name": "test", "second_last_name": "test"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 14:05:29'),
	(5, 1, 'update', 'external_business_categories', 40, '{"id": 40, "name": "ALIMENTOS PROCESADOS", "payment_count": "14.89", "installation_type": "TOLDO (4X3)"}', '{"name": "ALIMENTOS PROCESADOS t", "payment_count": "14.89", "installation_type": "TOLDO (4X3)"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 14:48:55'),
	(6, 1, 'update', 'external_business_categories', 40, '{"id": 40, "name": "ALIMENTOS PROCESADOS t", "payment_count": "14.89", "installation_type": "TOLDO (4X3)"}', '{"name": "ALIMENTOS PROCESADOS", "payment_count": "14.89", "installation_type": "TOLDO (4X3)"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 14:49:05'),
	(7, 1, 'insert', 'internal_business_categories', 43, NULL, '{"name": "test", "payment_count": "12.25"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 15:01:33'),
	(8, 1, 'delete', 'internal_business_categories', 43, '{"id": 43, "name": "test", "payment_count": "12.25"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 15:01:56'),
	(9, 1, 'update', 'internal_business_categories', 41, '{"id": 41, "name": "Aliños", "payment_count": "2.51"}', '{"name": "Aliños t", "payment_count": "2.51"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 15:02:35'),
	(10, 1, 'update', 'internal_business_categories', 41, '{"id": 41, "name": "Aliños t", "payment_count": "2.51"}', '{"name": "Aliños t", "payment_count": "2.51"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 15:02:50'),
	(11, 1, 'update', 'internal_business_categories', 41, '{"id": 41, "name": "Aliños t", "payment_count": "2.51"}', '{"name": "Aliños", "payment_count": "2.51"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 15:03:31'),
	(12, 1, 'update', 'internal_business_categories', 41, '{"id": 41, "name": "Aliños", "payment_count": "2.51"}', '{"name": "Aliños", "payment_count": "2.51"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 15:03:44'),
	(13, 1, 'update', 'internal_business_categories', 41, '{"id": 41, "name": "Aliños", "payment_count": "2.51"}', '{"name": "Aliños", "payment_count": "2.51"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 15:03:54'),
	(14, 1, 'update', 'internal_business_categories', 41, '{"id": 41, "name": "Aliños", "payment_count": "2.51"}', '{"name": "Aliños", "payment_count": "2.51"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 15:04:06'),
	(15, 1, 'update', 'internal_business_categories', 41, '{"id": 41, "name": "Aliños", "payment_count": "2.51"}', '{"name": "Aliños", "payment_count": "2.51"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 15:04:28'),
	(16, 1, 'update', 'internal_business_categories', 41, '{"id": 41, "name": "Aliños", "payment_count": "2.51"}', '{"name": "Aliños", "payment_count": "2.51"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 15:04:43'),
	(17, 1, 'update', 'internal_business_categories', 41, '{"id": 41, "name": "Aliños", "payment_count": "2.51"}', '{"name": "Aliños", "payment_count": "2.51"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 15:05:01'),
	(18, 1, 'update', 'internal_business_categories', 41, '{"id": 41, "name": "Aliños", "payment_count": "2.51"}', '{"name": "Aliños", "payment_count": "2.51"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 15:05:16'),
	(19, 1, 'update', 'external_business_categories', 40, '{"id": 40, "name": "ALIMENTOS PROCESADOS", "payment_count": "14.89", "installation_type": "TOLDO (4X3)"}', '{"name": "ALIMENTOS PROCESADOS", "payment_count": "14.89", "installation_type": "TOLDO (4X3)"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 15:05:42'),
	(20, 1, 'update', 'zones', 1, '{"id": 1, "name": "ZONAS  DE VENTA 1", "description": "testingt"}', '{"name": "ZONAS  DE VENTA 1", "description": ""}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 15:06:04'),
	(21, 1, 'insert', 'zones', 5, NULL, '{"name": "test", "description": ""}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 15:06:13'),
	(22, 1, 'delete', 'zones', 5, '{"id": 5, "name": "test", "description": ""}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 15:06:16'),
	(23, 1, 'update', 'sectors', 8, '{"id": 8, "name": "BOULEVAR", "zone_id": 2, "zone_name": "ZONAS  DE VENTA 2", "description": null}', '{"name": "BOULEVAR", "zone_id": "2", "description": ""}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 15:06:28'),
	(24, 1, 'insert', 'market_stalls', 4, NULL, '{"sector_id": "21", "stall_number": "L-005", "location_description": "tst"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 16:31:29'),
	(25, 1, 'delete', 'market_stalls', 4, '{"id": 4, "zone_id": 3, "sector_id": 21, "zone_name": "ZONAS  DE VENTA 3", "sector_name": "CALLE MERCADITO", "stall_number": "L-005", "location_description": "tst"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 16:31:38'),
	(26, 1, 'update', 'euro_rates', 5, '{"id": 5, "year": "2025", "month": "marzo", "bs_value": "148.90"}', '{"year": 2025, "month": "marzo", "bs_value": 148.7, "month_number": 3}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 16:57:24'),
	(27, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 19:21:06'),
	(28, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 19:21:12'),
	(29, 1, 'insert', 'awardees', 5, NULL, '{"email": "", "phone": "", "address": "", "id_number": "26119392", "last_name": "Mendoza", "first_name": "Rosanyelis", "middle_name": "Andreina", "second_last_name": "Bellorin"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 19:33:42'),
	(30, 1, 'insert', 'market_stalls', 5, NULL, '{"sector_id": 5, "description": "", "stall_number": "L-008"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 19:41:40'),
	(31, 1, 'insert', 'market_stalls', 6, NULL, '{"sector_id": 8, "description": "", "stall_number": "L-010"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 19:53:42'),
	(32, 1, 'insert', 'contracts', 3, NULL, '{"type": "simultaneous", "end_date": "2026-04-30", "awardee_id": 5, "start_date": "2025-10-15", "contract_mode": "monthly", "fiscal_year_id": 1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 19:53:52'),
	(33, 1, 'delete', 'contracts', 3, '{"id": 3, "type": "simultaneous", "end_date": "2026-04-30", "awardee_id": 5, "start_date": "2025-10-15", "fiscal_year": "2025", "contract_mode": "monthly", "fiscal_year_id": 1, "fiscal_end_date": "2026-04-30", "awardee_id_number": "26119392", "awardee_last_name": "Mendoza", "fiscal_start_date": "2025-05-01", "awardee_first_name": "Rosanyelis"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 20:04:30'),
	(34, 1, 'insert', 'contracts', 4, NULL, '{"type": "simultaneous", "end_date": "2026-04-30", "awardee_id": 5, "start_date": "2025-10-15", "contract_mode": "monthly", "fiscal_year_id": 1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 20:05:07'),
	(35, 1, 'insert', 'euro_rates', 7, NULL, '{"year": 2025, "month": "octubre", "bs_value": 230.45, "month_number": 10}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 20:46:56'),
	(36, 1, 'update', 'contract_payments', 66, '{"status": "pending"}', '{"status": "paid"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 22:05:56'),
	(37, 1, 'update', 'contract_payments', 66, '{"status": "paid"}', '{"status": "pending"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 22:06:08'),
	(38, 1, 'update', 'payment_methods', 5, '{"id": 5, "name": "Biopago", "is_active": 1}', '{"is_active": false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 22:13:08'),
	(39, 1, 'update', 'payment_methods', 5, '{"id": 5, "name": "Biopago", "is_active": 0}', '{"is_active": true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 22:13:15'),
	(40, 1, 'update', 'payment_methods', 5, '{"id": 5, "name": "Biopago", "is_active": 1}', '{"is_active": false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 22:16:16'),
	(41, 1, 'update', 'payment_methods', 5, '{"id": 5, "name": "Biopago", "is_active": 0}', '{"is_active": true}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 22:16:20'),
	(42, 1, 'insert', 'contract_payment_installments', 1, NULL, '{"date": "2025-10-15", "amount": 276.12, "concept": "Pago de mensualidad", "payment_method_id": 3, "contract_payment_id": 85}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 22:38:42'),
	(43, 1, 'insert', 'contract_payment_installments', 2, NULL, '{"date": "2025-10-15", "amount": 137.64, "concept": "Pago de mensualidad", "payment_method_id": 1, "contract_payment_id": 64}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 22:44:25'),
	(44, 1, 'insert', 'contract_payment_installments', 3, NULL, '{"date": "2025-10-15", "amount": 300, "concept": "Pago de mensualidad", "payment_method_id": 3, "contract_payment_id": 64}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 22:44:48'),
	(45, 1, 'insert', 'euro_rates', 8, NULL, '{"year": 2025, "month": "julio", "bs_value": 130, "month_number": 7}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 22:56:00'),
	(46, 1, 'insert', 'euro_rates', 9, NULL, '{"year": 2025, "month": "mayo", "bs_value": 125.35, "month_number": 5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 22:56:33'),
	(47, 1, 'update', 'euro_rates', 6, '{"id": 6, "year": "2025", "month": "abril", "bs_value": "147.65"}', '{"year": 2025, "month": "abril", "bs_value": 115.6, "month_number": 4}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 22:57:02'),
	(48, 1, 'insert', 'euro_rates', 10, NULL, '{"year": 2025, "month": "septiembre", "bs_value": 190.65, "month_number": 9}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 22:57:54'),
	(49, 1, 'insert', 'contract_payment_installments', 4, NULL, '{"date": "2025-10-15", "amount": 100, "concept": "Pago de mensualidad", "payment_method_id": 3, "contract_payment_id": 85}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-15 23:48:19'),
	(50, 1, 'insert', 'contract_payment_installments', 5, NULL, '{"date": "2025-10-15", "amount": 200.005, "concept": "Pago de mensualidad", "payment_method_id": 2, "contract_payment_id": 85}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-16 00:11:51'),
	(51, 1, 'logout', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-16 00:23:40'),
	(52, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-16 00:24:03'),
	(53, 1, 'insert', 'euro_rates', 11, NULL, '{"year": 2025, "month": "noviembre", "bs_value": 205.35, "month_number": 11}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-16 00:28:52'),
	(54, 1, 'update', 'contract_payments', 67, '{"status": "pending"}', '{"status": "paid"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-16 00:30:53'),
	(55, 1, 'update', 'contract_payments', 66, '{"status": "pending"}', '{"status": "paid"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-16 00:30:54'),
	(56, 1, 'update', 'contract_payments', 65, '{"status": "pending"}', '{"status": "paid"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-16 00:30:54'),
	(57, 1, 'update', 'contract_payments', 67, '{"status": "paid"}', '{"status": "pending"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-16 00:31:03'),
	(58, 1, 'update', 'contract_payments', 66, '{"status": "paid"}', '{"status": "pending"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-16 00:31:03'),
	(59, 1, 'update', 'contract_payments', 65, '{"status": "paid"}', '{"status": "pending"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-16 00:31:03'),
	(60, 1, 'insert', 'contract_payment_installments', 6, NULL, '{"date": "2025-10-15", "amount": 178.43, "concept": "Pago de mensualidad", "payment_method_id": 2, "contract_payment_id": 12}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-16 00:36:49'),
	(61, 1, 'insert', 'awardees', 6, NULL, '{"email": "", "phone": "", "address": "", "id_number": "24715826", "last_name": "Mundarain", "first_name": "Luis", "middle_name": "", "second_last_name": ""}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-16 00:50:54'),
	(62, 1, 'insert', 'contracts', 5, NULL, '{"type": "advance", "end_date": "2026-04-30", "awardee_id": 6, "start_date": "2025-10-15", "contract_mode": "monthly", "fiscal_year_id": 1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-16 00:52:54'),
	(63, 1, 'insert', 'market_stalls', 7, NULL, '{"sector_id": 21, "stall_number": "m-01", "location_description": ""}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-16 00:54:15'),
	(64, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-17 23:12:13'),
	(65, 1, 'delete', 'internal_business_categories', 11, '{"id": 11, "name": "Arepera", "payment_count": "2.50"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-17 23:26:30'),
	(66, 1, 'insert', 'internal_business_categories', 44, NULL, '{"name": "Arepera", "payment_count": "2.50"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-17 23:26:54'),
	(67, 1, 'insert', 'awardees', 7, NULL, '{"email": "arquimedez@gmail.com", "phone": "", "address": "Barrio Sucre", "id_number": "17624272", "last_name": "Salazar", "first_name": "Arquimedes", "middle_name": "David", "second_last_name": "Salazar"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-17 23:38:32'),
	(68, 1, 'update', 'awardees', 7, '{"id": 7, "email": "arquimedez@gmail.com", "phone": "", "address": "Barrio Sucre", "id_number": "17624272", "last_name": "Salazar", "first_name": "Arquimedes", "middle_name": "David", "second_last_name": "Salazar"}', '{"email": "arquimedez@gmail.com", "phone": "", "address": "Barrio Sucre", "id_number": "17624272", "last_name": "Salazar", "first_name": "Arquimedes", "middle_name": "David", "second_last_name": "Salazar"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-17 23:38:51'),
	(69, 1, 'delete', 'awardees', 7, '{"id": 7, "email": "arquimedez@gmail.com", "phone": "", "address": "Barrio Sucre", "id_number": "17624272", "last_name": "Salazar", "first_name": "Arquimedes", "middle_name": "David", "second_last_name": "Salazar"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-17 23:39:24'),
	(70, 1, 'insert', 'awardees', 8, NULL, '{"email": "arquimedessalazar@gmail.com", "phone": "", "address": "Barrio Sucre", "id_number": "17624271", "last_name": "Salazar", "first_name": "Arquimedes", "middle_name": "David", "second_last_name": "Salazar"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-17 23:40:30'),
	(71, 1, 'insert', 'market_stalls', 8, NULL, '{"sector_id": 7, "stall_number": "EA-01", "location_description": ""}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-17 23:46:52'),
	(72, 1, 'insert', 'awardees', 9, NULL, '{"email": "antonioguzman@gmail.com", "phone": "", "address": "", "id_number": "25835660", "last_name": "Guzman", "first_name": "Antonio", "middle_name": "Jose", "second_last_name": "Blanco"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-17 23:54:06'),
	(73, 1, 'insert', 'market_stalls', 9, NULL, '{"sector_id": 21, "description": "", "stall_number": "L-0078"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 00:00:01'),
	(74, 1, 'insert', 'contracts', 6, NULL, '{"type": "simultaneous", "end_date": "2026-04-30", "awardee_id": 9, "start_date": "2025-05-04", "contract_mode": "monthly", "fiscal_year_id": 1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 00:00:14'),
	(75, 1, 'update', 'euro_rates', 6, '{"id": 6, "year": "2025", "month": "abril", "bs_value": "115.60"}', '{"year": 2025, "month": "abril", "bs_value": 115.6, "month_number": 4}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 00:00:58'),
	(76, 1, 'insert', 'euro_rates', 12, NULL, '{"year": 2025, "month": "diciembre", "bs_value": 300, "month_number": 12}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 00:01:53'),
	(77, 1, 'update', 'contract_payments', 106, '{"status": "pending"}', '{"status": "paid"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 00:03:24'),
	(78, 1, 'update', 'contract_payments', 105, '{"status": "pending"}', '{"status": "paid"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 00:03:25'),
	(79, 1, 'update', 'contract_payments', 104, '{"status": "pending"}', '{"status": "paid"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 00:03:25'),
	(80, 1, 'update', 'contract_payments', 103, '{"status": "pending"}', '{"status": "paid"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 00:03:25'),
	(81, 1, 'update', 'contract_payments', 106, '{"status": "paid"}', '{"status": "pending"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 00:03:52'),
	(82, 1, 'update', 'contract_payments', 105, '{"status": "paid"}', '{"status": "pending"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 00:03:52'),
	(83, 1, 'update', 'contract_payments', 104, '{"status": "paid"}', '{"status": "pending"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 00:03:52'),
	(84, 1, 'update', 'contract_payments', 103, '{"status": "paid"}', '{"status": "pending"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 00:03:53'),
	(85, 1, 'insert', 'contract_payment_installments', 7, NULL, '{"date": "2025-10-17", "amount": 200, "concept": "Pago de mensualidad", "payment_method_id": 3, "contract_payment_id": 106}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 00:06:14'),
	(86, 1, 'insert', 'contract_payment_installments', 8, NULL, '{"date": "2025-10-17", "amount": 700, "concept": "Pago de mensualidad", "payment_method_id": 4, "contract_payment_id": 106}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 00:06:47'),
	(87, 1, 'logout', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-18 00:13:11'),
	(88, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-29 00:53:46'),
	(89, 1, 'insert', 'users', 13, NULL, '{"email": "carlos@example.com", "status": "active", "username": "carlos"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-29 01:20:40'),
	(90, 1, 'logout', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-29 02:55:16'),
	(91, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-29 03:04:33'),
	(92, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-03 03:50:05'),
	(93, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-06 14:11:06'),
	(94, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-06 22:25:53'),
	(95, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-09 14:52:07'),
	(96, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 01:24:37'),
	(97, 1, 'insert', 'contracts', 1, NULL, '{"type": "simultaneous", "end_date": "2026-04-30", "awardee_id": 48, "start_date": "2025-11-28", "contract_mode": "monthly", "fiscal_year_id": 1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 01:51:26'),
	(98, 1, 'insert', 'cash_registers', 1, NULL, '{"name": "Caja de Carlos", "status": "active", "user_id": 13}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 02:49:44'),
	(99, 1, 'insert', 'cash_registers', 2, NULL, '{"name": "Caja de maria", "status": "active", "user_id": 1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 02:50:47'),
	(100, 1, 'insert', 'daily_cash_registers', 1, NULL, '{"user_id": 1, "initial_amount": 5000, "cash_register_id": 2}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 02:52:00'),
	(101, 1, 'delete', 'contracts', 1, '{"id": 1, "type": "simultaneous", "status": null, "end_date": "2026-04-30", "awardee_id": 48, "start_date": "2025-11-28", "fiscal_year": "2025", "contract_mode": "monthly", "fiscal_year_id": 1, "status_payment": null, "fiscal_end_date": "2026-04-30", "awardee_id_number": "V-36373839", "awardee_last_name": "ARAUJO", "fiscal_start_date": "2025-05-01", "awardee_first_name": "LEONARDO"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 02:58:11'),
	(102, 1, 'insert', 'contracts', 2, NULL, '{"type": "simultaneous", "end_date": "2026-04-30", "awardee_id": 48, "start_date": "2025-11-28", "contract_mode": "monthly", "fiscal_year_id": 1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 02:58:36'),
	(103, 1, 'insert', 'contract_payment_installments', 1, NULL, '{"date": "2025-11-28", "amount": 315.43, "concept": "Pago de mensualidad", "payment_method_id": 5, "contract_payment_id": 7, "daily_cash_register_id": 1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 03:22:44'),
	(104, 1, 'insert', 'contract_payment_installments', 2, NULL, '{"date": "2025-11-28", "amount": 199.99, "concept": "Pago de mensualidad", "payment_method_id": 1, "contract_payment_id": 7, "daily_cash_register_id": 1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 03:23:47'),
	(105, 1, 'update', 'daily_cash_registers', 1, '{"id": 1, "status": "open", "user_id": 1, "username": "mmaria", "open_date": "2025-11-28", "open_time": "22:52:00", "user_name": "María González", "close_date": null, "close_time": null, "created_at": "2025-11-28 22:52:00", "updated_at": "2025-11-28 22:52:00", "final_amount": null, "initial_amount": "5000.00", "cash_register_id": 2, "cash_register_name": "Caja de maria"}', '{"status": "closed", "final_amount": 5515.42}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 03:30:48'),
	(106, 1, 'insert', 'daily_cash_registers', 4, NULL, '{"user_id": 1, "initial_amount": 3000, "cash_register_id": 2}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 03:42:51'),
	(107, 1, 'insert', 'contract_payment_installments', 3, NULL, '{"date": "2025-11-28", "amount": 500, "concept": "Pago de mensualidad", "payment_method_id": 1, "contract_payment_id": 8, "daily_cash_register_id": 4}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 03:44:09'),
	(108, 1, 'insert', 'contract_payment_installments', 4, NULL, '{"date": "2025-11-28", "amount": 253, "concept": "Pago de mensualidad", "payment_method_id": 1, "contract_payment_id": 8, "daily_cash_register_id": 4}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 03:44:22'),
	(109, 1, 'update', 'daily_cash_registers', 4, '{"id": 4, "status": "open", "user_id": 1, "username": "mmaria", "open_date": "2025-11-28", "open_time": "23:42:51", "user_name": "María González", "close_date": null, "close_time": null, "created_at": "2025-11-28 23:42:51", "updated_at": "2025-11-28 23:42:51", "final_amount": null, "initial_amount": "3000.00", "cash_register_id": 2, "cash_register_name": "Caja de maria"}', '{"status": "closed", "final_amount": 3753}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 03:56:26');

-- Volcando estructura para tabla seramermvc.awardees
CREATE TABLE IF NOT EXISTS `awardees` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Primer nombre',
  `middle_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Segundo nombre',
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Primer apellido',
  `second_last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Segundo apellido',
  `id_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Número de cédula o identificación',
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Número de teléfono',
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Correo electrónico',
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'Dirección completa',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_number` (`id_number`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Personas a quienes se adjudican espacios en el mercado';

-- Volcando datos para la tabla seramermvc.awardees: ~6 rows (aproximadamente)
DELETE FROM `awardees`;
INSERT INTO `awardees` (`id`, `first_name`, `middle_name`, `last_name`, `second_last_name`, `id_number`, `phone`, `email`, `address`) VALUES
	(1, 'MARIA', 'ELENA', 'GONZALEZ', 'RODRIGUEZ', 'V-12345678', '0412-5551234', 'maria.gonzalez@email.com', 'Av. Principal de El Paraíso, Caracas'),
	(2, 'JUAN', 'CARLOS', 'LOPEZ', 'MARTINEZ', 'V-87654321', '0414-5551235', 'juan.lopez@email.com', 'Calle 72 con Av. 3H, Maracaibo'),
	(3, 'ANA', 'ISABEL', 'HERNANDEZ', 'GARCIA', 'V-23456789', '0424-5551236', 'ana.hernandez@email.com', 'Urbanización La Viña, Valencia'),
	(4, 'CARLOS', 'ALBERTO', 'RAMIREZ', 'SILVA', 'J-123456789-1', '0416-5551237', 'carlos.ramirez@email.com', 'Sector Los Mangos, Barquisimeto'),
	(5, 'SOFIA', 'ALEJANDRA', 'MORALES', 'RUIZ', 'V-34567890', '0426-5551238', 'sofia.morales@email.com', 'Residencias El Recreo, Caracas'),
	(6, 'LUIS', 'MIGUEL', 'TORRES', 'MENDOZA', 'V-45678901', '0412-5551239', 'luis.torres@email.com', 'Urbanización El Valle, Caracas'),
	(7, 'PATRICIA', 'CAROLINA', 'DIAZ', 'PEREZ', 'J-234567890-2', '0414-5551240', 'patricia.diaz@email.com', 'Ciudad Jardín, Maracay'),
	(8, 'JOSE', 'GREGORIO', 'CASTILLO', 'ROJAS', 'V-56789012', '0424-5551241', 'jose.castillo@email.com', 'Urbanización La Concordia, San Cristóbal'),
	(9, 'MARIANA', 'JOSEFINA', 'FLORES', 'VARGAS', 'V-67890123', '0416-5551242', 'mariana.flores@email.com', 'Sector Santa Rosa, Barinas'),
	(10, 'PEDRO', 'ANTONIO', 'NAVARRO', 'ACOSTA', 'J-345678901-3', '0426-5551243', 'pedro.navarro@email.com', 'Urbanización Los Naranjos, Maturín'),
	(11, 'GABRIELA', 'FERNANDA', 'OROPEZA', 'LEON', 'V-78901234', '0412-5551244', 'gabriela.oropeza@email.com', 'Colinas de Bello Monte, Caracas'),
	(12, 'RAFAEL', 'ANGEL', 'SUAREZ', 'MEDINA', 'V-89012345', '0414-5551245', 'rafael.suarez@email.com', 'Urbanización La Victoria, Valencia'),
	(13, 'LAURA', 'VALENTINA', 'ROMERO', 'GUZMAN', 'J-456789012-4', '0424-5551246', 'laura.romero@email.com', 'Sector El Milagro, Maracaibo'),
	(14, 'DIEGO', 'ARMANDO', 'SALAZAR', 'HERRERA', 'V-90123456', '0416-5551247', 'diego.salazar@email.com', 'Urbanización Los Chaguaramos, Caracas'),
	(15, 'CAMILA', 'ANDREA', 'MONTILLA', 'PEÑA', 'V-11223344', '0426-5551248', 'camila.montilla@email.com', 'Residencias Paramaconi, Barquisimeto'),
	(16, 'ANDRES', 'FELIPE', 'QUINTERO', 'GOMEZ', 'J-567890123-5', '0412-5551249', 'andres.quintero@email.com', 'Urbanización La Paz, Puerto La Cruz'),
	(17, 'VALERIA', 'MARGARITA', 'BLANCO', 'MARIN', 'V-22334455', '0414-5551250', 'valeria.blanco@email.com', 'Sector La Morita, Maracay'),
	(18, 'RICARDO', 'JOSE', 'PARRA', 'NUNEZ', 'V-33445566', '0424-5551251', 'ricardo.parra@email.com', 'Urbanización Las Acacias, Caracas'),
	(19, 'ISABEL', 'CRISTINA', 'RIVAS', 'MOLINA', 'J-678901234-6', '0416-5551252', 'isabel.rivas@email.com', 'Colinas de Santa Mónica, Caracas'),
	(20, 'MIGUEL', 'ANGEL', 'FUENMAYOR', 'CORDERO', 'V-44556677', '0426-5551253', 'miguel.fuenmayor@email.com', 'Urbanización La Floresta, Barquisimeto'),
	(21, 'ADRIANA', 'MARIA', 'ZAMBRANO', 'FIGUEROA', 'V-55667788', '0412-5551254', 'adriana.zambrano@email.com', 'Sector La Candelaria, Mérida'),
	(22, 'JORGE', 'LUIS', 'BRICEÑO', 'ESCALONA', 'J-789012345-7', '0414-5551255', 'jorge.briceño@email.com', 'Urbanización Los Samanes, Valencia'),
	(23, 'NATALIA', 'ALEJANDRA', 'VELASQUEZ', 'ARIAS', 'V-66778899', '0424-5551256', 'natalia.velasquez@email.com', 'Residencias El Prado, Maracaibo'),
	(24, 'FERNANDO', 'JAVIER', 'MARCANO', 'BARRIOS', 'V-77889900', '0416-5551257', 'fernando.marcano@email.com', 'Urbanización La California, Caracas'),
	(25, 'DANIELA', 'CAROLINA', 'GIL', 'SALAS', 'J-890123456-8', '0426-5551258', 'daniela.gil@email.com', 'Sector Santa Eduvigis, San Cristóbal'),
	(26, 'OSCAR', 'MANUEL', 'URDANETA', 'MIRANDA', 'V-88990011', '0412-5551259', 'oscar.urdaneta@email.com', 'Urbanización El Carmen, Valencia'),
	(27, 'VERONICA', 'PATRICIA', 'ROJAS', 'LARA', 'V-99001122', '0414-5551260', 'veronica.rojas@email.com', 'Colinas de Los Caobos, Caracas'),
	(28, 'HECTOR', 'RAFAEL', 'MENESES', 'SANCHEZ', 'J-901234567-9', '0424-5551261', 'hector.meneses@email.com', 'Urbanización La Urbina, Caracas'),
	(29, 'LUCIA', 'BEATRIZ', 'ARAUJO', 'MORA', 'V-10111213', '0416-5551262', 'lucia.araujo@email.com', 'Sector La Trinidad, Barquisimeto'),
	(30, 'ROBERTO', 'CARLOS', 'BARRIOS', 'ROSALES', 'V-12131415', '0426-5551263', 'roberto.barrios@email.com', 'Urbanización Prados del Este, Caracas'),
	(31, 'ELENA', 'ROSA', 'CABRERA', 'FARIAS', 'J-123456780-0', '0412-5551264', 'elena.cabrera@email.com', 'Residencias El Trigal, Valencia'),
	(32, 'MARTIN', 'ANDRES', 'FALCON', 'RAMOS', 'V-14151617', '0414-5551265', 'martin.falcon@email.com', 'Urbanización La Rinconada, Caracas'),
	(33, 'SANDRA', 'LILIANA', 'GRATEROL', 'REYES', 'V-16171819', '0424-5551266', 'sandra.graterol@email.com', 'Sector Los Cortijos, Maracaibo'),
	(34, 'ALBERTO', 'ENRIQUE', 'HURTADO', 'SOTO', 'J-234567801-1', '0416-5551267', 'alberto.hurtado@email.com', 'Urbanización El Paraíso, Caracas'),
	(35, 'GLADYS', 'MARINA', 'IGLESIAS', 'TAPIA', 'V-18192021', '0426-5551268', 'gladys.iglesias@email.com', 'Colinas de San Román, Coro'),
	(36, 'EDUARDO', 'RAMON', 'JAIMES', 'URBINA', 'V-20212223', '0412-5551269', 'eduardo.jaimes@email.com', 'Urbanización La Victoria, Maracay'),
	(37, 'YOLANDA', 'FRANCISCA', 'LIRA', 'VALERA', 'J-345678912-2', '0414-5551270', 'yolanda.lira@email.com', 'Sector La Isabelica, Valencia'),
	(38, 'WILMER', 'ALEXIS', 'MATOS', 'YANEZ', 'V-22232425', '0424-5551271', 'wilmer.matos@email.com', 'Urbanización Los Sauces, Puerto Ordaz'),
	(39, 'ROSA', 'AMELIA', 'NIETO', 'ZABALA', 'V-24252627', '0416-5551272', 'rosa.nieto@email.com', 'Residencias La Alameda, Caracas'),
	(40, 'FRANCISCO', 'JAVIER', 'OJEDA', 'ALVARADO', 'J-456789023-3', '0426-5551273', 'francisco.ojeda@email.com', 'Urbanización El Manantial, Barquisimeto'),
	(41, 'TERESA', 'MARGARITA', 'PINTO', 'BETANCOURT', 'V-26272829', '0412-5551274', 'teresa.pinto@email.com', 'Sector La Vega, Caracas'),
	(42, 'ARMANDO', 'JESUS', 'QUIJADA', 'CASTELLANOS', 'V-28293031', '0414-5551275', 'armando.quijada@email.com', 'Urbanización La Campiña, Maracaibo'),
	(43, 'BEATRIZ', 'ELENA', 'RENGIFO', 'DUARTE', 'J-567890134-4', '0424-5551276', 'beatriz.rengifo@email.com', 'Colinas de Santa Rosa, Caracas'),
	(44, 'CESAR', 'AUGUSTO', 'SOSA', 'ESPINOZA', 'V-30313233', '0416-5551277', 'cesar.sosa@email.com', 'Urbanización Los Guayabitos, Valencia'),
	(45, 'DORA', 'ISABEL', 'TROCHEZ', 'FERRER', 'V-32333435', '0426-5551278', 'dora.trochez@email.com', 'Sector La Consolación, San Cristóbal'),
	(46, 'GUSTAVO', 'ADOLFO', 'USECHE', 'GARMENDIA', 'J-678901245-5', '0412-5551279', 'gustavo.useche@email.com', 'Urbanización El Recreo, Caracas'),
	(47, 'IRENE', 'CARMEN', 'VILLARROEL', 'HIDALGO', 'V-34353637', '0414-5551280', 'irene.villarroel@email.com', 'Residencias La Arboleda, Barquisimeto'),
	(48, 'LEONARDO', 'FABIAN', 'ARAUJO', 'IBARRA', 'V-36373839', '0424-5551281', 'leonardo.araujo@email.com', 'Urbanización Los Colorados, Puerto La Cruz'),
	(49, 'MIRIAM', 'JOSEFINA', 'BOHORQUEZ', 'JIMENEZ', 'J-789012356-6', '0416-5551282', 'miriam.bohorquez@email.com', 'Sector La Dolorita, Caracas'),
	(50, 'NELSON', 'JOSE', 'CARRILLO', 'KLEIN', 'V-38394041', '0426-5551283', 'nelson.carrillo@email.com', 'Urbanización La Trinidad, Caracas'),
	(51, 'OLGA', 'ANTONIA', 'DELGADO', 'LLOVERAS', 'V-40414243', '0412-5551284', 'olga.delgado@email.com', 'Colinas de Los Ruices, Caracas'),
	(52, 'PABLO', 'CESAR', 'ESPINOZA', 'MONTES', 'J-890123467-7', '0414-5551285', 'pablo.espinoza@email.com', 'Urbanización El Marques, Valencia'),
	(53, 'QUENY', 'MARIELA', 'FERNANDEZ', 'NUÑEZ', 'V-42434445', '0424-5551286', 'queny.fernandez@email.com', 'Sector La Sabanita, Maracay'),
	(54, 'RAMON', 'ANTONIO', 'GARCIA', 'OJEDA', 'V-44454647', '0416-5551287', 'ramon.garcia@email.com', 'Urbanización Los Caobos, Caracas'),
	(55, 'SILVIA', 'ALEJANDRA', 'HERNANDEZ', 'PEREZ', 'J-901234578-8', '0426-5551288', 'silvia.hernandez@email.com', 'Residencias El Valle, Caracas'),
	(56, 'TOMAS', 'EDUARDO', 'IRAZABAL', 'QUINTERO', 'V-46474849', '0412-5551289', 'tomas.irazabal@email.com', 'Urbanización La Candelaria, Caracas'),
	(57, 'URSULA', 'CAROLINA', 'JIMENEZ', 'RODRIGUEZ', 'V-48495051', '0414-5551290', 'ursula.jimenez@email.com', 'Sector Los Guayos, Valencia'),
	(58, 'VICENTE', 'MANUEL', 'LOPEZ', 'SANCHEZ', 'J-123456791-9', '0424-5551291', 'vicente.lopez@email.com', 'Urbanización El Paraíso, Maracaibo'),
	(59, 'XIMENA', 'PATRICIA', 'MARTINEZ', 'TORRES', 'V-50515253', '0416-5551292', 'ximena.martinez@email.com', 'Colinas de Bello Campo, Caracas'),
	(60, 'YAJAHIRA', 'FRANCISCA', 'NUÑEZ', 'URDANETA', 'V-52535455', '0426-5551293', 'yajahira.nuñez@email.com', 'Urbanización La Viña, Valencia'),
	(61, 'ZORAIDA', 'ISABEL', 'ORTIZ', 'VARGAS', 'J-234567892-0', '0412-5551294', 'zoraida.ortiz@email.com', 'Sector Santa Rosa, Barquisimeto');

-- Volcando estructura para tabla seramermvc.cash_registers
CREATE TABLE IF NOT EXISTS `cash_registers` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador único de la caja',
  `user_id` int NOT NULL COMMENT 'ID del usuario asignado a la caja',
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Nombre descriptivo de la caja (ej: Caja 1 - Recepción)',
  `status` enum('active','inactive','maintenance') COLLATE utf8mb4_general_ci DEFAULT 'active' COMMENT 'Estado operativo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última actualización',
  PRIMARY KEY (`id`),
  KEY `idx_cash_register_user` (`user_id`),
  KEY `idx_cash_register_status` (`status`),
  CONSTRAINT `cash_registers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro de cajas de cobro asignadas a personal';

-- Volcando datos para la tabla seramermvc.cash_registers: ~2 rows (aproximadamente)
DELETE FROM `cash_registers`;
INSERT INTO `cash_registers` (`id`, `user_id`, `name`, `status`, `created_at`, `updated_at`) VALUES
	(1, 13, 'Caja de Carlos', 'active', '2025-11-29 02:49:44', '2025-11-29 02:49:44'),
	(2, 1, 'Caja de maria', 'active', '2025-11-29 02:50:47', '2025-11-29 02:50:47');

-- Volcando estructura para tabla seramermvc.contract_business_categories
CREATE TABLE IF NOT EXISTS `contract_business_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `contract_id` int NOT NULL COMMENT 'Contrato relacionado',
  `external_category_id` int DEFAULT NULL COMMENT 'Categoría externa (si aplica)',
  `internal_category_id` int DEFAULT NULL COMMENT 'Categoría interna (si aplica)',
  `type` enum('internal','external') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Tipo de categoría',
  PRIMARY KEY (`id`),
  KEY `contract_id` (`contract_id`),
  KEY `external_category_id` (`external_category_id`),
  KEY `internal_category_id` (`internal_category_id`),
  CONSTRAINT `contract_business_categories_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`),
  CONSTRAINT `contract_business_categories_ibfk_2` FOREIGN KEY (`external_category_id`) REFERENCES `external_business_categories` (`id`),
  CONSTRAINT `contract_business_categories_ibfk_3` FOREIGN KEY (`internal_category_id`) REFERENCES `internal_business_categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Relaciona contratos con categorías de negocio';

-- Volcando datos para la tabla seramermvc.contract_business_categories: ~4 rows (aproximadamente)
DELETE FROM `contract_business_categories`;
INSERT INTO `contract_business_categories` (`id`, `contract_id`, `external_category_id`, `internal_category_id`, `type`) VALUES
	(2, 2, NULL, 41, 'internal');

-- Volcando estructura para tabla seramermvc.contract_locations
CREATE TABLE IF NOT EXISTS `contract_locations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `contract_id` int NOT NULL COMMENT 'Contrato relacionado',
  `stall_id` int NOT NULL COMMENT 'Local/puesto asignado',
  PRIMARY KEY (`id`),
  KEY `contract_id` (`contract_id`),
  KEY `stall_id` (`stall_id`),
  CONSTRAINT `contract_locations_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`),
  CONSTRAINT `contract_locations_ibfk_2` FOREIGN KEY (`stall_id`) REFERENCES `market_stalls` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Asignación de locales específicos a contratos';

-- Volcando datos para la tabla seramermvc.contract_locations: ~6 rows (aproximadamente)
DELETE FROM `contract_locations`;
INSERT INTO `contract_locations` (`id`, `contract_id`, `stall_id`) VALUES
	(2, 2, 5);

-- Volcando estructura para tabla seramermvc.contract_payment_installments
CREATE TABLE IF NOT EXISTS `contract_payment_installments` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del abono',
  `contract_payment_id` int NOT NULL COMMENT 'ID del pago de contrato al que pertenece este abono',
  `payment_method_id` int NOT NULL COMMENT 'Método de pago utilizado',
  `date` date NOT NULL COMMENT 'Fecha en que se realizó el abono',
  `amount` decimal(12,2) NOT NULL COMMENT 'Monto del abono',
  `concept` text COLLATE utf8mb4_general_ci COMMENT 'Concepto o descripción del abono',
  `daily_cash_register_id` int DEFAULT NULL COMMENT 'Caja diaria donde se registró el cobro',
  PRIMARY KEY (`id`),
  KEY `payment_method_id` (`payment_method_id`),
  KEY `idx_installment_contract_payment` (`contract_payment_id`),
  KEY `idx_installment_date` (`date`),
  KEY `idx_daily_cash_register` (`daily_cash_register_id`),
  CONSTRAINT `contract_payment_installments_ibfk_1` FOREIGN KEY (`contract_payment_id`) REFERENCES `contract_payments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contract_payment_installments_ibfk_2` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`),
  CONSTRAINT `fk_installment_daily_cash_register` FOREIGN KEY (`daily_cash_register_id`) REFERENCES `daily_cash_registers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro de abonos o pagos parciales realizados para un pago de contrato, con seguimiento de caja';

-- Volcando datos para la tabla seramermvc.contract_payment_installments: ~2 rows (aproximadamente)
DELETE FROM `contract_payment_installments`;
INSERT INTO `contract_payment_installments` (`id`, `contract_payment_id`, `payment_method_id`, `date`, `amount`, `concept`, `daily_cash_register_id`) VALUES
	(1, 7, 5, '2025-11-28', 315.43, 'Pago de mensualidad', 1),
	(2, 7, 1, '2025-11-28', 199.99, 'Pago de mensualidad', 1),
	(3, 8, 1, '2025-11-28', 500.00, 'Pago de mensualidad', 4),
	(4, 8, 1, '2025-11-28', 253.00, 'Pago de mensualidad', 4);

-- Volcando estructura para tabla seramermvc.contract_payments
CREATE TABLE IF NOT EXISTS `contract_payments` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del pago',
  `contract_id` int NOT NULL COMMENT 'ID del contrato asociado a este pago',
  `payment_reference` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Número o referencia del pago',
  `euro_rate_id` int DEFAULT NULL COMMENT 'ID de la tasa de cambio del euro aplicada',
  `payment_date` date NOT NULL COMMENT 'Fecha en que se realizó el pago',
  `amount` decimal(12,2) NOT NULL COMMENT 'Monto del pago',
  `status` enum('pending','paid','cancelled','refunded') COLLATE utf8mb4_general_ci DEFAULT 'pending' COMMENT 'Estado actual del pago',
  PRIMARY KEY (`id`),
  KEY `euro_rate_id` (`euro_rate_id`),
  KEY `idx_contract_payments_contract` (`contract_id`),
  KEY `idx_contract_payments_date` (`payment_date`),
  CONSTRAINT `contract_payments_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contract_payments_ibfk_2` FOREIGN KEY (`euro_rate_id`) REFERENCES `euro_rates` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro de pagos asociados a los contratos del mercado';

-- Volcando datos para la tabla seramermvc.contract_payments: ~6 rows (aproximadamente)
DELETE FROM `contract_payments`;
INSERT INTO `contract_payments` (`id`, `contract_id`, `payment_reference`, `euro_rate_id`, `payment_date`, `amount`, `status`) VALUES
	(7, 2, 'PAY-2025-11-000002', 11, '2025-11-28', 515.43, 'paid'),
	(8, 2, 'PAY-2025-12-000002', 12, '2025-12-15', 753.00, 'paid'),
	(9, 2, 'PAY-2026-01-000002', NULL, '2026-01-15', 0.00, 'pending'),
	(10, 2, 'PAY-2026-02-000002', NULL, '2026-02-15', 0.00, 'pending'),
	(11, 2, 'PAY-2026-03-000002', NULL, '2026-03-15', 0.00, 'pending'),
	(12, 2, 'PAY-2026-04-000002', NULL, '2026-04-15', 0.00, 'pending');

-- Volcando estructura para tabla seramermvc.contracts
CREATE TABLE IF NOT EXISTS `contracts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `awardee_id` int NOT NULL COMMENT 'Adjudicatario del contrato',
  `fiscal_year_id` int NOT NULL COMMENT 'Año fiscal asociado al contrato',
  `start_date` date NOT NULL COMMENT 'Fecha de inicio del contrato',
  `end_date` date NOT NULL COMMENT 'Fecha de finalización',
  `type` enum('simultaneous','advance') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Tipo de contrato',
  `contract_mode` enum('monthly','weekly') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Modalidad de pago',
  `status` enum('active','renewed','canceled') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_payment` enum('up to date','delinquent','unable to pay') COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `awardee_id` (`awardee_id`),
  KEY `fk_contracts_fiscal_year` (`fiscal_year_id`),
  CONSTRAINT `contracts_ibfk_2` FOREIGN KEY (`awardee_id`) REFERENCES `awardees` (`id`),
  CONSTRAINT `fk_contracts_fiscal_year` FOREIGN KEY (`fiscal_year_id`) REFERENCES `fiscal_year` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Contratos de adjudicación de espacios en el mercado';

-- Volcando datos para la tabla seramermvc.contracts: ~5 rows (aproximadamente)
DELETE FROM `contracts`;
INSERT INTO `contracts` (`id`, `awardee_id`, `fiscal_year_id`, `start_date`, `end_date`, `type`, `contract_mode`, `status`, `status_payment`) VALUES
	(2, 48, 1, '2025-11-28', '2026-04-30', 'simultaneous', 'monthly', 'active', NULL);

-- Volcando estructura para tabla seramermvc.daily_cash_registers
CREATE TABLE IF NOT EXISTS `daily_cash_registers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cash_register_id` int NOT NULL COMMENT 'Caja que se abre/cierra',
  `user_id` int NOT NULL COMMENT 'Usuario que realiza la apertura/cierre',
  `open_date` date NOT NULL COMMENT 'Fecha de apertura',
  `open_time` time NOT NULL COMMENT 'Hora de apertura',
  `close_date` date DEFAULT NULL COMMENT 'Fecha de cierre',
  `close_time` time DEFAULT NULL COMMENT 'Hora de cierre',
  `initial_amount` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Monto inicial',
  `final_amount` decimal(15,2) DEFAULT NULL COMMENT 'Monto final al cierre',
  `status` enum('open','closed') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'open' COMMENT 'Estado de la caja diaria',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cash_register_id` (`cash_register_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_open_date` (`open_date`),
  KEY `idx_status` (`status`),
  KEY `idx_dates` (`open_date`,`close_date`),
  CONSTRAINT `daily_cash_registers_ibfk_1` FOREIGN KEY (`cash_register_id`) REFERENCES `cash_registers` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `daily_cash_registers_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro de aperturas y cierres diarios de cajas';

-- Volcando datos para la tabla seramermvc.daily_cash_registers: ~2 rows (aproximadamente)
DELETE FROM `daily_cash_registers`;
INSERT INTO `daily_cash_registers` (`id`, `cash_register_id`, `user_id`, `open_date`, `open_time`, `close_date`, `close_time`, `initial_amount`, `final_amount`, `status`, `created_at`, `updated_at`) VALUES
	(1, 2, 1, '2025-11-28', '22:52:00', '2025-11-28', '23:30:48', 5000.00, 5515.42, 'closed', '2025-11-29 02:52:00', '2025-11-29 03:30:48'),
	(4, 2, 1, '2025-11-28', '23:42:51', '2025-11-28', '23:56:26', 3000.00, 3753.00, 'closed', '2025-11-29 03:42:51', '2025-11-29 03:56:26');

-- Volcando estructura para tabla seramermvc.department_schedules
CREATE TABLE IF NOT EXISTS `department_schedules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `department_id` int NOT NULL,
  `day` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_department_id` (`department_id`),
  KEY `idx_day` (`day`),
  KEY `idx_time_range` (`start_time`,`end_time`),
  CONSTRAINT `department_schedules_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Define los horarios de trabajo por departamento';

-- Volcando datos para la tabla seramermvc.department_schedules: ~0 rows (aproximadamente)
DELETE FROM `department_schedules`;

-- Volcando estructura para tabla seramermvc.departments
CREATE TABLE IF NOT EXISTS `departments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `shift_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `manager_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`),
  KEY `idx_shift_type` (`shift_type`),
  KEY `idx_manager_id` (`manager_id`),
  CONSTRAINT `fk_departments_manager` FOREIGN KEY (`manager_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Almacena los departamentos de la organización';

-- Volcando datos para la tabla seramermvc.departments: ~4 rows (aproximadamente)
DELETE FROM `departments`;
INSERT INTO `departments` (`id`, `name`, `description`, `shift_type`, `created_at`, `manager_id`) VALUES
	(1, 'Liquidacion', 'Departamento de liquidación de nóminas', 'Matutino', '2025-08-02 21:28:46', 1),
	(2, 'Cobranza', 'Departamento de gestión de cobros', 'Vespertino', '2025-08-02 21:28:46', 2),
	(3, 'Fiscalizacion', 'Departamento de control fiscal', 'Mixto', '2025-08-02 21:28:46', 3),
	(4, 'Recursos Humanos', 'Gestión del personal y talento humano', 'Administrativo', '2025-08-02 21:28:46', 4);

-- Volcando estructura para tabla seramermvc.divisions
CREATE TABLE IF NOT EXISTS `divisions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `department_id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `idx_department_id` (`department_id`),
  KEY `idx_name` (`name`),
  CONSTRAINT `divisions_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Contiene las divisiones que pertenecen a cada departamento';

-- Volcando datos para la tabla seramermvc.divisions: ~6 rows (aproximadamente)
DELETE FROM `divisions`;
INSERT INTO `divisions` (`id`, `department_id`, `name`, `description`) VALUES
	(1, 1, 'Liquidación de Nóminas', 'División encargada del cálculo de salarios'),
	(2, 1, 'Prestaciones Sociales', 'División de beneficios para empleados'),
	(3, 2, 'Cobranza Interna', 'Gestión de cobros a empleados'),
	(4, 2, 'Cobranza Externa', 'Gestión de cobros a clientes'),
	(5, 3, 'Auditoría', 'División de revisiones fiscales'),
	(6, 4, 'Reclutamiento', 'Selección de personal');

-- Volcando estructura para tabla seramermvc.euro_rates
CREATE TABLE IF NOT EXISTS `euro_rates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bs_value` decimal(10,2) NOT NULL COMMENT 'Valor en bolívares',
  `month` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Mes de la tasa',
  `year` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Año de la tasa',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro histórico de tasas de cambio del euro';

-- Volcando datos para la tabla seramermvc.euro_rates: ~8 rows (aproximadamente)
DELETE FROM `euro_rates`;
INSERT INTO `euro_rates` (`id`, `bs_value`, `month`, `year`) VALUES
	(1, 145.88, 'agosto', '2025'),
	(4, 152.10, 'febrero', '2025'),
	(5, 148.70, 'marzo', '2025'),
	(6, 115.60, 'abril', '2025'),
	(7, 230.45, 'octubre', '2025'),
	(8, 130.00, 'julio', '2025'),
	(9, 125.35, 'mayo', '2025'),
	(10, 190.65, 'septiembre', '2025'),
	(11, 205.35, 'noviembre', '2025'),
	(12, 300.00, 'diciembre', '2025');

-- Volcando estructura para tabla seramermvc.external_business_categories
CREATE TABLE IF NOT EXISTS `external_business_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Nombre de la categoría externa',
  `installation_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Tipo de instalación requerida',
  `payment_count` decimal(10,2) DEFAULT NULL COMMENT 'Número de cobros requeridos',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Categorías de negocios externos al mercado';

-- Volcando datos para la tabla seramermvc.external_business_categories: ~57 rows (aproximadamente)
DELETE FROM `external_business_categories`;
INSERT INTO `external_business_categories` (`id`, `name`, `installation_type`, `payment_count`) VALUES
	(2, 'PROTEINAS, VERDURAS, LEGUMBRES Y HORTALIZAS', 'CAMIÓN / CAVAS', 22.78),
	(3, 'PROTEINAS, VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (5X5)', 20.60),
	(4, 'PROTEINAS, VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (4X4)', 18.50),
	(5, 'PROTEINAS, VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (4X3)', 16.70),
	(6, 'PROTEINAS, VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (3X3)', 15.25),
	(7, 'PROTEINAS, VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (2X2)', 14.60),
	(8, 'CHARCUTERIA', 'TOLDO (5X5)', 19.30),
	(9, 'CHARCUTERIA', 'TOLDO (4X4)', 18.50),
	(10, 'CHARCUTERIA', 'TOLDO (4X3)', 17.70),
	(11, 'CHARCUTERIA', 'TOLDO (3X3)', 16.46),
	(12, 'PROTEINAS, VERDURAS, LEGUMBRES Y HORTALIZAS', 'CAMIÓN / CAVAS', 22.78),
	(13, 'PROTEINAS, VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (5X5)', 20.60),
	(14, 'PROTEINAS, VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (4X4)', 18.50),
	(15, 'PROTEINAS, VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (4X3)', 16.70),
	(16, 'PROTEINAS, VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (3X3)', 15.25),
	(17, 'PROTEINAS, VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (2X2)', 14.60),
	(18, 'CHARCUTERIA', 'TOLDO (5X5)', 19.30),
	(19, 'CHARCUTERIA', 'TOLDO (4X4)', 18.50),
	(20, 'CHARCUTERIA', 'TOLDO (4X3)', 17.70),
	(21, 'CHARCUTERIA', 'TOLDO (3X3)', 16.46),
	(22, 'VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (2X2)', 37.60),
	(23, 'VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (5X5)', 15.50),
	(24, 'VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (4X4)', 14.30),
	(25, 'VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (4X3)', 13.40),
	(26, 'VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (3X3)', 12.60),
	(27, 'VERDURAS, LEGUMBRES Y HORTALIZAS', 'TOLDO (2X2)', 11.89),
	(28, 'VIVERES', 'TOLDO (5X5)', 37.60),
	(29, 'VIVERES', 'TOLDO (4X4)', 15.40),
	(30, 'VIVERES', 'TOLDO (4X3)', 14.40),
	(31, 'VIVERES', 'TOLDO (3X3)', 13.60),
	(32, 'VIVERES', 'TOLDO (2X2)', 12.89),
	(33, 'LIMPIEZA E HIGIENE', 'TOLDO (5X5)', 17.60),
	(34, 'LIMPIEZA E HIGIENE', 'TOLDO (4X4)', 16.40),
	(35, 'LIMPIEZA E HIGIENE', 'TOLDO (4X3)', 15.40),
	(36, 'LIMPIEZA E HIGIENE', 'TOLDO (3X3)', 14.60),
	(37, 'LIMPIEZA E HIGIENE', 'TOLDO (2X2)', 13.89),
	(38, 'ALIMENTOS PROCESADOS', 'TOLDO (5X5)', 16.60),
	(39, 'ALIMENTOS PROCESADOS', 'TOLDO (4X4)', 15.60),
	(40, 'ALIMENTOS PROCESADOS', 'TOLDO (4X3)', 14.89),
	(41, 'ALIMENTOS PROCESADOS', 'TOLDO (3X3)', 13.70),
	(42, 'ALIMENTOS PROCESADOS', 'TOLDO (2X2)', 12.50),
	(43, 'DULCERIA/PANADERIA', 'TOLDO (5X5)', 15.30),
	(44, 'DULCERIA/PANADERIA', 'TOLDO (4X4)', 14.60),
	(45, 'DULCERIA/PANADERIA', 'TOLDO (4X3)', 13.30),
	(46, 'DULCERIA/PANADERIA', 'TOLDO (3X3)', 12.70),
	(47, 'DULCERIA/PANADERIA', 'TOLDO (2X2)', 10.30),
	(48, 'TEXTIL/CALZADO', 'TOLDO (5X5)', 15.30),
	(49, 'TEXTIL/CALZADO', 'TOLDO (4X4)', 14.60),
	(50, 'TEXTIL/CALZADO', 'TOLDO (4X3)', 13.30),
	(51, 'TEXTIL/CALZADO', 'TOLDO (3X3)', 12.70),
	(52, 'TEXTIL/CALZADO', 'TOLDO (2X2)', 11.30),
	(53, 'OTROS', 'TOLDO (5X5)', 3.30),
	(54, 'OTROS', 'TOLDO (4X4)', 2.70),
	(55, 'OTROS', 'TOLDO (4X3)', 2.20),
	(56, 'OTROS', 'TOLDO (3X3)', 1.60),
	(57, 'OTROS', 'TOLDO (2X2)', 1.30),
	(58, 'KIOSCOS', 'ESTANDAR', 16.70);

-- Volcando estructura para tabla seramermvc.fiscal_year
CREATE TABLE IF NOT EXISTS `fiscal_year` (
  `id` int NOT NULL AUTO_INCREMENT,
  `start_date` date DEFAULT NULL COMMENT 'Mes de inicio del año fiscal',
  `end_date` date DEFAULT NULL COMMENT 'Año de inicio del año fiscal',
  `year` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Año de finalización del año fiscal',
  `status` enum('active','inactive') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del registro',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Almacena la información de los años fiscales';

-- Volcando datos para la tabla seramermvc.fiscal_year: ~1 rows (aproximadamente)
DELETE FROM `fiscal_year`;
INSERT INTO `fiscal_year` (`id`, `start_date`, `end_date`, `year`, `status`, `created_at`) VALUES
	(1, '2025-05-01', '2026-04-30', '2025', 'active', '2025-08-04 01:17:49');

-- Volcando estructura para tabla seramermvc.internal_business_categories
CREATE TABLE IF NOT EXISTS `internal_business_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Nombre de la categoría',
  `payment_count` decimal(10,2) DEFAULT NULL COMMENT 'Número de cobros requeridos',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Categorías de negocios internos como puestos y mesones';

-- Volcando datos para la tabla seramermvc.internal_business_categories: ~40 rows (aproximadamente)
DELETE FROM `internal_business_categories`;
INSERT INTO `internal_business_categories` (`id`, `name`, `payment_count`) VALUES
	(1, 'Dulceria y Emprendedores', 2.00),
	(2, 'Varios', 2.00),
	(3, 'Viveres', 2.00),
	(4, 'Quincallas', 2.00),
	(6, 'Legumbre y Hortalizas', 2.00),
	(7, 'Verduras', 2.00),
	(8, 'Frutas', 2.00),
	(9, 'Pescados, Fresco o Salado', 2.00),
	(10, 'Condimentos y especias para cocinar', 2.00),
	(12, 'Comidas', 2.50),
	(13, 'Refresquerias / cafeterias', 2.50),
	(14, 'Empanadas', 2.50),
	(15, 'Mercancia Seca', 2.50),
	(16, 'Puesto de Reparación de zapatos', 2.50),
	(17, 'Chatarreros', 2.50),
	(18, 'Carne de Res', 3.00),
	(19, 'Carne de Cochino', 3.00),
	(20, 'Pollos Beneficiados', 3.00),
	(21, 'Charcuteria', 3.00),
	(22, 'Floristerias', 3.00),
	(23, 'Periodico y Revistas', 3.00),
	(24, 'Papeleria y Libreria', 3.00),
	(25, 'Servicio Tecnico e Internet', 3.00),
	(26, 'Plantas Curativas y Ornamentales', 3.00),
	(27, 'Relojeria, joyas y Prendas', 3.00),
	(28, 'Ventas de equipos celulares y de computadoras informatica', 3.00),
	(29, 'Ventas de Ropa y Zapatos', 3.00),
	(30, 'Ventas de Animales vivos', 3.00),
	(31, 'Peladoras de pollos y gallinas', 3.00),
	(32, 'Juegos y Apuestas Licitas', 5.00),
	(33, 'Loterias y Animalitos', 5.00),
	(34, 'Deposito', 5.00),
	(35, 'Ferreterias', 5.00),
	(36, 'Barberia y Peluqueria', 5.00),
	(37, 'Ventas de Comidas y Refresqueria', 5.00),
	(38, 'Mini Panaderias', 5.00),
	(39, 'Venta de electrodomésticos, repuestos, equipos y derivados', 5.00),
	(40, 'Ventas de bicicletas y repuestos', 5.00),
	(41, 'Aliños', 2.51),
	(44, 'Arepera', 2.50);

-- Volcando estructura para tabla seramermvc.job_positions
CREATE TABLE IF NOT EXISTS `job_positions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Catálogo de puestos de trabajo en la organización';

-- Volcando datos para la tabla seramermvc.job_positions: ~5 rows (aproximadamente)
DELETE FROM `job_positions`;
INSERT INTO `job_positions` (`id`, `name`) VALUES
	(1, 'Director test'),
	(2, 'Gerente'),
	(3, 'Secretaria'),
	(5, 'Desarrollador'),
	(6, 'test');

-- Volcando estructura para tabla seramermvc.leave_requests
CREATE TABLE IF NOT EXISTS `leave_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `staff_id` int NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `document_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `request_date` date NOT NULL,
  `approval_date` date DEFAULT NULL,
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_staff_id` (`staff_id`),
  KEY `idx_request_date` (`request_date`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`type`),
  CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Gestiona las solicitudes de permisos del personal';

-- Volcando datos para la tabla seramermvc.leave_requests: ~0 rows (aproximadamente)
DELETE FROM `leave_requests`;

-- Volcando estructura para tabla seramermvc.market_stalls
CREATE TABLE IF NOT EXISTS `market_stalls` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sector_id` int NOT NULL COMMENT 'Sector donde está ubicado',
  `stall_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Número o identificador del local',
  `location_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Descripción detallada de la ubicación',
  PRIMARY KEY (`id`),
  KEY `sector_id` (`sector_id`),
  CONSTRAINT `market_stalls_ibfk_1` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Puestos físicos o locales dentro del mercado';

-- Volcando datos para la tabla seramermvc.market_stalls: ~7 rows (aproximadamente)
DELETE FROM `market_stalls`;
INSERT INTO `market_stalls` (`id`, `sector_id`, `stall_number`, `location_description`) VALUES
	(1, 1, 'L-001', 'testing'),
	(3, 6, 'L-001', 'test'),
	(5, 5, 'L-008', NULL),
	(6, 8, 'L-010', NULL),
	(7, 21, 'm-01', ''),
	(8, 7, 'EA-01', ''),
	(9, 21, 'L-0078', NULL);

-- Volcando estructura para tabla seramermvc.payment_methods
CREATE TABLE IF NOT EXISTS `payment_methods` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del método de pago',
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Nombre del método de pago (Efectivo, Transferencia, etc.)',
  `is_active` tinyint(1) DEFAULT '1' COMMENT 'Indica si el método está activo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Catálogo de métodos de pago aceptados';

-- Volcando datos para la tabla seramermvc.payment_methods: ~6 rows (aproximadamente)
DELETE FROM `payment_methods`;
INSERT INTO `payment_methods` (`id`, `name`, `is_active`) VALUES
	(1, 'Efectivo', 1),
	(2, 'Transferencia Bancaria', 1),
	(3, 'Pago Móvil', 1),
	(4, 'Tarjeta de Débito', 1),
	(5, 'Biopago', 1),
	(6, 'Dólares', 1);

-- Volcando estructura para tabla seramermvc.sectors
CREATE TABLE IF NOT EXISTS `sectors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `zone_id` int NOT NULL COMMENT 'ID de la zona a la que pertenece',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Nombre del sector',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'Descripción del sector',
  PRIMARY KEY (`id`),
  KEY `zone_id` (`zone_id`),
  CONSTRAINT `sectors_ibfk_1` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Sectores específicos dentro de cada zona del mercado';

-- Volcando datos para la tabla seramermvc.sectors: ~21 rows (aproximadamente)
DELETE FROM `sectors`;
INSERT INTO `sectors` (`id`, `zone_id`, `name`, `description`) VALUES
	(1, 1, 'MERCADO NUEVO INTERNO: MESONES Y CUBICULOS', NULL),
	(2, 1, 'MERCADO NUEVO EXTERNO - PASILLO ESTILITA', NULL),
	(3, 1, 'MERCADO NUEVO EXTERNO - PASILLO VERDE', NULL),
	(4, 1, 'MERCADO NUEVO EXTERNO - PASILLO NELLY', NULL),
	(5, 1, 'MERCADO NUEVO EXTERNO - PASILLO DE LA VIDA', NULL),
	(6, 1, 'JAULA DE PESCADO', NULL),
	(7, 2, 'EDIFICIO ADMINISTRATIVO', NULL),
	(8, 2, 'BOULEVAR', ''),
	(9, 2, 'PLATANOS', NULL),
	(10, 2, 'ESTAC. VICTORIA', NULL),
	(11, 2, 'BOULEVAR EMPANADAS', NULL),
	(12, 2, 'MEJILLÓN', NULL),
	(13, 2, 'LAS FLORES', NULL),
	(14, 2, 'LAS CARNES', NULL),
	(15, 3, 'PASILLO ROJO', NULL),
	(16, 3, 'PATIO', NULL),
	(17, 3, 'QUESO AZUL', NULL),
	(18, 3, 'SUPER CARNE', NULL),
	(19, 3, 'EL PATIO', NULL),
	(20, 3, 'CHATARRERO', NULL),
	(21, 3, 'CALLE MERCADITO', NULL);

-- Volcando estructura para tabla seramermvc.staff
CREATE TABLE IF NOT EXISTS `staff` (
  `id` int NOT NULL AUTO_INCREMENT,
  `academic_degree_id` int DEFAULT NULL,
  `academic_specialization_id` int DEFAULT NULL,
  `job_position_id` int NOT NULL,
  `department_id` int NOT NULL,
  `division_id` int DEFAULT NULL,
  `id_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Número de cédula o identificación',
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `middle_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `second_last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` tinyint(1) DEFAULT NULL COMMENT 'TRUE para Femenino, FALSE para Masculino',
  `hire_date` date NOT NULL,
  `termination_date` date DEFAULT NULL,
  `status` enum('active','inactive','vacation','leave','suspended') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_number` (`id_number`),
  KEY `idx_id_number` (`id_number`),
  KEY `idx_department_id` (`department_id`),
  KEY `idx_division_id` (`division_id`),
  KEY `idx_status` (`status`),
  KEY `idx_hire_date` (`hire_date`),
  KEY `idx_full_name` (`first_name`,`last_name`),
  KEY `idx_job_position` (`job_position_id`),
  KEY `academic_degree_id` (`academic_degree_id`),
  KEY `academic_specialization_id` (`academic_specialization_id`),
  CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`academic_degree_id`) REFERENCES `academic_degrees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `staff_ibfk_2` FOREIGN KEY (`academic_specialization_id`) REFERENCES `academic_specializations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `staff_ibfk_3` FOREIGN KEY (`job_position_id`) REFERENCES `job_positions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `staff_ibfk_4` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `staff_ibfk_5` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tabla principal que almacena toda la información del personal';

-- Volcando datos para la tabla seramermvc.staff: ~9 rows (aproximadamente)
DELETE FROM `staff`;
INSERT INTO `staff` (`id`, `academic_degree_id`, `academic_specialization_id`, `job_position_id`, `department_id`, `division_id`, `id_number`, `first_name`, `middle_name`, `last_name`, `second_last_name`, `birth_date`, `gender`, `hire_date`, `termination_date`, `status`, `created_at`, `updated_at`) VALUES
	(1, 3, 2, 1, 1, NULL, 'V12345678', 'María', NULL, 'González', NULL, '1980-05-15', 1, '2015-03-10', NULL, 'active', '2025-08-02 21:28:46', '2025-08-02 21:28:46'),
	(2, 3, 1, 1, 2, NULL, 'V23456789', 'Carlos', NULL, 'Pérez', NULL, '1978-11-22', 0, '2016-07-20', NULL, 'active', '2025-08-02 21:28:46', '2025-08-02 21:28:46'),
	(3, 4, 3, 1, 3, NULL, 'V34567890', 'Ana', NULL, 'Rodríguez', NULL, '1982-08-30', 1, '2017-01-15', NULL, 'active', '2025-08-02 21:28:46', '2025-08-02 21:28:46'),
	(4, 4, 5, 1, 4, NULL, 'V45678901', 'Luis', NULL, 'Martínez', NULL, '1975-04-18', 0, '2014-09-05', NULL, 'active', '2025-08-02 21:28:46', '2025-08-02 21:28:46'),
	(5, 3, 4, 5, 1, NULL, 'V56789012', 'Pedro', NULL, 'López', NULL, '1990-07-25', 0, '2019-05-10', NULL, 'active', '2025-08-02 21:28:46', '2025-08-02 21:28:46'),
	(6, 3, 4, 5, 2, NULL, 'V67890123', 'Sofía', NULL, 'Hernández', NULL, '1992-03-18', 1, '2020-02-15', NULL, 'active', '2025-08-02 21:28:46', '2025-08-02 21:28:46'),
	(7, 3, 4, 5, 3, NULL, 'V78901234', 'Jorge', NULL, 'Díaz', NULL, '1988-11-30', 0, '2018-08-22', NULL, 'active', '2025-08-02 21:28:46', '2025-08-02 21:28:46'),
	(8, 3, 4, 5, 4, NULL, 'V89012345', 'Laura', NULL, 'Torres', NULL, '1991-09-05', 1, '2021-01-10', NULL, 'active', '2025-08-02 21:28:46', '2025-08-02 21:28:46'),
	(9, 3, 2, 3, 1, NULL, 'V21564385', 'Andres', NULL, 'Figueroa', NULL, '1997-02-12', 0, '2020-08-02', NULL, 'active', '2025-08-02 22:53:09', '2025-08-02 22:53:10'),
	(10, 3, 1, 6, 2, NULL, 'V28456987', 'Ana ', 'Laura', 'Rojas', 'Perez', '1994-08-04', 1, '2020-08-04', NULL, 'active', '2025-08-04 16:40:22', '2025-08-04 16:40:23'),
	(11, 2, 1, 3, 2, NULL, 'V22321456', 'Felipe', 'Alejandro', 'Rodriguez', 'Ordaz', '1993-08-04', 0, '2022-08-04', NULL, 'active', '2025-08-04 16:41:40', '2025-08-04 16:41:40');

-- Volcando estructura para vista seramermvc.staff_complete_info
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `staff_complete_info` (
	`id` INT NOT NULL,
	`id_number` VARCHAR(1) NOT NULL COMMENT 'Número de cédula o identificación' COLLATE 'utf8mb4_general_ci',
	`full_name` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_general_ci',
	`first_name` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_general_ci',
	`middle_name` VARCHAR(1) NULL COLLATE 'utf8mb4_general_ci',
	`last_name` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_general_ci',
	`second_last_name` VARCHAR(1) NULL COLLATE 'utf8mb4_general_ci',
	`birth_date` DATE NULL,
	`gender_text` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`hire_date` DATE NOT NULL,
	`termination_date` DATE NULL,
	`status` ENUM('active','inactive','vacation','leave','suspended') NULL COLLATE 'utf8mb4_general_ci',
	`department_name` VARCHAR(1) NULL COLLATE 'utf8mb4_general_ci',
	`division_name` VARCHAR(1) NULL COLLATE 'utf8mb4_general_ci',
	`job_position_name` VARCHAR(1) NULL COLLATE 'utf8mb4_general_ci',
	`academic_degree_name` VARCHAR(1) NULL COLLATE 'utf8mb4_general_ci',
	`academic_specialization_name` VARCHAR(1) NULL COLLATE 'utf8mb4_general_ci',
	`manager_name` VARCHAR(1) NULL COLLATE 'utf8mb4_general_ci',
	`created_at` TIMESTAMP NULL,
	`updated_at` TIMESTAMP NULL
);

-- Volcando estructura para tabla seramermvc.staff_department_history
CREATE TABLE IF NOT EXISTS `staff_department_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `staff_id` int NOT NULL,
  `department_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_staff_id` (`staff_id`),
  KEY `idx_department_id` (`department_id`),
  KEY `idx_date_range` (`start_date`,`end_date`),
  CONSTRAINT `staff_department_history_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `staff_department_history_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registra los cambios de departamento del personal a lo largo del tiempo';

-- Volcando datos para la tabla seramermvc.staff_department_history: ~0 rows (aproximadamente)
DELETE FROM `staff_department_history`;

-- Volcando estructura para tabla seramermvc.user_departments
CREATE TABLE IF NOT EXISTS `user_departments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `department_id` int NOT NULL,
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'inactive',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_department` (`user_id`,`department_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_department_id` (`department_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `user_departments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_departments_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Controla qué usuarios tienen acceso a qué departamentos';

-- Volcando datos para la tabla seramermvc.user_departments: ~10 rows (aproximadamente)
DELETE FROM `user_departments`;
INSERT INTO `user_departments` (`id`, `user_id`, `department_id`, `status`, `created_at`) VALUES
	(1, 1, 1, 'active', '2025-08-02 21:28:47'),
	(2, 2, 2, 'active', '2025-08-02 21:28:47'),
	(3, 3, 3, 'active', '2025-08-02 21:28:47'),
	(4, 4, 4, 'active', '2025-08-02 21:28:47'),
	(5, 5, 1, 'active', '2025-08-02 21:28:47'),
	(6, 6, 2, 'active', '2025-08-02 21:28:47'),
	(7, 7, 3, 'active', '2025-08-02 21:28:47'),
	(8, 8, 4, 'active', '2025-08-02 21:28:47'),
	(9, 9, 1, 'active', '2025-08-03 02:56:10'),
	(10, 10, 1, 'active', '2025-08-03 13:36:11'),
	(11, 11, 2, 'active', '2025-08-04 16:42:11'),
	(12, 12, 2, 'active', '2025-08-04 16:42:40');

-- Volcando estructura para tabla seramermvc.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `staff_id` int DEFAULT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `password_reset_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password_reset_expires` datetime DEFAULT NULL,
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'inactive',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `unique_email` (`email`),
  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`),
  KEY `idx_status` (`status`),
  KEY `idx_staff_id` (`staff_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Almacena las credenciales de acceso al sistema';

-- Volcando datos para la tabla seramermvc.users: ~13 rows (aproximadamente)
DELETE FROM `users`;
INSERT INTO `users` (`id`, `staff_id`, `username`, `password_hash`, `email`, `last_login`, `password_reset_token`, `password_reset_expires`, `status`, `created_at`, `updated_at`) VALUES
	(1, 1, 'mmaria', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'maria.gonzalez@empresa.com', '2025-11-28 21:24:37', NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-11-29 01:24:37'),
	(2, 2, 'cperez', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'carlos.perez@empresa.com', '2025-10-28 22:40:52', NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-10-29 02:40:52'),
	(3, 3, 'arodriguez', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'ana.rodriguez@empresa.com', NULL, NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-03 13:39:43'),
	(4, 4, 'lmartinez', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'luis.martinez@empresa.com', '2025-10-28 22:41:24', NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-10-29 02:41:24'),
	(5, NULL, 'devliq', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'devliquidacion@empresa.com', '2025-08-03 16:50:17', NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-03 20:50:17'),
	(6, NULL, 'devcob', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'devcobranza@empresa.com', NULL, NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-03 13:39:47'),
	(7, NULL, 'devrrhh', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'devrrhh@empresa.com', NULL, NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-03 13:39:48'),
	(8, NULL, 'devfisc', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'devfiscalizacion@empresa.com', '2025-10-28 22:45:26', NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-10-29 02:45:26'),
	(9, 9, 'afigueroa', '$2y$12$iFj3D7pQ3wCsdkCs4nU5O.Z0rBgK4ydNpbph5RumpqlqLj6q96SuO', 'Andres.Figueroa@empresa.com', NULL, NULL, NULL, 'active', '2025-08-03 02:56:10', '2025-08-03 02:56:10'),
	(10, 5, 'plopez', '$2y$12$.Xv3sGjkrCSNnlJmdyz1j.sxfCYf2C/09OvOa794nxeA2sWCwX6WC', 'pedro.lopez@empresa.com', NULL, NULL, NULL, 'active', '2025-08-03 13:36:10', '2025-08-03 15:33:38'),
	(11, 10, 'arojas', '$2y$12$DxcEraAN3tao8.z.FaOgsuz5jP39VqoFpDSQU3qZDgioePvAK6vh6', 'ana.rojas@empresa.com', '2025-10-28 22:42:52', NULL, NULL, 'active', '2025-08-04 16:42:11', '2025-10-29 02:42:52'),
	(12, 11, 'frodriguez', '$2y$12$32yiS2OUJt5hgQzoB/NCiuIdgb2Yvvh8L56GOlYsY9Kh9iUnM4Ri2', 'felipe.rodriguez@empresa.com', '2025-10-28 22:43:12', NULL, NULL, 'active', '2025-08-04 16:42:40', '2025-10-29 02:43:12'),
	(13, NULL, 'carlos', '$argon2id$v=19$m=65536,t=4,p=1$YXl4czNFSHN2N0NLZ2JPLg$BKhxwNylzoaUlEMGnbE+MLifhZqqxpQTlFIltsmaaNM', 'carlos@example.com', NULL, NULL, NULL, 'active', '2025-10-29 01:20:39', '2025-10-29 01:20:39');

-- Volcando estructura para tabla seramermvc.vacations
CREATE TABLE IF NOT EXISTS `vacations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `staff_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('requested','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'requested',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_staff_id` (`staff_id`),
  KEY `idx_date_range` (`start_date`,`end_date`),
  KEY `idx_status` (`status`),
  CONSTRAINT `vacations_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Administra las solicitudes y estados de vacaciones del personal';

-- Volcando datos para la tabla seramermvc.vacations: ~0 rows (aproximadamente)
DELETE FROM `vacations`;

-- Volcando estructura para tabla seramermvc.zones
CREATE TABLE IF NOT EXISTS `zones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Nombre de la zona',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'Descripción detallada de la zona',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Zonas o áreas principales del mercado';

-- Volcando datos para la tabla seramermvc.zones: ~4 rows (aproximadamente)
DELETE FROM `zones`;
INSERT INTO `zones` (`id`, `name`, `description`) VALUES
	(1, 'ZONAS  DE VENTA 1', ''),
	(2, 'ZONAS  DE VENTA 2', NULL),
	(3, 'ZONAS  DE VENTA 3', NULL);

-- Volcando estructura para disparador seramermvc.staff_updated_at_trigger
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `staff_updated_at_trigger` BEFORE UPDATE ON `staff` FOR EACH ROW BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador seramermvc.users_updated_at_trigger
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `users_updated_at_trigger` BEFORE UPDATE ON `users` FOR EACH ROW BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador seramermvc.vacations_updated_at_trigger
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `vacations_updated_at_trigger` BEFORE UPDATE ON `vacations` FOR EACH ROW BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `staff_complete_info`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `staff_complete_info` AS select `s`.`id` AS `id`,`s`.`id_number` AS `id_number`,concat(`s`.`first_name`,' ',ifnull(`s`.`middle_name`,''),' ',`s`.`last_name`,' ',ifnull(`s`.`second_last_name`,'')) AS `full_name`,`s`.`first_name` AS `first_name`,`s`.`middle_name` AS `middle_name`,`s`.`last_name` AS `last_name`,`s`.`second_last_name` AS `second_last_name`,`s`.`birth_date` AS `birth_date`,(case when (`s`.`gender` = true) then 'Femenino' else 'Masculino' end) AS `gender_text`,`s`.`hire_date` AS `hire_date`,`s`.`termination_date` AS `termination_date`,`s`.`status` AS `status`,`d`.`name` AS `department_name`,`dv`.`name` AS `division_name`,`jp`.`name` AS `job_position_name`,`ad`.`name` AS `academic_degree_name`,`asp`.`name` AS `academic_specialization_name`,concat(`m`.`first_name`,' ',`m`.`last_name`) AS `manager_name`,`s`.`created_at` AS `created_at`,`s`.`updated_at` AS `updated_at` from ((((((`staff` `s` left join `departments` `d` on((`s`.`department_id` = `d`.`id`))) left join `divisions` `dv` on((`s`.`division_id` = `dv`.`id`))) left join `job_positions` `jp` on((`s`.`job_position_id` = `jp`.`id`))) left join `academic_degrees` `ad` on((`s`.`academic_degree_id` = `ad`.`id`))) left join `academic_specializations` `asp` on((`s`.`academic_specialization_id` = `asp`.`id`))) left join `staff` `m` on((`d`.`manager_id` = `m`.`id`)))
;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
