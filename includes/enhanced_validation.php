<?php
/**
 * Enhanced validation functions
 */

require_once __DIR__ . '/security.php';

class FormValidator {
    private $errors = [];
    private $data = [];
    
    public function __construct($data = []) {
        $this->data = $data;
    }
    
    /**
     * Validate required field
     */
    public function required($field, $message = null) {
        if (empty($this->data[$field])) {
            $this->errors[$field] = $message ?: ucfirst($field) . ' is required.';
        }
        return $this;
    }
    
    /**
     * Validate email
     */
    public function email($field, $message = null) {
        if (!empty($this->data[$field]) && !is_valid_email($this->data[$field])) {
            $this->errors[$field] = $message ?: 'Please enter a valid email address.';
        }
        return $this;
    }
    
    /**
     * Validate minimum length
     */
    public function minLength($field, $min, $message = null) {
        if (!empty($this->data[$field]) && strlen($this->data[$field]) < $min) {
            $this->errors[$field] = $message ?: ucfirst($field) . " must be at least {$min} characters long.";
        }
        return $this;
    }
    
    /**
     * Validate maximum length
     */
    public function maxLength($field, $max, $message = null) {
        if (!empty($this->data[$field]) && strlen($this->data[$field]) > $max) {
            $this->errors[$field] = $message ?: ucfirst($field) . " must not exceed {$max} characters.";
        }
        return $this;
    }
    
    /**
     * Validate phone number
     */
    public function phone($field, $message = null) {
        if (!empty($this->data[$field]) && !is_valid_phone($this->data[$field])) {
            $this->errors[$field] = $message ?: 'Please enter a valid phone number.';
        }
        return $this;
    }
    
    /**
     * Validate date
     */
    public function date($field, $format = 'Y-m-d', $message = null) {
        if (!empty($this->data[$field]) && !is_valid_date($this->data[$field], $format)) {
            $this->errors[$field] = $message ?: 'Please enter a valid date.';
        }
        return $this;
    }
    
    /**
     * Validate future date
     */
    public function futureDate($field, $message = null) {
        if (!empty($this->data[$field])) {
            $date = DateTime::createFromFormat('Y-m-d', $this->data[$field]);
            $today = new DateTime();
            $today->setTime(0, 0, 0);
            
            if (!$date || $date < $today) {
                $this->errors[$field] = $message ?: 'Date must be in the future.';
            }
        }
        return $this;
    }
    
    /**
     * Validate time
     */
    public function time($field, $format = 'H:i', $message = null) {
        if (!empty($this->data[$field]) && !is_valid_time($this->data[$field], $format)) {
            $this->errors[$field] = $message ?: 'Please enter a valid time.';
        }
        return $this;
    }
    
    /**
     * Validate numeric value
     */
    public function numeric($field, $message = null) {
        if (!empty($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = $message ?: ucfirst($field) . ' must be a number.';
        }
        return $this;
    }
    
    /**
     * Validate value is in array
     */
    public function in($field, $values, $message = null) {
        if (!empty($this->data[$field]) && !in_array($this->data[$field], $values)) {
            $this->errors[$field] = $message ?: 'Please select a valid option.';
        }
        return $this;
    }
    
    /**
     * Custom validation
     */
    public function custom($field, $callback, $message) {
        if (!empty($this->data[$field]) && !$callback($this->data[$field])) {
            $this->errors[$field] = $message;
        }
        return $this;
    }
    
    /**
     * Check if validation passed
     */
    public function passes() {
        return empty($this->errors);
    }
    
    /**
     * Check if validation failed
     */
    public function fails() {
        return !empty($this->errors);
    }
    
    /**
     * Get all errors
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Get first error
     */
    public function getFirstError() {
        return !empty($this->errors) ? reset($this->errors) : null;
    }
    
    /**
     * Get errors as string
     */
    public function getErrorsAsString($separator = '<br>') {
        return implode($separator, $this->errors);
    }
}

/**
 * Validate reservation data
 */
function validate_reservation_data($data) {
    $validator = new FormValidator($data);
    
    $validator
        ->required('name')
        ->maxLength('name', 100)
        ->required('email')
        ->email('email')
        ->required('phone')
        ->phone('phone')
        ->required('guests')
        ->numeric('guests')
        ->custom('guests', function($value) {
            return $value >= 1 && $value <= 20;
        }, 'Number of guests must be between 1 and 20.')
        ->required('date')
        ->date('date')
        ->futureDate('date')
        ->required('time')
        ->time('time')
        ->in('time', ['17:00', '17:30', '18:00', '18:30', '19:00', '19:30', '20:00', '20:30', '21:00'])
        ->maxLength('special_requests', 500);
    
    return $validator;
}

/**
 * Validate menu item data
 */
function validate_menu_item_data($data) {
    $validator = new FormValidator($data);
    
    $validator
        ->required('name')
        ->maxLength('name', 100)
        ->required('description')
        ->maxLength('description', 500)
        ->required('price')
        ->numeric('price')
        ->custom('price', function($value) {
            return $value > 0 && $value <= 999.99;
        }, 'Price must be between $0.01 and $999.99')
        ->required('category_id')
        ->numeric('category_id');
    
    return $validator;
}

/**
 * Validate user registration data
 */
function validate_user_registration_data($data) {
    $validator = new FormValidator($data);
    
    $validator
        ->required('name')
        ->maxLength('name', 100)
        ->required('email')
        ->email('email')
        ->required('password')
        ->minLength('password', 8)
        ->custom('password', 'is_strong_password', 'Password must contain at least one uppercase letter, one lowercase letter, and one number.');
    
    return $validator;
}
?>
