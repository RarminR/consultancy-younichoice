<?php
session_start();
require_once dirname(__DIR__) . '/configDatabase.php';
require_once __DIR__ . '/google-calendar-helper.php';

echo "<h1>Force Token Refresh Test</h1>";

// Check if user is logged in and is admin
if (!isset($_SESSION['id']) || !isset($_SESSION['type']) || $_SESSION['type'] != 1) {
    echo "<p style='color: red;'>❌ Access denied. Admin privileges required.</p>";
    exit;
}

$userId = $_SESSION['id'];
echo "<p><strong>User ID:</strong> $userId</p>";

$googleHelper = new GoogleCalendarHelper();

echo "<h2>Step 1: Check Current Token Status</h2>";
$tokenStatus = $googleHelper->getCurrentTokenStatus();
echo "<pre>";
print_r($tokenStatus);
echo "</pre>";

if ($tokenStatus['hasToken']) {
    echo "<h2>Step 2: Attempt Token Refresh</h2>";
    
    try {
        echo "<p>Attempting to refresh token...</p>";
        $refreshResult = $googleHelper->refreshTokenIfNeeded();
        echo "<p><strong>Refresh Result:</strong> " . ($refreshResult ? "✅ Success" : "❌ Failed") . "</p>";
        
        if ($refreshResult) {
            echo "<h2>Step 3: Check Token Validity After Refresh</h2>";
            $isValid = $googleHelper->isTokenValid();
            echo "<p><strong>Token Valid:</strong> " . ($isValid ? "✅ Yes" : "❌ No") . "</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'><strong>Error during refresh:</strong> " . $e->getMessage() . "</p>";
        echo "<p><strong>Error trace:</strong></p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ No token found - cannot test refresh</p>";
}

echo "<h2>Step 4: Final Connection Status</h2>";
$connectionStatus = $googleHelper->getConnectionStatus();
echo "<pre>";
print_r($connectionStatus);
echo "</pre>";
?> 