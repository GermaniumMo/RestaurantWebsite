<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set flash message
function flash($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

// Get and remove flash message
function flash_get($type) {
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

// Display flash message as Bootstrap alert
function flash_show($type) {
    $message = flash_get($type);
    if ($message) {
        $alert_class = '';
        switch ($type) {
            case 'success':
                $alert_class = 'alert-success';
                break;
            case 'error':
                $alert_class = 'alert-danger';
                break;
            case 'warning':
                $alert_class = 'alert-warning';
                break;
            case 'info':
                $alert_class = 'alert-info';
                break;
            default:
                $alert_class = 'alert-primary';
        }
        
        echo '<div class="alert ' . $alert_class . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}

// Check if flash message exists
function has_flash($type) {
    return isset($_SESSION['flash'][$type]);
}

// Display all flash messages
function flash_show_all() {
    if (isset($_SESSION['flash'])) {
        foreach ($_SESSION['flash'] as $type => $message) {
            flash_show($type);
        }
    }
}

// Clear all flash messages
function flash_clear() {
    unset($_SESSION['flash']);
}
?>
