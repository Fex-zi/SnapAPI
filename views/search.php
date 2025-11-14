<?php
$pageTitle = 'Search';
require_once __DIR__ . '/layouts/header.php';

// Get current user
$user = getCurrentUser();
if (!$user) {
    redirect('login');
}

// Check if user can perform searches
if (!canSearch($user)) {
    setFlash('error', 'You have reached your monthly search limit. Please upgrade your plan to continue searching.');
    redirect('pricing');
}

// Get search query from URL
$query = sanitize($_GET['query'] ?? '');
$source = sanitize($_GET['source'] ?? 'reddit');

// If query provided, fetch results and redirect
if (!empty($query)) {
    // Save search to database (but don't count against credits yet)
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO searches (user_id, query, source, results_count, created_at) VALUES (?, ?, ?, 0, NOW())");
    $stmt->execute([$user['id'], $query, $source]);
    $searchId = $db->lastInsertId();

    $apiSuccess = false; // Track if API call succeeds

    // Fetch results based on source
    if ($source === 'reddit') {
        try {
            require_once __DIR__ . '/../api/reddit.php';
            $reddit = new RedditAPI();

            // Build search options from advanced filters
            $options = [
                'limit' => REDDIT_FETCH_LIMIT,
                'sort' => sanitize($_GET['sort'] ?? 'relevance'),
                'time' => sanitize($_GET['time'] ?? 'month')
            ];

            // Add subreddit filter if specified
            $subreddit = sanitize($_GET['subreddit'] ?? '');
            if (!empty($subreddit)) {
                $options['subreddit'] = $subreddit;
            }

            // Fetch results from Reddit
            $results = $reddit->search($query, $options);

            // Save results to database
            if (!empty($results)) {
                $reddit->saveResults($searchId, $results);
            }

            $apiSuccess = true; // Mark as successful
        } catch (Exception $e) {
            // Show actual error for debugging
            error_log('Reddit API Error: ' . $e->getMessage());
            setFlash('error', 'Reddit API Error: ' . $e->getMessage());

            // Delete the failed search so it doesn't count
            $stmt = $db->prepare("DELETE FROM searches WHERE id = ?");
            $stmt->execute([$searchId]);
            redirect('search');
        }
    } elseif ($source === 'hackernews') {
        try {
            require_once __DIR__ . '/../api/hackernews.php';
            $hn = new HackerNewsAPI();

            // Build search options from advanced filters
            $options = [
                'limit' => 100,
                'sort' => sanitize($_GET['sort'] ?? 'relevance'),
                'time' => sanitize($_GET['time'] ?? 'month')
            ];

            // Fetch results from Hacker News
            $results = $hn->search($query, $options);

            // Save results to database
            if (!empty($results)) {
                $hn->saveResults($searchId, $results);
            }

            $apiSuccess = true; // Mark as successful
        } catch (Exception $e) {
            // Show actual error for debugging
            error_log('Hacker News API Error: ' . $e->getMessage());
            setFlash('error', 'Hacker News API Error: ' . $e->getMessage());

            // Delete the failed search so it doesn't count
            $stmt = $db->prepare("DELETE FROM searches WHERE id = ?");
            $stmt->execute([$searchId]);
            redirect('search');
        }
    } elseif ($source === 'producthunt') {
        try {
            require_once __DIR__ . '/../api/producthunt.php';
            $ph = new ProductHuntAPI();

            // Build search options
            $options = [
                'limit' => 50,
            ];

            // Fetch results from Product Hunt
            $results = $ph->search($query, $options);

            // Save results to database
            if (!empty($results)) {
                $ph->saveResults($searchId, $results);
            }

            $apiSuccess = true; // Mark as successful
        } catch (Exception $e) {
            // Show actual error for debugging
            error_log('Product Hunt API Error: ' . $e->getMessage());
            setFlash('error', 'Product Hunt API Error: ' . $e->getMessage());

            // Delete the failed search so it doesn't count
            $stmt = $db->prepare("DELETE FROM searches WHERE id = ?");
            $stmt->execute([$searchId]);
            redirect('search');
        }
    }

    // Only increment counter and log activity if API call succeeded
    if ($apiSuccess) {
        incrementSearchCounter($user['id']);
        logActivity('search', "Searched for: {$query}", $user['id']);

        // Redirect to results
        redirect("results/{$searchId}");
    }
}
?>

<div class="container search-container">
    <!-- Search Form -->
    <div class="card card-border-left-primary">
        <h2 class="mb-3 text-center">🔎 Search Social Conversations</h2>

        <form action="<?= url('search') ?>" method="GET">
            <!-- Search Query -->
            <div class="form-group">
                <label for="query" class="form-label">Search Query</label>
                <input
                    type="text"
                    id="query"
                    name="query"
                    class="form-control"
                    placeholder="e.g., 'struggling with email marketing' or 'best CRM software'"
                    value="<?= htmlspecialchars($query) ?>"
                    required
                    autofocus
                >
                <small class="form-text">
                    <strong>💡 Pro tip:</strong> Use phrases like "frustrated with", "looking for", "alternative to" for better results
                </small>
            </div>

            <!-- Source Selection -->
            <div class="form-group">
                <label for="source" class="form-label">Source</label>
                <select id="source" name="source" class="form-control">
                    <option value="reddit" <?= $source === 'reddit' ? 'selected' : '' ?>>Reddit</option>
                    <option value="hackernews" <?= $source === 'hackernews' ? 'selected' : '' ?>>Hacker News</option>
                    <option value="producthunt" <?= $source === 'producthunt' ? 'selected' : '' ?>>Product Hunt</option>
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
        </form>

        <!-- Search Info -->
        <div class="search-info-divider">
            <div class="grid grid-2">
                <div>
                    <strong>Searches Remaining:</strong>
                    <span class="search-remaining-count">
                        <?= is_numeric(getRemainingSearches($user)) ? getRemainingSearches($user) : '∞' ?>
                    </span>
                </div>
                <div class="text-right">
                    <a href="<?= url('pricing') ?>" class="font-semibold">Upgrade Plan →</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Examples -->
    <div class="card card-border-left-secondary mt-3">
        <h3 class="mb-3">📝 Search Examples</h3>
        <div class="grid grid-2">
            <div class="search-example-box">
                <strong>For SaaS Founders:</strong>
                <ul class="search-example-list">
                    <li>"frustrated with project management tools"</li>
                    <li>"looking for CRM alternative"</li>
                    <li>"email marketing too expensive"</li>
                </ul>
            </div>

            <div class="search-example-box">
                <strong>For Marketers:</strong>
                <ul class="search-example-list">
                    <li>"how to increase conversion rate"</li>
                    <li>"social media scheduling tool recommendations"</li>
                    <li>"struggling with content ideas"</li>
                </ul>
            </div>

            <div class="search-example-box">
                <strong>For Product Research:</strong>
                <ul class="search-example-list">
                    <li>"wish there was an app for"</li>
                    <li>"pain points of freelancers"</li>
                    <li>"what features are missing from"</li>
                </ul>
            </div>

            <div class="search-example-box">
                <strong>For Content Ideas:</strong>
                <ul class="search-example-list">
                    <li>"beginner mistakes in"</li>
                    <li>"how do I start with"</li>
                    <li>"best practices for"</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
