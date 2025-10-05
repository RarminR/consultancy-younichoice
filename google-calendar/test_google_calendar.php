<?php
/**
 * Google Calendar Integration Test Script
 * This script tests the Google Calendar integration functionality
 */

session_start();
require_once dirname(__DIR__) . '/configDatabase.php';
require_once __DIR__ . '/google-calendar-helper.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['type']) || $_SESSION['type'] != 1) {
    echo "<h1>Google Calendar Integration Test</h1>";
    echo "<div style='color: red;'>❌ Access denied. Admin privileges required.</div>";
    exit;
}

$userId = $_SESSION['id'];
$userType = $_SESSION['type'];

echo "<h1>Google Calendar Integration Test</h1>";
echo "<p><strong>User ID:</strong> $userId</p>";
echo "<p><strong>User Type:</strong> $userType</p>";

// Test 1: Check if Google Calendar config exists
echo "<h2>Test 1: Configuration</h2>";
if (file_exists('google-calendar-config.php')) {
    echo "<div style='color: green;'>✅ Google Calendar config file exists</div>";
    
    // Check if credentials are set
    require_once __DIR__ . '/google-calendar-config.php';
    if (GOOGLE_CLIENT_ID !== 'YOUR_GOOGLE_CLIENT_ID') {
        echo "<div style='color: green;'>✅ Google Client ID is configured</div>";
    } else {
        echo "<div style='color: red;'>❌ Google Client ID not configured</div>";
    }
    
    if (GOOGLE_CLIENT_SECRET !== 'YOUR_GOOGLE_CLIENT_SECRET') {
        echo "<div style='color: green;'>✅ Google Client Secret is configured</div>";
    } else {
        echo "<div style='color: red;'>❌ Google Client Secret not configured</div>";
    }
} else {
    echo "<div style='color: red;'>❌ Google Calendar config file not found</div>";
}

// Test 2: Check database tables
echo "<h2>Test 2: Database Tables</h2>";
$sqlCheckTokensTable = "SHOW TABLES LIKE 'user_google_tokens'";
$result = mysqli_query($link, $sqlCheckTokensTable);

if (mysqli_num_rows($result) > 0) {
    echo "<div style='color: green;'>✅ user_google_tokens table exists</div>";
} else {
    echo "<div style='color: red;'>❌ user_google_tokens table not found</div>";
}

$sqlCheckMeetingsColumns = "SHOW COLUMNS FROM meetings LIKE 'googleEventId'";
$result = mysqli_query($link, $sqlCheckMeetingsColumns);

if (mysqli_num_rows($result) > 0) {
    echo "<div style='color: green;'>✅ Google Calendar columns exist in meetings table</div>";
} else {
    echo "<div style='color: red;'>❌ Google Calendar columns not found in meetings table</div>";
}

// Test 3: Check user's Google token
echo "<h2>Test 3: User Google Token</h2>";
$sqlToken = "SELECT accessToken, expiresAt FROM user_google_tokens WHERE userId = '$userId'";
$queryToken = mysqli_query($link, $sqlToken);

