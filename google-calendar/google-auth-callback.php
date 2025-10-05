<?php
session_start();
require_once dirname(__DIR__) . '/configDatabase.php';
require_once __DIR__ . '/google-calendar-helper.php';

if (!isset($_SESSION['type'])) {
    header("location: ../index.php");
    die();
}

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    $googleHelper = new GoogleCalendarHelper();
    
    try {
        $token = $googleHelper->exchangeCodeForToken($code);
        
        if (isset($token['access_token'])) {
            // Store the access token in the database for the current user
            $userId = $_SESSION['id'];
            $accessToken = $token['access_token'];
            $refreshToken = isset($token['refresh_token']) ? $token['refresh_token'] : '';
            $expiresIn = isset($token['expires_in']) ? $token['expires_in'] : 0;
            $expiresAt = time() + $expiresIn;
            
            // Log token information for debugging
            error_log('Google Auth: Access token received. Refresh token: ' . ($refreshToken ? 'YES' : 'NO'));
            
            // Check if user already has a Google token record
            $sqlCheck = "SELECT * FROM user_google_tokens WHERE userId = '$userId'";
            $resultCheck = mysqli_query($link, $sqlCheck);
            
            if (mysqli_num_rows($resultCheck) > 0) {
                // Update existing record - preserve refresh token if new one is empty
                if (empty($refreshToken)) {
                    // Get existing refresh token
                    $existingToken = mysqli_fetch_assoc($resultCheck);
                    $refreshToken = $existingToken['refreshToken'];
                }
                
                $sqlUpdate = "UPDATE user_google_tokens SET 
                    accessToken = '$accessToken', 
                    refreshToken = '$refreshToken', 
                    expiresAt = '$expiresAt', 
                    updatedAt = NOW() 
                    WHERE userId = '$userId'";
                $updateResult = mysqli_query($link, $sqlUpdate);
                
                if (!$updateResult) {
                    error_log('Google Auth: Failed to update token in database: ' . mysqli_error($link));
                }
            } else {
                // Insert new record
                $sqlInsert = "INSERT INTO user_google_tokens 
                    (userId, accessToken, refreshToken, expiresAt, createdAt, updatedAt) 
                    VALUES ('$userId', '$accessToken', '$refreshToken', '$expiresAt', NOW(), NOW())";
                $insertResult = mysqli_query($link, $sqlInsert);
                
                if (!$insertResult) {
                    error_log('Google Auth: Failed to insert token in database: ' . mysqli_error($link));
                }
            }
            
            $_SESSION['google_calendar_connected'] = true;
            $_SESSION['google_access_token'] = $accessToken;
            
            // Redirect back to the add meeting page or show success message
            if (isset($_SESSION['return_to_meeting'])) {
                $returnUrl = $_SESSION['return_to_meeting'];
                unset($_SESSION['return_to_meeting']);
                header("location: $returnUrl");
            } else {
                // Preserve studentId if it was stored in session
                $redirectUrl = "../addMeeting.php?google_connected=1";
                if (isset($_SESSION['google_auth_student_id'])) {
                    $redirectUrl .= "&studentId=" . $_SESSION['google_auth_student_id'];
                    unset($_SESSION['google_auth_student_id']);
                }
                header("location: $redirectUrl");
            }
            die();
        } else {
            throw new Exception('Failed to get access token');
        }
    } catch (Exception $e) {
        error_log('Google Auth Error: ' . $e->getMessage());
        $redirectUrl = "../addMeeting.php?google_error=1";
        if (isset($_SESSION['google_auth_student_id'])) {
            $redirectUrl .= "&studentId=" . $_SESSION['google_auth_student_id'];
            unset($_SESSION['google_auth_student_id']);
        }
        header("location: $redirectUrl");
        die();
    }
} else {
    // No authorization code received
    $redirectUrl = "../addMeeting.php?google_error=2";
    if (isset($_SESSION['google_auth_student_id'])) {
        $redirectUrl .= "&studentId=" . $_SESSION['google_auth_student_id'];
        unset($_SESSION['google_auth_student_id']);
    }
    header("location: $redirectUrl");
    die();
}
?> 