<?php
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../classes/User.php';

if (is_user_logged_in()) {
    redirect('../index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        $user = new User($db);
        
        if ($user->login($email, $password)) {
            login_user($user);
            
            // Redirect to intended page or dashboard
            $redirect_url = isset($_GET['redirect']) ? $_GET['redirect'] : '../profile/dashboard.php';
            redirect($redirect_url);
        } else {
            $error = 'Invalid email or password.';
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css">
    <style>
        .auth-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #111827 0%, #1f2937 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-card {
            background: white;
            border-radius: 1rem;
            padding: 3rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .auth-title {
            font-family: "Cormorant Garamond", serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: #ea580c;
            text-align: center;
            margin-bottom: 2rem;
        }
        .btn-primary {
            background-color: #ea580c;
            border-color: #ea580c;
        }
        .btn-primary:hover {
            background-color: #dc2626;
            border-color: #dc2626;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1 class="auth-title">Welcome Back</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                    <label class="form-check-label" for="remember_me">Remember me</label>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 p-3 mb-3">Login</button>
                
                <div class="text-center">
                    <p><a href="forgot-password.php" class="text-decoration-none" style="color: #ea580c;">Forgot your password?</a></p>
                    <p>Don't have an account? <a href="register.php" class="text-decoration-none" style="color: #ea580c;">Register here</a></p>
                    <p><a href="../index.php" class="text-decoration-none text-muted">← Back to Home</a></p>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
