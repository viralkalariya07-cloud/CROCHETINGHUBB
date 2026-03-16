<?php
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);

if (!$conn) {
    die("Database connection failed");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST['full_name'];
    $email = trim(strtolower($_POST['email']));

    $password_raw = trim($_POST['password']);

    /* ── Password strength validation ── */
    $pw_errors = [];
    if (strlen($password_raw) < 8) {
        $pw_errors[] = 'at least 8 characters';
    }
    if (!preg_match('/[A-Za-z]/', $password_raw)) {
        $pw_errors[] = 'at least 1 letter (a–z / A–Z)';
    }
    if (!preg_match('/[0-9]/', $password_raw)) {
        $pw_errors[] = 'at least 1 digit (0–9)';
    }
    if (!preg_match('/[^A-Za-z0-9]/', $password_raw)) {
        $pw_errors[] = 'at least 1 special character (!@#$…)';
    }

    if (!empty($pw_errors)) {
        $message = '<div class="alert alert-danger"><strong>Password must contain:</strong><ul class="mb-0 mt-1">';
        foreach ($pw_errors as $err) {
            $message .= '<li>' . htmlspecialchars($err) . '</li>';
        }
        $message .= '</ul></div>';
    } else {

    $password = password_hash($password_raw, PASSWORD_DEFAULT);

    /* Auto-fix: Ensure password column is large enough (VARCHAR 255) */
    mysqli_query($conn, "ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NOT NULL");

 

    /* Check if email already exists */
    $check_query = "SELECT id FROM users WHERE email = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "s", $email);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        // Update existing user instead of erroring, so they can 're-register' correctly
        mysqli_stmt_close($check_stmt);
        $role = $_POST['role'];
        $query = "UPDATE users SET full_name = ?, password = ?, role = ? WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $name, $password, $role, $email);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = '<div class="alert alert-success">Account updated correctly now! <a href="login.php" class="alert-link">Login now</a></div>';
        } else {
            $message = '<div class="alert alert-danger">Update Error: ' . mysqli_stmt_error($stmt) . '</div>';
        }
        mysqli_stmt_close($stmt);
    } else {
        mysqli_stmt_close($check_stmt);
        $role = $_POST['role'];
        
        $query = "INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $password, $role);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = '<div class="alert alert-success">Account created successfully! <a href="login.php" class="alert-link">Login now</a></div>';
        } else {
            $message = '<div class="alert alert-danger">Error: ' . mysqli_stmt_error($stmt) . '</div>';
        }
        mysqli_stmt_close($stmt);
    }
    } // end password-valid block
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | CrochetingHubb</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/register.css">
</head>

<body>

    <!-- Navbar -->
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

    <main class="register-main">
        <div class="register-container d-flex align-items-center justify-content-center">
            <div class="register-card">
                <h3>Create Account</h3>

                <?php
                if (isset($message)) {
                    echo $message;
                }
                ?>

                <form method="POST">

                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" placeholder="Enter your name" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="position-relative">
                            <input type="password" id="passwordInput" name="password" class="form-control pe-5" placeholder="Create password" required>
                            <span id="togglePw" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#888;" title="Show/Hide password">
                                <i class="bi bi-eye" id="eyeIcon"></i>
                            </span>
                        </div>
                        <!-- Live password rules -->
                        <div id="pwRules" class="mt-2 small" style="display:none;background:#f8f9fa;border:1px solid #dee2e6;border-radius:8px;padding:10px 14px;">
                            <div id="rule-len"    class="pw-rule" data-label="At least <strong>8 characters</strong>"></div>
                            <div id="rule-letter" class="pw-rule" data-label="At least <strong>1 letter</strong> (a–z / A–Z)"></div>
                            <div id="rule-digit"  class="pw-rule" data-label="At least <strong>1 digit</strong> (0–9)"></div>
                            <div id="rule-special" class="pw-rule" data-label="At least <strong>1 special character</strong> (!@#$%…)"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Register As</label>
                        <select class="form-select" name="role" required>
                            <option value="user">User / Buyer</option>
                            <option value="seller">Seller</option>
                        </select>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-register">Register Now</button>
                    </div>

                    <div class="login-link text-center mt-3">
                        Already have an account?
                        <a href="login.php">Login here</a>
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

    <script>
        /* ── Live password checker ── */
        const pwInput   = document.getElementById('passwordInput');
        const pwRules   = document.getElementById('pwRules');
        const eyeIcon   = document.getElementById('eyeIcon');
        const togglePw  = document.getElementById('togglePw');

        /* Show/hide rules panel on focus */
        pwInput.addEventListener('focus', () => pwRules.style.display = 'block');

        /* Toggle show/hide password */
        togglePw.addEventListener('click', () => {
            const isText = pwInput.type === 'text';
            pwInput.type = isText ? 'password' : 'text';
            eyeIcon.className = isText ? 'bi bi-eye' : 'bi bi-eye-slash';
        });

        /* Initialise rule labels */
        document.querySelectorAll('.pw-rule').forEach(el => {
            el.innerHTML = '✖ ' + el.dataset.label;
            el.style.color = '#dc3545';
        });

        /* Check rules on every keystroke */
        pwInput.addEventListener('input', () => {
            const val = pwInput.value;
            const rules = [
                { id: 'rule-len',     ok: val.length >= 8 },
                { id: 'rule-letter',  ok: /[A-Za-z]/.test(val) },
                { id: 'rule-digit',   ok: /[0-9]/.test(val) },
                { id: 'rule-special', ok: /[^A-Za-z0-9]/.test(val) },
            ];
            rules.forEach(r => {
                const el = document.getElementById(r.id);
                el.innerHTML = (r.ok ? '✔ ' : '✖ ') + el.dataset.label;
                el.style.color = r.ok ? '#198754' : '#dc3545';
            });
        });
    </script>

</body>
</html>
