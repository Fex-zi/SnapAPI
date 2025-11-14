<?php
$pageTitle = 'Pricing';
require_once __DIR__ . '/layouts/header.php';

$plans = PLANS;
?>

<!-- Hero Section -->
<section class="hero hero-small">
    <div class="container">
        <div class="hero-text-wrapper">
            <h6>PRICING PLANS</h6>
            <h2>Simple, <em>Transparent</em> Pricing</h2>
            <p>Choose the plan that fits your needs. Upgrade or downgrade anytime.</p>
        </div>
    </div>
</section>

<!-- Pricing Cards -->
<section class="section-padding section-bg-white">
    <div class="container">
        <div class="grid grid-3">
            <!-- Free Plan -->
            <div class="pricing-card">
                <div class="pricing-header">
                    <h3><?= $plans['free']['name'] ?></h3>
                    <div class="pricing-price">
                        $<?= $plans['free']['price'] ?>
                        <span>/month</span>
                    </div>
                </div>

                <ul class="pricing-features">
                    <?php foreach ($plans['free']['features'] as $feature): ?>
                        <li><?= htmlspecialchars($feature) ?></li>
                    <?php endforeach; ?>
                </ul>

                <a href="<?= url('register') ?>" class="btn btn-primary btn-block">Get Started</a>
            </div>

            <!-- Starter Plan (Featured) -->
            <div class="pricing-card featured">
                <div class="pricing-badge">
                    <strong>MOST POPULAR</strong>
                </div>
                <div class="pricing-header">
                    <h3><?= $plans['starter']['name'] ?></h3>
                    <div class="pricing-price">
                        $<?= $plans['starter']['price'] ?>
                        <span>/month</span>
                    </div>
                </div>

                <ul class="pricing-features">
                    <?php foreach ($plans['starter']['features'] as $feature): ?>
                        <li><?= htmlspecialchars($feature) ?></li>
                    <?php endforeach; ?>
                </ul>

                <?php if (isLoggedIn()): ?>
                    <a href="<?= url('upgrade/starter') ?>" class="btn btn-primary btn-block">Upgrade Now</a>
                <?php else: ?>
                    <a href="<?= url('register') ?>" class="btn btn-primary btn-block">Start Free Trial</a>
                <?php endif; ?>
            </div>

            <!-- Pro Plan -->
            <div class="pricing-card">
                <div class="pricing-header">
                    <h3><?= $plans['pro']['name'] ?></h3>
                    <div class="pricing-price">
                        $<?= $plans['pro']['price'] ?>
                        <span>/month</span>
                    </div>
                </div>

                <ul class="pricing-features">
                    <?php foreach ($plans['pro']['features'] as $feature): ?>
                        <li><?= htmlspecialchars($feature) ?></li>
                    <?php endforeach; ?>
                </ul>

                <?php if (isLoggedIn()): ?>
                    <a href="<?= url('upgrade/pro') ?>" class="btn btn-primary btn-block">Upgrade Now</a>
                <?php else: ?>
                    <a href="<?= url('register') ?>" class="btn btn-primary btn-block">Start Free Trial</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="section-padding section-bg-gradient">
    <div class="container">
        <h2 class="text-center mb-4 heading-lg">Frequently Asked Questions</h2>

        <div class="faq-wrapper">
            <div class="card mb-3">
                <h3 class="mb-2">Can I change plans anytime?</h3>
                <p class="text-secondary">Yes! You can upgrade, downgrade, or cancel your subscription at any time from your account settings.</p>
            </div>

            <div class="card mb-3">
                <h3 class="mb-2">What payment methods do you accept?</h3>
                <p class="text-secondary">We accept all major credit cards (Visa, Mastercard, American Express) via Stripe.</p>
            </div>

            <div class="card mb-3">
                <h3 class="mb-2">Do you offer refunds?</h3>
                <p class="text-secondary">Yes, we offer a 14-day money-back guarantee. If you're not satisfied, contact us for a full refund.</p>
            </div>

            <div class="card mb-3">
                <h3 class="mb-2">What happens when I hit my search limit?</h3>
                <p class="text-secondary">Your searches reset at the start of each billing month. You can upgrade anytime to get more searches.</p>
            </div>

            <div class="card mb-3">
                <h3 class="mb-2">Is there a team plan?</h3>
                <p class="text-secondary">Not yet, but we're working on it! Contact us at <?= SITE_EMAIL ?> if you're interested in a team account.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-wavy">
    <div class="container text-center cta-wavy-content">
        <h2 class="heading-lg mb-2">Ready to Get Started?</h2>
        <p class="heading-md">Join hundreds of businesses using <?= SITE_NAME ?> to understand their customers better.</p>
        <a href="<?= url('register') ?>" class="btn btn-lg cta-button-white">Start Free Trial</a>
    </div>
</section>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
