<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
// constants.php is already included by auth.php

class BaseController {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    protected function render($view, $data = []) {
        extract($data);
        include "../views/$view.php";
    }
    
    protected function redirect($url) {
        header("Location: $url");
        exit();
    }
    
    protected function jsonResponse($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
    
    protected function validateInput($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                $errors[$field] = "$field is required";
                continue;
            }
            
            if (!empty($value)) {
                if (isset($rule['min_length']) && strlen($value) < $rule['min_length']) {
                    $errors[$field] = "$field must be at least {$rule['min_length']} characters";
                }
                
                if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
                    $errors[$field] = "$field must not exceed {$rule['max_length']} characters";
                }
                
                if (isset($rule['email']) && $rule['email'] && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "$field must be a valid email address";
                }
            }
        }
        
        return $errors;
    }
}
?>