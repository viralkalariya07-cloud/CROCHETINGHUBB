<?php
// ── Pick the correct isolated session BEFORE starting it ──
$_intended_role = $_POST['role'] ?? ($_GET['role'] ?? 'user');
session_name($_intended_role === 'seller' ? 'SELLER_SESSION' : 'USER_SESSION');
session_start();

/* Database Connection */
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);
if (!$conn) {
    die("Database connection failed");
}

$message = "";

/* --- DATABASE SUPER-FIX --- */
$fix_sql = "ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NOT NULL";
mysqli_query($conn, $fix_sql);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Sanitize input
    $email    = trim(strtolower($_POST['email']));
    $password = trim($_POST['password']);

    /* Fetch user by EMAIL ONLY */
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt  = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {

        $user = mysqli_fetch_assoc($result);

        // Verify password
        if (password_verify($password, $user['password'])) {

            /* ================= SESSION ================= */
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['full_name'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role']; // ✅ ONLY FROM DB

            /* ================= ROLE-BASED SYNC ================= */
            $db_role    = $user['role']; // seller | user | admin
            $sync_id    = $user['id'];
            $sync_name  = mysqli_real_escape_string($conn, $user['full_name']);
            $sync_email = mysqli_real_escape_string($conn, $user['email']);

            if ($db_role === 'seller') {

                // 🔒 SYNC SELLERACCOUNT ONLY
                $check = mysqli_query($conn, "SELECT id FROM selleraccount WHERE id = '$sync_id'");
                if (mysqli_num_rows($check) > 0) {
                    mysqli_query($conn, "
                        UPDATE selleraccount 
                        SET full_name = '$sync_name', email = '$sync_email' 
                        WHERE id = '$sync_id'
                    ");
                } else {
                    mysqli_query($conn, "
                        INSERT INTO selleraccount (id, full_name, email) 
                        VALUES ('$sync_id', '$sync_name', '$sync_email')
                    ");
                }

            } elseif ($db_role === 'user') {

                // 🔒 SYNC USERACCOUNT ONLY
                $check = mysqli_query($conn, "SELECT id FROM useraccount WHERE id = '$sync_id'");
                if (mysqli_num_rows($check) > 0) {
                    mysqli_query($conn, "
                        UPDATE useraccount 
                        SET full_name = '$sync_name', email = '$sync_email' 
                        WHERE id = '$sync_id'
                    ");
                } else {
                    mysqli_query($conn, "
                        INSERT INTO useraccount (id, full_name, email) 
                        VALUES ('$sync_id', '$sync_name', '$sync_email')
                    ");
                }
            }

            /* ================= REDIRECT ================= */
            if ($db_role === 'seller') {
                header("Location: ../seller.php");
            } elseif ($db_role === 'admin') {
                header("Location: ../admin/admin.php");
            } else {
                header("Location: user.php");
            }
            exit();

        } else {
            $message = "<div class='alert alert-danger text-center'>Incorrect password</div>";
        }

    } else {
        $message = "<div class='alert alert-danger text-center'>Account not found</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CrochetingHubb</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/login.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="../index.html">🧶 CrochetingHubb</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.html">Home</a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link" href="../sellers.html">
                            <i class="bi bi-people-fill"></i> Sellers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="bi bi-person-circle"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">
                            <i class="bi bi-person-plus-fill"></i> Register
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="login-main">
        <div class="login-container d-flex align-items-center justify-content-center">
            <div class="login-card">
                <div class="login-header text-center">
                    <h2 class="website-name">🧶 CrochetingHubb</h2>
                    <p class="tagline">Welcome back! Please login to your account.</p>
                </div>

                <?php if ($message) echo $message; ?>

                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label">Email Address</label>
                        <div class="input-wrapper">
                            <i class="bi bi-envelope input-icon"></i>
                            <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <div class="input-wrapper">
                            <i class="bi bi-lock input-icon"></i>
                            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                        </div>
                    </div>
                     
                    <div class="mb-4">
                        <label class="form-label">Login As</label>
                        <div class="input-wrapper">
                            <i class="bi bi-person-badge input-icon"></i>
                            <select name="role" class="form-select ps-5" required>
                                <option value="">Select Role</option>
                                <option value="seller">Seller</option>
                                <option value="user">User / Buyer</option>
                            </select>
                        </div>
                   </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-login">Login Now</button>
                    </div>

                    <div class="login-link text-center mt-4">
                        Don’t have an account?
                        <a href="register.php" class="create-account-link">Register Here</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="premium-footer">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-3 text-center text-lg-start mb-3 mb-lg-0">
                    <a href="../index.html" class="footer-brand">🧶 CrochetingHubb</a>
                </div>
                <div class="col-lg-6 mb-3 mb-lg-0">
                    <div class="mini-reviews-footer d-flex justify-content-center gap-4">
                        <div class="mini-review text-center">
                            <p class="mb-0 small italic">"Love the quality!"</p>
                            <span class="small opacity-75">- Sarah</span>
                        </div>
                        <div class="mini-review text-center">
                            <p class="mb-0 small italic">"Great tutorials!"</p>
                            <span class="small opacity-75">- Mike</span>
                        </div>
                        <div class="mini-review text-center">
                            <p class="mb-0 small italic">"Best community!"</p>
                            <span class="small opacity-75">- Elena</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 text-center text-lg-end">
                    <div class="footer-nav-horizontal">
                        <a href="#" class="text-white text-decoration-none me-3">Shop</a>
                        <a href="#" class="text-white text-decoration-none me-3">Support</a>
                        <a href="#" class="text-white text-decoration-none">Policy</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom text-center">
                <p class="mb-0">© 2026 CrochetingHubb. Made with passion for the Crochet Community.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>