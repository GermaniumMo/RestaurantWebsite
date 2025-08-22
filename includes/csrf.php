<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Generate CSRF hidden field
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

// Verify CSRF token
function verify_csrf() {
    $token = $_POST['csrf_token'] ?? '';
    $session_token = $_SESSION['csrf_token'] ?? '';
    
    if (empty($token) || empty($session_token) || !hash_equals($session_token, $token)) {
        http_response_code(419);
        die('CSRF token validation failed. Please try again.');
    }
    
    return true;
}

// Generate new CSRF token (for forms that need fresh tokens)
function regenerate_csrf_token() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

// Get CSRF token via AJAX
if (isset($_GET['action']) && $_GET['action'] === 'get_token') {
    header('Content-Type: application/json');
    echo json_encode(['token' => csrf_token()]);
    exit;
}
?>
