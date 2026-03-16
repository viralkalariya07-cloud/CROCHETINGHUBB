<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Crochet Tutorials – Crocheting Hub</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet" />
    
    <!-- EMBEDDED CSS -->
    <style>
/* ============================================================
   PINK THEME
   ============================================================ */
:root {
    --primary-pink: #d83174;
    --soft-pink: #ffd1e1;
    --deep-pink: #b51d5b;
    --light-bg: #fff5f8;
    --gradient-pink: linear-gradient(135deg, #d83174 0%, #ff85a1 100%);
}

html, body {
    height: 100%;
}

body {
    margin: 0;
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    background-color: var(--light-bg);
    display: flex;
    flex-direction: column;
}

main {
    flex: 1 0 auto;
}

/* Navbar */
.navbar {
    background: var(--gradient-pink);
    box-shadow: 0 4px 15px rgba(216, 49, 116, 0.2);
    padding: 0.8rem 0;
}

.navbar-brand {
    color: #fff !important;
    font-weight: 700;
    font-size: 1.8rem;
    letter-spacing: -0.5px;
}

.nav-link {
    color: rgba(255, 255, 255, 0.95) !important;
    font-weight: 500;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
}

.nav-link:hover {
    color: #fff !important;
    transform: translateY(-2px);
}

.nav-link--active {
    background: rgba(255, 255, 255, 0.18) !important;
    border-radius: 8px;
    font-weight: 600 !important;
}

/* Page Hero */
.tutorials-hero {
    background: linear-gradient(180deg, rgba(216, 49, 116, 0.06) 0%, transparent 100%);
    border-bottom: 1px solid rgba(216, 49, 116, 0.1);
    padding: 2rem 0 1.6rem;
    text-align: center;
}

.section-title {
    color: var(--primary-pink);
    font-weight: 800;
    font-size: 2.2rem;
    margin-bottom: 0.3rem;
}

.tutorials-subtitle {
    color: #888;
    font-size: 1rem;
    margin-bottom: 0;
}

/* Content Section */
.tutorials-content {
    padding: 2rem 0 2.8rem;
}

/* Actions Bar */
.actions-bar {
    background: #fff;
    border: 1px solid rgba(216, 49, 116, 0.1);
    border-radius: 16px;
    padding: 1.2rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 12px rgba(216, 49, 116, 0.06);
}

.search-box {
    position: relative;
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--primary-pink);
    font-size: 0.9rem;
}

.search-input {
    padding-left: 2.8rem;
    border: 1.5px solid rgba(216, 49, 116, 0.2);
    border-radius: 10px;
    font-size: 0.88rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.search-input:focus {
    border-color: var(--primary-pink);
    box-shadow: 0 0 0 3px rgba(216, 49, 116, 0.1);
    outline: none;
}

.btn-pink {
    background: var(--gradient-pink);
    color: white !important;
    border: none;
    border-radius: 12px;
    padding: 10px 20px;
    font-weight: 600;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-pink:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(216, 49, 116, 0.3);
}

/* Empty State */
.empty-state-box {
    background: #fff;
    border: 2px dashed var(--soft-pink);
    border-radius: 18px;
    padding: 4rem 2rem 3.5rem;
    text-align: center;
    animation: fadeIn 0.4s ease;
}

.empty-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(216, 49, 116, 0.08), rgba(255, 133, 161, 0.1));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: var(--primary-pink);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}

/* Tutorial Card - Horizontal Layout */
.tutorial-card {
    background: #fff;
    border: 1px solid rgba(216, 49, 116, 0.12);
    border-radius: 16px;
    padding: 1.2rem;
    margin-bottom: 1.5rem;
    transition: box-shadow 0.25s ease, transform 0.2s ease;
    box-shadow: 0 2px 12px rgba(216, 49, 116, 0.06);
    animation: cardSlideIn 0.35s ease both;
}

.tutorial-card:hover {
    box-shadow: 0 6px 24px rgba(216, 49, 116, 0.12);
    transform: translateY(-2px);
}

@keyframes cardSlideIn {
    from { opacity: 0; transform: translateX(-20px); }
    to   { opacity: 1; transform: translateX(0); }
}

/* Card Inner Layout */
.tutorial-card-inner {
    display: flex;
    gap: 1.2rem;
    margin-bottom: 1rem;
}

/* Photo on LEFT */
.tutorial-photo {
    flex-shrink: 0;
    width: 200px;
    height: 150px;
    border-radius: 12px;
    overflow: hidden;
}

.tutorial-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Details on RIGHT */
.tutorial-details {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.tutorial-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--primary-pink);
    margin-bottom: 0.5rem;
}

.tutorial-seller {
    font-size: 0.85rem;
    color: #666;
    margin-bottom: 0.8rem;
}

.tutorial-seller i {
    color: var(--primary-pink);
}

.tutorial-actions {
    margin-top: auto;
    display: flex;
    gap: 0.5rem;
}

/* YouTube Link Below Card */
.tutorial-video-link {
    padding: 0.8rem 1rem;
    background: linear-gradient(135deg, rgba(216, 49, 116, 0.05), rgba(255, 133, 161, 0.03));
    border: 1px solid rgba(216, 49, 116, 0.15);
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 0.6rem;
    text-decoration: none;
    transition: all 0.2s ease;
}

.tutorial-video-link:hover {
    background: linear-gradient(135deg, rgba(216, 49, 116, 0.08), rgba(255, 133, 161, 0.05));
    border-color: var(--primary-pink);
    transform: translateX(4px);
}

.tutorial-video-link i {
    color: var(--primary-pink);
    font-size: 1.2rem;
}

.tutorial-video-link .link-text {
    flex: 1;
}

