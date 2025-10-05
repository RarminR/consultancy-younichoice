<?php
/**
 * Test Meeting Form
 * This page helps debug the meeting creation process
 */

session_start();
require_once dirname(__DIR__) . '/configDatabase.php';
require_once __DIR__ . '/google-calendar-helper.php';

if (!isset($_SESSION['type']) || $_SESSION['type'] != 1) {
    echo "<div style='color: red;'>❌ Access denied. Admin privileges required.</div>";
    exit;
}

$userId = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "<h2>Form Submitted</h2>\n";
    echo "<p><strong>POST Data:</strong></p>\n";
    echo "<pre>" . print_r($_POST, true) . "</pre>\n";
    
    $enableGoogleMeet = isset($_POST['enable-google-meet']) ? true : false;
    echo "<p><strong>Google Meet Enabled:</strong> " . ($enableGoogleMeet ? 'YES' : 'NO') . "</p>\n";
    
    if ($enableGoogleMeet) {
        echo "<h3>Testing Google Calendar Integration</h3>\n";
        
        // Get user's Google access token
        $sqlToken = "SELECT accessToken, expiresAt FROM user_google_tokens WHERE userId = '$userId'";
        $queryToken = mysqli_query($link, $sqlToken);
        
        if ($queryToken && mysqli_num_rows($queryToken) > 0) {
            $tokenData = mysqli_fetch_assoc($queryToken);
            $accessToken = $tokenData['accessToken'];
            $expiresAt = $tokenData['expiresAt'];
            
            if (time() < $expiresAt) {
                $googleHelper = new GoogleCalendarHelper($accessToken);
                
                $meetingData = [
                    'studentName' => $_POST['student_name'] ?? 'Test Student',
                    'consultantName' => $_POST['consultant_name'] ?? 'Test Consultant',
                    'studentSchool' => $_POST['student_school'] ?? 'Test School',
                    'meetingDate' => date('c', strtotime('+1 hour')),
                    'meetingTopic' => $_POST['meeting_topic'] ?? 'Test Meeting',
                    'meetingActivities' => $_POST['meeting_activities'] ?? 'Test Activities',
                    'meetingNotes' => $_POST['meeting_notes'] ?? 'Test notes',
                    'consultantEmail' => 'test@example.com',
                    'studentEmail' => 'student@example.com'
                ];
                
                echo "<p><strong>Meeting Data:</strong></p>\n";
                echo "<pre>" . print_r($meetingData, true) . "</pre>\n";
                
                $googleResult = $googleHelper->createMeetingEvent($meetingData);
                
                echo "<p><strong>Google Result:</strong></p>\n";
                echo "<pre>" . print_r($googleResult, true) . "</pre>\n";
                
                if ($googleResult['success']) {
                    echo "<div style='color: green;'>✅ Google Calendar event created successfully!</div>\n";
                    echo "<p><strong>Event ID:</strong> " . $googleResult['eventId'] . "</p>\n";
                    echo "<p><strong>Meet Link:</strong> <a href='" . $googleResult['meetLink'] . "' target='_blank'>" . $googleResult['meetLink'] . "</a></p>\n";
                    
                    // Clean up
                    $deleteResult = $googleHelper->deleteMeetingEvent($googleResult['eventId']);
                    if ($deleteResult['success']) {
                        echo "<div style='color: green;'>✅ Test event deleted successfully</div>\n";
                    }
                } else {
                    echo "<div style='color: red;'>❌ Google Calendar creation failed: " . $googleResult['error'] . "</div>\n";
                }
            } else {
                echo "<div style='color: red;'>❌ Google token expired</div>\n";
            }
        } else {
            echo "<div style='color: red;'>❌ No Google token found</div>\n";
        }
    } else {
        echo "<div style='color: orange;'>⚠️ Google Meet not enabled in form</div>\n";
    }
    
    echo "<hr>\n";
    echo "<p><a href='test_meeting_form.php'>Test Again</a></p>\n";
    exit;
}

// Check Google token status
$sqlToken = "SELECT expiresAt FROM user_google_tokens WHERE userId = '$userId'";
$queryToken = mysqli_query($link, $sqlToken);
$hasToken = $queryToken && mysqli_num_rows($queryToken) > 0;
$tokenValid = false;

if ($hasToken) {
    $tokenData = mysqli_fetch_assoc($queryToken);
    $tokenValid = (time() < $tokenData['expiresAt']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Meeting Form</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .status { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .status.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status.warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .status.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <h1>Test Meeting Form</h1>
    
    <div class="status <?php echo $hasToken ? ($tokenValid ? 'success' : 'warning') : 'error'; ?>">
        <strong>Google Calendar Status:</strong>
        <?php if ($hasToken): ?>
            <?php if ($tokenValid): ?>
                ✅ Connected and valid
            <?php else: ?>
                ⚠️ Token expired
            <?php endif; ?>
        <?php else: ?>
            ❌ Not connected
        <?php endif; ?>
    </div>
    
    <form method="POST">
        <div class="form-group">
            <label for="student_name">Student Name:</label>
            <input type="text" id="student_name" name="student_name" value="Test Student" required>
        </div>
        
        <div class="form-group">
            <label for="consultant_name">Consultant Name:</label>
            <input type="text" id="consultant_name" name="consultant_name" value="Test Consultant" required>
        </div>
        
        <div class="form-group">
            <label for="student_school">Student School:</label>
            <input type="text" id="student_school" name="student_school" value="Test School" required>
        </div>
        
        <div class="form-group">
            <label for="meeting_topic">Meeting Topic:</label>
            <input type="text" id="meeting_topic" name="meeting_topic" value="Test Meeting Topic" required>
        </div>
        
        <div class="form-group">
            <label for="meeting_activities">Meeting Activities:</label>
            <input type="text" id="meeting_activities" name="meeting_activities" value="Test Activities" required>
        </div>
        
        <div class="form-group">
            <label for="meeting_notes">Meeting Notes:</label>
            <textarea id="meeting_notes" name="meeting_notes" rows="3">Test meeting notes</textarea>
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" id="enable-google-meet" name="enable-google-meet" checked>
                Create Google Meet link and add to calendar
            </label>
        </div>
        
        <button type="submit">Test Meeting Creation</button>
    </form>
    
    <hr>
    <p><a href="../addMeeting.php?studentId=1">Go to Real Add Meeting Page</a></p>
</body>
</html> 