<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';

// Only allow POST requests for logout (security best practice)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Logout user
logout_user();

// Set flash message
flash('success', 'You have been successfully logged out.');

// Redirect to home page
header('Location: ' . BASE_URL . '/index.php');
exit;
?>
