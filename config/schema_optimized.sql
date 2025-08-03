-- --------------------------------------------------------
-- Estructura OPTIMIZADA de la base de datos
-- --------------------------------------------------------

-- Configuración inicial para mejorar rendimiento
SET FOREIGN_KEY_CHECKS = 0;
SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

-- --------------------------------------------------------
-- PASO 1: Crear tablas catálogo (sin dependencias)
-- --------------------------------------------------------

-- Tabla catálogo para grados académicos
CREATE TABLE academic_degrees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    
    -- Índices para optimizar consultas
    INDEX idx_name (name)
) COMMENT 'Catálogo de grados académicos (licenciatura, maestría, etc.)';

-- Tabla catálogo para especializaciones académicas
CREATE TABLE academic_specializations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    
    -- Índices para optimizar consultas
    INDEX idx_name (name)
) COMMENT 'Catálogo de especializaciones o menciones académicas';

-- Tabla catálogo para puestos de trabajo
CREATE TABLE job_positions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    
    -- Índices para optimizar consultas
    INDEX idx_name (name)
) COMMENT 'Catálogo de puestos de trabajo en la organización';

-- --------------------------------------------------------
-- PASO 2: Crear tabla departments SIN foreign key problemática
-- --------------------------------------------------------

CREATE TABLE departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    shift_type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    manager_id INT NULL, -- SIN FOREIGN KEY por ahora
    
    -- Índices para optimizar consultas
    INDEX idx_name (name),
    INDEX idx_shift_type (shift_type),
    INDEX idx_manager_id (manager_id)
) COMMENT 'Almacena los departamentos de la organización';

-- --------------------------------------------------------
-- PASO 3: Crear tabla divisions
-- --------------------------------------------------------

CREATE TABLE divisions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    department_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    
    -- Índices para optimizar consultas
    INDEX idx_department_id (department_id),
    INDEX idx_name (name),
    
    -- Foreign key
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE ON UPDATE CASCADE
) COMMENT 'Contiene las divisiones que pertenecen a cada departamento';

-- --------------------------------------------------------
-- PASO 4: Crear tabla staff con todas sus foreign keys
-- --------------------------------------------------------

CREATE TABLE staff (
    id INT PRIMARY KEY AUTO_INCREMENT,
    academic_degree_id INT NULL,
    academic_specialization_id INT NULL,
    job_position_id INT NOT NULL,
    department_id INT NOT NULL,
    division_id INT NULL,
    id_number VARCHAR(20) UNIQUE NOT NULL COMMENT 'Número de cédula o identificación',
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) NULL,
    last_name VARCHAR(100) NOT NULL,
    second_last_name VARCHAR(100) NULL,
    birth_date DATE NULL,
    gender BOOLEAN NULL COMMENT 'TRUE para Femenino, FALSE para Masculino',
    hire_date DATE NOT NULL,
    termination_date DATE NULL,
    status ENUM('active', 'inactive', 'vacation', 'leave', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices para optimizar consultas
    INDEX idx_id_number (id_number),
    INDEX idx_department_id (department_id),
    INDEX idx_division_id (division_id),
    INDEX idx_status (status),
    INDEX idx_hire_date (hire_date),
    INDEX idx_full_name (first_name, last_name),
    INDEX idx_job_position (job_position_id),
    
    -- Foreign keys
    FOREIGN KEY (academic_degree_id) REFERENCES academic_degrees(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (academic_specialization_id) REFERENCES academic_specializations(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (job_position_id) REFERENCES job_positions(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (division_id) REFERENCES divisions(id) ON DELETE SET NULL ON UPDATE CASCADE
) COMMENT 'Tabla principal que almacena toda la información del personal';

-- --------------------------------------------------------
-- PASO 5: Agregar la foreign key problemática DESPUÉS de crear staff
-- --------------------------------------------------------

ALTER TABLE departments 
ADD CONSTRAINT fk_departments_manager 
FOREIGN KEY (manager_id) REFERENCES staff(id) ON DELETE SET NULL ON UPDATE CASCADE;

-- --------------------------------------------------------
-- PASO 6: Crear tablas restantes
-- --------------------------------------------------------

-- Tabla para histórico de cambios de departamento
CREATE TABLE staff_department_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    staff_id INT NOT NULL,
    department_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Índices para optimizar consultas
    INDEX idx_staff_id (staff_id),
    INDEX idx_department_id (department_id),
    INDEX idx_date_range (start_date, end_date),
    
    -- Foreign keys
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE ON UPDATE CASCADE
) COMMENT 'Registra los cambios de departamento del personal a lo largo del tiempo';

-- Tabla de usuarios del sistema (CORREGIDA)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    staff_id INT NULL, -- CAMPO CORREGIDO
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL, -- Agregado UNIQUE
    last_login DATETIME NULL,
    password_reset_token VARCHAR(255) NULL,
    password_reset_expires DATETIME NULL,
    status ENUM('active', 'inactive') DEFAULT 'inactive',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices para optimizar consultas
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_staff_id (staff_id),
    
    -- Foreign key
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE SET NULL ON UPDATE CASCADE
) COMMENT 'Almacena las credenciales de acceso al sistema';

-- Tabla para relación usuarios-departamentos
CREATE TABLE user_departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    department_id INT NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'inactive',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Índices para optimizar consultas
    INDEX idx_user_id (user_id),
    INDEX idx_department_id (department_id),
    INDEX idx_status (status),
    
    -- Índice compuesto para evitar duplicados
    UNIQUE KEY unique_user_department (user_id, department_id),
    
    -- Foreign keys
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE ON UPDATE CASCADE
) COMMENT 'Controla qué usuarios tienen acceso a qué departamentos';

