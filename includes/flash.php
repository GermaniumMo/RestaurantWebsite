<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function flash($type, $message)
{
    $_SESSION['flash'][$type] = $message;
}

function flash_get($type)
{
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

function flash_show($type)
{
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

function has_flash($type)
{
    return isset($_SESSION['flash'][$type]);
}

function flash_show_all()
{
    if (isset($_SESSION['flash'])) {
        foreach ($_SESSION['flash'] as $type => $message) {
            flash_show($type);
        }
    }
}

function flash_clear()
{
    unset($_SESSION['flash']);
}
