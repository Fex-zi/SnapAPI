<?php
$pageTitle = 'Saved Searches';
require_once __DIR__ . '/layouts/header.php';

// Get current user
$user = getCurrentUser();
if (!$user) {
    redirect('login');
}

// Get saved searches
$db = getDB();
$stmt = $db->prepare("SELECT * FROM saved_searches WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$savedSearches = $stmt->fetchAll();
?>

<div class="container dashboard-container">
    <!-- Header -->
    <div class="card dashboard-header">
        <h1 class="mb-2">💾 Saved Searches</h1>
        <p>Set up email alerts to get notified when new conversations match your search queries.</p>
    </div>

    <!-- Coming Soon Notice -->
    <div class="card mt-3 card-border-left-primary text-center">
        <div class="placeholder-icon">🚧</div>
        <h2 class="mb-2">Feature Coming Soon!</h2>
        <p class="text-secondary placeholder-content mb-3" style="max-width: 600px; margin: 0 auto;">
            The saved searches feature is currently under development. Soon you'll be able to:
        </p>

        <div class="grid grid-2 mt-3" style="max-width: 800px; margin: 0 auto;">
            <div class="card card-border-left-secondary">
                <h3 class="mb-2">📧 Email Alerts</h3>
                <p class="text-secondary">Get daily or weekly email notifications when new Reddit posts match your saved queries.</p>
            </div>

            <div class="card card-border-left-accent">
                <h3 class="mb-2">🔔 Smart Notifications</h3>
                <p class="text-secondary">Only get notified when conversations have high engagement or match specific criteria.</p>
            </div>

            <div class="card card-border-left-primary">
                <h3 class="mb-2">📊 Trending Topics</h3>
                <p class="text-secondary">Track how topics evolve over time and spot emerging trends early.</p>
            </div>

            <div class="card card-border-left-secondary">
                <h3 class="mb-2">⚙️ Custom Filters</h3>
                <p class="text-secondary">Set minimum upvotes, specific subreddits, and other filters for your alerts.</p>
            </div>
        </div>

        <div class="mt-3">
            <a href="<?= url('dashboard') ?>" class="btn btn-primary btn-lg">Back to Dashboard</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
