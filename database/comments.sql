-- Uses existing `login` DB. Run once.
CREATE TABLE IF NOT EXISTS `comments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  -- Match jobs.id which is INT(11) signed
  `job_id` INT(11) NOT NULL,
  -- Match users.id which is INT(10) UNSIGNED
  `user_id` INT(10) UNSIGNED NOT NULL,
  -- Match this table's `id` which is UNSIGNED
  `parent_id` INT UNSIGNED DEFAULT NULL,
  `body` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_comments_job` (`job_id`),
  KEY `idx_comments_parent` (`parent_id`),
  KEY `idx_comments_user` (`user_id`),
  CONSTRAINT `fk_comments_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comments_parent` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
