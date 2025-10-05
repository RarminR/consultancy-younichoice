<?php
require_once "configDatabase.php";
header('Content-Type: application/json');

if (!isset($_POST['universityId'], $_POST['checklistName'])) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

$universityId = intval($_POST['universityId']);
$checklistName = trim($_POST['checklistName']);
if ($checklistName === '') {
    echo json_encode(['success' => false, 'error' => 'Checklist item name required']);
    exit;
}

// Insert into checklist
$checklistNameEscaped = mysqli_real_escape_string($link, $checklistName);
$insertChecklist = "INSERT INTO checklist (checklistName) VALUES ('$checklistNameEscaped')";
if (!mysqli_query($link, $insertChecklist)) {
    echo json_encode(['success' => false, 'error' => 'Failed to create checklist item']);
    exit;
}
$checklistId = mysqli_insert_id($link);

// Associate with university
$insertUniChecklist = "INSERT INTO universities_checklist (universityId, checklistId) VALUES ('$universityId', '$checklistId')";
if (!mysqli_query($link, $insertUniChecklist)) {
    echo json_encode(['success' => false, 'error' => 'Failed to associate checklist item with university']);
    exit;
}

echo json_encode(['success' => true, 'checklistId' => $checklistId, 'checklistName' => $checklistName]); 