<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/layouts/header.php';

// Get current user
$user = getCurrentUser();
if (!$user) {
    redirect('login');
}

// Get user stats
$userObj = new User();
$searchStats = $userObj->getSearchStats($user['id']);

// Get plan info
$currentPlan = PLANS[$user['plan']];
$searchesRemaining = getRemainingSearches($user);
?>

<div class="container dashboard-container">
    <!-- Welcome Header -->
    <div class="card dashboard-header">
        <h1 class="mb-2">Welcome back, <?= htmlspecialchars($user['name']) ?>! 👋</h1>
        <p>
            <strong><?= htmlspecialchars($currentPlan['name']) ?> Plan</strong>
            <?php if ($user['plan'] === 'free'): ?>
                • <a href="<?= url('pricing') ?>">Upgrade to unlock more features</a>
            <?php endif; ?>
        </p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-3 mt-3">
        <div class="card text-center feature-card">
            <div class="feature-icon blue dashboard-stats-icon">🔍</div>
            <div class="dashboard-stats-card heading-primary">
                <?= is_numeric($searchesRemaining) ? $searchesRemaining : '∞' ?>
            </div>
            <p class="dashboard-stats-label">Searches Remaining</p>
            <?php if (is_numeric($searchesRemaining) && $searchesRemaining < 5): ?>
                <small class="dashboard-warning">
                    Running low! <a href="<?= url('pricing') ?>">Upgrade</a>
                </small>
            <?php endif; ?>
        </div>

        <div class="card text-center feature-card crimson">
            <div class="feature-icon crimson dashboard-stats-icon">📊</div>
            <div class="dashboard-stats-card heading-secondary">
                <?= $searchStats['total_searches'] ?? 0 ?>
            </div>
            <p class="dashboard-stats-label">Total Searches</p>
        </div>

        <div class="card text-center feature-card darkblue">
            <div class="feature-icon darkblue dashboard-stats-icon">📅</div>
            <div class="dashboard-stats-card heading-accent">
                <?= $searchStats['week_searches'] ?? 0 ?>
            </div>
            <p class="dashboard-stats-label">This Week</p>
        </div>
    </div>

    <!-- Quick Search -->
    <div class="card mt-3 card-border-left-primary">
        <h2 class="mb-3">🔎 Quick Search</h2>

        <form action="<?= url('search') ?>" method="GET">
            <!-- Search Query -->
            <div class="form-group">
                <input
                    type="text"
                    name="query"
                    class="form-control"
                    placeholder="Search conversations... (e.g., 'struggling with email marketing')"
                    required
                    autofocus
                >
            </div>

            <!-- Source Selection -->
            <div class="form-group">
                <select name="source" class="form-control">
                    <option value="reddit">Reddit</option>
                    <option value="hackernews">Hacker News</option>
                    <option value="producthunt">Product Hunt</option>
                    <option value="twitter" disabled>Twitter (Coming Soon)</option>
                </select>
            </div>

            <!-- Advanced Filters (Expandable) -->
            <details class="search-filters-toggle">
                <summary class="search-filters-summary">
                    ⚙️ Advanced Filters (Optional)
                </summary>
                <div class="search-filters-content">
                    <div class="form-group">
                        <label for="subreddit" class="form-label">Specific Subreddit <span class="text-secondary">(Reddit only)</span></label>
                        <input
                            type="text"
                            id="subreddit"
                            name="subreddit"
                            class="form-control"
                            placeholder="e.g., entrepreneur, startups, marketing"
                        >
                        <small class="form-text">Leave blank to search all subreddits</small>
                    </div>

                    <div class="form-group">
                        <label for="sort" class="form-label">Sort By <span class="text-secondary">(All sources)</span></label>
                        <select id="sort" name="sort" class="form-control">
                            <option value="relevance">Relevance</option>
                            <option value="top">Top (Most Upvotes/Points)</option>
                            <option value="new">Newest First</option>
                            <option value="comments">Most Comments</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="time" class="form-label">Time Range <span class="text-secondary">(All sources)</span></label>
                        <select id="time" name="time" class="form-control">
                            <option value="all">All Time</option>
                            <option value="year">Past Year</option>
                            <option value="month" selected>Past Month</option>
                            <option value="week">Past Week</option>
                            <option value="day">Past 24 Hours</option>
                        </select>
                    </div>
                </div>
            </details>

            <button type="submit" class="btn btn-primary btn-block btn-lg">
                🔍 Search Conversations
            </button>

            <div class="search-tips mt-2">
                <small class="text-secondary">
                    <strong>💡 Search tips:</strong> Use specific keywords like "frustrated with", "looking for", "alternative to"
                </small>
            </div>
        </form>
    </div>

    <!-- Recent Searches -->
    <div class="card mt-3 card-border-left-secondary">
        <h2 class="mb-3">📋 Recent Searches</h2>

        <?php
        // Fetch recent searches
        $db = getDB();
        $stmt = $db->prepare("
            SELECT * FROM searches
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$user['id']]);
        $recentSearches = $stmt->fetchAll();
        ?>

        <?php if (empty($recentSearches)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🔍</div>
                <p class="empty-state-title">No searches yet</p>
                <p>Start by searching for a keyword or topic above!</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Query</th>
                            <th>Source</th>
                            <th class="text-center">Results</th>
                            <th>Date</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentSearches as $search): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($search['query']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge">
                                        <?= htmlspecialchars(ucfirst($search['source'])) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?= formatNumber($search['results_count']) ?>
                                </td>
                                <td class="text-secondary">
                                    <?= timeAgo($search['created_at']) ?>
                                </td>
                                <td class="text-right">
                                    <a href="<?= url('results/' . $search['id']) ?>" class="btn btn-secondary btn-small">
                                        View Results
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick Links -->
    <div class="grid grid-2 mt-3">
        <div class="card feature-card crimson">
            <h3 class="mb-2">💾 Saved Searches</h3>
            <p class="text-secondary mb-2">
                Save your searches and get email alerts when new conversations appear.
            </p>
            <a href="<?= url('saved-searches') ?>" class="btn btn-secondary">
                View Saved Searches
            </a>
        </div>

        <div class="card feature-card darkblue">
            <h3 class="mb-2">⚙️ Account Settings</h3>
            <p class="text-secondary mb-2">
                Update your profile, change your plan, or manage your subscription.
            </p>
            <a href="<?= url('account') ?>" class="btn btn-secondary">
                Go to Settings
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
