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
    
    // Debug: Log the student ID
    error_log("Checklist fetch for student ID: " . $studentId);
    
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
    
    // Get all applicationIds for this student
    $sqlAppIds = "SELECT applicationId, universityId FROM applicationStatus WHERE studentId = $studentId";
    $queryAppIds = mysqli_query($link, $sqlAppIds);
    
    if (!$queryAppIds) {
        echo json_encode(['error' => 'Failed to query applications: ' . mysqli_error($link)]);
        exit();
    }
    
    $applicationIds = [];
    $applicationIdToUniversity = [];
    while ($row = mysqli_fetch_assoc($queryAppIds)) {
        $applicationIds[] = $row['applicationId'];
        $applicationIdToUniversity[$row['applicationId']] = $row['universityId'];
    }
    
    // Debug: Log the number of applications found
    error_log("Found " . count($applicationIds) . " applications for student " . $studentId);
    
    if (count($applicationIds) > 0) {
        $applicationIdsStr = implode(",", array_map('intval', $applicationIds));
        
        // Get all checklist items for these applications - simplified approach like individual application pages
        $sqlChecklist = "SELECT ac.checklistId, ac.isCustom, ac.status, c.checklistName, ac.applicationId FROM applications_checklist ac LEFT JOIN checklist c ON ac.checklistId = c.checklistId WHERE ac.applicationId IN ($applicationIdsStr)";
        $queryChecklist = mysqli_query($link, $sqlChecklist);
        
        if (!$queryChecklist) {
            echo json_encode(['error' => 'Database query failed: ' . mysqli_error($link)]);
            exit();
        }
        
        // Debug: Log the checklist query
        error_log("Checklist query executed for " . count($applicationIds) . " applications");
        
        $checklistItems = [];
        $seenChecklistIds = [];
        
        while ($row = mysqli_fetch_assoc($queryChecklist)) {
            $checklistId = $row['checklistId'];
            $isCustom = (int)$row['isCustom'];
            $applicationId = $row['applicationId'];
            $universityId = $applicationIdToUniversity[$applicationId];
            
            // Skip if we've already processed this checklist item (to avoid duplicates)
            if (in_array($checklistId, $seenChecklistIds)) {
                continue;
            }
            
            // Get university name
            $universityName = "Unknown";
            if ($isCustom) {
                $sqlUni = "SELECT universityName FROM universities WHERE universityId = '" . mysqli_real_escape_string($link, $universityId) . "'";
                $queryUni = mysqli_query($link, $sqlUni);
                if ($uniRow = mysqli_fetch_assoc($queryUni)) {
                    $universityName = $uniRow['universityName'];
                }
            } else {
                $sqlUniChecklist = "SELECT u.universityName FROM universities_checklist uc JOIN universities u ON uc.universityId = u.universityId WHERE uc.checklistId = '" . mysqli_real_escape_string($link, $checklistId) . "' AND uc.universityId = '" . mysqli_real_escape_string($link, $universityId) . "'";
                $queryUniChecklist = mysqli_query($link, $sqlUniChecklist);
                if ($uniRow = mysqli_fetch_assoc($queryUniChecklist)) {
                    $universityName = $uniRow['universityName'];
                }
            }
            
            $item = [
                'checklistId' => $checklistId,
                'checklistName' => $row['checklistName'] ? $row['checklistName'] : $checklistId,
                'status' => $row['status'] ?? 'In Progress',
                'isCustom' => (bool)$isCustom,
                'universities' => [$universityName],
                'hasDocument' => false,
                'documentName' => null
            ];
            
            $checklistItems[] = $item;
            $seenChecklistIds[] = $checklistId;
        }
        
        // Debug: Log the final result
        error_log("Returning " . count($checklistItems) . " checklist items for student " . $studentId);
        echo json_encode(['checklistItems' => $checklistItems]);
    } else {
        error_log("No applications found for student " . $studentId);
        echo json_encode(['checklistItems' => [], 'message' => 'No applications found for this student']);
    }
    exit();
}

