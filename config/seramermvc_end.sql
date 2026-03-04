-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-03-2026 a las 07:43:24
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `seramermvc`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `academic_degrees`
--

CREATE TABLE `academic_degrees` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Catálogo de grados académicos (licenciatura, maestría, etc.)';

--
-- Volcado de datos para la tabla `academic_degrees`
--

INSERT INTO `academic_degrees` (`id`, `name`) VALUES
(1, 'Bachiller'),
(5, 'Doctorado'),
(3, 'Licenciatura'),
(4, 'Maestría'),
(2, 'Técnico Superior');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `academic_specializations`
--

CREATE TABLE `academic_specializations` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Catálogo de especializaciones o menciones académicas';

--
-- Volcado de datos para la tabla `academic_specializations`
--

INSERT INTO `academic_specializations` (`id`, `name`) VALUES
(1, 'Administración'),
(2, 'Contabilidad'),
(3, 'Derecho'),
(4, 'Informática'),
(5, 'Recursos Humanos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administrative_appeals`
--

CREATE TABLE `administrative_appeals` (
  `appeal_id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL,
  `appeal_type` varchar(100) NOT NULL,
  `filing_date` timestamp NULL DEFAULT current_timestamp(),
  `arguments` text NOT NULL,
  `appeal_status` varchar(50) NOT NULL DEFAULT 'In Review',
  `resolution_date` timestamp NULL DEFAULT NULL,
  `resolution_details` text DEFAULT NULL,
  `resolution_authority` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administrative_records`
--

CREATE TABLE `administrative_records` (
  `record_id` int(11) NOT NULL,
  `infraction_id` int(11) NOT NULL,
  `open_date` timestamp NULL DEFAULT current_timestamp(),
  `record_status` varchar(50) NOT NULL DEFAULT 'Open',
  `close_date` timestamp NULL DEFAULT NULL,
  `final_decision` text DEFAULT NULL,
  `decision_authority` varchar(150) DEFAULT NULL,
  `notification_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alert_tracking`
--

CREATE TABLE `alert_tracking` (
  `tracking_id` int(11) NOT NULL,
  `alert_id` int(11) NOT NULL,
  `action_date` timestamp NULL DEFAULT current_timestamp(),
  `action_type` varchar(100) NOT NULL,
  `action_description` text DEFAULT NULL,
  `performed_by_user_id` int(11) NOT NULL,
  `action_result` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alert_types`
--

CREATE TABLE `alert_types` (
  `alert_type_id` int(11) NOT NULL,
  `alert_type_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `automatic_escalation` tinyint(1) DEFAULT 0,
  `days_to_escalate` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `attached_documents`
--

CREATE TABLE `attached_documents` (
  `document_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `storage_url` text NOT NULL,
  `upload_date` timestamp NULL DEFAULT current_timestamp(),
  `uploaded_by_user_id` int(11) NOT NULL,
  `associated_entity_id` int(11) DEFAULT NULL,
  `associated_table_name` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `is_special` tinyint(1) DEFAULT 0 COMMENT 'Para marcar asistencias especiales o excepcionales',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registra las asistencias del personal';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL COMMENT 'Tipo de acción realizada (login, insert, update, delete)',
  `table_affected` varchar(50) NOT NULL,
  `record_id` int(11) DEFAULT NULL COMMENT 'ID del registro afectado',
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Valores anteriores en formato JSON' CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Valores nuevos en formato JSON' CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registra todas las acciones importantes realizadas por los usuarios en el sistema';

--
-- Volcado de datos para la tabla `audit_log`
--

INSERT INTO `audit_log` (`id`, `user_id`, `action`, `table_affected`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 15:01:58'),
(2, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 15:04:40'),
(3, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 23:44:04'),
(4, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 00:00:05'),
(5, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 02:12:38'),
(6, 5, 'logout', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 02:49:06'),
(7, 6, 'login', 'users', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 02:49:14'),
(8, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 02:49:22'),
(9, 1, 'logout', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 03:00:50'),
(10, 6, 'login', 'users', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 03:00:56'),
(11, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 03:01:45'),
(12, 5, 'logout', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 03:11:56'),
(13, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 03:12:11'),
(14, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 03:12:18'),
(15, 5, 'logout', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 03:39:00'),
(16, 6, 'login', 'users', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 03:39:05'),
(17, 6, 'logout', 'users', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 03:39:56'),
(18, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 03:40:03'),
(19, 1, 'logout', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 03:40:15'),
(20, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 03:40:25'),
(21, 5, 'logout', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 03:56:05'),
(22, 6, 'login', 'users', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 03:56:11'),
(23, 6, 'logout', 'users', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 03:59:56'),
(24, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 04:10:02'),
(25, 5, 'logout', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 04:25:14'),
(26, 6, 'login', 'users', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 04:25:19'),
(27, 6, 'logout', 'users', 6, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 04:53:37'),
(28, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 13:29:04'),
(29, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-22 23:55:57'),
(30, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-23 13:28:13'),
(31, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-23 16:36:25'),
(32, 8, 'login', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-23 22:35:10'),
(33, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-23 22:35:17'),
(34, 5, 'login', 'users', 5, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 01:21:51'),
(35, 5, 'insert', 'contracts', 11, NULL, '{\"awardee_id\":3,\"fiscal_year_id\":1,\"start_date\":\"2026-02-24\",\"end_date\":\"2026-12-31\",\"type\":\"simultaneous\",\"contract_mode\":\"monthly\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 05:41:18'),
(36, 5, 'insert', 'contracts', 12, NULL, '{\"awardee_id\":4,\"fiscal_year_id\":1,\"start_date\":\"2026-02-24\",\"end_date\":\"2026-12-31\",\"type\":\"simultaneous\",\"contract_mode\":\"monthly\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 05:42:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `log_datetime` timestamp NULL DEFAULT current_timestamp(),
  `action_type` varchar(100) NOT NULL,
  `affected_table_name` varchar(100) NOT NULL,
  `affected_record_id` int(11) NOT NULL,
  `action_details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `awardees`
--

CREATE TABLE `awardees` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL COMMENT 'Primer nombre',
  `middle_name` varchar(50) DEFAULT NULL COMMENT 'Segundo nombre',
  `last_name` varchar(50) NOT NULL COMMENT 'Primer apellido',
  `second_last_name` varchar(50) DEFAULT NULL COMMENT 'Segundo apellido',
  `id_number` varchar(20) NOT NULL COMMENT 'Número de cédula o identificación',
  `phone` varchar(20) DEFAULT NULL COMMENT 'Número de teléfono',
  `email` varchar(100) DEFAULT NULL COMMENT 'Correo electrónico',
  `address` text DEFAULT NULL COMMENT 'Dirección completa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Personas a quienes se adjudican espacios en el mercado';

--
-- Volcado de datos para la tabla `awardees`
--

INSERT INTO `awardees` (`id`, `first_name`, `middle_name`, `last_name`, `second_last_name`, `id_number`, `phone`, `email`, `address`) VALUES
(1, 'Juan', NULL, 'Pérez', NULL, 'V-12345678', '0414-0000000', 'user0@test.com', 'Calle Falsa 123, Sector Centro'),
(2, 'María', NULL, 'Gómez', NULL, 'V-87654321', '0414-0000001', 'user1@test.com', 'Calle Falsa 123, Sector Centro'),
(3, 'Carlos', NULL, 'López', NULL, 'V-11223344', '0414-0000002', 'user2@test.com', 'Calle Falsa 123, Sector Centro'),
(4, 'Ana', NULL, 'Martínez', NULL, 'V-55667788', '0414-0000003', 'user3@test.com', 'Calle Falsa 123, Sector Centro'),
(5, 'Pedro', NULL, 'Rodríguez', NULL, 'V-99887766', '0414-0000004', 'user4@test.com', 'Calle Falsa 123, Sector Centro'),
(6, 'Luis', NULL, 'Fernández', NULL, 'V-12341234', '0414-0000005', 'user5@test.com', 'Calle Falsa 123, Sector Centro'),
(7, 'Carmen', NULL, 'García', NULL, 'V-44556677', '0414-0000006', 'user6@test.com', 'Calle Falsa 123, Sector Centro'),
(8, 'José', NULL, 'Sánchez', NULL, 'V-88990011', '0414-0000007', 'user7@test.com', 'Calle Falsa 123, Sector Centro'),
(9, 'Rosa', NULL, 'Díaz', NULL, 'V-22114455', '0414-0000008', 'user8@test.com', 'Calle Falsa 123, Sector Centro'),
(10, 'Miguel', NULL, 'Torres', NULL, 'V-66778899', '0414-0000009', 'user9@test.com', 'Calle Falsa 123, Sector Centro'),
(11, 'Test', 'test', 'test', 'test', '28129366', '04248536876', '', 'Bloque #13. Playa Grande');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cash_registers`
--

CREATE TABLE `cash_registers` (
  `id` int(11) NOT NULL COMMENT 'Identificador único de la caja',
  `user_id` int(11) NOT NULL COMMENT 'ID del usuario asignado a la caja',
  `name` varchar(100) NOT NULL COMMENT 'Nombre descriptivo de la caja (ej: Caja 1 - Recepción)',
  `status` enum('active','inactive','maintenance') DEFAULT 'active' COMMENT 'Estado operativo',
  `created_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Fecha de creación',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Última actualización'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro de cajas de cobro asignadas a personal';

--
-- Volcado de datos para la tabla `cash_registers`
--

INSERT INTO `cash_registers` (`id`, `user_id`, `name`, `status`, `created_at`, `updated_at`) VALUES
(1, 12, 'Caja 1 - Cobranza', 'active', '2025-08-04 16:54:44', '2025-08-04 16:57:29'),
(2, 6, 'Caja 2 ', 'active', '2026-01-18 18:09:26', '2026-01-18 18:09:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citations`
--

CREATE TABLE `citations` (
  `citation_id` int(11) NOT NULL,
  `infraction_id` int(11) NOT NULL,
  `citation_datetime` timestamp NULL DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `mediator_user_id` int(11) NOT NULL,
  `citation_status` varchar(50) DEFAULT 'Scheduled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `citations`
--

INSERT INTO `citations` (`citation_id`, `infraction_id`, `citation_datetime`, `location`, `mediator_user_id`, `citation_status`) VALUES
(5, 17, '2026-03-18 16:27:00', 'CALLE CANTAURA', 8, 'Resuelta'),
(6, 21, '2026-03-18 16:27:00', 'TEST', 8, 'Scheduled'),
(7, 19, '2026-03-17 19:35:00', 'SERAMER', 8, 'Scheduled');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `complaints`
--

CREATE TABLE `complaints` (
  `complaint_id` int(11) NOT NULL,
  `complaint_datetime` timestamp NULL DEFAULT current_timestamp(),
  `client_user_id` int(11) DEFAULT NULL,
  `client_name` varchar(150) DEFAULT NULL,
  `client_phone` varchar(50) DEFAULT NULL,
  `client_email` varchar(100) DEFAULT NULL,
  `complaint_description` text NOT NULL,
  `stall_id` int(11) DEFAULT NULL,
  `awardee_id` int(11) DEFAULT NULL,
  `complaint_type` varchar(100) NOT NULL,
  `complaint_status` varchar(50) NOT NULL DEFAULT 'Received',
  `complaint_priority` varchar(50) NOT NULL DEFAULT 'Medium',
  `internal_observations` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `complaints`
--

INSERT INTO `complaints` (`complaint_id`, `complaint_datetime`, `client_user_id`, `client_name`, `client_phone`, `client_email`, `complaint_description`, `stall_id`, `awardee_id`, `complaint_type`, `complaint_status`, `complaint_priority`, `internal_observations`) VALUES
(9, '2025-09-19 02:43:59', 8, 'pepe veraz', '04264804748', 'pepe@gmail.com', 'sdsdsdsdsd dwsdsd', 1, 2, 'Suggestion', 'Received', 'Medium', 'dsds dsdsd'),
(10, '2025-10-28 03:53:55', 8, 'Martha Figuera', '04248536876', 'daniel.alfonsi2011@gmail.com', 'asdas', 1, 2, 'Suggestion', 'Received', 'Medium', 'asdasd'),
(11, '2026-01-21 05:11:42', NULL, 'Martha Figuera', '04248536876', 'daniel.alfonsi2011@gmail.com', 'asdasasdas', 1, 2, 'Claim', 'In Process', 'Medium', 'asdasdasd'),
(12, '2026-01-21 05:11:56', NULL, 'Martha Figuera', '04248536876', 'daniel.alfonsi2011@gmail.com', 'asdasdas', 1, 2, 'Suggestion', 'Received', 'Medium', 'asdasdasd'),
(13, '2026-01-21 05:59:33', NULL, 'Natera Jeu', '04248536876', 'daniel.alfonsi2011@gmail.com', 'Se robo una patilla ', 1, 2, 'Question', 'In Process', 'Urgent', 'Se ve peligroso'),
(14, '2026-03-04 06:28:36', NULL, 'Martha Figuera', '04248536876', 'daniel.alfonsi2011@gmail.com', 'ES TODO UN TEST', 1, 3, 'Suggestion', 'Received', 'High', 'ES TODO UN TEST'),
(15, '2026-03-04 06:33:23', NULL, 'Martha Figuera', '04248536876', 'daniel.alfonsi2011@gmail.com', 'No se que poner', 5, 2, 'Suggestion', 'In Process', 'Low', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `complaint_related_articles`
--

CREATE TABLE `complaint_related_articles` (
  `complaint_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `association_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `complaint_tracking`
--

CREATE TABLE `complaint_tracking` (
  `tracking_id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `action_datetime` timestamp NULL DEFAULT current_timestamp(),
  `admin_user_id` int(11) NOT NULL,
  `action_type` varchar(100) NOT NULL,
  `action_description` text DEFAULT NULL,
  `action_result` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `complaint_tracking`
--

INSERT INTO `complaint_tracking` (`tracking_id`, `complaint_id`, `action_datetime`, `admin_user_id`, `action_type`, `action_description`, `action_result`) VALUES
(1, 9, '2025-09-24 12:57:32', 1, 'Assignment', 'wsdsdsds sdsd', 'dsds dsds '),
(2, 9, '2025-09-24 13:01:55', 1, 'Resolution', 'hjghjhg', 'jghjgh'),
(3, 10, '2026-01-19 19:23:23', 1, 'Assignment', 'asdasd', 'asdasd'),
(4, 10, '2026-01-21 04:34:47', 10, 'Resolution', 'Ya me devolvio la plata', 'Ya me devolvio la plata'),
(5, 13, '2026-01-21 06:00:34', 1, 'Resolution', 'Devolvio la patilla', 'Devolvio la patilla'),
(6, 13, '2026-03-04 04:49:43', 11, 'Follow-up', 'test', 'test'),
(7, 11, '2026-03-04 04:59:24', 8, 'Follow-up', 'tEST', 'TEST');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compliance_alerts`
--

CREATE TABLE `compliance_alerts` (
  `alert_id` int(11) NOT NULL,
  `alert_type_id` int(11) NOT NULL,
  `awardee_id` int(11) NOT NULL,
  `stall_id` int(11) DEFAULT NULL,
  `generation_date` timestamp NULL DEFAULT current_timestamp(),
  `alert_description` text NOT NULL,
  `alert_status` varchar(50) NOT NULL DEFAULT 'Active',
  `resolution_escalation_date` timestamp NULL DEFAULT NULL,
  `escalated_infraction_id` int(11) DEFAULT NULL,
  `generated_by_user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `conciliation_reports`
--

CREATE TABLE `conciliation_reports` (
  `report_id` int(11) NOT NULL,
  `citation_id` int(11) NOT NULL,
  `awardee_attendance` tinyint(1) NOT NULL,
  `result` varchar(100) NOT NULL,
  `agreement_details` text DEFAULT NULL,
  `report_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `conciliation_reports`
--

INSERT INTO `conciliation_reports` (`report_id`, `citation_id`, `awardee_attendance`, `result`, `agreement_details`, `report_date`) VALUES
(1, 1, 1, 'Agreement Reached', 'dsddsdsd dsdsd dsdsd dsd ', '2025-09-22 02:27:18'),
(2, 1, 1, 'Agreement Reached', 'Se detallaron muchas cosas', '2025-10-28 23:08:07'),
(3, 1, 0, 'Agreement Reached', 'asdasd', '2025-10-29 02:02:04'),
(4, 2, 1, 'Agreement Reached', 'asdadasd', '2026-01-20 00:44:42'),
(5, 3, 1, 'Agreement Reached', 'ACUERDO ALCANZADO', '2026-01-21 04:31:19'),
(6, 4, 1, 'Agreement Reached', 'No va a consumir mas', '2026-01-21 04:50:28'),
(7, 5, 1, 'Agreement Reached', 'EL CLIENTE TUVO LA RAZóN', '2026-03-04 04:26:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contracts`
--

CREATE TABLE `contracts` (
  `id` int(11) NOT NULL,
  `awardee_id` int(11) NOT NULL COMMENT 'Adjudicatario del contrato',
  `fiscal_year_id` int(11) NOT NULL COMMENT 'Año fiscal asociado al contrato',
  `start_date` date NOT NULL COMMENT 'Fecha de inicio del contrato',
  `end_date` date NOT NULL COMMENT 'Fecha de finalización',
  `type` enum('simultaneous','advance') DEFAULT NULL COMMENT 'Tipo de contrato',
  `contract_mode` enum('monthly','weekly') DEFAULT NULL COMMENT 'Modalidad de pago',
  `status` enum('active','renewed','canceled') NOT NULL DEFAULT 'active',
  `status_payment` enum('up to date','delinquent','unable to pay') NOT NULL DEFAULT 'up to date'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Contratos de adjudicación de espacios en el mercado';

--
-- Volcado de datos para la tabla `contracts`
--

INSERT INTO `contracts` (`id`, `awardee_id`, `fiscal_year_id`, `start_date`, `end_date`, `type`, `contract_mode`, `status`, `status_payment`) VALUES
(2, 3, 1, '2026-01-01', '2026-12-31', 'simultaneous', '', 'active', 'up to date'),
(3, 3, 1, '2026-01-01', '2026-12-31', 'simultaneous', '', 'active', 'up to date'),
(4, 4, 1, '2026-01-01', '2026-12-31', 'advance', '', 'active', 'up to date'),
(5, 2, 1, '2026-01-01', '2026-12-31', 'simultaneous', '', 'active', 'up to date'),
(6, 6, 1, '2026-01-01', '2026-12-31', 'simultaneous', '', 'active', 'up to date'),
(7, 7, 1, '2026-01-01', '2026-12-31', 'simultaneous', '', 'active', 'up to date'),
(8, 6, 1, '2026-01-01', '2026-12-31', 'simultaneous', '', 'active', 'up to date'),
(9, 3, 1, '2026-01-01', '2026-12-31', 'simultaneous', '', 'active', 'up to date'),
(10, 5, 1, '2026-01-01', '2026-12-31', 'advance', '', 'renewed', 'delinquent'),
(11, 3, 1, '2026-02-24', '2026-12-31', 'simultaneous', 'monthly', 'active', 'up to date'),
(12, 4, 1, '2026-02-24', '2026-12-31', 'simultaneous', 'monthly', 'active', 'up to date');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contract_business_categories`
--

CREATE TABLE `contract_business_categories` (
  `id` int(11) NOT NULL,
  `contract_id` int(11) NOT NULL COMMENT 'Contrato relacionado',
  `external_category_id` int(11) DEFAULT NULL COMMENT 'Categoría externa (si aplica)',
  `internal_category_id` int(11) DEFAULT NULL COMMENT 'Categoría interna (si aplica)',
  `type` enum('internal','external') DEFAULT NULL COMMENT 'Tipo de categoría'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Relaciona contratos con categorías de negocio';

--
-- Volcado de datos para la tabla `contract_business_categories`
--

INSERT INTO `contract_business_categories` (`id`, `contract_id`, `external_category_id`, `internal_category_id`, `type`) VALUES
(5, 2, NULL, 21, 'internal'),
(7, 11, NULL, 1, 'internal'),
(8, 12, NULL, 5, 'internal'),
(9, 2, NULL, 5, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contract_locations`
--

CREATE TABLE `contract_locations` (
  `id` int(11) NOT NULL,
  `contract_id` int(11) NOT NULL COMMENT 'Contrato relacionado',
  `stall_id` int(11) NOT NULL COMMENT 'Local/puesto asignado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Asignación de locales específicos a contratos';

--
-- Volcado de datos para la tabla `contract_locations`
--

INSERT INTO `contract_locations` (`id`, `contract_id`, `stall_id`) VALUES
(3, 2, 1),
(4, 11, 12),
(5, 12, 16);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contract_payments`
--

CREATE TABLE `contract_payments` (
  `id` int(11) NOT NULL COMMENT 'Identificador único del pago',
  `contract_id` int(11) NOT NULL COMMENT 'ID del contrato asociado a este pago',
  `payment_reference` varchar(50) DEFAULT NULL COMMENT 'Número o referencia del pago',
  `euro_rate_id` int(11) DEFAULT NULL COMMENT 'ID de la tasa de cambio del euro aplicada',
  `payment_date` date NOT NULL COMMENT 'Fecha en que se realizó el pago',
  `amount` decimal(12,2) NOT NULL COMMENT 'Monto del pago',
  `status` enum('pending','paid','cancelled','refunded') DEFAULT 'pending' COMMENT 'Estado actual del pago'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro de pagos asociados a los contratos del mercado';

--
-- Volcado de datos para la tabla `contract_payments`
--

INSERT INTO `contract_payments` (`id`, `contract_id`, `payment_reference`, `euro_rate_id`, `payment_date`, `amount`, `status`) VALUES
(28, 2, 'PAY-2-001', 1, '2025-08-04', 1167.04, 'paid'),
(29, 2, 'PAY-2-002', NULL, '2025-09-04', 0.00, 'paid'),
(30, 2, 'PAY-2-003', NULL, '2025-10-04', 0.00, 'pending'),
(31, 2, 'PAY-2-004', NULL, '2025-11-04', 0.00, 'pending'),
(32, 2, 'PAY-2-005', NULL, '2025-12-04', 0.00, 'pending'),
(33, 2, 'PAY-2-006', NULL, '2026-01-04', 0.00, 'pending'),
(34, 2, 'PAY-2-007', NULL, '2026-02-04', 0.00, 'pending'),
(35, 2, 'PAY-2-008', NULL, '2026-03-04', 0.00, 'pending'),
(36, 2, 'PAY-2-009', NULL, '2026-04-04', 0.00, 'pending'),
(37, 11, 'PAY-2026-02-000011', NULL, '2026-02-24', 0.00, 'pending'),
(38, 11, 'PAY-2026-03-000011', NULL, '2026-03-15', 0.00, 'pending'),
(39, 11, 'PAY-2026-04-000011', NULL, '2026-04-15', 0.00, 'pending'),
(40, 11, 'PAY-2026-05-000011', NULL, '2026-05-15', 0.00, 'pending'),
(41, 11, 'PAY-2026-06-000011', NULL, '2026-06-15', 0.00, 'pending'),
(42, 11, 'PAY-2026-07-000011', NULL, '2026-07-15', 0.00, 'pending'),
(43, 11, 'PAY-2026-08-000011', NULL, '2026-08-15', 0.00, 'pending'),
(44, 11, 'PAY-2026-09-000011', NULL, '2026-09-15', 0.00, 'pending'),
(45, 11, 'PAY-2026-10-000011', NULL, '2026-10-15', 0.00, 'pending'),
(46, 11, 'PAY-2026-11-000011', NULL, '2026-11-15', 0.00, 'pending'),
(47, 11, 'PAY-2026-12-000011', NULL, '2026-12-15', 0.00, 'pending'),
(48, 12, 'PAY-2026-02-000012', NULL, '2026-02-24', 0.00, 'pending'),
(49, 12, 'PAY-2026-03-000012', NULL, '2026-03-15', 0.00, 'pending'),
(50, 12, 'PAY-2026-04-000012', NULL, '2026-04-15', 0.00, 'pending'),
(51, 12, 'PAY-2026-05-000012', NULL, '2026-05-15', 0.00, 'pending'),
(52, 12, 'PAY-2026-06-000012', NULL, '2026-06-15', 0.00, 'pending'),
(53, 12, 'PAY-2026-07-000012', NULL, '2026-07-15', 0.00, 'pending'),
(54, 12, 'PAY-2026-08-000012', NULL, '2026-08-15', 0.00, 'pending'),
(55, 12, 'PAY-2026-09-000012', NULL, '2026-09-15', 0.00, 'pending'),
(56, 12, 'PAY-2026-10-000012', NULL, '2026-10-15', 0.00, 'pending'),
(57, 12, 'PAY-2026-11-000012', NULL, '2026-11-15', 0.00, 'pending'),
(58, 12, 'PAY-2026-12-000012', NULL, '2026-12-15', 0.00, 'pending');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contract_payment_installments`
--

CREATE TABLE `contract_payment_installments` (
  `id` int(11) NOT NULL COMMENT 'Identificador único del abono',
  `contract_payment_id` int(11) NOT NULL COMMENT 'ID del pago de contrato al que pertenece este abono',
  `payment_method_id` int(11) NOT NULL COMMENT 'Método de pago utilizado',
  `date` date NOT NULL COMMENT 'Fecha en que se realizó el abono',
  `amount` decimal(12,2) NOT NULL COMMENT 'Monto del abono',
  `concept` text DEFAULT NULL COMMENT 'Concepto o descripción del abono',
  `daily_cash_registers_id` int(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro de abonos o pagos parciales realizados para un pago de contrato';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `shift_type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `manager_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Almacena los departamentos de la organización';

--
-- Volcado de datos para la tabla `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `shift_type`, `created_at`, `manager_id`) VALUES
(1, 'Liquidacion', 'Departamento de liquidación de nóminas', 'Day', '2025-08-02 21:28:46', 8),
(2, 'Cobranza', 'Departamento de gestión de cobros', 'Vespertino', '2025-08-02 21:28:46', 2),
(3, 'Fiscalizacion', 'Departamento de control fiscal', 'Mixto', '2025-08-02 21:28:46', 3),
(4, 'Recursos Humanos', 'Gestión del personal y talento humano', 'Administrativo', '2025-08-02 21:28:46', 4),
(5, 'Mixto', 'Con Varias cosas', 'Mixed', '2026-02-28 23:36:01', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `department_schedules`
--

CREATE TABLE `department_schedules` (
  `id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `day` varchar(15) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Define los horarios de trabajo por departamento';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `divisions`
--

CREATE TABLE `divisions` (
  `id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Contiene las divisiones que pertenecen a cada departamento';

--
-- Volcado de datos para la tabla `divisions`
--

INSERT INTO `divisions` (`id`, `department_id`, `name`, `description`) VALUES
(1, 1, 'Liquidación de Nóminas', 'División encargada del cálculo de salarios'),
(2, 1, 'Prestaciones Sociales', 'División de beneficios para empleados'),
(3, 2, 'Cobranza Interna', 'Gestión de cobros a empleados'),
(4, 2, 'Cobranza Externa', 'Gestión de cobros a clientes'),
(5, 3, 'Auditoría', 'División de revisiones fiscales'),
(6, 4, 'Reclutamiento', 'Selección de personal');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `economic_indicators`
--

CREATE TABLE `economic_indicators` (
  `indicator_id` int(11) NOT NULL,
  `ut_value` decimal(18,6) NOT NULL COMMENT 'Valor de la Unidad Tributaria (UT)',
  `euro_bcv_rate` decimal(18,6) NOT NULL COMMENT 'Tasa del Euro según BCV (Moneda de Mayor Valor - Art. 105)',
  `effective_date` date NOT NULL COMMENT 'Fecha desde la que son vigentes estos valores',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `economic_indicators`
--

INSERT INTO `economic_indicators` (`indicator_id`, `ut_value`, `euro_bcv_rate`, `effective_date`, `created_at`) VALUES
(1, 40.000000, 220.000000, '2025-10-28', '2025-10-28 05:30:04'),
(2, 40.000000, 350.000000, '2026-01-19', '2026-01-19 16:56:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `euro_rates`
--

CREATE TABLE `euro_rates` (
  `id` int(11) NOT NULL,
  `bs_value` decimal(10,2) NOT NULL COMMENT 'Valor en bolívares',
  `month` varchar(20) DEFAULT NULL COMMENT 'Mes de la tasa',
  `year` varchar(4) DEFAULT NULL COMMENT 'Año de la tasa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro histórico de tasas de cambio del euro';

--
-- Volcado de datos para la tabla `euro_rates`
--

INSERT INTO `euro_rates` (`id`, `bs_value`, `month`, `year`) VALUES
(1, 145.88, 'agosto', '2025'),
(3, 150.25, 'enero', '2025'),
(4, 152.10, 'febrero', '2025'),
(5, 148.90, 'marzo', '2025'),
(6, 147.65, 'abril', '2025'),
(7, 350.00, 'enero', '2026');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `external_business_categories`
--

CREATE TABLE `external_business_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Nombre de la categoría externa',
  `installation_type` varchar(100) DEFAULT NULL COMMENT 'Tipo de instalación requerida',
  `payment_count` decimal(10,2) DEFAULT NULL COMMENT 'Número de cobros requeridos'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Categorías de negocios externos al mercado';

--
-- Volcado de datos para la tabla `external_business_categories`
--

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fee_payments`
--

CREATE TABLE `fee_payments` (
  `payment_id` int(11) NOT NULL,
  `contract_id` int(11) NOT NULL,
  `period_month` date NOT NULL,
  `payment_date` timestamp NULL DEFAULT current_timestamp(),
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_type` varchar(50) NOT NULL,
  `payment_status` varchar(50) NOT NULL DEFAULT 'Paid',
  `transaction_reference` varchar(100) DEFAULT NULL,
  `daily_cash_register_id` int(11) DEFAULT NULL,
  `payment_method_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `fee_payments`
--

INSERT INTO `fee_payments` (`payment_id`, `contract_id`, `period_month`, `payment_date`, `amount_paid`, `payment_type`, `payment_status`, `transaction_reference`, `daily_cash_register_id`, `payment_method_id`) VALUES
(1, 1, '0000-00-00', '2026-03-26 04:00:00', 477.50, 'penalty', 'completed', 'REF-12489', NULL, 2),
(2, 3, '0000-00-00', '2026-03-22 04:00:00', 483.50, 'penalty', 'pending', 'REF-73136', NULL, 1),
(3, 2, '0000-00-00', '2026-05-24 04:00:00', 431.50, 'penalty', 'completed', 'REF-71342', NULL, 3),
(4, 5, '0000-00-00', '2026-07-18 04:00:00', 155.50, 'penalty', 'completed', 'REF-25838', NULL, 2),
(5, 8, '0000-00-00', '2026-07-04 04:00:00', 490.50, 'penalty', 'completed', 'REF-74674', NULL, 4),
(6, 7, '0000-00-00', '2026-07-16 04:00:00', 471.50, 'penalty', 'completed', 'REF-55307', NULL, 2),
(7, 10, '0000-00-00', '2026-07-16 04:00:00', 152.50, 'fee', 'pending', 'REF-36926', NULL, 1),
(8, 5, '0000-00-00', '2026-06-12 04:00:00', 315.50, 'fee', 'completed', 'REF-71371', NULL, 1),
(9, 9, '0000-00-00', '2026-05-06 04:00:00', 374.50, 'fee', 'completed', 'REF-17447', NULL, 3),
(10, 3, '0000-00-00', '2026-02-17 04:00:00', 440.50, 'penalty', 'pending', 'REF-30502', NULL, 1),
(11, 4, '0000-00-00', '2026-06-11 04:00:00', 474.50, 'fee', 'completed', 'REF-35800', NULL, 2),
(12, 2, '0000-00-00', '2026-04-03 04:00:00', 392.50, 'fee', 'completed', 'REF-57665', NULL, 2),
(13, 5, '0000-00-00', '2026-01-19 04:00:00', 420.50, 'fee', 'completed', 'REF-10599', NULL, 4),
(14, 9, '0000-00-00', '2026-07-08 04:00:00', 430.50, 'fee', 'completed', 'REF-34568', NULL, 2),
(15, 5, '0000-00-00', '2026-04-15 04:00:00', 427.50, 'fee', 'pending', 'REF-58520', NULL, 3),
(16, 9, '0000-00-00', '2026-04-03 04:00:00', 163.50, 'fee', 'completed', 'REF-47524', NULL, 4),
(17, 3, '0000-00-00', '2026-02-14 04:00:00', 447.50, 'penalty', 'completed', 'REF-84465', NULL, 2),
(18, 7, '0000-00-00', '2026-02-26 04:00:00', 434.50, 'penalty', 'completed', 'REF-96431', NULL, 1),
(19, 4, '0000-00-00', '2026-04-26 04:00:00', 235.50, 'fee', 'completed', 'REF-44825', NULL, 1),
(20, 9, '0000-00-00', '2026-07-15 04:00:00', 367.50, 'penalty', 'pending', 'REF-51369', NULL, 2),
(21, 1, '0000-00-00', '2026-01-01 04:00:00', 132.50, 'fee', 'completed', 'REF-21262', NULL, 3),
(22, 6, '0000-00-00', '2026-03-22 04:00:00', 345.50, 'fee', 'completed', 'REF-72615', NULL, 1),
(23, 5, '0000-00-00', '2026-05-16 04:00:00', 265.50, 'fee', 'completed', 'REF-63754', NULL, 3),
(24, 2, '0000-00-00', '2026-03-09 04:00:00', 126.50, 'fee', 'completed', 'REF-84465', NULL, 2),
(25, 7, '0000-00-00', '2026-03-15 04:00:00', 461.50, 'fee', 'completed', 'REF-83274', NULL, 1),
(26, 9, '0000-00-00', '2026-02-14 04:00:00', 137.50, 'fee', 'completed', 'REF-52587', NULL, 1),
(27, 4, '0000-00-00', '2026-02-18 04:00:00', 342.50, 'fee', 'completed', 'REF-48183', NULL, 4),
(28, 6, '0000-00-00', '2026-05-28 04:00:00', 465.50, 'fee', 'completed', 'REF-13341', NULL, 1),
(29, 5, '0000-00-00', '2026-04-14 04:00:00', 304.50, 'fee', 'pending', 'REF-10689', NULL, 4),
(30, 2, '0000-00-00', '2026-05-21 04:00:00', 377.50, 'penalty', 'completed', 'REF-68816', NULL, 1),
(31, 7, '0000-00-00', '2026-01-20 04:00:00', 211.50, 'fee', 'completed', 'REF-83402', NULL, 4),
(32, 7, '0000-00-00', '2026-01-04 04:00:00', 104.50, 'fee', 'completed', 'REF-93454', NULL, 3),
(33, 1, '0000-00-00', '2026-06-15 04:00:00', 464.50, 'penalty', 'completed', 'REF-40439', NULL, 2),
(34, 7, '0000-00-00', '2026-04-24 04:00:00', 183.50, 'penalty', 'completed', 'REF-15058', NULL, 2),
(35, 10, '0000-00-00', '2026-03-28 04:00:00', 131.50, 'fee', 'completed', 'REF-91791', NULL, 4),
(36, 10, '0000-00-00', '2026-04-26 04:00:00', 494.50, 'fee', 'completed', 'REF-54194', NULL, 2),
(37, 1, '0000-00-00', '2026-02-09 04:00:00', 307.50, 'fee', 'completed', 'REF-15865', NULL, 3),
(38, 10, '0000-00-00', '2026-03-24 04:00:00', 403.50, 'penalty', 'pending', 'REF-65253', NULL, 3),
(39, 10, '0000-00-00', '2026-05-17 04:00:00', 355.50, 'fee', 'completed', 'REF-46365', NULL, 4),
(40, 8, '0000-00-00', '2026-08-22 04:00:00', 430.50, 'fee', 'completed', 'REF-92389', NULL, 1),
(41, 6, '0000-00-00', '2026-02-27 04:00:00', 498.50, 'fee', 'completed', 'REF-34751', NULL, 1),
(42, 4, '0000-00-00', '2026-01-16 04:00:00', 242.50, 'fee', 'completed', 'REF-10790', NULL, 3),
(43, 6, '0000-00-00', '2026-03-17 04:00:00', 422.50, 'fee', 'completed', 'REF-47060', NULL, 1),
(44, 7, '0000-00-00', '2026-07-22 04:00:00', 460.50, 'fee', 'completed', 'REF-62225', NULL, 1),
(45, 7, '0000-00-00', '2026-08-14 04:00:00', 207.50, 'fee', 'completed', 'REF-23193', NULL, 1),
(46, 2, '0000-00-00', '2026-01-26 04:00:00', 312.50, 'fee', 'completed', 'REF-43474', NULL, 4),
(47, 3, '0000-00-00', '2026-05-03 04:00:00', 110.50, 'penalty', 'completed', 'REF-66702', NULL, 4),
(48, 4, '0000-00-00', '2026-07-11 04:00:00', 183.50, 'penalty', 'completed', 'REF-19802', NULL, 3),
(49, 8, '0000-00-00', '2026-02-21 04:00:00', 399.50, 'fee', 'completed', 'REF-49131', NULL, 3),
(50, 6, '0000-00-00', '2026-03-09 04:00:00', 112.50, 'fee', 'completed', 'REF-22635', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fine_payments`
--

CREATE TABLE `fine_payments` (
  `payment_id` int(11) NOT NULL,
  `sanction_id` int(11) NOT NULL,
  `payment_date` timestamp NULL DEFAULT current_timestamp(),
  `amount_paid` decimal(10,2) NOT NULL,
  `transaction_reference` varchar(100) DEFAULT NULL,
  `payment_type` varchar(100) NOT NULL,
  `payment_status` varchar(50) NOT NULL DEFAULT 'Paid',
  `daily_cash_register_id` int(11) DEFAULT NULL,
  `payment_method_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `fine_payments`
--

INSERT INTO `fine_payments` (`payment_id`, `sanction_id`, `payment_date`, `amount_paid`, `transaction_reference`, `payment_type`, `payment_status`, `daily_cash_register_id`, `payment_method_id`) VALUES
(1, 5, '2026-01-19 01:56:37', 400.00, '45446', 'Biopago', 'Paid', 1, 5),
(2, 4, '2026-01-19 02:22:59', 400.00, '2344', 'Dólares', 'Paid', 1, 6),
(3, 13, '2026-01-21 09:42:00', 400.00, NULL, 'Efectivo', 'Paid', 2, 1),
(4, 13, '2026-01-21 09:42:11', 400.00, '45444', 'Dólares', 'Paid', 2, 6),
(5, 13, '2026-01-21 09:42:15', 400.00, '45455454', 'Dólares', 'Paid', 2, 6),
(6, 13, '2026-01-21 09:42:26', 400.00, '58656', 'Biopago', 'Paid', 2, 5),
(7, 9, '2026-01-21 09:54:46', 20000.00, '44545', 'Pago Móvil', 'Paid', 2, 3),
(8, 16, '2026-02-09 10:05:45', 20000.00, '59898', 'Dólares', 'Paid', 2, 6),
(9, 16, '2026-02-09 10:06:51', 20000.00, '4546544454', 'Dólares', 'Paid', 2, 6),
(10, 14, '2026-02-09 10:12:12', 20000.00, '1111', 'Biopago', 'Paid', 2, 5),
(11, 18, '2026-03-04 11:31:14', 400.00, '123546', 'Transferencia', 'Paid', 2, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fiscalization_roles`
--

CREATE TABLE `fiscalization_roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL COMMENT 'administrador, oficina, inspector',
  `description` varchar(255) DEFAULT NULL,
  `permissions_mask` varchar(10) NOT NULL COMMENT 'Cadena de permisos rwx (ej: rwx-r--)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `fiscalization_roles`
--

INSERT INTO `fiscalization_roles` (`role_id`, `role_name`, `description`, `permissions_mask`) VALUES
(1, 'administrador', 'Acceso total y gestión de usuarios.', 'rwxrwxrwx'),
(2, 'oficina', 'Gestión de reportes, sin poder de modificación de tasas/config.', 'rw-r--rw-'),
(3, 'inspector', 'Solo reportar infracciones y ver sus propios casos.', 'rw-r-----'),
(504, 'Cobranzas', 'Gestión de cobranzas y pagos.', 'rwx------');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fiscalization_user_level`
--

CREATE TABLE `fiscalization_user_level` (
  `user_level_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `assigned_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `fiscalization_user_level`
--

INSERT INTO `fiscalization_user_level` (`user_level_id`, `user_id`, `role_id`, `assigned_at`) VALUES
(1, 8, 1, '2025-10-28 03:37:26'),
(2, 3, 3, '2026-01-21 04:14:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fiscal_year`
--

CREATE TABLE `fiscal_year` (
  `id` int(11) NOT NULL,
  `start_date` date DEFAULT NULL COMMENT 'Mes de inicio del año fiscal',
  `end_date` date DEFAULT NULL COMMENT 'Año de inicio del año fiscal',
  `year` varchar(4) DEFAULT NULL COMMENT 'Año de finalización del año fiscal',
  `status` enum('active','inactive') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Fecha de creación del registro'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Almacena la información de los años fiscales';

--
-- Volcado de datos para la tabla `fiscal_year`
--

INSERT INTO `fiscal_year` (`id`, `start_date`, `end_date`, `year`, `status`, `created_at`) VALUES
(1, '2026-01-01', '2026-12-31', '2026', 'active', '2026-02-22 04:00:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `infractions`
--

CREATE TABLE `infractions` (
  `infraction_id` int(11) NOT NULL,
  `awardee_id` int(11) NOT NULL,
  `stall_id` int(11) DEFAULT NULL,
  `infraction_datetime` datetime NOT NULL DEFAULT current_timestamp(),
  `infraction_type_id` int(11) NOT NULL,
  `infraction_description` text NOT NULL,
  `infraction_status` varchar(50) NOT NULL DEFAULT 'Reported',
  `inspector_observations` text DEFAULT NULL,
  `proof` varchar(200) DEFAULT NULL,
  `status_logical` varchar(50) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `infractions`
--

INSERT INTO `infractions` (`infraction_id`, `awardee_id`, `stall_id`, `infraction_datetime`, `infraction_type_id`, `infraction_description`, `infraction_status`, `inspector_observations`, `proof`, `status_logical`) VALUES
(4, 2, 1, '2025-09-18 20:19:43', 1, 'sssssssss fgfgfgf ffgfg', 'Resolved', 'sssssssssss', '68cc695fbe614.jpg', 'deleted'),
(5, 2, 1, '2025-10-29 00:11:19', 25, 'TestTestestestse', 'Reported', 'Test', '', 'deleted'),
(6, 1, 1, '2025-10-29 02:48:44', 22, 'Esto es una descripcion de mas de 10 caracteres', 'Reported', 'No se', '', 'deleted'),
(7, 2, 1, '2025-12-07 18:51:17', 3, 'Esta es una prueba', 'Reported', '', '', 'deleted'),
(8, 2, 1, '2025-12-07 20:49:37', 19, 'aasdasasas', 'Reported', '', '', 'deleted'),
(9, 2, 1, '2025-12-07 21:14:16', 3, 'asdasdasdasda', 'Resolved', '', '', 'deleted'),
(10, 2, 1, '2026-01-19 16:26:43', 7, 'TESTasdasdsd', 'Cancelled', '', '', 'deleted'),
(11, 2, 1, '2026-01-19 16:38:03', 28, 'asdasaaassdfsdf', 'Resolved', 'asdasdasdsad', '', 'deleted'),
(12, 2, 1, '2026-01-21 05:05:43', 7, 'aasdasddasd', 'Reported', 'asdasdasdas', '', 'deleted'),
(13, 2, 1, '2026-01-21 05:38:40', 19, 'asdasdasdasd', 'Resolved', 'asdasdsadasd', '', 'deleted'),
(14, 2, 1, '2026-01-21 07:05:13', 3, 'saasdasdas', 'Resolved', 'asdasdasdasd', '', 'deleted'),
(15, 2, 1, '2026-01-21 07:06:04', 3, 'asdasdasdsad', 'Reported', 'asdasdasda', '', 'deleted'),
(16, 2, 1, '2026-01-21 07:06:20', 3, 'asdasdasdas', 'Resolved', 'asdasdasdasd', '', 'deleted'),
(17, 3, 1, '2026-03-04 04:45:26', 24, 'intento acaparar', 'Resolved', '', '', 'active'),
(18, 2, 5, '2026-03-04 04:57:38', 19, 'agresion fisica', 'Resolved', 'le pego a una señora', '', 'active'),
(19, 1, 2, '2026-03-04 05:03:46', 30, 'intento quemar un local', 'Reported', 'mato a alguien', '', 'active'),
(20, 6, 9, '2026-03-04 05:04:50', 12, 'se peleo con alguien', 'Reported', 'se peleo con alguien', '', 'active'),
(21, 7, 7, '2026-03-04 05:10:21', 8, 'se le olvido cerrar', 'Reported', 'se le olvido cerrar', '', 'active'),
(22, 1, 2, '2026-03-04 07:12:37', 19, 'Juansito el pasaito', 'Reported', '', '', 'active');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `infraction_types`
--

CREATE TABLE `infraction_types` (
  `infraction_type_id` int(11) NOT NULL,
  `infraction_type_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `violated_article` varchar(50) DEFAULT NULL,
  `sanction_type_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `infraction_types`
--

INSERT INTO `infraction_types` (`infraction_type_id`, `infraction_type_name`, `description`, `violated_article`, `sanction_type_id`) VALUES
(1, 'Falta de Documentos', 'No tiene el Rif y otros documentos', '175', 0),
(2, 'Falta de limpieza', 'No efectuar la limpieza del área asignada al expendedor.', '107.1', 1),
(3, 'Abandono de mercancía', 'Abandonar envases y mercancías en recintos comunes, incumpliendo normas o directrices..', '107.2', 1),
(4, 'Documentación no visible', 'No mantener debidamente plastificado y visible los documentos de identificación, permisos, certificados u otros obligatorios.', '107.3', 1),
(5, 'Uso de indumentaria no autorizada', 'No utilizar la indumentaria establecida.', '107.4', 1),
(6, 'Falta de protección de productos', 'No encontrarse provistos de los dispositivos necesarios para proteger los productos de alteración o contaminación.', '107.5', 1),
(7, 'Almacenamiento indebido de alimentos', 'Hacinar o depositar en el suelo los productos destinados a la alimentación.', '107.6', 1),
(8, 'Cierre temporal injustificado (Leve)', 'Cerrar el puesto o local por un período entre tres (3) y ocho (8) días en el período de un año, salvo causa justificada.', '107.7', 1),
(9, 'Ofensa a la autoridad o público', 'Ofender de palabra a las autoridades, expendedores, empleados, o al público usuario de los mercados.', '107.8', 1),
(10, 'Permanencia fuera de horario', 'Permanecer en las instalaciones de los mercados fuera del horario establecido.', '107.9', 1),
(11, 'Fumar o beber licores en instalaciones', 'Fumar y beber licores en las instalaciones del mercado.', '107.10', 1),
(12, 'Altercado o escándalo', 'Los altercados que produzcan escándalos dentro del mercado o de sus inmediaciones, hayan o no, lesiones o daños.', '107.11', 1),
(13, 'Incumplimiento sanitario', 'Incumplir la normativa en vigor en materia sanitaria.', '108.1', 2),
(14, 'Venta de productos descompuestos', 'Vender alimentos y productos en estado de descomposición, deterioro u obsolescencia.', '108.2', 2),
(15, 'Venta de artículos no autorizados', 'Vender artículos o mercancías de especie distinta a la autorizada.', '108.3', 2),
(16, 'Venta en área no asignada', 'Colocar vendedores o agentes en áreas diferentes a la asignada.', '108.4', 2),
(17, 'Incumplimiento de horarios operativos', 'Incumplir con los horarios establecidos para las actividades de carga, descarga, almacenaje, apertura, cierre y limpieza.', '108.5', 2),
(18, 'Negativa a exhibir documentación', 'Negarse a exhibir cualquier documentación relacionada con el expendedor o el negocio cuando sea exigida por los fiscales.', '108.6', 2),
(19, 'Agresión física', 'Agredir físicamente a las autoridades municipales, a los demás expendedores, a los empleados o al público usuario.', '108.7', 2),
(20, 'Daños por negligencia', 'Causar daños en las instalaciones del mercado por negligencia, imprudencia o impericia, sin perjuicio de la reparación del daño.', '108.8', 2),
(21, 'Uso de altavoces/sonido excesivo', 'Utilizar altavoces o similares para ofrecer mercancías, así como equipos de sonido con niveles que afecten la tranquilidad.', '108.9', 2),
(22, 'Modificación estructural no autorizada', 'Realizar, sin autorización escrita, cualquier modificación estructural en el área asignada, aun cuando se trate de mejoras.', '108.10', 2),
(23, 'Uso indebido de bienes comunes', 'Usar indebidamente o sin autorización bienes o servicios comunes.', '108.11', 2),
(24, 'Provisión para venta ambulante', 'Proporcionar mercancía para la venta ambulante en instalaciones del mercado.', '108.12', 2),
(25, 'Cierre temporal injustificado (Moderada)', 'Cerrar el puesto o local por un período entre nueve (9) y quince (15) días, salvo causa justificada, en el período de un año.', '108.13', 2),
(26, 'Atención por tercero sin autorización', 'Atención del puesto o local por un tercero, sin la previa información y autorización del órgano de administración.', '108.14', 2),
(27, 'Obstrucción del tránsito', 'Transportar y colocar artículos u objetos en puertas, mostradores, pisos y/o aceras que impidan o interfieran el libre tránsito.', '108.15', 2),
(28, 'Consumo de sustancias prohibidas', 'Consumir bebidas alcohólicas o sustancias estupefacientes en las instalaciones del mercado.', '108.16', 2),
(29, 'Pesas/Medidas no reglamentarias', 'No utilizar pesas y medidas debidamente constatadas y mantenerlas visibles al público.', '108.17', 2),
(30, 'Provocación de disturbios graves', 'Provocar disturbios en el funcionamiento normal de los servicios, impidiendo ventas o enfrentando gravemente a usuarios/personal.', '109.1', 3),
(31, 'Incumplimiento de pago de tasa', 'Incumplimiento del pago de la tasa durante un periodo de un año y más.', '109.2', 3),
(32, 'Incumplimiento de sanción moderada', 'Incumplir la sanción como consecuencia de alguna infracción moderada.', '109.3', 3),
(33, 'Daños dolosos a instalaciones', 'Ocasionar dolosamente daños a las instalaciones del mercado, sin perjuicio de la obligación de reparar el daño.', '109.4', 3),
(34, 'Cierre temporal injustificado (Grave)', 'Cerrar el puesto o local durante un período de más de treinta (30) días en el periodo de un año, salvo causa justificada.', '109.5', 3),
(35, 'Traspaso o cesión unilateral', 'Traspasar, ceder o transferir total o parcialmente, el local o puesto que le haya sido adjudicado o arrendado de manera unilateral.', '109.6', 3),
(36, 'Expender bebidas alcohólicas o prohibidas', 'Expender bebidas alcohólicas o cualquier sustancia prohibida.', '109.7', 3),
(37, 'Test infraction', 'TEST infraction', '666', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inspection_reports`
--

CREATE TABLE `inspection_reports` (
  `report_id` int(11) NOT NULL,
  `scheduled_inspection_id` int(11) DEFAULT NULL,
  `main_inspector_id` int(11) NOT NULL,
  `assistant_inspector_id` int(11) DEFAULT NULL,
  `stall_id` int(11) NOT NULL,
  `awardee_id` int(11) NOT NULL,
  `creation_date` timestamp NULL DEFAULT current_timestamp(),
  `general_observations` text DEFAULT NULL,
  `inspector_signature_url` text DEFAULT NULL,
  `assistant_signature_url` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inspection_reports`
--

INSERT INTO `inspection_reports` (`report_id`, `scheduled_inspection_id`, `main_inspector_id`, `assistant_inspector_id`, `stall_id`, `awardee_id`, `creation_date`, `general_observations`, `inspector_signature_url`, `assistant_signature_url`) VALUES
(13, 12, 5, 0, 1, 3, '2026-03-04 03:13:39', 'Esto es un test 2', '', ''),
(14, 13, 1, 5, 7, 7, '2026-03-04 03:26:31', 'Reporte en progreso', '', ''),
(15, 14, 1, 1, 3, 3, '2026-03-04 03:34:20', 'observacion 3', '', ''),
(16, 15, 1, 0, 2, 1, '2026-03-04 03:36:18', 'Juan perez', '', ''),
(17, 16, 5, 0, 9, 6, '2026-03-04 03:36:55', 'sitio quemado', '', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inspection_updates`
--

CREATE TABLE `inspection_updates` (
  `update_id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `status_old` varchar(50) NOT NULL,
  `status_new` varchar(50) NOT NULL,
  `update_description` text DEFAULT NULL,
  `updated_by_user_id` int(11) DEFAULT NULL,
  `update_date` datetime DEFAULT current_timestamp(),
  `inspection_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inspection_updates`
--

INSERT INTO `inspection_updates` (`update_id`, `report_id`, `status_old`, `status_new`, `update_description`, `updated_by_user_id`, `update_date`, `inspection_id`) VALUES
(11, 13, 'Pending', 'Pending', 'No hubo progreso', 8, '2026-03-03 23:17:50', 12),
(12, 13, 'Pending', 'In Progress', 'Visitamos y todo bien', 8, '2026-03-03 23:18:00', 12),
(13, 13, 'In Progress', 'Completed', 'TODO FINO', 8, '2026-03-03 23:18:10', 12),
(14, 17, 'In Progress', 'In Progress', 'PASO ALGO\n--- Resultado: TEST', 8, '2026-03-04 01:06:35', 16);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inspectors`
--

CREATE TABLE `inspectors` (
  `inspector_id` int(11) NOT NULL,
  `inspector_code` varchar(50) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inspectors`
--

INSERT INTO `inspectors` (`inspector_id`, `inspector_code`, `full_name`, `phone_number`, `email`, `hire_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'A-01', 'Daniel Alfonsi', '04264804748', 'alfonsi.acosta@gmail.com', '2025-09-16', 1, '2025-09-16 16:40:48', '2025-09-16 16:40:48'),
(2, 'B-01', 'Pedro Berti', '04163197919', 'pedro@gmail.com', '2025-09-15', 0, '2025-09-16 16:46:42', '2025-09-16 17:11:45'),
(3, 'C-01', 'Jose Figuera', '04264804748', 'jose@gmail.com', '2025-09-16', 1, '2025-09-16 16:53:12', '2025-09-16 16:53:12'),
(5, 'D-02', 'Amador Jose Figueras', '04128581', 'amador_01@gmail.com', '2025-09-13', 1, '2025-09-16 16:54:44', '2025-10-28 04:44:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `internal_business_categories`
--

CREATE TABLE `internal_business_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Nombre de la categoría',
  `payment_count` decimal(10,2) DEFAULT NULL COMMENT 'Número de cobros requeridos'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Categorías de negocios internos como puestos y mesones';

--
-- Volcado de datos para la tabla `internal_business_categories`
--

INSERT INTO `internal_business_categories` (`id`, `name`, `payment_count`) VALUES
(1, 'Comida Rápida', 1.00),
(2, 'Pescadería', 1.00),
(3, 'Carnicería', 1.00),
(4, 'Verdulería', 1.00),
(5, 'Charcutería', 1.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_positions`
--

CREATE TABLE `job_positions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Catálogo de puestos de trabajo en la organización';

--
-- Volcado de datos para la tabla `job_positions`
--

INSERT INTO `job_positions` (`id`, `name`) VALUES
(5, 'Desarrollador'),
(1, 'Director test'),
(2, 'Gerente'),
(3, 'Secretaria'),
(6, 'test');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `reason` text DEFAULT NULL,
  `document_url` varchar(255) DEFAULT NULL,
  `request_date` date NOT NULL,
  `approval_date` date DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Gestiona las solicitudes de permisos del personal';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `market_stalls`
--

CREATE TABLE `market_stalls` (
  `id` int(11) NOT NULL,
  `awardee_id` int(11) DEFAULT NULL,
  `sector_id` int(11) NOT NULL COMMENT 'Sector donde está ubicado',
  `stall_number` varchar(50) NOT NULL COMMENT 'Número o identificador del local',
  `status` enum('vacant','occupied','maintenance','closed') DEFAULT 'vacant',
  `location_description` varchar(255) DEFAULT NULL COMMENT 'Descripción detallada de la ubicación'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Puestos físicos o locales dentro del mercado';

--
-- Volcado de datos para la tabla `market_stalls`
--

INSERT INTO `market_stalls` (`id`, `awardee_id`, `sector_id`, `stall_number`, `status`, `location_description`) VALUES
(1, 8, 4, 'L-001', 'occupied', NULL),
(2, 1, 3, 'L-002', 'vacant', NULL),
(3, 3, 3, 'L-003', 'vacant', NULL),
(4, 8, 3, 'L-004', 'vacant', NULL),
(5, 2, 1, 'L-005', 'vacant', NULL),
(6, 2, 6, 'L-006', 'vacant', NULL),
(7, 7, 6, 'L-007', 'vacant', NULL),
(8, 6, 1, 'L-008', 'vacant', NULL),
(9, 6, 1, 'L-009', 'vacant', NULL),
(10, 3, 4, 'L-010', 'vacant', NULL),
(11, NULL, 2, 'L-011', 'vacant', NULL),
(12, NULL, 5, 'L-012', 'vacant', NULL),
(13, NULL, 1, 'L-013', 'vacant', NULL),
(14, NULL, 4, 'L-014', 'vacant', NULL),
(15, NULL, 2, 'L-015', 'vacant', NULL),
(16, NULL, 5, 'Test Local', 'vacant', 'Twe');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `market_stall_info`
--

CREATE TABLE `market_stall_info` (
  `id` int(11) NOT NULL,
  `stall_id` int(11) NOT NULL,
  `rif` varchar(20) DEFAULT NULL COMMENT 'Registro de Información Fiscal',
  `business_name` varchar(255) DEFAULT NULL COMMENT 'Razón Social (Nombre del Local)',
  `address` text DEFAULT NULL COMMENT 'Dirección del negocio',
  `sector_id` int(11) DEFAULT NULL,
  `external_business_category_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `market_stall_info`
--

INSERT INTO `market_stall_info` (`id`, `stall_id`, `rif`, `business_name`, `address`, `sector_id`, `external_business_category_id`, `created_at`, `updated_at`) VALUES
(1, 1, '123456789', 'Los Futbolitos', 'Calle Cantaura', 1, 20, '2025-12-07 19:45:27', '2025-12-07 19:45:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modules`
--

CREATE TABLE `modules` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `route` varchar(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `modules`
--

INSERT INTO `modules` (`id`, `name`, `slug`, `description`, `icon`, `route`, `parent_id`, `order`, `status`, `created_at`) VALUES
(1, 'Dashboard', 'dashboard', 'Panel principal', 'ri-home-5-line', '/home/index', NULL, 1, 'active', '2026-02-22 03:33:55'),
(2, 'Adjudicatarios', 'awardee', 'Gestión de adjudicatarios', 'ri-group-line', '/awardee/index', NULL, 2, 'active', '2026-02-22 03:33:55'),
(3, 'Contratos', 'contract', 'Gestión de contratos', 'ri-file-text-line', '/contract/index', NULL, 3, 'active', '2026-02-22 03:33:55'),
(4, 'Planificación', 'planning', 'Planificación de pagos', 'ri-calendar-check-line', '/planning/index', NULL, 4, 'active', '2026-02-22 03:33:55'),
(5, 'Planificación Anticipada', 'planning-anticipados', 'Planificación anticipada', NULL, '/planning/anticipados', 4, 1, 'active', '2026-02-22 03:33:55'),
(6, 'Planificación Simultánea', 'planning-simultaneos', 'Planificación simultánea', NULL, '/planning/simultaneos', 4, 2, 'active', '2026-02-22 03:33:55'),
(7, 'Cobros', 'cobro', 'Gestión de cobros', 'ri-money-dollar-circle-line', '/cobro/index', NULL, 5, 'active', '2026-02-22 03:33:55'),
(8, 'Caja Diaria', 'dailycash', 'Gestión de caja diaria', 'ri-lock-unlock-line', '/dailycash/index', NULL, 6, 'active', '2026-02-22 03:33:55'),
(9, 'Reportes', 'report', 'Reportes del sistema', 'ri-file-list-3-line', '/report/index', NULL, 7, 'active', '2026-02-22 03:33:55'),
(10, 'Año Fiscal', 'fiscalyear', 'Gestión de años fiscales', 'ri-calendar-line', '/fiscalyear/index', NULL, 8, 'active', '2026-02-22 03:33:55'),
(11, 'Tasas de Euro', 'fiscalyear-rates', 'Gestión de tasas de euro', NULL, '/fiscalyear/rates', 8, 1, 'active', '2026-02-22 03:33:55'),
(12, 'Catálogo', 'catalog', 'Catálogos del sistema', 'ri-list-check', NULL, NULL, 9, 'active', '2026-02-22 03:33:55'),
(13, 'Rubros Internos', 'internal-category', 'Gestión de rubros internos', NULL, '/internalcategory/index', 12, 1, 'active', '2026-02-22 03:33:55'),
(14, 'Rubros Externos', 'external-category', 'Gestión de rubros externos', NULL, '/externalcategory/index', 12, 2, 'active', '2026-02-22 03:33:55'),
(15, 'Zonas', 'zone', 'Gestión de zonas', NULL, '/zone/index', 12, 3, 'active', '2026-02-22 03:33:55'),
(16, 'Sectores', 'sector', 'Gestión de sectores', NULL, '/sector/index', 12, 4, 'active', '2026-02-22 03:33:55'),
(17, 'Locales', 'marketstall', 'Gestión de locales', NULL, '/marketstall/index', 12, 5, 'active', '2026-02-22 03:33:55'),
(18, 'Métodos de Pago', 'paymentmethod', 'Gestión de métodos de pago', NULL, '/paymentmethod/index', 12, 6, 'active', '2026-02-22 03:33:55'),
(19, 'Gestión de Cajas', 'cashregister', 'Gestión de cajas registradoras', 'ri-safe-line', '/cashregister/index', NULL, 10, 'active', '2026-02-22 03:33:55'),
(20, 'Historial de Actividades', 'activitylog', 'Registro de actividades del sistema', 'ri-history-line', '/activitylog/index', NULL, 11, 'active', '2026-02-22 03:33:55'),
(21, 'Usuarios', 'user', 'Gestión de usuarios', 'ri-user-settings-line', '/user/index', NULL, 12, 'active', '2026-02-22 03:33:55'),
(22, 'Roles', 'role', 'Gestión de roles y permisos', 'ri-shield-user-line', '/role/index', NULL, 13, 'active', '2026-02-22 03:33:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `notification_datetime` timestamp NULL DEFAULT current_timestamp(),
  `sender_user_id` int(11) DEFAULT NULL,
  `recipient_user_id` int(11) DEFAULT NULL,
  `target_role_id` int(11) DEFAULT NULL,
  `target_department_id` int(11) DEFAULT NULL,
  `is_global` tinyint(1) NOT NULL DEFAULT 0,
  `notification_type` varchar(100) NOT NULL,
  `notification_subject` varchar(255) NOT NULL,
  `notification_message` text NOT NULL,
  `read_status` tinyint(1) DEFAULT 0,
  `complaint_id` int(11) DEFAULT NULL,
  `alert_id` int(11) DEFAULT NULL,
  `infraction_id` int(11) DEFAULT NULL,
  `citation_id` int(11) DEFAULT NULL,
  `sanction_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notifications`
--

INSERT INTO `notifications` (`notification_id`, `notification_datetime`, `sender_user_id`, `recipient_user_id`, `target_role_id`, `target_department_id`, `is_global`, `notification_type`, `notification_subject`, `notification_message`, `read_status`, `complaint_id`, `alert_id`, `infraction_id`, `citation_id`, `sanction_id`) VALUES
(100, '2026-03-04 06:12:37', 8, 6, NULL, 2, 0, 'sanction_new', 'Nueva Sanción Aplicada', 'Se ha aplicado una nueva sanción #22. Proceder con gestión de cobro.', 1, NULL, NULL, NULL, NULL, 22),
(101, '2026-03-04 06:12:37', 8, 11, NULL, 2, 0, 'sanction_new', 'Nueva Sanción Aplicada', 'Se ha aplicado una nueva sanción #22. Proceder con gestión de cobro.', 0, NULL, NULL, NULL, NULL, 22),
(102, '2026-03-04 06:12:37', 8, 12, NULL, 2, 0, 'sanction_new', 'Nueva Sanción Aplicada', 'Se ha aplicado una nueva sanción #22. Proceder con gestión de cobro.', 0, NULL, NULL, NULL, NULL, 22),
(103, '2026-03-04 06:12:37', 8, 13, NULL, 2, 0, 'sanction_new', 'Nueva Sanción Aplicada', 'Se ha aplicado una nueva sanción #22. Proceder con gestión de cobro.', 0, NULL, NULL, NULL, NULL, 22),
(108, '2026-03-04 06:28:36', 13, 3, NULL, 3, 0, 'complaint_new', 'Nueva Queja Registrada', 'Se ha registrado una nueva queja #14. Por favor, revise los detalles y asigne un inspector si es necesario.', 0, 14, NULL, NULL, NULL, NULL),
(109, '2026-03-04 06:28:36', 13, 8, NULL, 3, 0, 'complaint_new', 'Nueva Queja Registrada', 'Se ha registrado una nueva queja #14. Por favor, revise los detalles y asigne un inspector si es necesario.', 1, 14, NULL, NULL, NULL, NULL),
(110, '2026-03-04 06:31:14', 6, 3, NULL, 3, 0, 'fine_payment_received', 'Pago de Multa Recibido', 'Se ha recibido el pago de la sanción #18. La infracción #18 ha sido resuelta.', 0, NULL, NULL, NULL, NULL, NULL),
(111, '2026-03-04 06:31:14', 6, 8, NULL, 3, 0, 'fine_payment_received', 'Pago de Multa Recibido', 'Se ha recibido el pago de la sanción #18. La infracción #18 ha sido resuelta.', 1, NULL, NULL, NULL, NULL, NULL),
(112, '2026-03-04 06:33:23', 13, 3, NULL, 3, 0, 'complaint_new', 'Nueva Queja Registrada', 'Se ha registrado una nueva queja #15. Por favor, revise los detalles y asigne un inspector si es necesario.', 0, 15, NULL, NULL, NULL, NULL),
(113, '2026-03-04 06:33:23', 13, 8, NULL, 3, 0, 'complaint_new', 'Nueva Queja Registrada', 'Se ha registrado una nueva queja #15. Por favor, revise los detalles y asigne un inspector si es necesario.', 0, 15, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordinance_articles`
--

CREATE TABLE `ordinance_articles` (
  `article_id` int(11) NOT NULL,
  `article_number` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `infraction_type_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL COMMENT 'Identificador único del método de pago',
  `name` varchar(50) NOT NULL COMMENT 'Nombre del método de pago (Efectivo, Transferencia, etc.)',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Indica si el método está activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Catálogo de métodos de pago aceptados';

--
-- Volcado de datos para la tabla `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `name`, `is_active`) VALUES
(1, 'Transferencia', 1),
(2, 'Efectivo Bs', 1),
(3, 'Efectivo USD', 1),
(4, 'Punto de Venta', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `can_read` tinyint(1) NOT NULL DEFAULT 0,
  `can_write` tinyint(1) NOT NULL DEFAULT 0,
  `can_modify` tinyint(1) NOT NULL DEFAULT 0,
  `can_delete` tinyint(1) NOT NULL DEFAULT 0,
  `menu_json` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `department_id`, `name`, `description`, `can_read`, `can_write`, `can_modify`, `can_delete`, `menu_json`, `created_at`, `updated_at`) VALUES
(1, 2, 'admin', 'Administrador del departamento', 1, 1, 1, 1, NULL, '2026-02-28 13:54:58', '2026-02-28 13:54:58'),
(2, 3, 'admin', 'Administrador del departamento', 1, 1, 1, 1, NULL, '2026-02-28 13:54:58', '2026-02-28 13:54:58'),
(3, 4, 'admin', 'Administrador del departamento', 1, 1, 1, 1, NULL, '2026-02-28 13:54:58', '2026-02-28 13:54:58'),
(4, 1, 'admin', 'Administrador del departamento', 1, 1, 1, 1, NULL, '2026-02-28 13:54:58', '2026-02-28 13:54:58'),
(6, 5, 'Auditoria', 'Gestión de Reportes', 1, 1, 0, 0, '[\"Quejas\",\"Quejas::Quejas (Registrar)\",\"Quejas::Historial de Quejas\",\"Reportes\",\"Reportes\",\"Reportes::Editor de Reportes\",\"Reportes::Reportes Estadisticos\",\"Reportes\",\"Reportes::Reportes de Cobranza\"]', '2026-02-28 23:36:51', '2026-03-01 00:04:25'),
(7, 5, 'taquilla', 'taquilla', 1, 1, 1, 0, '[\"Quejas\",\"Quejas::Quejas (Registrar)\",\"Quejas::Historial de Quejas\",\"Reportes Estad\\u00edsticos\",\"Gesti\\u00f3n de Cobros\",\"Gesti\\u00f3n de Cobros::Cuentas por Cobrar\",\"Gesti\\u00f3n de Cobros::Gesti\\u00f3n de Multas\",\"Gesti\\u00f3n de Cobros::Control de Morosidad\",\"Gesti\\u00f3n de Cobros::Pagos Recibidos\",\"Reportes de Cobranza\"]', '2026-03-04 05:22:03', '2026-03-04 05:28:08'),
(8, 5, 'POR DEFECTO', 'ROL POR DEFECTO', 1, 0, 0, 0, '[\"Quejas\",\"Quejas::Quejas (Registrar)\",\"Quejas::Historial de Quejas\"]', '2026-03-04 05:29:17', '2026-03-04 05:29:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `role_modules`
--

CREATE TABLE `role_modules` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `role_modules`
--

INSERT INTO `role_modules` (`id`, `role_id`, `module_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3),
(4, 1, 4),
(5, 1, 5),
(6, 1, 6),
(7, 1, 7),
(8, 1, 8),
(9, 1, 9),
(10, 1, 10),
(11, 1, 11),
(12, 1, 12),
(13, 1, 13),
(14, 1, 14),
(15, 1, 15),
(16, 1, 16),
(17, 1, 17),
(18, 1, 18),
(19, 1, 19),
(20, 1, 20),
(21, 1, 21),
(22, 1, 22),
(29, 2, 1),
(24, 2, 2),
(27, 2, 3),
(36, 2, 4),
(37, 2, 5),
(38, 2, 6),
(26, 2, 7),
(28, 2, 8),
(39, 2, 9),
(31, 2, 10),
(32, 2, 11),
(33, 2, 13),
(30, 2, 14),
(41, 2, 15),
(40, 2, 16),
(34, 2, 17),
(35, 2, 18),
(25, 2, 19),
(23, 2, 20),
(46, 3, 1),
(42, 3, 2),
(44, 3, 3),
(43, 3, 7),
(45, 3, 8),
(48, 3, 13),
(47, 3, 14),
(52, 3, 15),
(51, 3, 16),
(49, 3, 17),
(50, 3, 18),
(53, 5, 1),
(54, 5, 7),
(55, 5, 8),
(56, 5, 11);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sanctions`
--

CREATE TABLE `sanctions` (
  `sanction_id` int(11) NOT NULL,
  `infraction_id` int(11) NOT NULL,
  `sanction_type_id` int(11) NOT NULL,
  `fine_amount` decimal(10,2) DEFAULT NULL,
  `fine_currency` varchar(10) DEFAULT NULL,
  `imposition_date` timestamp NULL DEFAULT current_timestamp(),
  `effect_start_date` date DEFAULT NULL,
  `effect_end_date` date DEFAULT NULL,
  `sanction_status` varchar(50) NOT NULL DEFAULT 'Imposed',
  `sanction_observations` text DEFAULT NULL,
  `is_repeat_offense` tinyint(1) DEFAULT 0,
  `imposed_by_user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sanctions`
--

INSERT INTO `sanctions` (`sanction_id`, `infraction_id`, `sanction_type_id`, `fine_amount`, `fine_currency`, `imposition_date`, `effect_start_date`, `effect_end_date`, `sanction_status`, `sanction_observations`, `is_repeat_offense`, `imposed_by_user_id`) VALUES
(17, 17, 2, 400.00, 'VES', '2026-03-04 03:45:27', '2026-03-03', '0000-00-00', 'Waived', '', 0, 1),
(18, 18, 2, 400.00, 'VES', '2026-03-04 03:57:38', '2026-03-02', '0000-00-00', 'Paid', 'le pego a una señora', 0, 1),
(19, 19, 3, 400.00, 'VES', '2026-03-04 04:03:46', '2026-03-04', '0000-00-00', 'Imposed', 'mato a alguien', 0, 1),
(20, 20, 1, 400.00, 'VES', '2026-03-04 04:04:50', '2026-03-02', '0000-00-00', 'Imposed', 'se peleo con alguien', 0, 1),
(21, 21, 1, 400.00, 'VES', '2026-03-04 04:10:21', '2026-03-03', '0000-00-00', 'Imposed', 'se le olvido cerrar', 0, 1),
(22, 22, 2, 400.00, 'VES', '2026-03-04 06:12:37', '2026-03-03', '0000-00-00', 'Imposed', '', 0, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sanction_types`
--

CREATE TABLE `sanction_types` (
  `sanction_type_id` int(11) NOT NULL,
  `severity_name` enum('leve','moderada','grave') NOT NULL COMMENT 'Clasificación de la sanción: Leve, Moderada, o Grave.',
  `description` text DEFAULT NULL,
  `bcv_multiplier` decimal(10,2) NOT NULL COMMENT 'Multiplicador de la Moneda de Mayor Valor publicada en el BCV (Art. 105).',
  `min_fine_ut_multiplier` decimal(10,2) DEFAULT NULL COMMENT 'Multiplicador mínimo de la UT para la multa aplicable (Art. 106).',
  `max_fine_ut_multiplier` decimal(10,2) DEFAULT NULL COMMENT 'Multiplicador máximo de la UT para la multa aplicable (Art. 106).',
  `min_suspension_days` int(11) DEFAULT NULL COMMENT 'Número mínimo de días para la suspensión temporal (Art. 106).',
  `max_suspension_days` int(11) DEFAULT NULL COMMENT 'Número máximo de días para la suspensión temporal (Art. 106).',
  `can_lead_to_rescission` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 si esta gravedad puede resultar en la rescisión de la concesión (Art. 106.4, 113), 0 si no.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sanction_types`
--

INSERT INTO `sanction_types` (`sanction_type_id`, `severity_name`, `description`, `bcv_multiplier`, `min_fine_ut_multiplier`, `max_fine_ut_multiplier`, `min_suspension_days`, `max_suspension_days`, `can_lead_to_rescission`) VALUES
(1, 'leve', 'Amonestación escrita. La repetición de tres (3) leves en 12 meses se convierte en Moderada.', 8.30, NULL, NULL, NULL, NULL, 0),
(2, 'moderada', 'Multa de 30 a 100 UT o suspensión temporal de 7 a 15 días (Art. 106.2). La reincidencia de tres moderadas en 12 meses se considera falta grave (Art. 106.3).', 13.90, 30.00, 100.00, 7, 15, 0),
(3, 'grave', 'Suspensión temporal de 15 a 60 días, o rescisión de la concesión (Art. 106.4). Si hay suspensión, se acompaña de multa de 100 a 500 UT.', 27.89, 100.00, 500.00, 15, 60, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `scheduled_inspections`
--

CREATE TABLE `scheduled_inspections` (
  `inspection_id` int(11) NOT NULL,
  `scheduled_date` date NOT NULL,
  `inspection_type` varchar(100) NOT NULL,
  `assigned_responsible_id` int(11) NOT NULL,
  `inspection_status` varchar(50) NOT NULL DEFAULT 'Pending',
  `observations` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `scheduled_inspections`
--

INSERT INTO `scheduled_inspections` (`inspection_id`, `scheduled_date`, `inspection_type`, `assigned_responsible_id`, `inspection_status`, `observations`, `created_at`) VALUES
(1, '2025-09-19', 'Rutina', 8, 'Pending', 'dsdsds', '2025-09-17 01:32:48'),
(2, '2025-09-25', 'Rutina', 3, 'Pending', 'aaaaaaaaaaaaaaaaa', '2025-09-17 11:43:12'),
(3, '2025-09-25', 'Queja', 9, 'In Progress', 'wwwwwwwwwwwww', '2025-09-17 12:50:32'),
(4, '2025-09-20', 'Queja', 11, 'Pending', 'jhgjhg', '2025-09-19 13:07:49'),
(5, '2025-10-14', 'Rutine', 11, 'In Progress', 'asdasd', '2025-10-26 22:38:50'),
(6, '2025-10-14', 'Rutine', 8, 'Completed', 'Test', '2025-10-26 22:55:23'),
(7, '2025-10-17', 'New Stall', 8, 'Pending', 'asdasd', '2025-10-27 05:30:20'),
(8, '0000-00-00', 'Rutine', 8, 'Completed', '', '2025-12-06 15:55:30'),
(9, '2026-01-21', 'Complain', 8, 'Cancelled', 'asdasd', '2026-01-19 05:00:43'),
(10, '2026-01-05', 'Rutine', 8, 'Pending', 'asdasdasdasd', '2026-01-21 04:36:43'),
(11, '2026-01-21', 'Complain', 8, 'Pending', 'asasdasdas', '2026-01-21 06:03:29'),
(12, '2026-03-04', 'Rutine', 8, 'Completed', 'TEST', '2026-03-04 03:13:39'),
(13, '2026-03-05', 'New Stall', 8, 'Pending', 'En progreso', '2026-03-04 03:26:31'),
(14, '2026-03-04', 'Rutine', 8, 'In Progress', 'Test', '2026-03-04 03:34:20'),
(15, '2026-03-27', 'Rutine', 8, 'Pending', 'inspeccion a futuro', '2026-03-04 03:36:18'),
(16, '2026-03-04', 'Complain', 8, 'In Progress', 'denuncia', '2026-03-04 03:36:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sectors`
--

CREATE TABLE `sectors` (
  `id` int(11) NOT NULL,
  `zone_id` int(11) NOT NULL COMMENT 'ID de la zona a la que pertenece',
  `name` varchar(100) NOT NULL COMMENT 'Nombre del sector',
  `description` text DEFAULT NULL COMMENT 'Descripción del sector'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Sectores específicos dentro de cada zona del mercado';

--
-- Volcado de datos para la tabla `sectors`
--

INSERT INTO `sectors` (`id`, `zone_id`, `name`, `description`) VALUES
(1, 1, 'Pasillo A', NULL),
(2, 1, 'Pasillo B', NULL),
(3, 2, 'Carnes Rojas', NULL),
(4, 2, 'Pescados', NULL),
(5, 3, 'Tubérculos', NULL),
(6, 4, 'Charcutería', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `academic_degree_id` int(11) DEFAULT NULL,
  `academic_specialization_id` int(11) DEFAULT NULL,
  `job_position_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `division_id` int(11) DEFAULT NULL,
  `id_number` varchar(20) NOT NULL COMMENT 'Número de cédula o identificación',
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `second_last_name` varchar(100) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` tinyint(1) DEFAULT NULL COMMENT 'TRUE para Femenino, FALSE para Masculino',
  `hire_date` date NOT NULL,
  `termination_date` date DEFAULT NULL,
  `status` enum('active','inactive','vacation','leave','suspended') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tabla principal que almacena toda la información del personal';

--
-- Volcado de datos para la tabla `staff`
--

INSERT INTO `staff` (`id`, `academic_degree_id`, `academic_specialization_id`, `job_position_id`, `department_id`, `division_id`, `id_number`, `first_name`, `middle_name`, `last_name`, `second_last_name`, `birth_date`, `gender`, `hire_date`, `termination_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 3, 2, 1, 1, NULL, 'V12345678', 'María', NULL, 'González', NULL, '1980-05-15', 1, '2015-03-10', NULL, 'active', '2025-08-02 21:28:46', '2025-08-02 21:28:46'),
(2, 3, 1, 1, 2, NULL, 'V23456789', 'Carlos', NULL, 'Pérez', NULL, '1978-11-22', 0, '2016-07-20', NULL, 'active', '2025-08-02 21:28:46', '2025-08-02 21:28:46'),
(3, 4, 3, 1, 3, NULL, 'V34567890', 'Ana', NULL, 'Rodríguez', NULL, '1982-08-30', 1, '2017-01-15', NULL, 'active', '2025-08-02 21:28:46', '2025-08-02 21:28:46'),
(4, 4, 5, 1, 4, NULL, 'V45678901', 'Luis', NULL, 'Martínez', NULL, '1975-04-18', 0, '2014-09-05', NULL, 'active', '2025-08-02 21:28:46', '2025-08-02 21:28:46'),
(5, 3, 4, 5, 1, NULL, 'V56789012', 'Pedro', NULL, 'López', NULL, '1990-07-25', 0, '2019-05-10', NULL, 'active', '2025-08-02 21:28:46', '2025-08-02 21:28:46'),
(6, 3, 4, 5, 2, NULL, 'V67890123', 'Sofía', NULL, 'Hernández', NULL, '1992-03-18', 1, '2020-02-15', NULL, 'active', '2025-08-02 21:28:46', '2025-08-02 21:28:46'),
(7, 3, 4, 5, 3, NULL, 'V78901234', 'Jorge', NULL, 'Díaz', NULL, '1988-11-30', 0, '2018-08-22', NULL, 'active', '2025-08-02 21:28:46', '2025-08-02 21:28:46'),
(8, 3, 4, 5, 4, NULL, 'V89012345', 'Laura', NULL, 'Torres', NULL, '1991-09-05', 1, '2021-01-10', NULL, 'active', '2025-08-02 21:28:46', '2025-08-02 21:28:46'),
(9, 3, 2, 3, 4, NULL, 'V21564385', 'Andres', NULL, 'Figueroa', NULL, '1997-02-12', 0, '2020-08-02', NULL, 'active', '2025-08-02 22:53:09', '2026-01-20 03:31:13'),
(10, 3, 1, 6, 2, NULL, 'V28456987', 'Ana ', 'Laura', 'Rojas', 'Perez', '1994-08-04', 1, '2020-08-04', NULL, 'active', '2025-08-04 16:40:22', '2025-08-04 16:40:23'),
(11, 2, 1, 3, 2, NULL, 'V22321456', 'Felipe', 'Alejandro', 'Rodriguez', 'Ordaz', '1993-08-04', 0, '2022-08-04', NULL, 'active', '2025-08-04 16:41:40', '2025-08-04 16:41:40'),
(14, NULL, NULL, 1, 2, NULL, '', 'Rene', NULL, 'Bello', NULL, NULL, NULL, '0000-00-00', NULL, 'active', '2026-02-28 22:11:48', '2026-02-28 22:11:48');

--
-- Disparadores `staff`
--
DELIMITER $$
CREATE TRIGGER `staff_updated_at_trigger` BEFORE UPDATE ON `staff` FOR EACH ROW BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `staff_complete_info`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `staff_complete_info` (
`id` int(11)
,`id_number` varchar(20)
,`full_name` varchar(403)
,`first_name` varchar(100)
,`middle_name` varchar(100)
,`last_name` varchar(100)
,`second_last_name` varchar(100)
,`birth_date` date
,`gender_text` varchar(9)
,`hire_date` date
,`termination_date` date
,`status` enum('active','inactive','vacation','leave','suspended')
,`department_name` varchar(255)
,`division_name` varchar(255)
,`job_position_name` varchar(255)
,`academic_degree_name` varchar(255)
,`academic_specialization_name` varchar(255)
,`manager_name` varchar(201)
,`created_at` timestamp
,`updated_at` timestamp
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `staff_department_history`
--

CREATE TABLE `staff_department_history` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registra los cambios de departamento del personal a lo largo del tiempo';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_superadmin` tinyint(1) NOT NULL DEFAULT 0,
  `email` varchar(100) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires` datetime DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'inactive',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Almacena las credenciales de acceso al sistema';

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `staff_id`, `username`, `password_hash`, `is_superadmin`, `email`, `last_login`, `password_reset_token`, `password_reset_expires`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'mmaria', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 1, 'maria.gonzalez@empresa.com', '2026-02-21 23:40:03', NULL, NULL, 'active', '2025-08-02 21:28:47', '2026-02-28 16:39:45'),
(2, 2, 'cperez', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 0, 'carlos.perez@empresa.com', '2026-03-04 02:40:26', NULL, NULL, 'active', '2025-08-02 21:28:47', '2026-03-04 06:40:26'),
(3, 3, 'arodriguez', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 0, 'ana.rodriguez@empresa.com', '2026-01-21 00:15:09', NULL, NULL, 'active', '2025-08-02 21:28:47', '2026-01-21 04:15:09'),
(4, 4, 'lmartinez', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 0, 'luis.martinez@empresa.com', '2026-03-02 20:01:55', NULL, NULL, 'active', '2025-08-02 21:28:47', '2026-03-03 00:01:55'),
(5, NULL, 'devliq', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 0, 'devliquidacion@empresa.com', '2026-03-04 02:39:33', NULL, NULL, 'active', '2025-08-02 21:28:47', '2026-03-04 06:39:33'),
(6, NULL, 'devcob', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 0, 'devcobranza@empresa.com', '2026-03-04 02:39:46', NULL, NULL, 'active', '2025-08-02 21:28:47', '2026-03-04 06:39:46'),
(7, 9, 'devrrhh', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 1, 'devrrhh@empresa.com', '2026-02-28 14:01:48', NULL, NULL, 'active', '2025-08-02 21:28:47', '2026-02-28 18:43:21'),
(8, 7, 'devfisc', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 0, 'devfiscalizacion@empresa.com', '2026-03-04 02:39:23', NULL, NULL, 'active', '2025-08-02 21:28:47', '2026-03-04 06:39:23'),
(9, 9, 'afigueroa', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 0, 'Andres.Figueroa@empresa.com', '2026-01-19 23:33:19', NULL, NULL, 'active', '2025-08-03 02:56:10', '2026-01-20 03:33:19'),
(10, 5, 'plopez', '$2y$12$.Xv3sGjkrCSNnlJmdyz1j.sxfCYf2C/09OvOa794nxeA2sWCwX6WC', 0, 'pedro.lopez@empresa.com', NULL, NULL, NULL, 'active', '2025-08-03 13:36:10', '2025-08-03 15:33:38'),
(11, 10, 'arojas', '$2y$12$DxcEraAN3tao8.z.FaOgsuz5jP39VqoFpDSQU3qZDgioePvAK6vh6', 0, 'ana.rojas@empresa.com', '2026-01-17 13:57:01', NULL, NULL, 'active', '2025-08-04 16:42:11', '2026-01-17 17:57:01'),
(12, 11, 'frodriguez', '$2y$12$32yiS2OUJt5hgQzoB/NCiuIdgb2Yvvh8L56GOlYsY9Kh9iUnM4Ri2', 0, 'felipe.rodriguez@empresa.com', NULL, NULL, NULL, 'active', '2025-08-04 16:42:40', '2025-08-04 16:42:40'),
(13, 14, 'renebello', '$2y$10$zsr7ylbSZeUyMvKvwHeir.1v51eq/P5WXi62b8w3NAmMfDJR/iTf2', 1, 'rene.bello@seramer.com', '2026-03-04 02:39:56', NULL, NULL, 'active', '2026-02-28 22:11:48', '2026-03-04 06:39:56');

--
-- Disparadores `users`
--
DELIMITER $$
CREATE TRIGGER `users_updated_at_trigger` BEFORE UPDATE ON `users` FOR EACH ROW BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_departments`
--

CREATE TABLE `user_departments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'inactive',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Controla qué usuarios tienen acceso a qué departamentos';

--
-- Volcado de datos para la tabla `user_departments`
--

INSERT INTO `user_departments` (`id`, `user_id`, `department_id`, `role_id`, `status`, `created_at`) VALUES
(1, 1, 1, 4, 'active', '2025-08-02 21:28:47'),
(3, 3, 3, 2, 'active', '2025-08-02 21:28:47'),
(5, 5, 1, 4, 'active', '2025-08-02 21:28:47'),
(6, 6, 2, 1, 'active', '2025-08-02 21:28:47'),
(7, 7, 4, 3, 'active', '2025-08-02 21:28:47'),
(8, 8, 3, 2, 'active', '2025-08-02 21:28:47'),
(9, 9, 1, 4, 'active', '2025-08-03 02:56:10'),
(11, 11, 2, 1, 'active', '2025-08-04 16:42:11'),
(12, 12, 2, 1, 'active', '2025-08-04 16:42:40'),
(13, 13, 2, NULL, 'active', '2026-02-28 22:11:48'),
(14, 10, 1, 4, 'active', '2026-03-01 00:04:54'),
(15, 10, 5, 6, 'active', '2026-03-01 00:04:54'),
(16, 4, 5, 6, 'active', '2026-03-01 00:06:05'),
(17, 2, 5, 7, 'active', '2026-03-04 05:22:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_records`
--

CREATE TABLE `user_records` (
  `id` int(11) NOT NULL,
  `action` varchar(500) NOT NULL,
  `user_id` int(100) NOT NULL,
  `department_id` int(100) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `user_records`
--

INSERT INTO `user_records` (`id`, `action`, `user_id`, `department_id`, `created_at`) VALUES
(1, 'Ha editado una infracción', 7, 3, '2025-10-28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_roles`
--

CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `user_roles`
--

INSERT INTO `user_roles` (`id`, `user_id`, `role_id`, `created_at`) VALUES
(1, 5, 1, '2026-02-22 03:33:56'),
(2, 1, 1, '2026-02-22 03:33:56'),
(3, 6, 5, '2026-02-22 03:33:56'),
(4, 13, 1, '2026-02-22 03:33:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vacations`
--

CREATE TABLE `vacations` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('requested','approved','rejected') DEFAULT 'requested',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Administra las solicitudes y estados de vacaciones del personal';

--
-- Disparadores `vacations`
--
DELIMITER $$
CREATE TRIGGER `vacations_updated_at_trigger` BEFORE UPDATE ON `vacations` FOR EACH ROW BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zones`
--

CREATE TABLE `zones` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Nombre de la zona',
  `description` text DEFAULT NULL COMMENT 'Descripción detallada de la zona'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Zonas o áreas principales del mercado';

--
-- Volcado de datos para la tabla `zones`
--

INSERT INTO `zones` (`id`, `name`, `description`) VALUES
(1, 'Zona Norte', 'Comedores'),
(2, 'Zona Sur', 'Carnes y pescados'),
(3, 'Zona Centro', 'Verduras'),
(4, 'Zona Este', 'Víveres');

-- --------------------------------------------------------

--
-- Estructura para la vista `staff_complete_info`
--
DROP TABLE IF EXISTS `staff_complete_info`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `staff_complete_info`  AS SELECT `s`.`id` AS `id`, `s`.`id_number` AS `id_number`, concat(`s`.`first_name`,' ',ifnull(`s`.`middle_name`,''),' ',`s`.`last_name`,' ',ifnull(`s`.`second_last_name`,'')) AS `full_name`, `s`.`first_name` AS `first_name`, `s`.`middle_name` AS `middle_name`, `s`.`last_name` AS `last_name`, `s`.`second_last_name` AS `second_last_name`, `s`.`birth_date` AS `birth_date`, CASE WHEN `s`.`gender` = 1 THEN 'Femenino' ELSE 'Masculino' END AS `gender_text`, `s`.`hire_date` AS `hire_date`, `s`.`termination_date` AS `termination_date`, `s`.`status` AS `status`, `d`.`name` AS `department_name`, `dv`.`name` AS `division_name`, `jp`.`name` AS `job_position_name`, `ad`.`name` AS `academic_degree_name`, `asp`.`name` AS `academic_specialization_name`, concat(`m`.`first_name`,' ',`m`.`last_name`) AS `manager_name`, `s`.`created_at` AS `created_at`, `s`.`updated_at` AS `updated_at` FROM ((((((`staff` `s` left join `departments` `d` on(`s`.`department_id` = `d`.`id`)) left join `divisions` `dv` on(`s`.`division_id` = `dv`.`id`)) left join `job_positions` `jp` on(`s`.`job_position_id` = `jp`.`id`)) left join `academic_degrees` `ad` on(`s`.`academic_degree_id` = `ad`.`id`)) left join `academic_specializations` `asp` on(`s`.`academic_specialization_id` = `asp`.`id`)) left join `staff` `m` on(`d`.`manager_id` = `m`.`id`)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `academic_degrees`
--
ALTER TABLE `academic_degrees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_name` (`name`);

--
-- Indices de la tabla `academic_specializations`
--
ALTER TABLE `academic_specializations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_name` (`name`);

--
-- Indices de la tabla `administrative_appeals`
--
ALTER TABLE `administrative_appeals`
  ADD PRIMARY KEY (`appeal_id`);

--
-- Indices de la tabla `administrative_records`
--
ALTER TABLE `administrative_records`
  ADD PRIMARY KEY (`record_id`),
  ADD UNIQUE KEY `infraction_id` (`infraction_id`);

--
-- Indices de la tabla `alert_tracking`
--
ALTER TABLE `alert_tracking`
  ADD PRIMARY KEY (`tracking_id`);

--
-- Indices de la tabla `alert_types`
--
ALTER TABLE `alert_types`
  ADD PRIMARY KEY (`alert_type_id`),
  ADD UNIQUE KEY `alert_type_name` (`alert_type_name`);

--
-- Indices de la tabla `attached_documents`
--
ALTER TABLE `attached_documents`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `fk_documents_users` (`uploaded_by_user_id`);

--
-- Indices de la tabla `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_staff_date` (`staff_id`,`date`),
  ADD KEY `idx_staff_id` (`staff_id`),
  ADD KEY `idx_date` (`date`),
  ADD KEY `idx_staff_date` (`staff_id`,`date`);

--
-- Indices de la tabla `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_table_affected` (`table_affected`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_record_id` (`record_id`);

--
-- Indices de la tabla `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indices de la tabla `awardees`
--
ALTER TABLE `awardees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_number` (`id_number`);

--
-- Indices de la tabla `cash_registers`
--
ALTER TABLE `cash_registers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cash_register_user` (`user_id`),
  ADD KEY `idx_cash_register_status` (`status`);

--
-- Indices de la tabla `citations`
--
ALTER TABLE `citations`
  ADD PRIMARY KEY (`citation_id`);

--
-- Indices de la tabla `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`complaint_id`),
  ADD KEY `fk_complaints_users` (`client_user_id`),
  ADD KEY `fk_complaints_stalls` (`stall_id`),
  ADD KEY `fk_complaints_awardees` (`awardee_id`);

--
-- Indices de la tabla `complaint_related_articles`
--
ALTER TABLE `complaint_related_articles`
  ADD PRIMARY KEY (`complaint_id`,`article_id`);

--
-- Indices de la tabla `complaint_tracking`
--
ALTER TABLE `complaint_tracking`
  ADD PRIMARY KEY (`tracking_id`);

--
-- Indices de la tabla `compliance_alerts`
--
ALTER TABLE `compliance_alerts`
  ADD PRIMARY KEY (`alert_id`),
  ADD KEY `fk_compliance_alerts_alert_types` (`alert_type_id`),
  ADD KEY `fk_compliance_alerts_awardees` (`awardee_id`),
  ADD KEY `fk_compliance_alerts_stalls` (`stall_id`),
  ADD KEY `fk_compliance_alerts_infractions` (`escalated_infraction_id`),
  ADD KEY `fk_compliance_alerts_users` (`generated_by_user_id`);

--
-- Indices de la tabla `conciliation_reports`
--
ALTER TABLE `conciliation_reports`
  ADD PRIMARY KEY (`report_id`);

--
-- Indices de la tabla `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `awardee_id` (`awardee_id`),
  ADD KEY `fk_contracts_fiscal_year` (`fiscal_year_id`);

--
-- Indices de la tabla `contract_business_categories`
--
ALTER TABLE `contract_business_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contract_id` (`contract_id`),
  ADD KEY `external_category_id` (`external_category_id`),
  ADD KEY `internal_category_id` (`internal_category_id`);

--
-- Indices de la tabla `contract_locations`
--
ALTER TABLE `contract_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contract_id` (`contract_id`),
  ADD KEY `stall_id` (`stall_id`);

--
-- Indices de la tabla `contract_payments`
--
ALTER TABLE `contract_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `euro_rate_id` (`euro_rate_id`),
  ADD KEY `idx_contract_payments_contract` (`contract_id`),
  ADD KEY `idx_contract_payments_date` (`payment_date`);

--
-- Indices de la tabla `contract_payment_installments`
--
ALTER TABLE `contract_payment_installments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_method_id` (`payment_method_id`),
  ADD KEY `idx_installment_contract_payment` (`contract_payment_id`),
  ADD KEY `idx_installment_date` (`date`);

--
-- Indices de la tabla `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_shift_type` (`shift_type`),
  ADD KEY `idx_manager_id` (`manager_id`);

--
-- Indices de la tabla `department_schedules`
--
ALTER TABLE `department_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_department_id` (`department_id`),
  ADD KEY `idx_day` (`day`),
  ADD KEY `idx_time_range` (`start_time`,`end_time`);

--
-- Indices de la tabla `divisions`
--
ALTER TABLE `divisions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_department_id` (`department_id`),
  ADD KEY `idx_name` (`name`);

--
-- Indices de la tabla `economic_indicators`
--
ALTER TABLE `economic_indicators`
  ADD PRIMARY KEY (`indicator_id`) USING BTREE,
  ADD UNIQUE KEY `idx_effective_date` (`effective_date`) USING BTREE;

--
-- Indices de la tabla `euro_rates`
--
ALTER TABLE `euro_rates`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `external_business_categories`
--
ALTER TABLE `external_business_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD PRIMARY KEY (`payment_id`);

--
-- Indices de la tabla `fine_payments`
--
ALTER TABLE `fine_payments`
  ADD PRIMARY KEY (`payment_id`);

--
-- Indices de la tabla `fiscalization_roles`
--
ALTER TABLE `fiscalization_roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indices de la tabla `fiscalization_user_level`
--
ALTER TABLE `fiscalization_user_level`
  ADD PRIMARY KEY (`user_level_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indices de la tabla `fiscal_year`
--
ALTER TABLE `fiscal_year`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `infractions`
--
ALTER TABLE `infractions`
  ADD PRIMARY KEY (`infraction_id`),
  ADD KEY `fk_infractions_awardees` (`awardee_id`),
  ADD KEY `fk_infractions_infraction_types` (`infraction_type_id`),
  ADD KEY `fk_infractions_market_stalls` (`stall_id`);

--
-- Indices de la tabla `infraction_types`
--
ALTER TABLE `infraction_types`
  ADD PRIMARY KEY (`infraction_type_id`),
  ADD UNIQUE KEY `infraction_type_name` (`infraction_type_name`);

--
-- Indices de la tabla `inspection_reports`
--
ALTER TABLE `inspection_reports`
  ADD PRIMARY KEY (`report_id`);

--
-- Indices de la tabla `inspection_updates`
--
ALTER TABLE `inspection_updates`
  ADD PRIMARY KEY (`update_id`),
  ADD KEY `report_id` (`report_id`),
  ADD KEY `updated_by_user_id` (`updated_by_user_id`);

--
-- Indices de la tabla `inspectors`
--
ALTER TABLE `inspectors`
  ADD PRIMARY KEY (`inspector_id`),
  ADD UNIQUE KEY `inspector_code` (`inspector_code`);

--
-- Indices de la tabla `internal_business_categories`
--
ALTER TABLE `internal_business_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `job_positions`
--
ALTER TABLE `job_positions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_name` (`name`);

--
-- Indices de la tabla `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_staff_id` (`staff_id`),
  ADD KEY `idx_request_date` (`request_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_type` (`type`);

--
-- Indices de la tabla `market_stalls`
--
ALTER TABLE `market_stalls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sector_id` (`sector_id`),
  ADD KEY `idx_market_stalls_awardee` (`awardee_id`);

--
-- Indices de la tabla `market_stall_info`
--
ALTER TABLE `market_stall_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_stall_id` (`stall_id`),
  ADD KEY `idx_sector_id` (`sector_id`),
  ADD KEY `idx_category_id` (`external_business_category_id`),
  ADD KEY `idx_rif` (`rif`);

--
-- Indices de la tabla `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_slug` (`slug`);

--
-- Indices de la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `fk_notifications_sender_users` (`sender_user_id`),
  ADD KEY `fk_notifications_recipient_users` (`recipient_user_id`),
  ADD KEY `fk_notifications_complaints` (`complaint_id`),
  ADD KEY `fk_notifications_alerts` (`alert_id`),
  ADD KEY `fk_notifications_infractions` (`infraction_id`),
  ADD KEY `idx_recipient_user` (`recipient_user_id`),
  ADD KEY `idx_target_role` (`target_role_id`),
  ADD KEY `idx_target_dept` (`target_department_id`),
  ADD KEY `idx_is_global` (`is_global`),
  ADD KEY `sanction_id` (`sanction_id`);

--
-- Indices de la tabla `ordinance_articles`
--
ALTER TABLE `ordinance_articles`
  ADD PRIMARY KEY (`article_id`),
  ADD UNIQUE KEY `article_number` (`article_number`),
  ADD KEY `fk_ordinance_articles_infraction_types` (`infraction_type_id`);

--
-- Indices de la tabla `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indices de la tabla `role_modules`
--
ALTER TABLE `role_modules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_module` (`role_id`,`module_id`);

--
-- Indices de la tabla `sanctions`
--
ALTER TABLE `sanctions`
  ADD PRIMARY KEY (`sanction_id`),
  ADD UNIQUE KEY `infraction_id` (`infraction_id`),
  ADD KEY `fk_sanctions_sanction_types` (`sanction_type_id`),
  ADD KEY `fk_sanctions_users` (`imposed_by_user_id`);

--
-- Indices de la tabla `sanction_types`
--
ALTER TABLE `sanction_types`
  ADD PRIMARY KEY (`sanction_type_id`),
  ADD UNIQUE KEY `sanction_type_name` (`severity_name`);

--
-- Indices de la tabla `scheduled_inspections`
--
ALTER TABLE `scheduled_inspections`
  ADD PRIMARY KEY (`inspection_id`),
  ADD KEY `fk_inspections_users` (`assigned_responsible_id`);

--
-- Indices de la tabla `sectors`
--
ALTER TABLE `sectors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `zone_id` (`zone_id`);

--
-- Indices de la tabla `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_number` (`id_number`),
  ADD KEY `idx_id_number` (`id_number`),
  ADD KEY `idx_department_id` (`department_id`),
  ADD KEY `idx_division_id` (`division_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_hire_date` (`hire_date`),
  ADD KEY `idx_full_name` (`first_name`,`last_name`),
  ADD KEY `idx_job_position` (`job_position_id`),
  ADD KEY `academic_degree_id` (`academic_degree_id`),
  ADD KEY `academic_specialization_id` (`academic_specialization_id`);

--
-- Indices de la tabla `staff_department_history`
--
ALTER TABLE `staff_department_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_staff_id` (`staff_id`),
  ADD KEY `idx_department_id` (`department_id`),
  ADD KEY `idx_date_range` (`start_date`,`end_date`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_staff_id` (`staff_id`);

--
-- Indices de la tabla `user_departments`
--
ALTER TABLE `user_departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_department` (`user_id`,`department_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_department_id` (`department_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `fk_user_dep_role` (`role_id`);

--
-- Indices de la tabla `user_records`
--
ALTER TABLE `user_records`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_role` (`user_id`,`role_id`);

--
-- Indices de la tabla `vacations`
--
ALTER TABLE `vacations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_staff_id` (`staff_id`),
  ADD KEY `idx_date_range` (`start_date`,`end_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indices de la tabla `zones`
--
ALTER TABLE `zones`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `academic_degrees`
--
ALTER TABLE `academic_degrees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `academic_specializations`
--
ALTER TABLE `academic_specializations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `administrative_appeals`
--
ALTER TABLE `administrative_appeals`
  MODIFY `appeal_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `administrative_records`
--
ALTER TABLE `administrative_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `alert_tracking`
--
ALTER TABLE `alert_tracking`
  MODIFY `tracking_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `alert_types`
--
ALTER TABLE `alert_types`
  MODIFY `alert_type_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `attached_documents`
--
ALTER TABLE `attached_documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de la tabla `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `awardees`
--
ALTER TABLE `awardees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `cash_registers`
--
ALTER TABLE `cash_registers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único de la caja', AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `citations`
--
ALTER TABLE `citations`
  MODIFY `citation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `complaints`
--
ALTER TABLE `complaints`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `complaint_tracking`
--
ALTER TABLE `complaint_tracking`
  MODIFY `tracking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `compliance_alerts`
--
ALTER TABLE `compliance_alerts`
  MODIFY `alert_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `conciliation_reports`
--
ALTER TABLE `conciliation_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `contracts`
--
ALTER TABLE `contracts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `contract_business_categories`
--
ALTER TABLE `contract_business_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `contract_locations`
--
ALTER TABLE `contract_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `contract_payments`
--
ALTER TABLE `contract_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del pago', AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT de la tabla `contract_payment_installments`
--
ALTER TABLE `contract_payment_installments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del abono';

--
-- AUTO_INCREMENT de la tabla `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `department_schedules`
--
ALTER TABLE `department_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `divisions`
--
ALTER TABLE `divisions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `economic_indicators`
--
ALTER TABLE `economic_indicators`
  MODIFY `indicator_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `euro_rates`
--
ALTER TABLE `euro_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `external_business_categories`
--
ALTER TABLE `external_business_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT de la tabla `fee_payments`
--
ALTER TABLE `fee_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de la tabla `fine_payments`
--
ALTER TABLE `fine_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `fiscalization_roles`
--
ALTER TABLE `fiscalization_roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=782;

--
-- AUTO_INCREMENT de la tabla `fiscalization_user_level`
--
ALTER TABLE `fiscalization_user_level`
  MODIFY `user_level_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=262;

--
-- AUTO_INCREMENT de la tabla `fiscal_year`
--
ALTER TABLE `fiscal_year`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `infractions`
--
ALTER TABLE `infractions`
  MODIFY `infraction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `infraction_types`
--
ALTER TABLE `infraction_types`
  MODIFY `infraction_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `inspection_reports`
--
ALTER TABLE `inspection_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `inspection_updates`
--
ALTER TABLE `inspection_updates`
  MODIFY `update_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `inspectors`
--
ALTER TABLE `inspectors`
  MODIFY `inspector_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `internal_business_categories`
--
ALTER TABLE `internal_business_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `job_positions`
--
ALTER TABLE `job_positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `market_stalls`
--
ALTER TABLE `market_stalls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `market_stall_info`
--
ALTER TABLE `market_stall_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT de la tabla `ordinance_articles`
--
ALTER TABLE `ordinance_articles`
  MODIFY `article_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del método de pago', AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `role_modules`
--
ALTER TABLE `role_modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT de la tabla `sanctions`
--
ALTER TABLE `sanctions`
  MODIFY `sanction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `sanction_types`
--
ALTER TABLE `sanction_types`
  MODIFY `sanction_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `scheduled_inspections`
--
ALTER TABLE `scheduled_inspections`
  MODIFY `inspection_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `sectors`
--
ALTER TABLE `sectors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `staff_department_history`
--
ALTER TABLE `staff_department_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `user_departments`
--
ALTER TABLE `user_departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `user_records`
--
ALTER TABLE `user_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `vacations`
--
ALTER TABLE `vacations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `zones`
--
ALTER TABLE `zones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `administrative_records`
--
ALTER TABLE `administrative_records`
  ADD CONSTRAINT `fk_admin_records_infractions` FOREIGN KEY (`infraction_id`) REFERENCES `infractions` (`infraction_id`);

--
-- Filtros para la tabla `attached_documents`
--
ALTER TABLE `attached_documents`
  ADD CONSTRAINT `fk_documents_users` FOREIGN KEY (`uploaded_by_user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `cash_registers`
--
ALTER TABLE `cash_registers`
  ADD CONSTRAINT `cash_registers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `fk_complaints_awardees` FOREIGN KEY (`awardee_id`) REFERENCES `awardees` (`id`),
  ADD CONSTRAINT `fk_complaints_stalls` FOREIGN KEY (`stall_id`) REFERENCES `market_stalls` (`id`),
  ADD CONSTRAINT `fk_complaints_users` FOREIGN KEY (`client_user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `compliance_alerts`
--
ALTER TABLE `compliance_alerts`
  ADD CONSTRAINT `fk_compliance_alerts_alert_types` FOREIGN KEY (`alert_type_id`) REFERENCES `alert_types` (`alert_type_id`),
  ADD CONSTRAINT `fk_compliance_alerts_awardees` FOREIGN KEY (`awardee_id`) REFERENCES `awardees` (`id`),
  ADD CONSTRAINT `fk_compliance_alerts_infractions` FOREIGN KEY (`escalated_infraction_id`) REFERENCES `infractions` (`infraction_id`),
  ADD CONSTRAINT `fk_compliance_alerts_stalls` FOREIGN KEY (`stall_id`) REFERENCES `market_stalls` (`id`),
  ADD CONSTRAINT `fk_compliance_alerts_users` FOREIGN KEY (`generated_by_user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `contracts`
--
ALTER TABLE `contracts`
  ADD CONSTRAINT `contracts_ibfk_2` FOREIGN KEY (`awardee_id`) REFERENCES `awardees` (`id`),
  ADD CONSTRAINT `fk_contracts_fiscal_year` FOREIGN KEY (`fiscal_year_id`) REFERENCES `fiscal_year` (`id`);

--
-- Filtros para la tabla `contract_business_categories`
--
ALTER TABLE `contract_business_categories`
  ADD CONSTRAINT `contract_business_categories_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`),
  ADD CONSTRAINT `contract_business_categories_ibfk_2` FOREIGN KEY (`external_category_id`) REFERENCES `external_business_categories` (`id`),
  ADD CONSTRAINT `contract_business_categories_ibfk_3` FOREIGN KEY (`internal_category_id`) REFERENCES `internal_business_categories` (`id`);

--
-- Filtros para la tabla `contract_locations`
--
ALTER TABLE `contract_locations`
  ADD CONSTRAINT `contract_locations_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`),
  ADD CONSTRAINT `contract_locations_ibfk_2` FOREIGN KEY (`stall_id`) REFERENCES `market_stalls` (`id`);

--
-- Filtros para la tabla `contract_payments`
--
ALTER TABLE `contract_payments`
  ADD CONSTRAINT `contract_payments_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contract_payments_ibfk_2` FOREIGN KEY (`euro_rate_id`) REFERENCES `euro_rates` (`id`);

--
-- Filtros para la tabla `contract_payment_installments`
--
ALTER TABLE `contract_payment_installments`
  ADD CONSTRAINT `contract_payment_installments_ibfk_1` FOREIGN KEY (`contract_payment_id`) REFERENCES `contract_payments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contract_payment_installments_ibfk_2` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`);

--
-- Filtros para la tabla `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `fk_departments_manager` FOREIGN KEY (`manager_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `department_schedules`
--
ALTER TABLE `department_schedules`
  ADD CONSTRAINT `department_schedules_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `divisions`
--
ALTER TABLE `divisions`
  ADD CONSTRAINT `divisions_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `fiscalization_user_level`
--
ALTER TABLE `fiscalization_user_level`
  ADD CONSTRAINT `fk_fisc_user_level_role` FOREIGN KEY (`role_id`) REFERENCES `fiscalization_roles` (`role_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fisc_user_level_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `infractions`
--
ALTER TABLE `infractions`
  ADD CONSTRAINT `fk_infractions_awardees` FOREIGN KEY (`awardee_id`) REFERENCES `awardees` (`id`),
  ADD CONSTRAINT `fk_infractions_infraction_types` FOREIGN KEY (`infraction_type_id`) REFERENCES `infraction_types` (`infraction_type_id`),
  ADD CONSTRAINT `fk_infractions_market_stalls` FOREIGN KEY (`stall_id`) REFERENCES `market_stalls` (`id`);

--
-- Filtros para la tabla `inspection_updates`
--
ALTER TABLE `inspection_updates`
  ADD CONSTRAINT `inspection_updates_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `inspection_reports` (`report_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inspection_updates_ibfk_2` FOREIGN KEY (`updated_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `market_stalls`
--
ALTER TABLE `market_stalls`
  ADD CONSTRAINT `fk_market_stalls_awardee` FOREIGN KEY (`awardee_id`) REFERENCES `awardees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `market_stalls_ibfk_1` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`);

--
-- Filtros para la tabla `market_stall_info`
--
ALTER TABLE `market_stall_info`
  ADD CONSTRAINT `fk_stall_info_category` FOREIGN KEY (`external_business_category_id`) REFERENCES `external_business_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_stall_info_sector` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_stall_info_stall` FOREIGN KEY (`stall_id`) REFERENCES `market_stalls` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_alerts` FOREIGN KEY (`alert_id`) REFERENCES `compliance_alerts` (`alert_id`),
  ADD CONSTRAINT `fk_notifications_complaints` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`complaint_id`),
  ADD CONSTRAINT `fk_notifications_infractions` FOREIGN KEY (`infraction_id`) REFERENCES `infractions` (`infraction_id`),
  ADD CONSTRAINT `fk_notifications_recipient_users` FOREIGN KEY (`recipient_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_notifications_sender_users` FOREIGN KEY (`sender_user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `ordinance_articles`
--
ALTER TABLE `ordinance_articles`
  ADD CONSTRAINT `fk_ordinance_articles_infraction_types` FOREIGN KEY (`infraction_type_id`) REFERENCES `infraction_types` (`infraction_type_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `roles`
--
ALTER TABLE `roles`
  ADD CONSTRAINT `roles_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `sanctions`
--
ALTER TABLE `sanctions`
  ADD CONSTRAINT `fk_sanctions_infractions` FOREIGN KEY (`infraction_id`) REFERENCES `infractions` (`infraction_id`),
  ADD CONSTRAINT `fk_sanctions_sanction_types` FOREIGN KEY (`sanction_type_id`) REFERENCES `sanction_types` (`sanction_type_id`),
  ADD CONSTRAINT `fk_sanctions_users` FOREIGN KEY (`imposed_by_user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `scheduled_inspections`
--
ALTER TABLE `scheduled_inspections`
  ADD CONSTRAINT `fk_inspections_users` FOREIGN KEY (`assigned_responsible_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_scheduled_inspections_users` FOREIGN KEY (`assigned_responsible_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `sectors`
--
ALTER TABLE `sectors`
  ADD CONSTRAINT `sectors_ibfk_1` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`);

--
-- Filtros para la tabla `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`academic_degree_id`) REFERENCES `academic_degrees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_ibfk_2` FOREIGN KEY (`academic_specialization_id`) REFERENCES `academic_specializations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_ibfk_3` FOREIGN KEY (`job_position_id`) REFERENCES `job_positions` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_ibfk_4` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_ibfk_5` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `staff_department_history`
--
ALTER TABLE `staff_department_history`
  ADD CONSTRAINT `staff_department_history_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `staff_department_history_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `user_departments`
--
ALTER TABLE `user_departments`
  ADD CONSTRAINT `fk_user_dep_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_departments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_departments_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `fk_user_roles_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `vacations`
--
ALTER TABLE `vacations`
  ADD CONSTRAINT `vacations_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
