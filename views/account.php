<?php
$pageTitle = 'Account Settings';
require_once __DIR__ . '/layouts/header.php';

// Get current user
$user = getCurrentUser();
if (!$user) {
    redirect('login');
}

// Get plan info
$currentPlan = PLANS[$user['plan']];
?>

<div class="container dashboard-container">
    <!-- Header -->
    <div class="card dashboard-header">
        <h1 class="mb-2">⚙️ Account Settings</h1>
        <p>Manage your profile, subscription, and preferences.</p>
    </div>

    <!-- Account Info -->
    <div class="grid grid-2 mt-3">
        <!-- Profile Info -->
        <div class="card card-border-left-primary">
            <h2 class="mb-3">👤 Profile Information</h2>

            <div class="form-group">
                <label class="form-label">Name</label>
                <p class="text-secondary"><?= htmlspecialchars($user['name']) ?></p>
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <p class="text-secondary"><?= htmlspecialchars($user['email']) ?></p>
            </div>

            <div class="form-group">
                <label class="form-label">Member Since</label>
                <p class="text-secondary"><?= date('F j, Y', strtotime($user['created_at'])) ?></p>
            </div>

            <button class="btn btn-secondary" onclick="Modal.alert('Profile editing feature coming soon! You\'ll be able to update your name, email, and password.', 'Coming Soon')">
                Edit Profile
            </button>
        </div>

        <!-- Current Plan -->
        <div class="card card-border-left-secondary">
            <h2 class="mb-3">💳 Current Plan</h2>

            <div class="form-group">
                <label class="form-label">Plan</label>
                <p class="text-secondary">
                    <strong style="font-size: 1.25rem; color: var(--primary-color);">
                        <?= htmlspecialchars($currentPlan['name']) ?>
                    </strong>
                </p>
            </div>

            <div class="form-group">
                <label class="form-label">Price</label>
                <p class="text-secondary">
                    <?php if ($currentPlan['price'] == 0): ?>
                        Free
                    <?php else: ?>
                        $<?= $currentPlan['price'] ?>/month
                    <?php endif; ?>
                </p>
            </div>

            <div class="form-group">
                <label class="form-label">Monthly Searches</label>
                <p class="text-secondary">
                    <?php if ($currentPlan['searches_per_month'] == -1): ?>
                        Unlimited
                    <?php else: ?>
                        <?= $currentPlan['searches_per_month'] ?> searches
                    <?php endif; ?>
                </p>
            </div>

            <?php if ($user['plan'] !== 'pro'): ?>
                <a href="<?= url('pricing') ?>" class="btn btn-primary">
                    Upgrade Plan
                </a>
            <?php else: ?>
                <button class="btn btn-secondary" onclick="Modal.alert('Subscription management coming soon! You\'ll be able to update payment methods, view invoices, and cancel your subscription.', 'Coming Soon')">
                    Manage Subscription
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Usage Stats -->
    <div class="card mt-3 card-border-left-accent">
        <h2 class="mb-3">📊 Usage Statistics</h2>

        <div class="grid grid-3">
            <div>
                <label class="form-label">Searches This Month</label>
                <p class="text-secondary">
                    <strong style="font-size: 1.5rem; color: var(--primary-color);">
                        <?= $user['searches_used_this_month'] ?>
                    </strong>
                </p>
            </div>

            <div>
                <label class="form-label">Remaining Searches</label>
                <p class="text-secondary">
                    <strong style="font-size: 1.5rem; color: var(--secondary-color);">
                        <?php
                        $remaining = getRemainingSearches($user);
                        echo is_numeric($remaining) ? $remaining : '∞';
                        ?>
                    </strong>
                </p>
            </div>

            <div>
                <label class="form-label">Next Reset</label>
                <p class="text-secondary">
                    <?php
                    $nextReset = new DateTime('first day of next month');
                    echo $nextReset->format('F j, Y');
                    ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="card mt-3" style="border-left: 4px solid #dc143c;">
        <h2 class="mb-3" style="color: #dc143c;">⚠️ Danger Zone</h2>

        <div class="form-group">
            <label class="form-label">Delete Account</label>
            <p class="text-secondary mb-2">
                Once you delete your account, there is no going back. All your searches and data will be permanently deleted.
            </p>
            <button class="btn" style="background-color: #dc143c; color: white;" onclick="Modal.alert('Account deletion feature coming soon! This will permanently delete all your data including searches, saved queries, and account information.', 'Delete Account')">
                Delete Account
            </button>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
