<?php
/**
 * Process Search Request
 * Fetches results from Reddit API and saves to database
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Helpers/functions.php';
require_once __DIR__ . '/reddit.php';

// This file should be called via AJAX or background job
// For now, we'll process searches synchronously

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Verify user is logged in
session_start();
$user = getCurrentUser();
if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get search ID
$searchId = intval($_POST['search_id'] ?? 0);

if (!$searchId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing search_id']);
    exit;
}

// Get search details from database
$db = getDB();
$stmt = $db->prepare("SELECT * FROM searches WHERE id = ? AND user_id = ?");
$stmt->execute([$searchId, $user['id']]);
$search = $stmt->fetch();

if (!$search) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Search not found']);
    exit;
}

// Check if already processed
if ($search['status'] === 'completed') {
    echo json_encode([
        'success' => true,
        'message' => 'Search already processed',
        'results_count' => $search['results_count']
    ]);
    exit;
}

try {
    // Update search status to processing
    $stmt = $db->prepare("UPDATE searches SET status = 'processing' WHERE id = ?");
    $stmt->execute([$searchId]);

    // Initialize Reddit API
    $reddit = new RedditAPI();

    // Prepare search options
    $options = [
        'limit' => REDDIT_FETCH_LIMIT,
        'sort' => $search['filters']['sort'] ?? 'relevance',
    ];

    // Add time filter if specified
    if (isset($search['filters']['time']) && $search['filters']['time'] !== 'all') {
        $options['time'] = $search['filters']['time'];
    }

    // Add subreddit filter if specified
    if (!empty($search['filters']['subreddit'])) {
        $options['subreddit'] = $search['filters']['subreddit'];
    }

    // Perform search
    $results = $reddit->search($search['query'], $options);

    // Save results to database
    $savedCount = $reddit->saveResults($searchId, $results);

    // Update search status to completed
    $stmt = $db->prepare("UPDATE searches SET status = 'completed', completed_at = NOW() WHERE id = ?");
    $stmt->execute([$searchId]);

    // Log activity
    logActivity('search_completed', "Search completed: {$search['query']} ({$savedCount} results)", $user['id']);

    echo json_encode([
        'success' => true,
        'message' => 'Search completed successfully',
        'results_count' => $savedCount
    ]);

} catch (Exception $e) {
    // Update search status to failed
    $stmt = $db->prepare("UPDATE searches SET status = 'failed' WHERE id = ?");
    $stmt->execute([$searchId]);

    error_log('Search processing failed: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to process search: ' . $e->getMessage()
    ]);
}
