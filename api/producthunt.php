<?php
/**
 * Product Hunt API Integration
 * Uses Product Hunt GraphQL API - requires free API token
 * Get token: https://www.producthunt.com/v2/oauth/applications
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Helpers/functions.php';

class ProductHuntAPI {
    private $apiUrl = 'https://api.producthunt.com/v2/api/graphql';
    private $token;

    public function __construct() {
        $this->token = PRODUCTHUNT_API_TOKEN;
    }

    /**
     * Search Product Hunt posts
     */
    public function search($query, $options = []) {
        if (empty($this->token)) {
            throw new Exception('Product Hunt API token not configured');
        }

        $limit = $options['limit'] ?? 50;

        // GraphQL query to get recent popular posts
        // Note: Product Hunt API doesn't have text search, so we fetch popular posts and filter
        $graphqlQuery = [
            'query' => '
                {
                    posts(order: VOTES, first: 30) {
                        edges {
                            node {
                                id
                                name
                                tagline
                                description
                                votesCount
                                commentsCount
                                createdAt
                                url
                            }
                        }
                    }
                }
            '
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($graphqlQuery));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new Exception('Product Hunt API cURL Error: ' . $curlError);
        }

        if ($httpCode !== 200) {
            throw new Exception('Product Hunt API request failed. HTTP Code: ' . $httpCode . '. Response: ' . substr($response, 0, 200));
        }

        $data = json_decode($response, true);

        if (!$data) {
            throw new Exception('Product Hunt API returned invalid JSON: ' . substr($response, 0, 200));
        }

        if (isset($data['errors'])) {
            throw new Exception('Product Hunt API Error: ' . json_encode($data['errors']));
        }

        $results = $this->parseSearchResults($data, $query);

        // Filter by relevance
        return $this->filterByRelevance($results, $query);
    }

    /**
     * Parse search results
     */
    private function parseSearchResults($data, $query) {
        if (!isset($data['data']['posts']['edges'])) {
            return [];
        }

        $results = [];

        foreach ($data['data']['posts']['edges'] as $edge) {
            $post = $edge['node'];

            $results[] = [
                'id' => $post['id'],
                'title' => $post['name'],
                'author' => 'Product Hunt',
                'content' => $post['tagline'] . "\n\n" . ($post['description'] ?? ''),
                'url' => $post['url'],
                'score' => $post['votesCount'],
                'num_comments' => $post['commentsCount'],
                'created_at' => date('Y-m-d H:i:s', strtotime($post['createdAt'])),
                'created_utc' => strtotime($post['createdAt']),
                'topics' => [],
            ];
        }

        return $results;
    }

    /**
     * Filter results by relevance to search query
     */
    private function filterByRelevance($results, $query) {
        $queryLower = strtolower($query);
        $queryWords = preg_split('/\s+/', $queryLower);

        $scored = [];
        foreach ($results as $result) {
            $score = 0;
            $titleLower = strtolower($result['title']);
            $contentLower = strtolower($result['content']);

            // Exact match in title = very relevant
            if (stripos($titleLower, $queryLower) !== false) {
                $score += 100;
            }

            // Exact match in content
            if (stripos($contentLower, $queryLower) !== false) {
                $score += 50;
            }

            // Word matches
            foreach ($queryWords as $word) {
                if (strlen($word) > 2) {
                    if (stripos($titleLower, $word) !== false) {
                        $score += 10;
                    }
                    if (stripos($contentLower, $word) !== false) {
                        $score += 5;
                    }
                }
            }

            // Boost by engagement
            $score += min($result['score'] / 10, 20);
            $score += min($result['num_comments'] / 2, 10);

            $result['relevance_score'] = $score;
            $scored[] = $result;
        }

        // Sort by relevance
        usort($scored, function($a, $b) {
            return $b['relevance_score'] - $a['relevance_score'];
        });

        return $scored;
    }

    /**
     * Save results to database
     */
    public function saveResults($searchId, $results) {
        $db = getDB();

        $saved = 0;
        foreach ($results as $result) {
            try {
                // Insert or update conversation
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

                // No category for simplified query
                $category = null;

                $stmt->execute([
                    'producthunt',
                    $result['id'],
                    $result['title'],
                    $result['content'],
                    $result['author'],
                    $category,
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
                error_log('Error saving Product Hunt result: ' . $e->getMessage());
            }
        }

        // Update search results count
        $stmt = $db->prepare("UPDATE searches SET results_count = ? WHERE id = ?");
        $stmt->execute([$saved, $searchId]);

        return $saved;
    }
}
