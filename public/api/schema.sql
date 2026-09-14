-- ============================================================
-- Karl Peace Legacy Foundation — MySQL Database Schema
-- Run this in phpMyAdmin or: mysql -u root -p < schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS karl_peace_foundation
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE karl_peace_foundation;

-- ------------------------------------------------------------
-- 1. Admin Users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  uid           VARCHAR(64)  NOT NULL UNIQUE,
  email         VARCHAR(191) NOT NULL UNIQUE,
  display_name  VARCHAR(191) NOT NULL DEFAULT 'Foundation Admin',
  password_hash VARCHAR(255) NOT NULL,
  photo_url     VARCHAR(512) DEFAULT NULL,
  role          ENUM('admin','editor','viewer') NOT NULL DEFAULT 'editor',
  is_super_admin TINYINT(1) NOT NULL DEFAULT 0,
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  reset_token   VARCHAR(128) DEFAULT NULL,
  reset_expires DATETIME    DEFAULT NULL,
  last_login_at DATETIME    DEFAULT NULL,
  created_at    DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. Admin Sessions
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_sessions (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED NOT NULL,
  token_hash  VARCHAR(128) NOT NULL UNIQUE,
  ip_address  VARCHAR(45)  DEFAULT NULL,
  user_agent  TEXT         DEFAULT NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expires_at  DATETIME NOT NULL,
  last_used_at DATETIME DEFAULT NULL,
  is_revoked  TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. Site Settings (single row)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS site_settings (
  id                        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  hero_title                TEXT NOT NULL,
  hero_subtitle             TEXT NOT NULL,
  hero_badge                VARCHAR(255) NOT NULL DEFAULT '',
  contact_email             VARCHAR(191) NOT NULL DEFAULT '',
  contact_phone             VARCHAR(64)  NOT NULL DEFAULT '',
  office_address            TEXT NOT NULL,
  registered_address        TEXT DEFAULT NULL,
  registration_number       VARCHAR(64)  DEFAULT NULL,
  official_domain           VARCHAR(191) DEFAULT NULL,
  scholarship_alert_active  TINYINT(1) NOT NULL DEFAULT 1,
  scholarship_alert_title   VARCHAR(255) NOT NULL DEFAULT '',
  scholarship_alert_text    TEXT NOT NULL,
  scholarship_alert_deadline VARCHAR(128) NOT NULL DEFAULT '',
  scholarship_alert_cycle   VARCHAR(128) NOT NULL DEFAULT '',
  updated_at                DATETIME DEFAULT NULL,
  updated_by                VARCHAR(191) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. Programs
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS programs (
  id                  VARCHAR(64) PRIMARY KEY,
  title               VARCHAR(255) NOT NULL,
  category            ENUM('scholarships','mentorship','health','opportunities') NOT NULL,
  badge               VARCHAR(128) NOT NULL DEFAULT '',
  badge_color         VARCHAR(255) NOT NULL DEFAULT '',
  image               TEXT DEFAULT NULL,
  description         TEXT NOT NULL,
  key_points          JSON DEFAULT NULL,
  detailed_narrative  TEXT DEFAULT NULL,
  eligibility_snippet TEXT DEFAULT NULL,
  timeline            VARCHAR(255) DEFAULT NULL,
  display_order       INT UNSIGNED NOT NULL DEFAULT 0,
  is_active           TINYINT(1) NOT NULL DEFAULT 1,
  created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  updated_by          VARCHAR(191) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 5. News Articles
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS news_articles (
  id            VARCHAR(64) PRIMARY KEY,
  title         VARCHAR(255) NOT NULL,
  category      VARCHAR(128) NOT NULL DEFAULT '',
  category_type ENUM('scholarship','health','mentorship','bulletin') NOT NULL DEFAULT 'bulletin',
  cycle         VARCHAR(128) NOT NULL DEFAULT '',
  summary       TEXT NOT NULL,
  full_content  LONGTEXT NOT NULL,
  location      VARCHAR(255) NOT NULL DEFAULT '',
  date          VARCHAR(64)  NOT NULL DEFAULT '',
  is_urgent     TINYINT(1) NOT NULL DEFAULT 0,
  is_published  TINYINT(1) NOT NULL DEFAULT 1,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  published_by  VARCHAR(191) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 6. Team Members (Leadership)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS team_members (
  id                VARCHAR(64) PRIMARY KEY,
  name              VARCHAR(255) NOT NULL,
  role              VARCHAR(255) NOT NULL,
  category          ENUM('founder','executive','operations','patron') NOT NULL DEFAULT 'operations',
  tagline           VARCHAR(255) NOT NULL DEFAULT '',
  short_bio         TEXT NOT NULL,
  full_bio          TEXT NOT NULL,
  image             TEXT DEFAULT NULL,
  badge             VARCHAR(128) NOT NULL DEFAULT '',
  badge_color       VARCHAR(255) DEFAULT NULL,
  linkedin          VARCHAR(512) DEFAULT NULL,
  twitter           VARCHAR(512) DEFAULT NULL,
  email             VARCHAR(191) DEFAULT NULL,
  key_contributions JSON DEFAULT NULL,
  department        VARCHAR(191) DEFAULT NULL,
  quote             TEXT DEFAULT NULL,
  display_order     INT UNSIGNED NOT NULL DEFAULT 0,
  is_active         TINYINT(1) NOT NULL DEFAULT 1,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 7. Gallery Photos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS gallery_photos (
  id            VARCHAR(64) PRIMARY KEY,
  title         VARCHAR(255) NOT NULL,
  caption       TEXT NOT NULL,
  category      ENUM('all','scholarships','mentorship','health','community') NOT NULL DEFAULT 'all',
  image         TEXT NOT NULL,
  location      VARCHAR(255) NOT NULL DEFAULT '',
  date          VARCHAR(64)  NOT NULL DEFAULT '',
  display_order INT UNSIGNED NOT NULL DEFAULT 0,
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  uploaded_by   VARCHAR(191) DEFAULT NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 8. Testimonials
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS testimonials (
  id          VARCHAR(64) PRIMARY KEY,
  name        VARCHAR(255) NOT NULL,
  institution VARCHAR(255) NOT NULL DEFAULT '',
  field       VARCHAR(255) NOT NULL DEFAULT '',
  quote       TEXT NOT NULL,
  year        VARCHAR(16)  NOT NULL DEFAULT '',
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  is_active   TINYINT(1) NOT NULL DEFAULT 1,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 9. FAQs
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS faqs (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  question      TEXT NOT NULL,
  answer        TEXT NOT NULL,
  category      ENUM('scholarships','mentorship','general') NOT NULL DEFAULT 'general',
  display_order INT UNSIGNED NOT NULL DEFAULT 0,
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 10. Subscribers (Scholarship notification sign-ups)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS subscribers (
  id               VARCHAR(64) PRIMARY KEY,
  name             VARCHAR(255) NOT NULL,
  email            VARCHAR(191) NOT NULL UNIQUE,
  institution      VARCHAR(255) DEFAULT NULL,
  course           VARCHAR(255) DEFAULT NULL,
  status           ENUM('new','notified','archived') NOT NULL DEFAULT 'new',
  ip_address       VARCHAR(45)  DEFAULT NULL,
  source_page      VARCHAR(255) DEFAULT NULL,
  unsubscribe_token VARCHAR(128) DEFAULT NULL,
  created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 11. Inquiries (Contact form)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS inquiries (
  id          VARCHAR(64) PRIMARY KEY,
  name        VARCHAR(255) NOT NULL,
  email       VARCHAR(191) NOT NULL,
  subject     VARCHAR(255) DEFAULT NULL,
  message     TEXT NOT NULL,
  role        VARCHAR(128) DEFAULT NULL,
  status      ENUM('unread','replied','archived') NOT NULL DEFAULT 'unread',
  ip_address  VARCHAR(45)  DEFAULT NULL,
  replied_at  DATETIME DEFAULT NULL,
  replied_by  VARCHAR(191) DEFAULT NULL,
  reply_notes TEXT DEFAULT NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 12. Scholarship Applications
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS scholarship_applications (
  id                  VARCHAR(64) PRIMARY KEY,
  -- Personal Info
  first_name          VARCHAR(128) NOT NULL,
  last_name           VARCHAR(128) NOT NULL,
  email               VARCHAR(191) NOT NULL,
  phone               VARCHAR(32)  NOT NULL,
  date_of_birth       DATE DEFAULT NULL,
  gender              ENUM('male','female','prefer_not_to_say') DEFAULT NULL,
  state_of_origin     VARCHAR(128) DEFAULT NULL,
  lga                 VARCHAR(128) DEFAULT NULL,
  home_address        TEXT DEFAULT NULL,
  -- Academic Info
  institution         VARCHAR(255) NOT NULL,
  institution_type    ENUM('university','polytechnic','college_of_education','other') NOT NULL DEFAULT 'university',
  faculty             VARCHAR(255) DEFAULT NULL,
  department          VARCHAR(255) NOT NULL,
  matric_number       VARCHAR(64)  DEFAULT NULL,
  academic_level      ENUM('100','200','300','400','500','postgraduate') NOT NULL DEFAULT '100',
  cgpa                DECIMAL(3,2) DEFAULT NULL,
  cgpa_scale          ENUM('4.0','5.0') NOT NULL DEFAULT '5.0',
  academic_year       VARCHAR(16)  NOT NULL DEFAULT '',
  scholarship_cycle   VARCHAR(64)  NOT NULL DEFAULT '',
  -- Supporting Statement
  personal_statement  TEXT DEFAULT NULL,
  why_deserve         TEXT DEFAULT NULL,
  career_goals        TEXT DEFAULT NULL,
  -- Documents (file paths)
  transcript_url      VARCHAR(512) DEFAULT NULL,
  id_card_url         VARCHAR(512) DEFAULT NULL,
  admission_letter_url VARCHAR(512) DEFAULT NULL,
  passport_photo_url  VARCHAR(512) DEFAULT NULL,
  recommendation_url  VARCHAR(512) DEFAULT NULL,
  -- Status & Review
  status              ENUM('draft','submitted','under_review','shortlisted','approved','rejected','withdrawn') NOT NULL DEFAULT 'draft',
  reviewer_id         INT UNSIGNED DEFAULT NULL,
  reviewer_notes      TEXT DEFAULT NULL,
  reviewed_at         DATETIME DEFAULT NULL,
  awarded_amount      DECIMAL(12,2) DEFAULT NULL,
  award_notes         TEXT DEFAULT NULL,
  -- Meta
  ip_address          VARCHAR(45)  DEFAULT NULL,
  submitted_at        DATETIME DEFAULT NULL,
  created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (reviewer_id) REFERENCES admin_users(id) ON DELETE SET NULL,
  INDEX idx_email (email),
  INDEX idx_status (status),
  INDEX idx_cycle (scholarship_cycle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 13. Media Uploads
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS media_uploads (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  original_filename VARCHAR(255) NOT NULL,
  stored_filename   VARCHAR(255) NOT NULL UNIQUE,
  file_path         VARCHAR(512) NOT NULL,
  public_url        VARCHAR(512) NOT NULL,
  file_size         INT UNSIGNED DEFAULT NULL,
  mime_type         VARCHAR(64)  DEFAULT NULL,
  width             INT UNSIGNED DEFAULT NULL,
  height            INT UNSIGNED DEFAULT NULL,
  entity_type       VARCHAR(64)  DEFAULT NULL,
  entity_id         VARCHAR(64)  DEFAULT NULL,
  uploaded_by       VARCHAR(191) DEFAULT NULL,
  created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 14. Audit Log
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS audit_log (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED DEFAULT NULL,
  user_email  VARCHAR(191) DEFAULT NULL,
  action      ENUM('created','updated','deleted','login','logout','exported','status_changed') NOT NULL,
  entity_type VARCHAR(64)  DEFAULT NULL,
  entity_id   VARCHAR(64)  DEFAULT NULL,
  old_value   JSON DEFAULT NULL,
  new_value   JSON DEFAULT NULL,
  ip_address  VARCHAR(45)  DEFAULT NULL,
  user_agent  TEXT DEFAULT NULL,
  performed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_entity (entity_type, entity_id),
  INDEX idx_user (user_id),
  INDEX idx_performed_at (performed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