if ($action === 'upload') {
    $studentId = isset($_POST['studentId']) ? intval($_POST['studentId']) : 0;
    $checklistId = isset($_POST['checklistId']) ? intval($_POST['checklistId']) : 0;
    
    if (!$studentId || !$checklistId || !isset($_FILES['file'])) {
        echo json_encode(['error' => 'Missing parameters']);
        exit();
    }
    
    // Check permissions
    if ($isStudent) {
        // Students can only upload for their own data
        if ($studentId != $_SESSION['idStudent']) {
            echo json_encode(['error' => 'Permission denied']);
            exit();
        }
    } else {
        // Consultants/admins can upload for their students' data
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
    
    $file = $_FILES['file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 'File upload error']);
        exit();
    }
    
    $fileContent = file_get_contents($file['tmp_name']);
    $fileName = $file['name'];
    $fileMime = mime_content_type($file['tmp_name']);
    
    // Find all applicationIds for this student
    $sql = "SELECT applicationId FROM applicationStatus WHERE studentId = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $studentId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $applicationIds = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $applicationIds[] = $row['applicationId'];
    }
    mysqli_stmt_close($stmt);
    
    if (empty($applicationIds)) {
        echo json_encode(['error' => 'No applications found for this student']);
        exit();
    }
    
    // Update all applications_checklist rows with this checklistId for this student
    $in = implode(',', array_fill(0, count($applicationIds), '?'));
    $params = $applicationIds;
    array_unshift($params, $checklistId);
    $types = str_repeat('i', count($params));
    
    // Check if document_mime and documentName columns exist
    $columnsRes = mysqli_query($link, "SHOW COLUMNS FROM applications_checklist");
    $hasMime = false;
    $hasName = false;
    while ($col = mysqli_fetch_assoc($columnsRes)) {
        if ($col['Field'] === 'document_mime') $hasMime = true;
        if ($col['Field'] === 'documentName') $hasName = true;
    }
    
    // Update the applications_checklist table
    $updateSql = "UPDATE applications_checklist SET document = ?, status = 'Completed'";
    if ($hasMime) $updateSql .= ", document_mime = ?";
    if ($hasName) $updateSql .= ", documentName = ?";
    $updateSql .= " WHERE checklistId = ? AND applicationId IN ($in)";
    
    $stmt = mysqli_prepare($link, $updateSql);
    if (!$stmt) {
        echo json_encode(['error' => 'Database prepare failed']);
        exit();
    }
    
    $bindParams = [$fileContent];
    if ($hasMime) $bindParams[] = $fileMime;
    if ($hasName) $bindParams[] = $fileName;
    $bindParams[] = $checklistId;
    $bindParams = array_merge($bindParams, $applicationIds);
    
    $types = 's' . ($hasMime ? 's' : '') . ($hasName ? 's' : '') . 'i' . str_repeat('i', count($applicationIds));
    $refs = [];
    foreach ($bindParams as $k => &$v) $refs[$k] = &$v;
    array_unshift($refs, $types);
    call_user_func_array([$stmt, 'bind_param'], $refs);
    
    if (!mysqli_stmt_execute($stmt)) {
        echo json_encode(['error' => 'Failed to upload document']);
        exit();
    }
    mysqli_stmt_close($stmt);
    
    echo json_encode(['success' => true, 'message' => 'Document uploaded successfully']);
    exit();
}

if ($action === 'add') {
    $studentId = isset($_POST['studentId']) ? intval($_POST['studentId']) : 0;
    $checklistName = isset($_POST['checklistName']) ? trim($_POST['checklistName']) : '';
    $applicationIds = isset($_POST['applicationIds']) ? $_POST['applicationIds'] : [];
    
    if (!$studentId || !$checklistName || empty($applicationIds)) {
        echo json_encode(['error' => 'Missing required parameters']);
        exit();
    }
    
    // Check permissions
    if ($isStudent) {
        // Students cannot add checklist items
        echo json_encode(['error' => 'Permission denied']);
        exit();
    } else {
        // Consultants/admins can add checklist items for their students
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
    
    // Validate that all applicationIds belong to this student
    $applicationIdsStr = implode(",", array_map('intval', $applicationIds));
    $sqlValidate = "SELECT applicationId FROM applicationStatus WHERE studentId = $studentId AND applicationId IN ($applicationIdsStr)";
    $queryValidate = mysqli_query($link, $sqlValidate);
    if (mysqli_num_rows($queryValidate) !== count($applicationIds)) {
        echo json_encode(['error' => 'Invalid application IDs']);
        exit();
    }
    
    // Insert new checklist item
    $checklistNameEscaped = mysqli_real_escape_string($link, $checklistName);
    $sqlInsertChecklist = "INSERT INTO checklist (checklistName) VALUES ('$checklistNameEscaped')";
    if (!mysqli_query($link, $sqlInsertChecklist)) {
        echo json_encode(['error' => 'Failed to create checklist item']);
        exit();
    }
    $checklistId = mysqli_insert_id($link);
    
    // Add checklist item to selected applications
    foreach ($applicationIds as $applicationId) {
        $applicationId = intval($applicationId);
        $sqlInsertAppChecklist = "INSERT INTO applications_checklist (applicationId, checklistId, isCustom, status) VALUES ($applicationId, $checklistId, 1, 'In Progress')";
        if (!mysqli_query($link, $sqlInsertAppChecklist)) {
            echo json_encode(['error' => 'Failed to assign checklist item to applications']);
            exit();
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Checklist item added successfully']);
    exit();
}

echo json_encode(['error' => 'Invalid action']);
exit(); 