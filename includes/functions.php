<?php
session_start();

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function is_admin_logged_in() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function require_admin_login() {
    if (!is_admin_logged_in()) {
        redirect('admin/login.php');
    }
}

function generate_session_id() {
    if (!isset($_SESSION['cart_session_id'])) {
        $_SESSION['cart_session_id'] = uniqid('cart_', true);
    }
    return $_SESSION['cart_session_id'];
}

function format_price($price) {
    return '$' . number_format($price, 2);
}

function format_date($date) {
    return date('M j, Y', strtotime($date));
}

function format_time($time) {
    return date('g:i A', strtotime($time));
}
?>
