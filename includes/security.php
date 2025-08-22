<?php
/**
 * Security utilities and helpers
 */

/**
 * Set security headers
 */
function set_security_headers() {
    // Prevent XSS attacks
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self';");
    
    // HTTPS enforcement in production
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

/**
 * Rate limiting implementation
 */
class RateLimiter {
    private $db;
    
    public function __construct($db = null) {
        $this->db = $db ?: db();
    }
    
    /**
     * Check if action is rate limited
     */
    public function isLimited($identifier, $action, $max_attempts = 5, $window_minutes = 15) {
        $window_start = date('Y-m-d H:i:s', strtotime("-{$window_minutes} minutes"));
        
        // Clean old attempts
        db_execute("DELETE FROM rate_limits WHERE created_at < ?", [$window_start]);
        
        // Count recent attempts
        $result = db_query("SELECT COUNT(*) as count FROM rate_limits WHERE identifier = ? AND action = ? AND created_at >= ?", 
                          [$identifier, $action, $window_start]);
        $attempts = 0;
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $attempts = $row['count'];
        }
        
        return $attempts >= $max_attempts;
    }
    
    /**
     * Record an attempt
     */
    public function recordAttempt($identifier, $action) {
        db_execute("INSERT INTO rate_limits (identifier, action, created_at) VALUES (?, ?, NOW())", 
                  [$identifier, $action]);
    }
    
    /**
     * Get remaining attempts
     */
    public function getRemainingAttempts($identifier, $action, $max_attempts = 5, $window_minutes = 15) {
        $window_start = date('Y-m-d H:i:s', strtotime("-{$window_minutes} minutes"));
        
        $result = db_query("SELECT COUNT(*) as count FROM rate_limits WHERE identifier = ? AND action = ? AND created_at >= ?", 
                          [$identifier, $action, $window_start]);
        $attempts = 0;
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $attempts = $row['count'];
        }
        
        return max(0, $max_attempts - $attempts);
    }
}

/**
 * Get client IP address
 */
function get_client_ip() {
    $ip_keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    
    foreach ($ip_keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            // Handle comma-separated IPs (from proxies)
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Sanitize input data
 */
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (basic)
 */
function is_valid_phone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return strlen($phone) >= 10 && strlen($phone) <= 15;
}

/**
 * Validate date format
 */
function is_valid_date($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Validate time format
 */
function is_valid_time($time, $format = 'H:i') {
    $t = DateTime::createFromFormat($format, $time);
    return $t && $t->format($format) === $time;
}

/**
 * Check if password meets requirements
 */
function is_strong_password($password) {
    return strlen($password) >= 8 && 
           preg_match('/[A-Z]/', $password) && 
           preg_match('/[a-z]/', $password) && 
           preg_match('/[0-9]/', $password);
}

/**
 * Generate secure random token
 */
function generate_secure_token($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Log security events
 */
function log_security_event($event, $details = [], $user_id = null) {
    try {
        db_execute("INSERT INTO security_logs (event, details, user_id, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())", [
            $event,
            json_encode($details),
            $user_id,
            get_client_ip(),
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (Exception $e) {
        error_log("Failed to log security event: " . $e->getMessage());
    }
}
?>
