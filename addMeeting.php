
<?php

    session_start();
    require_once "configDatabase.php";
    require_once "google-calendar/google-calendar-helper.php";


    if (!isset($_SESSION['type'])) { // testez daca userul est logat
        header("location: index.php");
        die();
    }
    else {
        $typeAccount = $_SESSION["type"];
        $accountId = $_SESSION["id"];
    }

    if (isset($_GET['studentId'])) // testez daca e setat un student
        $studentId = $_GET['studentId'];
    else {
        header("location: index.php");
        die();
    }

    $sqlStudentData = "SELECT * FROM studentData WHERE `studentId` = " .$studentId;
    
    $queryStudentData = mysqli_query($link, $sqlStudentData);

    if (mysqli_num_rows($queryStudentData) > 0) // testez daca exista vreun student cu id-ul dat
        $dataStudent = mysqli_fetch_assoc($queryStudentData);
    else {
        header("location: index.php");
        die();
    }

    if (!($dataStudent['consultantId'] == $accountId || $typeAccount == 1)) { // testez daca are acces userul la studentul dat
        header("location: index.php");
        die();
    }

    $meegtingNotes = "";
    $meetingDate = "";

    $consultantId = $dataStudent['consultantId'];
    $studentName = $dataStudent['name'];
    $studentSchool = $dataStudent['highSchool'];
    $consultantName = $dataStudent['consultantName'];
    
    // Get consultant and student email addresses
    $consultantEmail = '';
    $studentEmail = '';
    
    // Get consultant email
    $sqlConsultant = "SELECT email FROM users WHERE userId = '$consultantId'";
    $queryConsultant = mysqli_query($link, $sqlConsultant);
    if ($queryConsultant && mysqli_num_rows($queryConsultant) > 0) {
        $consultantData = mysqli_fetch_assoc($queryConsultant);
        $consultantEmail = $consultantData['email'];
    }
    
    // Get student email
    $sqlStudentEmail = "SELECT email FROM studentData WHERE studentId = '$studentId'";
    $queryStudentEmail = mysqli_query($link, $sqlStudentEmail);
    if ($queryStudentEmail && mysqli_num_rows($queryStudentEmail) > 0) {
        $studentData = mysqli_fetch_assoc($queryStudentEmail);
        $studentEmail = $studentData['email'];
    }

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        // Set timezone to Romania
        date_default_timezone_set('Europe/Bucharest');
        
        $time = $_POST['meeting-time']; // Get user-selected time (HH:MM format)
        $selectedDate = $_POST['meeting-date']; // Get user-selected date
        $meetingDate = $selectedDate . ' ' . $time . ':00'; // Combine date and time into a full timestamp
        
        $currentDateTime = new DateTime();
        $meetingDateTime = new DateTime($meetingDate);
        
        if ($meetingDateTime > $currentDateTime) {
            // Future meeting - no details needed
            $meetingNotes = "";
            $meetingTopic = "Not applicable";
            $stringActivities = "Not applicable";
            
            // Check if there are already 2 or more future meetings
            $sqlCheckFutureMeetings = "SELECT COUNT(*) as futureCount FROM meetings WHERE studentId = '$studentId' AND meetingDate > NOW()";
            $queryCheckFutureMeetings = mysqli_query($link, $sqlCheckFutureMeetings);
            $futureMeetingsCount = 0;
            
            if ($queryCheckFutureMeetings) {
                $row = mysqli_fetch_assoc($queryCheckFutureMeetings);
                $futureMeetingsCount = $row['futureCount'];
            }
            
            if ($futureMeetingsCount >= 2) {
                $errorMessage = "Maximum of 2 future meetings allowed. Please schedule meetings after existing ones are completed.";
            } else {
                // Check if Google Calendar integration is enabled
                $enableGoogleMeet = isset($_POST['enable-google-meet']) ? true : false;
                $disableGoogleMeet = isset($_POST['disable-google-meet']) ? true : false;
                
                $googleEventId = null;
                $googleMeetLink = null;
                $googleCalendarLink = null;
                
                // Only create Google Calendar event if enabled and not disabled
                if ($enableGoogleMeet && !$disableGoogleMeet) {
                    // Get user's Google access token
                    $userId = $_SESSION['id'];
                    $sqlToken = "SELECT accessToken, expiresAt FROM user_google_tokens WHERE userId = '$userId'";
                    $queryToken = mysqli_query($link, $sqlToken);
                    
                    if ($queryToken && mysqli_num_rows($queryToken) > 0) {
                        $tokenData = mysqli_fetch_assoc($queryToken);
                        $accessToken = $tokenData['accessToken'];
                        $expiresAt = $tokenData['expiresAt'];
                        
                        // Check if token is still valid
                        if (time() < $expiresAt) {
                            $googleHelper = new GoogleCalendarHelper($accessToken);
                            
                            // Prepare meeting data for Google Calendar
                            // Convert meeting date to ISO 8601 format for Google Calendar API
                            $meetingDateTime = new DateTime($meetingDate, new DateTimeZone('Europe/Bucharest'));
                            $meetingData = [
                                'studentName' => $studentName,
                                'consultantName' => $consultantName,
                                'studentSchool' => $studentSchool,
                                'meetingDate' => $meetingDateTime->format('c'), // ISO 8601 format
                                'meetingTopic' => $meetingTopic,
                                'meetingActivities' => $stringActivities,
                                'meetingNotes' => $meetingNotes,
                                'consultantEmail' => $consultantEmail,
                                'studentEmail' => $studentEmail
                            ];
                            
                            $googleResult = $googleHelper->createMeetingEvent($meetingData);
                            
                            if ($googleResult['success']) {
                                $googleEventId = $googleResult['eventId'];
                                $googleMeetLink = $googleResult['meetLink'];
                                $googleCalendarLink = $googleResult['eventLink'];
                            } else {
                                error_log('Google Calendar creation failed: ' . $googleResult['error']);
                                // Continue without Google Meet if there's an error
                            }
                        } else {
                            // Token expired, need to refresh or re-authenticate
                            error_log('Google Calendar token expired for user: ' . $userId);
                        }
                    } else {
                        // No Google token found
                        error_log('No Google Calendar token found for user: ' . $userId);
                    }
                }
                
                $sqlAddMeeting = "INSERT INTO meetings (`studentId`, `consultantId`, `studentName`, `studentSchool`, `consultantName`, `meetingDate`, `meetingNotes`, `meetingTopic`, `meetingActivities`, `googleEventId`, `googleMeetLink`, `googleCalendarLink`) VALUES ('$studentId', '$consultantId', '$studentName', '$studentSchool', '$consultantName','$meetingDate', '$meetingNotes', '$meetingTopic', '$stringActivities', " . 
                    ($googleEventId ? "'$googleEventId'" : "NULL") . ", " .
                    ($googleMeetLink ? "'$googleMeetLink'" : "NULL") . ", " .
                    ($googleCalendarLink ? "'$googleCalendarLink'" : "NULL") . ")";

                // Execute the query
                $result = mysqli_query($link, $sqlAddMeeting);

                if ($result) {
                    // Get the last inserted ID
                    $meetingId = mysqli_insert_id($link);
                    header("location: meeting.php?meetingId=$meetingId");
                    die();
                } else {
                    $errorMessage = "There has been an error in processing your request.";
                }
            }
        } else {
            // Past meeting - details are required
            $meetingNotes = $_POST['meeting-notes'] ?? "";
            $meetingTopic = $_POST['topic'] ?? "Not applicable";
            $selectedActivities = $_POST['activities'] ?? []; // Retrieves selected values as an array

            $stringActivities = "";
            if (!empty($selectedActivities)) {
                foreach ($selectedActivities as $activity) {
                    $stringActivities .= $activity;
                    $stringActivities .= " + ";
                }
                $stringActivities = substr($stringActivities, 0, -3);
            } else {
                $stringActivities = "Not applicable";
            }

            // Check if Google Calendar integration is enabled for past meetings
            $enableGoogleMeet = isset($_POST['enable-google-meet']) ? true : false;
            $disableGoogleMeet = isset($_POST['disable-google-meet']) ? true : false;
            
            $googleEventId = null;
            $googleMeetLink = null;
            $googleCalendarLink = null;
            
            // Only create Google Calendar event if enabled and not disabled
            if ($enableGoogleMeet && !$disableGoogleMeet) {
                // Get user's Google access token
                $userId = $_SESSION['id'];
                $sqlToken = "SELECT accessToken, expiresAt FROM user_google_tokens WHERE userId = '$userId'";
                $queryToken = mysqli_query($link, $sqlToken);
                
                if ($queryToken && mysqli_num_rows($queryToken) > 0) {
                    $tokenData = mysqli_fetch_assoc($queryToken);
                    $accessToken = $tokenData['accessToken'];
                    $expiresAt = $tokenData['expiresAt'];
                    
                    // Check if token is still valid
                    if (time() < $expiresAt) {
                        $googleHelper = new GoogleCalendarHelper($accessToken);
                        
                        // Prepare meeting data for Google Calendar
                        // Convert meeting date to ISO 8601 format for Google Calendar API
                        $meetingDateTime = new DateTime($meetingDate, new DateTimeZone('Europe/Bucharest'));
                        $meetingData = [
                            'studentName' => $studentName,
                            'consultantName' => $consultantName,
                            'studentSchool' => $studentSchool,
                            'meetingDate' => $meetingDateTime->format('c'), // ISO 8601 format
                            'meetingTopic' => $meetingTopic,
                            'meetingActivities' => $stringActivities,
                            'meetingNotes' => $meetingNotes,
                            'consultantEmail' => $consultantEmail,
                            'studentEmail' => $studentEmail
                        ];
                        
                        $googleResult = $googleHelper->createMeetingEvent($meetingData);
                        
                        if ($googleResult['success']) {
                            $googleEventId = $googleResult['eventId'];
                            $googleMeetLink = $googleResult['meetLink'];
                            $googleCalendarLink = $googleResult['eventLink'];
                        } else {
                            error_log('Google Calendar creation failed: ' . $googleResult['error']);
                            // Continue without Google Meet if there's an error
                        }
                    } else {
                        // Token expired, need to refresh or re-authenticate
                        error_log('Google Calendar token expired for user: ' . $userId);
                    }
                } else {
                    // No Google token found
                    error_log('No Google Calendar token found for user: ' . $userId);
                }
            }
            
            $sqlAddMeeting = "INSERT INTO meetings (`studentId`, `consultantId`, `studentName`, `studentSchool`, `consultantName`, `meetingDate`, `meetingNotes`, `meetingTopic`, `meetingActivities`, `googleEventId`, `googleMeetLink`, `googleCalendarLink`) VALUES ('$studentId', '$consultantId', '$studentName', '$studentSchool', '$consultantName','$meetingDate', '$meetingNotes', '$meetingTopic', '$stringActivities', " . 
                ($googleEventId ? "'$googleEventId'" : "NULL") . ", " .
                ($googleMeetLink ? "'$googleMeetLink'" : "NULL") . ", " .
                ($googleCalendarLink ? "'$googleCalendarLink'" : "NULL") . ")";

            // Execute the query
            $result = mysqli_query($link, $sqlAddMeeting);

            if ($result) {
                // Get the last inserted ID
                $meetingId = mysqli_insert_id($link);
                
                // Handle tasks if any were added
                if (isset($_POST['tasks']) && is_array($_POST['tasks'])) {
                    foreach ($_POST['tasks'] as $index => $task) {
                        if (!empty($task['text'])) {
                            $taskText = mysqli_real_escape_string($link, $task['text']);
                            $taskDeadline = (isset($task['deadline']) && $task['deadline'] !== '' && $task['deadline'] !== null) ? "'" . mysqli_real_escape_string($link, $task['deadline']) . "'" : "NULL";
                            
                            // Debug: Create a simple log file to track the data
                            $debugLog = "Task $index - Raw deadline: '" . (isset($task['deadline']) ? $task['deadline'] : 'NOT_SET') . "'\n";
                            $debugLog .= "Task $index - Text: $taskText, Deadline: " . ($taskDeadline === "NULL" ? "NULL" : $taskDeadline) . "\n";
                            file_put_contents('task_debug.log', $debugLog, FILE_APPEND);
                            
                            $sqlAddTask = "INSERT INTO tasks (`studentId`, `taskText`, `taskDeadline`, `taskStatus`, `meetingId`) VALUES ('$studentId', '$taskText', $taskDeadline, 'In Progress', '$meetingId')";
                            $taskResult = mysqli_query($link, $sqlAddTask);
                            
                            if (!$taskResult) {
                                // Task insertion failed - you might want to add proper error handling
                                file_put_contents('task_debug.log', "Task $index insertion failed: " . mysqli_error($link) . "\n", FILE_APPEND);
                            } else {
                                file_put_contents('task_debug.log', "Task $index inserted successfully\n", FILE_APPEND);
                            }
                        }
                    }
                } else {
                    file_put_contents('task_debug.log', "No tasks found in POST data\n", FILE_APPEND);
                }
                
                header("location: meeting.php?meetingId=$meetingId");
                die();
            } else {
                $errorMessage = "There has been an error in processing your request.";
            }
        }
    }

    $topics = ["Exams & Academic Planning", "Vocational Planning", "Summer Camps", "Essay & Creative Writing", "CV", "Passion Projects", "Activities", "Strategy", "Interview", "Parent & Kid", "Parent", "Financial Aid and Scholarships", "Internships", "University Application"];
    $nTopics = 14;
    
    for ($i = 0; $i < $nTopics; $i = $i + 1) 
        $topics[$i] = trim($topics[$i]);

    $activities = ["Descriere Proces", "Q&A", "Goals and intro", "Workshop 20 de lucruri", "MiniQuiz English", "Workshop Vise", "Eseu 1 Million", "Eseu longest line", "Consultant Prezinta Planingul & Proces", "Prezentare Internships", "Prezentare Voluntariate", "Discutie summercamps", "Prezentare propuneri activitati", "Intro CV Building", "First Draft CV", "Workshop tree", "Workshop essays 1", "Workshop essays 2", "Apply to internships & Shadowing", "Apply to volunteer Activites", "Present Summercamps", "Logistics", "Essays", "Application", "Ai Quizz", "Workshop passion Projects", "Domain Workshop", "Structure", "Activities", "Planning", "MiniQuiz Math", "Workshop Sale Yourself", "Workshop Pitch", "Interview Prep", "Self-Presentation", "Youni Needs", "Discover Youni Ecosystem", "Present Results", "Feedback & Needs", "Next Goals", "Inform Parent for Financial Aid", "Future Costs", "Establish Budget"];
    $nActivities = 43; 

    for ($i = 0; $i < $nActivities; $i = $i + 1)
        $activities[$i] = trim($activities[$i]);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
    <script src="https://unpkg.com/react-phone-number-input@3.x/bundle/react-phone-number-input.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/react-phone-number-input@3.x/bundle/style.css"/>

    <title>Add meeting</title>
    <style>
    .dropdown-activities {
        position: relative;
        display: inline-block;
        width: 250px;
    }

    .dropdown-btn {
        background-color: #f9f9f9;
        border: 1px solid #ccc;
        padding: 10px;
        width: 100%;
        text-align: left;
        cursor: pointer;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #fff;
        min-width: 250px;
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #ccc;
        z-index: 1;
    }

    .dropdown-content label {
        display: block;
        padding: 8px;
        cursor: pointer;
    }

    .dropdown-content input {
        margin-right: 10px;
    }

    .show {
        display: block;
    }

        #content {
            width: 70%;
            margin: auto;
        }
        #search-bar {
            background-image: url('/css/searchicon.png');
            background-position: 10px 12px;
            background-repeat: no-repeat;
            width: 100%;
            font-size: 16px;
            padding: 12px 20px 12px 40px;
            border: 1px solid #ddd;
            margin-bottom: 12px;
        }
        .full-name {
            font-weight: bold;
        }

        .navbar {
            height: 150px;
        }

        .badge {
            /* height: 30px; */
            font-size: 15px;
            color: white;
            background-color: var(--pink) !important;
            position: fixed;
            right: 50%;
        }
        
        .fw-bold {
            font-weight: bold;
        }

        .student-info {
            font-size: 18px;
            font-weight: bold;
        }

        .title-info {
            font-weight: bold;
            color: var(--pink);
            font-size: 20px;
        }

        .info-row {
            display: inline; /* the default for span */
        }

        .navbar {
            height: 150px;
        }

        .statusSelect {
            width: 100px;
            height: 25px;
        }

        input[name = "meeting-title"] {
            width: 40%;
        }

        input[name = "highSchool"] {
            width: 60%;
        }

        input[name = "phoneNumber"] {
            width: 40%;
        }

        input[name = "email"] {
            width: 50%;
        }

        .invalidPhoneNumber {
            color: red;
        }

        .validPhoneNumber {
            color: green;
        }

        input, select {
            border-radius: 10px; /* Adjust the value to control the roundness */
            padding: 8px 12px; /* Adjust padding as needed */
            border: 1px solid #ccc; /* Add a border for visual distinction */
        }

        .formfield {
            display: flex;
            align-items: center;
        }

        /* Tasks Section Styles */
        .meeting-tasks-section {
            margin: 20px 0;
        }

        .tasks-container {
            margin-left: 20px;
        }

        .tasks-list {
            margin-top: 15px;
        }

        .task-item {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 10px;
            padding: 12px;
            background-color: #f8f9fa;
            position: relative;
            display: flex;
            align-items: center;
        }

        .task-text {
            flex: 1;
            text-align: left;
            margin-right: 16px;
            word-wrap: break-word;
        }

        .task-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .task-deadline {
            color: #6c757d;
            font-size: 0.9em;
        }

        .edit-task-form {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        .mb-3 {
            margin-bottom: 1rem;
        }
    </style>

  </head>


  
  
  <?php include("navbar.php"); ?>


<div id = "content">
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>

    <h1 style = "color: rgba(79, 35, 95, .9);"> Add meeting for <?php echo $dataStudent['name']; ?> </h1>

    <br>
    <br>

    <?php if (isset($errorMessage)) { ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
        <br>
    <?php } ?>
    
    <?php if (isset($_GET['google_connected'])) { ?>
        <div class="alert alert-success" role="alert">
            <strong>Success!</strong> Google Calendar has been connected successfully.
        </div>
        <br>
    <?php } ?>
    
    <?php if (isset($_GET['google_error'])) { ?>
        <div class="alert alert-warning" role="alert">
            <strong>Google Calendar Error:</strong> There was an issue connecting to Google Calendar. Please try again.
        </div>
        <br>
    <?php } ?>
    
    <!-- Google Calendar Integration Status -->
    <?php
    $googleHelper = new GoogleCalendarHelper();
    $connectionStatus = $googleHelper->getConnectionStatus();
    $googleConnected = $connectionStatus['connected'];
    $tokenExpired = $connectionStatus['isExpired'];
    $canRefresh = $connectionStatus['canRefresh'];
    ?>

    <form method = "post">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-alt"></i> Google Calendar Integration
                </h5>
            </div>
            <div class="card-body">
                <?php if ($googleConnected && !$tokenExpired): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle"></i> Google Calendar is connected and ready to use.
                        <?php if ($connectionStatus['hasRefreshToken']): ?>
                            <br><small><i class="fas fa-shield-alt"></i> Persistent authentication enabled</small>
                        <?php endif; ?>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="enable-google-meet" name="enable-google-meet" checked>
                        <label class="form-check-label" for="enable-google-meet">
                            Create Google Meet link and add to calendar
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="disable-google-meet" name="disable-google-meet">
                        <label class="form-check-label" for="disable-google-meet">
                            Create meeting without Google Calendar integration
                        </label>
                    </div>
                <?php elseif ($googleConnected && $tokenExpired && $canRefresh): ?>
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> Google Calendar connection has expired. Click the button below to refresh it.
                    </div>
                    <a href="google-calendar/test_force_refresh.php" class="btn btn-warning" target="_blank">
                        <i class="fas fa-sync-alt"></i> Refresh Google Calendar Connection
                    </a>
                    <br><br>
                    <a href="<?php echo (new GoogleCalendarHelper())->getAuthUrl(isset($_GET['studentId']) ? $_GET['studentId'] : null); ?>" class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> Reconnect Google Calendar
                    </a>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="disable-google-meet" name="disable-google-meet">
                        <label class="form-check-label" for="disable-google-meet">
                            Create meeting without Google Calendar integration
                        </label>
                    </div>
                <?php elseif ($googleConnected && $tokenExpired && !$canRefresh): ?>
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> Google Calendar connection has expired and cannot be refreshed automatically. Please reconnect.
                    </div>
                    <a href="<?php echo (new GoogleCalendarHelper())->getAuthUrl(isset($_GET['studentId']) ? $_GET['studentId'] : null); ?>" class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> Reconnect Google Calendar
                    </a>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="disable-google-meet" name="disable-google-meet">
                        <label class="form-check-label" for="disable-google-meet">
                            Create meeting without Google Calendar integration
                        </label>
                    </div>
                <?php elseif ($googleConnected && $tokenExpired): ?>
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> Google Calendar connection has expired. Please reconnect.
                    </div>
                    <a href="<?php echo (new GoogleCalendarHelper())->getAuthUrl(isset($_GET['studentId']) ? $_GET['studentId'] : null); ?>" class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> Reconnect Google Calendar
                    </a>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="disable-google-meet" name="disable-google-meet">
                        <label class="form-check-label" for="disable-google-meet">
                            Create meeting without Google Calendar integration
                        </label>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle"></i> Connect your Google Calendar to automatically create meetings with Google Meet links.
                        <br><small><i class="fas fa-shield-alt"></i> Your connection will be persistent and won't require frequent re-authentication.</small>
                    </div>
                    <a href="<?php echo (new GoogleCalendarHelper())->getAuthUrl(isset($_GET['studentId']) ? $_GET['studentId'] : null); ?>" class="btn btn-primary">
                        <i class="fab fa-google"></i> Connect Google Calendar
                    </a>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="disable-google-meet" name="disable-google-meet">
                        <label class="form-check-label" for="disable-google-meet">
                            Create meeting without Google Calendar integration
                        </label>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class = "formfield">
            <label for="meeting-date" class = "title-info"> Select meeting's date:</label> &nbsp;
            <input type="date" name="meeting-date" id="meeting-date" required>
        </div>
        <br>
        <div class = "formfield">
            <label for="meeting-time" class = "title-info"> Select meeting's time:</label> &nbsp;
            <input type="time" name="meeting-time" id="meeting-time" required>
        </div>
        <br>
        <br>

        <!-- Meeting type indicator -->
        <div id="meeting-type-indicator" class="alert alert-success" role="alert" style="display: none; margin-bottom: 20px;">
            <strong>Scheduling Future Meeting:</strong> You can add details after the meeting takes place.
        </div>

        <!-- Meeting details section - only shown for past meetings -->
        <div id="meeting-details-section" style="display: none;">
            <div class="alert alert-info" role="alert" style="margin-bottom: 20px;">
                <strong>Recording Past Meeting:</strong> Please fill in the details about what was discussed during this meeting.
            </div>
            <div class="formfield">
                <label for="textarea" class = "title-info">  Meeting Notes:  </label> &nbsp;
                <textarea name = "meeting-notes" id="textarea" rows="8" cols = "50"></textarea>
            </div>
            <br>

            <br>

            <p class = "topic-info"> <span class = "title-info"> Meeting Topic: </span> 
                <select id="topic" name="topic">
                    <option value="" disabled selected hidden>Select Topic</option>
                    <?php 
                        for ($i = 0; $i < $nTopics; $i++) {
                            $topics[$i] = trim($topics[$i]); // elimin spatiile din statusArray[i]
                            ?>
                            <option value="<?php echo $topics[$i];?> "><?php echo $topics[$i];?></option> <?php
                        }
                    ?>
                </select>
             </p>
            
             <br>
            
             <span class="title-info">Meeting Activities:</span> 
             <div class="dropdown-activities">
                <button type="button" class="dropdown-btn" id="dropdown-btn">Select Activities</button>
                <div class="dropdown-content" id="dropdown-content">
                    <?php 
                        for ($i = 0; $i < $nActivities; $i++) {
                            $activities[$i] = trim($activities[$i]); // Elimină spațiile din activități
                    ?>
                        <label>
                            <input type="checkbox" name="activities[]" value="<?php echo $activities[$i]; ?>"> 
                            <?php echo $activities[$i]; ?>
                        </label>
                    <?php
                        }
                    ?>
                </div>
            </div>
            
            <!-- Tasks Section for Past Meetings -->
            <div class="meeting-tasks-section">
                <p class="title-info">Meeting Tasks:</p>
                <div class="tasks-container">
                    <div class="add-task-section mb-3">
                        <button type="button" class="btn btn-success btn-sm" onclick="showAddTaskForm()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                            </svg> Add Task
                        </button>
                    </div>
                    
                    <div id="add-task-form" style="display: none;" class="mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Add New Task</h6>
                                <div id="task-form">
                                    <div class="form-group">
                                        <label for="taskText">Task Description:</label>
                                        <textarea class="form-control" id="taskText" name="taskText" rows="3"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="taskDeadline">Deadline (optional):</label>
                                        <input type="date" class="form-control" id="taskDeadline" name="taskDeadline">
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-primary btn-sm" onclick="addTaskToList()">Add to List</button>
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="hideAddTaskForm()">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="meeting-tasks-list" class="tasks-list">
                        <!-- Tasks will be added here temporarily before meeting creation -->
                    </div>
                </div>
            </div>
        </div>

        <br>
        <br>
        <input class="btn btn-primary" type="submit" name = "submit" value="Add meeting">
    </form>



    <br>
    <br>


    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script>
    // Task management for add meeting form
    let meetingTasks = [];
    let taskCounter = 0;

    function showAddTaskForm() {
        document.getElementById('add-task-form').style.display = 'block';
        document.getElementById('taskText').focus();
    }

    function hideAddTaskForm() {
        document.getElementById('add-task-form').style.display = 'none';
        // Reset the form fields manually since task-form is now a div
        document.getElementById('taskText').value = '';
        document.getElementById('taskDeadline').value = '';
    }

    function addTaskToList() {
        const taskText = document.getElementById('taskText').value.trim();
        const taskDeadline = document.getElementById('taskDeadline').value;

        console.log('Adding task with deadline:', taskDeadline);

        if (!taskText) {
            alert('Task description is required.');
            return;
        }

        const taskId = 'temp_' + taskCounter++;
        const task = {
            id: taskId,
            text: taskText,
            deadline: taskDeadline
        };

        console.log('Created task object:', task);

        meetingTasks.push(task);
        displayMeetingTasks();
        hideAddTaskForm();
    }

    function displayMeetingTasks() {
        const tasksList = document.getElementById('meeting-tasks-list');
        
        if (meetingTasks.length === 0) {
            tasksList.innerHTML = '<p class="text-muted">No tasks added yet.</p>';
            return;
        }

        const tasksHtml = meetingTasks.map(task => {
            const deadlineText = task.deadline ? 
                `Deadline: ${formatDate(task.deadline)}` : 
                'No deadline';

            return `
                <div class="task-item">
                    <span class="task-text">${escapeHtml(task.text)}</span>
                    <div class="task-actions">
                        <small class="task-deadline">${deadlineText}</small>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTask('${task.id}')" title="Remove Task">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 1-1 0v6a.5.5 0 0 1 1 0V6z"/>
                                <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4.5 4.5V6H13V4.5L13.382 4H4.118zM2.5 7V5h11v2H2.5z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            `;
        }).join('');

        tasksList.innerHTML = tasksHtml;
    }

    function removeTask(taskId) {
        meetingTasks = meetingTasks.filter(task => task.id !== taskId);
        displayMeetingTasks();
    }

    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        const day = date.getDate().toString().padStart(2, '0');
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Modify form submission to include tasks
    document.querySelector('form').addEventListener('submit', function(e) {
        console.log('Form submission - meetingTasks:', meetingTasks);
        console.log('Form submission - meetingTasks length:', meetingTasks.length);
        
        // Add hidden inputs for tasks
        meetingTasks.forEach((task, index) => {
            console.log(`Adding task ${index} to form:`, task);
            console.log(`Task ${index} deadline value:`, task.deadline);
            console.log(`Task ${index} deadline type:`, typeof task.deadline);
            
            const textInput = document.createElement('input');
            textInput.type = 'hidden';
            textInput.name = `tasks[${index}][text]`;
            textInput.value = task.text;
            this.appendChild(textInput);

            const deadlineInput = document.createElement('input');
            deadlineInput.type = 'hidden';
            deadlineInput.name = `tasks[${index}][deadline]`;
            deadlineInput.value = task.deadline;
            this.appendChild(deadlineInput);
            
            console.log(`Added hidden inputs for task ${index} - text: "${task.text}" deadline: "${task.deadline}"`);
        });
    });

    // Set timezone to Romania for client-side validation
    const now = new Date();
    const romaniaTime = new Date(now.toLocaleString("en-US", {timeZone: "Europe/Bucharest"}));
    
    // Set default time to next hour
    const defaultTime = new Date(romaniaTime.getTime() + 60 * 60 * 1000); // Add 1 hour
    const timeString = defaultTime.toTimeString().slice(0, 5);
    document.getElementById("meeting-time").value = timeString;
    
    // Allow past dates - no minimum date restriction
    // const today = romaniaTime.toISOString().split('T')[0];
    // document.getElementById("meeting-date").min = today;
    
    // Function to check if meeting is in the past and show/hide details section
    function checkMeetingTime() {
        const dateInput = document.getElementById("meeting-date");
        const timeInput = document.getElementById("meeting-time");
        const detailsSection = document.getElementById("meeting-details-section");
        const typeIndicator = document.getElementById("meeting-type-indicator");
        const submitBtn = document.querySelector('input[type="submit"]');
        
        if (dateInput.value && timeInput.value) {
            const selectedDateTime = new Date(dateInput.value + 'T' + timeInput.value);
            const currentDateTime = new Date();
            
            if (selectedDateTime <= currentDateTime) {
                // Meeting is in the past - show details section
                detailsSection.style.display = 'block';
                typeIndicator.style.display = 'none';
                submitBtn.disabled = false;
                submitBtn.title = "Recording a past meeting - please fill in the details below";
                submitBtn.value = "Add Past Meeting";
                
                // Make required fields optional for past meetings
                document.getElementById("textarea").required = false;
                document.getElementById("topic").required = false;
                
                // Initialize tasks display
                displayMeetingTasks();
            } else {
                // Meeting is in the future - hide details section
                detailsSection.style.display = 'none';
                typeIndicator.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.title = "Scheduling a future meeting";
                submitBtn.value = "Schedule Future Meeting";
                
                // Clear the form fields when hiding
                document.getElementById("textarea").value = "";
                document.getElementById("topic").value = "";
                // Clear checkboxes
                const checkboxes = document.querySelectorAll('input[name="activities[]"]');
                checkboxes.forEach(checkbox => checkbox.checked = false);
                
                // Clear tasks for future meetings
                meetingTasks = [];
                displayMeetingTasks();
            }
        } else {
            // No date/time selected - hide both sections
            detailsSection.style.display = 'none';
            typeIndicator.style.display = 'none';
        }
    }
    
    document.getElementById("meeting-date").addEventListener("change", checkMeetingTime);
    document.getElementById("meeting-time").addEventListener("change", checkMeetingTime);
    
    // Initialize check
    checkMeetingTime();
    

    
    document.getElementById("dropdown-btn").addEventListener("click", function(event) {
        let dropdownContent = document.getElementById("dropdown-content");
        dropdownContent.classList.toggle("show");
        event.stopPropagation(); // Prevents immediate closing
    });

    document.addEventListener("click", function(event) {
        let dropdownContent = document.getElementById("dropdown-content");
        let dropdownBtn = document.getElementById("dropdown-btn");

        // Close dropdown if clicking outside (but NOT when clicking inside checkboxes)
        if (!dropdownBtn.contains(event.target) && !dropdownContent.contains(event.target)) {
            dropdownContent.classList.remove("show");
        }
    });

    // Prevent dropdown from closing when clicking checkboxes
    document.getElementById("dropdown-content").addEventListener("click", function(event) {
        event.stopPropagation();
    });
</script>