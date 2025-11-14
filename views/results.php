<?php
$pageTitle = 'Search Results';
require_once __DIR__ . '/layouts/header.php';

// Get current user
$user = getCurrentUser();
if (!$user) {
    redirect('login');
}

// Get search ID from URL
$searchId = intval($_GET['search_id'] ?? 0);

if (!$searchId) {
    setFlash('error', 'Invalid search ID.');
    redirect('dashboard');
}

// Get search details
$db = getDB();
$stmt = $db->prepare("SELECT * FROM searches WHERE id = ? AND user_id = ?");
$stmt->execute([$searchId, $user['id']]);
$search = $stmt->fetch();

if (!$search) {
    setFlash('error', 'Search not found or access denied.');
    redirect('dashboard');
}

// Fetch results from database via junction table
$stmt = $db->prepare("
    SELECT c.*
    FROM conversations c
    INNER JOIN search_results sr ON c.id = sr.conversation_id
    WHERE sr.search_id = ?
    ORDER BY c.score DESC, c.created_at_source DESC
");
$stmt->execute([$searchId]);
$results = $stmt->fetchAll();

$isLoading = false;
?>

<div class="container dashboard-container">
    <!-- Search Info Header -->
    <div class="card results-header-card">
        <div class="results-header">
            <div>
                <h1 class="mb-2">🔍 Search Results</h1>
                <p>
                    Query: <strong><?= htmlspecialchars($search['query']) ?></strong>
                    • Source: <strong><?= htmlspecialchars(ucfirst($search['source'])) ?></strong>
                    • <?= timeAgo($search['created_at']) ?>
                </p>
            </div>
            <div class="results-actions">
                <a href="<?= url('search') ?>" class="btn btn-white btn-lg">New Search</a>
                <button class="btn btn-ghost-white btn-lg" onclick="Modal.alert('Save search feature coming soon! This will allow you to get email alerts when new conversations match your query.', 'Coming Soon')">💾 Save Search</button>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <?php
    // Calculate stats
    $totalResults = count($results);

    // For Reddit: count unique subreddits
    // For others: count total comments
    if ($search['source'] === 'reddit') {
        $subreddits = array_filter(array_column($results, 'category'));
        $middleStat = !empty($subreddits) ? count(array_unique($subreddits)) : 0;
        $middleLabel = 'Subreddits';
    } else {
        $middleStat = !empty($results) ? array_sum(array_column($results, 'num_comments')) : 0;
        $middleLabel = 'Total Comments';
    }

    $avgScore = !empty($results) ? round(array_sum(array_column($results, 'score')) / $totalResults) : 0;
    ?>
    <div class="grid grid-3 mt-3">
        <div class="card text-center feature-card">
            <div class="stats-number heading-primary"><?= $totalResults ?></div>
            <p class="stats-label">Results Found</p>
        </div>
        <div class="card text-center feature-card crimson">
            <div class="stats-number heading-secondary"><?= formatNumber($middleStat) ?></div>
            <p class="stats-label"><?= $middleLabel ?></p>
        </div>
        <div class="card text-center feature-card darkblue">
            <div class="stats-number heading-accent"><?= formatNumber($avgScore) ?></div>
            <p class="stats-label">Avg. Score</p>
        </div>
    </div>

    <!-- Results List -->
    <?php if (false): ?>
    <div class="card placeholder-state">
        <div class="placeholder-icon">🚧</div>
        <h2 class="mb-2">Reddit API Integration In Progress</h2>
        <p class="text-secondary placeholder-content mb-3">
            The search results page is ready! Next step is to integrate the Reddit API to fetch real conversations.
            For now, here's what the results will look like:
        </p>

        <!-- Sample Result (Preview) -->
        <div class="sample-result card">
            <div class="results-header mb-2">
                <div>
                    <h3 class="mb-2">
                        <a href="#" class="text-primary">Sample Reddit Post Title</a>
                    </h3>
                    <div class="result-meta">
                        <span>👤 u/username</span>
                        <span>📁 r/entrepreneur</span>
                        <span>⬆️ 234 upvotes</span>
                        <span>💬 45 comments</span>
                        <span>🕐 2 days ago</span>
                    </div>
                </div>
            </div>
            <p class="result-text">
                This is a sample of what a Reddit post will look like. It will show the post content,
                along with metadata like upvotes, comments, and the subreddit it's from...
            </p>
            <div class="result-tags">
                <span class="tag tag-warning">
                    💡 Pain Point Detected
                </span>
                <span class="tag tag-info">
                    🎯 High Engagement
                </span>
            </div>
        </div>

        <div class="mt-3">
            <a href="<?= url('dashboard') ?>" class="btn btn-primary btn-lg">Back to Dashboard</a>
        </div>
    </div>

    <!-- Future: Filters & Stats -->
    <div class="grid grid-3 mt-3 stats-grid">
        <div class="card text-center">
            <div class="stats-number">0</div>
            <p class="stats-label">Results Found</p>
        </div>
        <div class="card text-center">
            <div class="stats-number" style="color: #10b981;">0</div>
            <p class="stats-label">Pain Points</p>
        </div>
        <div class="card text-center">
            <div class="dashboard-stats-card heading-secondary">0</div>
            <p class="stats-label">Subreddits</p>
        </div>
    </div>

    <?php else: ?>
    <!-- Real Results -->
    <div class="card mt-3 card-border-left-primary">
        <h2 class="mb-3">💬 Conversations</h2>

        <?php if (empty($results)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">🔍</div>
                <p class="empty-state-title">No results found for this search.</p>
                <p class="text-secondary">Try using different keywords or broaden your search filters.</p>
                <a href="<?= url('search') ?>" class="btn btn-primary btn-lg mt-2">Try Another Search</a>
            </div>
        <?php else: ?>
            <?php foreach ($results as $index => $result): ?>
                <div class="result-item <?= $index > 0 ? 'mt-3' : '' ?>">
                    <div class="result-item-header">
                        <h3 class="result-item-title">
                            <a href="<?= htmlspecialchars($result['url']) ?>" target="_blank" rel="noopener">
                                <?= htmlspecialchars($result['title']) ?>
                            </a>
                        </h3>
                        <div class="result-meta">
                            <span class="badge">👤 <?= $result['source'] === 'reddit' ? 'u/' : '' ?><?= htmlspecialchars($result['author']) ?></span>
                            <?php if (!empty($result['category'])): ?>
                                <span class="badge">
                                    <?php if ($result['source'] === 'reddit'): ?>
                                        📁 r/<?= htmlspecialchars($result['category']) ?>
                                    <?php elseif ($result['source'] === 'producthunt'): ?>
                                        🏷️ <?= htmlspecialchars($result['category']) ?>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                            <span class="badge">
                                <?php if ($result['source'] === 'producthunt'): ?>
                                    ▲ <?= formatNumber($result['score']) ?> upvotes
                                <?php elseif ($result['source'] === 'hackernews'): ?>
                                    ⬆️ <?= formatNumber($result['score']) ?> points
                                <?php else: ?>
                                    ⬆️ <?= formatNumber($result['score']) ?> upvotes
                                <?php endif; ?>
                            </span>
                            <span class="badge">💬 <?= formatNumber($result['num_comments']) ?> comments</span>
                            <span class="badge">🕐 <?= timeAgo($result['created_at_source']) ?></span>
                        </div>
                    </div>

                    <?php if (!empty($result['body'])): ?>
                        <p class="result-text">
                            <?= nl2br(htmlspecialchars(substr($result['body'], 0, 400))) ?>
                            <?php if (strlen($result['body']) > 400): ?>
                                <a href="<?= htmlspecialchars($result['url']) ?>" target="_blank">... Read more →</a>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>

                <?php if ($index < count($results) - 1): ?>
                    <hr class="result-divider">
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
