<?php
require_once 'functions.php';

function is_user_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function require_user_login() {
    if (!is_user_logged_in()) {
        redirect('auth/login.php');
    }
}

function get_current_user() {
    if (!is_user_logged_in()) {
        return null;
    }
    
    require_once 'config/database.php';
    require_once 'classes/User.php';
    
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);
    
    $user->id = $_SESSION['user_id'];
    if ($user->read_single()) {
        return $user;
    }
    
    return null;
}

function logout_user() {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_name']);
    session_destroy();
}

function login_user($user) {
    $_SESSION['user_id'] = $user->id;
    $_SESSION['user_email'] = $user->email;
    $_SESSION['user_name'] = $user->first_name . ' ' . $user->last_name;
}
?>
