<?php
$pageTitle = 'Sign Up';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard');
}

require_once __DIR__ . '/../layouts/header.php';

// Handle registration form submission
$error = '';
$formData = [
    'name' => '',
    'email' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check rate limiting (3 registrations per 10 minutes)
    if (isRateLimited('register', 3, 600)) {
        $error = 'Too many registration attempts. Please try again in 10 minutes.';
    } else {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';

        // Store form data for repopulation
        $formData['name'] = $name;
        $formData['email'] = $email;

        // Validate CSRF token
        if (!verifyCSRFToken($csrfToken)) {
            $error = 'Invalid request. Please try again.';
        }
        // Validate inputs
        elseif (empty($name) || empty($email) || empty($password) || empty($passwordConfirm)) {
            $error = 'Please fill in all fields.';
        }
        elseif (!isValidEmail($email)) {
            $error = 'Please enter a valid email address.';
        }
        elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long.';
        }
        elseif ($password !== $passwordConfirm) {
            $error = 'Passwords do not match.';
        }
        // Attempt registration
        else {
            $user = new User();
            $result = $user->register($email, $password, $name);

            if ($result['success']) {
                // Auto-login after registration
                $_SESSION['user_id'] = $result['user_id'];
                setFlash('success', 'Welcome to ' . SITE_NAME . '! Your account has been created successfully.');
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
        <h1 class="card-header text-center">Create Your Account</h1>

        <?php if ($error): ?>
            <div class="flash-message flash-error">
                <p><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('register') ?>">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <div class="form-group">
                <label for="name" class="form-label">Full Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    placeholder="John Doe"
                    value="<?= htmlspecialchars($formData['name']) ?>"
                    required
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    placeholder="you@example.com"
                    value="<?= htmlspecialchars($formData['email']) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    placeholder="At least 8 characters"
                    required
                >
                <small class="form-text">Must be at least 8 characters long</small>
            </div>

            <div class="form-group">
                <label for="password_confirm" class="form-label">Confirm Password</label>
                <input
                    type="password"
                    id="password_confirm"
                    name="password_confirm"
                    class="form-control"
                    placeholder="Re-enter your password"
                    required
                >
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg">Create Account</button>
        </form>

        <div class="auth-divider">
            <p class="text-secondary">
                Already have an account?
                <a href="<?= url('login') ?>" class="font-semibold">Login here</a>
            </p>
        </div>
    </div>

    <!-- Features -->
    <div class="auth-features">
        <p>What you get with a free account:</p>
        <ul>
            <li>✓ 5 searches per month</li>
            <li>✓ Access to Reddit data</li>
            <li>✓ Basic search results</li>
            <li>✓ No credit card required</li>
        </ul>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
