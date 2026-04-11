<?php
/**
 * Environment Switcher
 * Simple script to switch between environments
 */

require_once __DIR__ . '/env.php';

class EnvSwitcher {
    private $envFile;
    
    public function __construct($envFile = null) {
        $this->envFile = $envFile ?: __DIR__ . '/../.env';
    }
    
    /**
     * Switch to a specific environment
     */
    public function switchTo($environment) {
        $validEnvironments = ['development', 'testing', 'production'];
        
        if (!in_array($environment, $validEnvironments)) {
            throw new Exception("Invalid environment. Must be one of: " . implode(', ', $validEnvironments));
        }
        
        $this->updateEnvFile('APP_ENV', $environment);
        
        // Update debug mode based on environment
        $debug = $environment !== 'production' ? 'true' : 'false';
        $this->updateEnvFile('APP_DEBUG', $debug);
        
        return "Environment switched to: {$environment}";
    }
    
    /**
     * Get current environment
     */
    public function getCurrentEnvironment() {
        Env::load($this->envFile);
        return Env::get('APP_ENV', 'development');
    }
    
    /**
     * Update a specific environment variable in .env file
     */
    private function updateEnvFile($key, $value) {
        if (!file_exists($this->envFile)) {
            throw new Exception('.env file not found');
        }
        
        $content = file_get_contents($this->envFile);
        $lines = explode("\n", $content);
        $updated = false;
        
        foreach ($lines as &$line) {
            if (strpos($line, $key . '=') === 0) {
                $line = $key . '=' . $value;
                $updated = true;
                break;
            }
        }
        
        if (!$updated) {
            $lines[] = $key . '=' . $value;
        }
        
        file_put_contents($this->envFile, implode("\n", $lines));
    }
    
    /**
     * Show current configuration
     */
    public function showConfig() {
        Env::load($this->envFile);
        $dbConfig = Env::getDatabaseConfig();
        
        return [
            'environment' => Env::get('APP_ENV'),
            'debug' => Env::get('APP_DEBUG') ? 'enabled' : 'disabled',
            'database' => [
                'host' => $dbConfig['host'],
                'name' => $dbConfig['name'],
                'user' => $dbConfig['user']
            ],
            'app_url' => Env::get('APP_URL'),
            'upload_path' => Env::get('UPLOAD_PATH')
        ];
    }
}

// CLI usage
if (php_sapi_name() === 'cli') {
    $switcher = new EnvSwitcher();
    
    if ($argc < 2) {
        echo "Usage: php env-switcher.php [environment|status]\n";
        echo "Environments: development, testing, production\n";
        echo "Status: status (shows current configuration)\n";
        exit(1);
    }
    
    $command = $argv[1];
    
    try {
        if ($command === 'status') {
            $config = $switcher->showConfig();
            echo "Current Configuration:\n";
            echo "Environment: " . $config['environment'] . "\n";
            echo "Debug: " . $config['debug'] . "\n";
            echo "Database: " . $config['database']['name'] . " on " . $config['database']['host'] . "\n";
            echo "App URL: " . $config['app_url'] . "\n";
        } else {
            $result = $switcher->switchTo($command);
            echo $result . "\n";
            
            // Show new configuration
            $config = $switcher->showConfig();
            echo "New Configuration:\n";
            echo "Environment: " . $config['environment'] . "\n";
            echo "Debug: " . $config['debug'] . "\n";
            echo "Database: " . $config['database']['name'] . " on " . $config['database']['host'] . "\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}
?>