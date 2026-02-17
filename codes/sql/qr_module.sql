CREATE TABLE IF NOT EXISTS qr_admin_users (
  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  participant_id INT(10) UNSIGNED DEFAULT NULL,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  is_approved TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login_at DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_qr_admin_email (email),
  KEY idx_qr_admin_approved (is_approved),
  CONSTRAINT fk_qr_admin_participant FOREIGN KEY (participant_id) REFERENCES crawl_participants(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS qr_links (
  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  business_id INT(10) UNSIGNED DEFAULT NULL,
  created_by_admin_id INT(10) UNSIGNED DEFAULT NULL,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(180) NOT NULL,
  destination_url VARCHAR(2048) NOT NULL,
  qr_payload TEXT NOT NULL,
  qr_settings_json JSON DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  scan_count INT(10) UNSIGNED NOT NULL DEFAULT 0,
  last_scanned_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_qr_slug (slug),
  KEY idx_qr_business (business_id),
  KEY idx_qr_active (is_active),
  CONSTRAINT fk_qr_business FOREIGN KEY (business_id) REFERENCES biz_stpats(id) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_qr_admin FOREIGN KEY (created_by_admin_id) REFERENCES qr_admin_users(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS qr_scans (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  qr_link_id INT(10) UNSIGNED NOT NULL,
  ip_hash CHAR(64) NOT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  referer VARCHAR(255) DEFAULT NULL,
  accept_language VARCHAR(120) DEFAULT NULL,
  query_string TEXT DEFAULT NULL,
  device_type VARCHAR(20) NOT NULL DEFAULT 'desktop',
  scan_source VARCHAR(50) NOT NULL DEFAULT 'short-url',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_scan_link (qr_link_id),
  KEY idx_scan_created (created_at),
  KEY idx_scan_device (device_type),
  CONSTRAINT fk_scan_link FOREIGN KEY (qr_link_id) REFERENCES qr_links(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Example approved admin user (replace email/password hash first)
-- INSERT INTO qr_admin_users (email, password_hash, is_approved)
-- VALUES ('admin@glparade.com', '$2y$10$replace_with_password_hash', 1);
