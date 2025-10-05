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
    
    // Fetch exams
    $sqlExams = "SELECT examId, examName, examScore FROM exams WHERE studentId = $studentId";
    $queryExams = mysqli_query($link, $sqlExams);
    $exams = [];
    while ($row = mysqli_fetch_assoc($queryExams)) {
        $exams[] = $row;
    }
    echo json_encode(['exams' => $exams]);
    exit();
}

if ($action === 'add') {
    $studentId = isset($_POST['studentId']) ? intval($_POST['studentId']) : 0;
    $examName = isset($_POST['examName']) ? trim($_POST['examName']) : '';
    $examScore = isset($_POST['examScore']) ? trim($_POST['examScore']) : '';
    if (!$studentId || !$examName || !$examScore) {
        echo json_encode(['error' => 'Missing required fields']);
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
    
    // Insert exam
    $examNameEsc = mysqli_real_escape_string($link, $examName);
    $examScoreEsc = mysqli_real_escape_string($link, $examScore);
    $sqlAdd = "INSERT INTO exams (studentId, examName, examScore) VALUES ($studentId, '$examNameEsc', '$examScoreEsc')";
    if (!mysqli_query($link, $sqlAdd)) {
        echo json_encode(['error' => 'Failed to add exam']);
        exit();
    }
    // Return updated list
    $sqlExams = "SELECT examId, examName, examScore FROM exams WHERE studentId = $studentId";
    $queryExams = mysqli_query($link, $sqlExams);
    $exams = [];
    while ($row = mysqli_fetch_assoc($queryExams)) {
        $exams[] = $row;
    }
    echo json_encode(['exams' => $exams]);
    exit();
}

if ($action === 'edit') {
    $studentId = isset($_POST['studentId']) ? intval($_POST['studentId']) : 0;
    $examId = isset($_POST['examId']) ? intval($_POST['examId']) : 0;
    $examName = isset($_POST['examName']) ? trim($_POST['examName']) : '';
    $examScore = isset($_POST['examScore']) ? trim($_POST['examScore']) : '';
    if (!$studentId || !$examId || !$examName || !$examScore) {
        echo json_encode(['error' => 'Missing required fields']);
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
    
    // Update exam
    $examNameEsc = mysqli_real_escape_string($link, $examName);
    $examScoreEsc = mysqli_real_escape_string($link, $examScore);
    $sqlEdit = "UPDATE exams SET examName = '$examNameEsc', examScore = '$examScoreEsc' WHERE examId = $examId AND studentId = $studentId";
    if (!mysqli_query($link, $sqlEdit)) {
        echo json_encode(['error' => 'Failed to update exam']);
        exit();
    }
    // Return updated list
    $sqlExams = "SELECT examId, examName, examScore FROM exams WHERE studentId = $studentId";
    $queryExams = mysqli_query($link, $sqlExams);
    $exams = [];
    while ($row = mysqli_fetch_assoc($queryExams)) {
        $exams[] = $row;
    }
    echo json_encode(['exams' => $exams]);
    exit();
}

if ($action === 'delete') {
    $studentId = isset($_POST['studentId']) ? intval($_POST['studentId']) : 0;
    $examId = isset($_POST['examId']) ? intval($_POST['examId']) : 0;
    if (!$studentId || !$examId) {
        echo json_encode(['error' => 'Missing required fields']);
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
    
    // Delete exam
    $sqlDelete = "DELETE FROM exams WHERE examId = $examId AND studentId = $studentId";
    if (!mysqli_query($link, $sqlDelete)) {
        echo json_encode(['error' => 'Failed to delete exam']);
        exit();
    }
    // Return updated list
    $sqlExams = "SELECT examId, examName, examScore FROM exams WHERE studentId = $studentId";
    $queryExams = mysqli_query($link, $sqlExams);
    $exams = [];
    while ($row = mysqli_fetch_assoc($queryExams)) {
        $exams[] = $row;
    }
    echo json_encode(['exams' => $exams]);
    exit();
}

// Placeholder for other actions (add, edit, delete)
echo json_encode(['error' => 'Invalid action']);
exit(); 