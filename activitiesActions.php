<?php
session_start();
require_once 'configDatabase.php';
header('Content-Type: application/json');

// Check database connection
if (!isset($link) || !$link) {
    echo json_encode(['error' => 'Database connection failed.']);
    exit();
}

// Check if user is logged in (handle both student and consultant/admin sessions)
$isStudent = isset($_SESSION['typeStudent']) && isset($_SESSION['idStudent']);
$isConsultant = isset($_SESSION['type']) && isset($_SESSION['id']);

if (!$isStudent && !$isConsultant) {
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'fetch') {
    $studentId = isset($_GET['studentId']) ? intval($_GET['studentId']) : 0;
    if (!$studentId) {
        echo json_encode(['error' => 'Missing studentId']);
        exit();
    }
    
    // Check permissions
    if ($isStudent) {
        // Students can only access their own data
        if ($studentId != $_SESSION['idStudent']) {
            echo json_encode(['error' => 'Permission denied']);
            exit();
        }
    } else {
        // Consultants/admins can access their students' data
        $typeAccount = $_SESSION['type'];
        $accountId = $_SESSION['id'];
        $sqlStudent = "SELECT * FROM studentData WHERE studentId = $studentId";
        $queryStudent = mysqli_query($link, $sqlStudent);
        if (mysqli_num_rows($queryStudent) === 0) {
            echo json_encode(['error' => 'Student not found']);
            exit();
        }
        $dataStudent = mysqli_fetch_assoc($queryStudent);
        if (!($dataStudent['consultantId'] == $accountId || $typeAccount == 1)) {
            echo json_encode(['error' => 'Permission denied']);
            exit();
        }
    }
    
    // Fetch activities (include activityRating)
    $sqlActivities = "SELECT activityId, activityType, activityDescription, activityOrganization, activityPosition, startDate, endDate, hoursPerWeek, activityRating FROM activities WHERE studentId = $studentId";
    $queryActivities = mysqli_query($link, $sqlActivities);
    $activities = [];
    while ($row = mysqli_fetch_assoc($queryActivities)) {
        $activities[] = $row;
    }
    echo json_encode(['activities' => $activities]);
    exit();
}

if ($action === 'add') {
    $studentId = isset($_POST['studentId']) ? intval($_POST['studentId']) : 0;
    $activityType = isset($_POST['activityType']) ? trim($_POST['activityType']) : '';
    $activityDescription = isset($_POST['activityDescription']) ? trim($_POST['activityDescription']) : '';
    $activityOrganization = isset($_POST['activityOrganization']) ? trim($_POST['activityOrganization']) : '';
    $activityPosition = isset($_POST['activityPosition']) ? trim($_POST['activityPosition']) : '';
    $startDate = isset($_POST['startDate']) ? trim($_POST['startDate']) : '';
    $endDate = isset($_POST['endDate']) ? trim($_POST['endDate']) : '';
    $hoursPerWeek = isset($_POST['hoursPerWeek']) ? intval($_POST['hoursPerWeek']) : 0;
    $activityRating = isset($_POST['activityRating']) ? intval($_POST['activityRating']) : 0;
    if ($activityRating < 0 || $activityRating > 4) $activityRating = 0;
    if (!$studentId) {
        echo json_encode(['error' => 'Missing studentId']);
        exit();
    }
    
    // Check permissions
    if ($isStudent) {
        // Students can only add to their own data
        if ($studentId != $_SESSION['idStudent']) {
            echo json_encode(['error' => 'Permission denied']);
            exit();
        }
    } else {
        // Consultants/admins can add to their students' data
        $typeAccount = $_SESSION['type'];
        $accountId = $_SESSION['id'];
        $sqlStudent = "SELECT * FROM studentData WHERE studentId = $studentId";
        $queryStudent = mysqli_query($link, $sqlStudent);
        if (mysqli_num_rows($queryStudent) === 0) {
            echo json_encode(['error' => 'Student not found']);
            exit();
        }
        $dataStudent = mysqli_fetch_assoc($queryStudent);
        if (!($dataStudent['consultantId'] == $accountId || $typeAccount == 1)) {
            echo json_encode(['error' => 'Permission denied']);
            exit();
        }
    }
    
    // Insert activity (allow empty fields)
    $activityTypeEsc = mysqli_real_escape_string($link, $activityType);
    $activityDescriptionEsc = mysqli_real_escape_string($link, $activityDescription);
    $activityOrganizationEsc = mysqli_real_escape_string($link, $activityOrganization);
    $activityPositionEsc = mysqli_real_escape_string($link, $activityPosition);
    $startDateEsc = mysqli_real_escape_string($link, $startDate);
    $endDateEsc = mysqli_real_escape_string($link, $endDate);
    $sqlAdd = "INSERT INTO activities (studentId, activityType, activityDescription, activityOrganization, activityPosition, startDate, endDate, hoursPerWeek, activityRating) VALUES ($studentId, '$activityTypeEsc', '$activityDescriptionEsc', '$activityOrganizationEsc', '$activityPositionEsc', '$startDateEsc', '$endDateEsc', $hoursPerWeek, $activityRating)";
    if (!mysqli_query($link, $sqlAdd)) {
        echo json_encode(['error' => 'Failed to add activity']);
        exit();
    }
    // Return updated list
    $sqlActivities = "SELECT activityId, activityType, activityDescription, activityOrganization, activityPosition, startDate, endDate, hoursPerWeek, activityRating FROM activities WHERE studentId = $studentId";
    $queryActivities = mysqli_query($link, $sqlActivities);
    $activities = [];
    while ($row = mysqli_fetch_assoc($queryActivities)) {
        $activities[] = $row;
    }
    echo json_encode(['activities' => $activities]);
    exit();
}

