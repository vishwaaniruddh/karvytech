<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Creating sample installation data...\n";
    
    // First, let's check if we have any vendors and sites
    $vendorStmt = $db->query("SELECT id FROM vendors LIMIT 1");
    $vendor = $vendorStmt->fetch();
    
    $siteStmt = $db->query("SELECT id FROM sites LIMIT 1");
    $site = $siteStmt->fetch();
    
    $userStmt = $db->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    $admin = $userStmt->fetch();
    
    if (!$vendor || !$site || !$admin) {
        echo "Missing required data (vendor, site, or admin user). Please ensure you have:\n";
        echo "- At least one vendor in the vendors table\n";
        echo "- At least one site in the sites table\n";
        echo "- At least one admin user in the users table\n";
        return;
    }
    
    // Check if we already have installation data
    $checkStmt = $db->query("SELECT COUNT(*) FROM installation_delegations");
    $count = $checkStmt->fetchColumn();
    
    if ($count > 0) {
        echo "Installation data already exists ($count records found).\n";
        return;
    }
    
    // Create sample installation delegations
    $installations = [
        [
            'site_id' => $site['id'],
            'vendor_id' => $vendor['id'],
            'delegated_by' => $admin['id'],
            'expected_start_date' => date('Y-m-d', strtotime('+1 day')),
            'expected_completion_date' => date('Y-m-d', strtotime('+7 days')),
            'priority' => 'high',
            'installation_type' => 'standard',
            'status' => 'assigned',
            'special_instructions' => 'Please ensure all safety protocols are followed during installation.'
        ],
        [
            'site_id' => $site['id'],
            'vendor_id' => $vendor['id'],
            'delegated_by' => $admin['id'],
            'expected_start_date' => date('Y-m-d', strtotime('+3 days')),
            'expected_completion_date' => date('Y-m-d', strtotime('+10 days')),
            'priority' => 'medium',
            'installation_type' => 'complex',
            'status' => 'acknowledged',
            'special_instructions' => 'Complex installation requiring specialized equipment.'
        ]
    ];
    
    $sql = "INSERT INTO installation_delegations (
        site_id, vendor_id, delegated_by, delegation_date,
        expected_start_date, expected_completion_date, priority,
        installation_type, status, special_instructions
    ) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($sql);
    
    foreach ($installations as $installation) {
        $stmt->execute([
            $installation['site_id'],
            $installation['vendor_id'],
            $installation['delegated_by'],
            $installation['expected_start_date'],
            $installation['expected_completion_date'],
            $installation['priority'],
            $installation['installation_type'],
            $installation['status'],
            $installation['special_instructions']
        ]);
    }
    
    echo "Sample installation data created successfully!\n";
    echo "Created " . count($installations) . " installation records.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>