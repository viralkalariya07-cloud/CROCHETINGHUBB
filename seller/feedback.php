<?php
// ================= DATABASE CONNECTION =================
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ================= INSERT FEEDBACK =================
if (isset($_POST['submit_feedback'])) {
    $name = $_POST['customer_name'];
    $order_id = $_POST['order_id'];
    $rating = $_POST['rating'];
    $feedback = $_POST['feedback'];

    $sql = "INSERT INTO seller_feedback (customer_name, order_id, rating, feedback)
            VALUES ('$name', '$order_id', '$rating', '$feedback')";

    mysqli_query($conn, $sql);
}

// ================= FETCH FEEDBACK =================
$result = mysqli_query($conn, "SELECT * FROM seller_feedback ORDER BY created_at DESC");
?>

<?php include("../includes/seller_header.php"); ?>
<title>Seller Feedback | 🧶CrochetingHubb</title>
<link rel="stylesheet" href="http://localhost/CROCHETINGHUBB/assets/css/feedback.css">


<!-- ========== FEEDBACK TABLE SECTION ========== -->
<main class="container my-5">

    <div class="table-card">

        <div class="table-header">
            <i class="bi bi-list-task"></i> Recent Feedbacks
        </div>

        <div class="table-responsive">
            <table class="table align-middle text-center">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Order ID</th>
                        <th>Rating</th>
                        <th>Feedback</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
    <?php
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['customer_name']}</td>
                            <td>{$row['order_id']}</td>
                            <td>{$row['rating']} ⭐</td>
                            <td>{$row['feedback']}</td>
                        </tr>";
                    }
                } else {
                    echo "<tr>
                        <td colspan='5'> </td>
                    </tr>";
                }
                ?>
</tbody>
            </table>
        </div>
    </div>

    

</main>

<!-- ========== FOOTER ========== -->
<?php include("../includes/seller_footer.php"); ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>