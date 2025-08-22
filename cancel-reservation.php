<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/flash.php';

// Require user to be logged in
require_login();

error_log("[v0] Cancel reservation: Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("[v0] Cancel reservation: POST data: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("[v0] Cancel reservation: Not a POST request");
    header('Location: reservations.php');
    exit;
}

try {
    verify_csrf();
    error_log("[v0] Cancel reservation: CSRF verification passed");
} catch (Exception $e) {
    error_log("[v0] Cancel reservation: CSRF verification failed - " . $e->getMessage());
    flash('error', 'Security validation failed. Please try again.');
    header('Location: reservations.php');
    exit;
}

$reservation_id = (int)($_POST['id'] ?? 0);
$user = current_user();

error_log("[v0] Cancel reservation: Processing reservation ID: " . $reservation_id . " for user: " . $user['id']);

if (!$reservation_id) {
    error_log("[v0] Cancel reservation: Invalid reservation ID");
    flash('error', 'Invalid reservation ID.');
    header('Location: reservations.php');
    exit;
}

// Get reservation and verify ownership
$reservation = db_fetch_one(
    "SELECT * FROM reservations WHERE id = ? AND (user_id = ? OR email = ?)", 
    [$reservation_id, $user['id'], $user['email']], 
    'iis'
);

error_log("[v0] Cancel reservation: Found reservation: " . ($reservation ? 'Yes' : 'No'));
if ($reservation) {
    error_log("[v0] Cancel reservation: Reservation status: " . $reservation['status']);
    error_log("[v0] Cancel reservation: Reservation date: " . $reservation['reservation_date']);
}

if (!$reservation) {
    error_log("[v0] Cancel reservation: Reservation not found or no permission");
    flash('error', 'Reservation not found or you do not have permission to cancel it.');
    header('Location: reservations.php');
    exit;
}

// Check if reservation can be cancelled (must be pending and in the future)
if ($reservation['status'] !== 'pending') {
    error_log("[v0] Cancel reservation: Status not pending: " . $reservation['status']);
    flash('error', 'Only pending reservations can be cancelled.');
    header('Location: reservations.php');
    exit;
}

if (strtotime($reservation['reservation_date']) <= time()) {
    error_log("[v0] Cancel reservation: Reservation is in the past");
    flash('error', 'Cannot cancel past reservations.');
    header('Location: reservations.php');
    exit;
}

try {
    // Update reservation status to cancelled
    $affected_rows = db_execute(
        "UPDATE reservations SET status = 'cancelled', updated_at = NOW() WHERE id = ?", 
        [$reservation_id], 
        'i'
    );
    
    error_log("[v0] Cancel reservation: Database update affected rows: " . $affected_rows);
    
    if ($affected_rows > 0) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => true, 'message' => 'Reservation cancelled successfully']);
            exit;
        }
        flash('success', 'Your reservation has been cancelled successfully.');
        error_log("[v0] Cancel reservation: Successfully cancelled reservation ID: " . $reservation_id);
    } else {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => false, 'message' => 'Failed to cancel reservation']);
            exit;
        }
        flash('error', 'Failed to cancel reservation.');
        error_log("[v0] Cancel reservation: No rows affected for reservation ID: " . $reservation_id);
    }
} catch (Exception $e) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode(['success' => false, 'message' => 'An error occurred while cancelling the reservation']);
        exit;
    }
    flash('error', 'An error occurred while cancelling the reservation.');
    error_log("[v0] Cancel reservation: Database error - " . $e->getMessage());
}

header('Location: reservations.php');
exit;
?>
