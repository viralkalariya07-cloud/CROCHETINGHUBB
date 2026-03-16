<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Tutorial – Crocheting Hub</title>

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
.add-tutorial-hero {
    background: linear-gradient(180deg, rgba(216, 49, 116, 0.06) 0%, transparent 100%);
    border-bottom: 1px solid rgba(216, 49, 116, 0.1);
    padding: 2rem 0 1.6rem;
}

.section-title {
    color: var(--primary-pink);
    font-weight: 800;
    font-size: 2.2rem;
    margin-bottom: 0.3rem;
}

.add-tutorial-subtitle {
    color: #888;
    font-size: 1rem;
    margin-bottom: 0;
}

/* Back Button */
.back-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: var(--gradient-pink);
    color: #fff;
    border-radius: 10px;
    text-decoration: none;
    font-size: 1.1rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.back-btn:hover {
    transform: translateX(-4px);
    box-shadow: 0 4px 16px rgba(216, 49, 116, 0.3);
    color: #fff;
}

/* Content Section */
.add-tutorial-content {
    padding: 2rem 0 2.8rem;
}

/* Form Card */
.form-card {
    background: #fff;
    border: 1px solid rgba(216, 49, 116, 0.1);
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 2px 16px rgba(216, 49, 116, 0.08);
}

.form-section-title {
    font-weight: 700;
    color: var(--primary-pink);
    font-size: 1.1rem;
    margin-bottom: 1.2rem;
    padding-bottom: 0.6rem;
    border-bottom: 2px solid var(--soft-pink);
}

/* Form Inputs */
.form-label-custom {
    font-size: 0.82rem;
    font-weight: 600;
    color: #555;
    margin-bottom: 0.28rem;
}

.form-label-custom .req {
    color: var(--primary-pink);
}

.form-input-custom {
    border: 1px solid #e2e2e2;
    border-radius: 9px;
    padding: 0.55rem 0.78rem;
    font-size: 0.88rem;
    background: var(--light-bg);
    transition: border-color 0.22s ease, box-shadow 0.22s ease, background 0.22s ease;
}

.form-input-custom:focus {
    border-color: var(--primary-pink);
    box-shadow: 0 0 0 3px rgba(216, 49, 116, 0.13);
    background: #fff;
    outline: none;
}

.form-input-custom::placeholder {
    color: #bfbfbf;
}

.form-input-custom.is-invalid {
    border-color: #e74c3c;
    background: #fff5f5;
}

/* Photo Preview */
.photo-preview-area {
    margin-top: 0.8rem;
}

.photo-preview-box {
    width: 200px;
    height: 150px;
    border: 2px dashed var(--soft-pink);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: var(--light-bg);
}

.photo-preview-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: none;
}

.photo-placeholder {
    text-align: center;
    color: var(--primary-pink);
}

.photo-placeholder i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

/* Buttons */
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

.btn-outline-reset {
    background: transparent;
    border: 1.5px solid #ddd;
    border-radius: 12px;
    padding: 10px 20px;
    font-weight: 600;
    color: #666;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-block;
}

.btn-outline-reset:hover {
    border-color: var(--primary-pink);
    color: var(--primary-pink);
    background: rgba(216, 49, 116, 0.04);
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
    .add-tutorial-hero {
        padding: 1.6rem 0 1.2rem;
    }
    
    .section-title {
        font-size: 1.7rem;
    }
    
    .form-card {
        padding: 1.5rem;
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
                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-home me-1"></i> Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-th-large me-1"></i> Products</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-tags me-1"></i> Categories</a></li>
                <li class="nav-item"><a class="nav-link nav-link--active" href="tutorial.php"><i class="fas fa-video me-1"></i> Tutorials</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-user-circle me-1"></i> Account</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- ============================================================
     MAIN CONTENT
     ============================================================ -->
<main>

    <!-- Page Hero -->
    <section class="add-tutorial-hero">
        <div class="container">
            <div class="d-flex align-items-center gap-3 mb-2">
                <a href="tutorial.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="section-title mb-0">Add New Tutorial</h1>
            </div>
            <p class="add-tutorial-subtitle">Share your crochet knowledge with the community</p>
        </div>
    </section>

    <!-- Add Tutorial Form -->
    <section class="add-tutorial-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    
                    <div class="form-card">
                        <h5 class="form-section-title"><i class="fas fa-video me-2"></i>Tutorial Details</h5>

                        <form id="addTutorialForm">
                            
                            <!-- Photo Upload -->
                            <div class="mb-3">
                                <label class="form-label form-label-custom" for="photoUpload">
                                    Tutorial Photo <span class="req">*</span>
                                </label>
                                <input type="file" 
                                       id="photoUpload" 
                                       name="photoUpload" 
                                       class="form-control form-input-custom" 
                                       accept="image/*" 
                                       onchange="previewPhoto(event)"
                                       required />
                                <small class="text-muted">Upload a photo for your tutorial</small>
                                
                                <!-- Photo Preview -->
                                <div class="photo-preview-area">
                                    <div class="photo-preview-box" id="photoPreviewBox">
                                        <div class="photo-placeholder" id="photoPlaceholder">
                                            <i class="fas fa-image d-block"></i>
                                            <small>Preview</small>
                                        </div>
                                        <img id="photoPreview" class="photo-preview-img" alt="Photo Preview" />
                                    </div>
                                </div>
                            </div>

                            <!-- Link (YouTube URL) -->
                            <div class="mb-3">
                                <label class="form-label form-label-custom" for="videoLink">
                                    YouTube Video Link <span class="req">*</span>
                                </label>
                                <input type="url" 
                                       id="videoLink" 
                                       name="videoLink" 
                                       class="form-control form-input-custom" 
                                       placeholder="https://www.youtube.com/watch?v=..." 
                                       value="" 
                                       required />
                                <small class="text-muted">Enter the YouTube video URL</small>
                            </div>

                            <!-- Name (Tutorial Name) -->
                            <div class="mb-3">
                                <label class="form-label form-label-custom" for="tutorialName">
                                    Tutorial Name <span class="req">*</span>
                                </label>
                                <input type="text" 
                                       id="tutorialName" 
                                       name="tutorialName" 
                                       class="form-control form-input-custom" 
                                       placeholder="E.g., How to Crochet a Granny Square" 
                                       value="" 
                                       required />
                            </div>

                            <!-- Seller Name -->
                            <div class="mb-4">
                                <label class="form-label form-label-custom" for="sellerName">
                                    Seller Name <span class="req">*</span>
                                </label>
                                <input type="text" 
                                       id="sellerName" 
                                       name="sellerName" 
                                       class="form-control form-input-custom" 
                                       placeholder="Your name or shop name" 
                                       value="" 
                                       required />
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-3 flex-wrap">
                                <button type="submit" class="btn btn-pink">
                                    <i class="fas fa-save me-1"></i> Save Tutorial
                                </button>
                                <a href="tutorial.php" class="btn btn-outline-reset">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                            </div>

                        </form>

                    </div>

                </div>
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