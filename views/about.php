<?php
$pageTitle = 'About';
require_once __DIR__ . '/layouts/header.php';
?>

<!-- Hero Section -->
<section class="hero hero-small">
    <div class="container">
        <div class="hero-text-wrapper">
            <h6>ABOUT US</h6>
            <h2>Helping Businesses Understand Their <em>Customers</em></h2>
            <p>We're on a mission to make customer research easy and accessible for everyone.</p>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="section-padding section-bg-white">
    <div class="container text-center">
        <h2 class="heading-lg mb-4">Making Customer Research Effortless</h2>
        <div class="grid grid-3 mt-3">
            <div class="card text-center">
                <div class="stats-number">10,000+</div>
                <p class="stats-label">Searches Performed</p>
            </div>
            <div class="card text-center">
                <div class="stats-number" style="color: var(--primary-color);">500+</div>
                <p class="stats-label">Happy Users</p>
            </div>
            <div class="card text-center">
                <div class="stats-number" style="color: var(--secondary-color);">50K+</div>
                <p class="stats-label">Insights Discovered</p>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Story -->
<section class="section-padding section-bg-gradient">
    <div class="container">
        <div class="grid grid-2 mt-3">
            <div class="card card-border-left-primary">
                <div class="about-icon">🎯</div>
                <h2 class="mb-2">Our Mission</h2>
                <p class="text-secondary about-text">
                    We believe that the best customer insights come from real conversations happening every day on social media.
                    <?= SITE_NAME ?> makes it easy for businesses to tap into these conversations, understand their customers'
                    pain points, and build better products.
                </p>
            </div>

            <div class="card card-border-left-secondary">
                <div class="about-icon">💡</div>
                <h2 class="mb-2">Why We Built This</h2>
                <p class="text-secondary about-text">
                    We were tired of spending hours manually scrolling through Reddit threads and Twitter conversations to understand
                    what our customers were saying. We knew there had to be a better way. So we built <?= SITE_NAME ?> to help
                    entrepreneurs, marketers, and product teams discover customer insights in seconds, not hours.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="section-padding section-bg-white">
    <div class="container">
        <h2 class="text-center mb-4 heading-lg">How It Works</h2>
        <div class="grid grid-2 mt-3">
            <div class="card text-center feature-card">
                <div class="feature-icon blue">1️⃣</div>
                <h3 class="mb-2">Search</h3>
                <p class="text-secondary">Enter a keyword or topic you want to research across thousands of social conversations.</p>
            </div>

            <div class="card text-center feature-card crimson">
                <div class="feature-icon crimson">2️⃣</div>
                <h3 class="mb-2">Analyze</h3>
                <p class="text-secondary">Our AI automatically extracts pain points, sentiment, and key insights from the results.</p>
            </div>

            <div class="card text-center feature-card darkblue">
                <div class="feature-icon darkblue">3️⃣</div>
                <h3 class="mb-2">Save & Alert</h3>
                <p class="text-secondary">Save your searches and get email alerts when new relevant conversations appear.</p>
            </div>

            <div class="card text-center feature-card black">
                <div class="feature-icon black">4️⃣</div>
                <h3 class="mb-2">Act on Insights</h3>
                <p class="text-secondary">Use these insights to build better products, create content, and find your ideal customers.</p>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="section-padding section-bg-gradient">
    <div class="container text-center">
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <div class="about-icon">📧</div>
            <h2 class="mb-2">Get In Touch</h2>
            <p class="text-secondary about-text mb-3">
                We'd love to hear from you! Whether you have questions, feedback, or just want to say hi,
                feel free to reach out.
            </p>
            <a href="mailto:<?= SITE_EMAIL ?>" class="btn btn-primary btn-lg">Contact Us</a>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-wavy">
    <div class="container text-center cta-wavy-content">
        <h2 class="heading-lg mb-2">Ready to Get Started?</h2>
        <p class="heading-md">Join hundreds of businesses using <?= SITE_NAME ?> today.</p>
        <a href="<?= url('register') ?>" class="btn btn-lg cta-button-white">Start Free Trial</a>
    </div>
</section>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
