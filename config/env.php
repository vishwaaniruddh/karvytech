<?php
/**
 * Environment Configuration Loader
 * Simple yet powerful environment management
 */

class Env {
    private static $loaded = false;
    private static $variables = [];
    
    /**
     * Load environment variables from .env file
     */
    public static function load($path = null) {
        if (self::$loaded) {
            return;
        }
        
        $envFile = $path ?: __DIR__ . '/../.env';
        
        if (!file_exists($envFile)) {
            throw new Exception('.env file not found at: ' . $envFile);
        }
        
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse key=value pairs
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if (preg_match('/^"(.*)"$/', $value, $matches)) {
                    $value = $matches[1];
                } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
                    $value = $matches[1];
                }
                
                // Convert boolean strings
                if (strtolower($value) === 'true') {
                    $value = true;
                } elseif (strtolower($value) === 'false') {
                    $value = false;
                } elseif (is_numeric($value)) {
                    $value = is_float($value + 0) ? (float)$value : (int)$value;
                }
                
                self::$variables[$key] = $value;
                
                // Also set as PHP environment variable
                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
        
        self::$loaded = true;
    }
    
    /**
     * Get environment variable with optional default
     */
    public static function get($key, $default = null) {
        if (!self::$loaded) {
            self::load();
        }
        
        // Check our loaded variables first
        if (array_key_exists($key, self::$variables)) {
            return self::$variables[$key];
        }
        
        // Check PHP environment variables
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        // Check $_ENV superglobal
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }
        
        return $default;
    }
    
    /**
     * Check if we're in a specific environment
     */
    public static function is($environment) {
        return self::get('APP_ENV') === $environment;
    }
    
    /**
     * Check if we're in development mode
     */
    public static function isDevelopment() {
        return self::is('development');
    }
    
    /**
     * Check if we're in testing mode
     */
    public static function isTesting() {
        return self::is('testing');
    }
    
    /**
     * Check if we're in production mode
     */
    public static function isProduction() {
        return self::is('production');
    }
    
    /**
     * Get database configuration for current environment
     */
    public static function getDatabaseConfig() {
        $env = self::get('APP_ENV', 'development');
        $prefix = strtoupper($env === 'development' ? 'DEV' : ($env === 'testing' ? 'TEST' : 'PROD'));
        
        return [
            'host' => self::get("{$prefix}_DB_HOST", 'localhost'),
            'name' => self::get("{$prefix}_DB_NAME"),
            'user' => self::get("{$prefix}_DB_USER"),
            'pass' => self::get("{$prefix}_DB_PASS"),
            'charset' => self::get("{$prefix}_DB_CHARSET", 'utf8mb4')
        ];
    }
    
    /**
     * Get all loaded variables (for debugging)
     */
    public static function all() {
        if (!self::$loaded) {
            self::load();
        }
        return self::$variables;
    }
}
?>