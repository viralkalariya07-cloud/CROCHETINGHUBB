<?php include_once(__DIR__ . "/db.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($site_settings['website_name']); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>


    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.html">🧶 <?php echo htmlspecialchars($site_settings['website_name']); ?></a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
                    
                    <li class="nav-item"><a class="nav-link" href="sellers.html"><i class="bi bi-people-fill"></i> Sellers</a></li>
                    
                    <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-person-circle"></i> Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="user\register.php"><i class="bi bi-person-plus-fill"></i> Register</a></li>

                </ul>
            </div>
        </div>
    </nav>

    <main>
