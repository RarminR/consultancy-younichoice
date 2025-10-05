<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['type']) || $_SESSION['type'] != 1) {
    echo "<div style='color: red; font-family: Arial, sans-serif; padding: 20px;'>❌ Access denied. Admin privileges required.</div>";
    exit;
}

/**
 * Google Calendar Integration Installation Script
 * Run this script to set up the Google Calendar integration
 */

require_once dirname(__DIR__) . '/configDatabase.php';

echo "<h1>Google Calendar Integration Setup</h1>\n";

// Check if Composer is available
if (!file_exists('vendor/autoload.php')) {
    echo "<div style='color: red;'>❌ Composer dependencies not installed.</div>\n";
    echo "<p>Please run: <code>composer install</code></p>\n";
    exit;
}

// Check if Google Calendar config exists
if (!file_exists(__DIR__ . '/google-calendar-config.php')) {
    echo "<div style='color: red;'>❌ Google Calendar configuration file not found.</div>\n";
    echo "<p>Please create <code>google-calendar/google-calendar-config.php</code> with your Google API credentials.</p>\n";
    exit;
}

// Check database connection
if (!$link) {
    echo "<div style='color: red;'>❌ Database connection failed.</div>\n";
    exit;
}

echo "<div style='color: green;'>✅ Database connection successful.</div>\n";

// Create user_google_tokens table
$sqlCreateTokensTable = "
CREATE TABLE IF NOT EXISTS `user_google_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `accessToken` text NOT NULL,
  `refreshToken` text,
  `expiresAt` int(11) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if (mysqli_query($link, $sqlCreateTokensTable)) {
    echo "<div style='color: green;'>✅ user_google_tokens table created successfully.</div>\n";
} else {
    echo "<div style='color: orange;'>⚠️ user_google_tokens table already exists or creation failed: " . mysqli_error($link) . "</div>\n";
}

// Check if meetings table has Google Calendar columns
$sqlCheckColumns = "SHOW COLUMNS FROM meetings LIKE 'googleEventId'";
$result = mysqli_query($link, $sqlCheckColumns);

if (mysqli_num_rows($result) == 0) {
    // Add Google Calendar columns to meetings table
    $sqlAddColumns = "
    ALTER TABLE `meetings` 
    ADD COLUMN `googleEventId` varchar(255) DEFAULT NULL AFTER `meetingActivities`,
    ADD COLUMN `googleMeetLink` text DEFAULT NULL AFTER `googleEventId`,
    ADD COLUMN `googleCalendarLink` text DEFAULT NULL AFTER `googleMeetLink`
    ";
    
    if (mysqli_query($link, $sqlAddColumns)) {
        echo "<div style='color: green;'>✅ Google Calendar columns added to meetings table.</div>\n";
        
        // Add index
        $sqlAddIndex = "CREATE INDEX `idx_google_event_id` ON `meetings` (`googleEventId`)";
        if (mysqli_query($link, $sqlAddIndex)) {
            echo "<div style='color: green;'>✅ Index created for googleEventId.</div>\n";
        } else {
            echo "<div style='color: orange;'>⚠️ Index creation failed: " . mysqli_error($link) . "</div>\n";
        }
    } else {
        echo "<div style='color: red;'>❌ Failed to add Google Calendar columns: " . mysqli_error($link) . "</div>\n";
    }
} else {
    echo "<div style='color: green;'>✅ Google Calendar columns already exist in meetings table.</div>\n";
}

// Test Google Calendar helper
try {
    require_once __DIR__ . '/google-calendar-helper.php';
    $googleHelper = new GoogleCalendarHelper();
    echo "<div style='color: green;'>✅ Google Calendar helper loaded successfully.</div>\n";
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Google Calendar helper error: " . $e->getMessage() . "</div>\n";
}

echo "<h2>Setup Complete!</h2>\n";
echo "<p>Next steps:</p>\n";
echo "<ol>\n";
echo "<li>Configure your Google Cloud Project and get API credentials</li>\n";
echo "<li>Update <code>google-calendar-config.php</code> with your credentials</li>\n";
echo "<li>Test the integration by creating a meeting</li>\n";
echo "</ol>\n";

echo "<p><strong>Note:</strong> Make sure your domain is configured in Google Cloud Console with the correct redirect URI.</p>\n";
?> 