<?php
/* ============================================================
   CrochetingHubb  –  One-Click Table Installer
   Open in browser: http://localhost:3307/CROCHETINGHUBB/install_tables.php
   (or whatever port XAMPP Apache uses — usually 80)
   URL: http://localhost/CROCHETINGHUBB/install_tables.php
   ============================================================ */

// Same connection as register.php
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);

$results = [];

if (!$conn) {
    die("<h2 style='color:red'>❌ DB Connection Failed: " . mysqli_connect_error() . "</h2>
         <p>Make sure XAMPP MySQL is running on port 3307.</p>");
}

// ── Helper ────────────────────────────────────────────────────
function run($conn, $sql, $label, &$results) {
    if (mysqli_query($conn, $sql)) {
        $results[] = ['ok' => true,  'label' => $label];
    } else {
        $results[] = ['ok' => false, 'label' => $label, 'err' => mysqli_error($conn)];
    }
}

// ── 1. USERS ─────────────────────────────────────────────────
run($conn, "CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name`  VARCHAR(150)     NOT NULL,
  `email`      VARCHAR(200)     NOT NULL,
  `password`   VARCHAR(255)     NOT NULL,
  `role`       ENUM('user','seller','admin') NOT NULL DEFAULT 'user',
  `created_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "users", $results);

// Ensure password column is long enough
run($conn, "ALTER TABLE `users` MODIFY COLUMN `password` VARCHAR(255) NOT NULL",
    "users → password VARCHAR(255)", $results);

// ── 2. SELLER PRODUCTS ───────────────────────────────────────
run($conn, "CREATE TABLE IF NOT EXISTS `sellerproducts` (
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
  KEY `idx_seller` (`seller_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "sellerproducts", $results);

// ── 3. CART ──────────────────────────────────────────────────
run($conn, "CREATE TABLE IF NOT EXISTS `cart` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT(11) UNSIGNED NOT NULL,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `quantity`   INT(11)          NOT NULL DEFAULT 1,
  `added_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cart_user`    (`user_id`),
  KEY `idx_cart_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "cart", $results);

// ── 4. ORDERS (orderss) ──────────────────────────────────────
run($conn, "CREATE TABLE IF NOT EXISTS `orderss` (
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
  KEY `idx_order_customer` (`customer_id`),
  KEY `idx_order_seller`   (`seller_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "orderss", $results);

// Ensure upi_id column exists (for older installations)
$col = mysqli_query($conn, "SHOW COLUMNS FROM `orderss` LIKE 'upi_id'");
if ($col && mysqli_num_rows($col) == 0) {
    run($conn, "ALTER TABLE `orderss` ADD COLUMN `upi_id` VARCHAR(100) DEFAULT NULL AFTER `payment_method`",
        "orderss → add upi_id column", $results);
} else {
    $results[] = ['ok' => true, 'label' => 'orderss → upi_id column already exists'];
}

// ── 5. USER PAYMENTS ─────────────────────────────────────────
run($conn, "CREATE TABLE IF NOT EXISTS `user_payments` (
  `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`        INT(11) UNSIGNED NOT NULL,
  `payment_method` VARCHAR(50)      NOT NULL,
  `upi_id`         VARCHAR(100)     DEFAULT NULL,
  `created_at`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pay_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "user_payments", $results);

// ── 6. FEEDBACK ──────────────────────────────────────────────
run($conn, "CREATE TABLE IF NOT EXISTS `feedback` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT(11) UNSIGNED NOT NULL,
  `product_id` INT(11) UNSIGNED DEFAULT NULL,
  `rating`     TINYINT(1)       NOT NULL DEFAULT 5,
  `comment`    TEXT             DEFAULT NULL,
  `created_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fb_user`    (`user_id`),
  KEY `idx_fb_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "feedback", $results);

// ── 7. CATEGORIES ────────────────────────────────────────────
run($conn, "CREATE TABLE IF NOT EXISTS `categories` (
  `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100)     NOT NULL,
  `description` TEXT             DEFAULT NULL,
  `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cat_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "categories", $results);

// Seed default categories
$default_cats = ['Bags','Hats','Scarves','Toys','Home Decor','Clothing','Accessories','Blankets','Jewelry','Other'];
foreach ($default_cats as $cat) {
    $safe = mysqli_real_escape_string($conn, $cat);
    mysqli_query($conn, "INSERT IGNORE INTO `categories` (`name`) VALUES ('$safe')");
}
$results[] = ['ok' => true, 'label' => 'categories → seeded 10 default categories'];

// ── 8. TUTORIALS ─────────────────────────────────────────────
run($conn, "CREATE TABLE IF NOT EXISTS `tutorials` (
  `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(200)     NOT NULL,
  `seller_name` VARCHAR(150)     NOT NULL,
  `video_link`  VARCHAR(500)     NOT NULL,
  `photo`       VARCHAR(300)     NOT NULL,
  `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "tutorials", $results);

// ── 9. WEBSITE SETTINGS ──────────────────────────────────────
run($conn, "CREATE TABLE IF NOT EXISTS `website_settings` (
  `id`              INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `website_name`    VARCHAR(150)     NOT NULL DEFAULT 'CrochetingHubb',
  `support_email`   VARCHAR(200)     DEFAULT 'support@crochetinghub.com',
  `support_phone`   VARCHAR(50)      DEFAULT '',
  `currency`        VARCHAR(10)      NOT NULL DEFAULT 'INR',
  `gst_percentage`  DECIMAL(5,2)     NOT NULL DEFAULT 0.00,
  `shipping_charge` DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
  `updated_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "website_settings", $results);

