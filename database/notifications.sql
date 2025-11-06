CREATE TABLE IF NOT EXISTS `notifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(10) UNSIGNED NOT NULL,         -- recipient (job owner)
  `actor_id` INT(10) UNSIGNED NOT NULL,        -- commenter
  `job_id` INT(11) NOT NULL,                   -- jobs.id (signed)
  `comment_id` INT UNSIGNED NOT NULL,          -- comments.id (unsigned)
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `seen_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_seen` (`user_id`,`seen_at`),
  KEY `idx_job` (`job_id`),
  KEY `idx_actor` (`actor_id`),
  CONSTRAINT `fk_notif_user`    FOREIGN KEY (`user_id`)   REFERENCES `users` (`id`)     ON DELETE CASCADE,
  CONSTRAINT `fk_notif_actor`   FOREIGN KEY (`actor_id`)  REFERENCES `users` (`id`)     ON DELETE CASCADE,
  CONSTRAINT `fk_notif_job`     FOREIGN KEY (`job_id`)    REFERENCES `jobs`  (`id`)     ON DELETE CASCADE,
  CONSTRAINT `fk_notif_comment` FOREIGN KEY (`comment_id`)REFERENCES `comments` (`id`)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
