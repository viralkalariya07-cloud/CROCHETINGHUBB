    </main>

    <footer class="premium-footer">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-3 text-center text-lg-start mb-3 mb-lg-0">
                    <a href="#" class="footer-brand">🧶 <?php echo htmlspecialchars($site_settings['website_name']); ?></a>
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
                        <a href="mailto:<?php echo htmlspecialchars($site_settings['support_email']); ?>" class="text-white text-decoration-none me-3">Support</a>
                        <a href="#" class="text-white text-decoration-none">Policy</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom text-center">
                <p class="mb-0">© <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_settings['website_name']); ?>. Made with passion for the Crochet Community.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
