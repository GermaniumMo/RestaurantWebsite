<?php
// Validation helper functions

if (! function_exists('sanitize_input')) {
    function sanitize_input($data)
    {
        if (is_array($data)) {
            return array_map('sanitize_input', $data);
        }
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }
}

function validate_required($value, $field_name = 'Field')
{
    if (empty(trim($value))) {
        return "$field_name is required.";
    }
    return null;
}

function validate_email($email)
{
    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Please enter a valid email address.";
    }
    return null;
}

function validate_min_length($value, $min_length, $field_name = 'Field')
{
    if (strlen(trim($value)) < $min_length) {
        return "$field_name must be at least $min_length characters long.";
    }
    return null;
}

function validate_max_length($value, $max_length, $field_name = 'Field')
{
    if (strlen(trim($value)) > $max_length) {
        return "$field_name must not exceed $max_length characters.";
    }
    return null;
}

function validate_numeric($value, $field_name = 'Field')
{
    if (! is_numeric($value)) {
        return "$field_name must be a valid number.";
    }
    return null;
}

function validate_positive_integer($value, $field_name = 'Field')
{
    if (! filter_var($value, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
        return "$field_name must be a positive integer.";
    }
    return null;
}

function validate_date($date, $format = 'Y-m-d', $field_name = 'Date')
{
    $d = DateTime::createFromFormat($format, $date);
    if (! $d || $d->format($format) !== $date) {
        return "$field_name must be a valid date.";
    }
    return null;
}

function validate_time($time, $field_name = 'Time')
{
    if (! preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
        return "$field_name must be a valid time in HH:MM format.";
    }
    return null;
}

function validate_phone($phone, $field_name = 'Phone')
{
    if (! preg_match('/^\+?[\d\s\-]{10,}$/', $phone)) {
        return "$field_name must be a valid phone number.";
    }
    return null;
}

function validate_unique_email($email, $exclude_id = null)
{
    $sql    = "SELECT id FROM users WHERE email = ?";
    $params = [$email];
    $types  = 's';

    if ($exclude_id) {
        $sql .= " AND id != ?";
        $params[] = $exclude_id;
        $types .= 'i';
    }

    $existing = db_fetch_one($sql, $params, $types);

    if ($existing) {
        return "This email address is already registered.";
    }
    return null;
}

function sanitize_array($array)
{
    $sanitized = [];
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $sanitized[$key] = sanitize_array($value);
        } else {
            $sanitized[$key] = sanitize_input($value);
        }
    }
    return $sanitized;
}

// Validate multiple fields at once
function validate_fields($data, $rules)
{
    $errors = [];

    foreach ($rules as $field => $field_rules) {
        $value = $data[$field] ?? '';

        foreach ($field_rules as $rule) {
            $error = null;

            switch ($rule['type']) {
                case 'required':
                    $error = validate_required($value, $rule['field_name'] ?? $field);
                    break;
                case 'email':
                    if (! empty($value)) {
                        $error = validate_email($value);
                    }
                    break;
                case 'min_length':
                    if (! empty($value)) {
                        $error = validate_min_length($value, $rule['length'], $rule['field_name'] ?? $field);
                    }
                    break;
                case 'max_length':
                    if (! empty($value)) {
                        $error = validate_max_length($value, $rule['length'], $rule['field_name'] ?? $field);
                    }
                    break;
                case 'unique_email':
                    if (! empty($value)) {
                        $error = validate_unique_email($value, $rule['exclude_id'] ?? null);
                    }
                    break;
            }

            if ($error) {
                $errors[$field] = $error;
                break; // Stop at first error for this field
            }
        }
    }

    return $errors;
}
