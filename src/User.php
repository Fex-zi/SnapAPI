<?php
/**
 * User Class - Handle authentication and user management
 */

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Register new user
     */
    public function register($email, $password, $name) {
        // Validate email
        if (!isValidEmail($email)) {
            return ['success' => false, 'message' => 'Invalid email address'];
        }

        // Check if email exists
        if ($this->emailExists($email)) {
            return ['success' => false, 'message' => 'Email already registered'];
        }

        // Hash password
        $hashedPassword = hashPassword($password);

        try {
            $stmt = $this->db->prepare("INSERT INTO users (email, password, name, plan, last_search_reset) VALUES (?, ?, ?, 'free', CURDATE())");
            $stmt->execute([$email, $hashedPassword, $name]);

            $userId = $this->db->lastInsertId();

            // Log activity
            logActivity('register', "User registered: {$email}", $userId);

            return ['success' => true, 'user_id' => $userId];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }

    /**
     * Login user
     */
    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && verifyPassword($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_plan'] = $user['plan'];

            // Log activity
            logActivity('login', "User logged in", $user['id']);

            return ['success' => true, 'user' => $user];
        }

        return ['success' => false, 'message' => 'Invalid email or password'];
    }

    /**
     * Check if email exists
     */
    public function emailExists($email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() !== false;
    }

    /**
     * Get user by ID
     */
    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Update user plan
     */
    public function updatePlan($userId, $plan, $stripeCustomerId = null) {
        $stmt = $this->db->prepare("UPDATE users SET plan = ?, stripe_customer_id = ? WHERE id = ?");
        return $stmt->execute([$plan, $stripeCustomerId, $userId]);
    }

    /**
     * Get user's search stats
     */
    public function getSearchStats($userId) {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) as total_searches,
                COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_searches,
                COUNT(CASE WHEN YEARWEEK(created_at) = YEARWEEK(CURDATE()) THEN 1 END) as week_searches
            FROM searches
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
}