-- Tabla de horarios por departamento
CREATE TABLE department_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    department_id INT NOT NULL,
    day VARCHAR(15) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    type VARCHAR(50) NULL,
    
    -- Índices para optimizar consultas
    INDEX idx_department_id (department_id),
    INDEX idx_day (day),
    INDEX idx_time_range (start_time, end_time),
    
    -- Foreign key
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE ON UPDATE CASCADE
) COMMENT 'Define los horarios de trabajo por departamento';

-- Tabla de registros de asistencia
CREATE TABLE attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    staff_id INT NOT NULL,
    date DATE NOT NULL,
    check_in TIME NULL,
    check_out TIME NULL,
    is_special BOOLEAN DEFAULT FALSE COMMENT 'Para marcar asistencias especiales o excepcionales',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Índices para optimizar consultas
    INDEX idx_staff_id (staff_id),
    INDEX idx_date (date),
    INDEX idx_staff_date (staff_id, date),
    
    -- Evitar registros duplicados por staff y fecha
    UNIQUE KEY unique_staff_date (staff_id, date),
    
    -- Foreign key
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE ON UPDATE CASCADE
) COMMENT 'Registra las asistencias del personal';

-- Tabla de solicitudes de permisos (CORREGIDA)
CREATE TABLE leave_requests (
    id INT PRIMARY KEY AUTO_INCREMENT, -- CAMPO AGREGADO
    staff_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    reason TEXT,
    document_url VARCHAR(255) NULL,
    request_date DATE NOT NULL,
    approval_date DATE NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending', -- CAMPO AGREGADO
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Índices para optimizar consultas
    INDEX idx_staff_id (staff_id),
    INDEX idx_request_date (request_date),
    INDEX idx_status (status),
    INDEX idx_type (type),
    
    -- Foreign key
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE ON UPDATE CASCADE
) COMMENT 'Gestiona las solicitudes de permisos del personal';

-- Tabla para gestión de vacaciones
CREATE TABLE vacations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    staff_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('requested', 'approved', 'rejected') DEFAULT 'requested',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Índices para optimizar consultas
    INDEX idx_staff_id (staff_id),
    INDEX idx_date_range (start_date, end_date),
    INDEX idx_status (status),
    
    -- Foreign key
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE ON UPDATE CASCADE
) COMMENT 'Administra las solicitudes y estados de vacaciones del personal';

-- Tabla de bitácora/auditoría
CREATE TABLE audit_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL COMMENT 'Tipo de acción realizada (login, insert, update, delete)',
    table_affected VARCHAR(50) NOT NULL,
    record_id INT NULL COMMENT 'ID del registro afectado',
    old_values JSON NULL COMMENT 'Valores anteriores en formato JSON',
    new_values JSON NULL COMMENT 'Valores nuevos en formato JSON',
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Índices para optimizar consultas
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_table_affected (table_affected),
    INDEX idx_created_at (created_at),
    INDEX idx_record_id (record_id),
    
    -- Foreign key
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
) COMMENT 'Registra todas las acciones importantes realizadas por los usuarios en el sistema';

