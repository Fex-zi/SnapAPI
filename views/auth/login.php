<?php
$pageTitle = 'Login';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard');
}

require_once __DIR__ . '/../layouts/header.php';

// Handle login form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check rate limiting (5 attempts per 5 minutes)
    if (isRateLimited('login', 5, 300)) {
        $error = 'Too many login attempts. Please try again in 5 minutes.';
    } else {
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';

        // Validate CSRF token
        if (!verifyCSRFToken($csrfToken)) {
            $error = 'Invalid request. Please try again.';
        }
        // Validate inputs
        elseif (empty($email) || empty($password)) {
            $error = 'Please fill in all fields.';
        }
        elseif (!isValidEmail($email)) {
            $error = 'Please enter a valid email address.';
        }
        // Attempt login
        else {
            $user = new User();
            $result = $user->login($email, $password);

            if ($result['success']) {
                setFlash('success', 'Welcome back! You have been logged in successfully.');
                redirect('dashboard');
            } else {
                $error = $result['message'];
            }
        }
    }
}

$csrfToken = generateCSRFToken();
?>

<div class="container auth-container">
    <div class="card">
        <h1 class="card-header text-center">Login to <?= SITE_NAME ?></h1>

        <?php if ($error): ?>
            <div class="flash-message flash-error">
                <p><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('login') ?>">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    placeholder="you@example.com"
                    value="<?= htmlspecialchars($email ?? '') ?>"
                    required
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    placeholder="Enter your password"
                    required
                >
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg">Login</button>
        </form>

        <div class="auth-divider">
            <p class="text-secondary">
                Don't have an account?
                <a href="<?= url('register') ?>" class="font-semibold">Sign up for free</a>
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
