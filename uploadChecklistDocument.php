<?php
require_once 'configDatabase.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method.']);
    exit;
}

$studentId = isset($_POST['studentId']) ? intval($_POST['studentId']) : 0;
$checklistId = isset($_POST['checklistId']) ? intval($_POST['checklistId']) : 0;
$markDone = isset($_POST['markDone']) && $_POST['markDone'] == '1';

if (!$studentId || !$checklistId || !isset($_FILES['file'])) {
    echo json_encode(['error' => 'Missing parameters.']);
    exit;
}

$file = $_FILES['file'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'File upload error.']);
    exit;
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
    echo json_encode(['error' => 'No applications found for this student.']);
    exit;
}

// Prepare the update for all applications_checklist rows with this checklistId
$in = implode(',', array_fill(0, count($applicationIds), '?'));
$params = $applicationIds;
array_unshift($params, $checklistId); // checklistId first
$types = str_repeat('i', count($applicationIds) + 1);

// Check if document_mime and documentName columns exist
$columnsRes = mysqli_query($link, "SHOW COLUMNS FROM applications_checklist");
$hasMime = false;
$hasName = false;
while ($col = mysqli_fetch_assoc($columnsRes)) {
    if ($col['Field'] === 'document_mime') $hasMime = true;
    if ($col['Field'] === 'documentName') $hasName = true;
}

$set = "document = ?";
$bindTypes = 'b';
$bindParams = [$fileContent];
if ($hasMime) {
    $set .= ", document_mime = ?";
    $bindTypes .= 's';
    $bindParams[] = $fileMime;
}
if ($hasName) {
    $set .= ", documentName = ?";
    $bindTypes .= 's';
    $bindParams[] = $fileName;
}
// Always set status to 'Done' when uploading a file
$set .= ", status = 'Done'";

// WHERE checklistId = ? AND applicationId IN (...)
$where = "checklistId = ? AND applicationId IN ($in)";
$updateSql = "UPDATE applications_checklist SET $set WHERE $where";
$stmt = mysqli_prepare($link, $updateSql);

// Bind params: document, [mime], [name], checklistId, applicationIds...
$allTypes = $bindTypes . 'i' . str_repeat('i', count($applicationIds));
$allParams = array_merge($bindParams, [$checklistId], $applicationIds);

// Prepare bind_param arguments
$refs = [];
foreach ($allParams as $k => &$v) {
    $refs[$k] = &$v;
}
array_unshift($refs, $allTypes);
call_user_func_array([$stmt, 'bind_param'], $refs);

// For blob, use send_long_data
if ($fileContent !== false) {
    $stmt->send_long_data(0, $fileContent);
}

$success = $stmt->execute();
$newStatus = null;
if ($success) {
    // Fetch the new status for all affected rows
    $sqlStatusFetch = "SELECT status FROM applications_checklist WHERE checklistId = ? AND applicationId IN ($in)";
    $stmtStatusFetch = mysqli_prepare($link, $sqlStatusFetch);
    $paramsStatusFetch = array_merge([$checklistId], $applicationIds);
    $typesStatusFetch = str_repeat('i', count($paramsStatusFetch));
    $refsStatusFetch = [];
    foreach ($paramsStatusFetch as $k => &$v) $refsStatusFetch[$k] = &$v;
    array_unshift($refsStatusFetch, $typesStatusFetch);
    call_user_func_array([$stmtStatusFetch, 'bind_param'], $refsStatusFetch);
    $stmtStatusFetch->execute();
    $resultStatusFetch = $stmtStatusFetch->get_result();
    $statuses = [];
    while ($rowStatus = $resultStatusFetch->fetch_assoc()) {
        $statuses[] = $rowStatus['status'];
    }
    $stmtStatusFetch->close();
    if (count($statuses) > 0) {
        $statusCounts = array_count_values($statuses);
        arsort($statusCounts);
        $newStatus = array_key_first($statusCounts);
    }
}

$hasDocument = false;
$fileName = '';
if ($success) {
    // Check if any file is now present for this checklist for any of the student's applications
    $sqlDoc = "SELECT documentName FROM applications_checklist WHERE checklistId = ? AND applicationId IN ($in) AND document IS NOT NULL AND documentName IS NOT NULL AND documentName != '' LIMIT 1";
    $stmtDoc = mysqli_prepare($link, $sqlDoc);
    $paramsDoc = array_merge([$checklistId], $applicationIds);
    $typesDoc = str_repeat('i', count($paramsDoc));
    $refsDoc = [];
    foreach ($paramsDoc as $k => &$v) $refsDoc[$k] = &$v;
    array_unshift($refsDoc, $typesDoc);
    call_user_func_array([$stmtDoc, 'bind_param'], $refsDoc);
    $stmtDoc->execute();
    $resultDoc = $stmtDoc->get_result();
    if ($rowDoc = $resultDoc->fetch_assoc()) {
        $hasDocument = true;
        $fileName = $rowDoc['documentName'];
    }
    $stmtDoc->close();
}

if ($success) {
    echo json_encode(['success' => true, 'newStatus' => $newStatus, 'hasDocument' => $hasDocument, 'fileName' => $fileName]);
} else {
    echo json_encode(['error' => 'Database update failed.']);
}
$stmt->close();
$link->close(); 