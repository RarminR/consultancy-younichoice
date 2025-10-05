<?php
session_start();
require_once dirname(__DIR__) . '/configDatabase.php';

echo "<h1>Google Calendar Tokens Check</h1>";

// Check if user is logged in and is admin
if (!isset($_SESSION['id']) || !isset($_SESSION['type']) || $_SESSION['type'] != 1) {
    echo "<p style='color: red;'>❌ Access denied. Admin privileges required.</p>";
    exit;
}

$userId = $_SESSION['id'];
echo "<p><strong>User ID:</strong> $userId</p>";

try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT * FROM user_google_tokens WHERE userId = ?");
    $stmt->execute([$userId]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "<h2>Token Information:</h2>";
        echo "<pre>";
        print_r($result);
        echo "</pre>";
        
        $isExpired = time() >= $result['expiresAt'];
        $hasRefreshToken = !empty($result['refreshToken']);
        
        echo "<h2>Status:</h2>";
        echo "<p><strong>Has Access Token:</strong> " . (!empty($result['accessToken']) ? "✅ Yes" : "❌ No") . "</p>";
        echo "<p><strong>Has Refresh Token:</strong> " . ($hasRefreshToken ? "✅ Yes" : "❌ No") . "</p>";
        echo "<p><strong>Is Expired:</strong> " . ($isExpired ? "❌ Yes" : "✅ No") . "</p>";
        echo "<p><strong>Expires At:</strong> " . date('Y-m-d H:i:s', $result['expiresAt']) . " (Unix: " . $result['expiresAt'] . ")</p>";
        echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . " (Unix: " . time() . ")</p>";
        
        if ($isExpired && $hasRefreshToken) {
            echo "<p style='color: orange;'>⚠️ Token is expired but has refresh token - should be able to refresh</p>";
        } elseif ($isExpired && !$hasRefreshToken) {
            echo "<p style='color: red;'>❌ Token is expired and no refresh token - needs re-authentication</p>";
        }
        
    } else {
        echo "<p style='color: orange;'>⚠️ No tokens found for user $userId</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Database Error:</strong> " . $e->getMessage() . "</p>";
}
?> 