.tutorial-video-link .link-label {
    font-size: 0.75rem;
    color: #888;
    display: block;
    margin-bottom: 0.1rem;
}

.tutorial-video-link .link-url {
    font-size: 0.88rem;
    color: var(--primary-pink);
    font-weight: 600;
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.btn-sm {
    padding: 0.4rem 0.75rem;
    font-size: 0.8rem;
    border-radius: 8px;
}

/* Footer */
.premium-footer {
    background: var(--gradient-pink);
    color: #fff;
    padding: 40px 0 20px;
    margin-top: auto;
}

.footer-brand {
    font-weight: 700;
    font-size: 1.15rem;
    color: #fff;
}

.footer-tagline {
    font-size: 0.82rem;
    opacity: 0.78;
}

.footer-copy {
    font-size: 0.78rem;
    opacity: 0.7;
}

/* Responsive */
@media (max-width: 767px) {
    .tutorials-hero {
        padding: 1.6rem 0 1.2rem;
    }
    
    .section-title {
        font-size: 1.7rem;
    }
    
    .tutorial-card-inner {
        flex-direction: column;
    }
    
    .tutorial-photo {
        width: 100%;
        height: 180px;
    }
    
    .tutorial-video-link .link-url {
        font-size: 0.75rem;
    }
}
    </style>
</head>
<body>

<!-- ============================================================
     NAVBAR
     ============================================================ -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="#">🧶 Crocheting Hub</a>

        <button class="navbar-toggler border-0" type="button"
                data-bs-toggle="collapse" data-bs-target="#navMenu"
                aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon" style="filter:brightness(0) invert(1);"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto gap-1">
                <li class="nav-item"><a class="nav-link" href="index.html"><i class="fas fa-home me-1"></i> Home</a></li>
                
            </ul>
        </div>
    </div>
</nav>

<!-- ============================================================
     MAIN CONTENT
     ============================================================ -->
<main>

    <!-- Page Hero -->
    <section class="tutorials-hero">
        <div class="container">
            <h1 class="section-title">Crochet Tutorials 🎥</h1>
            <p class="tutorials-subtitle">Learn crochet techniques from our expert tutorials</p>
        </div>
    </section>

    <!-- Tutorials Content -->
    <section class="tutorials-content">
        <div class="container">

            <!-- Actions Bar -->
            <div class="actions-bar">
                <div class="row g-3 align-items-center">
                    <div class="col-md-8">
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" 
                                   id="searchTutorial" 
                                   class="form-control search-input" 
                                   placeholder="Search tutorials..." 
                                   onkeyup="searchTutorials()" />
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <a href="addtutorial.php" class="btn btn-pink">
                            <i class="fas fa-plus me-1"></i> Add Tutorial
                        </a>
                    </div>
                </div>
            </div>

            <?php
            include_once(__DIR__ . "/includes/db.php");
            $tutorials = [];

            $result = $conn->query("SELECT * FROM tutorials ORDER BY created_at DESC");
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $tutorials[] = $row;
                }
            }
            $isEmpty = (count($tutorials) === 0);
            ?>

            <!-- Empty State box -->
            <div id="emptyState" class="empty-state-box <?php echo !$isEmpty ? 'd-none' : ''; ?>">
                <div class="empty-icon">
                    <i class="fas fa-video-slash"></i>
                </div>
                <h5 class="mt-3 mb-1">No tutorials available</h5>
                <p class="text-muted mb-3">Be the first to add a crochet tutorial!</p>
                <a href="addtutorial.php" class="btn btn-pink">
                    <i class="fas fa-plus me-1"></i> Add First Tutorial
                </a>
            </div>

            <!-- Tutorials Container -->
            <div id="tutorialsContainer" class="<?php echo $isEmpty ? 'd-none' : ''; ?>">
                <?php if (!$isEmpty): ?>
                    <?php if (!$result): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Database Error:</strong> <?php echo $conn->error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php foreach ($tutorials as $row): ?>
                        <div class="tutorial-card" data-tutorial-id="<?php echo $row['id']; ?>">
                            <div class="tutorial-card-inner">
                                <div class="tutorial-photo">
                                    <img src="<?php echo htmlspecialchars($row['photo']); ?>" 
                                         alt="<?php echo htmlspecialchars($row['name']); ?>"
                                         onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22150%22><rect width=%22200%22 height=%22150%22 fill=%22%23ffd1e1%22/><text x=%22100%22 y=%2285%22 text-anchor=%22middle%22 font-size=%2240%22 fill=%22%23d83174%22>🎥</text></svg>'" />
                                </div>

                                <div class="tutorial-details">
                                    <h5 class="tutorial-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                                    <p class="tutorial-seller">
                                        <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($row['seller_name']); ?>
                                    </p>
                                    
                                    <div class="tutorial-actions">
                                        <a href="<?php echo htmlspecialchars($row['video_link']); ?>" target="_blank" class="btn btn-pink btn-sm">
                                            <i class="fas fa-play me-1"></i> Watch Tutorial
                                        </a>
                                       
                                    </div>
                                </div>
                            </div>

                            <a href="<?php echo htmlspecialchars($row['video_link']); ?>" target="_blank" class="tutorial-video-link">
                                <i class="fab fa-youtube"></i>
                                <div class="link-text">
                                    <span class="link-label">YouTube Video Link</span>
                                    <span class="link-url"><?php echo htmlspecialchars($row['video_link']); ?></span>
                                </div>
                                <i class="fas fa-external-link-alt" style="color: var(--primary-pink); font-size: 0.9rem;"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div><!-- container -->
    </section>

</main>

<!-- ============================================================
     FOOTER
     ============================================================ -->
<?php include("includes/seller_footer.php"); ?>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Tutorials JS -->
<script src="tutorial.js"></script>

</body>
</html>