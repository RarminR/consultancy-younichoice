<?php
header('Content-Type: application/json');
require_once 'configDatabase.php';

if (!isset($_REQUEST['action'])) {
    echo json_encode(['error' => 'No action specified for task.']);
    exit;
}

$action = $_REQUEST['action'];
$link = $GLOBALS['link'] ?? null;

if (!$link) {
    require 'configDatabase.php';
    $link = $GLOBALS['link'];
}

switch ($action) {
    case 'add':
        $studentId = isset($_POST['studentId']) ? intval($_POST['studentId']) : 0;
        $taskText = isset($_POST['taskText']) ? trim($_POST['taskText']) : '';
        $taskDeadline = isset($_POST['taskDeadline']) ? $_POST['taskDeadline'] : null;
        $meetingId = isset($_POST['meetingId']) ? intval($_POST['meetingId']) : null;
        
        if (!$studentId || !$taskText) {
            echo json_encode(['error' => 'Missing required fields for task.']);
            exit;
        }
        
        $stmt = mysqli_prepare($link, "INSERT INTO tasks (studentId, taskText, taskDeadline, taskStatus, meetingId) VALUES (?, ?, ?, 'In Progress', ?)");
        if (!$stmt) {
            echo json_encode(['error' => 'DB error (prepare task insert).']);
            exit;
        }
        
        mysqli_stmt_bind_param($stmt, 'issi', $studentId, $taskText, $taskDeadline, $meetingId);
        if (!mysqli_stmt_execute($stmt)) {
            echo json_encode(['error' => 'DB error (execute task insert).']);
            mysqli_stmt_close($stmt);
            exit;
        }
        
        $taskId = mysqli_insert_id($link);
        mysqli_stmt_close($stmt);
        
        echo json_encode(['success' => true, 'taskId' => $taskId]);
        break;
        
    case 'update':
        $taskId = isset($_POST['taskId']) ? intval($_POST['taskId']) : 0;
        $taskText = isset($_POST['taskText']) ? trim($_POST['taskText']) : '';
        $taskDeadline = isset($_POST['taskDeadline']) ? $_POST['taskDeadline'] : null;
        
        if (!$taskId || !$taskText) {
            echo json_encode(['error' => 'Missing required fields for task update.']);
            exit;
        }
        
        $stmt = mysqli_prepare($link, "UPDATE tasks SET taskText = ?, taskDeadline = ? WHERE taskId = ?");
        if (!$stmt) {
            echo json_encode(['error' => 'DB error (prepare task update).']);
            exit;
        }
        
        mysqli_stmt_bind_param($stmt, 'ssi', $taskText, $taskDeadline, $taskId);
        if (!mysqli_stmt_execute($stmt)) {
            echo json_encode(['error' => 'DB error (execute task update).']);
            mysqli_stmt_close($stmt);
            exit;
        }
        
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => true]);
        break;
        
    case 'delete':
        $taskId = isset($_POST['taskId']) ? intval($_POST['taskId']) : 0;
        
        if (!$taskId) {
            echo json_encode(['error' => 'Missing task ID for deletion.']);
            exit;
        }
        
        $stmt = mysqli_prepare($link, "DELETE FROM tasks WHERE taskId = ?");
        if (!$stmt) {
            echo json_encode(['error' => 'DB error (prepare task delete).']);
            exit;
        }
        
        mysqli_stmt_bind_param($stmt, 'i', $taskId);
        if (!mysqli_stmt_execute($stmt)) {
            echo json_encode(['error' => 'DB error (execute task delete).']);
            mysqli_stmt_close($stmt);
            exit;
        }
        
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => true]);
        break;
        
    case 'toggle':
        $taskId = isset($_POST['taskId']) ? intval($_POST['taskId']) : 0;
        
        if (!$taskId) {
            echo json_encode(['error' => 'Missing task ID for toggle.']);
            exit;
        }
        
        // Get current status
        $stmt = mysqli_prepare($link, "SELECT taskStatus FROM tasks WHERE taskId = ?");
        if (!$stmt) {
            echo json_encode(['error' => 'DB error (prepare status select).']);
            exit;
        }
        
        mysqli_stmt_bind_param($stmt, 'i', $taskId);
        if (!mysqli_stmt_execute($stmt)) {
            echo json_encode(['error' => 'DB error (execute status select).']);
            mysqli_stmt_close($stmt);
            exit;
        }
        
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if (!$row) {
            echo json_encode(['error' => 'Task not found.']);
            exit;
        }
        
        $newStatus = ($row['taskStatus'] === 'Done') ? 'In Progress' : 'Done';
        
        // Update status
        $stmt2 = mysqli_prepare($link, "UPDATE tasks SET taskStatus = ? WHERE taskId = ?");
        if (!$stmt2) {
            echo json_encode(['error' => 'DB error (prepare status update).']);
            exit;
        }
        
        mysqli_stmt_bind_param($stmt2, 'si', $newStatus, $taskId);
        if (!mysqli_stmt_execute($stmt2)) {
            echo json_encode(['error' => 'DB error (execute status update).']);
            mysqli_stmt_close($stmt2);
            exit;
        }
        
        mysqli_stmt_close($stmt2);
        echo json_encode(['success' => true, 'newStatus' => $newStatus]);
        break;
        
    case 'list':
        $studentId = isset($_GET['studentId']) ? intval($_GET['studentId']) : 0;
        
        if (!$studentId) {
            echo json_encode(['error' => 'Missing student ID for task list.']);
            exit;
        }
        
        $stmt = mysqli_prepare($link, "SELECT taskId, taskText, taskDeadline, taskStatus, meetingId FROM tasks WHERE studentId = ? ORDER BY taskDeadline ASC, taskId DESC");
        if (!$stmt) {
            echo json_encode(['error' => 'DB error (prepare task list).']);
            exit;
        }
        
        mysqli_stmt_bind_param($stmt, 'i', $studentId);
        if (!mysqli_stmt_execute($stmt)) {
            echo json_encode(['error' => 'DB error (execute task list).']);
            mysqli_stmt_close($stmt);
            exit;
        }
        
        $result = mysqli_stmt_get_result($stmt);
        $tasks = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $tasks[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => true, 'tasks' => $tasks]);
        break;
        
    case 'listByMeeting':
        $meetingId = isset($_GET['meetingId']) ? intval($_GET['meetingId']) : 0;
        
        if (!$meetingId) {
            echo json_encode(['error' => 'Missing meeting ID for task list.']);
            exit;
        }
        
        $stmt = mysqli_prepare($link, "SELECT taskId, taskText, taskDeadline, taskStatus, meetingId FROM tasks WHERE meetingId = ? ORDER BY taskDeadline ASC, taskId DESC");
        if (!$stmt) {
            echo json_encode(['error' => 'DB error (prepare meeting task list).']);
            exit;
        }
        
        mysqli_stmt_bind_param($stmt, 'i', $meetingId);
        if (!mysqli_stmt_execute($stmt)) {
            echo json_encode(['error' => 'DB error (execute meeting task list).']);
            mysqli_stmt_close($stmt);
            exit;
        }
        
        $result = mysqli_stmt_get_result($stmt);
        $tasks = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $tasks[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => true, 'tasks' => $tasks]);
        break;
        
    default:
        echo json_encode(['error' => 'Unknown action.']);
        break;
}
?> 