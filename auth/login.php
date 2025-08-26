<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/db.php';

set_security_headers();

if (is_logged_in()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        flash('error', 'Invalid security token.');
        header('Location: login.php');
        exit;
    }
    
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $db = db();
    $rate_limiter = new RateLimiter($db);
    $client_ip = get_client_ip();

    if ($rate_limiter->isLimited($client_ip, 'login', 5, 15)) {
        $remaining_attempts = $rate_limiter->getRemainingAttempts($client_ip, 'login', 5, 15);
        log_security_event('login_rate_limited', ['ip' => $client_ip, 'email' => $email]);
        $errors['general'] = 'Too many failed login attempts. Please try again in 15 minutes.';
    }

    if (empty($email)) {
        $errors['email'] = 'Email is required.';
    } elseif (!is_valid_email($email)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    }
    
    if (empty($errors)) {
        $user = get_user_by_email($email);
        
        if ($user && $user['is_active'] && verify_user_password($user, $password)) {
            log_security_event('login_success', ['user_id' => $user['id'], 'email' => $email], $user['id']);
            login_user($user['id']);
            $redirect = $_SESSION['redirect_after_login'] ?? (has_role('admin') ? '/admin/index.php' : '/index.php');
            unset($_SESSION['redirect_after_login']);
            
            header('Location: ' . BASE_URL . $redirect);
            exit;
        } else {
            $rate_limiter->recordAttempt($client_ip, 'login');
            log_security_event('login_failed', ['ip' => $client_ip, 'email' => $email]);
            $errors['general'] = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Savoria</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/main.css">
</head>
<body>
    <header class="d-flex top-header w-100 position-absolute left-0">
        <div class="d-flex justify-content-between py-3 w-100 header-container">
            <h1><a href="../index.php" style="color: white; text-decoration: none;">Savoria</a></h1>
            <ul class="d-flex list-unstyled gap-4 m-0 justify-content-center align-items-center navbar">
                <li><a href="../index.php" style="color: white; text-decoration: none">Home</a></li>
                <li><a href="../menu.php" style="color: white; text-decoration: none">Menu</a></li>
                <li><a href="../about.php" style="color: white; text-decoration: none">About</a></li>
                <li><a href="../contact.php" style="color: white; text-decoration: none">Contact</a></li>
            </ul>
            <div class="d-flex gap-3">
                <a class="btn btn-Reserve" href="register.php">Sign Up</a>
                <a class="btn btn-order" href="login.php">Login</a>
            </div>
        </div>
    </header>

    <section class="d-flex w-100 contactFooter-container justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="w-100 d-flex flex-column contactFooter-innContainer justify-content-center align-items-center">
            <div class="d-flex flex-column w-75" style="max-width: 500px;">
                <div class="d-flex flex-column justify-content-center align-items-center mb-4">
                    <h1 class="reservation-title text-center">Welcome Back</h1>
                    <p class="reservation-description text-center">Sign in to your account</p>
                </div>

                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= htmlspecialchars($errors['general']) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="d-flex flex-column gap-4">
                    <?= csrf_field() ?>
                    
                    <div class="d-flex flex-column">
                        <input 
                            type="email" 
                            name="email" 
                            placeholder="Email Address"
                            value="<?= htmlspecialchars($email) ?>"
                            class="w-100 bg-transparent p-3 border border-1 rounded-3 input-form <?= !empty($errors['email']) ? 'is-invalid' : '' ?>"
                            required
                        >
                        <?php if (!empty($errors['email'])): ?>
                            <div class="invalid-feedback d-block">
                                <?= htmlspecialchars($errors['email']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex flex-column">
                        <input 
                            type="password" 
                            name="password" 
                            placeholder="Password"
                            class="w-100 bg-transparent p-3 border border-1 rounded-3 input-form <?= !empty($errors['password']) ? 'is-invalid' : '' ?>"
                            required
                        >
                        <?php if (!empty($errors['password'])): ?>
                            <div class="invalid-feedback d-block">
                                <?= htmlspecialchars($errors['password']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn w-100 p-3 reservation-btn">
                        Sign In
                    </button>

                    <div class="text-center">
                        <p class="reservation-description">
                            Don't have an account? 
                            <a href="register.php" style="color: #ea580c; text-decoration: none;">Sign up here</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
