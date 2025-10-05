# Google Calendar Integration - Folder Organization

All Google Calendar and Google Meet integration files have been organized into the `google-calendar/` folder for better organization and easier maintenance.

## 📁 Folder Structure

```
google-calendar/
├── index.php                           # Navigation and information page
├── google-calendar-config.php          # API credentials and settings
├── google-calendar-helper.php          # Main integration class
├── google-auth-callback.php            # OAuth callback handler
├── google_calendar_tables.sql          # Database schema
├── install_google_calendar.php         # Installation script
├── test_google_calendar.php            # Testing script
├── GOOGLE_CALENDAR_SETUP.md           # Setup guide
└── README_GOOGLE_MEET.md              # Complete documentation
```

## 🔗 Quick Access

- **Setup Guide**: [google-calendar/GOOGLE_CALENDAR_SETUP.md](google-calendar/GOOGLE_CALENDAR_SETUP.md)
- **Complete Documentation**: [google-calendar/README_GOOGLE_MEET.md](google-calendar/README_GOOGLE_MEET.md)
- **Installation Script**: [google-calendar/install_google_calendar.php](google-calendar/install_google_calendar.php)
- **Test Script**: [google-calendar/test_google_calendar.php](google-calendar/test_google_calendar.php)
- **Navigation Page**: [google-calendar/index.php](google-calendar/index.php)

## 🚀 Quick Start

1. **Navigate to the Google Calendar folder**: Visit `google-calendar/index.php` for an overview
2. **Follow the setup guide**: See `google-calendar/GOOGLE_CALENDAR_SETUP.md`
3. **Run installation**: Execute `php google-calendar/install_google_calendar.php`
4. **Test integration**: Run `php google-calendar/test_google_calendar.php`

## 📝 Modified Files

The following main application files have been updated to reference the new folder structure:

- `addMeeting.php` - Updated to include Google Meet integration
- `meeting.php` - Updated to display Google Meet links
- `composer.json` - Added Google API client dependency

## 🔧 Configuration

Update the Google API credentials in `google-calendar/google-calendar-config.php`:

```php
define('GOOGLE_CLIENT_ID', 'your-client-id-here');
define('GOOGLE_CLIENT_SECRET', 'your-client-secret-here');
define('GOOGLE_REDIRECT_URI', 'https://yourdomain.com/google-calendar/google-auth-callback.php');
```

## 📚 Documentation

- **Setup Guide**: Complete step-by-step setup instructions
- **API Documentation**: Google Calendar API integration details
- **Troubleshooting**: Common issues and solutions
- **Security**: OAuth 2.0 and data protection information

## 🎯 Features

- ✅ Automatic Google Meet links for meetings
- ✅ Calendar integration with email invitations
- ✅ Smart reminders (1 day and 15 minutes before)
- ✅ Meeting details included in calendar events
- ✅ Secure OAuth 2.0 authentication
- ✅ Graceful error handling
- ✅ Organized file structure

---

**Note**: All Google Calendar integration files are now centralized in the `google-calendar/` folder for easier maintenance and better organization. 