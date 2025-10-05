<?php
/**
 * Debug script for Google Calendar meeting creation
 * This script tests the Google Calendar integration step by step
 */

session_start();
require_once dirname(__DIR__) . '/configDatabase.php';
require_once __DIR__ . '/google-calendar-helper.php';

echo "<h1>Google Calendar Meeting Creation Debug</h1>\n";

// Check if user is logged in and is admin
if (!isset($_SESSION['type']) || $_SESSION['type'] != 1) {
    echo "<div style='color: red;'>❌ Access denied. Admin privileges required.</div>\n";
    exit;
}

$userId = $_SESSION['id'];
echo "<p><strong>User ID:</strong> $userId</p>\n";

// Step 1: Check if user has Google token
echo "<h2>Step 1: Check Google Token</h2>\n";
$sqlToken = "SELECT accessToken, expiresAt FROM user_google_tokens WHERE userId = '$userId'";
$queryToken = mysqli_query($link, $sqlToken);

if ($queryToken && mysqli_num_rows($queryToken) > 0) {
    $tokenData = mysqli_fetch_assoc($queryToken);
    $expiresAt = $tokenData['expiresAt'];
    $isExpired = (time() >= $expiresAt);
    
    echo "<div style='color: green;'>✅ Google token found for user</div>\n";
    echo "<p><strong>Token expires:</strong> " . date('Y-m-d H:i:s', $expiresAt) . "</p>\n";
    echo "<p><strong>Current time:</strong> " . date('Y-m-d H:i:s') . "</p>\n";
    
    if ($isExpired) {
        echo "<div style='color: orange;'>⚠️ Token has expired</div>\n";
        exit;
    } else {
        echo "<div style='color: green;'>✅ Token is valid</div>\n";
    }
} else {
    echo "<div style='color: red;'>❌ No Google token found for user</div>\n";
    echo "<p>Please connect Google Calendar first.</p>\n";
    exit;
}

// Step 2: Test Google Calendar API connection
echo "<h2>Step 2: Test Google Calendar API</h2>\n";
try {
    $googleHelper = new GoogleCalendarHelper($tokenData['accessToken']);
    
    if ($googleHelper->isTokenValid()) {
        echo "<div style='color: green;'>✅ Google Calendar API connection successful</div>\n";
    } else {
        echo "<div style='color: red;'>❌ Google Calendar API connection failed</div>\n";
        exit;
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Google Calendar API error: " . $e->getMessage() . "</div>\n";
    exit;
}

// Step 3: Test creating a sample event
echo "<h2>Step 3: Test Creating Sample Event</h2>\n";
try {
    $testMeetingData = [
        'studentName' => 'Debug Test Student',
        'consultantName' => 'Debug Test Consultant',
        'studentSchool' => 'Debug Test School',
        'meetingDate' => date('c', strtotime('+1 hour')),
        'meetingTopic' => 'Debug Test Meeting',
        'meetingActivities' => 'Debug Test Activities',
        'meetingNotes' => 'This is a debug test meeting created by the debug script.',
        'consultantEmail' => 'test@example.com',
        'studentEmail' => 'student@example.com'
    ];
    
    echo "<p><strong>Test meeting data:</strong></p>\n";
    echo "<pre>" . print_r($testMeetingData, true) . "</pre>\n";
    
    $result = $googleHelper->createMeetingEvent($testMeetingData);
    
    if ($result['success']) {
        echo "<div style='color: green;'>✅ Sample event created successfully!</div>\n";
        echo "<p><strong>Event ID:</strong> " . $result['eventId'] . "</p>\n";
        echo "<p><strong>Meet Link:</strong> <a href='" . $result['meetLink'] . "' target='_blank'>" . $result['meetLink'] . "</a></p>\n";
        echo "<p><strong>Calendar Link:</strong> <a href='" . $result['eventLink'] . "' target='_blank'>" . $result['eventLink'] . "</a></p>\n";
        
        // Clean up - delete the test event
        echo "<h3>Cleaning up test event...</h3>\n";
        $deleteResult = $googleHelper->deleteMeetingEvent($result['eventId']);
        if ($deleteResult['success']) {
            echo "<div style='color: green;'>✅ Test event deleted successfully</div>\n";
        } else {
            echo "<div style='color: orange;'>⚠️ Could not delete test event: " . $deleteResult['error'] . "</div>\n";
        }
    } else {
        echo "<div style='color: red;'>❌ Failed to create sample event: " . $result['error'] . "</div>\n";
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Exception during event creation: " . $e->getMessage() . "</div>\n";
}

// Step 4: Check recent meetings
echo "<h2>Step 4: Check Recent Meetings</h2>\n";
$sqlRecentMeetings = "SELECT meetingId, studentName, meetingDate, googleEventId, googleMeetLink 
                      FROM meetings 
                      WHERE meetingDate > DATE_SUB(NOW(), INTERVAL 7 DAY)
                      ORDER BY meetingDate DESC 
                      LIMIT 10";
$result = mysqli_query($link, $sqlRecentMeetings);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<div style='color: green;'>✅ Found " . mysqli_num_rows($result) . " recent meetings</div>\n";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Meeting ID</th><th>Student</th><th>Date</th><th>Google Event ID</th><th>Meet Link</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['meetingId'] . "</td>";
        echo "<td>" . htmlspecialchars($row['studentName']) . "</td>";
        echo "<td>" . $row['meetingDate'] . "</td>";
        echo "<td>" . ($row['googleEventId'] ? $row['googleEventId'] : 'NULL') . "</td>";
        echo "<td>" . ($row['googleMeetLink'] ? '<a href="' . htmlspecialchars($row['googleMeetLink']) . '" target="_blank">Link</a>' : 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div style='color: orange;'>⚠️ No recent meetings found</div>\n";
}

echo "<h2>Debug Complete</h2>\n";
echo "<p><strong>Next steps:</strong></p>\n";
echo "<ol>\n";
echo "<li>If the sample event creation worked, the issue might be in the meeting creation form</li>\n";
echo "<li>Check if the 'Create Google Meet link' checkbox is being checked</li>\n";
echo "<li>Verify that the meeting creation process is calling the Google Calendar integration</li>\n";
echo "</ol>\n";
?> 