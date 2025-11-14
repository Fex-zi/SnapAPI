<?php
$pageTitle = 'Home';
require_once __DIR__ . '/layouts/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-grid">
            <div class="hero-content">
                <h6>WELCOME TO <?= strtoupper(SITE_NAME) ?></h6>
                <h2>Discover <em>Customer Insights</em> from <span>Social</span> Conversations</h2>
                <p>Search thousands of Reddit & Twitter conversations in seconds. Find pain points, validate product ideas, and understand what your customers really want.</p>
                <div class="hero-buttons">
                    <?php if (isLoggedIn()): ?>
                        <a href="<?= url('dashboard') ?>" class="btn btn-primary btn-lg">Go to Dashboard</a>
                        <a href="<?= url('search') ?>" class="btn btn-secondary btn-lg">Start Searching</a>
                    <?php else: ?>
                        <a href="<?= url('register') ?>" class="btn btn-primary btn-lg">Start Free Trial</a>
                        <a href="<?= url('pricing') ?>" class="btn btn-secondary btn-lg">View Pricing</a>
                    <?php endif; ?>
                </div>
                <p class="hero-features">✓ No credit card required  •  ✓ 5 free searches  •  ✓ Setup in 30 seconds</p>
            </div>
            <div class="hero-image">
                <div class="hero-image-frame">
                    <img src="<?= asset('images/banner-right-image.png') ?>" alt="ResearchFlow Dashboard">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="section-padding section-bg-gradient">
    <div class="container">
        <h2 class="text-center mb-4 heading-lg">Why <?= SITE_NAME ?>?</h2>

        <div class="grid grid-3 mt-3">
            <div class="card text-center feature-card">
                <div class="feature-icon blue">🔍</div>
                <h3 class="mb-2">Search Conversations</h3>
                <p>Search thousands of Reddit posts and comments by keyword, subreddit, or topic in seconds.</p>
            </div>

            <div class="card text-center feature-card crimson">
                <div class="feature-icon crimson">🤖</div>
                <h3 class="mb-2">AI-Powered Analysis</h3>
                <p>Our AI automatically extracts pain points, sentiment, and key insights from conversations.</p>
            </div>

            <div class="card text-center feature-card darkblue">
                <div class="feature-icon darkblue">📧</div>
                <h3 class="mb-2">Email Alerts</h3>
                <p>Save searches and get daily or weekly email alerts when new relevant conversations appear.</p>
            </div>

            <div class="card text-center feature-card">
                <div class="feature-icon blue">💾</div>
                <h3 class="mb-2">Save & Export</h3>
                <p>Save your favorite searches and export results to CSV for further analysis.</p>
            </div>

            <div class="card text-center feature-card crimson">
                <div class="feature-icon crimson">⚡</div>
                <h3 class="mb-2">Lightning Fast</h3>
                <p>Get results in seconds. No more manual scrolling through hundreds of Reddit threads.</p>
            </div>

            <div class="card text-center feature-card darkblue">
                <div class="feature-icon darkblue">🎯</div>
                <h3 class="mb-2">Find Your Audience</h3>
                <p>Discover where your target customers hang out and what they're talking about.</p>
            </div>
        </div>
    </div>
</section>

<!-- Use Cases Section -->
<section class="section-padding section-bg-gradient">
    <div class="container">
        <h2 class="text-center mb-4 heading-lg">Perfect For</h2>

        <div class="grid grid-2 mt-3">
            <div class="card use-case-card card-border-left-primary">
                <h3 class="heading-primary">🚀 SaaS Founders</h3>
                <p>Validate product ideas, find customer pain points, and discover feature requests before building.</p>
            </div>

            <div class="card use-case-card card-border-left-secondary">
                <h3 class="heading-secondary">📝 Content Creators</h3>
                <p>Find trending topics, understand what your audience cares about, and create content that resonates.</p>
            </div>

            <div class="card use-case-card card-border-left-accent">
                <h3 class="heading-accent">📊 Marketers</h3>
                <p>Understand customer language, find pain points for ad copy, and discover where your audience hangs out.</p>
            </div>

            <div class="card use-case-card card-border-left-primary">
                <h3 class="heading-primary">💼 Sales Teams</h3>
                <p>Find leads in niche communities, understand customer needs, and join conversations authentically.</p>
            </div>
        </div>
    </div>
</section>

<!-- Social Proof Section -->
<section class="section-padding section-bg-white">
    <div class="container text-center">
        <h2 class="heading-lg mb-4">Trusted by Growing Businesses</h2>
        <div class="grid grid-3 mt-3">
            <div>
                <div class="stats-number">10K+</div>
                <p class="stats-label">Searches Performed</p>
            </div>
            <div>
                <div class="stats-number">500+</div>
                <p class="stats-label">Active Users</p>
            </div>
            <div>
                <div class="stats-number">50K+</div>
                <p class="stats-label">Conversations Analyzed</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-wavy">
    <div class="container text-center cta-wavy-content">
        <h2 class="heading-lg mb-2">Ready to Discover Customer Insights?</h2>
        <p class="heading-md">
            <?php if (isLoggedIn()): ?>
                Start searching conversations now!
            <?php else: ?>
                Start your free trial today. No credit card required.
            <?php endif; ?>
        </p>
        <a href="<?= isLoggedIn() ? url('search') : url('register') ?>" class="btn btn-lg cta-button-white">
            <?= isLoggedIn() ? 'Start Searching' : 'Get Started Free' ?>
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
