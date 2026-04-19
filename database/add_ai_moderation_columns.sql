-- Adds AI moderation support for jobs and offers

ALTER TABLE jobs
  ADD COLUMN ai_decision VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER status,
  ADD COLUMN ai_score DECIMAL(5,4) NULL AFTER ai_decision,
  ADD COLUMN ai_reason VARCHAR(255) NULL AFTER ai_score,
  ADD COLUMN ai_model VARCHAR(80) NULL AFTER ai_reason,
  ADD COLUMN ai_reviewed_at DATETIME NULL AFTER ai_model,
  ADD COLUMN ai_raw_json LONGTEXT NULL AFTER ai_reviewed_at;

ALTER TABLE offers
  ADD COLUMN ai_decision VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER citizen_status,
  ADD COLUMN ai_score DECIMAL(5,4) NULL AFTER ai_decision,
  ADD COLUMN ai_reason VARCHAR(255) NULL AFTER ai_score,
  ADD COLUMN ai_model VARCHAR(80) NULL AFTER ai_reason,
  ADD COLUMN ai_reviewed_at DATETIME NULL AFTER ai_model,
  ADD COLUMN ai_raw_json LONGTEXT NULL AFTER ai_reviewed_at;

CREATE TABLE IF NOT EXISTS moderation_audit_log (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  target_type VARCHAR(20) NOT NULL,
  target_id INT NOT NULL,
  decision VARCHAR(20) NOT NULL,
  score DECIMAL(5,4) NULL,
  reason VARCHAR(255) NULL,
  model VARCHAR(80) NULL,
  raw_json LONGTEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  overridden_by INT NULL,
  override_note VARCHAR(255) NULL,
  PRIMARY KEY (id),
  KEY idx_target (target_type, target_id),
  KEY idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