if ($action === 'edit') {
    $studentId = isset($_POST['studentId']) ? intval($_POST['studentId']) : 0;
    $activityId = isset($_POST['activityId']) ? intval($_POST['activityId']) : 0;
    // Only update fields that are set in POST, otherwise keep existing values
    $fieldsToUpdate = [];
    if (isset($_POST['activityType'])) $fieldsToUpdate['activityType'] = trim($_POST['activityType']);
    if (isset($_POST['activityDescription'])) $fieldsToUpdate['activityDescription'] = trim($_POST['activityDescription']);
    if (isset($_POST['activityOrganization'])) $fieldsToUpdate['activityOrganization'] = trim($_POST['activityOrganization']);
    if (isset($_POST['activityPosition'])) $fieldsToUpdate['activityPosition'] = trim($_POST['activityPosition']);
    if (isset($_POST['startDate'])) $fieldsToUpdate['startDate'] = trim($_POST['startDate']);
    if (isset($_POST['endDate'])) $fieldsToUpdate['endDate'] = trim($_POST['endDate']);
    if (isset($_POST['hoursPerWeek'])) $fieldsToUpdate['hoursPerWeek'] = intval($_POST['hoursPerWeek']);
    if (isset($_POST['activityRating'])) {
        $activityRating = intval($_POST['activityRating']);
        if ($activityRating < 0 || $activityRating > 5) $activityRating = 0;
        $fieldsToUpdate['activityRating'] = $activityRating;
    }
    if (!$studentId || !$activityId) {
        echo json_encode(['error' => 'Missing studentId or activityId']);
        exit();
    }
    
    // Check permissions
    if ($isStudent) {
        // Students can only edit their own data
        if ($studentId != $_SESSION['idStudent']) {
            echo json_encode(['error' => 'Permission denied']);
            exit();
        }
    } else {
        // Consultants/admins can edit their students' data
        $typeAccount = $_SESSION['type'];
        $accountId = $_SESSION['id'];
        $sqlStudent = "SELECT * FROM studentData WHERE studentId = $studentId";
        $queryStudent = mysqli_query($link, $sqlStudent);
        if (mysqli_num_rows($queryStudent) === 0) {
            echo json_encode(['error' => 'Student not found']);
            exit();
        }
        $dataStudent = mysqli_fetch_assoc($queryStudent);
        if (!($dataStudent['consultantId'] == $accountId || $typeAccount == 1)) {
            echo json_encode(['error' => 'Permission denied']);
            exit();
        }
    }
    
    // If not all fields are set, fetch current values for missing fields
    if (count($fieldsToUpdate) < 8) {
        $sqlCurrent = "SELECT * FROM activities WHERE activityId = $activityId AND studentId = $studentId";
        $queryCurrent = mysqli_query($link, $sqlCurrent);
        if (mysqli_num_rows($queryCurrent) === 0) {
            echo json_encode(['error' => 'Activity not found']);
            exit();
        }
        $current = mysqli_fetch_assoc($queryCurrent);
        if (!isset($fieldsToUpdate['activityType'])) $fieldsToUpdate['activityType'] = $current['activityType'];
        if (!isset($fieldsToUpdate['activityDescription'])) $fieldsToUpdate['activityDescription'] = $current['activityDescription'];
        if (!isset($fieldsToUpdate['activityOrganization'])) $fieldsToUpdate['activityOrganization'] = $current['activityOrganization'];
        if (!isset($fieldsToUpdate['activityPosition'])) $fieldsToUpdate['activityPosition'] = $current['activityPosition'];
        if (!isset($fieldsToUpdate['startDate'])) $fieldsToUpdate['startDate'] = $current['startDate'];
        if (!isset($fieldsToUpdate['endDate'])) $fieldsToUpdate['endDate'] = $current['endDate'];
        if (!isset($fieldsToUpdate['hoursPerWeek'])) $fieldsToUpdate['hoursPerWeek'] = $current['hoursPerWeek'];
        if (!isset($fieldsToUpdate['activityRating'])) $fieldsToUpdate['activityRating'] = $current['activityRating'];
    }
    // Update activity
    $activityTypeEsc = mysqli_real_escape_string($link, $fieldsToUpdate['activityType']);
    $activityDescriptionEsc = mysqli_real_escape_string($link, $fieldsToUpdate['activityDescription']);
    $activityOrganizationEsc = mysqli_real_escape_string($link, $fieldsToUpdate['activityOrganization']);
    $activityPositionEsc = mysqli_real_escape_string($link, $fieldsToUpdate['activityPosition']);
    $startDateEsc = mysqli_real_escape_string($link, $fieldsToUpdate['startDate']);
    $endDateEsc = mysqli_real_escape_string($link, $fieldsToUpdate['endDate']);
    $hoursPerWeek = intval($fieldsToUpdate['hoursPerWeek']);
    $activityRating = intval($fieldsToUpdate['activityRating']);
    $sqlEdit = "UPDATE activities SET activityType = '$activityTypeEsc', activityDescription = '$activityDescriptionEsc', activityOrganization = '$activityOrganizationEsc', activityPosition = '$activityPositionEsc', startDate = '$startDateEsc', endDate = '$endDateEsc', hoursPerWeek = $hoursPerWeek, activityRating = $activityRating WHERE activityId = $activityId AND studentId = $studentId";
    if (!mysqli_query($link, $sqlEdit)) {
        echo json_encode(['error' => 'Failed to update activity']);
        exit();
    }
    // Return updated list
    $sqlActivities = "SELECT activityId, activityType, activityDescription, activityOrganization, activityPosition, startDate, endDate, hoursPerWeek, activityRating FROM activities WHERE studentId = $studentId";
    $queryActivities = mysqli_query($link, $sqlActivities);
    $activities = [];
    while ($row = mysqli_fetch_assoc($queryActivities)) {
        $activities[] = $row;
    }
    echo json_encode(['activities' => $activities]);
    exit();
}

