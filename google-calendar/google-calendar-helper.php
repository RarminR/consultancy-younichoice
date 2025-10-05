<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/google-calendar-config.php';

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;

class GoogleCalendarHelper {
    private $client;
    private $service;
    private $accessToken;
    
    public function __construct($accessToken = null) {
        $this->client = new Client();
        $this->client->setClientId(GOOGLE_CLIENT_ID);
        $this->client->setClientSecret(GOOGLE_CLIENT_SECRET);
        $this->client->setRedirectUri(GOOGLE_REDIRECT_URI);
        $this->client->setScopes([
            'https://www.googleapis.com/auth/calendar',
            'https://www.googleapis.com/auth/calendar.events'
        ]);
        
        // Enable offline access to get refresh tokens
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        
        if ($accessToken) {
            $this->accessToken = $accessToken;
            $this->client->setAccessToken($accessToken);
        }
        
        $this->service = new Calendar($this->client);
    }
    
    /**
     * Get authorization URL for Google Calendar access
     */
    public function getAuthUrl($studentId = null) {
        // Store studentId in session if provided
        if ($studentId !== null) {
            $_SESSION['google_auth_student_id'] = $studentId;
        }
        return $this->client->createAuthUrl();
    }
    
    /**
     * Exchange authorization code for access token
     */
    public function exchangeCodeForToken($code) {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);
        return $token;
    }
    
    /**
     * Refresh access token if needed
     */
    public function refreshTokenIfNeeded() {
        try {
            // Get current token status from database
            $tokenStatus = $this->getCurrentTokenStatus();
            
            if (!$tokenStatus['hasToken']) {
                error_log('Google Calendar: No token found in database');
                return false;
            }
            
            // Check if token is expired or will expire soon (within 5 minutes)
            if ($tokenStatus['isExpired'] || $tokenStatus['isExpiringSoon']) {
                $refreshToken = $tokenStatus['refreshToken'];
                
                if ($refreshToken) {
                    error_log('Google Calendar: Attempting to refresh token');
                    
                    // Set the refresh token and refresh
                    $this->client->setRefreshToken($refreshToken);
                    $newToken = $this->client->fetchAccessTokenWithRefreshToken();
                    
                    if (isset($newToken['access_token'])) {
                        // Update the stored token
                        $this->updateStoredToken($newToken);
                        
                        // Update current token
                        $this->accessToken = $newToken;
                        $this->client->setAccessToken($newToken);
                        
                        error_log('Google Calendar: Token refreshed successfully');
                        return true;
                    } else {
                        error_log('Google Calendar: Failed to get new access token from refresh');
                        return false;
                    }
                } else {
                    error_log('Google Calendar: No refresh token found in database');
                    return false;
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log('Google Calendar: Token refresh failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get current token status from database
     */
    private function getCurrentTokenStatus() {
        try {
            require_once dirname(__DIR__) . '/configDatabase.php';
            
            $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Get user ID from session or current user
            $userId = $_SESSION['id'] ?? 1; // Default to user 1 if not in session
            
            $stmt = $pdo->prepare("SELECT accessToken, refreshToken, expiresAt FROM user_google_tokens WHERE userId = ?");
            $stmt->execute([$userId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return [
                    'hasToken' => false,
                    'isExpired' => true,
                    'isExpiringSoon' => true,
                    'refreshToken' => null
                ];
            }
            
            $isExpired = time() >= $result['expiresAt'];
            $fiveMinutesFromNow = time() + 300; // 5 minutes
            $isExpiringSoon = $fiveMinutesFromNow >= $result['expiresAt'];
            
            return [
                'hasToken' => true,
                'isExpired' => $isExpired,
                'isExpiringSoon' => $isExpiringSoon,
                'refreshToken' => $result['refreshToken'],
                'accessToken' => $result['accessToken'],
                'expiresAt' => $result['expiresAt']
            ];
            
        } catch (Exception $e) {
            error_log('Google Calendar: Failed to get token status from database: ' . $e->getMessage());
            return [
                'hasToken' => false,
                'isExpired' => true,
                'isExpiringSoon' => true,
                'refreshToken' => null
            ];
        }
    }
    

    
    /**
     * Update stored token in database
     */
    private function updateStoredToken($newToken) {
        try {
            require_once dirname(__DIR__) . '/configDatabase.php';
            
            $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Get user ID from session or current user
            $userId = $_SESSION['id'] ?? 1; // Default to user 1 if not in session
            
            $stmt = $pdo->prepare("
                UPDATE user_google_tokens 
                SET accessToken = ?, refreshToken = ?, expiresAt = ?, updatedAt = NOW()
                WHERE userId = ?
            ");
            
            $stmt->execute([
                $newToken['access_token'],
                $newToken['refresh_token'],
                time() + $newToken['expires_in'],
                $userId
            ]);
            
            return true;
        } catch (Exception $e) {
            error_log('Google Calendar: Failed to update stored token: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create a calendar event with Google Meet
     */
    public function createMeetingEvent($meetingData) {
        try {
            // Refresh token if needed before making API call
            if (!$this->refreshTokenIfNeeded()) {
                return [
                    'success' => false,
                    'error' => 'Token refresh failed. Please reconnect to Google Calendar.'
                ];
            }
            
            $event = new Event();
            
            // Set event title
            $event->setSummary('Meeting with ' . $meetingData['studentName'] . ' - Youni Choice');
            
            // Set event description
            $description = "Meeting between " . $meetingData['consultantName'] . " and " . $meetingData['studentName'] . "\n\n";
            $description .= "Student School: " . $meetingData['studentSchool'] . "\n";
            if (!empty($meetingData['meetingTopic']) && $meetingData['meetingTopic'] !== 'Not applicable') {
                $description .= "Topic: " . $meetingData['meetingTopic'] . "\n";
            }
            if (!empty($meetingData['meetingActivities']) && $meetingData['meetingActivities'] !== 'Not applicable') {
                $description .= "Activities: " . $meetingData['meetingActivities'] . "\n";
            }
            if (!empty($meetingData['meetingNotes'])) {
                $description .= "\nNotes: " . $meetingData['meetingNotes'];
            }
            
            $event->setDescription($description);
            
            // Set start time
            $startDateTime = new EventDateTime();
            $startDateTime->setDateTime($meetingData['meetingDate']);
            $startDateTime->setTimeZone(TIMEZONE);
            $event->setStart($startDateTime);
            
            // Set end time (default 1 hour duration)
            $endDateTime = new EventDateTime();
            $endTime = new DateTime($meetingData['meetingDate']);
            $endTime->add(new DateInterval('PT' . DEFAULT_MEETING_DURATION . 'M'));
            $endDateTime->setDateTime($endTime->format('c'));
            $endDateTime->setTimeZone(TIMEZONE);
            $event->setEnd($endDateTime);
            
            // Add Google Meet conference
            $conferenceData = new \Google\Service\Calendar\ConferenceData();
            $conferenceData->setCreateRequest(new \Google\Service\Calendar\CreateConferenceRequest());
            $conferenceData->getCreateRequest()->setRequestId(uniqid());
            $conferenceData->getCreateRequest()->setConferenceSolutionKey(new \Google\Service\Calendar\ConferenceSolutionKey());
            $conferenceData->getCreateRequest()->getConferenceSolutionKey()->setType('hangoutsMeet');
            $event->setConferenceData($conferenceData);
            
            // Add attendees
            $attendees = [];
            
            // Add consultant email if available
            if (!empty($meetingData['consultantEmail'])) {
                $attendees[] = ['email' => $meetingData['consultantEmail']];
            }
            
            // Add student email if available
            if (!empty($meetingData['studentEmail'])) {
                $attendees[] = ['email' => $meetingData['studentEmail']];
            }
            
            if (!empty($attendees)) {
                $event->setAttendees($attendees);
            }
            
            // Create the event
            $createdEvent = $this->service->events->insert(CALENDAR_ID, $event, [
                'conferenceDataVersion' => 1,
                'sendUpdates' => 'all'
            ]);
            
            // Extract Google Meet link
            $meetLink = '';
            if ($createdEvent->getConferenceData() && $createdEvent->getConferenceData()->getEntryPoints()) {
                foreach ($createdEvent->getConferenceData()->getEntryPoints() as $entryPoint) {
                    if ($entryPoint->getEntryPointType() === 'video') {
                        $meetLink = $entryPoint->getUri();
                        break;
                    }
                }
            }
            
            return [
                'success' => true,
                'eventId' => $createdEvent->getId(),
                'meetLink' => $meetLink,
                'eventLink' => $createdEvent->getHtmlLink()
            ];
            
        } catch (Exception $e) {
            error_log('Google Calendar API Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Update an existing calendar event
     */
    public function updateMeetingEvent($eventId, $meetingData) {
        try {
            // Refresh token if needed before making API call
            if (!$this->refreshTokenIfNeeded()) {
                return [
                    'success' => false,
                    'error' => 'Token refresh failed. Please reconnect to Google Calendar.'
                ];
            }
            
            $event = $this->service->events->get(CALENDAR_ID, $eventId);
            
            // Update event details
            $event->setSummary('Meeting with ' . $meetingData['studentName'] . ' - Youni Choice');
            
            // Update description
            $description = "Meeting between " . $meetingData['consultantName'] . " and " . $meetingData['studentName'] . "\n\n";
            $description .= "Student School: " . $meetingData['studentSchool'] . "\n";
            if (!empty($meetingData['meetingTopic']) && $meetingData['meetingTopic'] !== 'Not applicable') {
                $description .= "Topic: " . $meetingData['meetingTopic'] . "\n";
            }
            if (!empty($meetingData['meetingActivities']) && $meetingData['meetingActivities'] !== 'Not applicable') {
                $description .= "Activities: " . $meetingData['meetingActivities'] . "\n";
            }
            if (!empty($meetingData['meetingNotes'])) {
                $description .= "\nNotes: " . $meetingData['meetingNotes'];
            }
            
            $event->setDescription($description);
            
            // Update time if changed
            if (isset($meetingData['meetingDate'])) {
                $startDateTime = new EventDateTime();
                $startDateTime->setDateTime($meetingData['meetingDate']);
                $startDateTime->setTimeZone(TIMEZONE);
                $event->setStart($startDateTime);
                
                $endDateTime = new EventDateTime();
                $endTime = new DateTime($meetingData['meetingDate']);
                $endTime->add(new DateInterval('PT' . DEFAULT_MEETING_DURATION . 'M'));
                $endDateTime->setDateTime($endTime->format('c'));
                $endDateTime->setTimeZone(TIMEZONE);
                $event->setEnd($endDateTime);
            }
            
            $updatedEvent = $this->service->events->update(CALENDAR_ID, $eventId, $event);
            
            return [
                'success' => true,
                'eventId' => $updatedEvent->getId(),
                'meetLink' => $updatedEvent->getConferenceData()->getEntryPoints()[0]->getUri(),
                'eventLink' => $updatedEvent->getHtmlLink()
            ];
            
        } catch (Exception $e) {
            error_log('Google Calendar API Update Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete a calendar event
     */
    public function deleteMeetingEvent($eventId) {
        try {
            // Refresh token if needed before making API call
            if (!$this->refreshTokenIfNeeded()) {
                return [
                    'success' => false,
                    'error' => 'Token refresh failed. Please reconnect to Google Calendar.'
                ];
            }
            
            $this->service->events->delete(CALENDAR_ID, $eventId);
            return ['success' => true];
        } catch (Exception $e) {
            error_log('Google Calendar API Delete Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Check if access token is valid and refresh if needed
     */
    public function isTokenValid() {
        try {
            // First try to refresh token if needed
            if (!$this->refreshTokenIfNeeded()) {
                return false;
            }
            
            // Load the current access token from database
            $tokenStatus = $this->getCurrentTokenStatus();
            if (!$tokenStatus['hasToken'] || $tokenStatus['isExpired']) {
                return false;
            }
            
            // Set the access token for the client
            $this->client->setAccessToken($tokenStatus['accessToken']);
            
            // Try to make a simple API call to check token validity
            $this->service->calendarList->listCalendarList(['maxResults' => 1]);
            return true;
        } catch (Exception $e) {
            error_log('Google Calendar: Token validation failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user's Google Calendar connection status
     */
    public function getConnectionStatus($userId = null) {
        if (!$userId) {
            $userId = $_SESSION['id'] ?? null;
        }
        
        if (!$userId) {
            return [
                'connected' => false,
                'message' => 'No user ID available'
            ];
        }
        
        try {
            require_once dirname(__DIR__) . '/configDatabase.php';
            
            $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $pdo->prepare("SELECT accessToken, refreshToken, expiresAt FROM user_google_tokens WHERE userId = ?");
            $stmt->execute([$userId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return [
                    'connected' => false,
                    'message' => 'No Google Calendar connection found'
                ];
            }
            
            $hasRefreshToken = !empty($result['refreshToken']);
            $isExpired = time() >= $result['expiresAt'];
            
            return [
                'connected' => true,
                'hasRefreshToken' => $hasRefreshToken,
                'isExpired' => $isExpired,
                'canRefresh' => $hasRefreshToken && $isExpired,
                'message' => $isExpired ? 'Token expired but can be refreshed' : 'Token is valid'
            ];
            
        } catch (Exception $e) {
            error_log('Google Calendar: Failed to get connection status: ' . $e->getMessage());
            return [
                'connected' => false,
                'message' => 'Error checking connection status'
            ];
        }
    }
    
    /**
     * Get token status for debugging
     */
    public function getTokenStatus() {
        $tokenStatus = $this->getCurrentTokenStatus();
        
        return [
            'hasToken' => $tokenStatus['hasToken'],
            'isExpired' => $tokenStatus['isExpired'],
            'isExpiringSoon' => $tokenStatus['isExpiringSoon'],
            'hasRefreshToken' => !empty($tokenStatus['refreshToken']),
            'expiresAt' => $tokenStatus['expiresAt'] ?? 'unknown',
            'message' => $tokenStatus['hasToken'] ? 
                ($tokenStatus['isExpired'] ? 'Token is expired' : 
                ($tokenStatus['isExpiringSoon'] ? 'Token expires soon' : 'Token is valid')) : 
                'No token available'
        ];
    }
}
?>