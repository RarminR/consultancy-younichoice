# Google Meet Integration Documentation

This document provides comprehensive information about the Google Meet integration in the consultation platform.

## Table of Contents

1. [Overview](#overview)
2. [Features](#features)
3. [Setup Instructions](#setup-instructions)
4. [Database Schema](#database-schema)
5. [API Integration](#api-integration)
6. [Token Management](#token-management)
7. [Troubleshooting](#troubleshooting)
8. [File Structure](#file-structure)

## Overview

The Google Meet integration allows consultants to automatically create Google Calendar events with Google Meet video conference links when scheduling meetings with students. This provides a seamless experience for both consultants and students.

## Features

- ✅ **Automatic Google Meet Creation**: Creates video conference links automatically
- ✅ **Calendar Integration**: Events appear in both consultant and student calendars
- ✅ **Email Notifications**: Both parties receive calendar invitations
- ✅ **Meeting Details**: Includes meeting topic, activities, and notes
- ✅ **Token Refresh**: Automatic token renewal to prevent reconnection issues
- ✅ **Long-term Connection**: Users can stay connected for up to 6 months

## Setup Instructions

### 1. Google Cloud Project Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the Google Calendar API
4. Create OAuth 2.0 credentials
5. Set the redirect URI to: `https://yourdomain.com/google-calendar/google-auth-callback.php`

### 2. Configuration

1. Update `google-calendar-config.php` with your credentials:
   ```php
   define('GOOGLE_CLIENT_ID', 'your-client-id');
   define('GOOGLE_CLIENT_SECRET', 'your-client-secret');
   define('GOOGLE_REDIRECT_URI', 'https://yourdomain.com/google-calendar/google-auth-callback.php');
   ```

### 3. Database Setup

1. Run the installation script: `install_google_calendar.php`
2. This will create the necessary database tables

### 4. Testing

1. Test the connection: `test_google_calendar.php`
2. Check token status: `test_token_refresh.php`

## Database Schema

### user_google_tokens Table
```sql
CREATE TABLE user_google_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    access_token TEXT NOT NULL,
    refresh_token TEXT NOT NULL,
    expires_in INT NOT NULL,
    created INT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### meetings Table Updates
```sql
ALTER TABLE meetings ADD COLUMN googleEventId VARCHAR(255) NULL;
ALTER TABLE meetings ADD COLUMN googleMeetLink TEXT NULL;
ALTER TABLE meetings ADD COLUMN googleCalendarLink TEXT NULL;
```

## API Integration

### GoogleCalendarHelper Class

The main integration class provides the following methods:

- `createMeetingEvent($meetingData)` - Creates a new calendar event with Google Meet
- `updateMeetingEvent($eventId, $meetingData)` - Updates an existing event
- `deleteMeetingEvent($eventId)` - Deletes an event
- `refreshTokenIfNeeded()` - Automatically refreshes expired tokens
- `isTokenValid()` - Checks if the current token is valid
- `getTokenStatus()` - Returns detailed token status information

### Meeting Data Structure

```php
$meetingData = [
    'studentName' => 'John Doe',
    'consultantName' => 'Jane Smith',
    'studentSchool' => 'High School Name',
    'meetingDate' => '2024-01-15T10:00:00+02:00', // ISO 8601 format
    'meetingTopic' => 'College Application Review',
    'meetingActivities' => 'Essay Review + Application Strategy',
    'meetingNotes' => 'Bring your essay drafts',
    'consultantEmail' => 'consultant@example.com',
    'studentEmail' => 'student@example.com'
];
```

## Token Management

### Automatic Token Refresh

The system now includes automatic token refresh functionality to prevent daily reconnection issues:

#### How It Works

1. **Access Token**: Valid for 1 hour
2. **Refresh Token**: Valid for up to 6 months
3. **Automatic Refresh**: Happens 5 minutes before expiration
4. **Database Storage**: Tokens are stored securely in the database

#### Connection Duration

- ✅ **Access Token**: 1 hour (automatically refreshed)
- ✅ **Refresh Token**: Up to 6 months (if not revoked)
- ✅ **Automatic Refresh**: Happens 5 minutes before expiration
- ✅ **No Manual Reconnection**: Required unless token is revoked

#### When Reconnection is Needed

You only need to reconnect if:
- You manually revoke access in your Google Account
- You don't use the system for 6+ months
- Google changes their security policies

#### Testing Token Status

Use the token refresh test script to check your connection status:
```
google-calendar/test_token_refresh.php
```

This script will show you:
- Current token status
- Time until expiration
- Automatic refresh capability
- API connection status

## Troubleshooting

### Common Issues

1. **"Token expired" error**
   - The system should automatically refresh tokens
   - If it fails, try reconnecting to Google Calendar

2. **"No Google Calendar token found"**
   - Connect to Google Calendar first
   - Check if you're logged in with the correct user

3. **"API connection failed"**
   - Check your Google Cloud Project settings
   - Verify API is enabled
   - Check credentials in config file

4. **"Bad Request" error**
   - Check meeting date format (should be ISO 8601)
   - Verify all required fields are provided

### Debug Tools

- `status_check.php` - Basic status check (no login required)
- `test_token_refresh.php` - Detailed token status (login required)
- `test_google_calendar.php` - Full integration test
- `debug_meeting_creation.php` - Step-by-step debugging

## File Structure

```
google-calendar/
├── google-calendar-config.php      # API credentials
├── google-calendar-helper.php      # Main integration class
├── google-auth-callback.php        # OAuth callback handler
├── google_calendar_tables.sql      # Database schema
├── install_google_calendar.php     # Installation script
├── test_google_calendar.php        # Testing script
├── test_token_refresh.php          # Token refresh test
├── status_check.php                # Status check
├── index.php                       # Navigation page
├── GOOGLE_CALENDAR_SETUP.md       # Setup guide
└── README_GOOGLE_MEET.md          # This documentation
```

## Usage Examples

### Creating a Meeting with Google Meet

1. Go to the Add Meeting page
2. Fill in meeting details
3. Check "Enable Google Meet" checkbox
4. Submit the form
5. The system will automatically:
   - Create a Google Calendar event
   - Generate a Google Meet link
   - Send invitations to both parties
   - Store the links in the database

### Checking Connection Status

1. Visit `google-calendar/test_token_refresh.php`
2. This will show your current token status
3. If needed, it will attempt to refresh your token
4. You'll see how long you can stay connected

## Security Considerations

- Tokens are stored securely in the database
- Access tokens are automatically refreshed
- Users can revoke access at any time
- No sensitive data is logged
- HTTPS is required for OAuth

## Support

For issues or questions:
1. Check the troubleshooting section
2. Use the debug tools provided
3. Review the error logs
4. Test with the provided scripts

---

*Last updated: January 2024* 