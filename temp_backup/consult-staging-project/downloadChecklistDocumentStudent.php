<?php
session_start();
require_once 'configDatabase.php';

$studentId = isset($_GET['studentId']) ? intval($_GET['studentId']) : 0;
$checklistId = isset($_GET['checklistId']) ? intval($_GET['checklistId']) : 0;

if (!$studentId || !$checklistId) {
    http_response_code(400);
    echo 'Missing parameters.';
    exit;
}

// Check if user is logged in (handle both student and consultant/admin sessions)
$isStudent = isset($_SESSION['typeStudent']) && isset($_SESSION['idStudent']);
$isConsultant = isset($_SESSION['type']) && isset($_SESSION['id']);

if (!$isStudent && !$isConsultant) {
    http_response_code(401);
    echo 'Not authenticated.';
    exit;
}

// Check permissions
if ($isStudent) {
    // Students can only download their own documents
    if ($studentId != $_SESSION['idStudent']) {
        http_response_code(403);
        echo 'Permission denied.';
        exit;
    }
} else {
    // Consultants/admins can download their students' documents
    $typeAccount = $_SESSION['type'];
    $accountId = $_SESSION['id'];
    $sqlStudent = "SELECT * FROM studentData WHERE studentId = $studentId";
    $queryStudent = mysqli_query($link, $sqlStudent);
    if (mysqli_num_rows($queryStudent) === 0) {
        http_response_code(404);
        echo 'Student not found.';
        exit;
    }
    $dataStudent = mysqli_fetch_assoc($queryStudent);
    if (!($dataStudent['consultantId'] == $accountId || $typeAccount == 1)) {
        http_response_code(403);
        echo 'Permission denied.';
        exit;
    }
}

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
    http_response_code(404);
    echo 'No applications found for this student.';
    exit;
}

$in = implode(',', array_fill(0, count($applicationIds), '?'));

// Check if document_mime and documentName columns exist
$columnsRes = mysqli_query($link, "SHOW COLUMNS FROM applications_checklist");
$hasMime = false;
$hasName = false;
while ($col = mysqli_fetch_assoc($columnsRes)) {
    if ($col['Field'] === 'document_mime') $hasMime = true;
    if ($col['Field'] === 'documentName') $hasName = true;
}

// Get the first non-null document for this task and student
$select = "SELECT document";
if ($hasMime) $select .= ", document_mime";
if ($hasName) $select .= ", documentName";
$select .= " FROM applications_checklist WHERE checklistId = ? AND applicationId IN ($in) AND document IS NOT NULL LIMIT 1";
$stmt = mysqli_prepare($link, $select);
$params = array_merge([$checklistId], $applicationIds);
$types = str_repeat('i', count($params));
$refs = [];
foreach ($params as $k => &$v) $refs[$k] = &$v;
array_unshift($refs, $types);
call_user_func_array([$stmt, 'bind_param'], $refs);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    http_response_code(404);
    echo 'Document not found.';
    exit;
}

$row = $res->fetch_assoc();
$document = $row['document'];
$mimeType = $hasMime ? $row['document_mime'] : 'application/octet-stream';
$fileName = $hasName ? $row['documentName'] : 'document';

mysqli_stmt_close($stmt);

// Output the document
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . strlen($document));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
echo $document;
exit; 