-- --------------------------------------------------------
-- Datos de ejemplo para la base de datos
-- --------------------------------------------------------

-- Grados académicos
INSERT INTO academic_degrees (id, name) VALUES
(1, 'Bachiller'),
(2, 'Técnico Superior'),
(3, 'Licenciatura'),
(4, 'Maestría'),
(5, 'Doctorado');

-- Especializaciones académicas
INSERT INTO academic_specializations (id, name) VALUES
(1, 'Administración'),
(2, 'Contabilidad'),
(3, 'Derecho'),
(4, 'Informática'),
(5, 'Recursos Humanos');

-- Puestos de trabajo
INSERT INTO job_positions (id, name) VALUES
(1, 'Director'),
(2, 'Gerente'),
(3, 'Analista'),
(4, 'Asistente'),
(5, 'Desarrollador');

-- Departamentos (SIN manager_id por ahora)
INSERT INTO departments (id, name, description, shift_type) VALUES
(1, 'Liquidacion', 'Departamento de liquidación de nóminas', 'Matutino'),
(2, 'Cobranza', 'Departamento de gestión de cobros', 'Vespertino'),
(3, 'Fiscalizacion', 'Departamento de control fiscal', 'Mixto'),
(4, 'Recursos Humanos', 'Gestión del personal y talento humano', 'Administrativo');

-- Divisiones
INSERT INTO divisions (id, department_id, name, description) VALUES
(1, 1, 'Liquidación de Nóminas', 'División encargada del cálculo de salarios'),
(2, 1, 'Prestaciones Sociales', 'División de beneficios para empleados'),
(3, 2, 'Cobranza Interna', 'Gestión de cobros a empleados'),
(4, 2, 'Cobranza Externa', 'Gestión de cobros a clientes'),
(5, 3, 'Auditoría', 'División de revisiones fiscales'),
(6, 4, 'Reclutamiento', 'Selección de personal');

-- Personal (jefes de departamento)
INSERT INTO staff (id, academic_degree_id, academic_specialization_id, job_position_id, department_id, division_id, id_number, first_name, last_name, birth_date, gender, hire_date, status) VALUES
(1, 3, 2, 1, 1, NULL, 'V12345678', 'María', 'González', '1980-05-15', TRUE, '2015-03-10', 'active'),
(2, 3, 1, 1, 2, NULL, 'V23456789', 'Carlos', 'Pérez', '1978-11-22', FALSE, '2016-07-20', 'active'),
(3, 4, 3, 1, 3, NULL, 'V34567890', 'Ana', 'Rodríguez', '1982-08-30', TRUE, '2017-01-15', 'active'),
(4, 4, 5, 1, 4, NULL, 'V45678901', 'Luis', 'Martínez', '1975-04-18', FALSE, '2014-09-05', 'active');

-- AHORA podemos actualizar departments con los managers
UPDATE departments SET manager_id = 1 WHERE id = 1;
UPDATE departments SET manager_id = 2 WHERE id = 2;
UPDATE departments SET manager_id = 3 WHERE id = 3;
UPDATE departments SET manager_id = 4 WHERE id = 4;

-- Personal (desarrolladores)
INSERT INTO staff (id, academic_degree_id, academic_specialization_id, job_position_id, department_id, division_id, id_number, first_name, last_name, birth_date, gender, hire_date, status) VALUES
(5, 3, 4, 5, 1, NULL, 'V56789012', 'Pedro', 'López', '1990-07-25', FALSE, '2019-05-10', 'active'),
(6, 3, 4, 5, 2, NULL, 'V67890123', 'Sofía', 'Hernández', '1992-03-18', TRUE, '2020-02-15', 'active'),
(7, 3, 4, 5, 3, NULL, 'V78901234', 'Jorge', 'Díaz', '1988-11-30', FALSE, '2018-08-22', 'active'),
(8, 3, 4, 5, 4, NULL, 'V89012345', 'Laura', 'Torres', '1991-09-05', TRUE, '2021-01-10', 'active');

