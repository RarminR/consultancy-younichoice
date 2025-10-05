<?php
require_once 'configDatabase.php';

$studentId = isset($_GET['studentId']) ? intval($_GET['studentId']) : 0;
$checklistId = isset($_GET['checklistId']) ? intval($_GET['checklistId']) : 0;

if (!$studentId || !$checklistId) {
    http_response_code(400);
    echo 'Missing parameters.';
    exit;
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
if ($row = $res->fetch_assoc()) {
    $mime = ($hasMime && !empty($row['document_mime'])) ? $row['document_mime'] : 'application/octet-stream';
    $name = ($hasName && !empty($row['documentName'])) ? $row['documentName'] : 'document.bin';
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . $name . '"');
    echo $row['document'];
    exit;
} else {
    http_response_code(404);
    echo 'No document found.';
    exit;
} 