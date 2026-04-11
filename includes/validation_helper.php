<?php
/**
 * Validation Helper Functions
 * 
 * Global validation functions for common data validation tasks
 * Usage: require_once __DIR__ . '/includes/validation_helper.php';
 */

/**
 * Validate Indian phone number
 * Accepts formats: 10 digits, +91-10digits, 0-10digits
 * 
 * @param string $phone Phone number to validate
 * @param bool $required Whether the field is required
 * @return array ['valid' => bool, 'message' => string, 'formatted' => string]
 */
function validatePhoneNumber($phone, $required = true) {
    $result = [
        'valid' => false,
        'message' => '',
        'formatted' => ''
    ];
    
    // Check if empty
    if (empty($phone) || trim($phone) === '') {
        if ($required) {
            $result['message'] = 'Phone number is required';
            return $result;
        } else {
            $result['valid'] = true;
            return $result;
        }
    }
    
    // Remove all spaces, hyphens, and parentheses
    $cleanPhone = preg_replace('/[\s\-\(\)]/', '', $phone);
    
    // Remove +91 country code if present
    $cleanPhone = preg_replace('/^\+91/', '', $cleanPhone);
    
    // Remove leading 0 if present
    $cleanPhone = preg_replace('/^0/', '', $cleanPhone);
    
    // Check if it's exactly 10 digits
    if (!preg_match('/^[6-9][0-9]{9}$/', $cleanPhone)) {
        $result['message'] = 'Invalid phone number. Must be 10 digits starting with 6-9';
        return $result;
    }
    
    $result['valid'] = true;
    $result['formatted'] = $cleanPhone;
    $result['message'] = 'Valid phone number';
    
    return $result;
}

/**
 * Validate email address
 * 
 * @param string $email Email address to validate
 * @param bool $required Whether the field is required
 * @return array ['valid' => bool, 'message' => string]
 */
function validateEmail($email, $required = true) {
    $result = [
        'valid' => false,
        'message' => ''
    ];
    
    // Check if empty
    if (empty($email) || trim($email) === '') {
        if ($required) {
            $result['message'] = 'Email address is required';
            return $result;
        } else {
            $result['valid'] = true;
            return $result;
        }
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $result['message'] = 'Invalid email address format';
        return $result;
    }
    
    // Check email length
    if (strlen($email) > 255) {
        $result['message'] = 'Email address is too long (max 255 characters)';
        return $result;
    }
    
    $result['valid'] = true;
    $result['message'] = 'Valid email address';
    
    return $result;
}

/**
 * Validate Indian PIN code
 * 
 * @param string $pincode PIN code to validate
 * @param bool $required Whether the field is required
 * @return array ['valid' => bool, 'message' => string]
 */
function validatePincode($pincode, $required = true) {
    $result = [
        'valid' => false,
        'message' => ''
    ];
    
    // Check if empty
    if (empty($pincode) || trim($pincode) === '') {
        if ($required) {
            $result['message'] = 'PIN code is required';
            return $result;
        } else {
            $result['valid'] = true;
            return $result;
        }
    }
    
    // Remove spaces
    $cleanPincode = preg_replace('/\s/', '', $pincode);
    
    // Check if it's exactly 6 digits
    if (!preg_match('/^[1-9][0-9]{5}$/', $cleanPincode)) {
        $result['message'] = 'Invalid PIN code. Must be 6 digits';
        return $result;
    }
    
    $result['valid'] = true;
    $result['message'] = 'Valid PIN code';
    
    return $result;
}

/**
 * Validate GST number (Indian)
 * Format: 22AAAAA0000A1Z5
 * 
 * @param string $gst GST number to validate
 * @param bool $required Whether the field is required
 * @return array ['valid' => bool, 'message' => string]
 */
function validateGST($gst, $required = true) {
    $result = [
        'valid' => false,
        'message' => ''
    ];
    
    // Check if empty
    if (empty($gst) || trim($gst) === '') {
        if ($required) {
            $result['message'] = 'GST number is required';
            return $result;
        } else {
            $result['valid'] = true;
            return $result;
        }
    }
    
    // Remove spaces and convert to uppercase
    $cleanGST = strtoupper(preg_replace('/\s/', '', $gst));
    
    // GST format: 2 digits (state code) + 10 chars (PAN) + 1 digit (entity number) + 1 char (Z) + 1 check digit
    if (!preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $cleanGST)) {
        $result['message'] = 'Invalid GST number format';
        return $result;
    }
    
    $result['valid'] = true;
    $result['message'] = 'Valid GST number';
    
    return $result;
}

/**
 * Validate PAN number (Indian)
 * Format: AAAAA9999A
 * 
 * @param string $pan PAN number to validate
 * @param bool $required Whether the field is required
 * @return array ['valid' => bool, 'message' => string]
 */
function validatePAN($pan, $required = true) {
    $result = [
        'valid' => false,
        'message' => ''
    ];
    
    // Check if empty
    if (empty($pan) || trim($pan) === '') {
        if ($required) {
            $result['message'] = 'PAN number is required';
            return $result;
        } else {
            $result['valid'] = true;
            return $result;
        }
    }
    
    // Remove spaces and convert to uppercase
    $cleanPAN = strtoupper(preg_replace('/\s/', '', $pan));
    
    // PAN format: 5 letters + 4 digits + 1 letter
    if (!preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $cleanPAN)) {
        $result['message'] = 'Invalid PAN number format (e.g., ABCDE1234F)';
        return $result;
    }
    
    $result['valid'] = true;
    $result['message'] = 'Valid PAN number';
    
    return $result;
}

/**
 * Validate Aadhaar number (Indian)
 * 
 * @param string $aadhaar Aadhaar number to validate
 * @param bool $required Whether the field is required
 * @return array ['valid' => bool, 'message' => string]
 */
