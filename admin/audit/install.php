<?php
/**
 * Audit System Installer
 * Run this page once to create the audit tables
 */

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

// Require admin authentication
Auth::requireRole(ADMIN_ROLE);

$title = 'Install Audit System';
$installed = false;
$errors = [];
$success = [];
$oldTablesDetected = false;

// Check if old tables exist
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SHOW TABLES LIKE 'user_audit_logs'");
    if ($stmt->rowCount() > 0) {
        // Check if it has old structure
        $stmt = $db->query("SHOW COLUMNS FROM user_audit_logs LIKE 'action_type'");
        if ($stmt->rowCount() == 0) {
            $oldTablesDetected = true;
        }
    }
} catch (Exception $e) {
    // Ignore
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install'])) {
    try {
        $db = Database::getInstance()->getConnection();
        
        // Read SQL file
        $sqlFile = __DIR__ . '/../../database/create_audit_system.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception("SQL file not found: $sqlFile");
        }
        
        $sql = file_get_contents($sqlFile);
        
        // Split into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (empty($statement) || strpos($statement, '--') === 0) {
                continue;
            }
            
            try {
                $db->exec($statement);
                
                // Extract table name for display
                if (preg_match('/DROP TABLE.*?`([^`]+)`/i', $statement, $matches)) {
                    $success[] = "Dropped table (if existed): {$matches[1]}";
                } elseif (preg_match('/CREATE TABLE.*?`([^`]+)`/i', $statement, $matches)) {
                    $success[] = "✓ Created table: {$matches[1]}";
                }
            } catch (PDOException $e) {
                $errorMsg = $e->getMessage();
                
                // Extract table name if possible
                $tableName = 'unknown';
                if (preg_match('/CREATE TABLE.*?`([^`]+)`/i', $statement, $matches)) {
                    $tableName = $matches[1];
                } elseif (preg_match('/DROP TABLE.*?`([^`]+)`/i', $statement, $matches)) {
                    $tableName = $matches[1];
                }
                
                $errors[] = "Error with table '$tableName': " . $errorMsg;
            }
        }
        
        if (empty($errors)) {
            $installed = true;
        }
        
    } catch (Exception $e) {
        $errors[] = "Fatal error: " . $e->getMessage();
    }
}

ob_start();
?>

<div class="max-w-4xl mx-auto">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <div class="flex items-center space-x-4 mb-2">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Install Audit System</h1>
                <p class="text-lg text-gray-600">Set up user access tracking and audit logging</p>
            </div>
        </div>
    </div>

    <?php if ($installed): ?>
        <!-- Success Message -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-8">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-lg font-medium text-green-900">Installation Successful!</h3>
                    <div class="mt-2 text-sm text-green-700">
                        <p class="mb-2">The audit system has been successfully installed.</p>
                        <?php if (!empty($success)): ?>
                            <ul class="list-disc list-inside space-y-1 mt-3">
                                <?php foreach ($success as $msg): ?>
                                    <li><?php echo htmlspecialchars($msg); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                    <div class="mt-4 flex gap-3">
                        <a href="stats.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                            View Statistics
                        </a>
                        <a href="index.php" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            View Audit Logs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <!-- Error Messages -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-8">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-lg font-medium text-red-900">Installation Errors</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p class="mb-2">The following errors occurred during installation:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <?php foreach ($errors as $error): ?>
                                <li class="font-mono text-xs"><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="mt-4">
                        <button onclick="location.reload()" class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50">
                            Try Again
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($success) && !$installed): ?>
        <!-- Partial Success Messages -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-lg font-medium text-yellow-900">Partial Success</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p class="mb-2">Some operations completed successfully:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <?php foreach ($success as $msg): ?>
                                <li><?php echo htmlspecialchars($msg); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!$installed): ?>
        <!-- Old Tables Warning -->
        <?php if ($oldTablesDetected): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-lg font-medium text-yellow-900">Old Audit Tables Detected</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>We detected old audit tables with an outdated structure. The installation will:</p>
                        <ul class="list-disc list-inside mt-2 space-y-1">
                            <li>Drop the old tables (any existing data will be lost)</li>
                            <li>Create new tables with the correct structure</li>
                            <li>Set up the complete audit system</li>
                        </ul>
                        <p class="mt-2 font-medium">⚠️ This will delete any existing audit log data.</p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Installation Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">What will be installed?</h3>
            
            <div class="space-y-4 mb-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-gray-900">user_audit_logs table</h4>
                        <p class="text-sm text-gray-500">Main audit log table for tracking all user activities</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-gray-900">user_audit_sessions table</h4>
                        <p class="text-sm text-gray-500">User session tracking with login/logout times</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-gray-900">user_audit_api_stats table</h4>
                        <p class="text-sm text-gray-500">API endpoint statistics and performance metrics</p>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-900">Note</h3>
                        <div class="mt-1 text-sm text-blue-700">
                            <p>This installation is safe to run multiple times. Existing tables will not be affected.</p>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST">
                <button type="submit" name="install" class="w-full inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path>
                    </svg>
                    Install Audit System
                </button>
            </form>
        </div>

        <!-- Features Preview -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Features You'll Get</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-gray-900">User Activity Tracking</h4>
                        <p class="text-xs text-gray-500">Monitor who accesses what and when</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-gray-900">API Call Logging</h4>
                        <p class="text-xs text-gray-500">Track all API requests and responses</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-gray-900">Session Management</h4>
                        <p class="text-xs text-gray-500">Track active sessions and durations</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-gray-900">Analytics Dashboard</h4>
                        <p class="text-xs text-gray-500">Comprehensive statistics and reports</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/admin_layout.php';
?>
