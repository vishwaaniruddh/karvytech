<?php

require_once __DIR__ . '/../../../config/auth.php';
require_once __DIR__ . '/../../../config/database.php';

Auth::requireRole(ADMIN_ROLE);

header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();

    // Sites
    $stmt = $db->prepare("SELECT id, site_id FROM sites ORDER BY site_id ASC");
    $stmt->execute();
    $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Cities
    $stmt = $db->prepare("
        SELECT DISTINCT TRIM(city) as city 
        FROM sites 
        WHERE city IS NOT NULL AND city != ''
        ORDER BY city ASC
    ");
    $stmt->execute();
    $cities = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // States
    $stmt = $db->prepare("
        SELECT DISTINCT TRIM(state) as state 
        FROM sites 
        WHERE state IS NOT NULL AND state != ''
        ORDER BY state ASC
    ");
    $stmt->execute();
    $states = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Branches (safe)
    try {
        $stmt = $db->prepare("SELECT DISTINCT TRIM(branch) as branch FROM sites WHERE branch IS NOT NULL AND branch != ''
        ORDER BY branch ASC");
        $stmt->execute();
        $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $branches = [];
    }


    // Contact Persons ✅
    $contacts = $db->query("
    SELECT DISTINCT 
        TRIM(contact_person_name) as name,
        TRIM(contact_person_number) as number
    FROM sites 
    WHERE contact_person_name IS NOT NULL 
      AND contact_person_name != ''
      AND contact_person_number IS NOT NULL
      AND contact_person_number != ''
    ORDER BY name ASC
")->fetchAll(PDO::FETCH_ASSOC);


    // Created By Users ✅
    $created_by = $db->query("
    SELECT DISTINCT TRIM(created_by) as created_by 
        FROM sites 
        WHERE created_by IS NOT NULL AND created_by != ''
        ORDER BY created_by ASC
")->fetchAll(PDO::FETCH_ASSOC);



    // Activity Status (static or DB se bhi le sakte ho)
    $activity_status = ['active', 'inactive'];




    echo json_encode([
        'success' => true,
        'data' => [
            'sites' => $sites ?? [],
            'cities' => $cities ?? [],
            'states' => $states ?? [],
            'branches' => $branches ?? [],
            'contacts' => $contacts ?? [],
            'activity_status' => $activity_status,
            'created_by' => $created_by ?? []
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}