function validateAadhaar($aadhaar, $required = true) {
    $result = [
        'valid' => false,
        'message' => ''
    ];
    
    // Check if empty
    if (empty($aadhaar) || trim($aadhaar) === '') {
        if ($required) {
            $result['message'] = 'Aadhaar number is required';
            return $result;
        } else {
            $result['valid'] = true;
            return $result;
        }
    }
    
    // Remove spaces
    $cleanAadhaar = preg_replace('/\s/', '', $aadhaar);
    
    // Check if it's exactly 12 digits
    if (!preg_match('/^[2-9][0-9]{11}$/', $cleanAadhaar)) {
        $result['message'] = 'Invalid Aadhaar number. Must be 12 digits';
        return $result;
    }
    
    $result['valid'] = true;
    $result['message'] = 'Valid Aadhaar number';
    
    return $result;
}

/**
 * Validate URL
 * 
 * @param string $url URL to validate
 * @param bool $required Whether the field is required
 * @return array ['valid' => bool, 'message' => string]
 */
function validateURL($url, $required = true) {
    $result = [
        'valid' => false,
        'message' => ''
    ];
    
    // Check if empty
    if (empty($url) || trim($url) === '') {
        if ($required) {
            $result['message'] = 'URL is required';
            return $result;
        } else {
            $result['valid'] = true;
            return $result;
        }
    }
    
    // Validate URL format
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $result['message'] = 'Invalid URL format';
        return $result;
    }
    
    $result['valid'] = true;
    $result['message'] = 'Valid URL';
    
    return $result;
}

/**
 * Validate date format
 * 
 * @param string $date Date string to validate
 * @param string $format Expected date format (default: Y-m-d)
 * @param bool $required Whether the field is required
 * @return array ['valid' => bool, 'message' => string]
 */
function validateDate($date, $format = 'Y-m-d', $required = true) {
    $result = [
        'valid' => false,
        'message' => ''
    ];
    
    // Check if empty
    if (empty($date) || trim($date) === '') {
        if ($required) {
            $result['message'] = 'Date is required';
            return $result;
        } else {
            $result['valid'] = true;
            return $result;
        }
    }
    
    // Try to create DateTime object
    $d = DateTime::createFromFormat($format, $date);
    
    if (!$d || $d->format($format) !== $date) {
        $result['message'] = "Invalid date format. Expected format: $format";
        return $result;
    }
    
    $result['valid'] = true;
    $result['message'] = 'Valid date';
    
    return $result;
}

/**
 * Validate numeric value with optional range
 * 
 * @param mixed $value Value to validate
 * @param bool $required Whether the field is required
 * @param float|null $min Minimum value (optional)
 * @param float|null $max Maximum value (optional)
 * @return array ['valid' => bool, 'message' => string]
 */
function validateNumeric($value, $required = true, $min = null, $max = null) {
    $result = [
        'valid' => false,
        'message' => ''
    ];
    
    // Check if empty
    if ($value === '' || $value === null) {
        if ($required) {
            $result['message'] = 'Value is required';
            return $result;
        } else {
            $result['valid'] = true;
            return $result;
        }
    }
    
    // Check if numeric
    if (!is_numeric($value)) {
        $result['message'] = 'Value must be numeric';
        return $result;
    }
    
    // Check minimum value
    if ($min !== null && $value < $min) {
        $result['message'] = "Value must be at least $min";
        return $result;
    }
    
    // Check maximum value
    if ($max !== null && $value > $max) {
        $result['message'] = "Value must not exceed $max";
        return $result;
    }
    
    $result['valid'] = true;
    $result['message'] = 'Valid numeric value';
    
    return $result;
}

/**
 * Validate string length
 * 
 * @param string $value String to validate
 * @param bool $required Whether the field is required
 * @param int|null $min Minimum length (optional)
 * @param int|null $max Maximum length (optional)
 * @return array ['valid' => bool, 'message' => string]
 */
function validateStringLength($value, $required = true, $min = null, $max = null) {
    $result = [
        'valid' => false,
        'message' => ''
    ];
    
    // Check if empty
    if (empty($value) || trim($value) === '') {
        if ($required) {
            $result['message'] = 'Value is required';
            return $result;
        } else {
            $result['valid'] = true;
            return $result;
        }
    }
    
    $length = strlen($value);
    
    // Check minimum length
    if ($min !== null && $length < $min) {
        $result['message'] = "Value must be at least $min characters";
        return $result;
    }
    
    // Check maximum length
    if ($max !== null && $length > $max) {
        $result['message'] = "Value must not exceed $max characters";
        return $result;
    }
    
    $result['valid'] = true;
    $result['message'] = 'Valid string length';
    
    return $result;
}

/**
 * Sanitize phone number for storage
 * Removes all formatting and returns 10-digit number
 * 
 * @param string $phone Phone number to sanitize
 * @return string Sanitized phone number
 */
function sanitizePhoneNumber($phone) {
    // Remove all non-numeric characters
    $clean = preg_replace('/[^0-9]/', '', $phone);
    
    // Remove country code if present
    $clean = preg_replace('/^91/', '', $clean);
    
    // Remove leading 0 if present
    $clean = preg_replace('/^0/', '', $clean);
    
    return $clean;
}

/**
 * Format phone number for display
 * Formats 10-digit number as XXX-XXX-XXXX
 * 
 * @param string $phone Phone number to format
 * @return string Formatted phone number
 */
function formatPhoneNumber($phone) {
    $clean = sanitizePhoneNumber($phone);
    
    if (strlen($clean) === 10) {
        return substr($clean, 0, 3) . '-' . substr($clean, 3, 3) . '-' . substr($clean, 6);
    }
    
    return $phone;
}
?>
