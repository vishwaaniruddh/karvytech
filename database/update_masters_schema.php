<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Creating master tables...\n";
    
    // Create countries table
    $db->exec("
        CREATE TABLE IF NOT EXISTS countries (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL UNIQUE,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_name (name),
            INDEX idx_status (status)
        )
    ");
    echo "✓ Created countries table\n";
    
    // Create zones table
    $db->exec("
        CREATE TABLE IF NOT EXISTS zones (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL UNIQUE,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_name (name),
            INDEX idx_status (status)
        )
    ");
    echo "✓ Created zones table\n";
    
    // Create states table
    $db->exec("
        CREATE TABLE IF NOT EXISTS states (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            country_id INT NOT NULL,
            zone_id INT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE RESTRICT,
            FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE SET NULL,
            INDEX idx_name (name),
            INDEX idx_country (country_id),
            INDEX idx_zone (zone_id),
            INDEX idx_status (status),
            UNIQUE KEY unique_state_country (name, country_id)
        )
    ");
    echo "✓ Created states table\n";
    
    // Create cities table
    $db->exec("
        CREATE TABLE IF NOT EXISTS cities (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            state_id INT NOT NULL,
            country_id INT NOT NULL,
            zone_id INT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (state_id) REFERENCES states(id) ON DELETE RESTRICT,
            FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE RESTRICT,
            FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE SET NULL,
            INDEX idx_name (name),
            INDEX idx_state (state_id),
            INDEX idx_country (country_id),
            INDEX idx_zone (zone_id),
            INDEX idx_status (status),
            UNIQUE KEY unique_city_state (name, state_id)
        )
    ");
    echo "✓ Created cities table\n";
    
    // Create banks table
    $db->exec("
        CREATE TABLE IF NOT EXISTS banks (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(200) NOT NULL UNIQUE,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_name (name),
            INDEX idx_status (status)
        )
    ");
    echo "✓ Created banks table\n";
    
    // Create customers table
    $db->exec("
        CREATE TABLE IF NOT EXISTS customers (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(200) NOT NULL UNIQUE,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_name (name),
            INDEX idx_status (status)
        )
    ");
    echo "✓ Created customers table\n";
    
    // Insert sample data only if tables are empty
    $stmt = $db->query("SELECT COUNT(*) FROM countries");
    if ($stmt->fetchColumn() == 0) {
        echo "Inserting sample data...\n";
        
        // Countries
        $db->exec("INSERT INTO countries (name) VALUES ('India'), ('United States'), ('United Kingdom'), ('Canada'), ('Australia')");
        echo "✓ Inserted countries\n";
        
        // Zones
        $db->exec("INSERT INTO zones (name) VALUES ('North Zone'), ('South Zone'), ('East Zone'), ('West Zone'), ('Central Zone'), ('Northeast Zone')");
        echo "✓ Inserted zones\n";
        
        // States
        $db->exec("
            INSERT INTO states (name, country_id, zone_id) VALUES 
            ('Maharashtra', 1, 4), ('Karnataka', 1, 2), ('Tamil Nadu', 1, 2), ('Delhi', 1, 1), ('Gujarat', 1, 4),
            ('Rajasthan', 1, 1), ('West Bengal', 1, 3), ('Uttar Pradesh', 1, 1), ('Madhya Pradesh', 1, 5), ('Kerala', 1, 2)
        ");
        echo "✓ Inserted states\n";
        
        // Cities
        $db->exec("
            INSERT INTO cities (name, state_id, country_id, zone_id) VALUES 
            ('Mumbai', 1, 1, 4), ('Pune', 1, 1, 4), ('Nashik', 1, 1, 4),
            ('Bangalore', 2, 1, 2), ('Mysore', 2, 1, 2),
            ('Chennai', 3, 1, 2), ('Coimbatore', 3, 1, 2),
            ('New Delhi', 4, 1, 1), ('Ahmedabad', 5, 1, 4), ('Surat', 5, 1, 4),
            ('Jaipur', 6, 1, 1), ('Kolkata', 7, 1, 3), ('Lucknow', 8, 1, 1),
            ('Bhopal', 9, 1, 5), ('Kochi', 10, 1, 2)
        ");
        echo "✓ Inserted cities\n";
        
        // Banks
        $db->exec("
            INSERT INTO banks (name) VALUES 
            ('State Bank of India'), ('HDFC Bank'), ('ICICI Bank'), ('Axis Bank'), ('Punjab National Bank'),
            ('Bank of Baroda'), ('Canara Bank'), ('Union Bank of India'), ('Indian Bank'), ('Central Bank of India'),
            ('IDFC First Bank'), ('IndusInd Bank'), ('Kotak Mahindra Bank'), ('Yes Bank'), ('Federal Bank')
        ");
        echo "✓ Inserted banks\n";
        
        // Customers
        $db->exec("
            INSERT INTO customers (name) VALUES 
            ('Reliance Industries Ltd'), ('Tata Consultancy Services'), ('Infosys Limited'), ('Wipro Limited'), ('HCL Technologies'),
            ('Tech Mahindra'), ('Larsen & Toubro'), ('ITC Limited'), ('Bharti Airtel'), ('Maruti Suzuki India'),
            ('Asian Paints'), ('Titan Company'), ('UltraTech Cement'), ('Bajaj Finance'), ('HDFC Life Insurance')
        ");
        echo "✓ Inserted customers\n";
    } else {
        echo "Sample data already exists, skipping...\n";
    }
    
    echo "\nMaster tables created successfully!\n";
    
} catch (Exception $e) {
    echo "Error creating master tables: " . $e->getMessage() . "\n";
}
?>