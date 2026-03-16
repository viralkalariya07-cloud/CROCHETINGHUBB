<?php
include("../includes/db.php");

// Create settings table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS website_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    website_name VARCHAR(255) DEFAULT 'CrochetingHubb',
    support_email VARCHAR(255) DEFAULT 'support@crochetinghub.com',
    support_phone VARCHAR(20) DEFAULT '',
    currency VARCHAR(10) DEFAULT 'INR',
    gst_percentage DECIMAL(5,2) DEFAULT 0.00,
    shipping_charge DECIMAL(10,2) DEFAULT 0.00
)");

// Check if settings exist, if not insert default row
$check = $conn->query("SELECT * FROM website_settings LIMIT 1");
if ($check->num_rows == 0) {
    $conn->query("INSERT INTO website_settings (website_name) VALUES ('CrochetingHubb')");
    $check = $conn->query("SELECT * FROM website_settings LIMIT 1");
}

$settings = $check->fetch_assoc();

// Handle AJAX Save Request
if (isset($_POST['action']) && $_POST['action'] == 'save_website_settings') {
    $name = mysqli_real_escape_string($conn, $_POST['websiteName']);
    $email = mysqli_real_escape_string($conn, $_POST['supportEmail']);
    $phone = mysqli_real_escape_string($conn, $_POST['supportPhone']);
    $currency = mysqli_real_escape_string($conn, $_POST['currency']);
    $gst = mysqli_real_escape_string($conn, $_POST['gstPercentage']);
    $shipping = mysqli_real_escape_string($conn, $_POST['shippingCharge']);

    $update = $conn->query("UPDATE website_settings SET 
        website_name = '$name',
        support_email = '$email',
        support_phone = '$phone',
        currency = '$currency',
        gst_percentage = '$gst',
        shipping_charge = '$shipping'
        WHERE id = 1");

    if ($update) {
        echo json_encode(['status' => 'success', 'message' => 'Settings updated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update settings.']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Settings – Admin Panel 🧶 CrochetingHubb</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet" />
    
    <!-- EMBEDDED CSS -->
    <style>
/* ============================================================
   EXISTING THEME — CONSISTENT WITH SITE
   ============================================================ */
:root {
    --primary-pink: #d83174;
    --soft-pink: #ffd1e1;
    --deep-pink: #b51d5b;
    --light-bg: #fff5f8;
    --gradient-pink: linear-gradient(135deg, #d83174 0%, #ff85a1 100%);
}

html,
body {
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

/* Navbar Tuning */
.navbar {
    background: var(--gradient-pink);
    box-shadow: 0 4px 15px rgba(216, 49, 116, 0.2);
    padding: 0.8rem 0;
}

.navbar-brand {
    color: #fff !important;
    font-weight: 700;
    font-size: 1.5rem;
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

.section-title {
    color: var(--primary-pink);
    font-weight: 800;
    font-size: 2.2rem;
    margin-bottom: 0.3rem;
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

.premium-footer {
    background: var(--gradient-pink);
    color: #fff;
    padding: 40px 0 20px;
    margin-top: auto;
}

/* ============================================================
   ADMIN SETTINGS PAGE STYLES
   ============================================================ */

/* ---------- Settings Hero ---------- */
.settings-hero {
    background: linear-gradient(180deg, rgba(216, 49, 116, 0.06) 0%, transparent 100%);
    border-bottom: 1px solid rgba(216, 49, 116, 0.1);
    padding: 2rem 0 1.6rem;
    text-align: center;
}

.settings-subtitle {
    color: #888;
    font-size: 1rem;
    margin-bottom: 0;
}

/* ---------- Settings Content ---------- */
.settings-content {
    padding: 2rem 0 2.8rem;
}

/* ---------- Settings Sidebar ---------- */
.settings-sidebar {
    background: #fff;
    border: 1px solid rgba(216, 49, 116, 0.1);
    border-radius: 16px;
    padding: 0.6rem;
    box-shadow: 0 2px 14px rgba(216, 49, 116, 0.06);
}

.sidebar-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.sidebar-btn {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    width: 100%;
    background: transparent;
    border: none;
    border-radius: 10px;
    padding: 0.72rem 1rem;
    font-size: 0.9rem;
    font-weight: 500;
    color: #555;
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease, transform 0.15s ease;
    text-align: left;
}

.sidebar-btn i {
    width: 18px;
    text-align: center;
    color: var(--primary-pink);
    font-size: 0.88rem;
    transition: color 0.2s ease;
}

.sidebar-btn:hover {
    background: var(--light-bg);
    color: var(--primary-pink);
    transform: translateX(4px);
}

.sidebar-btn--active {
    background: linear-gradient(135deg, rgba(216, 49, 116, 0.1) 0%, rgba(255, 133, 161, 0.08) 100%);
    color: var(--primary-pink) !important;
    font-weight: 600;
}

.sidebar-btn--active i {
    color: var(--primary-pink);
}

/* Logout button — red-ish tint on hover */
.sidebar-btn--logout:hover {
    background: #fff0f0;
    color: #c0392b !important;
}

.sidebar-btn--logout:hover i {
    color: #c0392b;
}

/* ---------- Settings Panel Wrapper ---------- */
.settings-panel-wrapper {
    background: #fff;
    border: 1px solid rgba(216, 49, 116, 0.1);
    border-radius: 16px;
    padding: 1.8rem 1.6rem;
    box-shadow: 0 2px 18px rgba(216, 49, 116, 0.06);
    min-height: 420px;
}

.settings-panel {
    animation: panelFadeIn 0.32s ease;
}

@keyframes panelFadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}

.panel-title {
    font-weight: 700;
    color: var(--primary-pink);
    font-size: 1.1rem;
    margin-bottom: 1.4rem;
    padding-bottom: 0.7rem;
    border-bottom: 2px solid var(--soft-pink);
}

/* ---------- Subsection Title ---------- */
.subsection-title {
    font-weight: 600;
    color: var(--primary-pink);
    font-size: 0.95rem;
    margin-bottom: 1rem;
    margin-top: 0;
}

/* ---------- Form Inputs ---------- */
.settings-label {
    font-size: 0.82rem;
    font-weight: 600;
    color: #555;
    margin-bottom: 0.28rem;
}

.settings-input {
    border: 1px solid #e2e2e2;
    border-radius: 9px;
    padding: 0.55rem 0.78rem;
    font-size: 0.88rem;
    background: var(--light-bg);
    transition: border-color 0.22s ease, box-shadow 0.22s ease, background 0.22s ease;
}

.settings-input:focus {
    border-color: var(--primary-pink);
    box-shadow: 0 0 0 3px rgba(216, 49, 116, 0.13);
    background: #fff;
    outline: none;
}

.settings-input::placeholder {
    color: #bfbfbf;
}

.settings-input.is-invalid {
    border-color: #e74c3c;
    background: #fff5f5;
}

/* Reset / outline button */
.btn-outline-reset {
    background: transparent;
    border: 1.5px solid #ddd;
    border-radius: 12px;
    padding: 10px 20px;
    font-weight: 600;
    color: #666;
    cursor: pointer;
    transition: border-color 0.2s ease, color 0.2s ease, background 0.2s ease;
}

.btn-outline-reset:hover {
    border-color: var(--primary-pink);
    color: var(--primary-pink);
    background: rgba(216, 49, 116, 0.04);
}

/* ---------- Profile Photo Section ---------- */
.profile-photo-section {
    margin-top: 0.4rem;
}

.profile-photo-ring {
    width: 120px;
    height: 120px;
    margin: 0 auto;
    border-radius: 50%;
    border: 4px solid var(--soft-pink);
    overflow: hidden;
    background: var(--light-bg);
    box-shadow: 0 4px 16px rgba(216, 49, 116, 0.15);
}

.profile-photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.photo-upload-label {
    display: inline-block;
    margin-top: 0.7rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--primary-pink);
    cursor: pointer;
    transition: color 0.2s ease;
}

.photo-upload-label:hover {
    color: var(--deep-pink);
}

/* ---------- Toggle Switch ---------- */
.toggle-option {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    background: var(--light-bg);
    border: 1px solid rgba(216, 49, 116, 0.1);
    border-radius: 12px;
    margin-bottom: 0.8rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.toggle-option:hover {
    border-color: rgba(216, 49, 116, 0.2);
    box-shadow: 0 2px 10px rgba(216, 49, 116, 0.08);
}

.toggle-info strong {
    display: block;
    font-size: 0.92rem;
    color: #333;
    margin-bottom: 0.2rem;
}

.toggle-info p {
    font-size: 0.8rem;
}

/* Custom Toggle Switch */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 26px;
    flex-shrink: 0;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: 0.3s;
    border-radius: 26px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
}

.toggle-switch input:checked + .toggle-slider {
    background: var(--gradient-pink);
}

.toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(24px);
}

.toggle-switch input:focus + .toggle-slider {
    box-shadow: 0 0 0 3px rgba(216, 49, 116, 0.2);
}

/* ---------- Footer Extras ---------- */
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

/* ============================================================
   RESPONSIVE
   ============================================================ */

/* Tablet & below — sidebar becomes horizontal pills */
@media (max-width: 991px) {
    .settings-sidebar {
        border-radius: 12px;
    }
    
    .sidebar-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }
    
    .sidebar-btn {
        padding: 0.5rem 0.75rem;
        font-size: 0.82rem;
        border-radius: 8px;
    }
    
    .settings-panel-wrapper {
        padding: 1.4rem 1.1rem;
    }
}

/* Small mobile tweaks */
@media (max-width: 767px) {
    .settings-hero {
        padding: 1.6rem 0 1.2rem;
    }
    
    .section-title {
        font-size: 1.7rem;
    }
    
    .sidebar-btn span {
        font-size: 0.78rem;
    }
    
    .settings-panel-wrapper {
        padding: 1.1rem 0.9rem;
        min-height: auto;
    }
    
    .profile-photo-ring {
        width: 100px;
        height: 100px;
    }
}

@media (max-width: 480px) {
    .toggle-option {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.8rem;
    }
    
    .toggle-switch {
        align-self: flex-end;
    }
}
    .details-box {
        background: #fff;
        border: 1px solid rgba(216, 49, 116, 0.1);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 12px rgba(216, 49, 116, 0.04);
        margin-bottom: 1rem;
    }
    .details-item {
        display: flex;
        justify-content: space-between;
        padding: 0.7rem 0;
        border-bottom: 1px dashed #eee;
    }
    .details-item:last-child {
        border-bottom: none;
    }
    .details-label {
        font-weight: 600;
        color: #666;
        font-size: 0.9rem;
    }
    .details-value {
        color: var(--primary-pink);
        font-weight: 600;
        font-size: 0.9rem;
    }
    </style>
</head>
<body>

<!-- ============================================================
     NAVBAR
     ============================================================ -->
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="#">
            <i class="fas fa-shield-alt me-2"></i>Admin Panel 🧶 <?php echo htmlspecialchars($settings['website_name']); ?>
        </a>

        <button class="navbar-toggler border-0" type="button"
                data-bs-toggle="collapse" data-bs-target="#navMenu"
                aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon" style="filter:brightness(0) invert(1);"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto gap-1">
                <li class="nav-item"><a class="nav-link" href="admin.php"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="adminusers.php"><i class="fas fa-users me-1"></i> Users</a></li>
                <li class="nav-item"><a class="nav-link" href="adminproducts.php"><i class="fas fa-box me-1"></i> Products</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-shopping-bag me-1"></i> Orders</a></li>
                
                <li class="nav-item"><a class="nav-link nav-link--active" href="#"><i class="fas fa-cog me-1"></i> Settings</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- ============================================================
     MAIN CONTENT
     ============================================================ -->
<main>

    <!-- Page Header -->
    <section class="settings-hero">
        <div class="container">
            <h1 class="section-title">Admin Settings ⚙️</h1>
            <p class="settings-subtitle">Manage website configuration</p>
        </div>
    </section>

    <!-- Settings Dashboard -->
    <section class="settings-content">
        <div class="container">
            <div class="row g-4">

                <!-- ============ LEFT SIDEBAR ============ -->
                <div class="col-lg-3">
                    <nav class="settings-sidebar">
                        <ul class="sidebar-list">
                            <li>
                                <button class="sidebar-btn sidebar-btn--active" data-section="profile">
                                    <i class="fas fa-user"></i>
                                    <span>Profile Settings</span>
                                </button>
                            </li>
                            <li>
                                <button class="sidebar-btn" data-section="website">
                                    <i class="fas fa-globe"></i>
                                    <span>Website Settings</span>
                                </button>
                            </li>
                            <li>
                                <button class="sidebar-btn" data-section="security">
                                    <i class="fas fa-lock"></i>
                                    <span>Security Settings</span>
                                </button>
                            </li>
                            <li>
                                <button class="sidebar-btn" data-section="notifications">
                                    <i class="fas fa-bell"></i>
                                    <span>Notifications</span>
                                </button>
                            </li>
                            <li>
                                <button class="sidebar-btn sidebar-btn--logout" data-section="logout">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Logout</span>
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>

                <!-- ============ RIGHT CONTENT ============ -->
                <div class="col-lg-9">
                    <div class="settings-panel-wrapper">

                        <!-- ===== SECTION 1: ADMIN PROFILE ===== -->
                        <div class="settings-panel" id="panel-profile">
                            <h5 class="panel-title"><i class="fas fa-user me-2"></i>Admin Profile</h5>

                            <!-- Profile Photo -->
                            <div class="profile-photo-section text-center mb-4">
                                <div class="profile-photo-ring">
                                    <img src="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='120' height='120'><rect width='120' height='120' rx='60' fill='%23ffd1e1'/><text x='60' y='70' text-anchor='middle' font-size='50'>👤</text></svg>" 
                                         alt="Admin Photo" 
                                         class="profile-photo" 
                                         id="profilePhotoPreview" />
                                </div>
                                <label class="photo-upload-label" for="profilePhotoUpload">
                                    <i class="fas fa-camera me-1"></i> Change Photo
                                </label>
                                <input type="file" id="profilePhotoUpload" accept="image/*" class="d-none" onchange="previewPhoto(event)" />
                            </div>

                            <!-- Profile Form -->
                            <div id="profileFormWrapper">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label settings-label" for="adminName">Admin Name</label>
                                        <input type="text" id="adminName" class="form-control settings-input" placeholder="Enter admin name" value="" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label settings-label" for="adminEmail">Email Address</label>
                                        <input type="email" id="adminEmail" class="form-control settings-input" placeholder="admin@example.com" value="" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label settings-label" for="adminPhone">Phone Number</label>
                                        <input type="tel" id="adminPhone" class="form-control settings-input" placeholder="10-digit phone number" maxlength="10" value="" oninput="restrictToDigits(this)" />
                                    </div>
                                </div>
                            </div>

                            <!-- Profile Details Display (Hidden by default) -->
                            <div id="profileDetailsWrapper" class="d-none">
                                <div class="details-box">
                                    <div class="details-item">
                                        <span class="details-label">Admin Name</span>
                                        <span class="details-value" id="dispAdminName"></span>
                                    </div>
                                    <div class="details-item">
                                        <span class="details-label">Email Address</span>
                                        <span class="details-value" id="dispAdminEmail"></span>
                                    </div>
                                    <div class="details-item">
                                        <span class="details-label">Phone Number</span>
                                        <span class="details-value" id="dispAdminPhone"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 d-flex gap-3 flex-wrap">
                                <button id="saveProfileBtn" class="btn btn-pink" onclick="saveProfile()">
                                    <i class="fas fa-save me-1"></i> Save Changes
                                </button>
                                <button class="btn btn-outline-reset" onclick="resetForm('profile')">
                                    <i class="fas fa-undo me-1"></i> Reset
                                </button>
                            </div>
                        </div>

                        <!-- ===== SECTION 2: WEBSITE SETTINGS ===== -->
                        <div class="settings-panel d-none" id="panel-website">
                            <h5 class="panel-title"><i class="fas fa-globe me-2"></i>Website Settings</h5>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label settings-label" for="websiteName">Website Name</label>
                                    <input type="text" id="websiteName" class="form-control settings-input" placeholder="Crocheting Hub" value="<?php echo htmlspecialchars($settings['website_name']); ?>" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label settings-label" for="supportEmail">Support Email</label>
                                    <input type="email" id="supportEmail" class="form-control settings-input" placeholder="support@crochetinghub.com" value="<?php echo htmlspecialchars($settings['support_email']); ?>" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label settings-label" for="supportPhone">Support Phone</label>
                                    <input type="tel" id="supportPhone" class="form-control settings-input" placeholder="Customer support number" value="<?php echo htmlspecialchars($settings['support_phone']); ?>" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label settings-label" for="currency">Currency</label>
                                    <select id="currency" class="form-select settings-input">
                                        <option value="">Select Currency</option>
                                        <option value="INR" <?php echo ($settings['currency'] == 'INR') ? 'selected' : ''; ?>>INR (₹)</option>
                                        <option value="USD" <?php echo ($settings['currency'] == 'USD') ? 'selected' : ''; ?>>USD ($)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label settings-label" for="gstPercentage">GST Percentage (%)</label>
                                    <input type="number" id="gstPercentage" class="form-control settings-input" placeholder="e.g., 18" value="<?php echo htmlspecialchars($settings['gst_percentage']); ?>" min="0" max="100" step="0.01" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label settings-label" for="shippingCharge">Shipping Charge (₹)</label>
                                    <input type="number" id="shippingCharge" class="form-control settings-input" placeholder="e.g., 99" value="<?php echo htmlspecialchars($settings['shipping_charge']); ?>" min="0" step="0.01" />
                                </div>
                            </div>

                            <div class="mt-4">
                                <button class="btn btn-pink" onclick="saveWebsiteSettings()">
                                    <i class="fas fa-save me-1"></i> Save Website Settings
                                </button>
                            </div>
                        </div>

                        <!-- ===== SECTION 3: SECURITY SETTINGS ===== -->
                        <div class="settings-panel d-none" id="panel-security">
                            <h5 class="panel-title"><i class="fas fa-lock me-2"></i>Security Settings</h5>

                            <!-- Change Password -->
                            <h6 class="subsection-title">Change Password</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label settings-label" for="currentPassword">Current Password</label>
                                    <input type="password" id="currentPassword" class="form-control settings-input" placeholder="Enter current password" value="" />
                                </div>
                                <div class="col-md-6"></div>
                                <div class="col-md-6">
                                    <label class="form-label settings-label" for="newPassword">New Password</label>
                                    <input type="password" id="newPassword" class="form-control settings-input" placeholder="Enter new password" value="" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label settings-label" for="confirmPassword">Confirm Password</label>
                                    <input type="password" id="confirmPassword" class="form-control settings-input" placeholder="Re-enter new password" value="" />
                                </div>
                            </div>

                            <button class="btn btn-pink mb-4" onclick="changePassword()">
                                <i class="fas fa-key me-1"></i> Change Password
                            </button>

                            <!-- Two-Factor Authentication -->
                            <h6 class="subsection-title">Two-Factor Authentication</h6>
                            <div class="toggle-option">
                                <div class="toggle-info">
                                    <strong>Enable Two-Factor Authentication</strong>
                                    <p class="text-muted mb-0">Add an extra layer of security to your account</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="twoFactorToggle" onchange="toggle2FA(this)" />
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <!-- ===== SECTION 4: NOTIFICATION SETTINGS ===== -->
                        <div class="settings-panel d-none" id="panel-notifications">
                            <h5 class="panel-title"><i class="fas fa-bell me-2"></i>Notification Settings</h5>

                            <p class="text-muted mb-3">Choose which notifications you want to receive</p>

                            <!-- New Order Notifications -->
                            <div class="toggle-option">
                                <div class="toggle-info">
                                    <strong>New Order Notifications</strong>
                                    <p class="text-muted mb-0">Get notified when a new order is placed</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="notifNewOrders" />
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>

                            <!-- New Seller Registration -->
                            <div class="toggle-option">
                                <div class="toggle-info">
                                    <strong>New Seller Registration Alerts</strong>
                                    <p class="text-muted mb-0">Get alerted when a new seller registers</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="notifNewSellers" />
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>

                            <!-- Low Stock Alerts -->
                            <div class="toggle-option">
                                <div class="toggle-info">
                                    <strong>Low Stock Alerts</strong>
                                    <p class="text-muted mb-0">Get notified when products are running low on stock</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="notifLowStock" />
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>

                            <!-- Customer Message Alerts -->
                            <div class="toggle-option">
                                <div class="toggle-info">
                                    <strong>Customer Message Alerts</strong>
                                    <p class="text-muted mb-0">Get notified when customers send messages</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="notifCustomerMessages" />
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>

                            <div class="mt-4">
                                <button class="btn btn-pink" onclick="saveNotificationSettings()">
                                    <i class="fas fa-save me-1"></i> Save Notification Settings
                                </button>
                            </div>
                        </div>

                    </div><!-- settings-panel-wrapper -->
                </div><!-- col-lg-9 -->

            </div><!-- row -->
        </div><!-- container -->
    </section>

</main>

<!-- ============================================================
     FOOTER
     ============================================================ -->
<footer class="premium-footer">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 mb-3 mb-md-0">
                <h5 class="footer-brand mb-1">🧶 Crocheting Hub Admin</h5>
                <p class="footer-tagline mb-0">Managing handcrafted excellence</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="footer-copy mb-0">&copy; 2025 Crocheting Hub. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- EMBEDDED JavaScript -->
<script>
(function () {
    "use strict";

    window.switchSection = function (sectionName) {
        if (sectionName === "logout") {
            handleLogout();
            return;
        }

        document.querySelectorAll(".settings-panel").forEach(function (panel) {
            panel.classList.add("d-none");
        });

        const targetPanel = document.getElementById("panel-" + sectionName);
        if (targetPanel) {
            targetPanel.classList.remove("d-none");
        }

        document.querySelectorAll(".sidebar-btn").forEach(function (btn) {
            btn.classList.remove("sidebar-btn--active");
            if (btn.dataset.section === sectionName) {
                btn.classList.add("sidebar-btn--active");
            }
        });
    };

    document.addEventListener("click", function (e) {
        const btn = e.target.closest(".sidebar-btn");
        if (btn && btn.dataset.section) {
            switchSection(btn.dataset.section);
        }
    });

    window.previewPhoto = function (event) {
        const file = event.target.files[0];
        if (!file) return;

        const img = document.getElementById("profilePhotoPreview");
        
        if (img.src && img.src.startsWith("blob:")) {
            URL.revokeObjectURL(img.src);
        }

        img.src = URL.createObjectURL(file);
    };

    window.restrictToDigits = function (el) {
        el.value = el.value.replace(/\D/g, "");
    };

    window.saveProfile = function () {
        const name = document.getElementById("adminName");
        const email = document.getElementById("adminEmail");
        const phone = document.getElementById("adminPhone");

        [name, email, phone].forEach(function (el) {
            if (el) el.classList.remove("is-invalid");
        });

        if (!name.value.trim()) {
            name.classList.add("is-invalid");
            name.focus();
            alert("Validation Error\n\nAdmin name is required.");
            return;
        }

        if (!email.value.trim()) {
            email.classList.add("is-invalid");
            email.focus();
            alert("Validation Error\n\nEmail address is required.");
            return;
        }

        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email.value.trim())) {
            email.classList.add("is-invalid");
            email.focus();
            alert("Invalid Email\n\nPlease enter a valid email address.");
            return;
        }

        if (phone.value.trim() && phone.value.length !== 10) {
            phone.classList.add("is-invalid");
            phone.focus();
            alert("Invalid Phone\n\nPhone number must be exactly 10 digits.");
            return;
        }

        // Copy values to display elements
        document.getElementById("dispAdminName").innerText = name.value.trim();
        document.getElementById("dispAdminEmail").innerText = email.value.trim();
        document.getElementById("dispAdminPhone").innerText = phone.value.trim() || "Not Provided";

        // Toggle visibility
        document.getElementById("profileFormWrapper").classList.add("d-none");
        document.getElementById("profileDetailsWrapper").classList.remove("d-none");
        document.getElementById("saveProfileBtn").classList.add("d-none");

        alert("Profile Saved!\n\nYour profile details are shown below.");
    };

    window.saveWebsiteSettings = function () {
        const websiteName = document.getElementById("websiteName");
        const supportEmail = document.getElementById("supportEmail");
        const supportPhone = document.getElementById("supportPhone");
        const currency = document.getElementById("currency");
        const gstPercentage = document.getElementById("gstPercentage");
        const shippingCharge = document.getElementById("shippingCharge");

        [websiteName, currency].forEach(function (el) {
            if (el) el.classList.remove("is-invalid");
        });

        if (!websiteName.value.trim()) {
            websiteName.classList.add("is-invalid");
            websiteName.focus();
            alert("Validation Error\n\nWebsite name is required.");
            return;
        }

        if (!currency.value) {
            currency.classList.add("is-invalid");
            currency.focus();
            alert("Validation Error\n\nPlease select a currency.");
            return;
        }

        // Prepare data for AJAX
        const formData = new FormData();
        formData.append('action', 'save_website_settings');
        formData.append('websiteName', websiteName.value.trim());
        formData.append('supportEmail', supportEmail.value.trim());
        formData.append('supportPhone', supportPhone.value.trim());
        formData.append('currency', currency.value);
        formData.append('gstPercentage', gstPercentage.value);
        formData.append('shippingCharge', shippingCharge.value);

        fetch('adminsettings.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert("Settings Saved!\n\n" + data.message);
                // Optionally update UI elements that depend on these settings
                document.querySelector('.navbar-brand').innerHTML = `<i class="fas fa-shield-alt me-2"></i>Admin Panel – ${websiteName.value.trim()}`;
            } else {
                alert("Error\n\n" + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Error\n\nSomething went wrong while saving settings.");
        });
    };

    window.changePassword = function () {
        const current = document.getElementById("currentPassword");
        const newPass = document.getElementById("newPassword");
        const confirm = document.getElementById("confirmPassword");

        [current, newPass, confirm].forEach(function (el) {
            if (el) el.classList.remove("is-invalid");
        });

        if (!current.value) {
            current.classList.add("is-invalid");
            current.focus();
            alert("Validation Error\n\nCurrent password is required.");
            return;
        }

        if (!newPass.value) {
            newPass.classList.add("is-invalid");
            newPass.focus();
            alert("Validation Error\n\nNew password is required.");
            return;
        }

        if (newPass.value.length < 6) {
            newPass.classList.add("is-invalid");
            newPass.focus();
            alert("Weak Password\n\nPassword must be at least 6 characters long.");
            return;
        }

        if (newPass.value !== confirm.value) {
            confirm.classList.add("is-invalid");
            confirm.focus();
            alert("Password Mismatch\n\nNew password and confirm password do not match.");
            return;
        }

        alert("Password Changed!\n\nYour password has been updated successfully.");
        
        current.value = "";
        newPass.value = "";
        confirm.value = "";
    };

    window.toggle2FA = function (checkbox) {
        const status = checkbox.checked ? "enabled" : "disabled";
        alert("2FA " + (checkbox.checked ? "Enabled" : "Disabled") + "\n\nTwo-factor authentication has been " + status + ".");
    };

    window.saveNotificationSettings = function () {
        alert("Notifications Saved!\n\nYour notification preferences have been updated.");
    };

    window.resetForm = function (sectionName) {
        const panel = document.getElementById("panel-" + sectionName);
        if (!panel) return;

        panel.querySelectorAll("input[type='text'], input[type='email'], input[type='tel'], input[type='number'], select").forEach(function (el) {
            el.value = "";
            el.classList.remove("is-invalid");
        });

        if (sectionName === "profile") {
            const photo = document.getElementById("profilePhotoPreview");
            if (photo) {
                photo.src = "data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='120' height='120'>" +
                    "<rect width='120' height='120' rx='60' fill='%23ffd1e1'/>" +
                    "<text x='60' y='70' text-anchor='middle' font-size='50'>👤</text></svg>";
            }
            document.getElementById("profilePhotoUpload").value = "";

            // Restore visibility of form and hide details
            document.getElementById("profileFormWrapper").classList.remove("d-none");
            document.getElementById("profileDetailsWrapper").classList.add("d-none");
            document.getElementById("saveProfileBtn").classList.remove("d-none");
        }

        alert("Form Reset\n\nDetails removed and fields cleared for new values.");
    };

    function handleLogout() {
        const confirmed = confirm("Are you sure you want to logout?");
        
        if (confirmed) {
            alert("You have been logged out successfully.");
        } else {
            const activeBtn = document.querySelector(".sidebar-btn--active");
            if (activeBtn && activeBtn.dataset.section !== "logout") {
                // Already on the right section
            } else {
                switchSection("profile");
            }
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".settings-input").forEach(function (input) {
            input.addEventListener("input", function () {
                this.classList.remove("is-invalid");
            });
        });

        console.log("Admin Settings Page initialized");
    });

})();
</script>

</body>
</html>