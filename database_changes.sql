-- Clean up previous attempts if needed
ALTER TABLE `user_departments` DROP FOREIGN KEY `fk_user_dep_role`;
ALTER TABLE `user_departments` DROP COLUMN `role_id`;
DROP TABLE IF EXISTS `roles`;

-- 1. Modify users table to add is_superadmin (ignore if exists)
-- This was already done in a previous run, so I'll leave it out to avoid errors.
-- ALTER TABLE `users` ADD COLUMN `is_superadmin` TINYINT(1) NOT NULL DEFAULT 0 AFTER `password_hash`;

-- 2. Create roles table
CREATE TABLE `roles` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `department_id` INT(11) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `can_read` TINYINT(1) NOT NULL DEFAULT 0,
  `can_write` TINYINT(1) NOT NULL DEFAULT 0,
  `can_modify` TINYINT(1) NOT NULL DEFAULT 0,
  `can_delete` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Modify user_departments to add role_id
ALTER TABLE `user_departments` ADD COLUMN `role_id` INT(11) NULL AFTER `department_id`;

-- Add foreign key for role_id
ALTER TABLE `user_departments` ADD CONSTRAINT `fk_user_dep_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE SET NULL;

-- 4. Insert default 'admin' roles for each existing department
INSERT INTO `roles` (`department_id`, `name`, `description`, `can_read`, `can_write`, `can_modify`, `can_delete`)
SELECT `id`, 'admin', 'Administrador del departamento', 1, 1, 1, 1
FROM `departments`;
