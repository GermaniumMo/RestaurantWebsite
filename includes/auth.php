<?php
require_once __DIR__ . '/db.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Get current user data
function current_user() {
    if (!is_logged_in()) {
        return null;
    }
    
    static $user = null;
    if ($user === null) {
        $user = db_fetch_one(
            "SELECT id, name, email, role, created_at FROM users WHERE id = ?",
            [$_SESSION['user_id']],
            'i'
        );
    }
    
    return $user;
}

// Require user to be logged in
function require_login($redirect_to = '/auth/login.php') {
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . BASE_URL . $redirect_to);
        exit;
    }
}

// Require specific role
function require_role($required_role) {
    require_login();
    
    $user = current_user();
    if (!$user || $user['role'] !== $required_role) {
        http_response_code(403);
        die('Access denied. Insufficient permissions.');
    }
}

// Check if user has role
function has_role($role) {
    $user = current_user();
    return $user && $user['role'] === $role;
}

// Login user
function login_user($user_id, $remember = false) {
    $_SESSION['user_id'] = $user_id;
    $_SESSION['login_time'] = time();
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    if ($remember) {
        // Set remember me cookie (optional implementation)
        // This would require additional token-based system
    }
    
    // Update last login
    db_execute(
        "UPDATE users SET last_login = NOW() WHERE id = ?",
        [$user_id],
        'i'
    );
}

// Logout user
function logout_user() {
    // Clear all session variables
    $_SESSION = array();
    
    // Delete session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy session
    session_destroy();
}

// Check login attempts (basic rate limiting)
function check_login_attempts($email) {
    $attempts_key = 'login_attempts_' . md5($email);
    $lockout_key = 'login_lockout_' . md5($email);
    
    // Check if locked out
    if (isset($_SESSION[$lockout_key]) && $_SESSION[$lockout_key] > time()) {
        return false;
    }
    
    // Check attempts
    $attempts = $_SESSION[$attempts_key] ?? 0;
    return $attempts < MAX_LOGIN_ATTEMPTS;
}

// Record failed login attempt
function record_failed_login($email) {
    $attempts_key = 'login_attempts_' . md5($email);
    $lockout_key = 'login_lockout_' . md5($email);
    
    $_SESSION[$attempts_key] = ($_SESSION[$attempts_key] ?? 0) + 1;
    
    if ($_SESSION[$attempts_key] >= MAX_LOGIN_ATTEMPTS) {
        $_SESSION[$lockout_key] = time() + LOGIN_LOCKOUT_TIME;
    }
}

// Clear login attempts on successful login
function clear_login_attempts($email) {
    $attempts_key = 'login_attempts_' . md5($email);
    $lockout_key = 'login_lockout_' . md5($email);
    
    unset($_SESSION[$attempts_key]);
    unset($_SESSION[$lockout_key]);
}

// Get user by email
function get_user_by_email($email) {
    return db_fetch_one(
        "SELECT * FROM users WHERE email = ?",
        [$email],
        's'
    );
}

// Create new user
function create_user($name, $email, $password, $role = 'user') {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    return db_insert(
        "INSERT INTO users (name, email, password_hash, role, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())",
        [$name, $email, $password_hash, $role],
        'ssss'
    );
}

// Verify password
function verify_user_password($user, $password) {
    return password_verify($password, $user['password_hash']);
}
?>
