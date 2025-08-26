<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/flash.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

logout_user();

flash('success', 'You have been successfully logged out.');

header('Location: ' . BASE_URL . '/index.php');
exit;
?>
