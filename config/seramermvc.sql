-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-10-2025 a las 17:54:39
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

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
(1, 'Mariela', 'del Carmen', 'Romero', 'Listas', 'V24569874', '584148035352', 'mariela@example.com', 'test'),
(2, 'Daniel', NULL, 'Fiiguera', NULL, 'V14852369', NULL, 'danielfiguera@example.com', 'test');

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
(1, 12, 'Caja 1 - Cobranza', 'active', '2025-08-04 16:54:44', '2025-08-04 16:57:29');

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
(1, 4, '2025-09-23 16:13:00', 'dsdsds dsds', 5, 'Completed');

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
(9, '2025-09-19 02:43:59', 8, 'pepe veraz', '04264804748', 'pepe@gmail.com', 'sdsdsdsdsd dwsdsd', 1, 2, 'Suggestion', 'Received', 'Medium', 'dsds dsdsd');

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
(2, 9, '2025-09-24 13:01:55', 1, 'Resolution', 'hjghjhg', 'jghjgh');

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
(1, 1, 1, 'Agreement Reached', 'dsddsdsd dsdsd dsdsd dsd ', '2025-09-22 02:27:18');

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
  `contract_mode` enum('monthly','weekly') DEFAULT NULL COMMENT 'Modalidad de pago'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Contratos de adjudicación de espacios en el mercado';

--
-- Volcado de datos para la tabla `contracts`
--

INSERT INTO `contracts` (`id`, `awardee_id`, `fiscal_year_id`, `start_date`, `end_date`, `type`, `contract_mode`) VALUES
(1, 1, 1, '2025-08-04', '2026-04-30', 'simultaneous', 'monthly'),
(2, 2, 1, '2025-08-04', '2026-04-30', 'simultaneous', 'monthly');

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
(1, 1, NULL, 41, 'internal'),
(2, 1, NULL, 22, 'internal'),
(5, 2, NULL, 21, 'internal'),
(6, 2, NULL, 33, 'internal');

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
(1, 1, 1),
(3, 2, 1);

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
(10, 1, 'PAY-1-001', 1, '2025-08-04', 5.50, 'pending'),
(11, 1, 'PAY-1-002', NULL, '2025-09-04', 5.50, 'pending'),
(12, 1, 'PAY-1-003', NULL, '2025-10-04', 5.50, 'pending'),
(13, 1, 'PAY-1-004', NULL, '2025-11-04', 5.50, 'pending'),
(14, 1, 'PAY-1-005', NULL, '2025-12-04', 5.50, 'pending'),
(15, 1, 'PAY-1-006', NULL, '2026-01-04', 5.50, 'pending'),
(16, 1, 'PAY-1-007', NULL, '2026-02-04', 5.50, 'pending'),
(17, 1, 'PAY-1-008', NULL, '2026-03-04', 5.50, 'pending'),
(18, 1, 'PAY-1-009', NULL, '2026-04-04', 5.50, 'pending'),
(28, 2, 'PAY-2-001', 1, '2025-08-04', 1167.04, 'pending'),
(29, 2, 'PAY-2-002', NULL, '2025-09-04', 0.00, 'pending'),
(30, 2, 'PAY-2-003', NULL, '2025-10-04', 0.00, 'pending'),
(31, 2, 'PAY-2-004', NULL, '2025-11-04', 0.00, 'pending'),
(32, 2, 'PAY-2-005', NULL, '2025-12-04', 0.00, 'pending'),
(33, 2, 'PAY-2-006', NULL, '2026-01-04', 0.00, 'pending'),
(34, 2, 'PAY-2-007', NULL, '2026-02-04', 0.00, 'pending'),
(35, 2, 'PAY-2-008', NULL, '2026-03-04', 0.00, 'pending'),
(36, 2, 'PAY-2-009', NULL, '2026-04-04', 0.00, 'pending');

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
  `concept` text DEFAULT NULL COMMENT 'Concepto o descripción del abono'
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
(1, 'Liquidacion', 'Departamento de liquidación de nóminas', 'Matutino', '2025-08-02 21:28:46', 1),
(2, 'Cobranza', 'Departamento de gestión de cobros', 'Vespertino', '2025-08-02 21:28:46', 2),
(3, 'Fiscalizacion', 'Departamento de control fiscal', 'Mixto', '2025-08-02 21:28:46', 3),
(4, 'Recursos Humanos', 'Gestión del personal y talento humano', 'Administrativo', '2025-08-02 21:28:46', 4);

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
(6, 147.65, 'abril', '2025');

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
  `transaction_reference` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `payment_status` varchar(50) NOT NULL DEFAULT 'Paid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, '2025-05-01', '2026-04-30', '2025', 'active', '2025-08-04 01:17:49');

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
(4, 2, 1, '2025-09-18 20:19:43', 1, 'sssssssss fgfgfgf ffgfg', 'In Process', 'sssssssssss', '68cc695fbe614.jpg', 'active');

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
(3, 'Abandono de mercancía', 'Abandonar envases y mercancías en recintos comunes, incumpliendo normas o directrices.', '107.2', 1),
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
(36, 'Expender bebidas alcohólicas o prohibidas', 'Expender bebidas alcohólicas o cualquier sustancia prohibida.', '109.7', 3);

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
(2, 1, 5, 1, 1, 2, '2025-09-17 01:32:49', 'swadsd', '', ''),
(3, 2, 1, 3, 1, 2, '2025-09-17 11:43:12', 'aaaaa', '', ''),
(4, 3, 3, 5, 1, 1, '2025-09-17 12:50:32', 'wwwwwwwwwwwwww', '', ''),
(5, 4, 5, 1, 1, 2, '2025-09-19 13:07:49', 'hghghg', '', '');

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
(5, 'D-02', 'Amador Jose Figuera', '04264804748', 'amador_01@gmail.com', '2025-09-13', 1, '2025-09-16 16:54:44', '2025-09-17 12:50:04');

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
(1, 'Dulceria y Emprendedores', 2.00),
(2, 'Varios', 2.00),
(3, 'Viveres', 2.00),
(4, 'Quincallas', 2.00),
(6, 'Legumbre y Hortalizas', 2.00),
(7, 'Verduras', 2.00),
(8, 'Frutas', 2.00),
(9, 'Pescados, Fresco o Salado', 2.00),
(10, 'Condimentos y especias para cocinar', 2.00),
(11, 'Arepera', 2.50),
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
(41, 'Aliños', 2.50);

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
  `sector_id` int(11) NOT NULL COMMENT 'Sector donde está ubicado',
  `stall_number` varchar(50) NOT NULL COMMENT 'Número o identificador del local',
  `location_description` varchar(255) DEFAULT NULL COMMENT 'Descripción detallada de la ubicación'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Puestos físicos o locales dentro del mercado';

