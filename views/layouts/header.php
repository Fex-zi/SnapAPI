<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= SITE_TAGLINE ?>">
    <title><?= $pageTitle ?? SITE_NAME ?> - <?= SITE_NAME ?></title>

    <!-- CSS -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= asset('images/favicon.ico') ?>">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-content">
                <a href="<?= url('/') ?>" class="logo">
                    Research<span>Flow</span>
                </a>

                <ul class="nav-menu">
                    <?php if (isLoggedIn()): ?>
                        <!-- Logged in navigation -->
                        <li><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                        <li><a href="<?= url('saved-searches') ?>">Saved Searches</a></li>
                        <li><a href="<?= url('account') ?>">Account</a></li>
                        <li><a href="<?= url('logout') ?>" class="btn btn-secondary">Logout</a></li>
                    <?php else: ?>
                        <!-- Public navigation -->
                        <li><a href="<?= url('/') ?>">Home</a></li>
                        <li><a href="<?= url('pricing') ?>">Pricing</a></li>
                        <li><a href="<?= url('about') ?>">About</a></li>
                        <li><a href="<?= url('login') ?>">Login</a></li>
                        <li><a href="<?= url('register') ?>" class="btn btn-primary">Sign Up Free</a></li>
                    <?php endif; ?>
                </ul>

                <!-- Mobile menu toggle -->
                <button class="mobile-menu-toggle" id="mobileMenuToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </nav>

    <!-- Flash messages -->
    <?php
    $flash = getFlash();
    if ($flash):
    ?>
    <div class="flash-message flash-<?= $flash['type'] ?>">
        <div class="container">
            <p><?= htmlspecialchars($flash['message']) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <main class="main-content">
