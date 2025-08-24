<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/flash.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: user-list.php');
    exit;
}

// Check if user is admin
if (! is_logged_in() || ! has_role('admin')) {
    flash('error', 'You do not have permission to perform this action.');
    header('Location: user-list.php');
    exit;
}

// Collect and sanitize input
$name      = trim($_POST['name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$password  = $_POST['password'] ?? '';
$role      = $_POST['role'] ?? '';
$is_active = isset($_POST['is_active']) ? 1 : 0;

// Validate inputs
if ($name === '' || $email === '' || $password === '' || $role === '') {
    flash('error', 'All fields are required.');
    header('Location: user-list.php');
    exit;
}

if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'Invalid email address.');
    header('Location: user-list.php');
    exit;
}

// Hash password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $sql = "INSERT INTO users (name, email, password_hash, role, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())";

    // Explicitly define parameter types: s = string, i = integer
    db_insert($sql, [$name, $email, $password_hash, $role, $is_active], 'ssssi');

    flash('success', "User '{$name}' has been created successfully.");
} catch (Exception $e) {
    flash('error', 'Error creating user: ' . $e->getMessage());
}

// Redirect back to user list
header('Location: user-list.php');
exit;
