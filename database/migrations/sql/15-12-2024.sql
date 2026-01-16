-- ALTER TABLE questions ADD video TEXT NULL DEFAULT NULL AFTER id;
-- ALTER TABLE `student_exams` ADD `total_degree` DOUBLE NULL DEFAULT NULL AFTER `id`;
-- ALTER TABLE `users` ADD `location` VARCHAR(255) NULL DEFAULT NULL AFTER `id`, ADD `birth_date` DATE NULL DEFAULT NULL AFTER `location`, ADD `full_name` VARCHAR(255) NULL AFTER `birth_date`;
-- ALTER TABLE `exams` ADD `exam_order` INT NOT NULL DEFAULT '1' AFTER `model_id`;
-- ALTER TABLE `student_exams` CHANGE `start_date` `start_date` DATETIME NULL DEFAULT NULL;
-- ALTER TABLE `users` CHANGE `last_name` `last_name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;
-- ALTER TABLE `questions` ADD `video` TEXT NULL DEFAULT NULL AFTER `text`;

-- ALTER TABLE lessons
-- ADD COLUMN lesson_order INT;

-- ALTER TABLE lessons
-- ADD COLUMN section_id BIGINT UNSIGNED,
-- ADD CONSTRAINT fk_section
-- FOREIGN KEY (section_id) REFERENCES sections(id)
-- ON DELETE SET NULL;

-- DROP TABLE IF EXISTS lesson_section;

-- ALTER TABLE `exams` ADD `image` VARCHAR(255) NULL AFTER `id`, ADD `name` VARCHAR(255) NULL AFTER `image`;
-- ALTER TABLE `exams` CHANGE `exam_order` `exam_order` INT NULL DEFAULT NULL;

-- ALTER TABLE `questions` ADD `image` VARCHAR(255) NULL DEFAULT NULL AFTER `video`;

-- ALTER TABLE `student_exams` ADD `on_time` BOOLEAN NULL DEFAULT NULL AFTER `exam_id`;

-- ALTER TABLE `exams` ADD `is_free` BOOLEAN NOT NULL DEFAULT FALSE AFTER `model_id`;

-- ALTER TABLE infos MODIFY value TEXT NULL;
-- ALTER TABLE `exams` ADD `solution_file` VARCHAR(255) NULL DEFAULT NULL AFTER `id`;

-- ALTER TABLE `users` ADD `family_phone_number` VARCHAR(255) NULL DEFAULT NULL AFTER `id`;

-- alter table users add COLUMN city_id bigint unsigned null ;
-- alter table users add CONSTRAINT FOREIGN KEY(city_id) REFERENCES cities(id) on delete set null;

-- INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES (NULL, 'super_admin', 'api', NULL, NULL)

-- ALTER TABLE `users` CHANGE `email` `email` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;
-- ALTER TABLE `offers` CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;
-- ALTER TABLE `offers` CHANGE `description` `description` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;
-- ALTER TABLE `offers` CHANGE `discount` `discount` INT NULL DEFAULT NULL;

-- ALTER TABLE notifications ADD COLUMN params JSON NULL;

-- ALTER TABLE notifications MODIFY COLUMN title TEXT NULL;
-- ALTER TABLE notifications MODIFY COLUMN description TEXT NULL;
-- ALTER TABLE notifications MODIFY COLUMN type VARCHAR(255) NULL;
-- ALTER TABLE notifications MODIFY COLUMN model_type VARCHAR(255) NULL;
-- ALTER TABLE notifications MODIFY COLUMN model_id BIGINT UNSIGNED NULL;

-- ALTER TABLE `users` ADD `is_banned` BOOLEAN NOT NULL DEFAULT FALSE AFTER `id`;

-- ALTER TABLE `contact_messages` CHANGE `email` `email` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;

-- ALTER TABLE `subscription_requests` CHANGE `image` `image` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;

-- ALTER TABLE `auth_codes` ADD `type` VARCHAR(255) NULL DEFAULT 'forget_password' AFTER `id`;

-- ALTER TABLE `exams` ADD `random_questions_max` INT NULL DEFAULT NULL AFTER `id`;

-- ALTER TABLE `student_answers` CHANGE `option_id` `option_id` BIGINT UNSIGNED NULL DEFAULT NULL;

-- ALTER TABLE student_answers DROP FOREIGN KEY student_answers_option_id_foreign;

-- ALTER TABLE student_answers
-- ADD CONSTRAINT student_answers_option_id_foreign
-- FOREIGN KEY (option_id) REFERENCES options(id)
-- ON DELETE SET NULL;

-- ALTER TABLE `exams` ADD `degree` DOUBLE NOT NULL DEFAULT '100' FIRST;

-- ALTER TABLE `student_exams` ADD `exam_degree` DOUBLE NOT NULL DEFAULT '100' AFTER `id`;

-- ALTER TABLE `whats_app_messages` CHANGE `receiver_id` `receiver_id` BIGINT UNSIGNED NULL DEFAULT NULL;
-- ALTER TABLE `auth_codes` ADD `phone_number` VARCHAR(255) NULL DEFAULT NULL AFTER `id`;

-- ALTER TABLE `users` ADD `family_phone_number_country_code` VARCHAR(255) NULL DEFAULT NULL AFTER `is_banned`,
--  ADD `phone_number_country_code` VARCHAR(255) NOT NULL AFTER `family_phone_number_country_code`;
-- ALTER TABLE `users` CHANGE `phone_number_country_code` `phone_number_country_code` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;

-- ALTER TABLE `users` CHANGE `email` `email` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;

-- ALTER TABLE `options` CHANGE `name` `name` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
-- ALTER TABLE `questions` CHANGE `text` `text` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
-- ALTER TABLE `questions` CHANGE `note` `note` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;


-- ALTER TABLE notifications DROP COLUMN title;
-- ALTER TABLE notifications DROP COLUMN description;
-- ALTER TABLE notifications DROP COLUMN type;
-- ALTER TABLE notifications DROP COLUMN model_id;
-- ALTER TABLE notifications DROP COLUMN model_type;
-- ALTER TABLE notifications DROP COLUMN additional_data;

-- ALTER TABLE `notifications` ADD `params` JSON NULL DEFAULT NULL AFTER `id`;
-- ALTER TABLE `notifications` ADD `type` VARCHAR(255) NULL DEFAULT NULL AFTER `params`;

-- ALTER TABLE `users` DROP INDEX `users_username_unique`;
-- ALTER TABLE `users` DROP INDEX `users_email_unique`;

-- ALTER TABLE `questions` ADD `page_number` INT NULL DEFAULT NULL AFTER `id`;

-- ALTER TABLE `student_exams` ADD `exam_pass_percentage` INT NULL DEFAULT NULL AFTER `exam_degree`;

-- ALTER TABLE lessons CHANGE video_url video_url VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;
-- ALTER TABLE lessons CHANGE time time TIME NULL;


-- ALTER TABLE `exams`
-- ADD COLUMN `student_id` BIGINT UNSIGNED NULL AFTER `degree`,
-- ADD CONSTRAINT `exams_student_id_foreign`
-- FOREIGN KEY (`student_id`) REFERENCES `users` (`id`)
-- ON DELETE CASCADE;

-- ALTER TABLE `exams` ADD `type` VARCHAR(255) NOT NULL DEFAULT 'ORIGINAL' AFTER `id`;


-- ALTER TABLE exams ADD COLUMN expiers_at TIMESTAMP NULL;
-- ALTER TABLE `questions` ADD `note_image` VARCHAR(255) NULL DEFAULT NULL AFTER `image`;
