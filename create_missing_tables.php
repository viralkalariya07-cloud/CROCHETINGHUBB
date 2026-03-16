<?php
/* ============================================================
   CrochetingHubb – Missing Tables Creator
   Open in browser: http://localhost/CROCHETINGHUBB/create_missing_tables.php
   This script creates all tables that were missing from the DB
   but are referenced in PHP code.
   ============================================================ */

$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);

$results = [];

if (!$conn) {
    die("<h2 style='color:red'>❌ DB Connection Failed: " . mysqli_connect_error() . "</h2>
         <p>Make sure XAMPP MySQL is running on port 3307.</p>");
}

// ── Helper ──────────────────────────────────────────────────
function run($conn, $sql, $label, &$results) {
    if (mysqli_query($conn, $sql)) {
        $results[] = ['ok' => true,  'label' => $label];
    } else {
        $results[] = ['ok' => false, 'label' => $label, 'err' => mysqli_error($conn)];
    }
}

// ── 1. USERACCOUNT ──────────────────────────────────────────
// Used in: useraccount.php, sellercontact.php, selleraccount.php, adminorders.php
run($conn, "CREATE TABLE IF NOT EXISTS `useraccount` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "useraccount", $results);

// ── 2. USER_ADDRESS ─────────────────────────────────────────
// Used in: useraccount.php, sellerorder.php, adminorders.php
run($conn, "CREATE TABLE IF NOT EXISTS `user_address` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "user_address", $results);

// ── 3. USER_FEEDBACK ────────────────────────────────────────
// Used in: userfeedback.php, useraccount.php, edit_feedback.php, delete_feedback.php
run($conn, "CREATE TABLE IF NOT EXISTS `user_feedback` (
  `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name`   VARCHAR(150)     NOT NULL,
  `email`       VARCHAR(200)     DEFAULT NULL,
  `feedback_id` VARCHAR(50)      DEFAULT NULL,
  `order_id`    VARCHAR(50)      DEFAULT NULL,
  `rating`      TINYINT(1)       NOT NULL DEFAULT 5,
  `message`     TEXT             DEFAULT NULL,
  `created_at`  TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "user_feedback", $results);

// ── 4. SELLER_FEEDBACK ──────────────────────────────────────
// Used in: seller.php, selleraccount.php, feedback.php
run($conn, "CREATE TABLE IF NOT EXISTS `seller_feedback` (
  `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `seller_id`     INT(11) UNSIGNED DEFAULT NULL,
  `customer_name` VARCHAR(150)     NOT NULL,
  `order_id`      VARCHAR(50)      DEFAULT NULL,
  `rating`        TINYINT(1)       NOT NULL DEFAULT 5,
  `feedback`      TEXT             DEFAULT NULL,
  `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sf_seller` (`seller_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "seller_feedback", $results);

// ── 5. SELLER_MESSAGES ──────────────────────────────────────
// Used in: useraccount.php, sellercontact.php
run($conn, "CREATE TABLE IF NOT EXISTS `seller_messages` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT(11) UNSIGNED NOT NULL,
  `seller_id`  INT(11) UNSIGNED NOT NULL,
  `message`    TEXT             NOT NULL,
  `is_read`    TINYINT(1)       NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sm_seller` (`seller_id`),
  KEY `idx_sm_user`   (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "seller_messages", $results);

// ── 6. SELLER_ADDRESS ───────────────────────────────────────
// Used in: selleraccount.php
run($conn, "CREATE TABLE IF NOT EXISTS `seller_address` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "seller_address", $results);

// ── 7. SELLERACCOUNT ────────────────────────────────────────
// Visible in phpMyAdmin; extended seller profile
run($conn, "CREATE TABLE IF NOT EXISTS `selleraccount` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "selleraccount", $results);

// ── 8. SELLERADDPRODUCTS ────────────────────────────────────
// Visible in phpMyAdmin; product add staging table for sellers
run($conn, "CREATE TABLE IF NOT EXISTS `selleraddproducts` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "selleraddproducts", $results);

// ── 9. USERCART ─────────────────────────────────────────────
// Visible in phpMyAdmin; alternative cart table
run($conn, "CREATE TABLE IF NOT EXISTS `usercart` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT(11) UNSIGNED NOT NULL,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `quantity`   INT(11)          NOT NULL DEFAULT 1,
  `added_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_uc_user`    (`user_id`),
  KEY `idx_uc_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "usercart", $results);

// ── 10. PRODUCT_IMAGES ──────────────────────────────────────
// Used in: selleraddproducts.php, productdetails.php
run($conn, "CREATE TABLE IF NOT EXISTS `product_images` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `image`      VARCHAR(300)     NOT NULL,
  `is_primary` TINYINT(1)       NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pi_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "product_images", $results);

// ── PATCHES: Add missing columns to existing tables ─────────
// categories → seller_id (used in selleraddproducts.php)
$col = mysqli_query($conn, "SHOW COLUMNS FROM `categories` LIKE 'seller_id'");
if ($col && mysqli_num_rows($col) == 0) {
    run($conn, "ALTER TABLE `categories` ADD COLUMN `seller_id` INT(11) UNSIGNED DEFAULT NULL",
        "categories → add seller_id column", $results);
} else {
    $results[] = ['ok' => true, 'label' => 'categories → seller_id column already exists'];
}

// sellerproducts → shipping_charges
$col2 = mysqli_query($conn, "SHOW COLUMNS FROM `sellerproducts` LIKE 'shipping_charges'");
if ($col2 && mysqli_num_rows($col2) == 0) {
    run($conn, "ALTER TABLE `sellerproducts` ADD COLUMN `shipping_charges` DECIMAL(10,2) NOT NULL DEFAULT 0.00",
        "sellerproducts → add shipping_charges column", $results);
} else {
    $results[] = ['ok' => true, 'label' => 'sellerproducts → shipping_charges column already exists'];
}

// sellerproducts → video_link
$col3 = mysqli_query($conn, "SHOW COLUMNS FROM `sellerproducts` LIKE 'video_link'");
if ($col3 && mysqli_num_rows($col3) == 0) {
    run($conn, "ALTER TABLE `sellerproducts` ADD COLUMN `video_link` VARCHAR(500) DEFAULT NULL",
        "sellerproducts → add video_link column", $results);
} else {
    $results[] = ['ok' => true, 'label' => 'sellerproducts → video_link column already exists'];
}

// ── Show all tables now in DB ────────────────────────────────
$tables_list = [];
$t = mysqli_query($conn, "SHOW TABLES");
while ($r = mysqli_fetch_row($t)) $tables_list[] = $r[0];
sort($tables_list);

$ok_count  = count(array_filter($results, fn($r) => $r['ok']));
$err_count = count(array_filter($results, fn($r) => !$r['ok']));
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CrochetingHubb – Missing Tables Creator</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { background: #fff5f8; font-family: 'Segoe UI', sans-serif; padding: 40px; }
  h1  { color: #e91e8c; font-weight: 800; }
  .card { border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: none; }
  .badge-ok  { background: #d1fae5; color: #065f46; padding: 4px 10px; border-radius: 20px; font-size: .8rem; font-weight: 600; }
  .badge-err { background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 20px; font-size: .8rem; font-weight: 600; }
  .table-chip { background:#fff0f8; border-radius:6px; padding:5px 12px; display:inline-block; margin:4px; font-size:.85rem; font-weight:600; color:#c2185b; border: 1px solid #f9c8e0; }
</style>
</head>
<body>
<div class="container" style="max-width:860px">
  <h1>🧶 CrochetingHubb – Missing Tables Creator</h1>
  <p class="text-muted">Connection: <code>127.0.0.1 : 3307</code> → database <strong>crochetinghubb</strong></p>

  <div class="card p-4 mb-4">
    <h5 class="mb-3">📋 Results</h5>
    <ul class="list-unstyled mb-0">
      <?php foreach ($results as $r): ?>
        <li class="mb-2 d-flex align-items-center gap-2">
          <?php if ($r['ok']): ?>
            <span class="badge-ok">✓ OK</span>
          <?php else: ?>
            <span class="badge-err">✗ ERROR</span>
          <?php endif; ?>
          <strong><?= htmlspecialchars($r['label']) ?></strong>
          <?php if (!$r['ok']): ?>
            <span class="text-danger small ms-1">(<?= htmlspecialchars($r['err'] ?? '') ?>)</span>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <?php if ($err_count === 0): ?>
    <div class="alert alert-success fw-bold rounded-3">
      ✅ All <?= $ok_count ?> operations completed successfully! All missing tables are now created.
    </div>
  <?php else: ?>
    <div class="alert alert-warning rounded-3">
      ⚠️ <?= $ok_count ?> ok, <?= $err_count ?> error(s). See details above.
    </div>
  <?php endif; ?>

  <div class="card p-4">
    <h5>🗄️ All tables now in <em>crochetinghubb</em> (<?= count($tables_list) ?> total)</h5>
    <div>
      <?php foreach ($tables_list as $tbl): ?>
        <span class="table-chip"><?= htmlspecialchars($tbl) ?></span>
      <?php endforeach; ?>
    </div>
  </div>

  <p class="mt-4 text-muted small">
    ⚠️ For security, delete or rename <code>create_missing_tables.php</code> after running it.
  </p>
</div>
</body>
</html>