if ($queryToken && mysqli_num_rows($queryToken) > 0) {
    $tokenData = mysqli_fetch_assoc($queryToken);
    $expiresAt = $tokenData['expiresAt'];
    $isExpired = (time() >= $expiresAt);
    
    echo "<div style='color: green;'>✅ Google token found for user</div>";
    echo "<p><strong>Token expires:</strong> " . date('Y-m-d H:i:s', $expiresAt) . "</p>";
    echo "<p><strong>Current time:</strong> " . date('Y-m-d H:i:s') . "</p>";
    
    if ($isExpired) {
        echo "<div style='color: orange;'>⚠️ Token has expired</div>";
    } else {
        echo "<div style='color: green;'>✅ Token is valid</div>";
        
        // Test 4: Test Google Calendar API connection
        echo "<h2>Test 4: Google Calendar API Connection</h2>";
        try {
            $googleHelper = new GoogleCalendarHelper($tokenData['accessToken']);
            
            if ($googleHelper->isTokenValid()) {
                echo "<div style='color: green;'>✅ Google Calendar API connection successful</div>";
                
                // Test creating a sample event
                echo "<h2>Test 5: Create Sample Event</h2>";
                $testMeetingData = [
                    'studentName' => 'Test Student',
                    'consultantName' => 'Test Consultant',
                    'studentSchool' => 'Test School',
                    'meetingDate' => date('c', strtotime('+1 hour')),
                    'meetingTopic' => 'Test Meeting',
                    'meetingActivities' => 'Test Activities',
                    'meetingNotes' => 'This is a test meeting created by the integration test script.',
                    'consultantEmail' => 'test@example.com',
                    'studentEmail' => 'student@example.com'
                ];
                
                $result = $googleHelper->createMeetingEvent($testMeetingData);
                
                if ($result['success']) {
                    echo "<div style='color: green;'>✅ Sample event created successfully</div>";
                    echo "<p><strong>Event ID:</strong> " . $result['eventId'] . "</p>";
                    echo "<p><strong>Meet Link:</strong> <a href='" . $result['meetLink'] . "' target='_blank'>" . $result['meetLink'] . "</a></p>";
                    echo "<p><strong>Calendar Link:</strong> <a href='" . $result['eventLink'] . "' target='_blank'>" . $result['eventLink'] . "</a></p>";
                    
                    // Clean up - delete the test event
                    $deleteResult = $googleHelper->deleteMeetingEvent($result['eventId']);
                    if ($deleteResult['success']) {
                        echo "<div style='color: green;'>✅ Test event deleted successfully</div>";
                    } else {
                        echo "<div style='color: orange;'>⚠️ Could not delete test event: " . $deleteResult['error'] . "</div>";
                    }
                } else {
                    echo "<div style='color: red;'>❌ Failed to create sample event: " . $result['error'] . "</div>";
                }
            } else {
                echo "<div style='color: red;'>❌ Google Calendar API connection failed</div>";
            }
        } catch (Exception $e) {
            echo "<div style='color: red;'>❌ Google Calendar API error: " . $e->getMessage() . "</div>";
        }
    }
} else {
    echo "<div style='color: orange;'>⚠️ No Google token found for user</div>";
    echo "<p>To connect Google Calendar, go to the Add Meeting page and click 'Connect Google Calendar'</p>";
}

// Test 6: Check recent meetings with Google Meet
echo "<h2>Test 6: Recent Meetings with Google Meet</h2>";
$sqlRecentMeetings = "SELECT meetingId, studentName, meetingDate, googleMeetLink, googleCalendarLink 
                      FROM meetings 
                      WHERE googleMeetLink IS NOT NULL 
                      ORDER BY meetingDate DESC 
                      LIMIT 5";
$result = mysqli_query($link, $sqlRecentMeetings);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<div style='color: green;'>✅ Found " . mysqli_num_rows($result) . " meetings with Google Meet links</div>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Meeting ID</th><th>Student</th><th>Date</th><th>Meet Link</th><th>Calendar Link</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['meetingId'] . "</td>";
        echo "<td>" . htmlspecialchars($row['studentName']) . "</td>";
        echo "<td>" . $row['meetingDate'] . "</td>";
        echo "<td><a href='" . htmlspecialchars($row['googleMeetLink']) . "' target='_blank'>Join</a></td>";
        echo "<td><a href='" . htmlspecialchars($row['googleCalendarLink']) . "' target='_blank'>View</a></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div style='color: orange;'>⚠️ No meetings with Google Meet links found</div>";
}

echo "<h2>Test Complete</h2>";
echo "<p>If all tests pass, your Google Calendar integration is working correctly!</p>";
echo "<p><a href='addMeeting.php?studentId=1'>Go to Add Meeting page</a></p>";
?> 