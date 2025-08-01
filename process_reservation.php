<?php
require_once 'config/database.php';
require_once 'classes/Reservation.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $reservation = new Reservation($db);
    
    $reservation->name = sanitize_input($_POST['name']);
    $reservation->email = sanitize_input($_POST['email']);
    $reservation->date = sanitize_input($_POST['date']);
    $reservation->time = sanitize_input($_POST['time']);
    $reservation->number_of_guests = sanitize_input($_POST['NumberofGuests']);
    $reservation->status = 'pending';
    
    if ($reservation->create()) {
        $_SESSION['reservation_success'] = true;
    } else {
        $_SESSION['reservation_error'] = true;
    }
    
    // Redirect back to the referring page
    $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
    redirect($redirect_url);
} else {
    redirect('index.php');
}
?>
