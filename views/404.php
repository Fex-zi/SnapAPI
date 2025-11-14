<?php
$pageTitle = '404 - Page Not Found';
require_once __DIR__ . '/layouts/header.php';
?>

<div class="container text-center error-page">
    <div class="error-icon">🔍</div>
    <h1 class="error-title">404 - Page Not Found</h1>
    <p class="error-message">
        Sorry, we couldn't find the page you're looking for.
    </p>

    <div class="error-buttons">
        <a href="<?= url('/') ?>" class="btn btn-primary btn-lg">Go Home</a>
        <?php if (!isLoggedIn()): ?>
            <a href="<?= url('register') ?>" class="btn btn-secondary btn-lg">Sign Up</a>
        <?php else: ?>
            <a href="<?= url('dashboard') ?>" class="btn btn-secondary btn-lg">Go to Dashboard</a>
        <?php endif; ?>
    </div>

    <div class="error-links">
        <p class="text-secondary">Looking for something specific?</p>
        <ul>
            <li><a href="<?= url('/') ?>">Home</a></li>
            <li><a href="<?= url('pricing') ?>">Pricing</a></li>
            <li><a href="<?= url('about') ?>">About</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="<?= url('dashboard') ?>">Dashboard</a></li>
            <?php else: ?>
                <li><a href="<?= url('login') ?>">Login</a></li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
