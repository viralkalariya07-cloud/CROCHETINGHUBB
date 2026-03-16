<?php
session_name('SELLER_SESSION');
session_start();

/* ================= DATABASE CONNECTION ================= */
include("../includes/db.php");

/* ================= LOGIN CHECK ================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../user/login.php");
    exit();
}
$seller_id = $_SESSION['user_id'];

/* ================= ENSURE TABLE EXISTS ================= */
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS seller_notifications (
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

/* ================= FETCH NOTIFICATIONS ================= */
$stmt = mysqli_prepare($conn, "SELECT * FROM seller_notifications WHERE seller_id = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt, "i", $seller_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$notifications = [];
while ($row = mysqli_fetch_assoc($result)) {
    $notifications[] = $row;
}

/* ================= MARK ALL AS READ ================= */
mysqli_query($conn, "UPDATE seller_notifications SET is_read = 1 WHERE seller_id = '$seller_id' AND is_read = 0");

/* ================= DELETE single notification ================= */
if (isset($_POST['delete_notif_id'])) {
    $del_id = intval($_POST['delete_notif_id']);
    $del_stmt = mysqli_prepare($conn, "DELETE FROM seller_notifications WHERE id = ? AND seller_id = ?");
    mysqli_stmt_bind_param($del_stmt, "ii", $del_id, $seller_id);
    mysqli_stmt_execute($del_stmt);
    header("Location: notifications.php");
    exit();
}

/* ================= CLEAR ALL ================= */
if (isset($_POST['clear_all'])) {
    mysqli_query($conn, "DELETE FROM seller_notifications WHERE seller_id = '$seller_id'");
    header("Location: notifications.php");
    exit();
}
?>
<?php include("../includes/seller_header.php"); ?>

<style>
    .notif-page-wrap {
        max-width: 820px;
        margin: 48px auto 60px;
        padding: 0 16px;
    }

    .notif-page-title {
        font-size: 2rem;
        font-weight: 800;
        color: var(--primary-pink);
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 6px;
    }

    .notif-subtitle {
        color: #888;
        font-size: 0.95rem;
        margin-bottom: 28px;
    }

    .notif-card {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 6px 28px rgba(216, 49, 116, 0.07);
        overflow: hidden;
        border: 1.5px solid #ffe5ef;
        margin-bottom: 14px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        display: flex;
        align-items: stretch;
        position: relative;
    }

    .notif-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 36px rgba(216, 49, 116, 0.14);
    }

    .notif-card.unread {
        border-left: 4px solid var(--primary-pink);
        background: linear-gradient(to right, #fff5f9 0%, #fff 100%);
    }

    .notif-card.read {
        border-left: 4px solid #e8e8e8;
        opacity: 0.88;
    }

    .notif-icon-col {
        background: linear-gradient(135deg, var(--primary-pink), #ff85a1);
        width: 70px;
        min-width: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.7rem;
        color: white;
        flex-shrink: 0;
    }

    .notif-body {
        flex: 1;
        padding: 16px 18px;
    }

    .notif-title {
        font-weight: 700;
        font-size: 1rem;
        color: #2a2a2a;
        margin-bottom: 4px;
    }

    .notif-title span.order-id {
        color: var(--primary-pink);
    }

    .notif-meta {
        font-size: 0.82rem;
        color: #888;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .notif-meta .badge-pill {
        background: #fff0f5;
        color: var(--deep-pink);
        border-radius: 30px;
        padding: 3px 10px;
        font-weight: 600;
        font-size: 0.78rem;
        border: 1px solid #ffd0e4;
    }

    .notif-price {
        font-weight: 800;
        color: var(--primary-pink);
        font-size: 1.1rem;
    }

    .notif-time {
        color: #bbb;
        font-size: 0.75rem;
        margin-top: 6px;
    }

    .notif-actions {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 14px 14px;
        gap: 8px;
        min-width: 80px;
    }

    .btn-view-order {
        background: var(--gradient-pink);
        color: white;
        font-size: 0.73rem;
        font-weight: 700;
        border: none;
        border-radius: 30px;
        padding: 5px 12px;
        text-decoration: none;
        white-space: nowrap;
        transition: box-shadow 0.2s;
    }
    .btn-view-order:hover {
        box-shadow: 0 4px 14px rgba(216,49,116,0.3);
        color: white;
    }

    .btn-delete-notif {
        background: transparent;
        color: #ccc;
        border: 1.5px solid #eee;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-delete-notif:hover {
        color: #e74c3c;
        border-color: #e74c3c;
        background: #fff5f5;
    }

    .new-badge {
        position: absolute;
        top: 10px;
        right: 90px;
        background: var(--primary-pink);
        color: white;
        font-size: 0.62rem;
        font-weight: 800;
        padding: 2px 8px;
        border-radius: 30px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        animation: pulse-notif 1.5s ease infinite;
    }
    @keyframes pulse-notif {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }

    .empty-box {
        text-align: center;
        padding: 70px 20px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        border: 2px dashed #ffd1e1;
    }
    .empty-box .empty-icon { font-size: 4rem; margin-bottom: 14px; }
    .empty-box h4 { color: var(--primary-pink); font-weight: 700; }
    .empty-box p { color: #aaa; font-size: 0.95rem; }

    .notif-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .btn-clear-all {
        background: #fff;
        border: 1.5px solid #e74c3c;
        color: #e74c3c;
        border-radius: 30px;
        padding: 6px 18px;
        font-size: 0.82rem;
        font-weight: 700;
        transition: all 0.2s;
    }
    .btn-clear-all:hover {
        background: #e74c3c;
        color: white;
    }

    .notif-count-badge {
        background: var(--light-pink);
        color: var(--primary-pink);
        font-size: 0.82rem;
        font-weight: 700;
        border-radius: 30px;
        padding: 4px 14px;
        border: 1.5px solid #ffd1e1;
    }
</style>

<div class="notif-page-wrap">

    <h1 class="notif-page-title">
        <i class="bi bi-bell-fill"></i>
        Notifications
    </h1>
    <p class="notif-subtitle">You'll be notified here every time a customer places an order for your products.</p>

    <div class="notif-toolbar">
        <span class="notif-count-badge">
            <?php $total = count($notifications); echo $total . " notification" . ($total !== 1 ? "s" : ""); ?>
        </span>
        <?php if (!empty($notifications)): ?>
        <form method="POST" onsubmit="return confirm('Clear all notifications?')">
            <button type="submit" name="clear_all" class="btn-clear-all">
                <i class="bi bi-trash me-1"></i> Clear All
            </button>
        </form>
        <?php endif; ?>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="empty-box">
            <div class="empty-icon">🔔</div>
            <h4>No Notifications Yet</h4>
            <p>When a customer places an order for your product, it will appear here.</p>
            <a href="sellerorder.php" class="btn btn-sm btn-outline-secondary mt-2 rounded-pill px-4">View My Orders</a>
        </div>
    <?php else: ?>

        <?php foreach ($notifications as $notif): ?>
        <div class="notif-card <?php echo $notif['is_read'] == 0 ? 'unread' : 'read'; ?>">

            <?php if ($notif['is_read'] == 0): ?>
            <span class="new-badge">NEW</span>
            <?php endif; ?>

            <div class="notif-icon-col">
                🛍️
            </div>

            <div class="notif-body">
                <div class="notif-title">
                    New Order <span class="order-id">#<?php echo $notif['order_id']; ?></span>
                    from <strong><?php echo htmlspecialchars($notif['customer_name']); ?></strong>
                </div>
                <div class="notif-meta mt-2">
                    <span>📦 <?php echo htmlspecialchars($notif['product_name']); ?></span>
                    <span class="badge-pill"><?php echo strtoupper(htmlspecialchars($notif['payment_method'])); ?></span>
                </div>
                <div class="notif-price mt-2">₹<?php echo number_format($notif['total_price'], 2); ?></div>
                <div class="notif-time">
                    <i class="bi bi-clock me-1"></i>
                    <?php
                        $now = new DateTime();
                        $created = new DateTime($notif['created_at']);
                        $diff = $now->diff($created);
                        if ($diff->days > 0) echo $diff->days . "d ago";
                        elseif ($diff->h > 0) echo $diff->h . "h ago";
                        elseif ($diff->i > 0) echo $diff->i . "m ago";
                        else echo "Just now";
                    ?>
                </div>
            </div>

            <div class="notif-actions">
                <a href="sellerorder.php" class="btn-view-order">
                    <i class="bi bi-eye me-1"></i> View
                </a>
                <form method="POST">
                    <input type="hidden" name="delete_notif_id" value="<?php echo $notif['id']; ?>">
                    <button type="submit" class="btn-delete-notif" title="Dismiss">
                        <i class="bi bi-x"></i>
                    </button>
                </form>
            </div>

        </div>
        <?php endforeach; ?>

    <?php endif; ?>

</div>

<?php include("../includes/seller_footer.php"); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
