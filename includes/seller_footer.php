<?php
// seller_footer.php
?>
<style>
    .premium-seller-footer {
        background: linear-gradient(135deg, #d83174 0%, #ff85a1 100%);
        color: #fff;
        padding: 40px 0 20px;
        margin-top: auto;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }
    .footer-brand-title {
        font-size: 1.6rem;
        font-weight: 700;
        color: #fff;
        text-decoration: none;
    }
    .footer-social-links a {
        color: #fff;
        font-size: 1.4rem;
        margin: 0 10px;
        transition: all 0.3s ease;
        text-decoration: none;
    }
    .footer-social-links a:hover {
        color: #ffd1e1;
        transform: scale(1.2);
    }
    .footer-bottom-text {
        opacity: 0.8;
        font-size: 0.9rem;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }
</style>

<footer class="premium-seller-footer">
    <div class="container">
        <div class="row gy-4 align-items-center">
            <div class="col-12 col-lg-6 text-center text-lg-start">
                <a href="#" class="footer-brand-title">🧶 CrochetingHubb</a>
                <p class="mb-0 opacity-75 mt-2">Empowering crochet artists to share their handmade treasures with the world.</p>
            </div>
            <div class="col-12 col-lg-6 text-center text-lg-end">
                <div class="footer-social-links mb-3">
                    <a href="#"><i class="bi bi-instagram"></i></a>
                    <a href="#"><i class="bi bi-facebook"></i></a>
                    <a href="#"><i class="bi bi-youtube"></i></a>
                    <a href="#"><i class="bi bi-pinterest"></i></a>
                </div>
                <div class="footer-bottom-text">
                    <p class="mb-0">&copy; <?php echo date("Y"); ?> CrochetingHubb. All rights reserved. Made with ❤️ for the community.</p>
                </div>
            </div>
        </div>
    </div>
</footer>
