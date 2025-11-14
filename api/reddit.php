<?php
/**
 * Reddit API Integration
 * Handles Reddit OAuth and data fetching
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Helpers/functions.php';

class RedditAPI {
    private $clientId;
    private $clientSecret;
    private $userAgent;
    private $accessToken;
    private $tokenExpiry;

    public function __construct() {
        $this->clientId = REDDIT_CLIENT_ID;
        $this->clientSecret = REDDIT_CLIENT_SECRET;
        $this->userAgent = REDDIT_USER_AGENT;
        $this->accessToken = null;
        $this->tokenExpiry = null;
    }

    /**
     * Get OAuth access token
     */
    private function getAccessToken() {
        // Check if we have a valid cached token in session
        if (isset($_SESSION['reddit_token']) && isset($_SESSION['reddit_token_expiry'])) {
            if (time() < $_SESSION['reddit_token_expiry']) {
                return $_SESSION['reddit_token'];
            }
        }

        // Request new token
        $ch = curl_init('https://www.reddit.com/api/v1/access_token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'client_credentials'
        ]));
        curl_setopt($ch, CURLOPT_USERPWD, $this->clientId . ':' . $this->clientSecret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: ' . $this->userAgent
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception('Failed to get Reddit access token. HTTP Code: ' . $httpCode);
        }

        $data = json_decode($response, true);

        if (!isset($data['access_token'])) {
            throw new Exception('Invalid token response from Reddit API');
        }

        // Cache token in session
        $_SESSION['reddit_token'] = $data['access_token'];
        $_SESSION['reddit_token_expiry'] = time() + ($data['expires_in'] - 60); // 60 second buffer

        return $data['access_token'];
    }

    /**
     * Make authenticated API request
     */
    private function makeRequest($endpoint, $params = []) {
        $token = $this->getAccessToken();

        $url = 'https://oauth.reddit.com' . $endpoint;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'User-Agent: ' . $this->userAgent
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception('Reddit API request failed. HTTP Code: ' . $httpCode);
        }

        return json_decode($response, true);
    }

    /**
     * Search Reddit posts and comments
     */
    public function search($query, $options = []) {
        $params = [
            'q' => $query,
            'limit' => $options['limit'] ?? 100,
            'sort' => $options['sort'] ?? 'relevance',
            'type' => 'link', // Search posts only
        ];

        // Add time filter if specified
        if (isset($options['time'])) {
            $params['t'] = $options['time']; // hour, day, week, month, year, all
        }

        // Add subreddit restriction if specified
        if (isset($options['subreddit'])) {
            $params['restrict_sr'] = 'true';
            $endpoint = '/r/' . $options['subreddit'] . '/search';
        } else {
            $endpoint = '/search';
        }

        $data = $this->makeRequest($endpoint, $params);
        $results = $this->parseSearchResults($data);

        // Apply relevance filtering and scoring
        $results = $this->filterRelevantResults($results, $query);

        return $results;
    }

    /**
     * Filter results based on relevance to query
     */
    private function filterRelevantResults($results, $query) {
        if (empty($results)) {
            return $results;
        }

        $queryWords = array_map('strtolower', preg_split('/\s+/', $query));

        $scored = [];
        foreach ($results as $result) {
            $score = 0;
            $titleLower = strtolower($result['title']);
            $contentLower = strtolower($result['content']);

            // Exact phrase match in title = very relevant
            if (stripos($titleLower, strtolower($query)) !== false) {
                $score += 100;
            }

            // Exact phrase match in content
            if (stripos($contentLower, strtolower($query)) !== false) {
                $score += 50;
            }

            // Count matching words in title and content
            foreach ($queryWords as $word) {
                if (strlen($word) > 2) { // Skip short words like "to", "for"
                    if (stripos($titleLower, $word) !== false) {
                        $score += 10;
                    }
                    if (stripos($contentLower, $word) !== false) {
                        $score += 5;
                    }
                }
            }

            // Boost score based on engagement
            $score += min($result['score'] / 10, 20); // Max 20 points from upvotes
            $score += min($result['num_comments'] / 5, 10); // Max 10 points from comments

            // Include all results - let Reddit's algorithm do the heavy lifting
            $result['relevance_score'] = $score;
            $scored[] = $result;
        }

        // Sort by relevance score (highest first)
        usort($scored, function($a, $b) {
            return $b['relevance_score'] - $a['relevance_score'];
        });

        return $scored;
    }

    /**
     * Parse search results into clean format
     */
    private function parseSearchResults($data) {
        if (!isset($data['data']['children'])) {
            return [];
        }

        $results = [];

        foreach ($data['data']['children'] as $child) {
            $post = $child['data'];

            // Skip if removed or deleted
            if (isset($post['removed_by_category']) || $post['author'] === '[deleted]') {
                continue;
            }

            $results[] = [
                'id' => $post['id'],
                'title' => $post['title'],
                'author' => $post['author'],
                'subreddit' => $post['subreddit'],
                'content' => $post['selftext'] ?? '',
                'url' => 'https://reddit.com' . $post['permalink'],
                'score' => $post['score'],
                'upvote_ratio' => $post['upvote_ratio'],
                'num_comments' => $post['num_comments'],
                'created_utc' => $post['created_utc'],
                'created_at' => date('Y-m-d H:i:s', $post['created_utc']),
                'thumbnail' => $post['thumbnail'] ?? null,
                'is_self' => $post['is_self'],
                'domain' => $post['domain'] ?? null,
            ];
        }

        return $results;
    }

    /**
     * Get comments from a specific post
     */
    public function getPostComments($subreddit, $postId, $limit = 100) {
        $endpoint = "/r/{$subreddit}/comments/{$postId}";
        $params = [
            'limit' => $limit,
            'sort' => 'top'
        ];

        $data = $this->makeRequest($endpoint, $params);

        // Comments are in the second element of the array
        if (!isset($data[1]['data']['children'])) {
            return [];
        }

        return $this->parseComments($data[1]['data']['children']);
    }

    /**
     * Parse comments into clean format
     */
    private function parseComments($children, $depth = 0) {
        $comments = [];

        foreach ($children as $child) {
            if ($child['kind'] !== 't1') continue; // Only process comments

            $comment = $child['data'];

            // Skip if removed or deleted
            if (isset($comment['body']) && ($comment['body'] === '[deleted]' || $comment['body'] === '[removed]')) {
                continue;
            }

            $parsedComment = [
                'id' => $comment['id'],
                'author' => $comment['author'],
                'body' => $comment['body'] ?? '',
                'score' => $comment['score'],
                'created_utc' => $comment['created_utc'],
                'created_at' => date('Y-m-d H:i:s', $comment['created_utc']),
                'depth' => $depth,
                'replies' => []
            ];

            // Parse nested replies
            if (isset($comment['replies']['data']['children'])) {
                $parsedComment['replies'] = $this->parseComments(
                    $comment['replies']['data']['children'],
                    $depth + 1
                );
            }

            $comments[] = $parsedComment;
        }

        return $comments;
    }

    /**
     * Get hot posts from a subreddit
     */
    public function getHotPosts($subreddit, $limit = 25) {
        $params = ['limit' => $limit];
        $endpoint = "/r/{$subreddit}/hot";

        $data = $this->makeRequest($endpoint, $params);
        return $this->parseSearchResults($data);
    }

    /**
     * Save results to database
     */
    public function saveResults($searchId, $results) {
        $db = getDB();

        $saved = 0;
        foreach ($results as $result) {
            try {
                // First, insert or get existing conversation
                $stmt = $db->prepare("
                    INSERT INTO conversations (
                        source,
                        source_id,
                        title,
                        body,
                        author,
                        category,
                        url,
                        score,
                        num_comments,
                        created_at_source
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        score = VALUES(score),
                        num_comments = VALUES(num_comments)
                ");

                $stmt->execute([
                    'reddit',
                    $result['id'],
                    $result['title'],
                    $result['content'],
                    $result['author'],
                    $result['subreddit'],
                    $result['url'],
                    $result['score'],
                    $result['num_comments'],
                    $result['created_at']
                ]);

                // Get the conversation ID
                $conversationId = $db->lastInsertId();
                if (!$conversationId) {
                    // Already exists, get the ID
                    $stmt = $db->prepare("SELECT id FROM conversations WHERE source_id = ?");
                    $stmt->execute([$result['id']]);
                    $conversationId = $stmt->fetchColumn();
                }

                // Link to search via junction table
                $stmt = $db->prepare("
                    INSERT IGNORE INTO search_results (search_id, conversation_id, relevance_score)
                    VALUES (?, ?, 1.00)
                ");
                $stmt->execute([$searchId, $conversationId]);

                $saved++;
            } catch (PDOException $e) {
                error_log('Error saving Reddit result: ' . $e->getMessage());
            }
        }

        // Update search results count
        $stmt = $db->prepare("UPDATE searches SET results_count = ? WHERE id = ?");
        $stmt->execute([$saved, $searchId]);

        return $saved;
    }
}
