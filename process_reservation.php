<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/flash.php';
require_once __DIR__ . '/includes/validation.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/enhanced_validation.php';
require_once __DIR__ . '/includes/db.php';

if (!is_logged_in()) {
    flash('error', 'You must be logged in to make a reservation.');
    header('Location: auth/login.php');
    exit;
}

// Set security headers
set_security_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!verify_csrf()) {
    flash('error', 'Invalid security token.');
    header('Location: index.php');
    exit;
}

$db = db();
$rate_limiter = new RateLimiter($db);
$client_ip = get_client_ip();

if ($rate_limiter->isLimited($client_ip, 'reservation', 3, 10)) {
    log_security_event('reservation_rate_limited', ['ip' => $client_ip]);
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
        
        flash('success', 'Your reservation has been submitted successfully! We will contact you shortly to confirm.');
    } else {
        $rate_limiter->recordAttempt($client_ip, 'reservation');
        flash('error', 'Failed to submit reservation. Please try again.');
    }
} catch (Exception $e) {
    $rate_limiter->recordAttempt($client_ip, 'reservation');
    log_security_event('reservation_error', ['error' => $e->getMessage()], $user_id);
    flash('error', 'An error occurred while processing your reservation. Please try again.');
}

header('Location: index.php');
exit;
?>
