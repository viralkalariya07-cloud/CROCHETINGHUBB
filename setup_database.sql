-- ============================================================
--  CrochetingHubb  –  Full Database Setup Script
--  Run this in phpMyAdmin (SQL tab) or via MySQL CLI
--  MySQL port: 3307  |  Host: 127.0.0.1
-- ============================================================

CREATE DATABASE IF NOT EXISTS `crochetinghubb`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `crochetinghubb`;

-- ─────────────────────────────────────────────
--  1. USERS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name`  VARCHAR(150)     NOT NULL,
  `email`      VARCHAR(200)     NOT NULL UNIQUE,
  `password`   VARCHAR(255)     NOT NULL,
  `role`       ENUM('user','seller','admin') NOT NULL DEFAULT 'user',
  `created_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
--  2. SELLER PRODUCTS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `sellerproducts` (
  `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `seller_id`   INT(11) UNSIGNED NOT NULL,
  `name`        VARCHAR(200)     NOT NULL,
  `description` TEXT             DEFAULT NULL,
  `category`    VARCHAR(100)     DEFAULT NULL,
  `price`       DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
  `stock`       INT(11)          NOT NULL DEFAULT 0,
  `image`       VARCHAR(300)     DEFAULT NULL,
  `status`      ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_sp_seller` (`seller_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
--  3. CART
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `cart` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT(11) UNSIGNED NOT NULL,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `quantity`   INT(11)          NOT NULL DEFAULT 1,
  `added_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_cart_user`    (`user_id`),
  KEY `fk_cart_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
--  4. ORDERS  (table name used in code: orderss)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `orderss` (
  `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id`    INT(11) UNSIGNED NOT NULL,
  `product_id`     INT(11) UNSIGNED NOT NULL,
  `seller_id`      INT(11) UNSIGNED NOT NULL,
  `quantity`       INT(11)          NOT NULL DEFAULT 1,
  `total_price`    DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
  `payment_method` VARCHAR(50)      NOT NULL,
  `upi_id`         VARCHAR(100)     DEFAULT NULL,
  `status`         VARCHAR(50)      NOT NULL DEFAULT 'Pending',
  `order_date`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_order_customer` (`customer_id`),
  KEY `fk_order_product`  (`product_id`),
  KEY `fk_order_seller`   (`seller_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
--  5. USER PAYMENTS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `user_payments` (
  `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`        INT(11) UNSIGNED NOT NULL,
  `payment_method` VARCHAR(50)      NOT NULL,
  `upi_id`         VARCHAR(100)     DEFAULT NULL,
  `created_at`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_pay_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
--  6. FEEDBACK / REVIEWS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `feedback` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT(11) UNSIGNED NOT NULL,
  `product_id` INT(11) UNSIGNED DEFAULT NULL,
  `rating`     TINYINT(1)       NOT NULL DEFAULT 5,
  `comment`    TEXT             DEFAULT NULL,
  `created_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_fb_user`    (`user_id`),
  KEY `fk_fb_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
--  7. CATEGORIES
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `categories` (
  `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100)     NOT NULL UNIQUE,
  `description` TEXT             DEFAULT NULL,
  `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default categories
INSERT IGNORE INTO `categories` (`name`) VALUES
  ('Bags'), ('Hats'), ('Scarves'), ('Toys'), ('Home Decor'),
  ('Clothing'), ('Accessories'), ('Blankets'), ('Jewelry'), ('Other');

-- ─────────────────────────────────────────────
--  8. TUTORIALS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tutorials` (
  `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(200)     NOT NULL,
  `seller_name` VARCHAR(150)     NOT NULL,
  `video_link`  VARCHAR(500)     NOT NULL,
  `photo`       VARCHAR(300)     NOT NULL,
  `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────
--  9. WEBSITE SETTINGS
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `website_settings` (
  `id`              INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `website_name`    VARCHAR(150)     NOT NULL DEFAULT 'CrochetingHubb',
  `support_email`   VARCHAR(200)     DEFAULT 'support@crochetinghub.com',
  `support_phone`   VARCHAR(50)      DEFAULT '',
  `currency`        VARCHAR(10)      NOT NULL DEFAULT 'INR',
  `gst_percentage`  DECIMAL(5,2)     NOT NULL DEFAULT 0.00,
  `shipping_charge` DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
  `updated_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed one default row
INSERT IGNORE INTO `website_settings` (`id`, `website_name`) VALUES (1, 'CrochetingHubb');

-- ─────────────────────────────────────────────
--  10. SELLER CONTACT MESSAGES
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `seller_contacts` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `seller_id`  INT(11) UNSIGNED NOT NULL,
  `name`       VARCHAR(150)     NOT NULL,
  `email`      VARCHAR(200)     NOT NULL,
  `message`    TEXT             NOT NULL,
  `created_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ✅  All tables created successfully!
-- ============================================================
