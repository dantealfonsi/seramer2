-- ========================================================
-- SCRIPT DE MIGRACIÓN GRADUAL CORREGIDO - SERAMER2 OPTIMIZADO
-- ========================================================
-- IMPORTANTE: ¡HACER BACKUP COMPLETO ANTES DE EJECUTAR!
-- mysqldump -u usuario -p seramer2 > backup_seramer2_YYYY_MM_DD.sql

-- Configuración inicial
SET FOREIGN_KEY_CHECKS = 0;
SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

-- ========================================================
-- PASO 1: CREAR PROCEDIMIENTO PARA AGREGAR COLUMNAS SEGURO
-- ========================================================

DELIMITER $$

CREATE PROCEDURE AddColumnIfNotExists(
    IN tableName VARCHAR(64),
    IN columnName VARCHAR(64),
    IN columnDefinition TEXT
)
BEGIN
    DECLARE columnExists INT DEFAULT 0;
    
    SELECT COUNT(*) INTO columnExists
    FROM information_schema.columns 
    WHERE table_schema = DATABASE() 
    AND table_name = tableName 
    AND column_name = columnName;
    
    IF columnExists = 0 THEN
        SET @sql = CONCAT('ALTER TABLE ', tableName, ' ADD COLUMN ', columnName, ' ', columnDefinition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

-- Procedimiento para agregar índices de forma segura
CREATE PROCEDURE AddIndexIfNotExists(
    IN tableName VARCHAR(64),
    IN indexName VARCHAR(64),
    IN indexDefinition TEXT
)
BEGIN
    DECLARE indexExists INT DEFAULT 0;
    
    SELECT COUNT(*) INTO indexExists
    FROM information_schema.statistics 
    WHERE table_schema = DATABASE() 
    AND table_name = tableName 
    AND index_name = indexName;
    
    IF indexExists = 0 THEN
        SET @sql = CONCAT('ALTER TABLE ', tableName, ' ADD INDEX ', indexName, ' ', indexDefinition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

-- Procedimiento para agregar constraints de forma segura
CREATE PROCEDURE AddConstraintIfNotExists(
    IN tableName VARCHAR(64),
    IN constraintName VARCHAR(64),
    IN constraintDefinition TEXT
)
BEGIN
    DECLARE constraintExists INT DEFAULT 0;
    
    SELECT COUNT(*) INTO constraintExists
    FROM information_schema.table_constraints 
    WHERE table_schema = DATABASE() 
    AND table_name = tableName 
    AND constraint_name = constraintName;
    
    IF constraintExists = 0 THEN
        SET @sql = CONCAT('ALTER TABLE ', tableName, ' ADD CONSTRAINT ', constraintName, ' ', constraintDefinition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

-- ========================================================
-- PASO 2: AGREGAR CAMPOS FALTANTES USANDO PROCEDIMIENTOS
-- ========================================================

-- Agregar campos de timestamp a staff
CALL AddColumnIfNotExists('staff', 'created_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
CALL AddColumnIfNotExists('staff', 'updated_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

-- Agregar campos de timestamp a users
CALL AddColumnIfNotExists('users', 'created_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
CALL AddColumnIfNotExists('users', 'updated_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

-- Agregar campo status a leave_requests
CALL AddColumnIfNotExists('leave_requests', 'status', 'ENUM(\'pending\', \'approved\', \'rejected\') DEFAULT \'pending\'');

-- Agregar timestamps a vacations
CALL AddColumnIfNotExists('vacations', 'created_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
CALL AddColumnIfNotExists('vacations', 'updated_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

-- Agregar timestamps a otras tablas
CALL AddColumnIfNotExists('staff_department_history', 'created_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
CALL AddColumnIfNotExists('attendance', 'created_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP');
CALL AddColumnIfNotExists('user_departments', 'created_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP');

-- ========================================================
-- PASO 3: HACER EMAIL ÚNICO EN USERS (SI NO LO ES)
-- ========================================================

-- Verificar y agregar constraint de email único
CALL AddConstraintIfNotExists('users', 'unique_email', 'UNIQUE (email)');

-- ========================================================
-- PASO 4: AGREGAR ÍNDICES PARA OPTIMIZACIÓN
-- ========================================================

-- Índices para academic_degrees
CALL AddIndexIfNotExists('academic_degrees', 'idx_name', '(name)');

-- Índices para academic_specializations  
CALL AddIndexIfNotExists('academic_specializations', 'idx_name', '(name)');

-- Índices para job_positions
CALL AddIndexIfNotExists('job_positions', 'idx_name', '(name)');

-- Índices para departments
CALL AddIndexIfNotExists('departments', 'idx_name', '(name)');
CALL AddIndexIfNotExists('departments', 'idx_shift_type', '(shift_type)');
CALL AddIndexIfNotExists('departments', 'idx_manager_id', '(manager_id)');

-- Índices para divisions
CALL AddIndexIfNotExists('divisions', 'idx_department_id', '(department_id)');
CALL AddIndexIfNotExists('divisions', 'idx_name', '(name)');

-- Índices críticos para staff (tabla principal)
CALL AddIndexIfNotExists('staff', 'idx_id_number', '(id_number)');
CALL AddIndexIfNotExists('staff', 'idx_department_id', '(department_id)');
CALL AddIndexIfNotExists('staff', 'idx_division_id', '(division_id)');
CALL AddIndexIfNotExists('staff', 'idx_status', '(status)');
CALL AddIndexIfNotExists('staff', 'idx_hire_date', '(hire_date)');
CALL AddIndexIfNotExists('staff', 'idx_full_name', '(first_name, last_name)');
CALL AddIndexIfNotExists('staff', 'idx_job_position', '(job_position_id)');

-- Índices para users
CALL AddIndexIfNotExists('users', 'idx_username', '(username)');
CALL AddIndexIfNotExists('users', 'idx_email', '(email)');
CALL AddIndexIfNotExists('users', 'idx_status', '(status)');
CALL AddIndexIfNotExists('users', 'idx_staff_id', '(staff_id)');

-- Índices para user_departments
CALL AddIndexIfNotExists('user_departments', 'idx_user_id', '(user_id)');
CALL AddIndexIfNotExists('user_departments', 'idx_department_id', '(department_id)');
CALL AddIndexIfNotExists('user_departments', 'idx_status', '(status)');

-- Constraint único para user_departments
CALL AddConstraintIfNotExists('user_departments', 'unique_user_department', 'UNIQUE (user_id, department_id)');

-- Índices para attendance
CALL AddIndexIfNotExists('attendance', 'idx_staff_id', '(staff_id)');
CALL AddIndexIfNotExists('attendance', 'idx_date', '(date)');
CALL AddIndexIfNotExists('attendance', 'idx_staff_date', '(staff_id, date)');

-- Constraint único para attendance
CALL AddConstraintIfNotExists('attendance', 'unique_staff_date', 'UNIQUE (staff_id, date)');

-- Índices para leave_requests
CALL AddIndexIfNotExists('leave_requests', 'idx_staff_id', '(staff_id)');
CALL AddIndexIfNotExists('leave_requests', 'idx_request_date', '(request_date)');
CALL AddIndexIfNotExists('leave_requests', 'idx_status', '(status)');
CALL AddIndexIfNotExists('leave_requests', 'idx_type', '(type)');

-- Índices para vacations
CALL AddIndexIfNotExists('vacations', 'idx_staff_id', '(staff_id)');
CALL AddIndexIfNotExists('vacations', 'idx_date_range', '(start_date, end_date)');
CALL AddIndexIfNotExists('vacations', 'idx_status', '(status)');

-- Índices para staff_department_history
CALL AddIndexIfNotExists('staff_department_history', 'idx_staff_id', '(staff_id)');
CALL AddIndexIfNotExists('staff_department_history', 'idx_department_id', '(department_id)');
CALL AddIndexIfNotExists('staff_department_history', 'idx_date_range', '(start_date, end_date)');

-- Índices para department_schedules
CALL AddIndexIfNotExists('department_schedules', 'idx_department_id', '(department_id)');
CALL AddIndexIfNotExists('department_schedules', 'idx_day', '(day)');
CALL AddIndexIfNotExists('department_schedules', 'idx_time_range', '(start_time, end_time)');

-- Índices para audit_log
CALL AddIndexIfNotExists('audit_log', 'idx_user_id', '(user_id)');
CALL AddIndexIfNotExists('audit_log', 'idx_action', '(action)');
CALL AddIndexIfNotExists('audit_log', 'idx_table_affected', '(table_affected)');
CALL AddIndexIfNotExists('audit_log', 'idx_created_at', '(created_at)');
CALL AddIndexIfNotExists('audit_log', 'idx_record_id', '(record_id)');

-- ========================================================
-- PASO 5: CREAR TRIGGERS PARA CAMPOS updated_at
-- ========================================================

-- Trigger para staff
DROP TRIGGER IF EXISTS staff_updated_at_trigger;
DELIMITER $$
CREATE TRIGGER staff_updated_at_trigger
    BEFORE UPDATE ON staff
    FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END$$
DELIMITER ;

-- Trigger para users
DROP TRIGGER IF EXISTS users_updated_at_trigger;
DELIMITER $$
CREATE TRIGGER users_updated_at_trigger
    BEFORE UPDATE ON users
    FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END$$
DELIMITER ;

-- Trigger para vacations
DROP TRIGGER IF EXISTS vacations_updated_at_trigger;
DELIMITER $$
CREATE TRIGGER vacations_updated_at_trigger
    BEFORE UPDATE ON vacations
    FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END$$
DELIMITER ;

-- ========================================================
-- PASO 6: CREAR VISTA OPTIMIZADA
-- ========================================================

-- Vista completa de información del personal
DROP VIEW IF EXISTS staff_complete_info;
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
    CONCAT(m.first_name, ' ', m.last_name) AS manager_name,
    s.created_at,
    s.updated_at
FROM staff s
    LEFT JOIN departments d ON s.department_id = d.id
    LEFT JOIN divisions dv ON s.division_id = dv.id
    LEFT JOIN job_positions jp ON s.job_position_id = jp.id
    LEFT JOIN academic_degrees ad ON s.academic_degree_id = ad.id
    LEFT JOIN academic_specializations asp ON s.academic_specialization_id = asp.id
    LEFT JOIN staff m ON d.manager_id = m.id;

-- ========================================================
-- PASO 7: OPTIMIZAR TABLAS Y ANALIZAR ESTADÍSTICAS
-- ========================================================

-- Optimizar todas las tablas principales
OPTIMIZE TABLE academic_degrees;
OPTIMIZE TABLE academic_specializations;
OPTIMIZE TABLE job_positions;
OPTIMIZE TABLE departments;
OPTIMIZE TABLE divisions;
OPTIMIZE TABLE staff;
OPTIMIZE TABLE users;
OPTIMIZE TABLE user_departments;
OPTIMIZE TABLE attendance;
OPTIMIZE TABLE leave_requests;
OPTIMIZE TABLE vacations;
OPTIMIZE TABLE staff_department_history;
OPTIMIZE TABLE department_schedules;
OPTIMIZE TABLE audit_log;

-- Analizar tablas para estadísticas actualizadas
ANALYZE TABLE academic_degrees;
ANALYZE TABLE academic_specializations;
ANALYZE TABLE job_positions;
ANALYZE TABLE departments;
ANALYZE TABLE divisions;
ANALYZE TABLE staff;
ANALYZE TABLE users;
ANALYZE TABLE user_departments;
ANALYZE TABLE attendance;
ANALYZE TABLE leave_requests;
ANALYZE TABLE vacations;
ANALYZE TABLE staff_department_history;
ANALYZE TABLE department_schedules;
ANALYZE TABLE audit_log;

-- ========================================================
-- PASO 8: LIMPIAR PROCEDIMIENTOS TEMPORALES
-- ========================================================

DROP PROCEDURE IF EXISTS AddColumnIfNotExists;
DROP PROCEDURE IF EXISTS AddIndexIfNotExists;
DROP PROCEDURE IF EXISTS AddConstraintIfNotExists;

-- ========================================================
-- PASO 9: VERIFICACIONES FINALES
-- ========================================================

-- Verificar integridad de foreign keys
SELECT 
    TABLE_NAME as 'Tabla',
    CONSTRAINT_NAME as 'Restricción',
    REFERENCED_TABLE_NAME as 'Tabla Referenciada',
    REFERENCED_COLUMN_NAME as 'Columna Referenciada'
FROM information_schema.KEY_COLUMN_USAGE 
WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
ORDER BY TABLE_NAME;

-- Mostrar índices creados
SELECT 
    TABLE_NAME as 'Tabla',
    INDEX_NAME as 'Índice',
    COLUMN_NAME as 'Columna',
    NON_UNIQUE as 'No Único'
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = DATABASE()
AND INDEX_NAME != 'PRIMARY'
ORDER BY TABLE_NAME, INDEX_NAME;

-- Verificar campos agregados
SHOW COLUMNS FROM staff LIKE '%created_at%';
SHOW COLUMNS FROM staff LIKE '%updated_at%';
SHOW COLUMNS FROM users LIKE '%created_at%';
SHOW COLUMNS FROM users LIKE '%updated_at%';
SHOW COLUMNS FROM leave_requests LIKE '%status%';

-- Restaurar configuración
SET FOREIGN_KEY_CHECKS = 1;

-- ========================================================
-- MENSAJE FINAL
-- ========================================================

SELECT 'MIGRACIÓN COMPLETADA EXITOSAMENTE' as 'STATUS',
       'Revisa que todas las verificaciones muestren los resultados esperados' as 'NOTA',
       'Ejecuta consultas de prueba para verificar el rendimiento mejorado' as 'SIGUIENTE_PASO';

-- ========================================================
-- CONSULTAS DE PRUEBA PARA VERIFICAR OPTIMIZACIÓN
-- ========================================================

-- Estas consultas deberían ser ahora mucho más rápidas:

-- 1. Búsqueda por cédula (debería usar idx_id_number)
-- EXPLAIN SELECT * FROM staff WHERE id_number = 'V12345678';

-- 2. Empleados por departamento (debería usar idx_department_id)
-- EXPLAIN SELECT * FROM staff WHERE department_id = 1;

-- 3. Consulta compleja optimizada (debería usar la vista)
-- EXPLAIN SELECT * FROM staff_complete_info WHERE status = 'active';

-- 4. Asistencias de un empleado (debería usar idx_staff_date)
-- EXPLAIN SELECT * FROM attendance WHERE staff_id = 1 AND date >= '2024-01-01';

COMMIT;