<?php
define('ADMIN_PAGE', true);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/flash.php';

// Check if user is admin
if (! is_logged_in() || ! has_role('admin')) {
    header('Location: ../auth/login.php');
    exit;
}

// Only POST requests allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: user-list.php');
    exit;
}

$user_id         = intval($_POST['user_id'] ?? 0);
$current_user_id = current_user()['id'];

if (! $user_id) {
    flash('error', 'Invalid user ID.');
    header('Location: user-list.php');
    exit;
}

// Prevent self-deletion
if ($user_id === $current_user_id) {
    flash('error', 'You cannot delete your own account.');
    header('Location: user-list.php');
    exit;
}

try {
    // Check if user exists
    $user = db_fetch_one("SELECT name FROM users WHERE id = ?", [$user_id]);
    if (! $user) {
        flash('error', 'User not found.');
        header('Location: user-list.php');
        exit;
    }

    // Delete user
    db_execute("DELETE FROM users WHERE id = ?", [$user_id]);

    flash('success', 'User "' . htmlspecialchars($user['name']) . '" has been deleted successfully.');
} catch (Exception $e) {
    flash('error', 'Error deleting user. Please try again.');
}

header('Location: user-list.php');
exit;
