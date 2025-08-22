<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/flash.php';
require_once __DIR__ . '/includes/db.php';

// Require user to be logged in
require_login();

function wants_json(): bool {
    $xr = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    $acc = isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
    return $xr || $acc || (isset($_POST['ajax']) && $_POST['ajax'] == '1');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (wants_json()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }
    header('Location: reservations.php');
    exit;
}

try {
    verify_csrf();
} catch (Exception $e) {
    if (wants_json()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Security validation failed.']);
        exit;
    }
    flash('error', 'Security validation failed. Please try again.');
    header('Location: reservations.php');
    exit;
}

$reservation_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$user = current_user();

if (!$reservation_id) {
    if (wants_json()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid reservation ID.']);
        exit;
    }
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

if (!$reservation) {
    if (wants_json()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Reservation not found or not permitted.']);
        exit;
    }
    flash('error', 'Reservation not found or you do not have permission to cancel it.');
    header('Location: reservations.php');
    exit;
}

// Check if reservation can be cancelled (must be pending and in the future)
if ($reservation['status'] !== 'pending') {
    if (wants_json()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Only pending reservations can be cancelled.']);
        exit;
    }
    flash('error', 'Only pending reservations can be cancelled.');
    header('Location: reservations.php');
    exit;
}

// Use combined date+time for a correct "future" check
$when = strtotime($reservation['reservation_date'].' '.$reservation['reservation_time']);
if ($when !== false && $when <= time()) {
    if (wants_json()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Cannot cancel past reservations.']);
        exit;
    }
    flash('error', 'Cannot cancel past reservations.');
    header('Location: reservations.php');
    exit;
}

try {
    $affected_rows = db_execute(
        "UPDATE reservations SET status = 'cancelled', updated_at = NOW() WHERE id = ?",
        [$reservation_id],
        'i'
    );

    if ($affected_rows > 0) {
        if (wants_json()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Reservation cancelled successfully.']);
            exit;
        }
        flash('success', 'Your reservation has been cancelled successfully.');
    } else {
        if (wants_json()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to cancel reservation.']);
            exit;
        }
        flash('error', 'Failed to cancel reservation.');
    }
} catch (Exception $e) {
    if (wants_json()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'An error occurred while cancelling the reservation.']);
        exit;
    }
    flash('error', 'An error occurred while cancelling the reservation.');
}

header('Location: reservations.php');
exit;
