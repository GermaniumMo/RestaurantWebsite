<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/flash.php';

// Check if user is admin
if (!is_logged_in() || !has_role('admin')) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: user-list.php');
    exit;
}

$user_id = intval($_POST['user_id'] ?? 0);
$current_user_id = current_user()['id'];

if (!$user_id) {
    flash_set('error', 'Invalid user ID.');
    header('Location: user-list.php');
    exit;
}

// Prevent self-deletion
if ($user_id === $current_user_id) {
    flash_set('error', 'You cannot delete your own account.');
    header('Location: user-list.php');
    exit;
}

$db = get_db_connection();

try {
    // Check if user exists
    $stmt = $db->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        flash_set('error', 'User not found.');
        header('Location: user-list.php');
        exit;
    }

    // Delete user (this will cascade delete related reservations due to foreign key)
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);

    flash_set('success', 'User "' . htmlspecialchars($user['name']) . '" has been deleted successfully.');
} catch (Exception $e) {
    flash_set('error', 'Error deleting user. Please try again.');
}

header('Location: user-list.php');
exit;
?>
