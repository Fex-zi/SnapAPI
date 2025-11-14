<?php
/**
 * Hacker News API Integration
 * Uses Algolia's HN Search API - completely free, no authentication needed
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Helpers/functions.php';

class HackerNewsAPI {
    private $baseUrl = 'https://hn.algolia.com/api/v1';

    /**
     * Search Hacker News stories and comments
     */
    public function search($query, $options = []) {
        $params = [
            'query' => $query,
            'tags' => 'story', // Search stories only (can also be 'comment', 'poll', etc.)
            'hitsPerPage' => $options['limit'] ?? 100,
        ];

        // Add time filter
        if (isset($options['time'])) {
            $params['numericFilters'] = $this->getTimeFilter($options['time']);
        }

        // Add sort option
        $endpoint = '/search';
        if (isset($options['sort']) && $options['sort'] === 'date') {
            $endpoint = '/search_by_date';
        }

        $url = $this->baseUrl . $endpoint . '?' . http_build_query($params);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: ' . SITE_NAME . '/1.0'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception('Hacker News API request failed. HTTP Code: ' . $httpCode);
        }

        $data = json_decode($response, true);

        return $this->parseSearchResults($data);
    }

    /**
     * Get time filter for Algolia API
     */
    private function getTimeFilter($time) {
        $now = time();
        $filters = [
            'day' => $now - 86400,
            'week' => $now - 604800,
            'month' => $now - 2592000,
            'year' => $now - 31536000,
        ];

        if (isset($filters[$time])) {
            return 'created_at_i>' . $filters[$time];
        }

        return null;
    }

    /**
     * Parse search results into clean format
     */
    private function parseSearchResults($data) {
        if (!isset($data['hits'])) {
            return [];
        }

        $results = [];

        foreach ($data['hits'] as $hit) {
            // Skip if no title
            if (empty($hit['title'])) {
                continue;
            }

            $results[] = [
                'id' => $hit['objectID'],
                'title' => $hit['title'],
                'author' => $hit['author'] ?? 'unknown',
                'content' => $this->getStoryText($hit),
                'url' => $this->getStoryUrl($hit),
                'score' => $hit['points'] ?? 0,
                'num_comments' => $hit['num_comments'] ?? 0,
                'created_at' => date('Y-m-d H:i:s', $hit['created_at_i']),
                'created_utc' => $hit['created_at_i'],
            ];
        }

        return $results;
    }

    /**
     * Get story text/content
     */
    private function getStoryText($hit) {
        // Try story_text first (for Ask HN, Show HN posts)
        if (!empty($hit['story_text'])) {
            return strip_tags($hit['story_text']);
        }

        // Otherwise use the URL as content
        if (!empty($hit['url'])) {
            return 'External link: ' . $hit['url'];
        }

        return '';
    }

    /**
     * Get story URL
     */
    private function getStoryUrl($hit) {
        $hnUrl = 'https://news.ycombinator.com/item?id=' . $hit['objectID'];

        // If it's a link post, return the HN discussion page
        // Users can click through to see the discussion
        return $hnUrl;
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
                    'hackernews',
                    $result['id'],
                    $result['title'],
                    $result['content'],
                    $result['author'],
                    null, // HN doesn't have categories
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
                error_log('Error saving HN result: ' . $e->getMessage());
            }
        }

        // Update search results count
        $stmt = $db->prepare("UPDATE searches SET results_count = ? WHERE id = ?");
        $stmt->execute([$saved, $searchId]);

        return $saved;
    }
}
