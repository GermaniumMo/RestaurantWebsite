<?php
require_once 'config/database.php';
require_once 'classes/ContactMessage.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $contactMessage = new ContactMessage($db);
    
    $contactMessage->first_name = sanitize_input($_POST['first_name']);
    $contactMessage->last_name = sanitize_input($_POST['last_name']);
    $contactMessage->email = sanitize_input($_POST['email']);
    $contactMessage->message = sanitize_input($_POST['message']);
    $contactMessage->status = 'unread';
    
    if ($contactMessage->create()) {
        $_SESSION['contact_success'] = true;
    } else {
        $_SESSION['contact_error'] = true;
    }
    
    // Redirect back to the referring page
    $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'contact.php';
    redirect($redirect_url);
} else {
    redirect('contact.php');
}
?>
