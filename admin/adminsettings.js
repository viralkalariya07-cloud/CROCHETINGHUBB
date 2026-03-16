(function () {
    "use strict";

    /* ============================================================
       SIDEBAR SECTION SWITCHING
       ============================================================ */

    /**
     * Switch between settings sections
     * @param {string} sectionName - 'profile', 'website', 'security', 'notifications', 'logout'
     */
    window.switchSection = function (sectionName) {
        // Handle logout separately
        if (sectionName === "logout") {
            handleLogout();
            return;
        }

        // Hide all panels
        document.querySelectorAll(".settings-panel").forEach(function (panel) {
            panel.classList.add("d-none");
        });

        // Show target panel
        const targetPanel = document.getElementById("panel-" + sectionName);
        if (targetPanel) {
            targetPanel.classList.remove("d-none");
        }

        // Update sidebar active state
        document.querySelectorAll(".sidebar-btn").forEach(function (btn) {
            btn.classList.remove("sidebar-btn--active");
            if (btn.dataset.section === sectionName) {
                btn.classList.add("sidebar-btn--active");
            }
        });
    };

    // Wire sidebar buttons via delegation
    document.addEventListener("click", function (e) {
        const btn = e.target.closest(".sidebar-btn");
        if (btn && btn.dataset.section) {
            switchSection(btn.dataset.section);
        }
    });

    /* ============================================================
       PROFILE PHOTO PREVIEW
       ============================================================ */

    /**
     * Preview selected profile photo
     * @param {Event} event - File input change event
     */
    window.previewPhoto = function (event) {
        const file = event.target.files[0];
        if (!file) return;

        const img = document.getElementById("profilePhotoPreview");
        
        // Revoke previous blob URL to free memory
        if (img.src && img.src.startsWith("blob:")) {
            URL.revokeObjectURL(img.src);
        }

        img.src = URL.createObjectURL(file);
    };

    /* ============================================================
       INPUT HELPER — Digits Only
       ============================================================ */

    /**
     * Restrict input to digits only (for phone numbers)
     * @param {HTMLInputElement} el
     */
    window.restrictToDigits = function (el) {
        el.value = el.value.replace(/\D/g, "");
    };

    /* ============================================================
       SECTION 1: SAVE PROFILE
       ============================================================ */

    /**
     * Validate and save admin profile
     */
    window.saveProfile = function () {
        const name = document.getElementById("adminName");
        const email = document.getElementById("adminEmail");
        const phone = document.getElementById("adminPhone");

        // Clear previous validation highlights
        [name, email, phone].forEach(function (el) {
            if (el) el.classList.remove("is-invalid");
        });

        // Validation
        if (!name.value.trim()) {
            name.classList.add("is-invalid");
            name.focus();
            showAlert("error", "Validation Error", "Admin name is required.");
            return;
        }

        if (!email.value.trim()) {
            email.classList.add("is-invalid");
            email.focus();
            showAlert("error", "Validation Error", "Email address is required.");
            return;
        }

        // Basic email validation
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email.value.trim())) {
            email.classList.add("is-invalid");
            email.focus();
            showAlert("error", "Invalid Email", "Please enter a valid email address.");
            return;
        }

        if (phone.value.trim() && phone.value.length !== 10) {
            phone.classList.add("is-invalid");
            phone.focus();
            showAlert("error", "Invalid Phone", "Phone number must be exactly 10 digits.");
            return;
        }

        // In production: send to backend
        // fetch('/api/admin/profile', {
        //     method: 'POST',
        //     body: JSON.stringify({ name: name.value, email: email.value, phone: phone.value })
        // });

        showAlert("success", "Profile Saved!", "Your profile has been updated successfully.");
    };

    /* ============================================================
       SECTION 2: SAVE WEBSITE SETTINGS
       ============================================================ */

    /**
     * Validate and save website settings
     */
    window.saveWebsiteSettings = function () {
        const websiteName = document.getElementById("websiteName");
        const supportEmail = document.getElementById("supportEmail");
        const currency = document.getElementById("currency");

        // Clear validation
        [websiteName, supportEmail, currency].forEach(function (el) {
            if (el) el.classList.remove("is-invalid");
        });

        // Basic validation
        if (!websiteName.value.trim()) {
            websiteName.classList.add("is-invalid");
            websiteName.focus();
            showAlert("error", "Validation Error", "Website name is required.");
            return;
        }

        if (!currency.value) {
            currency.classList.add("is-invalid");
            currency.focus();
            showAlert("error", "Validation Error", "Please select a currency.");
            return;
        }

        // In production: send to backend
        showAlert("success", "Settings Saved!", "Website settings have been updated successfully.");
    };

    /* ============================================================
       SECTION 3: CHANGE PASSWORD
       ============================================================ */

    /**
     * Validate and change password
     */
    window.changePassword = function () {
        const current = document.getElementById("currentPassword");
        const newPass = document.getElementById("newPassword");
        const confirm = document.getElementById("confirmPassword");

        // Clear validation
        [current, newPass, confirm].forEach(function (el) {
            if (el) el.classList.remove("is-invalid");
        });

        // Validation
        if (!current.value) {
            current.classList.add("is-invalid");
            current.focus();
            showAlert("error", "Validation Error", "Current password is required.");
            return;
        }

        if (!newPass.value) {
            newPass.classList.add("is-invalid");
            newPass.focus();
            showAlert("error", "Validation Error", "New password is required.");
            return;
        }

        if (newPass.value.length < 6) {
            newPass.classList.add("is-invalid");
            newPass.focus();
            showAlert("error", "Weak Password", "Password must be at least 6 characters long.");
            return;
        }

        if (newPass.value !== confirm.value) {
            confirm.classList.add("is-invalid");
            confirm.focus();
            showAlert("error", "Password Mismatch", "New password and confirm password do not match.");
            return;
        }

        // In production: send to backend
        showAlert("success", "Password Changed!", "Your password has been updated successfully.");
        
        // Clear password fields
        current.value = "";
        newPass.value = "";
        confirm.value = "";
    };

    /**
     * Toggle Two-Factor Authentication
     * @param {HTMLInputElement} checkbox
     */
    window.toggle2FA = function (checkbox) {
        const status = checkbox.checked ? "enabled" : "disabled";
        
        // In production: send to backend
        showAlert("success", "2FA " + (checkbox.checked ? "Enabled" : "Disabled"), 
            "Two-factor authentication has been " + status + ".");
    };

    /* ============================================================
       SECTION 4: SAVE NOTIFICATION SETTINGS
       ============================================================ */

    /**
     * Save notification preferences
     */
    window.saveNotificationSettings = function () {
        const settings = {
            newOrders: document.getElementById("notifNewOrders").checked,
            newSellers: document.getElementById("notifNewSellers").checked,
            lowStock: document.getElementById("notifLowStock").checked,
            customerMessages: document.getElementById("notifCustomerMessages").checked
        };

        // In production: send to backend
        // fetch('/api/admin/notifications', {
        //     method: 'POST',
        //     body: JSON.stringify(settings)
        // });

        showAlert("success", "Notifications Saved!", "Your notification preferences have been updated.");
    };

    /* ============================================================
       RESET FORM
       ============================================================ */

    /**
     * Reset form fields in a section
     * @param {string} sectionName - 'profile'
     */
    window.resetForm = function (sectionName) {
        const panel = document.getElementById("panel-" + sectionName);
        if (!panel) return;

        panel.querySelectorAll("input[type='text'], input[type='email'], input[type='tel']").forEach(function (el) {
            el.value = "";
            el.classList.remove("is-invalid");
        });

        // Reset profile photo if in profile section
        if (sectionName === "profile") {
            const photo = document.getElementById("profilePhotoPreview");
            if (photo) {
                photo.src = "data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='120' height='120'>" +
                    "<rect width='120' height='120' rx='60' fill='%23ffd1e1'/>" +
                    "<text x='60' y='70' text-anchor='middle' font-size='50'>👤</text></svg>";
            }
            document.getElementById("profilePhotoUpload").value = "";
        }

        showAlert("info", "Form Reset", "All fields have been cleared.");
    };

    /* ============================================================
       LOGOUT HANDLER
       ============================================================ */

    /**
     * Handle logout with confirmation
     */
    function handleLogout() {
        const confirmed = confirm("Are you sure you want to logout?");
        
        if (confirmed) {
            // In production: clear session and redirect
            // fetch('/api/admin/logout', { method: 'POST' })
            //     .then(() => window.location.href = 'login.php');
            
            alert("You have been logged out successfully.");
            // window.location.href = "login.php";
        } else {
            // Return to previous active section (default to profile)
            const activeBtn = document.querySelector(".sidebar-btn--active");
            if (activeBtn && activeBtn.dataset.section !== "logout") {
                // Already on the right section, do nothing
            } else {
                switchSection("profile");
            }
        }
    }

    /* ============================================================
       ALERT HELPER
       ============================================================ */

    /**
     * Show success/error/info alert
     * @param {string} type - 'success', 'error', 'info'
     * @param {string} title
     * @param {string} message
     */
    function showAlert(type, title, message) {
        // Simple alert for now
        // In production, use a better modal/toast system
        alert(title + "\n\n" + message);
    }

    /* ============================================================
       INITIALIZATION
       ============================================================ */

    document.addEventListener("DOMContentLoaded", function () {
        // Clear invalid highlight when user starts typing
        document.querySelectorAll(".settings-input").forEach(function (input) {
            input.addEventListener("input", function () {
                this.classList.remove("is-invalid");
            });
        });

        console.log("Admin Settings Page initialized");
    });

})();