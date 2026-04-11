<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Updating sites table with foreign key columns...\n";
    
    // Add foreign key columns to sites table
    $alterTableSQL = "
    ALTER TABLE sites 
    ADD COLUMN country_id INT NULL AFTER country,
    ADD COLUMN state_id INT NULL AFTER state,
    ADD COLUMN city_id INT NULL AFTER city,
    ADD COLUMN customer_id INT NULL AFTER customer,
    ADD COLUMN bank_id INT NULL AFTER bank,
    ADD INDEX idx_country_id (country_id),
    ADD INDEX idx_state_id (state_id),
    ADD INDEX idx_city_id (city_id),
    ADD INDEX idx_customer_id (customer_id),
    ADD INDEX idx_bank_id (bank_id),
    ADD FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE SET NULL,
    ADD FOREIGN KEY (state_id) REFERENCES states(id) ON DELETE SET NULL,
    ADD FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE SET NULL,
    ADD FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    ADD FOREIGN KEY (bank_id) REFERENCES banks(id) ON DELETE SET NULL
    ";
    
    $db->exec($alterTableSQL);
    echo "Foreign key columns added successfully.\n";
    
    // Update existing data to populate foreign key columns based on text values
    echo "Updating existing data with foreign key relationships...\n";
    
    // Update country_id based on country name
    $updateCountrySQL = "
    UPDATE sites s 
    JOIN countries c ON s.country = c.name 
    SET s.country_id = c.id 
    WHERE s.country IS NOT NULL AND s.country != ''
    ";
    $db->exec($updateCountrySQL);
    echo "Country foreign keys updated.\n";
    
    // Update state_id based on state name and country
    $updateStateSQL = "
    UPDATE sites s 
    JOIN states st ON s.state = st.name 
    JOIN countries c ON s.country = c.name AND st.country_id = c.id
    SET s.state_id = st.id 
    WHERE s.state IS NOT NULL AND s.state != ''
    ";
    $db->exec($updateStateSQL);
    echo "State foreign keys updated.\n";
    
    // Update city_id based on city name and state
    $updateCitySQL = "
    UPDATE sites s 
    JOIN cities ct ON s.city = ct.name 
    JOIN states st ON s.state = st.name AND ct.state_id = st.id
    SET s.city_id = ct.id 
    WHERE s.city IS NOT NULL AND s.city != ''
    ";
    $db->exec($updateCitySQL);
    echo "City foreign keys updated.\n";
    
    // Update customer_id based on customer name
    $updateCustomerSQL = "
    UPDATE sites s 
    JOIN customers cu ON s.customer = cu.name 
    SET s.customer_id = cu.id 
    WHERE s.customer IS NOT NULL AND s.customer != ''
    ";
    $db->exec($updateCustomerSQL);
    echo "Customer foreign keys updated.\n";
    
    // Update bank_id based on bank name
    $updateBankSQL = "
    UPDATE sites s 
    JOIN banks b ON s.bank = b.name 
    SET s.bank_id = b.id 
    WHERE s.bank IS NOT NULL AND s.bank != ''
    ";
    $db->exec($updateBankSQL);
    echo "Bank foreign keys updated.\n";
    
    echo "Sites table foreign key migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error updating sites table: " . $e->getMessage() . "\n";
    exit(1);
}
?>