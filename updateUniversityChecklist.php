<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once "configDatabase.php";
header('Content-Type: application/json');

function respond($arr) {
    echo json_encode($arr);
    exit;
}

try {
    if (!isset($_POST['universityId'], $_POST['checklistId'], $_POST['checked'])) {
        respond(['success' => false, 'error' => 'Missing parameters']);
    }

    $universityId = intval($_POST['universityId']);
    $checklistId = intval($_POST['checklistId']);
    $checked = ($_POST['checked'] === 'true' || $_POST['checked'] === 1 || $_POST['checked'] === true) ? 1 : 0;

    // Check if row exists
    $stmt = mysqli_prepare($link, "SELECT 1 FROM universities_checklist WHERE universityId = ? AND checklistId = ?");
    if (!$stmt) respond(['success' => false, 'error' => 'Prepare failed: ' . mysqli_error($link)]);
    mysqli_stmt_bind_param($stmt, 'ii', $universityId, $checklistId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $rowExists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);

    if ($checked) {
        if ($rowExists) {
            // Update isActive to 1
            $stmt = mysqli_prepare($link, "UPDATE universities_checklist SET isActive = 1 WHERE universityId = ? AND checklistId = ?");
            if (!$stmt) respond(['success' => false, 'error' => 'Prepare failed: ' . mysqli_error($link)]);
            mysqli_stmt_bind_param($stmt, 'ii', $universityId, $checklistId);
            if (!mysqli_stmt_execute($stmt)) {
                respond(['success' => false, 'error' => 'Update failed: ' . mysqli_stmt_error($stmt)]);
            }
            mysqli_stmt_close($stmt);
        } else {
            // Insert new row with isActive = 1
            $stmt = mysqli_prepare($link, "INSERT INTO universities_checklist (universityId, checklistId, isActive) VALUES (?, ?, 1)");
            if (!$stmt) respond(['success' => false, 'error' => 'Prepare failed: ' . mysqli_error($link)]);
            mysqli_stmt_bind_param($stmt, 'ii', $universityId, $checklistId);
            if (!mysqli_stmt_execute($stmt)) {
                respond(['success' => false, 'error' => 'Insert failed: ' . mysqli_stmt_error($stmt)]);
            }
            mysqli_stmt_close($stmt);
        }
        // Add checklist item to all applications for this university if not already present
        $sqlAppIds = "SELECT applicationId FROM applicationStatus WHERE universityId = ?";
        $stmtAppIds = mysqli_prepare($link, $sqlAppIds);
        if ($stmtAppIds) {
            mysqli_stmt_bind_param($stmtAppIds, 'i', $universityId);
            mysqli_stmt_execute($stmtAppIds);
            $resultAppIds = mysqli_stmt_get_result($stmtAppIds);
            while ($row = mysqli_fetch_assoc($resultAppIds)) {
                $applicationId = $row['applicationId'];
                // Check if already exists
                $stmtCheck = mysqli_prepare($link, "SELECT 1 FROM applications_checklist WHERE applicationId = ? AND checklistId = ? AND isCustom = 0");
                if ($stmtCheck) {
                    mysqli_stmt_bind_param($stmtCheck, 'ii', $applicationId, $checklistId);
                    mysqli_stmt_execute($stmtCheck);
                    mysqli_stmt_store_result($stmtCheck);
                    $exists = mysqli_stmt_num_rows($stmtCheck) > 0;
                    mysqli_stmt_close($stmtCheck);
                    if (!$exists) {
                        $stmtInsert = mysqli_prepare($link, "INSERT INTO applications_checklist (applicationId, checklistId, isCustom, status) VALUES (?, ?, 0, 'In Progress')");
                        if ($stmtInsert) {
                            mysqli_stmt_bind_param($stmtInsert, 'ii', $applicationId, $checklistId);
                            mysqli_stmt_execute($stmtInsert);
                            mysqli_stmt_close($stmtInsert);
                        }
                    }
                }
            }
            mysqli_stmt_close($stmtAppIds);
        }
        respond(['success' => true, 'action' => 'activated']);
    } else {
        if ($rowExists) {
            // Update isActive to 0
            $stmt = mysqli_prepare($link, "UPDATE universities_checklist SET isActive = 0 WHERE universityId = ? AND checklistId = ?");
            if (!$stmt) respond(['success' => false, 'error' => 'Prepare failed: ' . mysqli_error($link)]);
            mysqli_stmt_bind_param($stmt, 'ii', $universityId, $checklistId);
            if (!mysqli_stmt_execute($stmt)) {
                respond(['success' => false, 'error' => 'Update failed: ' . mysqli_stmt_error($stmt)]);
            }
            mysqli_stmt_close($stmt);
        } else {
            // Insert new row with isActive = 0
            $stmt = mysqli_prepare($link, "INSERT INTO universities_checklist (universityId, checklistId, isActive) VALUES (?, ?, 0)");
            if (!$stmt) respond(['success' => false, 'error' => 'Prepare failed: ' . mysqli_error($link)]);
            mysqli_stmt_bind_param($stmt, 'ii', $universityId, $checklistId);
            if (!mysqli_stmt_execute($stmt)) {
                respond(['success' => false, 'error' => 'Insert failed: ' . mysqli_stmt_error($stmt)]);
            }
            mysqli_stmt_close($stmt);
        }
        respond(['success' => true, 'action' => 'deactivated']);
    }
} catch (Throwable $e) {
    respond(['success' => false, 'error' => 'Unexpected error: ' . $e->getMessage()]);
} 