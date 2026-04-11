<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Setting up inventory management tables...\n";
    
    // Read and execute the SQL file
    $sql = file_get_contents(__DIR__ . '/create_inventory_tables.sql');
    
    // Split SQL into individual statements
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            try {
                $db->exec($statement);
                echo "✓ Executed statement successfully\n";
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "✗ Error executing statement: " . $e->getMessage() . "\n";
                    echo "Statement: " . substr($statement, 0, 100) . "...\n";
                } else {
                    echo "✓ Table already exists, skipping\n";
                }
            }
        }
    }
    
    // Update calculated fields
    echo "\nUpdating calculated fields...\n";
    
    // Update available stock
    $db->exec("UPDATE inventory_stock SET available_stock = current_stock - reserved_stock");
    echo "✓ Updated available stock calculations\n";
    
    // Update total values
    $db->exec("UPDATE inventory_stock SET total_value = current_stock * unit_cost");
    echo "✓ Updated total value calculations\n";
    
    // Update inward item total costs
    $db->exec("UPDATE inventory_inward_items SET total_cost = quantity_received * unit_cost");
    echo "✓ Updated inward item total costs\n";
    
    // Update dispatch item total costs
    $db->exec("UPDATE inventory_dispatch_items SET total_cost = quantity_dispatched * unit_cost");
    echo "✓ Updated dispatch item total costs\n";
    
    // Update movement total values
    $db->exec("UPDATE inventory_movements SET total_value = quantity * unit_cost");
    echo "✓ Updated movement total values\n";
    
    // Update reconciliation differences
    $db->exec("UPDATE inventory_reconciliation_items SET 
               difference_quantity = physical_quantity - system_quantity,
               value_difference = (physical_quantity - system_quantity) * unit_cost");
    echo "✓ Updated reconciliation differences\n";
    
    echo "\n✓ Inventory system tables setup completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Error setting up inventory system: " . $e->getMessage() . "\n";
}
?>