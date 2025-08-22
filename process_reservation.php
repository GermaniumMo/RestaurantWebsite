<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/flash.php';
require_once __DIR__ . '/includes/validation.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/enhanced_validation.php';
require_once __DIR__ . '/includes/db.php';

$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($is_ajax) {
    header('Content-Type: application/json');
}

if (!is_logged_in()) {
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'You must be logged in to make a reservation.']);
        exit;
    }
    flash('error', 'You must be logged in to make a reservation.');
    header('Location: auth/login.php');
    exit;
}

// Set security headers
set_security_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
        exit;
    }
    header('Location: index.php');
    exit;
}

if (!verify_csrf()) {
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
        exit;
    }
    flash('error', 'Invalid security token.');
    header('Location: index.php');
    exit;
}

$db = db();
$rate_limiter = new RateLimiter($db);
$client_ip = get_client_ip();

if ($rate_limiter->isLimited($client_ip, 'reservation', 3, 10)) {
    log_security_event('reservation_rate_limited', ['ip' => $client_ip]);
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'Too many reservation attempts. Please try again in 10 minutes.']);
        exit;
    }
    flash('error', 'Too many reservation attempts. Please try again in 10 minutes.');
    header('Location: index.php');
    exit;
}

$data = [
    'name' => $_POST['name'] ?? '',
    'email' => $_POST['email'] ?? '',
    'phone' => $_POST['phone'] ?? '',
    'guests' => $_POST['guests'] ?? '',
    'date' => $_POST['date'] ?? '',
    'time' => $_POST['time'] ?? '',
    'special_requests' => $_POST['special_requests'] ?? ''
];

$validator = validate_reservation_data($data);

if ($validator->fails()) {
    $rate_limiter->recordAttempt($client_ip, 'reservation');
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => $validator->getErrorsAsString()]);
        exit;
    }
    flash('error', $validator->getErrorsAsString());
    header('Location: index.php');
    exit;
}

// Sanitize data
$name = sanitize_input($data['name']);
$email = sanitize_input($data['email']);
$phone = sanitize_input($data['phone']);
$guests = (int)$data['guests'];
$date = sanitize_input($data['date']);
$time = sanitize_input($data['time']);
$special_requests = sanitize_input($data['special_requests']);

try {
    $user_id = current_user()['id'];
    
    $reservation_id = db_insert("
        INSERT INTO reservations (user_id, name, email, phone, reservation_date, reservation_time, number_of_guests, special_requests, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ", [$user_id, $name, $email, $phone, $date, $time, $guests, $special_requests], 'isssssis');
    
    if ($reservation_id > 0) {
        log_security_event('reservation_created', [
            'name' => $name,
            'email' => $email,
            'date' => $date,
            'time' => $time,
            'guests' => $guests
        ], $user_id);
        
        if ($is_ajax) {
            echo json_encode(['success' => true, 'message' => 'Your reservation has been submitted successfully! We will contact you shortly to confirm.']);
            exit;
        }
        flash('success', 'Your reservation has been submitted successfully! We will contact you shortly to confirm.');
    } else {
        $rate_limiter->recordAttempt($client_ip, 'reservation');
        if ($is_ajax) {
            echo json_encode(['success' => false, 'message' => 'Failed to submit reservation. Please try again.']);
            exit;
        }
        flash('error', 'Failed to submit reservation. Please try again.');
    }
} catch (Exception $e) {
    $rate_limiter->recordAttempt($client_ip, 'reservation');
    log_security_event('reservation_error', ['error' => $e->getMessage()], $user_id);
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'An error occurred while processing your reservation. Please try again.']);
        exit;
    }
    flash('error', 'An error occurred while processing your reservation. Please try again.');
}

if (!$is_ajax) {
    header('Location: index.php');
    exit;
}
?>
