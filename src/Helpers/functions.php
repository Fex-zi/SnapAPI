<?php
/**
 * Common Helper Functions
 */

/**
 * Get database connection
 */
function getDB() {
    return Database::getInstance()->getConnection();
}

/**
 * Sanitize input data
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current logged in user
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }

    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Generate URL with base path
 */
function url($path = '') {
    return BASE_PATH . '/' . ltrim($path, '/');
}

/**
 * Generate asset URL (for CSS, JS, images)
 */
function asset($path = '') {
    return BASE_PATH . '/public/' . ltrim($path, '/');
}

/**
 * Redirect to a page
 */
function redirect($path) {
    $url = (strpos($path, 'http') === 0) ? $path : url($path);
    header("Location: $url");
    exit;
}

/**
 * Check if user can perform more searches this month
 */
function canSearch($user) {
    $plan = PLANS[$user['plan']];

    // Unlimited searches
    if ($plan['searches_per_month'] === -1) {
        return true;
    }

    // Check if we need to reset monthly counter
    $today = date('Y-m-d');
    if ($user['last_search_reset'] !== $today) {
        // Reset counter at the start of each month
        $lastReset = new DateTime($user['last_search_reset']);
        $now = new DateTime($today);

        if ($lastReset->format('Y-m') !== $now->format('Y-m')) {
            resetSearchCounter($user['id']);
            return true;
        }
    }

    return $user['searches_used_this_month'] < $plan['searches_per_month'];
}

/**
 * Reset monthly search counter
 */
function resetSearchCounter($userId) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE users SET searches_used_this_month = 0, last_search_reset = CURDATE() WHERE id = ?");
    $stmt->execute([$userId]);
}

/**
 * Increment search counter
 */
function incrementSearchCounter($userId) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE users SET searches_used_this_month = searches_used_this_month + 1 WHERE id = ?");
    $stmt->execute([$userId]);
}

/**
 * Get remaining searches for user
 */
function getRemainingSearches($user) {
    $plan = PLANS[$user['plan']];

    if ($plan['searches_per_month'] === -1) {
        return 'Unlimited';
    }

    $remaining = $plan['searches_per_month'] - $user['searches_used_this_month'];
    return max(0, $remaining);
}

/**
 * Format number (e.g., 1234 -> 1.2K)
 */
function formatNumber($num) {
    if ($num >= 1000000) {
        return round($num / 1000000, 1) . 'M';
    }
    if ($num >= 1000) {
        return round($num / 1000, 1) . 'K';
    }
    return $num;
}

/**
 * Time ago function (e.g., "2 hours ago")
 */
function timeAgo($timestamp) {
    $time = is_numeric($timestamp) ? $timestamp : strtotime($timestamp);
    $diff = time() - $time;

    if ($diff < 60) {
        return 'Just now';
    }

    $intervals = [
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute'
    ];

    foreach ($intervals as $seconds => $label) {
        $count = floor($diff / $seconds);
        if ($count >= 1) {
            return $count . ' ' . $label . ($count > 1 ? 's' : '') . ' ago';
        }
    }

    return 'Just now';
}

/**
 * Log activity
 */
function logActivity($action, $details = null, $userId = null) {
    $db = getDB();
    $userId = $userId ?? ($_SESSION['user_id'] ?? null);
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

    $stmt = $db->prepare("INSERT INTO activity_log (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $action, $details, $ipAddress, $userAgent]);
}

/**
 * Send JSON response
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Display flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Rate limiting - prevent spam
 * Returns true if rate limit exceeded
 */
function isRateLimited($action, $maxAttempts = 5, $timeWindow = 300) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = "rate_limit_{$action}_{$ip}";

    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [
            'attempts' => 1,
            'first_attempt' => time()
        ];
        return false;
    }

    $data = $_SESSION[$key];
    $timeElapsed = time() - $data['first_attempt'];

    // Reset if time window passed
    if ($timeElapsed > $timeWindow) {
        $_SESSION[$key] = [
            'attempts' => 1,
            'first_attempt' => time()
        ];
        return false;
    }

    // Increment attempts
    $_SESSION[$key]['attempts']++;

    // Check if limit exceeded
    return $_SESSION[$key]['attempts'] > $maxAttempts;
}
