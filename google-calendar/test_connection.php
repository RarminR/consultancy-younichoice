<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['type']) || $_SESSION['type'] != 1) {
    echo "<div style='color: red; font-family: Arial, sans-serif; padding: 20px;'>❌ Access denied. Admin privileges required.</div>";
    exit;
}

/**
 * Simple Google Calendar Connection Test
 * This page tests the basic Google Calendar connection
 */

require_once dirname(__DIR__) . '/configDatabase.php';
require_once __DIR__ . '/google-calendar-helper.php';

echo "<h1>Google Calendar Connection Test</h1>\n";

// Check if there are any stored tokens
$sqlTokens = "SELECT userId, expiresAt FROM user_google_tokens ORDER BY createdAt DESC LIMIT 5";
$result = mysqli_query($link, $sqlTokens);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<h2>Stored Google Tokens</h2>\n";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>User ID</th><th>Expires At</th><th>Status</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        $expiresAt = $row['expiresAt'];
        $isExpired = (time() >= $expiresAt);
        $status = $isExpired ? 'EXPIRED' : 'VALID';
        $color = $isExpired ? 'red' : 'green';
        
        echo "<tr>";
        echo "<td>" . $row['userId'] . "</td>";
        echo "<td>" . date('Y-m-d H:i:s', $expiresAt) . "</td>";
        echo "<td style='color: $color;'>$status</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test with the first valid token
    $result = mysqli_query($link, $sqlTokens);
    while ($row = mysqli_fetch_assoc($result)) {
        $expiresAt = $row['expiresAt'];
        if (time() < $expiresAt) {
            echo "<h2>Testing with User ID: " . $row['userId'] . "</h2>\n";
            
            // Get the full token data
            $userId = $row['userId'];
            $sqlToken = "SELECT accessToken FROM user_google_tokens WHERE userId = '$userId'";
            $tokenResult = mysqli_query($link, $sqlToken);
            $tokenData = mysqli_fetch_assoc($tokenResult);
            
            try {
                $googleHelper = new GoogleCalendarHelper($tokenData['accessToken']);
                
                if ($googleHelper->isTokenValid()) {
                    echo "<div style='color: green;'>✅ Google Calendar API connection successful</div>\n";
                    
                    // Test creating a simple event
                    $testData = [
                        'studentName' => 'Test Student',
                        'consultantName' => 'Test Consultant',
                        'studentSchool' => 'Test School',
                        'meetingDate' => date('c', strtotime('+1 hour')),
                        'meetingTopic' => 'Test Meeting',
                        'meetingActivities' => 'Test Activities',
                        'meetingNotes' => 'This is a test meeting.',
                        'consultantEmail' => 'test@example.com',
                        'studentEmail' => 'student@example.com'
                    ];
                    
                    $result = $googleHelper->createMeetingEvent($testData);
                    
                    if ($result['success']) {
                        echo "<div style='color: green;'>✅ Test event created successfully!</div>\n";
                        echo "<p><strong>Event ID:</strong> " . $result['eventId'] . "</p>\n";
                        echo "<p><strong>Meet Link:</strong> <a href='" . $result['meetLink'] . "' target='_blank'>" . $result['meetLink'] . "</a></p>\n";
                        
                        // Clean up
                        $deleteResult = $googleHelper->deleteMeetingEvent($result['eventId']);
                        if ($deleteResult['success']) {
                            echo "<div style='color: green;'>✅ Test event deleted successfully</div>\n";
                        }
                    } else {
                        echo "<div style='color: red;'>❌ Failed to create test event: " . $result['error'] . "</div>\n";
                    }
                } else {
                    echo "<div style='color: red;'>❌ Google Calendar API connection failed</div>\n";
                }
            } catch (Exception $e) {
                echo "<div style='color: red;'>❌ Error: " . $e->getMessage() . "</div>\n";
            }
            
            break; // Only test with the first valid token
        }
    }
} else {
    echo "<div style='color: orange;'>⚠️ No Google tokens found in database</div>\n";
    echo "<p>Please connect Google Calendar first by going to the Add Meeting page.</p>\n";
}

echo "<h2>Configuration Check</h2>\n";
require_once __DIR__ . '/google-calendar-config.php';

if (defined('GOOGLE_CLIENT_ID') && GOOGLE_CLIENT_ID !== 'your-client-id-here') {
    echo "<div style='color: green;'>✅ Google Client ID configured</div>\n";
} else {
    echo "<div style='color: red;'>❌ Google Client ID not configured</div>\n";
}

if (defined('GOOGLE_CLIENT_SECRET') && GOOGLE_CLIENT_SECRET !== 'your-client-secret-here') {
    echo "<div style='color: green;'>✅ Google Client Secret configured</div>\n";
} else {
    echo "<div style='color: red;'>❌ Google Client Secret not configured</div>\n";
}

echo "<p><a href='../addMeeting.php?studentId=1'>Go to Add Meeting Page</a></p>\n";
?> 