<?php
session_start();
require_once dirname(__DIR__) . '/configDatabase.php';
require_once __DIR__ . '/google-calendar-helper.php';

echo "<h1>Google Calendar Refresh Token Debug</h1>";

// Check if user is logged in and is admin
if (!isset($_SESSION['id']) || !isset($_SESSION['type']) || $_SESSION['type'] != 1) {
    echo "<p style='color: red;'>❌ Access denied. Admin privileges required.</p>";
    exit;
}

$userId = $_SESSION['id'];
echo "<p><strong>User ID:</strong> $userId</p>";

// Get current token status
$googleHelper = new GoogleCalendarHelper();
$tokenStatus = $googleHelper->getCurrentTokenStatus();

echo "<h2>Current Token Status:</h2>";
echo "<pre>";
print_r($tokenStatus);
echo "</pre>";

if ($tokenStatus['hasToken']) {
    echo "<h2>Testing Token Refresh:</h2>";
    
    try {
        $refreshResult = $googleHelper->refreshTokenIfNeeded();
        echo "<p><strong>Refresh Result:</strong> " . ($refreshResult ? "✅ Success" : "❌ Failed") . "</p>";
        
        // Check token validity
        $isValid = $googleHelper->isTokenValid();
        echo "<p><strong>Token Valid:</strong> " . ($isValid ? "✅ Yes" : "❌ No") . "</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ No token found in database</p>";
}

// Show connection status
echo "<h2>Connection Status:</h2>";
$connectionStatus = $googleHelper->getConnectionStatus();
echo "<pre>";
print_r($connectionStatus);
echo "</pre>";

// Show recent error logs
echo "<h2>Recent Error Logs:</h2>";
$errorLog = shell_exec("tail -20 " . dirname(__DIR__) . "/error_log | grep -i google");
echo "<pre>" . htmlspecialchars($errorLog) . "</pre>";
?> 