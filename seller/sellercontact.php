<?php
// ======================
// DATABASE CONNECTION
// ======================
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Mark as read logic
if (isset($_GET['mark_read'])) {
    $msg_id = (int)$_GET['mark_read'];
    mysqli_query($conn, "UPDATE seller_messages SET is_read = 1 WHERE id = $msg_id");
    header("Location: sellercontact.php");
    exit();
}

// Fetch messages
$sql = "SELECT m.*, u.full_name, u.email 
        FROM seller_messages m 
        JOIN useraccount u ON m.user_id = u.id 
        ORDER BY m.created_at DESC";
$result = mysqli_query($conn, $sql);

$is_included = debug_backtrace(); // Simple check for inclusion
?>
<?php if (!$is_included): ?>
<?php include("../includes/seller_header.php"); ?>
<title>User Messages – Seller Panel – Crocheting Hub</title>
<style>
    :root {
        --primary-pink: #d83174;
        --soft-pink: #ffd1e1;
        --deep-pink: #b51d5b;
        --light-bg: #fff5f8;
        --gradient-pink: linear-gradient(135deg, #d83174 0%, #ff85a1 100%);
    }
    body { margin: 0; font-family: 'Segoe UI', system-ui, sans-serif; background-color: var(--light-bg); display: flex; flex-direction: column; min-vh-100; }
    .section-title { color: var(--primary-pink); font-weight: 800; font-size: 2.2rem; }
    .premium-footer { background: var(--gradient-pink); color: #fff; padding: 40px 0 20px; margin-top: auto; }
    .contact-hero { background: linear-gradient(180deg, rgba(216, 49, 116, 0.06) 0%, transparent 100%); border-bottom: 1px solid rgba(216, 49, 116, 0.1); padding: 2rem 0 1.6rem; text-align: center; }
    .message-card { background: #fff; border: 1px solid rgba(216, 49, 116, 0.12); border-radius: 16px; padding: 1.5rem; margin-bottom: 1.2rem; transition: 0.2s; box-shadow: 0 2px 12px rgba(216, 49, 116, 0.06); }
    .message-card--unread { border-left: 4px solid var(--primary-pink); }
    .unread-badge { background: var(--gradient-pink); color: #fff; font-size: 0.68rem; padding: 2px 8px; border-radius: 10px; }
    .btn-pink { background: var(--gradient-pink); color: white !important; border-radius: 10px; padding: 5px 15px; border: none; }
</style>


<main>
    <section class="contact-hero">
        <div class="container">
            <h1 class="section-title">User Messages 📩</h1>
            <p class="contact-subtitle">Messages received from customers</p>
        </div>
    </section>
<?php endif; ?>

    <!-- Messages Content (Common for both standalone and included) -->
    <section class="messages-section py-4">
        <div class="container">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <div class="messages-container">
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <div class="message-card <?php echo $row['is_read'] ? 'message-card--read' : 'message-card--unread'; ?>">
                            <div class="d-flex justify-content-between border-bottom pb-2 mb-3">
                                <div class="user-info">
                                    <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($row['full_name']); ?></h6>
                                    <small class="text-muted"><i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($row['email']); ?></small>
                                </div>
                                <?php if (!$row['is_read']): ?>
                                    <span class="badge bg-danger">New</span>
                                <?php endif; ?>
                            </div>

                            <div class="message-body mb-3">
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($row['message'])); ?></p>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted"><i class="bi bi-clock me-1"></i><?php echo date("M d, Y • h:i A", strtotime($row['created_at'])); ?></small>
                                <?php if (!$row['is_read']): ?>
                                    <a href="?mark_read=<?php echo $row['id']; ?>" class="btn btn-sm btn-pink">Mark as Read</a>
                                <?php else: ?>
                                    <span class="text-success small"><i class="bi bi-check-circle me-1"></i>Read</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-4 text-muted"></i>
                    <p class="mt-3 text-muted">No messages received yet</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

<?php if (!$is_included): ?>
</main>

<?php include("../includes/seller_footer.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php endif; ?>

