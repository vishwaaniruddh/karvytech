<?php
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

// Get all responses for TAP1 site
echo "<h2>Responses for site_id 'TAP1'</h2>";

// First, find the site
$stmt = $db->prepare("SELECT * FROM sites WHERE site_id = ?");
$stmt->execute(['TAP1']);
$site = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h3>Site Details:</h3>";
echo "<pre>";
print_r($site);
echo "</pre>";

if ($site) {
    // Find responses by sites table ID
    $stmt = $db->prepare("
        SELECT sr.id, sr.site_id, sr.survey_form_id, sr.survey_status, sr.submitted_date,
               s.site_id as site_code, s.customer_id,
               c.id as customer_table_id, c.name as customer_name
        FROM dynamic_survey_responses sr
        LEFT JOIN sites s ON sr.site_id = s.id
        LEFT JOIN customers c ON s.customer_id = c.id
        WHERE s.site_id = ?
        ORDER BY sr.id DESC
    ");
    $stmt->execute(['TAP1']);
    $responses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Responses found (by site_id 'TAP1'):</h3>";
    echo "<pre>";
    print_r($responses);
    echo "</pre>";
    
    // Check if customer_id is NULL
    if ($site['customer_id'] === null) {
        echo "<p style='color: red;'><strong>WARNING:</strong> This site has customer_id = NULL! This is why responses aren't showing in the customer filter.</p>";
        
        // Show all customers
        $stmt = $db->query("SELECT id, name FROM customers WHERE status = 'active' ORDER BY name");
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<h3>Available Customers:</h3>";
        echo "<pre>";
        print_r($customers);
        echo "</pre>";
    }
}

// Get total count of all responses
$stmt = $db->query("SELECT COUNT(*) as total FROM dynamic_survey_responses");
$total = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<h3>Total responses in database: " . $total['total'] . "</h3>";

// Get responses with NULL customer
$stmt = $db->query("
    SELECT sr.id, sr.site_id, s.site_id as site_code, s.customer_id
    FROM dynamic_survey_responses sr
    LEFT JOIN sites s ON sr.site_id = s.id
    WHERE s.customer_id IS NULL
");
$nullCustomer = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Responses with NULL customer_id: " . count($nullCustomer) . "</h3>";
if (count($nullCustomer) > 0) {
    echo "<pre>";
    print_r($nullCustomer);
    echo "</pre>";
}
