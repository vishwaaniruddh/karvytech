<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Setting up BOQ Items table...\n";
    
    // Read and execute the SQL file
    $sql = file_get_contents(__DIR__ . '/recreate_boq_items.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            try {
                $db->exec($statement);
                echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
            } catch (PDOException $e) {
                echo "✗ Error executing statement: " . $e->getMessage() . "\n";
                echo "Statement: " . substr($statement, 0, 100) . "...\n";
            }
        }
    }
    
    // Verify the setup
    echo "\n=== Verification ===\n";
    
    // Check table structure
    $stmt = $db->query("DESCRIBE boq_items");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Table structure:\n";
    foreach ($columns as $column) {
        echo "  - {$column['Field']} ({$column['Type']})\n";
    }
    
    // Check data count
    $stmt = $db->query("SELECT COUNT(*) as total FROM boq_items");
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "\nTotal BOQ items: {$total['total']}\n";
    
    // Check categories
    $stmt = $db->query("SELECT category, COUNT(*) as count FROM boq_items GROUP BY category ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nCategories:\n";
    foreach ($categories as $cat) {
        echo "  - {$cat['category']}: {$cat['count']} items\n";
    }
    
    // Test the BoqItem model
    echo "\n=== Testing BoqItem Model ===\n";
    require_once __DIR__ . '/../models/BoqItem.php';
    
    $boqModel = new BoqItem();
    
    // Test getActive method
    $activeItems = $boqModel->getActive();
    echo "Active items from model: " . count($activeItems) . "\n";
    
    // Test getCategories method
    $categories = $boqModel->getCategories();
    echo "Categories from model: " . implode(', ', $categories) . "\n";
    
    // Test search
    $searchResults = $boqModel->searchByName('camera', 5);
    echo "Search results for 'camera': " . count($searchResults) . " items\n";
    
    echo "\n✅ BOQ setup completed successfully!\n";
    echo "You can now access the BOQ management at: /admin/boq/\n";
    
} catch (Exception $e) {
    echo "❌ Error setting up BOQ: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>