<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/flash.php';

// Require admin role
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: reservation-list.php');
    exit;
}

verify_csrf();

$reservation_id = (int)($_POST['id'] ?? 0);

if (!$reservation_id) {
    flash('error', 'Invalid reservation ID.');
    header('Location: reservation-list.php');
    exit;
}

// Get reservation to verify it exists
$reservation = db_fetch_one("SELECT name FROM reservations WHERE id = ?", [$reservation_id], 'i');

if (!$reservation) {
    flash('error', 'Reservation not found.');
    header('Location: reservation-list.php');
    exit;
}

try {
    // Delete the reservation
    $affected_rows = db_execute("DELETE FROM reservations WHERE id = ?", [$reservation_id], 'i');

    if ($affected_rows > 0) {
        flash('success', 'Reservation for "' . $reservation['name'] . '" has been deleted successfully.');
    } else {
        flash('error', 'Failed to delete reservation.');
    }
} catch (Exception $e) {
    flash('error', 'An error occurred while deleting the reservation.');
}

header('Location: reservation-list.php');
exit;
?>
