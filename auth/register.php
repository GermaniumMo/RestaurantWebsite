<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/flash.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/security.php';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation rules
    $validation_rules = [
        'name' => [
            ['type' => 'required', 'field_name' => 'Name'],
            ['type' => 'min_length', 'length' => 2, 'field_name' => 'Name'],
            ['type' => 'max_length', 'length' => 100, 'field_name' => 'Name']
        ],
        'email' => [
            ['type' => 'required', 'field_name' => 'Email'],
            ['type' => 'email'],
            ['type' => 'unique_email']
        ]
    ];
    
    $errors = validate_fields($_POST, $validation_rules);
    
    // Password validation
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters long.';
    }
    
    if (empty($confirm_password)) {
        $errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }
    
    if (empty($errors)) {
        try {
            $user_id = create_user($name, $email, $password);
            
            if ($user_id) {
                login_user($user_id);
                flash('success', 'Account created successfully! Welcome to Savoria.');
                header('Location: ' . BASE_URL . '/index.php');
                exit;
            } else {
                $errors['general'] = 'Failed to create account. Please try again.';
            }
        } catch (Exception $e) {
            $errors['general'] = 'An error occurred. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Savoria</title>
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
                    <h1 class="reservation-title text-center">Join Savoria</h1>
                    <p class="reservation-description text-center">Create your account for exclusive dining experiences</p>
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
                            type="text" 
                            name="name" 
                            placeholder="Full Name"
                            value="<?= htmlspecialchars($name) ?>"
                            class="w-100 bg-transparent p-3 border border-1 rounded-3 input-form <?= !empty($errors['name']) ? 'is-invalid' : '' ?>"
                            required
                        >
                        <?php if (!empty($errors['name'])): ?>
                            <div class="invalid-feedback d-block">
                                <?= htmlspecialchars($errors['name']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

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
                            placeholder="Password (min. 6 characters)"
                            class="w-100 bg-transparent p-3 border border-1 rounded-3 input-form <?= !empty($errors['password']) ? 'is-invalid' : '' ?>"
                            required
                        >
                        <?php if (!empty($errors['password'])): ?>
                            <div class="invalid-feedback d-block">
                                <?= htmlspecialchars($errors['password']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex flex-column">
                        <input 
                            type="password" 
                            name="confirm_password" 
                            placeholder="Confirm Password"
                            class="w-100 bg-transparent p-3 border border-1 rounded-3 input-form <?= !empty($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                            required
                        >
                        <?php if (!empty($errors['confirm_password'])): ?>
                            <div class="invalid-feedback d-block">
                                <?= htmlspecialchars($errors['confirm_password']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn w-100 p-3 reservation-btn">
                        Create Account
                    </button>

                    <div class="text-center">
                        <p class="reservation-description">
                            Already have an account? 
                            <a href="login.php" style="color: #ea580c; text-decoration: none;">Sign in here</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
