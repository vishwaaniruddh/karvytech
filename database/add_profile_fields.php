<?php
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Adding profile fields to users table...\n";
    
    // Check if columns already exist
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $fieldsToAdd = [];
    
    if (!in_array('first_name', $columns)) {
        $fieldsToAdd[] = "ADD COLUMN first_name VARCHAR(50) NULL AFTER username";
    }
    
    if (!in_array('last_name', $columns)) {
        $fieldsToAdd[] = "ADD COLUMN last_name VARCHAR(50) NULL AFTER " . (in_array('first_name', $columns) ? 'first_name' : 'username');
    }
    
    if (!in_array('bio', $columns)) {
        $fieldsToAdd[] = "ADD COLUMN bio TEXT NULL AFTER phone";
    }
    
    if (!in_array('profile_picture', $columns)) {
        $fieldsToAdd[] = "ADD COLUMN profile_picture VARCHAR(255) NULL AFTER " . (in_array('bio', $columns) ? 'bio' : 'phone');
    }
    
    if (!empty($fieldsToAdd)) {
        $sql = "ALTER TABLE users " . implode(', ', $fieldsToAdd);
        $pdo->exec($sql);
        echo "Profile fields added successfully!\n";
        echo "Added fields: " . implode(', ', array_map(function($field) {
            preg_match('/ADD COLUMN (\w+)/', $field, $matches);
            return $matches[1];
        }, $fieldsToAdd)) . "\n";
    } else {
        echo "All profile fields already exist.\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>