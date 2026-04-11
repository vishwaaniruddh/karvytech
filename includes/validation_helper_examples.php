<?php
/**
 * Validation Helper - Usage Examples
 * 
 * This file demonstrates how to use the validation helper functions
 */

require_once __DIR__ . '/validation_helper.php';

// ============================================
// EXAMPLE 1: Phone Number Validation
// ============================================

echo "<h2>Phone Number Validation Examples</h2>\n\n";

$phoneNumbers = [
    '9876543210',           // Valid
    '+91-9876543210',       // Valid with country code
    '0-9876543210',         // Valid with leading 0
    '987 654 3210',         // Valid with spaces
    '12345',                // Invalid - too short
    '5876543210',           // Invalid - doesn't start with 6-9
    '',                     // Empty
];

foreach ($phoneNumbers as $phone) {
    $result = validatePhoneNumber($phone, false); // false = not required
    echo "Phone: '$phone' - " . ($result['valid'] ? '✓ VALID' : '✗ INVALID') . " - {$result['message']}\n";
    if ($result['valid'] && !empty($result['formatted'])) {
        echo "  Formatted: {$result['formatted']}\n";
    }
    echo "\n";
}

// ============================================
// EXAMPLE 2: Using in Form Processing
// ============================================

echo "\n<h2>Form Processing Example</h2>\n\n";

// Simulated POST data
$_POST = [
    'contact_person_phone' => '+91-9876543210',
    'email' => 'test@example.com',
    'pincode' => '400001'
];

$errors = [];

// Validate phone
$phoneValidation = validatePhoneNumber($_POST['contact_person_phone'], true);
if (!$phoneValidation['valid']) {
    $errors['phone'] = $phoneValidation['message'];
} else {
    $cleanPhone = $phoneValidation['formatted'];
    echo "✓ Phone validated: $cleanPhone\n";
}

// Validate email
$emailValidation = validateEmail($_POST['email'], true);
if (!$emailValidation['valid']) {
    $errors['email'] = $emailValidation['message'];
} else {
    echo "✓ Email validated: {$_POST['email']}\n";
}

// Validate pincode
$pincodeValidation = validatePincode($_POST['pincode'], true);
if (!$pincodeValidation['valid']) {
    $errors['pincode'] = $pincodeValidation['message'];
} else {
    echo "✓ Pincode validated: {$_POST['pincode']}\n";
}

if (!empty($errors)) {
    echo "\n✗ Validation errors:\n";
    foreach ($errors as $field => $message) {
        echo "  - $field: $message\n";
    }
} else {
    echo "\n✓ All validations passed!\n";
}

// ============================================
// EXAMPLE 3: Using in API/AJAX Response
// ============================================

echo "\n<h2>API Response Example</h2>\n\n";

// Simulated API request
$apiData = [
    'phone' => '9876543210',
    'gst' => '22AAAAA0000A1Z5',
    'pan' => 'ABCDE1234F'
];

$apiErrors = [];

// Validate phone
$phoneResult = validatePhoneNumber($apiData['phone']);
if (!$phoneResult['valid']) {
    $apiErrors['phone'] = $phoneResult['message'];
}

// Validate GST
$gstResult = validateGST($apiData['gst']);
if (!$gstResult['valid']) {
    $apiErrors['gst'] = $gstResult['message'];
}

// Validate PAN
$panResult = validatePAN($apiData['pan']);
if (!$panResult['valid']) {
    $apiErrors['pan'] = $panResult['message'];
}

// Return JSON response
$response = [
    'success' => empty($apiErrors),
    'errors' => $apiErrors,
    'data' => empty($apiErrors) ? $apiData : null
];

echo json_encode($response, JSON_PRETTY_PRINT) . "\n";

// ============================================
// EXAMPLE 4: Sanitize and Format Functions
// ============================================

echo "\n<h2>Sanitize and Format Examples</h2>\n\n";

$rawPhone = '+91-987-654-3210';
$sanitized = sanitizePhoneNumber($rawPhone);
$formatted = formatPhoneNumber($rawPhone);

echo "Raw phone: $rawPhone\n";
echo "Sanitized: $sanitized (for database storage)\n";
echo "Formatted: $formatted (for display)\n";

// ============================================
// EXAMPLE 5: Numeric Validation with Range
// ============================================

echo "\n<h2>Numeric Validation Examples</h2>\n\n";

$quantities = [
    ['value' => 50, 'min' => 1, 'max' => 100],
    ['value' => 150, 'min' => 1, 'max' => 100],
    ['value' => -5, 'min' => 1, 'max' => 100],
];

foreach ($quantities as $test) {
    $result = validateNumeric($test['value'], true, $test['min'], $test['max']);
    echo "Value: {$test['value']} (range: {$test['min']}-{$test['max']}) - ";
    echo ($result['valid'] ? '✓ VALID' : '✗ INVALID') . " - {$result['message']}\n";
}

// ============================================
// EXAMPLE 6: String Length Validation
// ============================================

echo "\n<h2>String Length Validation Examples</h2>\n\n";

$names = [
    ['value' => 'John', 'min' => 2, 'max' => 50],
    ['value' => 'A', 'min' => 2, 'max' => 50],
    ['value' => str_repeat('A', 60), 'min' => 2, 'max' => 50],
];

foreach ($names as $test) {
    $result = validateStringLength($test['value'], true, $test['min'], $test['max']);
    $displayValue = strlen($test['value']) > 20 ? substr($test['value'], 0, 20) . '...' : $test['value'];
    echo "Value: '$displayValue' (length: " . strlen($test['value']) . ") - ";
    echo ($result['valid'] ? '✓ VALID' : '✗ INVALID') . " - {$result['message']}\n";
}

// ============================================
// EXAMPLE 7: Complete Form Validation
// ============================================

echo "\n<h2>Complete Form Validation Example</h2>\n\n";

function validateVendorForm($data) {
    $errors = [];
    
    // Company name
    $nameResult = validateStringLength($data['company_name'] ?? '', true, 2, 255);
    if (!$nameResult['valid']) {
        $errors['company_name'] = $nameResult['message'];
    }
    
    // Phone
    $phoneResult = validatePhoneNumber($data['phone'] ?? '', true);
    if (!$phoneResult['valid']) {
        $errors['phone'] = $phoneResult['message'];
    }
    
    // Email
    $emailResult = validateEmail($data['email'] ?? '', true);
    if (!$emailResult['valid']) {
        $errors['email'] = $emailResult['message'];
    }
    
    // GST (optional)
    if (!empty($data['gst'])) {
        $gstResult = validateGST($data['gst'], false);
        if (!$gstResult['valid']) {
            $errors['gst'] = $gstResult['message'];
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

$vendorData = [
    'company_name' => 'ABC Company Pvt Ltd',
    'phone' => '9876543210',
    'email' => 'contact@abc.com',
    'gst' => '22AAAAA0000A1Z5'
];

$validation = validateVendorForm($vendorData);

if ($validation['valid']) {
    echo "✓ Vendor form validation passed!\n";
    echo "Ready to save to database.\n";
} else {
    echo "✗ Vendor form validation failed:\n";
    foreach ($validation['errors'] as $field => $message) {
        echo "  - $field: $message\n";
    }
}

?>
