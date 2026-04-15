-- Chat messages table for ServisyoHub
CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_id` INT(11) NOT NULL,
  `offer_id` INT(11) NOT NULL,
  `sender_id` INT(10) UNSIGNED NOT NULL,
  `recipient_id` INT(10) UNSIGNED NOT NULL,
  `body` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_chat_offer` (`offer_id`),
  KEY `idx_chat_job` (`job_id`),
  KEY `idx_chat_sender` (`sender_id`),
  KEY `idx_chat_recipient` (`recipient_id`),
  KEY `idx_chat_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
