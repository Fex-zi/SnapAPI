<?php
/**
 * Main Router - Clean URLs
 * Examples: /dashboard, /search, /pricing
 */

// Load configuration
require_once __DIR__ . '/config/config.php';

// Autoloader for classes in /src
spl_autoload_register(function ($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = __DIR__ . '/src/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
});

// Load helper functions
require_once __DIR__ . '/src/Helpers/functions.php';

// Get the requested URL
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Define routes
$route = $url[0] ?: 'home';

// Public routes (no login required)
$publicRoutes = ['home', 'pricing', 'login', 'register', 'about', 'api'];

// Check if user needs to be logged in
if (!in_array($route, $publicRoutes) && !isLoggedIn()) {
    redirect('/login');
}

// Route to appropriate page
switch ($route) {
    case 'home':
    case '':
        require __DIR__ . '/views/home.php';
        break;

    case 'login':
        require __DIR__ . '/views/auth/login.php';
        break;

    case 'register':
        require __DIR__ . '/views/auth/register.php';
        break;

    case 'logout':
        session_destroy();
        redirect('/login');
        break;

    case 'dashboard':
        require __DIR__ . '/views/dashboard.php';
        break;

    case 'search':
        // Handle /search or /search/{query}
        $_GET['query'] = $url[1] ?? $_GET['query'] ?? '';
        $_GET['source'] = $_GET['source'] ?? 'reddit';
        require __DIR__ . '/views/search.php';
        break;

    case 'results':
        // Handle /results/{search_id}
        $_GET['search_id'] = $url[1] ?? $_GET['search_id'] ?? 0;
        require __DIR__ . '/views/results.php';
        break;

    case 'saved-searches':
        require __DIR__ . '/views/saved-searches.php';
        break;

    case 'account':
        require __DIR__ . '/views/account.php';
        break;

    case 'pricing':
        require __DIR__ . '/views/pricing.php';
        break;

    case 'upgrade':
        // Handle /upgrade/{plan}
        $_GET['plan'] = $url[1] ?? $_GET['plan'] ?? '';
        require __DIR__ . '/views/upgrade.php';
        break;

    case 'about':
        require __DIR__ . '/views/about.php';
        break;

    // API endpoints
    case 'api':
        $endpoint = $url[1] ?? '';
        $apiFile = __DIR__ . "/api/{$endpoint}.php";
        if (file_exists($apiFile)) {
            require $apiFile;
        } else {
            jsonResponse(['error' => 'API endpoint not found'], 404);
        }
        break;

    // 404
    default:
        http_response_code(404);
        require __DIR__ . '/views/404.php';
        break;
}
