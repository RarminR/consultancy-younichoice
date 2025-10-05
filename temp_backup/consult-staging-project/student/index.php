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
        #content {
            width: 70%;
            margin: auto;
        }
        .search-bar-name {
            background-image: url('/css/searchicon.png');
            background-position: 10px 12px;
            background-repeat: no-repeat;
            width: 100%;
            font-size: 16px;
            padding: 12px 20px 12px 40px;
            border: 1px solid #ddd;
            margin-bottom: 12px;
        }
        .search-bar-country {
            background-image: url('/css/searchicon.png');
            background-position: 10px 12px;
            background-repeat: no-repeat;
            width: 100%;
            font-size: 16px;
            padding: 12px 20px 12px 40px;
            border: 1px solid #ddd;
            margin-bottom: 12px;
        }
        .full-name {
            font-weight: bold;
        }

        .navbar {
            height: 150px;
        }

        .badge {
            font-size: 15px;
            color: white;
            background-color: var(--pink) !important;
            position: absolute;
            right: 50%;
        }
        
        .fw-bold {
            font-weight: bold;
        }

        .student-info {
            font-size: 18px;
            font-weight: bold;
        }

        .title-info {
            font-weight: bold;
            color: var(--pink);
            font-size: 20px;
        }

        .info-row {
            display: inline;
        }

        .statusSelect {
            width: 100px;
            height: 25px;
        }

        .comissionable-filter {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
            margin-left: 3px;
        }

        label {
            padding-top: 4.5px;
            padding-left: 3px;
            font-weight: normal;
        }

        /* Navigation Buttons */
        .nav-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .tab-button {
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            width: 45%;
            text-align: center;
            cursor: pointer;
            border: 2px solid #007bff;
            background-color: white;
            color: #007bff;
            font-weight: bold;
        }

        .tab-button:hover {
            background: #e0e0e0;
        }

        .tab-button.active {
            background: #007bff;
            color: white;
        }

        .content-section {
            display: none;
            text-align: center;
        }

        .visible {
            display: block;
        }

        .card {
            background: #e9e9e9;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            margin: 10px auto;
            text-align: center;
        }

        .card h3 {
            margin: 10px 0;
            font-size: 18px;
            color: #333;
        }

        .card p {
            color: #555;
            font-size: 14px;
            margin: 5px 0;
        }

        .tab-button {
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            cursor: pointer;
            margin: 5px;
            font-size: 14px;
        }

        .edit-btn {
            background: #007bff;
            color: #fff;
        }

        .edit-btn:hover {
            background: #0056b3;
        }

        .view-notes-btn {
            background: #28a745;
            color: #fff;
        }

        .view-notes-btn:hover {
            background: #218838;
        }

        .disabled:hover {
            cursor: not-allowed;
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
        }
        .popup {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
        }
        .popup h2 {
            margin-top: 0;
        }
        .popup .close-btn {
            background: red;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            float: right;
        }

        .task-item:hover {
            background-color: #f8f9fa;
        }

        .file-drop-zone {
            border: 2px dashed #007bff;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .file-drop-zone:hover {
            border-color: #0056b3;
            background-color: #e9ecef;
        }

        .file-drop-zone.dragover {
            border-color: #28a745;
            background-color: #d4edda;
        }

        .file-drop-content {
            color: #6c757d;
        }

        .file-drop-content i {
            display: block;
            margin-bottom: 10px;
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
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }

        .file-item .file-name {
            flex: 1;
            margin-right: 10px;
            word-break: break-all;
        }

        .file-item .file-size {
            color: #6c757d;
            font-size: 0.9em;
            margin-right: 10px;
        }

        .file-item .remove-file {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 3px;
            padding: 2px 6px;
            cursor: pointer;
            font-size: 0.8em;
        }

        .file-item .remove-file:hover {
            background: #c82333;
        }

        .task-dropdown-icon {
            font-size: 14px;
        }

        .task-dropdown-icon.rotated {
            transform: translateY(-50%) rotate(180deg) !important;
        }

        .mini-nav-btn {
            background: none;
            border: none;
            font-size: 1.1rem;
            color: #888;
            font-weight: 600;
            padding: 6px 18px;
            border-radius: 6px;
            cursor: pointer;
            transition: color 0.2s, background 0.2s;
        }
        .mini-nav-btn.mini-nav-selected {
            color: #111;
            background: #e9e9e9;
        }
        .mini-nav-btn:not(.mini-nav-selected):hover {
            color: #444;
            background: #f3f3f3;
        }

        /* Checklist styles */
        .checklist-dropdown-icon {
            font-size: 14px;
        }

        .checklist-dropdown-icon.rotated {
            transform: translateY(-50%) rotate(180deg) !important;
        }

        .file-drop-zone {
            border: 2px dashed #007bff;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .file-drop-zone:hover {
            border-color: #0056b3;
            background-color: #e9ecef;
        }

        .file-drop-zone.dragover {
            border-color: #28a745;
            background-color: #d4edda;
        }

        .file-drop-content {
            color: #6c757d;
        }

        .file-drop-content i {
            display: block;
            margin-bottom: 10px;
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
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }

        .file-item .file-name {
            flex: 1;
            margin-right: 10px;
            word-break: break-all;
        }

        .file-item .file-size {
            color: #6c757d;
            font-size: 0.9em;
            margin-right: 10px;
        }

        .file-item .remove-file {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 3px;
            padding: 2px 6px;
            cursor: pointer;
            font-size: 0.8em;
        }

        .file-item .remove-file:hover {
            background: #c82333;
        }
    </style>
  </head>

  <div class="popup-container" id="popup-container">
    <div class="popup">
        <button class="close-btn" onclick="closePopup()">X</button>
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
    <br>
    <br>

    <h1 style="color: rgba(79, 35, 95, .9);">Welcome, <?php echo $dataStudent['name']; ?>!</h1>
    <br>
    <br>

    <p class="student-info"> <span class="title-info"> Student Name: </span> <?php echo $dataStudent['name']; ?> </p>
    <p class="student-info"> <span class="title-info"> Student's Email: </span> <?php echo $dataStudent['email']; ?> </p>
    <p class="student-info"> <span class="title-info"> Parent's Email: </span> <?php echo $dataStudent['emailParent']; ?> </p>

    <p class="student-info"> <span class="title-info"> Location: </span> <?php echo htmlspecialchars($dataStudent['judet']); ?> </p>

    <p class="student-info"> <span class="title-info"> HighSchool: </span> <?php echo $dataStudent['highSchool']; ?> </p>
    <p class="student-info"> <span class="title-info"> Phone number: </span> <?php echo $dataStudent['phoneNumber']; ?> </p>
    <p class="student-info"> <span class="title-info"> Grade: </span> <?php echo $dataStudent['grade']; ?> </p>
    <p class="student-info"> <span class="title-info"> Graduation Year: </span> <?php echo $dataStudent['graduationYear']; ?> </p>
    <p class="student-info"> <span class="title-info"> Sign Grade: </span> <?php echo $dataStudent['signGrade']; ?> </p>
    <p class="student-info"> <span class="title-info"> Consultant: </span> <?php echo $dataConsultant['fullName']; ?> </p>


    <br>

    <a href="<?php echo $dataConsultant["calendlyLink"]; ?>" target="__blank"> 
      <button class="btn btn-primary">
        View consultant's calendly
      </button>
    </a>

    <br>
    <br>
    
    <div class="nav-buttons">
        <button id="meetings-btn" class="tab-button active" onclick="showSection('meetings-section', 'meetings-btn')">Meetings</button>
        <?php if ($nUniversities > 0) { ?>
            <button id="university-applications-btn" class="tab-button" onclick="showSection('university-applications-section', 'university-applications-btn')">University Applications</button>
        <?php } ?>
        <?php if ($nSummer > 0) { ?>
            <button id="summer-applications-btn" class="tab-button" onclick="showSection('summer-applications-section', 'summer-applications-btn')">Summer School Applications</button>
        <?php } ?>
        <?php if ($nBoarding > 0) { ?>
            <button id="boarding-applications-btn" class="tab-button" onclick="showSection('boarding-applications-section', 'boarding-applications-btn')">Boarding School Applications</button>
        <?php } ?>
        <button id="application-overview-btn" class="tab-button" onclick="showSection('application-overview-section', 'application-overview-btn')">Application Overview</button>
    </div>

    <!-- Meetings -->
    <div id="meetings-section" class="content-section visible">
        <h1 style="float: left;"> Your Meetings </h1>
        <br>
        <br>
        <br>
        <br>

        <ol class="list-group list-group-numbered" id="meetings-list">
            <?php
            for ($i = 0; $i < $nMeetings; $i++) { ?>
                <div class="meeting">
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold">Meeting on <?php echo $arrMeetingDate[$i]; ?></div>
                            <br>
                            <p style="float: left;"> <span style="font-weight: bold;"> Topics: </span> <?php echo $arrMeetingTopic[$i];?> </p>
                            <br>
                            <p style="float: left;"> <span style="font-weight: bold;"> Activities: </span> <?php echo $arrMeetingActivities[$i];?> </p>
                        </div>
                        <div>
                            <a href="meeting?meetingId=<?php echo $arrMeetingId[$i]; ?>"> <button type="button" class="btn btn-primary">View details</button> </a>
                        </div>
                    </li>
                </div>
            <?php
            }
            ?>
        </ol>

        <br>
    </div>

    <?php
    if ($nUniversities > 0) { ?>
    <!-- Universities --> 
    <div id="university-applications-section" class="content-section">
        <h1 style="float: left;"> University Applications</h1>
        <input type="text" class="search-bar-name" id="search-bar-university-name" onkeyup="searchFunctionUniversities()" placeholder="Search for university's name.." title="Type in a name">
        <input type="text" class="search-bar-country" id="search-bar-university-country" onkeyup="searchFunctionUniversities()" placeholder="Search for university's country.." title="Type in a name">

        <div class="comissionable-filter">
            <input checked onchange="searchFunctionUniversities()" type="checkbox" id="commissionable-university" name="commisionable-university" value="1">
            <label for="commissionable-university"> Commissionable Universities</label>
        </div>

        <div class="comissionable-filter">
            <input checked onchange="searchFunctionUniversities()" type="checkbox" id="non-commissionable-university" name="non-commisionable-university" value="0">
            <label for="non-commissionable-university"> Non-commissionable Universities</label>
        </div>

        <ol class="list-group list-group-numbered" id="university-applications-list">
            <?php
            for ($i = 0; $i < $nUniversities; $i++) { ?>
                <div class="university-application">
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold university-name"><?php echo $arrUniversityName[$i]; ?></div>
                            <p class="university-country"> <?php echo $arrUniversityCountry[$i]; ?> </p>
                            <?php
                            if (trim($arrUniversityAppStatus[$i]) == "Accepted") { ?>
                                <p> Scholarship: <span class="university-commission">  <b><?php echo $arrUniversityScholarship[$i]; ?></b> </span> </p>
                            <?php } ?>
                            <p> Comission: <span class="university-commission">  <b><?php echo $arrUniversityComission[$i]; ?> </b></span> </p>
                        </div>
                        <span class="badge bg-primary rounded-pill" style="background-color: <?php echo getStatusColor($arrUniversityAppStatus[$i]); ?> !important;"> <?php echo $arrUniversityAppStatus[$i]; ?></span>
                        <div>
                            <a href="application?applicationId=<?php echo $arrUniversityAppId[$i]; ?>"> <button type="button" class="btn btn-primary">View details</button> </a>
                        </div>
                    </li>
                </div>
            <?php
            }
            ?>
        </ol>
            <br>
        </div>
    <?php } ?>

    <!-- Summer Schools --> 
    <div id="summer-applications-section" class="content-section">
        <h1 style="float: left;"> Summer School Applications</h1>
        <input type="text" class="search-bar-name" id="search-bar-summer-name" onkeyup="searchFunctionSummer()" placeholder="Search for summer school's name.." title="Type in a name">
        <input type="text" class="search-bar-country" id="search-bar-summer-country" onkeyup="searchFunctionSummer()" placeholder="Search for summer school's country.." title="Type in a name">

        <div class="comissionable-filter">
            <input checked onchange="searchFunctionSummer()" type="checkbox" id="commissionable-summer" name="commisionable-summer" value="1">
            <label for="commissionable-summer"> Commissionable Schools</label>
        </div>

        <div class="comissionable-filter">
            <input checked onchange="searchFunctionSummer()" type="checkbox" id="non-commissionable-summer" name="non-commisionable-summer" value="0">
            <label for="non-commissionable-summer"> Non-commissionable Schools</label>
        </div>

        <ol class="list-group list-group-numbered" id="summer-applications-list">
            <?php
            for ($i = 0; $i < $nSummer; $i++) { ?>
                <div class="summer-application">
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold summer-name"><?php echo $arrSummerName[$i]; ?></div>
                            <p class="summer-country"> <?php echo $arrSummerCountry[$i]; ?> </p>
                            <p> Comission: <span class="summer-commission">  <?php echo $arrSummerComission[$i]; ?> </span> </p>
                        </div>
                        <span class="badge bg-primary rounded-pill" style="background-color: <?php echo getStatusColor($arrSummerAppStatus[$i]); ?> !important;"> <?php echo $arrSummerAppStatus[$i]; ?></span>
                        <div>
                            <a href="application?applicationId=<?php echo $arrSummerAppId[$i]; ?>"> <button type="button" class="btn btn-primary">View details</button> </a>
                        </div>
                    </li>
                </div>
            <?php
            }
            ?>
        </ol>

        <br>
    </div>

    <!-- Boarding Schools --> 
    <div id="boarding-applications-section" class="content-section">
        <h1 style="float: left;"> Boarding School Applications</h1>
        <input type="text" class="search-bar-name" id="search-bar-boarding-name" onkeyup="searchFunctionBoarding()" placeholder="Search for boarding school's name.." title="Type in a name">
        <input type="text" class="search-bar-country" id="search-bar-boarding-country" onkeyup="searchFunctionBoarding()" placeholder="Search for boarding school's country.." title="Type in a name">

        <div class="comissionable-filter">
            <input checked onchange="searchFunctionBoarding()" type="checkbox" id="commissionable-boarding" name="commisionable-boarding" value="1">
            <label for="commissionable-boarding"> Commissionable Schools</label>
        </div>

        <div class="comissionable-filter">
            <input checked onchange="searchFunctionBoarding()" type="checkbox" id="non-commissionable-boarding" name="non-commisionable-boarding" value="0">
            <label for="non-commissionable-boarding"> Non-commissionable Schools</label>
        </div>

        <ol class="list-group list-group-numbered" id="boarding-applications-list">
            <?php
            for ($i = 0; $i < $nBoarding; $i++) { ?>
                <div class="boarding-application">
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold boarding-name"><?php echo $arrBoardingName[$i]; ?></div>
                            <p class="boarding-country"> <?php echo $arrBoardingCountry[$i]; ?> </p>
                            <p> Comission: <span class="boarding-commission">  <?php echo $arrBoardingComission[$i]; ?> </span> </p>
                        </div>
                        <span class="badge bg-primary rounded-pill" style="background-color: <?php echo getStatusColor($arrBoardingAppStatus[$i]); ?> !important;"> <?php echo $arrBoardingAppStatus[$i]; ?></span>
                        <div>
                            <a href="application?applicationId=<?php echo $arrBoardingAppId[$i]; ?>"> <button type="button" class="btn btn-primary">View details</button> </a>
                        </div>
                    </li>
                </div>
            <?php
            }
            ?>
        </ol>

        <br>
    </div>

    <!-- Application Overview -->
    <div id="application-overview-section" class="content-section">
        <h1 style="margin-bottom: 20px;">Application Overview</h1>
        
        <!-- Mini-Nav Bar for Overview Subsections -->
        <div id="overview-mini-nav" style="display: flex; justify-content: center; margin-bottom: 24px; gap: 24px; margin-top: 0;">
            <button id="mini-nav-checklist" class="mini-nav-btn mini-nav-selected" onclick="showOverviewSubsection('checklist')">Checklist</button>
            <button id="mini-nav-activities" class="mini-nav-btn" onclick="showOverviewSubsection('activities')">Activities</button>
            <button id="mini-nav-exams" class="mini-nav-btn" onclick="showOverviewSubsection('exams')">Exams</button>
        </div>

        <div id="overview-checklist-section">
            <div class="card mb-4 p-3" style="background:#f8f9fa; border-radius:8px; max-width:900px; margin:auto;">
                <div id="checklist-container">
                    <p>Loading checklist items...</p>
                </div>
            </div>
        </div>

        <div id="overview-activities-section" style="display: none;">
            <div class="activity-card mb-4 p-3" style="background:#f8f9fa; border-radius:8px; max-width:700px; margin:auto;">
                <button id="show-add-activity-btn" class="btn btn-primary w-100 mb-2">Add Activity</button>
                <form id="add-activity-form" class="mb-2" style="display:none;">
                    <div class="form-group mb-2">
                        <select class="form-control" name="activityType">
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
                    <div class="form-group mb-2">
                        <input type="text" class="form-control" name="activityOrganization" placeholder="Organization">
                    </div>
                    <div class="form-group mb-2">
                        <input type="text" class="form-control" name="activityPosition" placeholder="Position/Role">
                    </div>
                    <div class="form-group mb-2">
                        <input type="number" class="form-control" name="hoursPerWeek" placeholder="Hours/Week" min="0">
                    </div>
                    <div class="form-group mb-2">
                        <label for="activityStartDate" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="activityStartDate" name="startDate">
                    </div>
                    <div class="form-group mb-2">
                        <label for="activityEndDate" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="activityEndDate" name="endDate">
                    </div>
                    <div class="form-group mb-2">
                        <textarea class="form-control" name="activityDescription" placeholder="Description" rows="2"></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">Add Activity</button>
                        <button type="button" id="cancel-add-activity-btn" class="btn btn-secondary w-100 ml-2">Cancel</button>
                    </div>
                </form>
                <div id="add-activity-error" class="text-danger mb-2"></div>
            </div>
            <div id="activities-scroll-container" style="max-height:600px; overflow-y:auto;">
                <div id="activities-table-container"></div>
            </div>
        </div>

        <div id="overview-exams-section" style="display: none;">
            <div id="overview-exams">
                <!-- Add Exam Button -->
                <button id="show-add-exam-btn" class="btn btn-primary mb-3">Add Exam</button>
                <form id="add-exam-form" class="form-inline mb-3" style="display:none; justify-content:center;">
                    <div class="form-group mx-sm-2 mb-2">
                        <label for="examName" class="sr-only">Exam Name</label>
                        <input type="text" class="form-control" id="examName" name="examName" placeholder="Exam Name" required>
                    </div>
                    <div class="form-group mx-sm-2 mb-2">
                        <label for="examScore" class="sr-only">Exam Score</label>
                        <input type="text" class="form-control" id="examScore" name="examScore" placeholder="Exam Score" required>
                    </div>
                    <button type="submit" class="btn btn-primary mb-2">Add Exam</button>
                    <button type="button" id="cancel-add-exam-btn" class="btn btn-secondary mb-2 ml-2">Cancel</button>
                </form>
                <div id="add-exam-error" class="text-danger mb-2"></div>
                <div id="exams-table-container"></div>
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
        // Remove selected class
        document.getElementById('mini-nav-checklist').classList.remove('mini-nav-selected');
        document.getElementById('mini-nav-activities').classList.remove('mini-nav-selected');
        document.getElementById('mini-nav-exams').classList.remove('mini-nav-selected');
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
                                <button class="btn btn-danger btn-sm" style="margin-left:8px;" onclick="deleteActivity(${activityIdInt})">Delete</button>
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

    function deleteActivity(activityId) {
        if (!confirm('Are you sure you want to delete this activity?')) return;
        const studentId = <?php echo json_encode($studentId); ?>;
        fetch('../activitiesActions.php?action=delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `studentId=${encodeURIComponent(studentId)}&activityId=${encodeURIComponent(activityId)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
            } else {
                fetchAndRenderActivities();
            }
        })
        .catch(() => {
            alert('An error occurred while deleting the activity.');
        });
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

  </script>

</body>
</html>