--
-- Volcado de datos para la tabla `market_stalls`
--

INSERT INTO `market_stalls` (`id`, `sector_id`, `stall_number`, `location_description`) VALUES
(1, 1, 'L-001', 'testing');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `notification_datetime` timestamp NULL DEFAULT current_timestamp(),
  `sender_user_id` int(11) DEFAULT NULL,
  `recipient_user_id` int(11) NOT NULL,
  `notification_type` varchar(100) NOT NULL,
  `notification_subject` varchar(255) NOT NULL,
  `notification_message` text NOT NULL,
  `read_status` tinyint(1) DEFAULT 0,
  `complaint_id` int(11) DEFAULT NULL,
  `alert_id` int(11) DEFAULT NULL,
  `infraction_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'Efectivo', 1),
(2, 'Transferencia Bancaria', 1),
(3, 'Pago Móvil', 1),
(4, 'Tarjeta de Débito', 1),
(5, 'Biopago', 1),
(6, 'Dólares', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permissions`
--

CREATE TABLE `permissions` (
  `permission_id` int(11) NOT NULL,
  `permission_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 4, 1, 100.00, 'Euro', '2025-09-21 00:27:52', '2025-09-22', '2025-09-23', 'Paid', 'dsdssd', 0, 1);

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
(1, '2025-09-19', 'dsdsds', 8, 'Pending', 'dsdsds', '2025-09-17 01:32:48'),
(2, '2025-09-25', 'aaaaaaaaaaaaaa', 3, 'Pending', 'aaaaaaaaaaaaaaaaa', '2025-09-17 11:43:12'),
(3, '2025-09-25', 'wwwwwwwww', 9, 'In Progress', 'wwwwwwwwwwwww', '2025-09-17 12:50:32'),
(4, '2025-09-20', 'hjkjkjhk', 11, 'Pending', 'jhgjhg', '2025-09-19 13:07:49');

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
(1, 1, 'Sector 1', 'test');

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
(9, 3, 2, 3, 1, NULL, 'V21564385', 'Andres', NULL, 'Figueroa', NULL, '1997-02-12', 0, '2020-08-02', NULL, 'active', '2025-08-02 22:53:09', '2025-08-02 22:53:10'),
(10, 3, 1, 6, 2, NULL, 'V28456987', 'Ana ', 'Laura', 'Rojas', 'Perez', '1994-08-04', 1, '2020-08-04', NULL, 'active', '2025-08-04 16:40:22', '2025-08-04 16:40:23'),
(11, 2, 1, 3, 2, NULL, 'V22321456', 'Felipe', 'Alejandro', 'Rodriguez', 'Ordaz', '1993-08-04', 0, '2022-08-04', NULL, 'active', '2025-08-04 16:41:40', '2025-08-04 16:41:40');

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

INSERT INTO `users` (`id`, `staff_id`, `username`, `password_hash`, `email`, `last_login`, `password_reset_token`, `password_reset_expires`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'mmaria', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'maria.gonzalez@empresa.com', '2025-08-04 11:37:31', NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-04 15:37:31'),
(2, 2, 'cperez', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'carlos.perez@empresa.com', '2025-08-04 13:19:16', NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-04 17:19:16'),
(3, 3, 'arodriguez', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'ana.rodriguez@empresa.com', NULL, NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-03 13:39:43'),
(4, 4, 'lmartinez', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'luis.martinez@empresa.com', '2025-08-03 22:23:17', NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-04 02:23:17'),
(5, NULL, 'devliq', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'devliquidacion@empresa.com', '2025-08-03 16:50:17', NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-03 20:50:17'),
(6, NULL, 'devcob', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'devcobranza@empresa.com', NULL, NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-03 13:39:47'),
(7, NULL, 'devrrhh', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'devrrhh@empresa.com', NULL, NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-08-03 13:39:48'),
(8, NULL, 'devfisc', '$2y$10$c1/w.fOiN.1tFuNTUJ0ZnuejWAUnTP.EFcds7MHQnu1G/h47gw7Ly', 'devfiscalizacion@empresa.com', '2025-10-26 10:14:46', NULL, NULL, 'active', '2025-08-02 21:28:47', '2025-10-26 14:14:46'),
(9, 9, 'afigueroa', '$2y$12$iFj3D7pQ3wCsdkCs4nU5O.Z0rBgK4ydNpbph5RumpqlqLj6q96SuO', 'Andres.Figueroa@empresa.com', NULL, NULL, NULL, 'active', '2025-08-03 02:56:10', '2025-08-03 02:56:10'),
(10, 5, 'plopez', '$2y$12$.Xv3sGjkrCSNnlJmdyz1j.sxfCYf2C/09OvOa794nxeA2sWCwX6WC', 'pedro.lopez@empresa.com', NULL, NULL, NULL, 'active', '2025-08-03 13:36:10', '2025-08-03 15:33:38'),
(11, 10, 'arojas', '$2y$12$DxcEraAN3tao8.z.FaOgsuz5jP39VqoFpDSQU3qZDgioePvAK6vh6', 'ana.rojas@empresa.com', NULL, NULL, NULL, 'active', '2025-08-04 16:42:11', '2025-08-04 16:42:11'),
(12, 11, 'frodriguez', '$2y$12$32yiS2OUJt5hgQzoB/NCiuIdgb2Yvvh8L56GOlYsY9Kh9iUnM4Ri2', 'felipe.rodriguez@empresa.com', NULL, NULL, NULL, 'active', '2025-08-04 16:42:40', '2025-08-04 16:42:40');

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
  `status` enum('active','inactive') DEFAULT 'inactive',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Controla qué usuarios tienen acceso a qué departamentos';

--
-- Volcado de datos para la tabla `user_departments`
--

INSERT INTO `user_departments` (`id`, `user_id`, `department_id`, `status`, `created_at`) VALUES
(1, 1, 1, 'active', '2025-08-02 21:28:47'),
(2, 2, 2, 'active', '2025-08-02 21:28:47'),
(3, 3, 3, 'active', '2025-08-02 21:28:47'),
(4, 4, 4, 'active', '2025-08-02 21:28:47'),
(5, 5, 1, 'active', '2025-08-02 21:28:47'),
(6, 6, 2, 'active', '2025-08-02 21:28:47'),
(7, 7, 3, 'active', '2025-08-02 21:28:47'),
(8, 8, 3, 'active', '2025-08-02 21:28:47'),
(9, 9, 1, 'active', '2025-08-03 02:56:10'),
(10, 10, 1, 'active', '2025-08-03 13:36:11'),
(11, 11, 2, 'active', '2025-08-04 16:42:11'),
(12, 12, 2, 'active', '2025-08-04 16:42:40');

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
(1, 'Zona Hortalizas', 'testing');

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
  ADD KEY `sector_id` (`sector_id`);

--
-- Indices de la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `fk_notifications_sender_users` (`sender_user_id`),
  ADD KEY `fk_notifications_recipient_users` (`recipient_user_id`),
  ADD KEY `fk_notifications_complaints` (`complaint_id`),
  ADD KEY `fk_notifications_alerts` (`alert_id`),
  ADD KEY `fk_notifications_infractions` (`infraction_id`);

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
-- Indices de la tabla `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`permission_id`),
  ADD UNIQUE KEY `permission_name` (`permission_name`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indices de la tabla `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `fk_role_permissions_permissions` (`permission_id`);

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
  ADD KEY `idx_status` (`status`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `awardees`
--
ALTER TABLE `awardees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `cash_registers`
--
ALTER TABLE `cash_registers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único de la caja', AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `citations`
--
ALTER TABLE `citations`
  MODIFY `citation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `complaints`
--
ALTER TABLE `complaints`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `complaint_tracking`
--
ALTER TABLE `complaint_tracking`
  MODIFY `tracking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `compliance_alerts`
--
ALTER TABLE `compliance_alerts`
  MODIFY `alert_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `conciliation_reports`
--
ALTER TABLE `conciliation_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `contracts`
--
ALTER TABLE `contracts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `contract_business_categories`
--
ALTER TABLE `contract_business_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `contract_locations`
--
ALTER TABLE `contract_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `contract_payments`
--
ALTER TABLE `contract_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del pago', AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de la tabla `contract_payment_installments`
--
ALTER TABLE `contract_payment_installments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del abono';

--
-- AUTO_INCREMENT de la tabla `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
-- AUTO_INCREMENT de la tabla `euro_rates`
--
ALTER TABLE `euro_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `external_business_categories`
--
ALTER TABLE `external_business_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT de la tabla `fee_payments`
--
ALTER TABLE `fee_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `fine_payments`
--
ALTER TABLE `fine_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `fiscal_year`
--
ALTER TABLE `fiscal_year`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `infractions`
--
ALTER TABLE `infractions`
  MODIFY `infraction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `infraction_types`
--
ALTER TABLE `infraction_types`
  MODIFY `infraction_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de la tabla `inspection_reports`
--
ALTER TABLE `inspection_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `inspectors`
--
ALTER TABLE `inspectors`
  MODIFY `inspector_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `internal_business_categories`
--
ALTER TABLE `internal_business_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ordinance_articles`
--
ALTER TABLE `ordinance_articles`
  MODIFY `article_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del método de pago', AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `permissions`
--
ALTER TABLE `permissions`
  MODIFY `permission_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sanctions`
--
ALTER TABLE `sanctions`
  MODIFY `sanction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `sanction_types`
--
ALTER TABLE `sanction_types`
  MODIFY `sanction_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `scheduled_inspections`
--
ALTER TABLE `scheduled_inspections`
  MODIFY `inspection_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `sectors`
--
ALTER TABLE `sectors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `staff_department_history`
--
ALTER TABLE `staff_department_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `user_departments`
--
ALTER TABLE `user_departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `vacations`
--
ALTER TABLE `vacations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `zones`
--
ALTER TABLE `zones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- Filtros para la tabla `infractions`
--
ALTER TABLE `infractions`
  ADD CONSTRAINT `fk_infractions_awardees` FOREIGN KEY (`awardee_id`) REFERENCES `awardees` (`id`),
  ADD CONSTRAINT `fk_infractions_infraction_types` FOREIGN KEY (`infraction_type_id`) REFERENCES `infraction_types` (`infraction_type_id`),
  ADD CONSTRAINT `fk_infractions_market_stalls` FOREIGN KEY (`stall_id`) REFERENCES `market_stalls` (`id`);

--
-- Filtros para la tabla `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `market_stalls`
--
ALTER TABLE `market_stalls`
  ADD CONSTRAINT `market_stalls_ibfk_1` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`);

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
-- Filtros para la tabla `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_role_permissions_permissions` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_role_permissions_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `user_departments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_departments_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `vacations`
--
ALTER TABLE `vacations`
  ADD CONSTRAINT `vacations_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
