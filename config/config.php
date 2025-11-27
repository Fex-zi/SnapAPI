<?php
/**
 * Global Configuration File
 * Change these values to customize your application
 */

// Site Configuration (CHANGE THESE FOR YOUR BRAND)
define('SITE_NAME', 'ResearchFlow'); // Your brand name
define('SITE_TAGLINE', 'Discover Customer Insights from Social Conversations');
define('SITE_EMAIL', 'hello@researchflow.com');

// Auto-detect base path (works locally and in production)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$script = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
define('BASE_URL', $protocol . $host . $script);
define('BASE_PATH', $script);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', '');
define('DB_USER', 'root');
define('DB_PASS', '');

// API Keys (Add your keys here)
define('REDDIT_CLIENT_ID', ''); // Get from: https://www.reddit.com/prefs/apps
define('REDDIT_CLIENT_SECRET', '');
define('REDDIT_USER_AGENT', SITE_NAME . '/1.0');

define('PRODUCTHUNT_API_TOKEN', ''); // Get from: https://www.producthunt.com/v2/oauth/applications

define('OPENAI_API_KEY', ''); // Get from: https://platform.openai.com/api-keys

define('STRIPE_SECRET_KEY', ''); // Get from: https://dashboard.stripe.com/apikeys
define('STRIPE_PUBLISHABLE_KEY', '');
define('STRIPE_WEBHOOK_SECRET', '');

// Email Configuration (for alerts)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');

// Subscription Plans
define('PLANS', [
    'free' => [
        'name' => 'Free',
        'price' => 0,
        'searches_per_month' => 5,
        'features' => ['5 searches per month', 'Basic results', 'No AI analysis']
    ],
    'starter' => [
        'name' => 'Starter',
        'price' => 29,
        'stripe_price_id' => 'price_xxx', // Add Stripe price ID later
        'searches_per_month' => 50,
        'features' => ['50 searches per month', 'Save searches', 'Email alerts', 'AI pain point analysis']
    ],
    'pro' => [
        'name' => 'Pro',
        'price' => 79,
        'stripe_price_id' => 'price_xxx', // Add Stripe price ID later
        'searches_per_month' => -1, // unlimited
        'features' => ['Unlimited searches', 'Save searches', 'Email alerts', 'AI analysis', 'Export to CSV', 'API access']
    ]
]);

// App Configuration
define('RESULTS_PER_PAGE', 20);
define('REDDIT_FETCH_LIMIT', 100);
define('SESSION_LIFETIME', 86400); // 24 hours

// Timezone
date_default_timezone_set('UTC');

// Error Reporting (Set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
