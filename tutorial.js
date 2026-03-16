(function () {
    "use strict";

    const STORAGE_KEY = "crochetingHub_tutorials";

    /* ============================================================
       TUTORIALS PAGE FUNCTIONS
       ============================================================ */

    /**
     * Load tutorials from localStorage
     * @returns {Array} Array of tutorial objects
     */
    function loadTutorials() {
        try {
            const data = localStorage.getItem(STORAGE_KEY);
            return data ? JSON.parse(data) : [];
        } catch (error) {
            console.error("Error loading tutorials:", error);
            return [];
        }
    }

    /**
     * Save tutorials to localStorage
     * @param {Array} tutorials - Array of tutorial objects
     */
    function saveTutorials(tutorials) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(tutorials));
        } catch (error) {
            console.error("Error saving tutorials:", error);
        }
    }

    /**
     * Render tutorials on the page
     * @param {Array} tutorials - Array of tutorial objects
     */
    function renderTutorials(tutorials) {
        const container = document.getElementById("tutorialsContainer");
        const emptyState = document.getElementById("emptyState");

        if (!container || !emptyState) return;

        container.innerHTML = "";

        if (tutorials.length === 0) {
            emptyState.classList.remove("d-none");
            container.classList.add("d-none");
            return;
        }

        emptyState.classList.add("d-none");
        container.classList.remove("d-none");

        tutorials.forEach((tutorial, index) => {
            const card = createTutorialCard(tutorial, index);
            container.appendChild(card);
        });
    }

    /**
     * Create a tutorial card element (horizontal layout)
     * @param {Object} tutorial - Tutorial data object
     * @param {number} index - Card index for animation delay
     * @returns {HTMLElement} Tutorial card DOM element
     */
    function createTutorialCard(tutorial, index) {
        const card = document.createElement("div");
        card.className = "tutorial-card";
        card.style.animationDelay = `${index * 0.05}s`;
        card.dataset.tutorialId = tutorial.id;

        // Photo: use uploaded image or default
        const photoSrc = tutorial.photo ||
            "data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='200' height='150'>" +
            "<rect width='200' height='150' fill='%23ffd1e1'/>" +
            "<text x='100' y='85' text-anchor='middle' font-size='40' fill='%23d83174'>🎥</text></svg>";

        card.innerHTML = `
            <!-- Card Inner: Photo LEFT + Details RIGHT -->
            <div class="tutorial-card-inner">
                <!-- Photo on LEFT -->
                <div class="tutorial-photo">
                    <img src="${escapeHtml(photoSrc)}" 
                         alt="${escapeHtml(tutorial.name)}"
                         onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22150%22><rect width=%22200%22 height=%22150%22 fill=%22%23ffd1e1%22/><text x=%22100%22 y=%2285%22 text-anchor=%22middle%22 font-size=%2240%22 fill=%22%23d83174%22>🎥</text></svg>'" />
                </div>

                <!-- Details on RIGHT -->
                <div class="tutorial-details">
                    <h5 class="tutorial-title">${escapeHtml(tutorial.name)}</h5>
                    <p class="tutorial-seller">
                        <i class="fas fa-user-circle"></i> ${escapeHtml(tutorial.sellerName)}
                    </p>
                    
                    <div class="tutorial-actions">
                        <a href="${escapeHtml(tutorial.link)}" target="_blank" class="btn btn-pink btn-sm">
                            <i class="fas fa-play me-1"></i> Watch Tutorial
                        </a>
                        <button class="btn btn-danger btn-sm" onclick="deleteTutorial('${tutorial.id}')">
                            <i class="fas fa-trash-alt me-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- YouTube Link BELOW Card -->
            <a href="${escapeHtml(tutorial.link)}" target="_blank" class="tutorial-video-link">
                <i class="fab fa-youtube"></i>
                <div class="link-text">
                    <span class="link-label">YouTube Video Link</span>
                    <span class="link-url">${escapeHtml(tutorial.link)}</span>
                </div>
                <i class="fas fa-external-link-alt" style="color: var(--primary-pink); font-size: 0.9rem;"></i>
            </a>
        `;

        return card;
    }

    /**
     * Delete a tutorial with confirmation
     * @param {string} tutorialId - Tutorial ID to delete
     */
    window.deleteTutorial = function (tutorialId) {
        if (!confirm("Are you sure you want to delete this tutorial?")) return;

        const formData = new FormData();
        formData.append("id", tutorialId);

        fetch("api_delete_tutorial.php", {
            method: "POST",
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    alert("Tutorial deleted successfully!");
                    location.reload();
                } else {
                    alert("Error deleting tutorial: " + data.message);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred while deleting the tutorial.");
            });
    };

    /**
     * Search/filter tutorials by name or seller
     */
    window.searchTutorials = function () {
        const input = document.getElementById("searchTutorial");
        if (!input) return;

        const filter = input.value.toLowerCase();
        const cards = document.querySelectorAll(".tutorial-card");
        let found = false;

        cards.forEach(card => {
            const title = card.querySelector(".tutorial-title").textContent.toLowerCase();
            const seller = card.querySelector(".tutorial-seller").textContent.toLowerCase();

            if (title.includes(filter) || seller.includes(filter)) {
                card.style.display = "";
                found = true;
            } else {
                card.style.display = "none";
            }
        });

        const container = document.getElementById("tutorialsContainer");
        const emptyState = document.getElementById("emptyState");

        if (filter && !found) {
            // maybe show a "no results" message
        } else if (!filter && cards.length === 0) {
            if (emptyState) emptyState.classList.remove("d-none");
            if (container) container.classList.add("d-none");
        }
    };

    /* ============================================================
       ADD TUTORIAL PAGE FUNCTIONS
       ============================================================ */

    /**
     * Preview photo when user selects a file
     * @param {Event} event - File input change event
     */
    window.previewPhoto = function (event) {
        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();

        reader.onload = function (e) {
            const preview = document.getElementById("photoPreview");
            const placeholder = document.getElementById("photoPlaceholder");

            if (preview && placeholder) {
                preview.src = e.target.result;
                preview.style.display = "block";
                placeholder.style.display = "none";
            }
        };

        reader.readAsDataURL(file);
    };

    /**
     * Handle add tutorial form submission
     * @param {Event} event - Form submit event
     */
    function handleAddTutorialSubmit(event) {
        event.preventDefault();

        const photoUpload = document.getElementById("photoUpload");
        const videoLink = document.getElementById("videoLink");
        const tutorialName = document.getElementById("tutorialName");
        const sellerName = document.getElementById("sellerName");

        // Clear previous validation highlights
        [photoUpload, videoLink, tutorialName, sellerName].forEach(function (el) {
            if (el) el.classList.remove("is-invalid");
        });

        // Validation: Photo
        if (!photoUpload.files || photoUpload.files.length === 0) {
            photoUpload.classList.add("is-invalid");
            photoUpload.focus();
            alert("Validation Error\n\nPlease upload a tutorial photo.");
            return;
        }

        // Validation: Video Link
        if (!videoLink.value.trim()) {
            videoLink.classList.add("is-invalid");
            videoLink.focus();
            alert("Validation Error\n\nYouTube video link is required.");
            return;
        }

        // URL validation
        try {
            new URL(videoLink.value);
        } catch (e) {
            videoLink.classList.add("is-invalid");
            videoLink.focus();
            alert("Invalid URL\n\nPlease enter a valid YouTube video link.");
            return;
        }

        // Validation: Name
        if (!tutorialName.value.trim()) {
            tutorialName.classList.add("is-invalid");
            tutorialName.focus();
            alert("Validation Error\n\nTutorial name is required.");
            return;
        }

        // Validation: Seller Name
        if (!sellerName.value.trim()) {
            sellerName.classList.add("is-invalid");
            sellerName.focus();
            alert("Validation Error\n\nSeller name is required.");
            return;
        }

        // Get photo as base64
        const photoPreview = document.getElementById("photoPreview");
        const photoData = photoPreview && photoPreview.style.display === "block" ? photoPreview.src : null;

        if (!photoData) {
            alert("Error\n\nPhoto preview failed. Please try uploading again.");
            return;
        }

        // Create FormData
        const formData = new FormData();
        formData.append('photoUpload', photoUpload.files[0]);
        formData.append('videoLink', videoLink.value.trim());
        formData.append('tutorialName', tutorialName.value.trim());
        formData.append('sellerName', sellerName.value.trim());

        // Send to PHP via fetch
        fetch('api_save_tutorial.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert("Success!\n\nTutorial added successfully!");
                    window.location.href = "tutorial.php";
                } else {
                    alert("Error\n\n" + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Error\n\nAn unexpected error occurred while saving the tutorial.");
            });
    }

    /* ============================================================
       UTILITY FUNCTIONS
       ============================================================ */

    /**
     * Escape HTML to prevent XSS
     * @param {string} str - Raw string
     * @returns {string} - Escaped string
     */
    function escapeHtml(str) {
        if (str == null) return "";
        const div = document.createElement("div");
        div.textContent = String(str);
        return div.innerHTML;
    }

    /* ============================================================
       INITIALIZATION
       ============================================================ */

    document.addEventListener("DOMContentLoaded", function () {
        // Check which page we're on
        const tutorialsPage = document.getElementById("tutorialsContainer") !== null;
        const addTutorialPage = document.getElementById("addTutorialForm") !== null;

        if (tutorialsPage) {
            // Tutorials are now rendered by PHP
            console.log("Tutorials page (PHP-rendered) initialized");
        }

        if (addTutorialPage) {
            // Attach form submit handler
            const form = document.getElementById("addTutorialForm");
            if (form) {
                form.addEventListener("submit", handleAddTutorialSubmit);
            }

            // Clear validation on input
            document.querySelectorAll(".form-input-custom").forEach(function (input) {
                input.addEventListener("input", function () {
                    this.classList.remove("is-invalid");
                });

                input.addEventListener("change", function () {
                    this.classList.remove("is-invalid");
                });
            });

            console.log("Add Tutorial page initialized");
        }
    });

})();