if ($action === 'delete') {
    $studentId = isset($_POST['studentId']) ? intval($_POST['studentId']) : 0;
    $activityId = isset($_POST['activityId']) ? intval($_POST['activityId']) : 0;
    if (!$studentId || !$activityId) {
        echo json_encode(['error' => 'Missing studentId or activityId']);
        exit();
    }
    
    // Check permissions
    if ($isStudent) {
        // Students can only delete their own data
        if ($studentId != $_SESSION['idStudent']) {
            echo json_encode(['error' => 'Permission denied']);
            exit();
        }
    } else {
        // Consultants/admins can delete their students' data
        $typeAccount = $_SESSION['type'];
        $accountId = $_SESSION['id'];
        $sqlStudent = "SELECT * FROM studentData WHERE studentId = $studentId";
        $queryStudent = mysqli_query($link, $sqlStudent);
        if (mysqli_num_rows($queryStudent) === 0) {
            echo json_encode(['error' => 'Student not found']);
            exit();
        }
        $dataStudent = mysqli_fetch_assoc($queryStudent);
        if (!($dataStudent['consultantId'] == $accountId || $typeAccount == 1)) {
            echo json_encode(['error' => 'Permission denied']);
            exit();
        }
    }
    
    // Delete activity
    $sqlDelete = "DELETE FROM activities WHERE activityId = $activityId AND studentId = $studentId";
    if (!mysqli_query($link, $sqlDelete)) {
        echo json_encode(['error' => 'Failed to delete activity']);
        exit();
    }
    // Return updated list
    $sqlActivities = "SELECT activityId, activityType, activityDescription, activityOrganization, activityPosition, startDate, endDate, hoursPerWeek FROM activities WHERE studentId = $studentId";
    $queryActivities = mysqli_query($link, $sqlActivities);
    $activities = [];
    while ($row = mysqli_fetch_assoc($queryActivities)) {
        $activities[] = $row;
    }
    echo json_encode(['activities' => $activities]);
    exit();
}

echo json_encode(['error' => 'Invalid action']);
exit(); 