<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('SELLER_SESSION');
    session_start();
}
// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    $login_path = (basename(dirname($_SERVER['PHP_SELF'])) == 'seller') ? "../user/login.php" : "user/login.php";
    header("Location: $login_path");
    exit();
}

// Logic to handle links correctly from both root and subfolder
$is_in_seller_folder = (basename(dirname($_SERVER['PHP_SELF'])) == 'seller');
$dashboard_link = $is_in_seller_folder ? "../seller.php" : "seller.php";
$seller_folder_prefix = $is_in_seller_folder ? "" : "seller/";
$tutorial_link = $is_in_seller_folder ? "../tutorial.php" : "tutorial.php";

// Fetch unread notification count for this seller
$notif_count = 0;
if (isset($_SESSION['user_id'])) {
    $notif_conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);
    if ($notif_conn) {
        // Make sure table exists
        mysqli_query($notif_conn, "CREATE TABLE IF NOT EXISTS seller_notifications (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            seller_id INT(11) NOT NULL,
            order_id INT(11) NOT NULL,
            customer_name VARCHAR(150) DEFAULT 'Customer',
            product_name VARCHAR(255) DEFAULT '',
            total_price DECIMAL(10,2) NOT NULL DEFAULT 0,
            payment_method VARCHAR(50) DEFAULT '',
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $nc_stmt = mysqli_prepare($notif_conn, "SELECT COUNT(*) as cnt FROM seller_notifications WHERE seller_id = ? AND is_read = 0");
        if ($nc_stmt) {
            $sid = $_SESSION['user_id'];
            mysqli_stmt_bind_param($nc_stmt, "i", $sid);
            mysqli_stmt_execute($nc_stmt);
            $nc_result = mysqli_stmt_get_result($nc_stmt);
            $nc_row = mysqli_fetch_assoc($nc_result);
            $notif_count = (int)($nc_row['cnt'] ?? 0);
            mysqli_stmt_close($nc_stmt);
        }
        mysqli_close($notif_conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-pink: #d83174;
            --deep-pink: #b51d5b;
            --light-pink: #fff5f8;
            --soft-pink: #ffd1e1;
            --gradient-pink: linear-gradient(135deg, #d83174 0%, #ff85a1 100%);
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-pink);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .navbar-seller {
            background: var(--gradient-pink);
            box-shadow: 0 4px 15px rgba(216, 49, 116, 0.2);
            padding: 0.8rem 0; /* Standardized with user side */
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .navbar-seller .navbar-brand {
            font-weight: 800;
            color: white;
            font-size: 1.8rem;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar-seller .nav-link {
            color: rgba(255, 255, 255, 0.95) !important;
            font-weight: 600;
            transition: 0.3s;
            padding: 0.5rem 1.2rem;
            font-size: 1rem;
            position: relative;
        }

        .navbar-seller .nav-link:hover {
            color: white !important;
            transform: translateY(-2px);
        }

        .navbar-seller .nav-link i {
            margin-right: 6px;
            font-size: 1.1rem;
        }

        /* Premium Pink Decorative Bar */
        .pink-decorative-bar {
            height: 10px;
            background: linear-gradient(90deg, 
                #d83174 0%, 
                #b51d5b 25%, 
                #ff85a1 50%, 
                #b51d5b 75%, 
                #d83174 100%
            );
            background-size: 200% auto;
            width: 100%;
            box-shadow: 0 4px 10px rgba(216, 49, 116, 0.2);
            position: relative;
            z-index: 10;
            animation: shimmer 4s linear infinite;
        }

        @keyframes shimmer {
            0% { background-position: 0% center; }
            100% { background-position: 200% center; }
        }

        /* Notification Bell */
        .notif-bell-btn {
            background: rgba(255,255,255,0.18);
            border: 1.5px solid rgba(255,255,255,0.35);
            border-radius: 50px;
            padding: 6px 14px;
            color: white !important;
            font-size: 1.1rem;
            position: relative;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .notif-bell-btn:hover {
            background: rgba(255,255,255,0.32);
            transform: translateY(-2px);
            box-shadow: 0 4px 14px rgba(0,0,0,0.18);
        }
        .notif-bell-btn .bi-bell-fill {
            animation: bell-ring 2.5s ease infinite;
            transform-origin: top center;
            display: inline-block;
        }
        @keyframes bell-ring {
            0%,100% { transform: rotate(0deg); }
            10% { transform: rotate(12deg); }
            20% { transform: rotate(-12deg); }
            30% { transform: rotate(8deg); }
            40% { transform: rotate(-8deg); }
            50% { transform: rotate(0deg); }
        }
        .notif-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #fff;
            color: var(--primary-pink);
            font-size: 0.65rem;
            font-weight: 800;
            min-width: 18px;
            height: 18px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--deep-pink);
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
            line-height: 1;
        }
        .notif-badge.hidden { display: none; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-seller sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?php echo $dashboard_link; ?>">🧶 CrochetingHubb</a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sellerNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="sellerNavbar">
            <ul class="navbar-nav ms-auto align-items-center">
                <?php if (!isset($hide_nav) || !$hide_nav): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $dashboard_link; ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $seller_folder_prefix; ?>sellerproducts.php"><i class="bi bi-bag-heart"></i> Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $seller_folder_prefix; ?>category.php"><i class="bi bi-tags"></i> Categories</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $seller_folder_prefix; ?>sellerorder.php"><i class="bi bi-box-seam"></i> Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $seller_folder_prefix; ?>feedback.php"><i class="bi bi-star"></i> Feedback</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $seller_folder_prefix; ?>sellercontact.php"><i class="bi bi-envelope"></i> Messages</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $tutorial_link; ?>"><i class="bi bi-journal-text"></i> Tutorials</a>
                </li>
                <?php endif; ?>
                <li class="nav-item ms-lg-2">
                    <a href="<?php echo $seller_folder_prefix; ?>notifications.php" class="notif-bell-btn ms-lg-1" title="Notifications" id="sellerNotifBell">
                        <i class="bi bi-bell-fill"></i>
                        <?php if ($notif_count > 0): ?>
                        <span class="notif-badge" id="notifBadge"><?php echo $notif_count > 99 ? '99+' : $notif_count; ?></span>
                        <?php else: ?>
                        <span class="notif-badge hidden" id="notifBadge"></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo $seller_folder_prefix; ?>selleraccount.php" class="nav-link fs-5 ms-lg-2" title="My Account">
                        <i class="bi bi-person-circle"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- PINK HORIZONTAL RECTANGLE (DECORATIVE BAR) -->
<div class="pink-decorative-bar"></div>