mysqli_query($conn, "INSERT IGNORE INTO `website_settings` (`id`, `website_name`) VALUES (1, 'CrochetingHubb')");
$results[] = ['ok' => true, 'label' => 'website_settings → seeded default row'];

// ── 10. SELLER CONTACTS ──────────────────────────────────────
run($conn, "CREATE TABLE IF NOT EXISTS `seller_contacts` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `seller_id`  INT(11) UNSIGNED NOT NULL,
  `name`       VARCHAR(150)     NOT NULL,
  `email`      VARCHAR(200)     NOT NULL,
  `message`    TEXT             NOT NULL,
  `created_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "seller_contacts", $results);

mysqli_close($conn);

// ── Show existing tables ──────────────────────────────────────
$conn2 = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);
$tables_list = [];
if ($conn2) {
    $t = mysqli_query($conn2, "SHOW TABLES");
    while ($r = mysqli_fetch_row($t)) $tables_list[] = $r[0];
    mysqli_close($conn2);
}

$ok_count  = count(array_filter($results, fn($r) => $r['ok']));
$err_count = count(array_filter($results, fn($r) => !$r['ok']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CrochetingHubb – DB Installer</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { background: #fff5f8; font-family: 'Segoe UI', sans-serif; padding: 40px; }
  h1  { color: #e91e8c; font-weight: 800; }
  .card { border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
  .badge-ok  { background: #d1fae5; color: #065f46; }
  .badge-err { background: #fee2e2; color: #991b1b; }
  .table-chip { background:#fff0f8; border-radius:6px; padding:4px 10px; display:inline-block; margin:3px; font-size:.85rem; font-weight:600; color:#c2185b; }
</style>
</head>
<body>
<div class="container" style="max-width:800px">
  <h1>🧶 CrochetingHubb – DB Installer</h1>
  <p class="text-muted">Connection: <code>127.0.0.1 : 3307</code> → database <strong>crochetinghubb</strong></p>

  <div class="card p-4 mb-4">
    <h5 class="mb-3">📋 Results</h5>
    <ul class="list-unstyled mb-0">
      <?php foreach ($results as $r): ?>
        <li class="mb-1">
          <?php if ($r['ok']): ?>
            <span class="badge badge-ok me-2">✓ OK</span>
          <?php else: ?>
            <span class="badge badge-err me-2">✗ ERROR</span>
          <?php endif; ?>
          <strong><?= htmlspecialchars($r['label']) ?></strong>
          <?php if (!$r['ok']): ?>
            <span class="text-danger small ms-2">(<?= htmlspecialchars($r['err'] ?? '') ?>)</span>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <?php if ($err_count === 0): ?>
    <div class="alert alert-success fw-bold">✅ All <?= $ok_count ?> operations completed successfully!</div>
  <?php else: ?>
    <div class="alert alert-warning">⚠️ <?= $ok_count ?> ok, <?= $err_count ?> error(s). See details above.</div>
  <?php endif; ?>

  <div class="card p-4">
    <h5>🗄️ Tables now in <em>crochetinghubb</em></h5>
    <div>
      <?php foreach ($tables_list as $t): ?>
        <span class="table-chip"><?= htmlspecialchars($t) ?></span>
      <?php endforeach; ?>
    </div>
  </div>

  <p class="mt-4 text-muted small">
    ⚠️ For security, delete or rename <code>install_tables.php</code> after running it.
  </p>
</div>
</body>
</html>
