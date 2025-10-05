<?php
/**
 * Google Calendar Integration Status Check
 * This script checks the basic setup without requiring user login
 */

echo "<h1>Google Calendar Integration Status Check</h1>\n";

// Check 1: Composer dependencies
echo "<h2>1. Composer Dependencies</h2>\n";
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    echo "<div style='color: green;'>✅ Composer dependencies installed</div>\n";
} else {
    echo "<div style='color: red;'>❌ Composer dependencies not found. Run: composer install</div>\n";
}

// Check 2: Configuration file
echo "<h2>2. Configuration File</h2>\n";
if (file_exists(__DIR__ . '/google-calendar-config.php')) {
    echo "<div style='color: green;'>✅ Configuration file exists</div>\n";
    
    // Check if credentials are configured
    require_once __DIR__ . '/google-calendar-config.php';
    if (defined('GOOGLE_CLIENT_ID') && GOOGLE_CLIENT_ID !== 'YOUR_GOOGLE_CLIENT_ID') {
        echo "<div style='color: green;'>✅ Google Client ID configured</div>\n";
    } else {
        echo "<div style='color: orange;'>⚠️ Google Client ID not configured</div>\n";
    }
    
    if (defined('GOOGLE_CLIENT_SECRET') && GOOGLE_CLIENT_SECRET !== 'YOUR_GOOGLE_CLIENT_SECRET') {
        echo "<div style='color: green;'>✅ Google Client Secret configured</div>\n";
    } else {
        echo "<div style='color: orange;'>⚠️ Google Client Secret not configured</div>\n";
    }
} else {
    echo "<div style='color: red;'>❌ Configuration file not found</div>\n";
}

// Check 3: Database connection
echo "<h2>3. Database Connection</h2>\n";
try {
    require_once dirname(__DIR__) . '/configDatabase.php';
    if (isset($link) && $link) {
        echo "<div style='color: green;'>✅ Database connection successful</div>\n";
        
        // Check if tables exist
        $sqlCheckTokens = "SHOW TABLES LIKE 'user_google_tokens'";
        $result = mysqli_query($link, $sqlCheckTokens);
        if (mysqli_num_rows($result) > 0) {
            echo "<div style='color: green;'>✅ user_google_tokens table exists</div>\n";
        } else {
            echo "<div style='color: red;'>❌ user_google_tokens table not found</div>\n";
        }
        
        $sqlCheckColumns = "SHOW COLUMNS FROM meetings LIKE 'googleEventId'";
        $result = mysqli_query($link, $sqlCheckColumns);
        if (mysqli_num_rows($result) > 0) {
            echo "<div style='color: green;'>✅ Google Calendar columns exist in meetings table</div>\n";
        } else {
            echo "<div style='color: red;'>❌ Google Calendar columns not found in meetings table</div>\n";
        }
    } else {
        echo "<div style='color: red;'>❌ Database connection failed</div>\n";
    }
} catch (Exception $e) {
    echo "<div style='color: red;'>❌ Database error: " . $e->getMessage() . "</div>\n";
}

// Check 4: Google Calendar helper
echo "<h2>4. Google Calendar Helper</h2>\n";
if (file_exists(__DIR__ . '/google-calendar-helper.php')) {
    echo "<div style='color: green;'>✅ Google Calendar helper file exists</div>\n";
    
    try {
        require_once __DIR__ . '/google-calendar-helper.php';
        $googleHelper = new GoogleCalendarHelper();
        echo "<div style='color: green;'>✅ Google Calendar helper class loaded successfully</div>\n";
    } catch (Exception $e) {
        echo "<div style='color: red;'>❌ Google Calendar helper error: " . $e->getMessage() . "</div>\n";
    }
} else {
    echo "<div style='color: red;'>❌ Google Calendar helper file not found</div>\n";
}

// Check 5: Auth callback
echo "<h2>5. Authentication Callback</h2>\n";
if (file_exists(__DIR__ . '/google-auth-callback.php')) {
    echo "<div style='color: green;'>✅ Auth callback file exists</div>\n";
} else {
    echo "<div style='color: red;'>❌ Auth callback file not found</div>\n";
}

// Summary
echo "<h2>Summary</h2>\n";
echo "<p><strong>Next steps:</strong></p>\n";
echo "<ol>\n";
echo "<li>Configure Google Cloud Project and get API credentials</li>\n";
echo "<li>Update <code>google-calendar/google-calendar-config.php</code> with your credentials</li>\n";
echo "<li>Test the integration by creating a meeting</li>\n";
echo "</ol>\n";

echo "<p><strong>Useful links:</strong></p>\n";
echo "<ul>\n";
echo "<li><a href='index.php'>Google Calendar Integration Overview</a></li>\n";
echo "<li><a href='GOOGLE_CALENDAR_SETUP.md'>Setup Guide</a></li>\n";
echo "<li><a href='install_google_calendar.php'>Installation Script</a></li>\n";
echo "<li><a href='test_google_calendar.php'>Test Script (requires login)</a></li>\n";
echo "</ul>\n";
?> 