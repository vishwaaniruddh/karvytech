<?php
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

// Find sites with NULL customer_id that have survey responses
$stmt = $db->query("
    SELECT DISTINCT s.id, s.site_id, s.store_id, s.customer as customer_name,
           COUNT(sr.id) as response_count
    FROM sites s
    INNER JOIN dynamic_survey_responses sr ON s.id = sr.site_id
    WHERE s.customer_id IS NULL
    GROUP BY s.id
    ORDER BY response_count DESC
");
$sitesWithNullCustomer = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Sites with NULL customer_id that have survey responses</h2>";
echo "<p>These sites need to be assigned to a customer to show their responses in the customer filter.</p>";

if (count($sitesWithNullCustomer) > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Site ID</th><th>Store ID</th><th>Customer Name (text)</th><th>Response Count</th><th>Action</th></tr>";
    
    foreach ($sitesWithNullCustomer as $site) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($site['site_id']) . "</td>";
        echo "<td>" . htmlspecialchars($site['store_id']) . "</td>";
        echo "<td>" . htmlspecialchars($site['customer_name']) . "</td>";
        echo "<td>" . $site['response_count'] . "</td>";
        echo "<td>";
        
        // Try to find matching customer
        if (!empty($site['customer_name'])) {
            $stmt = $db->prepare("SELECT id, name FROM customers WHERE name LIKE ? AND status = 'active'");
            $stmt->execute(['%' . $site['customer_name'] . '%']);
            $matchingCustomers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($matchingCustomers) > 0) {
                echo "<form method='POST' style='display:inline;'>";
                echo "<input type='hidden' name='site_id' value='" . $site['id'] . "'>";
                echo "<select name='customer_id'>";
                foreach ($matchingCustomers as $customer) {
                    echo "<option value='" . $customer['id'] . "'>" . htmlspecialchars($customer['name']) . "</option>";
                }
                echo "</select>";
                echo " <button type='submit' name='assign'>Assign</button>";
                echo "</form>";
            } else {
                echo "No matching customer found";
            }
        } else {
            echo "No customer name in site";
        }
        
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p style='color: green;'>All sites with survey responses have customer_id assigned!</p>";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign'])) {
    $siteId = $_POST['site_id'];
    $customerId = $_POST['customer_id'];
    
    $stmt = $db->prepare("UPDATE sites SET customer_id = ? WHERE id = ?");
    if ($stmt->execute([$customerId, $siteId])) {
        echo "<p style='color: green; font-weight: bold;'>✓ Site updated successfully! Refresh the page to see updated list.</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to update site</p>";
    }
}

// Show all customers for reference
echo "<h3>All Active Customers:</h3>";
$stmt = $db->query("SELECT id, name FROM customers WHERE status = 'active' ORDER BY name");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<ul>";
foreach ($customers as $customer) {
    echo "<li>ID: " . $customer['id'] . " - " . htmlspecialchars($customer['name']) . "</li>";
}
echo "</ul>";
