<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['type']) || $_SESSION['type'] != 1) {
    echo "<div style='color: red; font-family: Arial, sans-serif; padding: 20px;'>❌ Access denied. Admin privileges required.</div>";
    exit;
}

/**
 * Google Calendar Integration - Index Page
 * This page provides information and links to all Google Calendar integration files
 */

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Google Calendar Integration</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'>
    <style>
        body { padding: 20px; }
        .card { margin-bottom: 20px; }
        .file-link { color: #007bff; text-decoration: none; }
        .file-link:hover { text-decoration: underline; }
        .status-ok { color: #28a745; }
        .status-warning { color: #ffc107; }
        .status-error { color: #dc3545; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='row'>
            <div class='col-12'>
                <h1><i class='fab fa-google'></i> Google Calendar Integration</h1>
                <p class='lead'>This folder contains all files related to the Google Calendar and Google Meet integration.</p>
                
                <div class='card'>
                    <div class='card-header'>
                        <h5><i class='fas fa-cogs'></i> Configuration Files</h5>
                    </div>
                    <div class='card-body'>
                        <ul class='list-unstyled'>
                            <li><i class='fas fa-file-code status-ok'></i> <a href='google-calendar-config.php' class='file-link'>google-calendar-config.php</a> - API credentials and settings</li>
                            <li><i class='fas fa-file-code status-ok'></i> <a href='google-calendar-helper.php' class='file-link'>google-calendar-helper.php</a> - Main integration class</li>
                            <li><i class='fas fa-file-code status-ok'></i> <a href='google-auth-callback.php' class='file-link'>google-auth-callback.php</a> - OAuth callback handler</li>
                        </ul>
                    </div>
                </div>
                
                <div class='card'>
                    <div class='card-header'>
                        <h5><i class='fas fa-database'></i> Database Files</h5>
                    </div>
                    <div class='card-body'>
                        <ul class='list-unstyled'>
                            <li><i class='fas fa-file-alt status-ok'></i> <a href='google_calendar_tables.sql' class='file-link'>google_calendar_tables.sql</a> - Database schema</li>
                        </ul>
                    </div>
                </div>
                
                <div class='card'>
                    <div class='card-header'>
                        <h5><i class='fas fa-tools'></i> Setup and Testing</h5>
                    </div>
                    <div class='card-body'>
                        <ul class='list-unstyled'>
                            <li><i class='fas fa-download status-ok'></i> <a href='install_google_calendar.php' class='file-link'>install_google_calendar.php</a> - Installation script</li>
                            <li><i class='fas fa-vial status-ok'></i> <a href='test_google_calendar.php' class='file-link'>test_google_calendar.php</a> - Testing script</li>
                            <li><i class='fas fa-check-circle status-ok'></i> <a href='status_check.php' class='file-link'>status_check.php</a> - Status check (no login required)</li>
                            <li><i class='fas fa-sync-alt status-ok'></i> <a href='test_token_refresh.php' class='file-link'>test_token_refresh.php</a> - Token refresh test (login required)</li>
                        </ul>
                    </div>
                </div>
                
                <div class='card'>
                    <div class='card-header'>
                        <h5><i class='fas fa-book'></i> Documentation</h5>
                    </div>
                    <div class='card-body'>
                        <ul class='list-unstyled'>
                            <li><i class='fas fa-file-alt status-ok'></i> <a href='GOOGLE_CALENDAR_SETUP.md' class='file-link'>GOOGLE_CALENDAR_SETUP.md</a> - Setup guide</li>
                            <li><i class='fas fa-file-alt status-ok'></i> <a href='README_GOOGLE_MEET.md' class='file-link'>README_GOOGLE_MEET.md</a> - Complete documentation</li>
                        </ul>
                    </div>
                </div>
                
                <div class='card'>
                    <div class='card-header'>
                        <h5><i class='fas fa-info-circle'></i> Quick Start</h5>
                    </div>
                    <div class='card-body'>
                        <ol>
                            <li>Check the current status with <a href='status_check.php'>status_check.php</a></li>
                            <li>Configure your Google Cloud Project (see <a href='GOOGLE_CALENDAR_SETUP.md'>setup guide</a>)</li>
                            <li>Update <a href='google-calendar-config.php'>google-calendar-config.php</a> with your credentials</li>
                            <li>Run <a href='install_google_calendar.php'>install_google_calendar.php</a> to set up the database</li>
                            <li>Test the integration with <a href='test_google_calendar.php'>test_google_calendar.php</a></li>
                            <li>Start using Google Meet in your meetings!</li>
                        </ol>
                    </div>
                </div>
                
                <div class='card'>
                    <div class='card-header'>
                        <h5><i class='fas fa-external-link-alt'></i> External Links</h5>
                    </div>
                    <div class='card-body'>
                        <ul class='list-unstyled'>
                            <li><i class='fas fa-external-link-alt'></i> <a href='../addMeeting.php' class='file-link'>Add Meeting Page</a> - Where Google Meet is integrated</li>
                            <li><i class='fas fa-external-link-alt'></i> <a href='../meeting.php' class='file-link'>Meeting Details Page</a> - Where Google Meet links are displayed</li>
                            <li><i class='fas fa-external-link-alt'></i> <a href='https://console.cloud.google.com/' target='_blank' class='file-link'>Google Cloud Console</a> - For API setup</li>
                        </ul>
                    </div>
                </div>
                
                <div class='alert alert-info'>
                    <i class='fas fa-info-circle'></i> 
                    <strong>Note:</strong> All Google Calendar integration files are now organized in this folder for better organization and easier maintenance.
                </div>
            </div>
        </div>
    </div>
</body>
</html>";
?> 