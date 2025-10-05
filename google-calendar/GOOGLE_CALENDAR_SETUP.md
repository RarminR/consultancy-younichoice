# Google Calendar API Setup Guide

This guide will help you set up Google Calendar API integration for the meeting scheduling system.

## Prerequisites

1. A Google Cloud Platform account
2. PHP with cURL extension enabled
3. Composer installed on your server

## Step 1: Set up Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the Google Calendar API:
   - Go to "APIs & Services" > "Library"
   - Search for "Google Calendar API"
   - Click on it and press "Enable"

## Step 2: Create OAuth 2.0 Credentials

1. Go to "APIs & Services" > "Credentials"
2. Click "Create Credentials" > "OAuth 2.0 Client IDs"
3. Choose "Web application" as the application type
4. Add your domain to "Authorized JavaScript origins":
   - `https://consult-staging.younichoice.com`
5. Add your callback URL to "Authorized redirect URIs":
   - `https://consult-staging.younichoice.com/google-calendar/google-auth-callback.php`
6. Click "Create"
7. Note down your Client ID and Client Secret

## Step 3: Configure the Application

1. Update `google-calendar/google-calendar-config.php` with your credentials:
   ```php
   define('GOOGLE_CLIENT_ID', 'your-client-id-here');
   define('GOOGLE_CLIENT_SECRET', 'your-client-secret-here');
   ```

2. Install the Google API client library:
   ```bash
   composer install
   ```

## Step 4: Set up Database Tables

Run the SQL script to create the necessary database tables:

```sql
-- Execute the contents of google-calendar/google_calendar_tables.sql
```

Or run this command:
```bash
mysql -u your_username -p your_database < google-calendar/google_calendar_tables.sql
```

## Step 5: Test the Integration

1. Log in to your application as a consultant
2. Go to the "Add Meeting" page
3. You should see a "Google Calendar Integration" section
4. Click "Connect Google Calendar"
5. Authorize the application to access your Google Calendar
6. You should be redirected back with a success message

## Features

Once set up, the integration provides:

- **Automatic Google Meet Links**: Every meeting gets a Google Meet link automatically
- **Calendar Events**: Meetings are automatically added to Google Calendar
- **Email Invitations**: Both consultant and student receive calendar invitations
- **Reminders**: Automatic email and popup reminders (1 day and 15 minutes before)
- **Meeting Details**: Topic, activities, and notes are included in the calendar event

## Troubleshooting

### Common Issues

1. **"Invalid redirect URI" error**:
   - Make sure the redirect URI in Google Cloud Console matches exactly
   - Check for trailing slashes or protocol mismatches

2. **"Access denied" error**:
   - Ensure the Google Calendar API is enabled
   - Check that your OAuth consent screen is configured

3. **Token expiration**:
   - The system will automatically detect expired tokens
   - Users can reconnect by clicking the "Reconnect" button

4. **Database errors**:
   - Ensure all database tables are created
   - Check that the `user_google_tokens` table exists

### Debugging

Check the error logs for detailed information:
```bash
tail -f error_log
```

### Security Considerations

1. **HTTPS Required**: Google OAuth requires HTTPS for production
2. **Token Storage**: Access tokens are encrypted and stored securely
3. **Scope Limitation**: Only calendar access is requested, not full account access
4. **Token Expiration**: Tokens automatically expire and require re-authentication

## API Limits

- Google Calendar API has a quota of 1,000,000 requests per day
- Each meeting creation uses approximately 2-3 API calls
- Monitor usage in Google Cloud Console

## Support

For issues with the Google Calendar integration:
1. Check the error logs
2. Verify Google Cloud Console settings
3. Test with a fresh OAuth token
4. Contact the development team if issues persist 