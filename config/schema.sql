-- --------------------------------------------------------
-- Estructura de la base de datos
-- --------------------------------------------------------

-- Tabla de departamentos organizacionales
CREATE TABLE departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    shift_type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    manager_id INT NULL,
    FOREIGN KEY (manager_id) REFERENCES staff(id)
) COMMENT 'Almacena los departamentos de la organización';

-- Tabla para divisiones dentro de departamentos
CREATE TABLE divisions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    department_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    FOREIGN KEY (department_id) REFERENCES departments(id)
) COMMENT 'Contiene las divisiones que pertenecen a cada departamento';

-- Tabla catálogo para grados académicos
CREATE TABLE academic_degrees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL
) COMMENT 'Catálogo de grados académicos (licenciatura, maestría, etc.)';

-- Tabla catálogo para especializaciones académicas
CREATE TABLE academic_specializations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL
) COMMENT 'Catálogo de especializaciones o menciones académicas';

-- Tabla catálogo para puestos de trabajo
CREATE TABLE job_positions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL
) COMMENT 'Catálogo de puestos de trabajo en la organización';

-- Tabla principal del personal/empleados
CREATE TABLE staff (
    id INT PRIMARY KEY AUTO_INCREMENT,
    academic_degree_id INT,
    academic_specialization_id INT,
    job_position_id INT NOT NULL,
    department_id INT NOT NULL,
    division_id INT,
    id_number VARCHAR(20) UNIQUE NOT NULL COMMENT 'Número de cédula o identificación',
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    last_name VARCHAR(100) NOT NULL,
    second_last_name VARCHAR(100),
    birth_date DATE,
    gender BOOLEAN COMMENT 'TRUE para Femenino, FALSE para Masculino',
    hire_date DATE NOT NULL,
    termination_date DATE,
    status ENUM('active', 'inactive', 'vacation', 'leave', 'suspended') DEFAULT 'active',
    FOREIGN KEY (academic_degree_id) REFERENCES academic_degrees(id),
    FOREIGN KEY (academic_specialization_id) REFERENCES academic_specializations(id),
    FOREIGN KEY (job_position_id) REFERENCES job_positions(id),
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (division_id) REFERENCES divisions(id)
) COMMENT 'Tabla principal que almacena toda la información del personal';

-- Tabla para histórico de cambios de departamento
CREATE TABLE staff_department_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    staff_id INT NOT NULL,
    department_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    reason TEXT,
    FOREIGN KEY (staff_id) REFERENCES staff(id),
    FOREIGN KEY (department_id) REFERENCES departments(id)
) COMMENT 'Registra los cambios de departamento del personal a lo largo del tiempo';

-- Tabla de usuarios del sistema (modificada con nuevos campos)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    staff_id INT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    last_login DATETIME,
    password_reset_token VARCHAR(255),
    password_reset_expires DATETIME,
    status ENUM('active', 'inactive') DEFAULT 'inactive',
    FOREIGN KEY (staff_id) REFERENCES staff(id)
) COMMENT 'Almacena las credenciales de acceso al sistema';

-- Tabla para relación usuarios-departamentos
CREATE TABLE user_departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    department_id INT NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'inactive',
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (department_id) REFERENCES departments(id)
) COMMENT 'Controla qué usuarios tienen acceso a qué departamentos';

-- Tabla de horarios por departamento
CREATE TABLE department_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    department_id INT NOT NULL,
    day VARCHAR(15) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    type VARCHAR(50),
    FOREIGN KEY (department_id) REFERENCES departments(id)
) COMMENT 'Define los horarios de trabajo por departamento';

-- Tabla de registros de asistencia
CREATE TABLE attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    staff_id INT NOT NULL,
    date DATE NOT NULL,
    check_in TIME,
    check_out TIME,
    is_special BOOLEAN DEFAULT FALSE COMMENT 'Para marcar asistencias especiales o excepcionales',
    FOREIGN KEY (staff_id) REFERENCES staff(id)
) COMMENT 'Registra las asistencias del personal';

-- Tabla de solicitudes de permisos
CREATE TABLE leave_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    staff_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    reason TEXT,
    document_url VARCHAR(255),
    request_date DATE,
    approval_date DATE,
    FOREIGN KEY (staff_id) REFERENCES staff(id)
) COMMENT 'Gestiona las solicitudes de permisos del personal';

-- Tabla para gestión de vacaciones
CREATE TABLE vacations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    staff_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('requested', 'approved', 'rejected') DEFAULT 'requested',
    FOREIGN KEY (staff_id) REFERENCES staff(id)
) COMMENT 'Administra las solicitudes y estados de vacaciones del personal';

-- Tabla de bitácora/auditoría
CREATE TABLE audit_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL COMMENT 'Tipo de acción realizada (login, insert, update, delete)',
    table_affected VARCHAR(50) NOT NULL,
    record_id INT COMMENT 'ID del registro afectado',
    old_values JSON COMMENT 'Valores anteriores en formato JSON',
    new_values JSON COMMENT 'Valores nuevos en formato JSON',
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) COMMENT 'Registra todas las acciones importantes realizadas por los usuarios en el sistema';

-- --------------------------------------------------------
-- Datos de ejemplo para la base de datos
-- --------------------------------------------------------

-- Departamentos
INSERT INTO departments (id, name, description, shift_type) VALUES
(1, 'Liquidacion', 'Departamento de liquidación de nóminas', 'Matutino'),
(2, 'Cobranza', 'Departamento de gestión de cobros', 'Vespertino'),
(3, 'Fiscalizacion', 'Departamento de control fiscal', 'Mixto'),
(4, 'Recursos Humanos', 'Gestión del personal y talento humano', 'Administrativo');

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

-- Actualizar departamentos con jefes
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
(null,'devliq', '$2y$10$N9qo8uLOickgx2ZMRZoMy.MrYV7WYhS3HZ8L3ZqGJQyJ1J2vJQ9X.', 'devliquidacion@empresa.com', 'active'),
(null,'devcob', '$2y$10$N9qo8uLOickgx2ZMRZoMy.MrYV7WYhS3HZ8L3ZqGJQyJ1J2vJQ9X.', 'devcobranza@empresa.com', 'active'),
(null,'devrrhh', '$2y$10$N9qo8uLOickgx2ZMRZoMy.MrYV7WYhS3HZ8L3ZqGJQyJ1J2vJQ9X.', 'devrrhh@empresa.com', 'active'),
(null,'devfisc', '$2y$10$N9qo8uLOickgx2ZMRZoMy.MrYV7WYhS3HZ8L3ZqGJQyJ1J2vJQ9X.', 'devfiscalizacion@empresa.com', 'active');

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
