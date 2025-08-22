<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'savoria_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site Configuration
define('BASE_URL', 'http://localhost/RestaurantWebsite');
define('SITE_NAME', 'Savoria Restaurant');

// Security Configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Pagination
define('ITEMS_PER_PAGE', 10);

// File Upload
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_PATH', __DIR__ . '/uploads/');

// Error Reporting (set to false in production)
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('America/New_York');
?>
