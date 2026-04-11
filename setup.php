<?php
/**
 * Project Setup Script
 * Simple setup wizard for initial configuration
 */

echo "=== Site Installation Management System Setup ===\n\n";

// Check if .env file exists
if (!file_exists('.env')) {
    echo "Creating .env file from template...\n";
    if (file_exists('.env.example')) {
        copy('.env.example', '.env');
        echo "✓ .env file created. Please edit it with your configuration.\n\n";
    } else {
        echo "✗ .env.example not found. Please create .env file manually.\n\n";
    }
} else {
    echo "✓ .env file already exists.\n\n";
}

// Load environment
require_once 'config/env.php';
try {
    Env::load();
    echo "✓ Environment configuration loaded successfully.\n";
    echo "Current environment: " . Env::get('APP_ENV', 'development') . "\n\n";
} catch (Exception $e) {
    echo "✗ Error loading environment: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test database connection
echo "Testing database connection...\n";
try {
    require_once 'config/database.php';
    $db = Database::getInstance();
    
    if ($db->testConnection()) {
        echo "✓ Database connection successful.\n";
        $info = $db->getInfo();
        echo "Connected to: " . $info['database'] . " on " . $info['host'] . "\n\n";
    } else {
        echo "✗ Database connection failed.\n\n";
    }
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n\n";
}

// Check required directories
echo "Checking required directories...\n";
$requiredDirs = [
    'logs',
    'assets/uploads',
    'assets/installation_progress',
    'uploads'
];

foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "✓ Created directory: $dir\n";
        } else {
            echo "✗ Failed to create directory: $dir\n";
        }
    } else {
        echo "✓ Directory exists: $dir\n";
    }
}

echo "\n=== Setup Complete ===\n";
echo "Next steps:\n";
echo "1. Edit .env file with your database credentials\n";
echo "2. Run database migrations if needed\n";
echo "3. Configure web server to point to this directory\n";
echo "4. Access the application via web browser\n\n";

echo "Environment management:\n";
echo "- Switch to development: php config/env-switcher.php development\n";
echo "- Switch to production: php config/env-switcher.php production\n";
echo "- Check status: php config/env-switcher.php status\n\n";
?>