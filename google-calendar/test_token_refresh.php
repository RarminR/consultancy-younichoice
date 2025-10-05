<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['type']) || $_SESSION['type'] != 1) {
    echo "<div style='color: red; font-family: Arial, sans-serif; padding: 20px;'>❌ Access denied. Admin privileges required.</div>";
    exit;
}

require_once dirname(__DIR__) . '/configDatabase.php';
require_once __DIR__ . '/google-calendar-helper.php';

echo "<h1>🔐 Google Calendar Token Status & Refresh Test</h1>";

// Get user ID (default to 1 if not in session)
$userId = $_SESSION['id'] ?? 1;

echo "<h2>👤 User ID: $userId</h2>";

try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get stored token
    $stmt = $pdo->prepare("SELECT * FROM user_google_tokens WHERE userId = ?");
    $stmt->execute([$userId]);
    $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$tokenData) {
        echo "<div style='background: #ffebee; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "❌ <strong>No Google Calendar token found for user $userId</strong><br>";
        echo "Please connect to Google Calendar first.";
        echo "</div>";
        echo "<p><a href='../addMeeting.php' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Back to Add Meeting</a></p>";
        exit;
    }
    
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "✅ <strong>Google Calendar token found!</strong>";
    echo "</div>";
    
    // Create helper with stored token
    $accessToken = [
        'access_token' => $tokenData['accessToken'],
        'refresh_token' => $tokenData['refreshToken'],
        'expires_in' => $tokenData['expiresAt'] - $tokenData['createdAt'],
        'created' => strtotime($tokenData['createdAt'])
    ];
    
    $googleHelper = new GoogleCalendarHelper($accessToken);
    
    // Get token status
    $tokenStatus = $googleHelper->getTokenStatus();
    
    echo "<h3>📊 Token Status:</h3>";
    echo "<div style='background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>Status:</strong> " . $tokenStatus['message'] . "<br>";
    echo "<strong>Has Token:</strong> " . ($tokenStatus['hasToken'] ? 'Yes' : 'No') . "<br>";
    echo "<strong>Is Expired:</strong> " . ($tokenStatus['isExpired'] ? 'Yes' : 'No') . "<br>";
    echo "<strong>Expires Soon:</strong> " . ($tokenStatus['isExpiringSoon'] ? 'Yes' : 'No') . "<br>";
    echo "<strong>Expires In:</strong> " . $tokenStatus['expiresIn'] . " seconds<br>";
    echo "<strong>Created:</strong> " . date('Y-m-d H:i:s', $tokenStatus['created']) . "<br>";
    echo "</div>";
    
    // Calculate time until expiration
    if (isset($tokenData['createdAt']) && isset($tokenData['expiresAt'])) {
        $expiresAt = strtotime($tokenData['expiresAt']);
        $timeUntilExpiry = $expiresAt - time();
        
        if ($timeUntilExpiry > 0) {
            $hours = floor($timeUntilExpiry / 3600);
            $minutes = floor(($timeUntilExpiry % 3600) / 60);
            $seconds = $timeUntilExpiry % 60;
            
            echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "⏰ <strong>Token expires in:</strong> $hours hours, $minutes minutes, $seconds seconds";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "⚠️ <strong>Token has expired!</strong>";
            echo "</div>";
        }
    }
    
    // Test token refresh
    echo "<h3>🔄 Testing Token Refresh:</h3>";
    
    if ($tokenStatus['isExpired'] || $tokenStatus['isExpiringSoon']) {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "🔄 <strong>Attempting to refresh token...</strong><br>";
        
        $refreshResult = $googleHelper->refreshTokenIfNeeded();
        
        if ($refreshResult) {
            echo "✅ <strong>Token refreshed successfully!</strong><br>";
            echo "You should now be able to create meetings without reconnecting.";
        } else {
            echo "❌ <strong>Token refresh failed!</strong><br>";
            echo "You may need to reconnect to Google Calendar.";
        }
        echo "</div>";
    } else {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "✅ <strong>Token is still valid!</strong><br>";
        echo "No refresh needed at this time.";
        echo "</div>";
    }
    
    // Test API connection
    echo "<h3>🌐 Testing API Connection:</h3>";
    
    if ($googleHelper->isTokenValid()) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "✅ <strong>API connection successful!</strong><br>";
        echo "You can create Google Calendar events.";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "❌ <strong>API connection failed!</strong><br>";
        echo "You may need to reconnect to Google Calendar.";
        echo "</div>";
    }
    
    // Show how long users can stay connected
    echo "<h3>⏰ Connection Duration Info:</h3>";
    echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>How long can you stay connected?</strong><br><br>";
    echo "✅ <strong>Access Token:</strong> 1 hour (automatically refreshed)<br>";
    echo "✅ <strong>Refresh Token:</strong> Up to 6 months (if not revoked)<br>";
    echo "✅ <strong>Automatic Refresh:</strong> Happens 5 minutes before expiration<br>";
    echo "✅ <strong>No Manual Reconnection:</strong> Required unless token is revoked<br><br>";
    echo "<strong>Note:</strong> With this new system, you should only need to reconnect if:<br>";
    echo "• You manually revoke access in your Google Account<br>";
    echo "• You don't use the system for 6+ months<br>";
    echo "• Google changes their security policies";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "❌ <strong>Error:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<br><div style='text-align: center;'>";
echo "<a href='../addMeeting.php' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>← Back to Add Meeting</a>";
echo "<a href='index.php' style='background: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📁 Google Calendar Files</a>";
echo "</div>";
?> 