-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-08-2025 a las 02:34:23
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
-- Estructura de tabla para la tabla `adjudicatories`
--

CREATE TABLE `adjudicatories` (
  `id_adjudicatory` int(11) NOT NULL,
  `document_type` enum('cedula','riff') NOT NULL,
  `document_number` varchar(30) NOT NULL,
  `full_name_or_company_name` varchar(200) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `is_juridical_person` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `adjudicatories`
--

INSERT INTO `adjudicatories` (`id_adjudicatory`, `document_type`, `document_number`, `full_name_or_company_name`, `phone`, `email`, `is_juridical_person`) VALUES
(1, 'cedula', '12345678', 'Juan Pérez', '555-1234', 'juan.perez@example.com', 0),
(2, 'riff', 'J-20123456-7', 'Comercializadora S.A.', '555-5678', 'info@comercializadora.com', 1),
(3, 'cedula', '87654321', 'María García', '555-9012', 'maria.garcia@example.com', 0);

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registra todas las acciones importantes realizadas por los usuarios en el sistema';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `complaints`
--

CREATE TABLE `complaints` (
  `complaint_id` int(11) NOT NULL,
  `complaint_timestamp` timestamp NULL DEFAULT current_timestamp(),
  `client_user_id` int(11) DEFAULT NULL,
  `client_name` varchar(150) DEFAULT NULL,
  `client_phone` varchar(50) DEFAULT NULL,
  `client_email` varchar(100) DEFAULT NULL,
  `complaint_description` text NOT NULL,
  `position_id` int(11) DEFAULT NULL,
  `contractor_id` int(11) DEFAULT NULL,
  `complaint_type` varchar(100) NOT NULL,
  `complaint_status` varchar(50) NOT NULL DEFAULT 'Received',
  `complaint_priority` varchar(50) NOT NULL DEFAULT 'Medium',
  `internal_observations` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `complaints`
--

INSERT INTO `complaints` (`complaint_id`, `complaint_timestamp`, `client_user_id`, `client_name`, `client_phone`, `client_email`, `complaint_description`, `position_id`, `contractor_id`, `complaint_type`, `complaint_status`, `complaint_priority`, `internal_observations`) VALUES
(1, '2025-08-04 14:21:14', 8, 'Pedro Berti', '04264804748', 'pedro@gmail.com', 'sdfasdfdfadfda sadasd', 1, NULL, 'Suggestion', 'Received', 'Low', 'N/A'),
(2, '2025-08-04 14:25:22', 8, 'Jesus miguel', '04264804748', 'jesusmiguel@gmail.com', 'dsdsdsd dsdsds sdsdsd sdsdsds ', 2, NULL, 'Claim', 'Received', 'Medium', 'ddsdsd sdsdsd sdsds '),
(3, '2025-08-04 14:29:53', 8, 'Martha Figuera', '04264804748', 'martha@gmail.com', 'ddsds dsds sdsd', 4, NULL, 'Question', 'Received', 'High', 'dsdsd dsd'),
(4, '2025-08-04 14:35:11', 8, 'pepe', '04264804748', 'pepe@gmail.com', 'ddsd', 1, NULL, 'Suggestion', 'Received', 'Medium', 'dsds');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `shift_type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `manager_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Almacena los departamentos de la organización';

--
-- Volcado de datos para la tabla `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `shift_type`, `created_at`, `manager_id`) VALUES
(1, 'Liquidacion', 'Departamento de liquidación de nóminas', 'Matutino', '2025-08-03 16:24:43', 1),
(2, 'Cobranza', 'Departamento de gestión de cobros', 'Vespertino', '2025-08-03 16:24:43', 2),
(3, 'Fiscalizacion', 'Departamento de control fiscal', 'Mixto', '2025-08-03 16:24:43', 10),
(4, 'Recursos Humanos', 'Gestión del personal y talento humano', 'Administrativo', '2025-08-03 16:24:43', 4);

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
-- Estructura de tabla para la tabla `evidence`
--

CREATE TABLE `evidence` (
  `id_evidence` int(11) NOT NULL,
  `id_infraction` int(11) NOT NULL,
  `evidence_type` enum('image','video','testimony') NOT NULL,
  `content_url` varchar(255) DEFAULT NULL,
  `testimony_text` text DEFAULT NULL,
  `upload_datetime` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `infractions`
--

CREATE TABLE `infractions` (
  `id_infraction` int(11) NOT NULL,
  `id_adjudicatory` int(11) NOT NULL,
  `id_stall` int(11) DEFAULT NULL,
  `infraction_datetime` datetime NOT NULL DEFAULT current_timestamp(),
  `id_infraction_type` int(11) NOT NULL,
  `infraction_description` text NOT NULL,
  `infraction_status` varchar(50) NOT NULL DEFAULT 'Reported',
  `inspector_observations` text DEFAULT NULL,
  `proof` varchar(200) DEFAULT NULL,
  `status_logical` varchar(50) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `infractions`
--

INSERT INTO `infractions` (`id_infraction`, `id_adjudicatory`, `id_stall`, `infraction_datetime`, `id_infraction_type`, `infraction_description`, `infraction_status`, `inspector_observations`, `proof`, `status_logical`) VALUES
(1, 2, 1, '2025-08-04 20:43:52', 6, 'ssdsds sdsd ssdsds sdsdsds', 'Reported', 'dsdsd', NULL, 'active'),
(2, 1, 2, '2025-08-04 20:52:09', 6, 'xvxcvxc xc222222222222222222222222', 'Reported', 'vxvxcv000000000000000000000', NULL, 'active'),
(3, 1, 2, '2025-08-04 21:31:46', 33, 'bfdbbvcb  fffffffffffffffffffffffffffffffffffff', 'Reported', 'vcvcvcvcvcvcvcvcvcvcvc', NULL, 'active');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `infraction_types`
--

CREATE TABLE `infraction_types` (
  `id_infraction_type` int(11) NOT NULL,
  `infraction_type_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `violated_article` varchar(50) DEFAULT NULL,
  `base_fine` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `infraction_types`
--

INSERT INTO `infraction_types` (`id_infraction_type`, `infraction_type_name`, `description`, `violated_article`, `base_fine`) VALUES
(1, 'Limpieza Insuficiente', 'No efectuar la limpieza del área asignada al expendedor.', 'ARTÍCULO 107.1', 8.30),
(2, 'Residuos Mal Gestionados', 'Abandonar envases y mercancías en recintos comunes, incumpliendo las normas o directrices establecidas.', 'ARTÍCULO 107.2', 8.30),
(3, 'Documentos no Visibles', 'No mantener debidamente plastificados y en un sitio visible en el puesto o local, los documentos de identificación, permisos de funcionamiento, permisos sanitarios, certificados de salud, etc.', 'ARTÍCULO 107.3', 8.30),
(4, 'Indumentaria Inadecuada', 'No utilizar la indumentaria establecida.', 'ARTÍCULO 107.4', 8.30),
(5, 'Productos Desprotegidos', 'No estar provisto de los dispositivos necesarios para proteger los productos de cualquier alteración o contaminación.', 'ARTÍCULO 107.5', 8.30),
(6, 'Almacenamiento Inadecuado', 'Hacinar o depositar en el suelo los productos destinados a la alimentación.', 'ARTÍCULO 107.6', 8.30),
(7, 'Cierre sin Justificación (Leve)', 'Cerrar el puesto o local por un período comprendido entre tres (3) y ocho (8) días en el período de un año, salvo causa justificada.', 'ARTÍCULO 107.7', 8.30),
(8, 'Ofensas Verbales', 'Ofender de palabra a las autoridades municipales, a los demás expendedores, a los empleados a cargo del expendedor o al público usuario de los mercados.', 'ARTÍCULO 107.8', 8.30),
(9, 'Permanencia Fuera de Horario', 'Permanecer en las instalaciones de los mercados fuera del horario establecido.', 'ARTÍCULO 107.9', 8.30),
(10, 'Consumo en Instalaciones', 'Fumar y beber licores en las instalaciones del mercado.', 'ARTÍCULO 107.10', 8.30),
(11, 'Altercados Públicos', 'Los altercados que produzcan escándalos dentro del mercado o de sus inmediaciones.', 'ARTÍCULO 107.11', 8.30),
(12, 'Incumplimiento Normativa Sanitaria', 'Incumplir la normativa vigente en materia sanitaria.', 'ARTÍCULO 108.1', 13.90),
(13, 'Venta de Productos Deteriorados', 'Vender alimentos y productos en estado de descomposición, deterioro u obsolescencia.', 'ARTÍCULO 108.2', 13.90),
(14, 'Venta de Rubro no Autorizado', 'Vender artículos o mercancías de especie distinta a la autorizada.', 'ARTÍCULO 108.3', 13.90),
(15, 'Ocupación de Áreas no Asignadas', 'Colocar vendedores o agentes en áreas diferentes a las asignadas.', 'ARTÍCULO 108.4', 13.90),
(16, 'Incumplimiento Horario Operativo', 'Incumplir los horarios establecidos para las actividades de carga, descarga, recogida y almacenaje de los productos, apertura y cierre del mercado y limpieza del puesto o local.', 'ARTÍCULO 108.5', 13.90),
(17, 'Negativa a Exhibir Documentación', 'Negarse a exhibir cualquier documentación relacionada con el expendedor o el negocio cuando sean exigidas por los fiscales de mercados del Municipio.', 'ARTÍCULO 108.6', 13.90),
(18, 'Agresión Física', 'Agraviar físicamente a las autoridades municipales, a los demás expendedores, a los empleados a cargo del expendedor o al público usuario de los mercados.', 'ARTÍCULO 108.7', 13.90),
(19, 'Daños por Negligencia', 'Causar daños en las instalaciones del mercado por negligencia, imprudencia o impericia.', 'ARTÍCULO 108.8', 13.90),
(20, 'Uso de Altavoces', 'Utilizar altavoces o similares para ofrecer sus mercancías, productos o servicios, así como equipos de sonido que registren niveles que afecten la tranquilidad del mercado.', 'ARTÍCULO 108.9', 13.90),
(21, 'Modificaciones sin Autorización', 'Realizar, sin autorización escrita, cualquier modificación estructural en el área asignada.', 'ARTÍCULO 108.10', 13.90),
(22, 'Uso Indebido de Bienes Comunes', 'Usar indebidamente o sin autorización bienes o servicios comunes.', 'ARTÍCULO 108.11', 13.90),
(23, 'Venta Ambulante', 'Proporcionar mercancía para la venta ambulante en las instalaciones del mercado.', 'ARTÍCULO 108.12', 13.90),
(24, 'Cierre sin Justificación (Moderado)', 'Cerrar el puesto o local por un período comprendido entre nueve (9) y quince (15) días, salvo causa justificada.', 'ARTÍCULO 108.13', 13.90),
(25, 'Tercero a Cargo sin Autorización', 'Atención del puesto o local por un tercero, sin la previa información y autorización del órgano o autoridad de administración de mercados.', 'ARTÍCULO 108.14', 13.90),
(26, 'Obstrucción del Paso', 'Transportar y colocar artículos u objetos en puertas, mostradores, pisos y/o aceras que impidan o interfieran el libre tránsito.', 'ARTÍCULO 108.15', 13.90),
(27, 'Consumo de Sustancias Prohibidas', 'Consumir bebidas alcohólicas o sustancias estupefacientes en las instalaciones del mercado.', 'ARTÍCULO 108.16', 13.90),
(28, 'Pesas y Medidas Incorrectas', 'No utilizar pesas y medidas, debidamente constatadas y mantenerlas visibles al público.', 'ARTÍCULO 108.17', 13.90),
(29, 'Perturbación Grave', 'Provocar perturbaciones en el funcionamiento normal de los servicios, impidiendo la realización de ventas o enfrentando gravemente a los usuarios entre sí, o con el personal del mercado.', 'ARTÍCULO 109.1', 27.89),
(30, 'Falta de Pago de Tasas', 'Incumplimiento del pago de la tasa durante un período de un año o más.', 'ARTÍCULO 109.2', 27.89),
(31, 'Incumplimiento de Sanción Leve', 'Incumplir la sanción como consecuencia de alguna infracción leve.', 'ARTÍCULO 109.3', 27.89),
(32, 'Daños Graves', 'Ocasionar daños graves a las instalaciones del mercado.', 'ARTÍCULO 109.4', 27.89),
(33, 'Cierre sin Justificación (Grave)', 'Cerrar el puesto o local durante un período de más de treinta (30) días en el período de un año, salvo causa justificada.', 'ARTÍCULO 109.5', 27.89),
(34, 'Transferencia Ilegal de Puesto', 'Traspasar, ceder o transferir total o parcialmente el local o puesto que haya sido adjudicado o arrendado de manera unilateral.', 'ARTÍCULO 109.6', 27.89),
(35, 'Venta de Sustancias Prohibidas', 'Expender bebidas alcohólicas o cualquier sustancia prohibida.', 'ARTÍCULO 109.7', 27.89),
(36, 'Falta de Pago de Mensualidades', 'La falta de pago de tres (3) o más mensualidades en el plazo de un (1) año.', 'ARTÍCULO 113.1', NULL),
(37, 'No Restitución de Área Modificada', 'La no restitución del área asignada al estado en que se encontraba, dentro de los treinta (30) días siguientes a la notificación de la sanción.', 'ARTÍCULO 113.2', NULL),
(38, 'Reincidencia en Infracciones Graves', 'Incurrir en dos (2) infracciones graves en el transcurso de un (1) año.', 'ARTÍCULO 113.3', NULL),
(39, 'Falta de Pago de Impuestos Municipales', 'Incumplimiento del pago puntual de los servicios públicos y los impuestos municipales.', 'ARTÍCULO 26.11', NULL);

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
(3, 'Analista'),
(4, 'Asistente'),
(5, 'Desarrollador'),
(1, 'Director'),
(2, 'Gerente');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Gestiona las solicitudes de permisos del personal';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `market_stalls`
--

CREATE TABLE `market_stalls` (
  `id_stall` int(11) NOT NULL,
  `id_market` int(11) NOT NULL,
  `stall_code` varchar(20) NOT NULL,
  `stall_type` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `market_stalls`
--

INSERT INTO `market_stalls` (`id_stall`, `id_market`, `stall_code`, `stall_type`, `status`) VALUES
(1, 1, 'M1-A101', 'Permanent', 'Occupied'),
(2, 1, 'M1-A102', 'Permanent', 'Available'),
(3, 2, 'M2-B205', 'Temporary', 'Occupied'),
(4, 1, 'M1-A103', 'Permanent', 'Available');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tabla principal que almacena toda la información del personal';

--
-- Volcado de datos para la tabla `staff`
--

INSERT INTO `staff` (`id`, `academic_degree_id`, `academic_specialization_id`, `job_position_id`, `department_id`, `division_id`, `id_number`, `first_name`, `middle_name`, `last_name`, `second_last_name`, `birth_date`, `gender`, `hire_date`, `termination_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 3, 2, 1, 1, NULL, 'V12345678', 'María', NULL, 'González', NULL, '1980-05-15', 1, '2015-03-10', NULL, 'active', '2025-08-03 16:24:43', '2025-08-03 16:24:43'),
(2, 3, 1, 1, 2, NULL, 'V23456789', 'Carlos', NULL, 'Pérez', NULL, '1978-11-22', 0, '2016-07-20', NULL, 'active', '2025-08-03 16:24:43', '2025-08-03 16:24:43'),
(3, 4, 3, 1, 3, NULL, 'V34567890', 'Ana', NULL, 'Rodríguez', NULL, '1982-08-30', 1, '2017-01-15', NULL, 'active', '2025-08-03 16:24:43', '2025-08-03 16:24:43'),
(4, 4, 5, 1, 4, NULL, 'V45678901', 'Luis', NULL, 'Martínez', NULL, '1975-04-18', 0, '2014-09-05', NULL, 'active', '2025-08-03 16:24:43', '2025-08-03 16:24:43'),
(5, 3, 4, 5, 1, NULL, 'V56789012', 'Pedro', NULL, 'López', NULL, '1990-07-25', 0, '2019-05-10', NULL, 'active', '2025-08-03 16:24:43', '2025-08-03 16:24:43'),
(6, 3, 4, 5, 2, NULL, 'V67890123', 'Sofía', NULL, 'Hernández', NULL, '1992-03-18', 1, '2020-02-15', NULL, 'active', '2025-08-03 16:24:43', '2025-08-03 16:24:43'),
(7, 3, 4, 5, 3, NULL, 'V78901234', 'Jorge', NULL, 'Díaz', NULL, '1988-11-30', 0, '2018-08-22', NULL, 'active', '2025-08-03 16:24:43', '2025-08-03 16:24:43'),
(8, 3, 4, 5, 4, NULL, 'V89012345', 'Laura', NULL, 'Torres', NULL, '1991-09-05', 1, '2021-01-10', NULL, 'active', '2025-08-03 16:24:43', '2025-08-03 16:24:43'),
(10, 2, 4, 5, 3, NULL, 'V28129366', 'Daniel', NULL, 'Alfonsi', NULL, NULL, 0, '0000-00-00', NULL, 'active', '2025-08-03 16:45:50', '2025-08-03 16:47:31');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Almacena las credenciales de acceso al sistema';

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `staff_id`, `username`, `password_hash`, `email`, `last_login`, `password_reset_token`, `password_reset_expires`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'mgonzalez', '$2y$10$N9qo8uLOickgx2ZMRZoMy.MrYV7WYhS3HZ8L3ZqGJQyJ1J2vJQ9X.', 'maria.gonzalez@empresa.com', NULL, NULL, NULL, 'active', '2025-08-03 16:24:43', '2025-08-03 16:24:43'),
(2, 2, 'cperez', '$2y$10$N9qo8uLOickgx2ZMRZoMy.MrYV7WYhS3HZ8L3ZqGJQyJ1J2vJQ9X.', 'carlos.perez@empresa.com', NULL, NULL, NULL, 'active', '2025-08-03 16:24:43', '2025-08-03 16:24:43'),
(3, 3, 'arodriguez', '$2y$10$N9qo8uLOickgx2ZMRZoMy.MrYV7WYhS3HZ8L3ZqGJQyJ1J2vJQ9X.', 'ana.rodriguez@empresa.com', NULL, NULL, NULL, 'active', '2025-08-03 16:24:43', '2025-08-03 16:24:43'),
(4, 4, 'lmartinez', '$2y$10$N9qo8uLOickgx2ZMRZoMy.MrYV7WYhS3HZ8L3ZqGJQyJ1J2vJQ9X.', 'luis.martinez@empresa.com', NULL, NULL, NULL, 'active', '2025-08-03 16:24:43', '2025-08-03 16:24:43'),
(5, NULL, 'devliq', '$2y$10$N9qo8uLOickgx2ZMRZoMy.MrYV7WYhS3HZ8L3ZqGJQyJ1J2vJQ9X.', 'devliquidacion@empresa.com', NULL, NULL, NULL, 'active', '2025-08-03 16:24:43', '2025-08-03 16:24:43'),
(6, NULL, 'devcob', '$2y$10$N9qo8uLOickgx2ZMRZoMy.MrYV7WYhS3HZ8L3ZqGJQyJ1J2vJQ9X.', 'devcobranza@empresa.com', NULL, NULL, NULL, 'active', '2025-08-03 16:24:43', '2025-08-03 16:24:43'),
(7, NULL, 'devrrhh', '$2y$10$N9qo8uLOickgx2ZMRZoMy.MrYV7WYhS3HZ8L3ZqGJQyJ1J2vJQ9X.', 'devrrhh@empresa.com', NULL, NULL, NULL, 'active', '2025-08-03 16:24:43', '2025-08-03 16:24:43'),
(8, 10, 'devfisc', '$2y$12$pCS92M3Eolg0iYVa.jBKM.EJCDZh9Hso/FRxCWnzeHIYvlzxXSDxi', 'devfiscalizacion@empresa.com', '2025-08-04 21:27:32', '930171', '2025-08-04 19:50:55', 'active', '2025-08-03 16:24:43', '2025-08-04 21:27:32');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Controla qué usuarios tienen acceso a qué departamentos';

--
-- Volcado de datos para la tabla `user_departments`
--

INSERT INTO `user_departments` (`id`, `user_id`, `department_id`, `status`, `created_at`) VALUES
(1, 1, 1, 'active', '2025-08-03 16:24:43'),
(2, 2, 2, 'active', '2025-08-03 16:24:43'),
(3, 3, 3, 'active', '2025-08-03 16:24:43'),
(4, 4, 4, 'active', '2025-08-03 16:24:43'),
(5, 5, 1, 'active', '2025-08-03 16:24:43'),
(6, 6, 2, 'active', '2025-08-03 16:24:43'),
(7, 7, 3, 'active', '2025-08-03 16:24:43'),
(8, 8, 3, 'active', '2025-08-03 16:24:43');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Administra las solicitudes y estados de vacaciones del personal';

-- --------------------------------------------------------

--
-- Estructura para la vista `staff_complete_info`
--
DROP TABLE IF EXISTS `staff_complete_info`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `staff_complete_info`  AS SELECT `s`.`id` AS `id`, `s`.`id_number` AS `id_number`, concat(`s`.`first_name`,' ',ifnull(`s`.`middle_name`,''),' ',`s`.`last_name`,' ',ifnull(`s`.`second_last_name`,'')) AS `full_name`, `s`.`first_name` AS `first_name`, `s`.`middle_name` AS `middle_name`, `s`.`last_name` AS `last_name`, `s`.`second_last_name` AS `second_last_name`, `s`.`birth_date` AS `birth_date`, CASE WHEN `s`.`gender` = 1 THEN 'Femenino' ELSE 'Masculino' END AS `gender_text`, `s`.`hire_date` AS `hire_date`, `s`.`termination_date` AS `termination_date`, `s`.`status` AS `status`, `d`.`name` AS `department_name`, `dv`.`name` AS `division_name`, `jp`.`name` AS `job_position_name`, `ad`.`name` AS `academic_degree_name`, `asp`.`name` AS `academic_specialization_name`, concat(`m`.`first_name`,' ',`m`.`last_name`) AS `manager_name` FROM ((((((`staff` `s` left join `departments` `d` on(`s`.`department_id` = `d`.`id`)) left join `divisions` `dv` on(`s`.`division_id` = `dv`.`id`)) left join `job_positions` `jp` on(`s`.`job_position_id` = `jp`.`id`)) left join `academic_degrees` `ad` on(`s`.`academic_degree_id` = `ad`.`id`)) left join `academic_specializations` `asp` on(`s`.`academic_specialization_id` = `asp`.`id`)) left join `staff` `m` on(`d`.`manager_id` = `m`.`id`)) ;

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
-- Indices de la tabla `adjudicatories`
--
ALTER TABLE `adjudicatories`
  ADD PRIMARY KEY (`id_adjudicatory`),
  ADD UNIQUE KEY `document_number` (`document_number`);

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
-- Indices de la tabla `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`complaint_id`),
  ADD KEY `idx_complaint_client_user_id` (`client_user_id`),
  ADD KEY `idx_complaint_position_id` (`position_id`);

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
-- Indices de la tabla `evidence`
--
ALTER TABLE `evidence`
  ADD PRIMARY KEY (`id_evidence`),
  ADD KEY `fk_evidence_infractions` (`id_infraction`);

--
-- Indices de la tabla `infractions`
--
ALTER TABLE `infractions`
  ADD PRIMARY KEY (`id_infraction`),
  ADD KEY `fk_infractions_adjudicatories` (`id_adjudicatory`),
  ADD KEY `fk_infractions_market_stalls` (`id_stall`),
  ADD KEY `fk_infractions_infraction_types` (`id_infraction_type`);

--
-- Indices de la tabla `infraction_types`
--
ALTER TABLE `infraction_types`
  ADD PRIMARY KEY (`id_infraction_type`),
  ADD UNIQUE KEY `infraction_type_name` (`infraction_type_name`);

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
  ADD PRIMARY KEY (`id_stall`),
  ADD UNIQUE KEY `stall_code` (`stall_code`);

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
-- AUTO_INCREMENT de la tabla `adjudicatories`
--
ALTER TABLE `adjudicatories`
  MODIFY `id_adjudicatory` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- AUTO_INCREMENT de la tabla `complaints`
--
ALTER TABLE `complaints`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
-- AUTO_INCREMENT de la tabla `evidence`
--
ALTER TABLE `evidence`
  MODIFY `id_evidence` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `infractions`
--
ALTER TABLE `infractions`
  MODIFY `id_infraction` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `infraction_types`
--
ALTER TABLE `infraction_types`
  MODIFY `id_infraction_type` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `job_positions`
--
ALTER TABLE `job_positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `market_stalls`
--
ALTER TABLE `market_stalls`
  MODIFY `id_stall` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `staff_department_history`
--
ALTER TABLE `staff_department_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `user_departments`
--
ALTER TABLE `user_departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `vacations`
--
ALTER TABLE `vacations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

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
-- Filtros para la tabla `evidence`
--
ALTER TABLE `evidence`
  ADD CONSTRAINT `fk_evidence_infractions` FOREIGN KEY (`id_infraction`) REFERENCES `infractions` (`id_infraction`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Filtros para la tabla `infractions`
--
ALTER TABLE `infractions`
  ADD CONSTRAINT `fk_infractions_adjudicatories` FOREIGN KEY (`id_adjudicatory`) REFERENCES `adjudicatories` (`id_adjudicatory`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_infractions_infraction_types` FOREIGN KEY (`id_infraction_type`) REFERENCES `infraction_types` (`id_infraction_type`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_infractions_market_stalls` FOREIGN KEY (`id_stall`) REFERENCES `market_stalls` (`id_stall`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
