-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.4.5 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.11.0.7065
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
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
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
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
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
  `action` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Tipo de acción realizada (login, insert, update, delete)',
  `table_affected` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `record_id` int DEFAULT NULL COMMENT 'ID del registro afectado',
  `old_values` json DEFAULT NULL COMMENT 'Valores anteriores en formato JSON',
  `new_values` json DEFAULT NULL COMMENT 'Valores nuevos en formato JSON',
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_table_affected` (`table_affected`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_record_id` (`record_id`),
  CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registra todas las acciones importantes realizadas por los usuarios en el sistema';

-- Volcando datos para la tabla seramermvc.audit_log: ~0 rows (aproximadamente)
DELETE FROM `audit_log`;

-- Volcando estructura para tabla seramermvc.departments
CREATE TABLE IF NOT EXISTS `departments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `shift_type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
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

-- Volcando estructura para tabla seramermvc.department_schedules
CREATE TABLE IF NOT EXISTS `department_schedules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `department_id` int NOT NULL,
  `day` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_department_id` (`department_id`),
  KEY `idx_day` (`day`),
  KEY `idx_time_range` (`start_time`,`end_time`),
  CONSTRAINT `department_schedules_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Define los horarios de trabajo por departamento';

-- Volcando datos para la tabla seramermvc.department_schedules: ~0 rows (aproximadamente)
DELETE FROM `department_schedules`;

-- Volcando estructura para tabla seramermvc.divisions
CREATE TABLE IF NOT EXISTS `divisions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `department_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
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

-- Volcando estructura para tabla seramermvc.job_positions
CREATE TABLE IF NOT EXISTS `job_positions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Catálogo de puestos de trabajo en la organización';

-- Volcando datos para la tabla seramermvc.job_positions: ~5 rows (aproximadamente)
DELETE FROM `job_positions`;
INSERT INTO `job_positions` (`id`, `name`) VALUES
	(1, 'Director'),
	(2, 'Gerente'),
	(3, 'Analista'),
	(4, 'Asistente'),
	(5, 'Desarrollador');

-- Volcando estructura para tabla seramermvc.leave_requests
CREATE TABLE IF NOT EXISTS `leave_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `staff_id` int NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `reason` text COLLATE utf8mb4_general_ci,
  `document_url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `request_date` date NOT NULL,
  `approval_date` date DEFAULT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_general_ci DEFAULT 'pending',
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

-- Volcando estructura para tabla seramermvc.staff
CREATE TABLE IF NOT EXISTS `staff` (
  `id` int NOT NULL AUTO_INCREMENT,
  `academic_degree_id` int DEFAULT NULL,
  `academic_specialization_id` int DEFAULT NULL,
  `job_position_id` int NOT NULL,
  `department_id` int NOT NULL,
  `division_id` int DEFAULT NULL,
  `id_number` varchar(20) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Número de cédula o identificación',
  `first_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `middle_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `second_last_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` tinyint(1) DEFAULT NULL COMMENT 'TRUE para Femenino, FALSE para Masculino',
  `hire_date` date NOT NULL,
  `termination_date` date DEFAULT NULL,
  `status` enum('active','inactive','vacation','leave','suspended') COLLATE utf8mb4_general_ci DEFAULT 'active',
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tabla principal que almacena toda la información del personal';

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
	(9, 3, 2, 3, 1, NULL, 'V21564385', 'Andres', NULL, 'Figueroa', NULL, '1997-02-12', 0, '2020-08-02', NULL, 'active', '2025-08-02 22:53:09', '2025-08-02 22:53:10');

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
  `reason` text COLLATE utf8mb4_general_ci,
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

-- Volcando estructura para tabla seramermvc.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `staff_id` int DEFAULT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `password_reset_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password_reset_expires` datetime DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_general_ci DEFAULT 'inactive',
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Almacena las credenciales de acceso al sistema';

-- Volcando datos para la tabla seramermvc.users: ~10 rows (aproximadamente)
DELETE FROM `users`;
INSERT INTO `users` (`id`, `staff_id`, `username`, `password_hash`, `email`, `last_login`, `password_reset_token`, `password_reset_expires`, `status`, `created_at`, `updated_at`) VALUES
	(1, 1, 'mmaria', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'maria.gonzalez@empresa.com', '2025-08-03 12:13:08', NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-03 16:13:08'),
	(2, 2, 'cperez', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'carlos.perez@empresa.com', NULL, NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-03 13:39:42'),
	(3, 3, 'arodriguez', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'ana.rodriguez@empresa.com', NULL, NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-03 13:39:43'),
	(4, 4, 'lmartinez', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'luis.martinez@empresa.com', '2025-08-03 13:06:37', NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-03 17:06:37'),
	(5, NULL, 'devliq', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'devliquidacion@empresa.com', NULL, NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-03 13:39:46'),
	(6, NULL, 'devcob', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'devcobranza@empresa.com', NULL, NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-03 13:39:47'),
	(7, NULL, 'devrrhh', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'devrrhh@empresa.com', NULL, NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-03 13:39:48'),
	(8, NULL, 'devfisc', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'devfiscalizacion@empresa.com', NULL, NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-03 13:39:50'),
	(9, 9, 'afigueroa', '$2y$12$iFj3D7pQ3wCsdkCs4nU5O.Z0rBgK4ydNpbph5RumpqlqLj6q96SuO', 'Andres.Figueroa@empresa.com', NULL, NULL, NULL, 'active', '2025-08-03 02:56:10', '2025-08-03 02:56:10'),
	(10, 5, 'plopez', '$2y$12$.Xv3sGjkrCSNnlJmdyz1j.sxfCYf2C/09OvOa794nxeA2sWCwX6WC', 'pedro.lopez@empresa.com', NULL, NULL, NULL, 'active', '2025-08-03 13:36:10', '2025-08-03 15:33:38');

-- Volcando estructura para tabla seramermvc.user_departments
CREATE TABLE IF NOT EXISTS `user_departments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `department_id` int NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_general_ci DEFAULT 'inactive',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_department` (`user_id`,`department_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_department_id` (`department_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `user_departments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_departments_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Controla qué usuarios tienen acceso a qué departamentos';

-- Volcando datos para la tabla seramermvc.user_departments: ~8 rows (aproximadamente)
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
	(10, 10, 1, 'active', '2025-08-03 13:36:11');

-- Volcando estructura para tabla seramermvc.vacations
CREATE TABLE IF NOT EXISTS `vacations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `staff_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('requested','approved','rejected') COLLATE utf8mb4_general_ci DEFAULT 'requested',
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
