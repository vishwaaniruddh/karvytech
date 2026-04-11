<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Updating location masters...\n";
    
    // Insert sample zones if not exists
    $zones = [
        'North Zone',
        'South Zone',
        'East Zone',
        'West Zone',
        'Central Zone'
    ];
    
    foreach ($zones as $zoneName) {
        $stmt = $db->prepare("INSERT IGNORE INTO zones (name, status, created_at, updated_at) VALUES (?, 'active', NOW(), NOW())");
        $stmt->execute([$zoneName]);
    }
    echo "Zones updated successfully.\n";
    
    // Insert sample countries if not exists
    $countries = [
        'India',
        'United States',
        'United Kingdom',
        'Canada',
        'Australia'
    ];
    
    foreach ($countries as $countryName) {
        $stmt = $db->prepare("INSERT IGNORE INTO countries (name, status, created_at, updated_at) VALUES (?, 'active', NOW(), NOW())");
        $stmt->execute([$countryName]);
    }
    echo "Countries updated successfully.\n";
    
    // Get India's ID for states
    $stmt = $db->prepare("SELECT id FROM countries WHERE name = 'India' LIMIT 1");
    $stmt->execute();
    $indiaId = $stmt->fetchColumn();
    
    if ($indiaId) {
        // Insert sample Indian states
        $states = [
            'Maharashtra',
            'Karnataka',
            'Tamil Nadu',
            'Gujarat',
            'Rajasthan',
            'Uttar Pradesh',
            'West Bengal',
            'Delhi'
        ];
        
        foreach ($states as $stateName) {
            $stmt = $db->prepare("INSERT IGNORE INTO states (name, country_id, status, created_at, updated_at) VALUES (?, ?, 'active', NOW(), NOW())");
            $stmt->execute([$stateName, $indiaId]);
        }
        echo "States updated successfully.\n";
        
        // Get Maharashtra's ID for cities
        $stmt = $db->prepare("SELECT id FROM states WHERE name = 'Maharashtra' AND country_id = ? LIMIT 1");
        $stmt->execute([$indiaId]);
        $maharashtraId = $stmt->fetchColumn();
        
        if ($maharashtraId) {
            // Insert sample Maharashtra cities
            $cities = [
                'Mumbai',
                'Pune',
                'Nagpur',
                'Nashik',
                'Aurangabad'
            ];
            
            foreach ($cities as $cityName) {
                $stmt = $db->prepare("INSERT IGNORE INTO cities (name, state_id, country_id, status, created_at, updated_at) VALUES (?, ?, ?, 'active', NOW(), NOW())");
                $stmt->execute([$cityName, $maharashtraId, $indiaId]);
            }
            echo "Cities updated successfully.\n";
        }
        
        // Get Karnataka's ID for cities
        $stmt = $db->prepare("SELECT id FROM states WHERE name = 'Karnataka' AND country_id = ? LIMIT 1");
        $stmt->execute([$indiaId]);
        $karnatakaId = $stmt->fetchColumn();
        
        if ($karnatakaId) {
            // Insert sample Karnataka cities
            $cities = [
                'Bangalore',
                'Mysore',
                'Hubli',
                'Mangalore'
            ];
            
            foreach ($cities as $cityName) {
                $stmt = $db->prepare("INSERT IGNORE INTO cities (name, state_id, country_id, status, created_at, updated_at) VALUES (?, ?, ?, 'active', NOW(), NOW())");
                $stmt->execute([$cityName, $karnatakaId, $indiaId]);
            }
            echo "Karnataka cities updated successfully.\n";
        }
    }
    
    echo "Location masters update completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error updating location masters: " . $e->getMessage() . "\n";
    exit(1);
}
?>