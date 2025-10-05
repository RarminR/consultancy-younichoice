<?php
    function getStatusColor($status) {
        $status = trim($status);
        if ($status == "In progress")
            $colorStatus = "#FFA500";
        else if ($status == "Accepted")
            $colorStatus = "#008000";
        else if ($status == "Rejected")
            $colorStatus = "#FF0000";
        else if ($status == "Waitlisted")
            $colorStatus = "#808080";

        return $colorStatus;
    }

    session_start();
    require_once "../configDatabase.php";

    // Check if student is logged in
    if (!isset($_SESSION['typeStudent']) || !isset($_SESSION['idStudent'])) {
        header("location: ../index.php");
        die();
    }

    $studentId = $_SESSION['idStudent'];
    $studentEmail = $_SESSION['emailStudent'];
    $studentName = $_SESSION['fullNameStudent'];

    // Get student data
    $sqlStudentData = "SELECT * FROM studentData WHERE `studentId` = '$studentId'";
    $queryStudentData = mysqli_query($link, $sqlStudentData);

    if (mysqli_num_rows($queryStudentData) == 0) {
        header("location: ../index.php");
        die();
    }

    $dataStudent = mysqli_fetch_assoc($queryStudentData);

    $sqlConsultantData = "SELECT * FROM users WHERE `userId` = '$dataStudent[consultantId]' AND `type` = 0";
    $queryConsultantData = mysqli_query($link, $sqlConsultantData);

    if (mysqli_num_rows($queryConsultantData) == 0) {
        header("location: ../index.php");
        die();
    }

    $dataConsultant = mysqli_fetch_assoc($queryConsultantData);

    // for Universities
    $sqlApplicationsData = "SELECT *
    FROM applicationStatus AS a
    JOIN universities AS u
    ON a.universityId = u.universityId
    WHERE a.studentId = '$studentId' AND u.institutionType = 0;";
    $queryApplicationsData = mysqli_query($link, $sqlApplicationsData);

    $nUniversities = 0;
    while ($row = mysqli_fetch_assoc($queryApplicationsData)) {
        $universityId = $row["universityId"];
        $sqlUniversityName = "SELECT * FROM universities WHERE `universityId` = '$universityId'";
        $queryUniversityName = mysqli_query($link, $sqlUniversityName);
        if (mysqli_num_rows($queryUniversityName) > 0) {
            $rowUniversityInfo = mysqli_fetch_assoc($queryUniversityName);
            $arrUniversityName[$nUniversities] = $rowUniversityInfo["universityName"];
            $arrUniversityCountry[$nUniversities] = $rowUniversityInfo["universityCountry"];
            $arrUniversityAppId[$nUniversities] = $row["applicationId"];
            $arrUniversityAppStatus[$nUniversities] = $row["appStatus"];
            $arrUniversityComission[$nUniversities] = $rowUniversityInfo["commission"];
            $arrUniversityScholarship[$nUniversities] = $row["scholarship"] . "$";

            $nUniversities++;
        }
    }

    // for Summer Schools
    $sqlApplicationsData = "SELECT *
    FROM applicationStatus AS a
    JOIN universities AS u
    ON a.universityId = u.universityId
    WHERE a.studentId = '$studentId' AND u.institutionType = 1;";
    $queryApplicationsData = mysqli_query($link, $sqlApplicationsData);

    $nSummer = 0;
    while ($row = mysqli_fetch_assoc($queryApplicationsData)) {
        $universityId = $row["universityId"];
        $sqlUniversityName = "SELECT * FROM universities WHERE `universityId` = '$universityId'";
        $queryUniversityName = mysqli_query($link, $sqlUniversityName);

        if (mysqli_num_rows($queryUniversityName) > 0) {
            $rowUniversityInfo = mysqli_fetch_assoc($queryUniversityName);
            $arrSummerName[$nSummer] = $rowUniversityInfo["universityName"];
            $arrSummerCountry[$nSummer] = $rowUniversityInfo["universityCountry"];
            $arrSummerAppId[$nSummer] = $row["applicationId"];
            $arrSummerAppStatus[$nSummer] = $row["appStatus"];
            $arrSummerComission[$nSummer] = $rowUniversityInfo["commission"];

            $nSummer++;
        }
    }

    // for Boarding Schools
    $sqlApplicationsData = "SELECT *
    FROM applicationStatus AS a
    JOIN universities AS u
    ON a.universityId = u.universityId
    WHERE a.studentId = '$studentId' AND u.institutionType = 2;";
    $queryApplicationsData = mysqli_query($link, $sqlApplicationsData);

    $nBoarding = 0;
    while ($row = mysqli_fetch_assoc($queryApplicationsData)) {
        $universityId = $row["universityId"];
        $sqlUniversityName = "SELECT * FROM universities WHERE `universityId` = '$universityId'";
        $queryUniversityName = mysqli_query($link, $sqlUniversityName);

        if (mysqli_num_rows($queryUniversityName) > 0) {
            $rowUniversityInfo = mysqli_fetch_assoc($queryUniversityName);
            $arrBoardingName[$nBoarding] = $rowUniversityInfo["universityName"];
            $arrBoardingCountry[$nBoarding] = $rowUniversityInfo["universityCountry"];
            $arrBoardingAppId[$nBoarding] = $row["applicationId"];
            $arrBoardingAppStatus[$nBoarding] = $row["appStatus"];
            $arrBoardingComission[$nBoarding] = $rowUniversityInfo["commission"];

            $nBoarding++;
        }
    }

    // for Meetings
    $sqlMeetings = "SELECT * FROM meetings WHERE `studentId` = '$studentId'";
    $queryMeetings = mysqli_query($link, $sqlMeetings);

    $nMeetings = 0;
    while ($row = mysqli_fetch_assoc($queryMeetings)) {
        $arrMeetingId[$nMeetings] = $row["meetingId"];
        $arrMeetingConsultantId[$nMeetings] = $row["consultantId"];
        $arrMeetingDate[$nMeetings] = $row['meetingDate'];
        $arrMeetingNotes[$nMeetings] = $row['meetingNotes'];
        $arrMeetingTopic[$nMeetings] = $row['meetingTopic'];
        $arrMeetingActivities[$nMeetings] = $row['meetingActivities'];

        if ($arrMeetingTopic[$nMeetings] == "")
            $arrMeetingTopic[$nMeetings] = "Not applicable";
        if ($arrMeetingActivities[$nMeetings] == "")
            $arrMeetingActivities[$nMeetings] = "Not applicable";

        $nMeetings++;
    }

    // for Exams
    $sqlExams = "SELECT * FROM exams WHERE studentId = '$studentId'";
    $queryExams = mysqli_query($link, $sqlExams);
    $nExams = 0;
    while ($row = mysqli_fetch_assoc($queryExams)) {
        $arrExamId[$nExams] = $row["examId"];
        $arrExamName[$nExams] = $row["examName"];
        $arrExamScore[$nExams] = $row["examScore"];
        $nExams++;
    }
?>

