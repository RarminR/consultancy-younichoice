<?php
require_once 'configDatabase.php';
$studentId = isset($_GET['studentId']) ? intval($_GET['studentId']) : 0;
if (!$studentId) {
    echo '<p class="mb-0">No checklist items found for this student.</p>';
    exit;
}
$link = $GLOBALS['link'] ?? null;
if (!$link) {
    require 'configDatabase.php';
    $link = $GLOBALS['link'];
}
// 1. Get all applicationIds for this student
$sqlAppIds = "SELECT applicationId, universityId FROM applicationStatus WHERE studentId = '$studentId'";
$queryAppIds = mysqli_query($link, $sqlAppIds);
$applicationIds = [];
$applicationIdToUniversity = [];
while ($row = mysqli_fetch_assoc($queryAppIds)) {
    $applicationIds[] = $row['applicationId'];
    $applicationIdToUniversity[$row['applicationId']] = $row['universityId'];
}
if (count($applicationIds) > 0) {
    $applicationIdsStr = implode(",", array_map('intval', $applicationIds));
    // 2. Get all checklist items for these applications (union, including custom)
    $sqlChecklist = "SELECT ac.checklistId, ac.isCustom, ac.status, c.checklistName, ac.applicationId FROM applications_checklist ac LEFT JOIN checklist c ON ac.checklistId = c.checklistId WHERE ac.applicationId IN ($applicationIdsStr)";
    $queryChecklist = mysqli_query($link, $sqlChecklist);
    $checklistUnion = [];
    $checklistIdToStatus = [];
    $checklistIdToIsCustom = [];
    $checklistIdToAppIds = [];
    while ($row = mysqli_fetch_assoc($queryChecklist)) {
        $checklistId = $row['checklistId'];
        $isCustom = (int)$row['isCustom'];
        $applicationId = $row['applicationId'];
        $universityId = $applicationIdToUniversity[$applicationId];
        if (!$isCustom) {
            // Only show if universities_checklist.isActive = 1
            $sqlActive = "SELECT isActive FROM universities_checklist WHERE universityId = '" . mysqli_real_escape_string($link, $universityId) . "' AND checklistId = '" . mysqli_real_escape_string($link, $checklistId) . "' LIMIT 1";
            $resultActive = mysqli_query($link, $sqlActive);
            $isActive = 0;
            if ($resultActive && ($rowActive = mysqli_fetch_assoc($resultActive))) {
                $isActive = (int)$rowActive['isActive'];
            }
            if ($isActive !== 1) continue;
        }
        $checklistUnion[$checklistId] = $row['checklistName'] ? $row['checklistName'] : $checklistId;
        $checklistIdToStatus[$checklistId] = $row['status'];
        $checklistIdToIsCustom[$checklistId] = $row['isCustom'];
        $checklistIdToAppIds[$checklistId][] = $row['applicationId'];
    }
    // 3. For each checklist item, get all universities associated with it
    $checklistIdToUniversities = [];
    foreach ($checklistUnion as $checklistId => $checklistName) {
        $universityNames = [];
        if (isset($checklistIdToAppIds[$checklistId])) {
            foreach ($checklistIdToAppIds[$checklistId] as $appId) {
                $isCustom = (int)$checklistIdToIsCustom[$checklistId];
                $universityId = $applicationIdToUniversity[$appId];
                if ($isCustom) {
                    $sqlUni = "SELECT universityName FROM universities WHERE universityId = '" . mysqli_real_escape_string($link, $universityId) . "'";
                    $queryUni = mysqli_query($link, $sqlUni);
                    if ($uniRow = mysqli_fetch_assoc($queryUni)) {
                        $universityNames[] = $uniRow['universityName'];
                    }
                } else {
                    $sqlUniChecklist = "SELECT u.universityName FROM universities_checklist uc JOIN universities u ON uc.universityId = u.universityId WHERE uc.checklistId = '" . mysqli_real_escape_string($link, $checklistId) . "' AND uc.universityId = '" . mysqli_real_escape_string($link, $universityId) . "'";
                    $queryUniChecklist = mysqli_query($link, $sqlUniChecklist);
                    while ($uniRow = mysqli_fetch_assoc($queryUniChecklist)) {
                        $universityNames[] = $uniRow['universityName'];
                    }
                }
            }
        }
        $universityNames = array_unique($universityNames);
        $checklistIdToUniversities[$checklistId] = $universityNames;
    }
    // 4. Display
    if (count($checklistUnion) > 0) {
        echo '<ul class="list-group">';
        foreach ($checklistUnion as $checklistId => $checklistName) {
            $isCustom = (int)$checklistIdToIsCustom[$checklistId];
            $universities = $checklistIdToUniversities[$checklistId];
            $status = htmlspecialchars($checklistIdToStatus[$checklistId]);
            $statusRaw = $checklistIdToStatus[$checklistId];
            $badgeColor = (strtolower(trim($statusRaw)) === 'done') ? '#28a745' : '#c61b75';
            echo '<li class="list-group-item checklist-item" style="position: relative; padding-right: 140px; cursor: pointer;" onclick="toggleChecklistFiles(' . $checklistId . ')">';
            echo '<span style="display: block; overflow: hidden; text-overflow: ellipsis; text-align: left;">';
            echo '<strong style="color: '.($isCustom ? '#5bc0de' : '#f0ad4e').';">'.($isCustom ? 'Extra Checklist Item: ' : 'University Checklist Item: ').'</strong>';
            echo htmlspecialchars($checklistName);
            echo ' <span style="color: #888; font-size: 13px;">['.implode(", ", $universities).']</span>';
            echo '</span>';
            echo '<span class="badge badge-pill" style="background-color: ' . $badgeColor . ' !important; color: white; font-size: 15px; position: absolute; right: 50px; top: 50%; transform: translateY(-50%); min-width: 90px; text-align: right; display: flex; align-items: center; justify-content: center;">'.$status.'</span>';
            echo '<i class="fas fa-chevron-down checklist-dropdown-icon" id="dropdown-icon-' . $checklistId . '" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: #6c757d; transition: transform 0.3s ease;"></i>';
            echo '</li>';
            // File upload section (initially hidden)
            echo '<li class="list-group-item checklist-files-section" id="checklist-files-' . $checklistId . '" style="display: none; background-color: #f8f9fa; border-top: none; padding: 20px;">';
            echo '<div class="file-upload-container">';
            echo '<h6 style="margin-bottom: 15px; color: #333;">Upload File for: <strong>' . htmlspecialchars($checklistName) . '</strong></h6>';
            echo '<div class="file-drop-zone" id="drop-zone-' . $checklistId . '">';
            echo '<div class="file-drop-content">';
            echo '<i class="fas fa-cloud-upload-alt" style="font-size: 2em; color: #007bff; margin-bottom: 10px;"></i>';
            echo '<p>Drag and drop a file here or click to browse</p>';
            echo '<input type="file" class="file-input" id="file-input-' . $checklistId . '" accept="*/*" style="display: none;">';
            echo '</div>';
            echo '</div>';
            echo '<div class="file-list" id="file-list-' . $checklistId . '" style="margin-top: 15px;"></div>';
            $hasDocument = false;
            if (!empty($checklistIdToAppIds[$checklistId])) {
                $appIds = $checklistIdToAppIds[$checklistId];
                $in = implode(',', array_map('intval', $appIds));
                $sqlDoc = "SELECT COUNT(*) as cnt FROM applications_checklist WHERE checklistId = $checklistId AND applicationId IN ($in) AND document IS NOT NULL";
                $queryDoc = mysqli_query($link, $sqlDoc);
                if ($rowDoc = mysqli_fetch_assoc($queryDoc)) {
                    if ($rowDoc['cnt'] > 0) $hasDocument = true;
                }
            }
            $jsIsFirstUpload = $hasDocument ? 'false' : 'true';
            echo "<script>window.isFirstUpload_{$checklistId} = {$jsIsFirstUpload};</script>";
            $saveBtnText = 'Save';
            echo '<button class="btn btn-success mt-2" id="save-file-btn-' . $checklistId . '" onclick="saveChecklistFile(event, ' . $checklistId . ')">' . $saveBtnText . '</button>';
            if ($hasDocument) {
                $fileName = '';
                if (!empty($checklistIdToAppIds[$checklistId])) {
                    $appIds = $checklistIdToAppIds[$checklistId];
                    $in = implode(',', array_map('intval', $appIds));
                    $sqlName = "SELECT documentName FROM applications_checklist WHERE checklistId = $checklistId AND applicationId IN ($in) AND document IS NOT NULL AND documentName IS NOT NULL AND documentName != '' LIMIT 1";
                    $queryName = mysqli_query($link, $sqlName);
                    if ($rowName = mysqli_fetch_assoc($queryName)) {
                        $fileName = $rowName['documentName'];
                    }
                }
                if ($fileName) {
                    $fileNameDisplay = '<div class="mb-2"><strong>Uploaded file:</strong> ' . htmlspecialchars($fileName) . '</div>';
                    echo $fileNameDisplay;
                }
                echo '<a href="downloadChecklistDocument.php?studentId=' . $studentId . '&checklistId=' . $checklistId . '" target="_blank" class="btn btn-info mt-2 ml-2">Download</a>';
            }
            echo '<div id="file-upload-msg-' . $checklistId . '" class="mt-2"></div>';
            echo '</div>';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p class="mb-0">No checklist items found for this student.</p>';
    }
} else {
    echo '<p class="mb-0">No checklist items found for this student.</p>';
} 