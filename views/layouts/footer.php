    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><?= SITE_NAME ?></h3>
                    <p><?= SITE_TAGLINE ?></p>
                </div>

                <div class="footer-section">
                    <h4>Product</h4>
                    <ul>
                        <li><a href="<?= url('pricing') ?>">Pricing</a></li>
                        <li><a href="<?= url('about') ?>">About</a></li>
                        <?php if (isLoggedIn()): ?>
                            <li><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Contact</h4>
                    <ul>
                        <li><a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