<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <title>Student Dashboard - <?php echo $dataStudent["name"]; ?></title>

    <style>
        :root {
            --primary-color: #4f235f;
            --secondary-color: #6c757d;
            --accent-color: #007bff;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-bg: #f8f9fa;
            --white: #ffffff;
            --border-radius: 12px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        #content {
            width: 90%;
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }

        .dashboard-header {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--box-shadow);
            text-align: center;
        }

        .welcome-title {
            color: var(--primary-color);
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .welcome-subtitle {
            color: var(--secondary-color);
            font-size: 1.1rem;
            margin-bottom: 0;
        }

        .info-cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            border-left: 4px solid var(--primary-color);
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }

        .info-card h3 {
            color: var(--primary-color);
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .info-card h3 i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: var(--secondary-color);
            font-size: 0.9rem;
        }

        .info-value {
            color: var(--primary-color);
            font-weight: 500;
        }

        .consultant-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, #6a4c93 100%);
            color: var(--white);
            border-radius: var(--border-radius);
            padding: 25px;
            text-align: center;
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
        }

        .consultant-card h3 {
            color: var(--white);
            margin-bottom: 15px;
        }

        .consultant-name {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .calendly-btn {
            background: var(--white);
            color: var(--primary-color);
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: var(--transition);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .calendly-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            color: var(--primary-color);
            text-decoration: none;
        }

        .nav-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .tab-button {
            background: var(--white);
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            border-radius: 25px;
            padding: 12px 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            min-width: 200px;
            text-align: center;
        }

        .tab-button:hover {
            background: var(--primary-color);
            color: var(--white);
            transform: translateY(-2px);
        }

        .tab-button.active {
            background: var(--primary-color);
            color: var(--white);
            box-shadow: 0 4px 8px rgba(79, 35, 95, 0.3);
        }

        .content-section {
            display: none;
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
        }

        .content-section.visible {
            display: block;
        }

        .section-title {
            color: var(--primary-color);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 25px;
            text-align: center;
        }

        .search-container {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            min-width: 200px;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 35, 95, 0.1);
        }

        .filter-container {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary-color);
        }

        .filter-item label {
            font-weight: 500;
            color: var(--secondary-color);
            margin: 0;
        }

        .list-group-item {
            border: none;
            border-radius: var(--border-radius) !important;
            margin-bottom: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }

        .list-group-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .badge {
            border-radius: 20px;
            padding: 8px 15px;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 25px;
            padding: 8px 20px;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-primary:hover {
            background: #3a1a47;
            border-color: #3a1a47;
            transform: translateY(-1px);
        }

        .meeting-item {
            background: var(--light-bg);
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid var(--accent-color);
        }

        .meeting-date {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .meeting-details {
            color: var(--secondary-color);
            margin-bottom: 5px;
        }

        .meeting-details strong {
            color: var(--primary-color);
        }

        /* Enhanced Meeting Styles */
        .meetings-section {
            margin-bottom: 40px;
        }

        .meetings-section-title {
            color: var(--primary-color);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .meetings-count {
            background: var(--light-bg);
            color: var(--secondary-color);
            font-size: 0.9rem;
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: 500;
        }

        .meeting-item {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            border-left: 4px solid var(--accent-color);
        }

        .meeting-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .upcoming-meeting {
            border-left-color: var(--accent-color);
        }

        .past-meeting {
            border-left-color: var(--success-color);
            opacity: 0.9;
        }

        .meeting-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .meeting-date {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .meeting-time-badge {
            background: var(--accent-color);
            color: var(--white);
            font-size: 0.7rem;
            padding: 3px 8px;
            border-radius: 10px;
            font-weight: 600;
            margin-left: 10px;
        }

        .meeting-time-badge.completed {
            background: var(--success-color);
        }

        .meeting-content {
            color: var(--secondary-color);
        }

        .meeting-details {
            margin-bottom: 8px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        .meeting-details strong {
            color: var(--primary-color);
            min-width: 80px;
        }

        .meeting-details i {
            color: var(--accent-color);
            margin-right: 5px;
            width: 16px;
        }

        .meeting-notes {
            font-style: italic;
            color: var(--secondary-color);
            background: var(--light-bg);
            padding: 8px 12px;
            border-radius: 6px;
            margin-top: 5px;
            display: block;
        }

        .no-meetings-message {
            text-align: center;
            padding: 30px;
            color: var(--secondary-color);
        }

        .no-meetings-message i {
            font-size: 2rem;
            margin-bottom: 15px;
            display: block;
        }

        .no-meetings-message p {
            margin: 0;
            font-size: 1.1rem;
        }

        .popup-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .popup {
            background: var(--white);
            padding: 30px;
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .popup h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .close-btn {
            background: var(--danger-color);
            color: var(--white);
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            float: right;
            font-weight: 600;
            transition: var(--transition);
        }

        .close-btn:hover {
            background: #c82333;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            #content {
                width: 95%;
                padding: 10px;
            }

            .welcome-title {
                font-size: 2rem;
            }

            .info-cards-container {
                grid-template-columns: 1fr;
            }

            .nav-buttons {
                flex-direction: column;
                align-items: center;
            }

            .tab-button {
                width: 100%;
                max-width: 300px;
            }

            .search-container {
                flex-direction: column;
            }

            .search-input {
                width: 100%;
            }
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #3a1a47;
        }

        /* Mini Navigation Buttons */
        .mini-nav-btn {
            background: var(--white);
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            font-size: 1rem;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 25px;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .mini-nav-btn:hover {
            background: var(--primary-color);
            color: var(--white);
            transform: translateY(-2px);
        }

        .mini-nav-btn.mini-nav-selected {
            background: var(--primary-color);
            color: var(--white);
            box-shadow: 0 4px 8px rgba(79, 35, 95, 0.3);
        }

        /* File Upload Styles */
        .file-drop-zone {
            border: 2px dashed var(--accent-color);
            border-radius: var(--border-radius);
            padding: 30px;
            text-align: center;
            background-color: var(--light-bg);
            transition: var(--transition);
            cursor: pointer;
        }

        .file-drop-zone:hover {
            border-color: var(--primary-color);
            background-color: #e9ecef;
        }

        .file-drop-zone.dragover {
            border-color: var(--success-color);
            background-color: #d4edda;
        }

        .file-drop-content {
            color: var(--secondary-color);
        }

        .file-drop-content i {
            display: block;
            margin-bottom: 10px;
            font-size: 2rem;
            color: var(--accent-color);
        }

        .file-list {
            max-height: 200px;
            overflow-y: auto;
        }

        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            margin: 5px 0;
            background-color: var(--white);
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }

        .file-item .file-name {
            flex: 1;
            margin-right: 10px;
            word-break: break-all;
        }

        .file-item .file-size {
            color: var(--secondary-color);
            font-size: 0.9em;
            margin-right: 10px;
        }

        .file-item .remove-file {
            background: var(--danger-color);
            color: var(--white);
            border: none;
            border-radius: 3px;
            padding: 2px 6px;
            cursor: pointer;
            font-size: 0.8em;
        }

        .file-item .remove-file:hover {
            background: #c82333;
        }

        /* Checklist Styles */
        .checklist-dropdown-icon {
            font-size: 14px;
            transition: var(--transition);
        }

        .checklist-dropdown-icon.rotated {
            transform: translateY(-50%) rotate(180deg) !important;
        }

        .checklist-item {
            cursor: pointer;
            transition: var(--transition);
        }

        .checklist-item:hover {
            background-color: var(--light-bg);
        }

        /* Activity and Exam Cards */
        .activity-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: var(--box-shadow);
            border-left: 4px solid var(--accent-color);
        }

        .activity-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        /* Table Styles */
        .table {
            background: var(--white);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
        }

        .table th {
            background: var(--primary-color);
            color: var(--white);
            border: none;
            font-weight: 600;
        }

        .table td {
            border-color: #e9ecef;
        }

        /* Form Controls */
        .form-control {
            border-radius: var(--border-radius);
            border: 2px solid #e9ecef;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 35, 95, 0.1);
        }

        /* Button Enhancements */
        .btn {
            border-radius: 25px;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-secondary:hover {
            background: #5a6268;
            border-color: #5a6268;
        }

        .btn-success {
            background: var(--success-color);
            border-color: var(--success-color);
        }

        .btn-success:hover {
            background: #218838;
            border-color: #218838;
        }

        .btn-warning {
            background: var(--warning-color);
            border-color: var(--warning-color);
            color: #212529;
        }

        .btn-warning:hover {
            background: #e0a800;
            border-color: #e0a800;
            color: #212529;
        }

        .btn-danger {
            background: var(--danger-color);
            border-color: var(--danger-color);
        }

        .btn-danger:hover {
            background: #c82333;
            border-color: #c82333;
        }

        .btn-info {
            background: #17a2b8;
            border-color: #17a2b8;
        }

        .btn-info:hover {
            background: #138496;
            border-color: #138496;
        }
    </style>
  </head>

  <div class="popup-container" id="popup-container">
    <div class="popup">
        <button class="close-btn" onclick="closePopup()">×</button>
        <h2>Package Details</h2>
        <p id="package-details"><?php echo $dataStudent['packageDetails']; ?></p>
    </div>
  </div>

  <div id="content">
    <?php include("navbarStudent.php"); ?>
    <br>
    <br>
    <br>
    <br>
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <h1 class="welcome-title">Welcome back, <?php echo $dataStudent['name']; ?>!</h1>
        <p class="welcome-subtitle">Track your academic journey and stay connected with your consultant</p>
    </div>

    <!-- Information Cards -->
    <div class="info-cards-container">
        <div class="info-card">
            <h3><i class="fas fa-user-graduate"></i> Personal Information</h3>
            <div class="info-item">
                <span class="info-label">Email:</span>
                <span class="info-value"><?php echo $dataStudent['email']; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Parent Email:</span>
                <span class="info-value"><?php echo $dataStudent['emailParent']; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Phone:</span>
                <span class="info-value"><?php echo $dataStudent['phoneNumber']; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Location:</span>
                <span class="info-value"><?php echo htmlspecialchars($dataStudent['judet']); ?></span>
            </div>
        </div>

        <div class="info-card">
            <h3><i class="fas fa-school"></i> Academic Details</h3>
            <div class="info-item">
                <span class="info-label">High School:</span>
                <span class="info-value"><?php echo $dataStudent['highSchool']; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Grade:</span>
                <?php
                    $currentYear = date('Y');
                    $currentMonth = date('m');
                    if ($currentMonth >= 8) {
                        $grade = 12 - ($dataStudent['graduationYear'] - $currentYear) + 1;
                    } else {
                        $grade = 12 - ($dataStudent['graduationYear'] - $currentYear);
                    }
                ?>
                <span class="info-value"><?php echo $grade; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Graduation Year:</span>
                <span class="info-value"><?php echo $dataStudent['graduationYear']; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Sign Grade:</span>
                <span class="info-value"><?php echo $dataStudent['signGrade']; ?></span>
            </div>
        </div>
    </div>

    <!-- Consultant Card -->
    <div class="consultant-card">
        <h3><i class="fas fa-user-tie"></i> Your Consultant</h3>
        <div class="consultant-name"><?php echo $dataConsultant['fullName']; ?></div>
        <p>Your dedicated academic advisor is here to guide you through your journey</p>
        <a href="<?php echo $dataConsultant["calendlyLink"]; ?>" target="_blank" class="calendly-btn">
            <i class="fas fa-calendar-alt"></i> Schedule Meeting
        </a>
    </div>

    <!-- Navigation Tabs -->
    <div class="nav-buttons">
        <button id="meetings-btn" class="tab-button active" onclick="showSection('meetings-section', 'meetings-btn')">
            <i class="fas fa-calendar-check"></i> Meetings
        </button>
        <?php if ($nUniversities > 0) { ?>
            <button id="university-applications-btn" class="tab-button" onclick="showSection('university-applications-section', 'university-applications-btn')">
                <i class="fas fa-university"></i> University Applications
            </button>
        <?php } ?>
        <?php if ($nSummer > 0) { ?>
            <button id="summer-applications-btn" class="tab-button" onclick="showSection('summer-applications-section', 'summer-applications-btn')">
                <i class="fas fa-sun"></i> Summer Schools
            </button>
        <?php } ?>
        <?php if ($nBoarding > 0) { ?>
            <button id="boarding-applications-btn" class="tab-button" onclick="showSection('boarding-applications-section', 'boarding-applications-btn')">
                <i class="fas fa-home"></i> Boarding Schools
            </button>
        <?php } ?>
        <button id="application-overview-btn" class="tab-button" onclick="showSection('application-overview-section', 'application-overview-btn')">
            <i class="fas fa-chart-line"></i> Application Overview
        </button>
    </div>

    <!-- Meetings -->
    <div id="meetings-section" class="content-section visible">
        <h2 class="section-title"><i class="fas fa-calendar-check"></i> Your Meetings</h2>
        
        <?php if ($nMeetings > 0) { ?>
            <?php
            // Separate meetings into past and upcoming
            $pastMeetings = [];
            $upcomingMeetings = [];
            $currentDate = date('Y-m-d');
            
            for ($i = 0; $i < $nMeetings; $i++) {
                $meetingDate = $arrMeetingDate[$i];
                if ($meetingDate < $currentDate) {
                    $pastMeetings[] = [
                        'id' => $arrMeetingId[$i],
                        'date' => $arrMeetingDate[$i],
                        'topic' => $arrMeetingTopic[$i],
                        'activities' => $arrMeetingActivities[$i],
                        'notes' => $arrMeetingNotes[$i]
                    ];
                } else {
                    $upcomingMeetings[] = [
                        'id' => $arrMeetingId[$i],
                        'date' => $arrMeetingDate[$i],
                        'topic' => $arrMeetingTopic[$i],
                        'activities' => $arrMeetingActivities[$i],
                        'notes' => $arrMeetingNotes[$i]
                    ];
                }
            }
            
            // Sort meetings by date
            usort($pastMeetings, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']); // Past meetings: newest first
            });
            usort($upcomingMeetings, function($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']); // Upcoming meetings: oldest first
            });
            ?>
            
            <!-- Upcoming Meetings -->
            <div class="meetings-section">
                <h3 class="meetings-section-title">
                    <i class="fas fa-calendar-plus text-primary"></i> Upcoming Meetings
                    <span class="meetings-count">(<?php echo count($upcomingMeetings); ?>)</span>
                </h3>
                
                <?php if (count($upcomingMeetings) > 0) { ?>
                    <div class="meetings-container">
                        <?php foreach ($upcomingMeetings as $meeting) { ?>
                            <div class="meeting-item upcoming-meeting">
                                <div class="meeting-header">
                                    <div class="meeting-date">
                                        <i class="fas fa-calendar-alt text-primary"></i> 
                                        <?php echo date('F j, Y', strtotime($meeting['date'])); ?>
                                        <span class="meeting-time-badge">Upcoming</span>
                                    </div>
                                    <div class="meeting-actions">
                                        <a href="meeting?meetingId=<?php echo $meeting['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                    </div>
                                </div>
                                <div class="meeting-content">
                                    <div class="meeting-details">
                                        <strong><i class="fas fa-comments"></i> Topics:</strong> 
                                        <?php echo $meeting['topic']; ?>
                                    </div>
                                    <div class="meeting-details">
                                        <strong><i class="fas fa-tasks"></i> Activities:</strong> 
                                        <?php echo $meeting['activities']; ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="no-meetings-message">
                        <i class="fas fa-calendar-times text-muted"></i>
                        <p>No upcoming meetings scheduled</p>
                    </div>
                <?php } ?>
            </div>

            <!-- Past Meetings -->
            <div class="meetings-section">
                <h3 class="meetings-section-title">
                    <i class="fas fa-history text-secondary"></i> Past Meetings
                    <span class="meetings-count">(<?php echo count($pastMeetings); ?>)</span>
                </h3>
                
                <?php if (count($pastMeetings) > 0) { ?>
                    <div class="meetings-container">
                        <?php foreach ($pastMeetings as $meeting) { ?>
                            <div class="meeting-item past-meeting">
                                <div class="meeting-header">
                                    <div class="meeting-date">
                                        <i class="fas fa-calendar-check text-success"></i> 
                                        <?php echo date('F j, Y', strtotime($meeting['date'])); ?>
                                        <span class="meeting-time-badge completed">Completed</span>
                                    </div>
                                    <div class="meeting-actions">
                                        <a href="meeting?meetingId=<?php echo $meeting['id']; ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                    </div>
                                </div>
                                <div class="meeting-content">
                                    <div class="meeting-details">
                                        <strong><i class="fas fa-comments"></i> Topics:</strong> 
                                        <?php echo $meeting['topic']; ?>
                                    </div>
                                    <div class="meeting-details">
                                        <strong><i class="fas fa-tasks"></i> Activities:</strong> 
                                        <?php echo $meeting['activities']; ?>
                                    </div>
                                    <?php if (!empty($meeting['notes'])) { ?>
                                        <div class="meeting-details">
                                            <strong><i class="fas fa-sticky-note"></i> Notes:</strong> 
                                            <span class="meeting-notes"><?php echo $meeting['notes']; ?></span>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="no-meetings-message">
                        <i class="fas fa-calendar-times text-muted"></i>
                        <p>No past meetings found</p>
                    </div>
                <?php } ?>
            </div>
            
        <?php } else { ?>
            <div class="text-center" style="padding: 40px;">
                <i class="fas fa-calendar-times" style="font-size: 3rem; color: var(--secondary-color); margin-bottom: 20px;"></i>
                <h3 style="color: var(--secondary-color);">No meetings scheduled yet</h3>
                <p style="color: var(--secondary-color);">Your consultant will schedule meetings as needed.</p>
            </div>
        <?php } ?>
    </div>

    <?php if ($nUniversities > 0) { ?>
    <!-- Universities --> 
    <div id="university-applications-section" class="content-section">
        <h2 class="section-title"><i class="fas fa-university"></i> University Applications</h2>
        
        <div class="search-container">
            <input type="text" class="search-input" id="search-bar-university-name" onkeyup="searchFunctionUniversities()" placeholder="Search by university name...">
            <input type="text" class="search-input" id="search-bar-university-country" onkeyup="searchFunctionUniversities()" placeholder="Search by country...">
        </div>

        <div class="applications-container">
            <?php for ($i = 0; $i < $nUniversities; $i++) { ?>
                <div class="list-group-item university-application">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h5 class="university-name mb-1"><?php echo $arrUniversityName[$i]; ?></h5>
                            <p class="university-country mb-2">
                                <i class="fas fa-map-marker-alt"></i> <?php echo $arrUniversityCountry[$i]; ?>
                            </p>
                            <?php if (trim($arrUniversityAppStatus[$i]) == "Accepted") { ?>
                                <p class="mb-1">
                                    <i class="fas fa-graduation-cap"></i> 
                                    <strong>Scholarship:</strong> 
                                    <span class="university-commission text-success"><?php echo $arrUniversityScholarship[$i]; ?></span>
                                </p>
                            <?php } ?>
                        </div>
                        <div class="d-flex flex-column align-items-end">
                            <span class="badge mb-2" style="background-color: <?php echo getStatusColor($arrUniversityAppStatus[$i]); ?> !important;">
                                <?php echo $arrUniversityAppStatus[$i]; ?>
                            </span>
                            <a href="application?applicationId=<?php echo $arrUniversityAppId[$i]; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <!-- Summer Schools --> 
    <div id="summer-applications-section" class="content-section">
        <h2 class="section-title"><i class="fas fa-sun"></i> Summer School Applications</h2>
        
        <div class="search-container">
            <input type="text" class="search-input" id="search-bar-summer-name" onkeyup="searchFunctionSummer()" placeholder="Search by summer school name...">
            <input type="text" class="search-input" id="search-bar-summer-country" onkeyup="searchFunctionSummer()" placeholder="Search by country...">
        </div>

        <div class="applications-container">
            <?php for ($i = 0; $i < $nSummer; $i++) { ?>
                <div class="list-group-item summer-application">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h5 class="summer-name mb-1"><?php echo $arrSummerName[$i]; ?></h5>
                            <p class="summer-country mb-2">
                                <i class="fas fa-map-marker-alt"></i> <?php echo $arrSummerCountry[$i]; ?>
                            </p>
                        </div>
                        <div class="d-flex flex-column align-items-end">
                            <span class="badge mb-2" style="background-color: <?php echo getStatusColor($arrSummerAppStatus[$i]); ?> !important;">
                                <?php echo $arrSummerAppStatus[$i]; ?>
                            </span>
                            <a href="application?applicationId=<?php echo $arrSummerAppId[$i]; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- Boarding Schools --> 
    <div id="boarding-applications-section" class="content-section">
        <h2 class="section-title"><i class="fas fa-home"></i> Boarding School Applications</h2>
        
        <div class="search-container">
            <input type="text" class="search-input" id="search-bar-boarding-name" onkeyup="searchFunctionBoarding()" placeholder="Search by boarding school name...">
            <input type="text" class="search-input" id="search-bar-boarding-country" onkeyup="searchFunctionBoarding()" placeholder="Search by country...">
        </div>

        <div class="applications-container">
            <?php for ($i = 0; $i < $nBoarding; $i++) { ?>
                <div class="list-group-item boarding-application">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h5 class="boarding-name mb-1"><?php echo $arrBoardingName[$i]; ?></h5>
                            <p class="boarding-country mb-2">
                                <i class="fas fa-map-marker-alt"></i> <?php echo $arrBoardingCountry[$i]; ?>
                            </p>
                        </div>
                        <div class="d-flex flex-column align-items-end">
                            <span class="badge mb-2" style="background-color: <?php echo getStatusColor($arrBoardingAppStatus[$i]); ?> !important;">
                                <?php echo $arrBoardingAppStatus[$i]; ?>
                            </span>
                            <a href="application?applicationId=<?php echo $arrBoardingAppId[$i]; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- Application Overview -->
    <div id="application-overview-section" class="content-section">
        <h2 class="section-title"><i class="fas fa-chart-line"></i> Application Overview</h2>
        
        <!-- Mini-Nav Bar for Overview Subsections -->
        <div id="overview-mini-nav" style="display: flex; justify-content: center; margin-bottom: 30px; gap: 15px; flex-wrap: wrap;">
            <button id="mini-nav-checklist" class="mini-nav-btn mini-nav-selected" onclick="showOverviewSubsection('checklist')">
                <i class="fas fa-tasks"></i> Checklist
            </button>
            <button id="mini-nav-activities" class="mini-nav-btn" onclick="showOverviewSubsection('activities')">
                <i class="fas fa-running"></i> Activities
            </button>
            <button id="mini-nav-exams" class="mini-nav-btn" onclick="showOverviewSubsection('exams')">
                <i class="fas fa-file-alt"></i> Exams
            </button>
            <button id="mini-nav-tasks" class="mini-nav-btn" onclick="showOverviewSubsection('tasks')">
                <i class="fas fa-clipboard-list"></i> Tasks
            </button>
        </div>

        <div id="overview-checklist-section">
            <div class="card mb-4 p-4" style="background: var(--light-bg); border-radius: var(--border-radius); max-width: 900px; margin: auto; border: none; box-shadow: var(--box-shadow);">
                <div id="checklist-container">
                    <div class="text-center" style="padding: 40px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary-color);"></i>
                        <p style="color: var(--secondary-color); margin-top: 15px;">Loading checklist items...</p>
                    </div>
                </div>
            </div>
        </div>

        <div id="overview-activities-section" style="display: none;">
            <div class="card mb-4 p-4" style="background: var(--light-bg); border-radius: var(--border-radius); max-width: 700px; margin: auto; border: none; box-shadow: var(--box-shadow);">
                <button id="show-add-activity-btn" class="btn btn-primary w-100 mb-3">
                    <i class="fas fa-plus"></i> Add Activity
                </button>
                <form id="add-activity-form" class="mb-3" style="display:none;">
                    <div class="form-group mb-3">
                        <select class="form-control" name="activityType" style="border-radius: var(--border-radius);">
                            <option value="">Select activity type</option>
                            <option value="Academic">Academic</option>
                            <option value="Art">Art</option>
                            <option value="Athletics: Club">Athletics: Club</option>
                            <option value="Athletics: JV/Varsity">Athletics: JV/Varsity</option>
                            <option value="Career-Oriented">Career-Oriented</option>
                            <option value="Community Service (Volunteer)">Community Service (Volunteer)</option>
                            <option value="Computer/Technology">Computer/Technology</option>
                            <option value="Cultural">Cultural</option>
                            <option value="Dance">Dance</option>
                            <option value="Debate/Speech">Debate/Speech</option>
                            <option value="Environmental">Environmental</option>
                            <option value="Family Responsibilities">Family Responsibilities</option>
                            <option value="Foreign Exchange">Foreign Exchange</option>
                            <option value="Internship">Internship</option>
                            <option value="Journalism/Publication">Journalism/Publication</option>
                            <option value="Junior R.O.T.C.">Junior R.O.T.C.</option>
                            <option value="LGBT">LGBT</option>
                            <option value="Music: Instrumental">Music: Instrumental</option>
                            <option value="Music: Vocal">Music: Vocal</option>
                            <option value="Religious">Religious</option>
                            <option value="Research">Research</option>
                            <option value="Robotics">Robotics</option>
                            <option value="School Spirit">School Spirit</option>
                            <option value="Science/Math">Science/Math</option>
                            <option value="Student Govt./Politics">Student Govt./Politics</option>
                            <option value="Theater/Drama">Theater/Drama</option>
                            <option value="Work (Paid)">Work (Paid)</option>
                            <option value="Other Club/Activity">Other Club/Activity</option>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <input type="text" class="form-control" name="activityOrganization" placeholder="Organization" style="border-radius: var(--border-radius);">
                    </div>
                    <div class="form-group mb-3">
                        <input type="text" class="form-control" name="activityPosition" placeholder="Position/Role" style="border-radius: var(--border-radius);">
                    </div>
                    <div class="form-group mb-3">
                        <input type="number" class="form-control" name="hoursPerWeek" placeholder="Hours/Week" min="0" style="border-radius: var(--border-radius);">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="activityStartDate" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="activityStartDate" name="startDate" style="border-radius: var(--border-radius);">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="activityEndDate" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="activityEndDate" name="endDate" style="border-radius: var(--border-radius);">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <textarea class="form-control" name="activityDescription" placeholder="Description" rows="3" style="border-radius: var(--border-radius);"></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Add Activity
                        </button>
                        <button type="button" id="cancel-add-activity-btn" class="btn btn-secondary w-100 ml-2">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </form>
                <div id="add-activity-error" class="text-danger mb-3"></div>
            </div>
            <div id="activities-scroll-container" style="max-height: 600px; overflow-y: auto;">
                <div id="activities-table-container"></div>
            </div>
        </div>

        <div id="overview-exams-section" style="display: none;">
            <div id="overview-exams">
                <div class="card mb-4 p-4" style="background: var(--light-bg); border-radius: var(--border-radius); max-width: 800px; margin: auto; border: none; box-shadow: var(--box-shadow);">
                    <!-- Add Exam Button -->
                    <button id="show-add-exam-btn" class="btn btn-primary mb-3">
                        <i class="fas fa-plus"></i> Add Exam
                    </button>
                    <form id="add-exam-form" class="form-inline mb-3" style="display:none; justify-content:center; flex-wrap: wrap; gap: 10px;">
                        <div class="form-group">
                            <input type="text" class="form-control" id="examName" name="examName" placeholder="Exam Name" required style="border-radius: var(--border-radius);">
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" id="examScore" name="examScore" placeholder="Exam Score" required style="border-radius: var(--border-radius);">
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Add Exam
                        </button>
                        <button type="button" id="cancel-add-exam-btn" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </form>
                    <div id="add-exam-error" class="text-danger mb-3"></div>
                    <div id="exams-table-container"></div>
                </div>
            </div>
        </div>

        <div id="overview-tasks-section" style="display: none;">
            <div class="card mb-4 p-4" style="background: var(--light-bg); border-radius: var(--border-radius); max-width: 900px; margin: auto; border: none; box-shadow: var(--box-shadow);">
                <button id="show-add-task-btn" class="btn btn-primary w-100 mb-3">
                    <i class="fas fa-plus"></i> Add Task
                </button>
                <form id="add-task-form" class="mb-3" style="display:none;">
                    <div class="form-group mb-3">
                        <textarea class="form-control" name="taskText" placeholder="Task description" rows="3" required style="border-radius: var(--border-radius);"></textarea>
                    </div>
                    <div class="form-group mb-3">
                        <label for="taskDeadline" class="form-label">Deadline (optional)</label>
                        <input type="date" class="form-control" id="taskDeadline" name="taskDeadline" style="border-radius: var(--border-radius);">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Add Task
                        </button>
                        <button type="button" id="cancel-add-task-btn" class="btn btn-secondary w-100 ml-2">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </form>
                <div id="add-task-error" class="text-danger mb-3"></div>
                
                <!-- Task Filtering and Sorting -->
                <div class="task-controls mb-3" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div class="task-filters" style="display: flex; gap: 8px; align-items: center;">
                        <label for="task-status-filter" class="form-label mb-0" style="font-size: 0.9em; color: var(--secondary-color);">Filter:</label>
                        <select id="task-status-filter" class="form-control form-control-sm" style="width: auto; border-radius: var(--border-radius);">
                            <option value="all">All Tasks</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Done">Done</option>
                            <option value="meeting">Meeting Tasks</option>
                            <option value="general">General Tasks</option>
                        </select>
                    </div>
                    <div class="task-sorting" style="display: flex; gap: 8px; align-items: center;">
                        <label for="task-sort-by" class="form-label mb-0" style="font-size: 0.9em; color: var(--secondary-color);">Sort by:</label>
                        <select id="task-sort-by" class="form-control form-control-sm" style="width: auto; border-radius: var(--border-radius);">
                            <option value="deadline">Deadline</option>
                            <option value="status">Status</option>
                            <option value="created">Created Date</option>
                            <option value="meeting">Meeting Context</option>
                        </select>
                    </div>
                </div>
                
                <div id="tasks-list-container">
                    <div class="text-center text-muted">
                        <i class="fas fa-tasks" style="font-size: 2em; margin-bottom: 10px;"></i>
                        <p>No tasks found for this student.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>

  <!-- Optional JavaScript -->
  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

  <script>
    function showSection(sectionId, buttonId) {
        // Hide all sections
        document.getElementById('application-overview-section').classList.remove('visible');
        if (document.getElementById('university-applications-section')) {
            document.getElementById('university-applications-section').classList.remove('visible');
        }
        if (document.getElementById('summer-applications-section')) {
            document.getElementById('summer-applications-section').classList.remove('visible');
        }
        if (document.getElementById('boarding-applications-section')) {
            document.getElementById('boarding-applications-section').classList.remove('visible');
        }
        document.getElementById('meetings-section').classList.remove('visible');

        // Show the selected section
        document.getElementById(sectionId).classList.add('visible');

        // Remove active from all buttons
        var buttons = document.querySelectorAll('.tab-button');
        buttons.forEach(function(btn) {
            btn.classList.remove('active');
        });

        // Add active to clicked button
        document.getElementById(buttonId).classList.add('active');
    }

    function showOverviewSubsection(section) {
        // Hide all
        document.getElementById('overview-checklist-section').style.display = 'none';
        document.getElementById('overview-activities-section').style.display = 'none';
        document.getElementById('overview-exams-section').style.display = 'none';
        document.getElementById('overview-tasks-section').style.display = 'none';
        // Remove selected class
        document.getElementById('mini-nav-checklist').classList.remove('mini-nav-selected');
        document.getElementById('mini-nav-activities').classList.remove('mini-nav-selected');
        document.getElementById('mini-nav-exams').classList.remove('mini-nav-selected');
        document.getElementById('mini-nav-tasks').classList.remove('mini-nav-selected');
        // Show selected
        if (section === 'checklist') {
            document.getElementById('overview-checklist-section').style.display = '';
            document.getElementById('mini-nav-checklist').classList.add('mini-nav-selected');
            fetchAndRenderChecklist();
        } else if (section === 'activities') {
            document.getElementById('overview-activities-section').style.display = '';
            document.getElementById('mini-nav-activities').classList.add('mini-nav-selected');
            fetchAndRenderActivities();
        } else if (section === 'exams') {
            document.getElementById('overview-exams-section').style.display = '';
            document.getElementById('mini-nav-exams').classList.add('mini-nav-selected');
            fetchAndRenderExams();
        } else if (section === 'tasks') {
            document.getElementById('overview-tasks-section').style.display = '';
            document.getElementById('mini-nav-tasks').classList.add('mini-nav-selected');
            fetchAndRenderTasks();
        }
    }

    function searchFunctionUniversities() {
        var inputName = document.getElementById("search-bar-university-name");
        var inputCountry = document.getElementById("search-bar-university-country");
        var inputCheckbox1 = document.getElementById("commissionable-university");
        var inputCheckbox2 = document.getElementById("non-commissionable-university");

        var filterName = inputName.value.toUpperCase();
        var filterCountry = inputCountry.value.toUpperCase();
        var checkbox1 = inputCheckbox1.checked;
        var checkbox2 = inputCheckbox2.checked;

        var list = document.getElementById("university-applications-list");
        var universities = list.getElementsByClassName("university-application");

        for (var i = 0; i < universities.length; i++) {
            var name = universities[i].getElementsByClassName("university-name")[0].innerHTML;
            var country = universities[i].getElementsByClassName("university-country")[0].innerHTML;
            var commission = universities[i].getElementsByClassName("university-commission")[0].innerHTML;
            
            if (name.toUpperCase().indexOf(filterName) > -1 && country.toUpperCase().indexOf(filterCountry) > -1 && ((commission > 0 && checkbox1) || (commission == 0 && checkbox2))) {
                universities[i].style.display = "";
            } else {
                universities[i].style.display = "none";
            }
        }
    }

    function searchFunctionSummer() {
        var inputName = document.getElementById("search-bar-summer-name");
        var inputCountry = document.getElementById("search-bar-summer-country");
        var inputCheckbox1 = document.getElementById("commissionable-summer");
        var inputCheckbox2 = document.getElementById("non-commissionable-summer");

        var filterName = inputName.value.toUpperCase();
        var filterCountry = inputCountry.value.toUpperCase();
        var checkbox1 = inputCheckbox1.checked;
        var checkbox2 = inputCheckbox2.checked;

        var list = document.getElementById("summer-applications-list");
        var universities = list.getElementsByClassName("summer-application");

        for (var i = 0; i < universities.length; i++) {
            var name = universities[i].getElementsByClassName("summer-name")[0].innerHTML;
            var country = universities[i].getElementsByClassName("summer-country")[0].innerHTML;
            var commission = universities[i].getElementsByClassName("summer-commission")[0].innerHTML;
            
            if (name.toUpperCase().indexOf(filterName) > -1 && country.toUpperCase().indexOf(filterCountry) > -1 && ((commission > 0 && checkbox1) || (commission == 0 && checkbox2))) {
                universities[i].style.display = "";
            } else {
                universities[i].style.display = "none";
            }
        }
    }

    function searchFunctionBoarding() {
        var inputName = document.getElementById("search-bar-boarding-name");
        var inputCountry = document.getElementById("search-bar-boarding-country");
        var inputCheckbox1 = document.getElementById("commissionable-boarding");
        var inputCheckbox2 = document.getElementById("non-commissionable-boarding");

        var filterName = inputName.value.toUpperCase();
        var filterCountry = inputCountry.value.toUpperCase();
        var checkbox1 = inputCheckbox1.checked;
        var checkbox2 = inputCheckbox2.checked;

        var list = document.getElementById("boarding-applications-list");
        var universities = list.getElementsByClassName("boarding-application");

        for (var i = 0; i < universities.length; i++) {
            var name = universities[i].getElementsByClassName("boarding-name")[0].innerHTML;
            var country = universities[i].getElementsByClassName("boarding-country")[0].innerHTML;
            var commission = universities[i].getElementsByClassName("boarding-commission")[0].innerHTML;
            
            if (name.toUpperCase().indexOf(filterName) > -1 && country.toUpperCase().indexOf(filterCountry) > -1 && ((commission > 0 && checkbox1) || (commission == 0 && checkbox2))) {
                universities[i].style.display = "";
            } else {
                universities[i].style.display = "none";
            }
        }
    }

    function showPopup() {
        document.getElementById('popup-container').style.display = 'flex';
    }

    function closePopup() {
        document.getElementById('popup-container').style.display = 'none';
    }

    // Default: show checklist
    window.addEventListener('DOMContentLoaded', function() {
        showOverviewSubsection('checklist');
        fetchAndRenderChecklist();
    });

    // Activities functionality
    let editingActivityId = null;

    function fetchAndRenderActivities() {
        const studentId = <?php echo json_encode($studentId); ?>;
        fetch('../activitiesActions.php?action=fetch&studentId=' + studentId)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('activities-table-container');
                if (data.error) {
                    container.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
                    return;
                }
                const activities = data.activities;
                if (!activities || activities.length === 0) {
                    container.innerHTML = '<p>No activities found for this student.</p>';
                    return;
                }
                let html = '';
                activities.forEach(activity => {
                    const activityIdInt = parseInt(activity.activityId);
                    if (editingActivityId === activityIdInt) {
                        html += `<div class="activity-card mb-4 p-3" style="background:#f8f9fa; border-radius:8px; max-width:700px; margin:auto; text-align:left;">
                            <form onsubmit="return saveActivityEdit(${activityIdInt})">
                                <div class="form-group mb-2"><select class="form-control" id="edit-activity-type">
                                    <option value="">Select activity type</option>
                                    <option value="Academic" ${activity.activityType === 'Academic' ? 'selected' : ''}>Academic</option>
                                    <option value="Art" ${activity.activityType === 'Art' ? 'selected' : ''}>Art</option>
                                    <option value="Athletics: Club" ${activity.activityType === 'Athletics: Club' ? 'selected' : ''}>Athletics: Club</option>
                                    <option value="Athletics: JV/Varsity" ${activity.activityType === 'Athletics: JV/Varsity' ? 'selected' : ''}>Athletics: JV/Varsity</option>
                                    <option value="Career-Oriented" ${activity.activityType === 'Career-Oriented' ? 'selected' : ''}>Career-Oriented</option>
                                    <option value="Community Service (Volunteer)" ${activity.activityType === 'Community Service (Volunteer)' ? 'selected' : ''}>Community Service (Volunteer)</option>
                                    <option value="Computer/Technology" ${activity.activityType === 'Computer/Technology' ? 'selected' : ''}>Computer/Technology</option>
                                    <option value="Cultural" ${activity.activityType === 'Cultural' ? 'selected' : ''}>Cultural</option>
                                    <option value="Dance" ${activity.activityType === 'Dance' ? 'selected' : ''}>Dance</option>
                                    <option value="Debate/Speech" ${activity.activityType === 'Debate/Speech' ? 'selected' : ''}>Debate/Speech</option>
                                    <option value="Environmental" ${activity.activityType === 'Environmental' ? 'selected' : ''}>Environmental</option>
                                    <option value="Family Responsibilities" ${activity.activityType === 'Family Responsibilities' ? 'selected' : ''}>Family Responsibilities</option>
                                    <option value="Foreign Exchange" ${activity.activityType === 'Foreign Exchange' ? 'selected' : ''}>Foreign Exchange</option>
                                    <option value="Internship" ${activity.activityType === 'Internship' ? 'selected' : ''}>Internship</option>
                                    <option value="Journalism/Publication" ${activity.activityType === 'Journalism/Publication' ? 'selected' : ''}>Journalism/Publication</option>
                                    <option value="Junior R.O.T.C." ${activity.activityType === 'Junior R.O.T.C.' ? 'selected' : ''}>Junior R.O.T.C.</option>
                                    <option value="LGBT" ${activity.activityType === 'LGBT' ? 'selected' : ''}>LGBT</option>
                                    <option value="Music: Instrumental" ${activity.activityType === 'Music: Instrumental' ? 'selected' : ''}>Music: Instrumental</option>
                                    <option value="Music: Vocal" ${activity.activityType === 'Music: Vocal' ? 'selected' : ''}>Music: Vocal</option>
                                    <option value="Religious" ${activity.activityType === 'Religious' ? 'selected' : ''}>Religious</option>
                                    <option value="Research" ${activity.activityType === 'Research' ? 'selected' : ''}>Research</option>
                                    <option value="Robotics" ${activity.activityType === 'Robotics' ? 'selected' : ''}>Robotics</option>
                                    <option value="School Spirit" ${activity.activityType === 'School Spirit' ? 'selected' : ''}>School Spirit</option>
                                    <option value="Science/Math" ${activity.activityType === 'Science/Math' ? 'selected' : ''}>Science/Math</option>
                                    <option value="Student Govt./Politics" ${activity.activityType === 'Student Govt./Politics' ? 'selected' : ''}>Student Govt./Politics</option>
                                    <option value="Theater/Drama" ${activity.activityType === 'Theater/Drama' ? 'selected' : ''}>Theater/Drama</option>
                                    <option value="Work (Paid)" ${activity.activityType === 'Work (Paid)' ? 'selected' : ''}>Work (Paid)</option>
                                    <option value="Other Club/Activity" ${activity.activityType === 'Other Club/Activity' ? 'selected' : ''}>Other Club/Activity</option>
                                </select></div>
                                <div class="form-group mb-2"><input type="text" class="form-control" id="edit-activity-organization" value="${activity.activityOrganization || ''}"></div>
                                <div class="form-group mb-2"><input type="text" class="form-control" id="edit-activity-position" value="${activity.activityPosition || ''}"></div>
                                <div class="form-group mb-2"><input type="number" class="form-control" id="edit-activity-hours" value="${activity.hoursPerWeek || ''}" min="0"></div>
                                <div class="form-group mb-2"><label for="edit-activity-start" class="form-label">Start Date</label><input type="date" class="form-control" id="edit-activity-start" value="${activity.startDate || ''}"></div>
                                <div class="form-group mb-2"><label for="edit-activity-end" class="form-label">End Date</label><input type="date" class="form-control" id="edit-activity-end" value="${activity.endDate || ''}"></div>
                                <div class="form-group mb-2"><textarea class="form-control" id="edit-activity-description" rows="2">${activity.activityDescription || ''}</textarea></div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success w-100">Save</button>
                                    <button type="button" class="btn btn-secondary w-100 ml-2" onclick="cancelActivityEdit()">Cancel</button>
                                </div>
                                <div id="edit-activity-error" class="text-danger mt-2"></div>
                            </form>
                        </div>`;
                    } else {
                        html += `<div class="activity-card mb-4 p-3" style="background:#f8f9fa; border-radius:8px; max-width:700px; margin:auto; text-align:left;">
                            <div class="mb-1"><strong>Type:</strong> ${activity.activityType || ''}</div>
                            <div class="mb-1"><strong>Organization:</strong> ${activity.activityOrganization || ''}</div>
                            <div class="mb-1"><strong>Position:</strong> ${activity.activityPosition || ''}</div>
                            <div class="mb-1"><strong>Hours/Week:</strong> ${activity.hoursPerWeek || ''}</div>
                            <div class="mb-1"><strong>Start Date:</strong> ${activity.startDate || ''}</div>
                            <div class="mb-1"><strong>End Date:</strong> ${activity.endDate || ''}</div>
                            <div class="mb-1"><strong>Description:</strong> <br><span style="white-space:pre-line;">${activity.activityDescription || ''}</span></div>
                            <div style='display:flex; justify-content:flex-end; align-items:center; margin-top:8px;'>
                                <button class="btn btn-warning btn-sm" onclick="startActivityEdit(${activityIdInt})">Edit</button>
                            </div>
                        </div>`;
                    }
                });
                container.innerHTML = html;
            });
    }

    function startActivityEdit(activityId) {
        editingActivityId = activityId;
        fetchAndRenderActivities();
    }

    function cancelActivityEdit() {
        editingActivityId = null;
        fetchAndRenderActivities();
    }

    function saveActivityEdit(activityId) {
        const studentId = <?php echo json_encode($studentId); ?>;
        const activityType = document.getElementById('edit-activity-type').value.trim();
        const activityDescription = document.getElementById('edit-activity-description').value.trim();
        const activityOrganization = document.getElementById('edit-activity-organization').value.trim();
        const activityPosition = document.getElementById('edit-activity-position').value.trim();
        const startDate = document.getElementById('edit-activity-start').value;
        const endDate = document.getElementById('edit-activity-end').value;
        const hoursPerWeek = document.getElementById('edit-activity-hours').value;
        const errorDiv = document.getElementById('edit-activity-error');
        if (errorDiv) errorDiv.textContent = '';
        fetch('../activitiesActions.php?action=edit', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `studentId=${encodeURIComponent(studentId)}&activityId=${encodeURIComponent(activityId)}&activityType=${encodeURIComponent(activityType)}&activityDescription=${encodeURIComponent(activityDescription)}&activityOrganization=${encodeURIComponent(activityOrganization)}&activityPosition=${encodeURIComponent(activityPosition)}&startDate=${encodeURIComponent(startDate)}&endDate=${encodeURIComponent(endDate)}&hoursPerWeek=${encodeURIComponent(hoursPerWeek)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                if (errorDiv) errorDiv.textContent = data.error;
            } else {
                editingActivityId = null;
                fetchAndRenderActivities();
            }
        })
        .catch(() => {
            if (errorDiv) errorDiv.textContent = 'An error occurred.';
        });
        return false;
    }



    // Add Activity form submission
    const addActivityForm = document.getElementById('add-activity-form');
    if (addActivityForm) {
        addActivityForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const studentId = <?php echo json_encode($studentId); ?>;
            const formData = new FormData(addActivityForm);
            formData.append('studentId', studentId);
            const errorDiv = document.getElementById('add-activity-error');
            errorDiv.textContent = '';
            fetch('../activitiesActions.php?action=add', {
                method: 'POST',
                body: new URLSearchParams(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    errorDiv.textContent = data.error;
                } else {
                    addActivityForm.reset();
                    fetchAndRenderActivities();
                    addActivityForm.style.display = 'none';
                    document.getElementById('show-add-activity-btn').style.display = '';
                }
            })
            .catch(() => {
                errorDiv.textContent = 'An error occurred.';
            });
        });
    }

    // Add Activity button handlers
    const showAddActivityBtn = document.getElementById('show-add-activity-btn');
    const cancelAddActivityBtn = document.getElementById('cancel-add-activity-btn');
    
    if (showAddActivityBtn) {
        showAddActivityBtn.addEventListener('click', function() {
            document.getElementById('add-activity-form').style.display = '';
            this.style.display = 'none';
        });
    }
    
    if (cancelAddActivityBtn) {
        cancelAddActivityBtn.addEventListener('click', function() {
            document.getElementById('add-activity-form').style.display = 'none';
            document.getElementById('show-add-activity-btn').style.display = '';
        });
    }

    // Exams functionality
    let editingExamId = null;

    function fetchAndRenderExams() {
        const studentId = <?php echo json_encode($studentId); ?>;
        fetch('../examActions.php?action=fetch&studentId=' + studentId)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('exams-table-container');
                if (data.error) {
                    container.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
                    return;
                }
                const exams = data.exams;
                if (!exams || exams.length === 0) {
                    container.innerHTML = '<p>No exams found for this student.</p>';
                    return;
                }
                let html = '<table class="table table-bordered table-striped">';
                html += '<thead><tr><th>Exam Name</th><th>Exam Score</th><th>Actions</th></tr></thead><tbody>';
                exams.forEach(exam => {
                    const examIdInt = parseInt(exam.examId);
                    if (editingExamId === examIdInt) {
                        html += '<tr>' +
                            '<td><input type="text" class="form-control" id="edit-exam-name" value="' +
                            exam.examName.replace(/&/g, '&amp;').replace(/"/g, '&quot;') + '" /></td>' +
                            '<td><input type="text" class="form-control" id="edit-exam-score" value="' +
                            exam.examScore.replace(/&/g, '&amp;').replace(/"/g, '&quot;') + '" /></td>' +
                            '<td>' +
                                '<button class="btn btn-success btn-sm" onclick="saveExamEdit(' + examIdInt + ')">Save</button> ' +
                                '<button class="btn btn-secondary btn-sm" onclick="cancelExamEdit()">Cancel</button>' +
                                '<div id="edit-exam-error" class="text-danger mt-2"></div>' +
                            '</td>' +
                        '</tr>';
                    } else {
                        html += '<tr>' +
                            '<td>' + exam.examName + '</td>' +
                            '<td>' + exam.examScore + '</td>' +
                            '<td>' +
                                '<button class="btn btn-warning btn-sm" onclick="startExamEdit(' + examIdInt + ')">Edit</button>' +
                            '</td>' +
                        '</tr>';
                    }
                });
                html += '</tbody></table>';
                container.innerHTML = html;
            });
    }

    function startExamEdit(examId) {
        editingExamId = examId;
        fetchAndRenderExams();
    }

    function cancelExamEdit() {
        editingExamId = null;
        fetchAndRenderExams();
    }

    function saveExamEdit(examId) {
        const studentId = <?php echo json_encode($studentId); ?>;
        const examName = document.getElementById('edit-exam-name').value.trim();
        const examScore = document.getElementById('edit-exam-score').value.trim();
        const errorDiv = document.getElementById('edit-exam-error');
        if (errorDiv) errorDiv.textContent = '';
        fetch('../examActions.php?action=edit', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `studentId=${encodeURIComponent(studentId)}&examId=${encodeURIComponent(examId)}&examName=${encodeURIComponent(examName)}&examScore=${encodeURIComponent(examScore)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                if (errorDiv) errorDiv.textContent = data.error;
            } else {
                editingExamId = null;
                fetchAndRenderExams();
            }
        })
        .catch(() => {
            if (errorDiv) errorDiv.textContent = 'An error occurred.';
        });
    }



    // Add Exam form submission
    const addExamForm = document.getElementById('add-exam-form');
    if (addExamForm) {
        addExamForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const studentId = <?php echo json_encode($studentId); ?>;
            const formData = new FormData(addExamForm);
            formData.append('studentId', studentId);
            const errorDiv = document.getElementById('add-exam-error');
            errorDiv.textContent = '';
            fetch('../examActions.php?action=add', {
                method: 'POST',
                body: new URLSearchParams(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    errorDiv.textContent = data.error;
                } else {
                    addExamForm.reset();
                    fetchAndRenderExams();
                    addExamForm.style.display = 'none';
                    document.getElementById('show-add-exam-btn').style.display = '';
                }
            })
            .catch(() => {
                errorDiv.textContent = 'An error occurred.';
            });
        });
    }

    // Add Exam button handlers
    const showAddExamBtn = document.getElementById('show-add-exam-btn');
    const cancelAddExamBtn = document.getElementById('cancel-add-exam-btn');
    
    if (showAddExamBtn) {
        showAddExamBtn.addEventListener('click', function() {
            document.getElementById('add-exam-form').style.display = '';
            this.style.display = 'none';
        });
    }
    
    if (cancelAddExamBtn) {
        cancelAddExamBtn.addEventListener('click', function() {
            document.getElementById('add-exam-form').style.display = 'none';
            document.getElementById('show-add-exam-btn').style.display = '';
        });
    }

    // Fetch activities when activities section is shown

    // Checklist functionality
    function fetchAndRenderChecklist() {
        const studentId = <?php echo json_encode($studentId); ?>;
                fetch('../checklistActions.php?action=fetch&studentId=' + studentId)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Checklist response:', data);
                const container = document.getElementById('checklist-container');
                if (data.error) {
                    container.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
                    return;
                }
                const checklistItems = data.checklistItems;
                if (!checklistItems || checklistItems.length === 0) {
                    if (data.message) {
                        container.innerHTML = '<p>' + data.message + '</p>';
                    } else {
                        container.innerHTML = '<p>No checklist items found for this student.</p>';
                    }
                    return;
                }
                
                let html = '<ul class="list-group">';
                checklistItems.forEach(item => {
                    const isCustom = item.isCustom;
                    const universities = item.universities;
                    const status = item.status;
                    const statusRaw = item.status;
                    const badgeColor = (statusRaw.toLowerCase().trim() === 'done') ? '#28a745' : '#c61b75';
                    const itemTypeColor = isCustom ? '#5bc0de' : '#f0ad4e';
                    
                    html += `<li class="list-group-item checklist-item" style="position: relative; padding-right: 140px; cursor: pointer;" onclick="toggleChecklistFiles(${item.checklistId})">
                        <span style="display: block; overflow: hidden; text-overflow: ellipsis; text-align: left;">
                            <strong style="color: ${itemTypeColor};">${isCustom ? 'Extra Checklist Item: ' : 'University Checklist Item: '}</strong>
                            ${item.checklistName}
                            ${!isCustom ? 
                                `<span style="color: #888; font-size: 13px;">[${universities.length > 0 ? universities.join(", ") : 'Checklist item is no longer required'}]</span>` :
                                `<span style="color: #888; font-size: 13px;">[${universities.join(", ")}]</span>`
                            }
                        </span>
                        <span class="badge badge-pill" style="background-color: ${badgeColor} !important; color: white; font-size: 15px; position: absolute; right: 50px; top: 50%; transform: translateY(-50%); min-width: 90px; text-align: right; display: flex; align-items: center; justify-content: center;">${status}</span>
                        <i class="fas fa-chevron-down checklist-dropdown-icon" id="dropdown-icon-${item.checklistId}" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: #6c757d; transition: transform 0.3s ease;"></i>
                    </li>
                    <li class="list-group-item checklist-files-section" id="checklist-files-${item.checklistId}" style="display: none; background-color: #f8f9fa; border-top: none; padding: 20px;">
                        <div class="file-upload-container">
                            <h6 style="margin-bottom: 15px; color: #333;">Upload File for: <strong>${item.checklistName}</strong></h6>
                            <div class="file-drop-zone" id="drop-zone-${item.checklistId}">
                                <div class="file-drop-content">
                                    <i class="fas fa-cloud-upload-alt" style="font-size: 2em; color: #007bff; margin-bottom: 10px;"></i>
                                    <p>Drag and drop a file here or click to browse</p>
                                    <input type="file" class="file-input" id="file-input-${item.checklistId}" accept="*/*" style="display: none;">
                                </div>
                            </div>
                            <div class="file-list" id="file-list-${item.checklistId}" style="margin-top: 15px;"></div>
                            <button class="btn btn-success mt-2" id="save-file-btn-${item.checklistId}" onclick="saveChecklistFile(event, ${item.checklistId})">Save</button>
                            ${item.hasDocument ? `
                                <div class="mb-2"><strong>Uploaded file:</strong> ${item.documentName}</div>
                                <a href="../downloadChecklistDocumentStudent.php?studentId=${studentId}&checklistId=${item.checklistId}" target="_blank" class="btn btn-info mt-2 ml-2">Download</a>
                            ` : ''}
                            <div id="file-upload-msg-${item.checklistId}" class="mt-2"></div>
                        </div>
                    </li>`;
                });
                html += '</ul>';
                container.innerHTML = html;
            })
            .catch(error => {
                console.error('Error fetching checklist:', error);
                document.getElementById('checklist-container').innerHTML = '<div class="alert alert-danger">Error loading checklist items: ' + error.message + '</div>';
            });
    }



    function getStatusColor(status) {
        status = status.trim();
        if (status === "In Progress") return "#FFA500";
        else if (status === "Completed") return "#008000";
        else if (status === "Rejected") return "#FF0000";
        else if (status === "Waitlisted") return "#808080";
        return "#6c757d";
    }

    // Checklist file upload functionality
    function toggleChecklistFiles(checklistId) {
        const filesSection = document.getElementById('checklist-files-' + checklistId);
        const dropdownIcon = document.getElementById('dropdown-icon-' + checklistId);
        
        if (filesSection.style.display === 'none') {
            filesSection.style.display = 'block';
            dropdownIcon.classList.add('rotated');
            initializeFileUpload(checklistId);
        } else {
            filesSection.style.display = 'none';
            dropdownIcon.classList.remove('rotated');
        }
    }

    function initializeFileUpload(checklistId) {
        const dropZone = document.getElementById('drop-zone-' + checklistId);
        const fileInput = document.getElementById('file-input-' + checklistId);
        const fileList = document.getElementById('file-list-' + checklistId);
        fileList.innerHTML = '';
        
        dropZone.addEventListener('click', () => {
            fileInput.click();
        });

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFileSelect(files[0], checklistId);
            }
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFileSelect(e.target.files[0], checklistId);
            }
        });
    }

    function handleFileSelect(file, checklistId) {
        const fileList = document.getElementById('file-list-' + checklistId);
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        fileItem.innerHTML = `
            <span class="file-name">${file.name}</span>
            <span class="file-size">${formatFileSize(file.size)}</span>
            <button class="remove-file" onclick="removeFile(this)">×</button>
        `;
        fileList.appendChild(fileItem);
        
        // Store the file for upload
        window.selectedFile = file;
    }

    function removeFile(button) {
        button.parentElement.remove();
        window.selectedFile = null;
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function saveChecklistFile(event, checklistId) {
        event.preventDefault();
        const studentId = <?php echo json_encode($studentId); ?>;
        const msgDiv = document.getElementById('file-upload-msg-' + checklistId);
        
        if (!window.selectedFile) {
            msgDiv.innerHTML = '<div class="alert alert-warning">Please select a file first.</div>';
            return;
        }

        const formData = new FormData();
        formData.append('studentId', studentId);
        formData.append('checklistId', checklistId);
        formData.append('file', window.selectedFile);

        msgDiv.innerHTML = '<div class="alert alert-info">Uploading...</div>';

        fetch('../checklistActions.php?action=upload', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                msgDiv.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
            } else {
                msgDiv.innerHTML = '<div class="alert alert-success">File uploaded successfully!</div>';
                window.selectedFile = null;
                // Refresh the checklist to show the uploaded file
                setTimeout(() => {
                    fetchAndRenderChecklist();
                }, 1000);
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            msgDiv.innerHTML = '<div class="alert alert-danger">An error occurred during upload.</div>';
        });
    }

    // Tasks functionality
    let tasksLoaded = false;
    let allTasks = []; // Store all tasks for filtering/sorting

    function fetchAndRenderTasks() {
        if (tasksLoaded) return;
        
        const studentId = <?php echo json_encode($studentId); ?>;
        fetch('../tasksActions.php?action=list&studentId=' + studentId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    allTasks = data.tasks; // Store all tasks
                    displayTasks(allTasks);
                    tasksLoaded = true;
                } else {
                    console.error('Error loading tasks:', data.error);
                    document.getElementById('tasks-list-container').innerHTML = '<div class="alert alert-danger">Error loading tasks: ' + data.error + '</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('tasks-list-container').innerHTML = '<div class="alert alert-danger">Error loading tasks.</div>';
            });
    }

    function displayTasks(tasks) {
        const container = document.getElementById('tasks-list-container');
        
        if (!tasks || tasks.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted">
                    <i class="fas fa-tasks" style="font-size: 2em; margin-bottom: 10px;"></i>
                    <p>No tasks found for this student.</p>
                </div>
            `;
            return;
        }
        
        let html = '<ul class="list-group">';
        tasks.forEach(task => {
            const isDone = task.taskStatus === 'Done';
            const deadline = task.taskDeadline ? new Date(task.taskDeadline).toLocaleDateString() : 'No deadline';
            
            // Add meeting context if task was created during a meeting
            const meetingContext = task.meetingId ? 
                `<small class="text-info" style="margin-right: 8px;">
                    <i class="fas fa-calendar-alt"></i> 
                    <a href="../meeting.php?meetingId=${task.meetingId}" style="color: #17a2b8; text-decoration: none;">
                        Meeting Task
                    </a>
                </small>` : '';
            
            html += `
                <li class="task-item" style="display: flex; align-items: center; justify-content: space-between; padding: 8px 16px; border: 1px solid #dee2e6; border-radius: 4px; margin-bottom: 8px; background-color: white;">
                    <span class="task-text" style="flex: 1; text-align: left; text-decoration: ${isDone ? 'line-through' : 'none'}; color: ${isDone ? '#888' : '#333'}; margin-right: 16px; margin: 0; padding: 0; display: block; word-wrap: break-word;">
                        ${escapeHtml(task.taskText)}
                    </span>
                    <div style="display: flex; align-items: center; gap: 8px; white-space: nowrap;">
                        ${meetingContext}
                        <small class="text-muted" style="margin-right: 12px;">Deadline: ${deadline}</small>
                        <button class="btn btn-sm ${isDone ? 'btn-success' : 'btn-outline-success'}" 
                                onclick="toggleTask(${task.taskId})" 
                                title="${isDone ? 'Mark as In Progress' : 'Mark as Done'}">
                            <i class="fas fa-check"></i>
                        </button>
                    </div>
                </li>
            `;
        });
        html += '</ul>';
        
        container.innerHTML = html;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function toggleTask(taskId) {
        const formData = new FormData();
        formData.append('taskId', taskId);
        
        fetch('../tasksActions.php?action=toggle', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tasksLoaded = false; // Reset to force reload
                fetchAndRenderTasks(); // Reload tasks to update display
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the task.');
        });
    }



    function filterAndSortTasks() {
        const statusFilter = document.getElementById('task-status-filter').value;
        const sortBy = document.getElementById('task-sort-by').value;
        
        let filteredTasks = [...allTasks];
        
        // Apply filters
        if (statusFilter === 'In Progress') {
            filteredTasks = filteredTasks.filter(task => task.taskStatus === 'In Progress');
        } else if (statusFilter === 'Done') {
            filteredTasks = filteredTasks.filter(task => task.taskStatus === 'Done');
        } else if (statusFilter === 'meeting') {
            filteredTasks = filteredTasks.filter(task => task.meetingId);
        } else if (statusFilter === 'general') {
            filteredTasks = filteredTasks.filter(task => !task.meetingId);
        }
        // 'all' shows all tasks
        
        // Apply sorting
        filteredTasks.sort((a, b) => {
            switch (sortBy) {
                case 'deadline':
                    if (!a.taskDeadline && !b.taskDeadline) return 0;
                    if (!a.taskDeadline) return 1;
                    if (!b.taskDeadline) return -1;
                    return new Date(a.taskDeadline) - new Date(b.taskDeadline);
                
                case 'status':
                    if (a.taskStatus === b.taskStatus) return 0;
                    return a.taskStatus === 'Done' ? 1 : -1;
                
                case 'created':
                    return b.taskId - a.taskId; // Assuming taskId represents creation order
                
                case 'meeting':
                    if (a.meetingId && !b.meetingId) return -1;
                    if (!a.meetingId && b.meetingId) return 1;
                    return 0;
                
                default:
                    return 0;
            }
        });
        
        displayTasks(filteredTasks);
    }

    // Add event listeners for task filtering and sorting
    document.addEventListener('DOMContentLoaded', function() {
        const statusFilter = document.getElementById('task-status-filter');
        const sortBy = document.getElementById('task-sort-by');
        
        if (statusFilter) {
            statusFilter.addEventListener('change', filterAndSortTasks);
        }
        
        if (sortBy) {
            sortBy.addEventListener('change', filterAndSortTasks);
        }
    });

    // Add task form functionality
    document.addEventListener('DOMContentLoaded', function() {
        const showAddTaskBtn = document.getElementById('show-add-task-btn');
        const addTaskForm = document.getElementById('add-task-form');
        const cancelAddTaskBtn = document.getElementById('cancel-add-task-btn');
        
        if (showAddTaskBtn) {
            showAddTaskBtn.addEventListener('click', function() {
                addTaskForm.style.display = '';
                this.style.display = 'none';
            });
        }
        
        if (cancelAddTaskBtn) {
            cancelAddTaskBtn.addEventListener('click', function() {
                addTaskForm.style.display = 'none';
                showAddTaskBtn.style.display = '';
                addTaskForm.reset();
                document.getElementById('add-task-error').textContent = '';
            });
        }
        
        if (addTaskForm) {
            addTaskForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('studentId', <?php echo json_encode($studentId); ?>);
                
                fetch('../tasksActions.php?action=add', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.reset();
                        this.style.display = 'none';
                        showAddTaskBtn.style.display = '';
                        document.getElementById('add-task-error').textContent = '';
                        tasksLoaded = false; // Reset to reload tasks
                        fetchAndRenderTasks();
                    } else {
                        document.getElementById('add-task-error').textContent = data.error;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('add-task-error').textContent = 'An error occurred while adding the task.';
                });
            });
        }
    });
  </script>

</body>
</html>