-- Usuarios (jefes de departamento)
INSERT INTO users (staff_id, username, password_hash, email, status) VALUES
(1, 'mgonzalez', '$2y$10$N9qo8uLOickgx2ZMRZoMy.MrYV7WYhS3HZ8L3ZqGJQyJ1J2vJQ9X.', 'maria.gonzalez@empresa.com', 'active'),
(2, 'cperez', '$2y$10$N9qo8uLOickgx2ZMRZoMy.MrYV7WYhS3HZ8L3ZqGJQyJ1J2vJQ9X.', 'carlos.perez@empresa.com', 'active'),
(3, 'arodriguez', '$2y$10$N9qo8uLOickgx2ZMRZoMy.MrYV7WYhS3HZ8L3ZqGJQyJ1J2vJQ9X.', 'ana.rodriguez@empresa.com', 'active'),
(4, 'lmartinez', '$2y$10$N9qo8uLOickgx2ZMRZoMy.MrYV7WYhS3HZ8L3ZqGJQyJ1J2vJQ9X.', 'luis.martinez@empresa.com', 'active');

-- Usuarios (desarrolladores)
INSERT INTO users (staff_id, username, password_hash, email, status) VALUES
(NULL, 'devliq', '$2y$10$N9qo8uLOickgx2ZMRZoMy.MrYV7WYhS3HZ8L3ZqGJQyJ1J2vJQ9X.', 'devliquidacion@empresa.com', 'active'),
(NULL, 'devcob', '$2y$10$N9qo8uLOickgx2ZMRZoMy.MrYV7WYhS3HZ8L3ZqGJQyJ1J2vJQ9X.', 'devcobranza@empresa.com', 'active'),
(NULL, 'devrrhh', '$2y$10$N9qo8uLOickgx2ZMRZoMy.MrYV7WYhS3HZ8L3ZqGJQyJ1J2vJQ9X.', 'devrrhh@empresa.com', 'active'),
(NULL, 'devfisc', '$2y$10$N9qo8uLOickgx2ZMRZoMy.MrYV7WYhS3HZ8L3ZqGJQyJ1J2vJQ9X.', 'devfiscalizacion@empresa.com', 'active');

-- Asignación de departamentos a usuarios
INSERT INTO user_departments (user_id, department_id, status) VALUES
(1, 1, 'active'),
(2, 2, 'active'),
(3, 3, 'active'),
(4, 4, 'active'),
(5, 1, 'active'),
(6, 2, 'active'),
(7, 3, 'active'),
(8, 4, 'active');

-- Restaurar configuración
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- OPTIMIZACIONES ADICIONALES
-- --------------------------------------------------------

-- Trigger para actualizar updated_at en staff
DELIMITER $$
CREATE TRIGGER staff_updated_at_trigger
    BEFORE UPDATE ON staff
    FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END$$
DELIMITER ;

-- Trigger para actualizar updated_at en users
DELIMITER $$
CREATE TRIGGER users_updated_at_trigger
    BEFORE UPDATE ON users
    FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END$$
DELIMITER ;

-- Vista optimizada para información completa del personal
CREATE VIEW staff_complete_info AS
SELECT 
    s.id,
    s.id_number,
    CONCAT(s.first_name, ' ', IFNULL(s.middle_name, ''), ' ', s.last_name, ' ', IFNULL(s.second_last_name, '')) AS full_name,
    s.first_name,
    s.middle_name,
    s.last_name,
    s.second_last_name,
    s.birth_date,
    CASE WHEN s.gender = TRUE THEN 'Femenino' ELSE 'Masculino' END AS gender_text,
    s.hire_date,
    s.termination_date,
    s.status,
    d.name AS department_name,
    dv.name AS division_name,
    jp.name AS job_position_name,
    ad.name AS academic_degree_name,
    asp.name AS academic_specialization_name,
    CONCAT(m.first_name, ' ', m.last_name) AS manager_name
FROM staff s
    LEFT JOIN departments d ON s.department_id = d.id
    LEFT JOIN divisions dv ON s.division_id = dv.id
    LEFT JOIN job_positions jp ON s.job_position_id = jp.id
    LEFT JOIN academic_degrees ad ON s.academic_degree_id = ad.id
    LEFT JOIN academic_specializations asp ON s.academic_specialization_id = asp.id
    LEFT JOIN staff m ON d.manager_id = m.id;

COMMIT;