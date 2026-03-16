-- ============================================================
--  CrochetingHubb – Missing Tables Creator
--  Run this in phpMyAdmin (SQL tab) on the crochetinghubb database
--  This script creates all tables found in PHP code but missing
--  from the original install_tables.php
-- ============================================================

USE `crochetinghubb`;

-- ─────────────────────────────────────────────
--  1. USERACCOUNT  (user profile details)
--     Used in: useraccount.php, sellercontact.php,
--              selleraccount.php, adminorders.php
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `useraccount` (
  `id`         INT(11) UNSIGNED NOT NULL,
  `full_name`  VARCHAR(150)     DEFAULT NULL,
  `email`      VARCHAR(200)     DEFAULT NULL,
  `mobile`     VARCHAR(20)      DEFAULT '',
  `gender`     VARCHAR(10)      DEFAULT '',
  `city`       VARCHAR(100)     DEFAULT '',
  `bio`        TEXT             DEFAULT NULL,
  `link`       VARCHAR(255)     DEFAULT '',
  `created_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
--  2. USER_ADDRESS  (delivery / shipping address)
--     Used in: useraccount.php, sellerorder.php, adminorders.php
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `user_address` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT(11) UNSIGNED NOT NULL,
  `full_name`  VARCHAR(150)     NOT NULL,
  `mobile`     VARCHAR(20)      NOT NULL,
  `street`     TEXT             NOT NULL,
  `city`       VARCHAR(100)     NOT NULL,
  `state`      VARCHAR(100)     NOT NULL,
  `pincode`    VARCHAR(10)      NOT NULL,
  `created_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ua_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
--  3. USER_FEEDBACK  (feedback submitted by users)
--     Used in: userfeedback.php, useraccount.php,
--              edit_feedback.php, delete_feedback.php
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `user_feedback` (
  `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name`   VARCHAR(150)     NOT NULL,
  `email`       VARCHAR(200)     DEFAULT NULL,
  `feedback_id` VARCHAR(50)      DEFAULT NULL,
  `order_id`    VARCHAR(50)      DEFAULT NULL,
  `rating`      TINYINT(1)       NOT NULL DEFAULT 5,
  `message`     TEXT             DEFAULT NULL,
  `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
--  4. SELLER_FEEDBACK  (feedback for sellers)
--     Used in: seller.php, selleraccount.php, feedback.php
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `seller_feedback` (
  `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `seller_id`     INT(11) UNSIGNED DEFAULT NULL,
  `customer_name` VARCHAR(150)     NOT NULL,
  `order_id`      VARCHAR(50)      DEFAULT NULL,
  `rating`        TINYINT(1)       NOT NULL DEFAULT 5,
  `feedback`      TEXT             DEFAULT NULL,
  `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sf_seller` (`seller_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
--  5. SELLER_MESSAGES  (messages from users to sellers)
--     Used in: useraccount.php, sellercontact.php, create_table.php
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `seller_messages` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT(11) UNSIGNED NOT NULL,
  `seller_id`  INT(11) UNSIGNED NOT NULL,
  `message`    TEXT             NOT NULL,
  `is_read`    TINYINT(1)       NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sm_seller` (`seller_id`),
  KEY `idx_sm_user`   (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
--  6. SELLER_ADDRESS  (seller's own address)
--     Used in: selleraccount.php
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `seller_address` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `seller_id`  INT(11) UNSIGNED NOT NULL,
  `full_name`  VARCHAR(150)     DEFAULT NULL,
  `mobile`     VARCHAR(20)      DEFAULT NULL,
  `street`     TEXT             DEFAULT NULL,
  `city`       VARCHAR(100)     DEFAULT NULL,
  `state`      VARCHAR(100)     DEFAULT NULL,
  `pincode`    VARCHAR(10)      DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sa_seller` (`seller_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
--  7. SELLERACCOUNT  (extended seller profile)
--     Visible in phpMyAdmin; referenced for seller info
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `selleraccount` (
  `id`         INT(11) UNSIGNED NOT NULL,
  `full_name`  VARCHAR(150)     DEFAULT NULL,
  `email`      VARCHAR(200)     DEFAULT NULL,
  `mobile`     VARCHAR(20)      DEFAULT '',
  `gender`     VARCHAR(10)      DEFAULT '',
  `city`       VARCHAR(100)     DEFAULT '',
  `bio`        TEXT             DEFAULT NULL,
  `link`       VARCHAR(255)     DEFAULT '',
  `created_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
--  8. SELLERADDPRODUCTS  (visible in phpMyAdmin)
--     Stores temporarily-staged product additions;
--     mirrors sellerproducts for quick reference
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `selleraddproducts` (
  `id`               INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `seller_id`        INT(11) UNSIGNED NOT NULL,
  `name`             VARCHAR(200)     NOT NULL,
  `description`      TEXT             DEFAULT NULL,
  `category`         VARCHAR(100)     DEFAULT NULL,
  `price`            DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
  `stock`            INT(11)          NOT NULL DEFAULT 0,
  `shipping_charges` DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
  `image`            VARCHAR(300)     DEFAULT NULL,
  `video_link`       VARCHAR(500)     DEFAULT NULL,
  `status`           VARCHAR(50)      NOT NULL DEFAULT 'active',
  `created_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sap_seller` (`seller_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
--  9. USERCART  (visible in phpMyAdmin)
--     Alternative/alias cart table used in some pages
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `usercart` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT(11) UNSIGNED NOT NULL,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `quantity`   INT(11)          NOT NULL DEFAULT 1,
  `added_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_uc_user`    (`user_id`),
  KEY `idx_uc_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- 10. PRODUCT_IMAGES  (multiple images per product)
--     Used in: selleraddproducts.php, productdetails.php
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `product_images` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `image`      VARCHAR(300)     NOT NULL,
  `is_primary` TINYINT(1)       NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pi_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
-- Patch: ensure categories table has seller_id column
-- (used in selleraddproducts.php)
-- ─────────────────────────────────────────────
ALTER TABLE `categories` ADD COLUMN IF NOT EXISTS `seller_id` INT(11) UNSIGNED DEFAULT NULL;

-- ─────────────────────────────────────────────
-- Patch: ensure sellerproducts has shipping_charges & video_link columns
-- (used in selleraddproducts.php)
-- ─────────────────────────────────────────────
ALTER TABLE `sellerproducts` ADD COLUMN IF NOT EXISTS `shipping_charges` DECIMAL(10,2) NOT NULL DEFAULT 0.00;
ALTER TABLE `sellerproducts` ADD COLUMN IF NOT EXISTS `video_link` VARCHAR(500) DEFAULT NULL;

-- ============================================================
-- ✅  All missing tables created successfully!
-- Run this SQL in: phpMyAdmin → crochetinghubb → SQL tab
-- ============================================================
