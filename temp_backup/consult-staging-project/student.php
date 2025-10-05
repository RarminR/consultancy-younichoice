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
    require_once "configDatabase.php";

    if (!isset($_SESSION['type'])) { // testez daca userul est logat
        header("location: index.php");
        die();
    }
    else {
        $typeAccount = $_SESSION["type"];
        $accountId = $_SESSION["id"];
    }

    if (isset($_GET['studentId'])) // testez daca e setat un student
        $studentId = $_GET['studentId'];
    else {
        header("location: index.php");
        die();
    }

    $sqlStudentData = "SELECT * FROM studentData WHERE `studentId` = " .$studentId;
    
    $queryStudentData = mysqli_query($link, $sqlStudentData);

    if (mysqli_num_rows($queryStudentData) > 0) // testez daca exista vreun student cu id-ul dat
        $dataStudent = mysqli_fetch_assoc($queryStudentData);
    else {
        header("location: index.php");
        die();
    }

    if (!($dataStudent['consultantId'] == $accountId || $typeAccount == 1)) { // testez daca are acces userul la studentul dat
        header("location: index.php");
        die();
    }

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
        // echo mysqli_num_rows($queryUniversityName);
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
    $sqlMeetings = "SELECT * FROM meetings WHERE `studentId` = '$studentId' ORDER BY meetingDate ASC";
    $queryMeetings = mysqli_query($link, $sqlMeetings);

    // Set timezone to Romania
    date_default_timezone_set('Europe/Bucharest');
    $currentDateTime = new DateTime();

    $upcomingMeetings = [];
    $pastMeetings = [];
    
    while ($row = mysqli_fetch_assoc($queryMeetings)) {
        $meetingDateTime = new DateTime($row['meetingDate']);
        
        $meetingData = [
            'meetingId' => $row["meetingId"],
            'consultantId' => $row["consultantId"],
            'meetingDate' => $row['meetingDate'],
            'meetingNotes' => $row['meetingNotes'],
            'meetingTopic' => $row['meetingTopic'] ?: "Not applicable",
            'meetingActivities' => $row['meetingActivities'] ?: "Not applicable"
        ];
        
        if ($meetingDateTime > $currentDateTime) {
            $upcomingMeetings[] = $meetingData;
        } else {
            $pastMeetings[] = $meetingData;
        }
    }
    
    // Sort upcoming meetings by date (ascending)
    usort($upcomingMeetings, function($a, $b) {
        return strtotime($a['meetingDate']) - strtotime($b['meetingDate']);
    });
    
    // Sort past meetings by date (descending - most recent first)
    usort($pastMeetings, function($a, $b) {
        return strtotime($b['meetingDate']) - strtotime($a['meetingDate']);
    });

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

    <title>Student <?php echo $dataStudent["name"]; ?></title>

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
            /* height: 30px; */
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
            display: inline; /* the default for span */
        }

        .statusSelect {
            width: 100px;
            height: 25px;
        }

        .comissionable-filter {
            display: flex;
            align-items: center;
            margin-bottom: 5px; /* Adjust margin as needed */
            margin-left: 3px;
        }

        label {
            padding-top: 4.5px;
            padding-left: 3px;
            font-weight: normal;
        }

    </style>
    <style>

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

        /* Active Button (Selected) */
        .tab-button.active {
            background: #007bff;
            color: white;
        }

        /* Hide All Sections Initially */
        .content-section {
            display: none;
            text-align: center;
        }

        /* Show only the selected section */
        .visible {
            display: block;
        }

        /* Card Styles */
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

        /* Buttons */
        .tab-button {
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            cursor: pointer;
            margin: 5px;
            font-size: 14px;
        }

        /* Edit Button */
        .edit-btn {
            background: #007bff;
            color: #fff;
        }

        .edit-btn:hover {
            background: #0056b3;
        }

        /* View Notes Button */
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

        /* Task file upload styles */
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
    </style>
  </head>


  
  
  <?php include("navbar.php"); ?>


<div class="popup-container" id="popup-container">
    <div class="popup">
        <button class="close-btn" onclick="closePopup()">X</button>
        <h2>Package Details</h2>
        <p id = "package-details"><?php echo $dataStudent['packageDetails']; ?></p>
    </div>
</div>

<div id = "content">
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>


    <p class = "student-info"> <span class = "title-info"> Student Name: </span> <?php echo $dataStudent['name']; ?> </p>
    <p class = "student-info"> <span class = "title-info"> Student's Email: </span> <?php echo $dataStudent['email']; ?> </p>
    <p class = "student-info"> <span class = "title-info"> Parent's Email: </span> <?php echo $dataStudent['parentEmail']; ?> </p>

    <p class = "student-info"> <span class = "title-info"> Location: </span> <?php echo htmlspecialchars($dataStudent['judet']); ?> </p>

    <p class = "student-info"> <span class = "title-info"> HighSchool: </span> <?php echo $dataStudent['highSchool']; ?> </p>
    <p class = "student-info"> <span class = "title-info"> Phone number: </span> <?php echo $dataStudent['phoneNumber']; ?> </p>
    <p class = "student-info"> <span class = "title-info"> Grade: </span> <?php echo $dataStudent['grade']; ?> </p>
    <p class = "student-info"> <span class = "title-info"> Graduation Year: </span> <?php echo $dataStudent['graduationYear']; ?> </p>
    <p class = "student-info"> <span class = "title-info"> Sign Grade: </span> <?php echo $dataStudent['signGrade']; ?> </p>
    <p class = "student-info"> <span class = "title-info"> Package Type: </span> <?php echo $dataStudent['packageType']; ?> </p>
    <p class = "student-info"> <span class = "title-info"> Consultant: </span> <?php echo $dataStudent['consultantName']; ?> </p>


    <button class="btn btn-primary" onclick = "showPopup()">
        View Package Details
    </button>
    <br>
    <br>
    <a href = <?php echo "editStudent.php?studentId=".$studentId; ?> > <button class = "btn btn-primary"> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
  <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
</svg> Edit student info </button> </a>

<br>
<br>

<a href="<?php echo $dataStudent["driveLink"]; ?>" target = "__blank"> 
  <button class="btn btn-primary">
    Drive Link Student
  </button>
</a>

    
    <br>
    <br>
    
    <!-- <button onclick = "confirmRemove('removeStudent.php?studentId=<?php echo $studentId;?>')" class = "btn btn-danger"> <i class="fa-solid fa-minus"></i> Remove Student </button>
    <button onclick = "confirmGraduate('graduateStudent.php?studentId=<?php echo $studentId;?>')" class = "btn btn-primary"> <i class="fa-solid fa-graduation-cap"></i> Graduate Student </button>
    <button onclick = "confirmRestore('restoreStudent.php?studentId=<?php echo $studentId;?>')" class = "btn btn-primary"> <i class="fa-solid fa-refresh"></i> Restore Student </button> -->

    <br>
    <br>
    
    <div class="nav-buttons">
            <button id="meetings-btn" class="tab-button" onclick="showSection('meetings-section', 'meetings-btn')">Meetings</button>
            <button id="university-applications-btn" class="tab-button active" onclick="showSection('university-applications-section', 'university-applications-btn')">University Applications</button>
            <button id="summer-applications-btn" class="tab-button" onclick="showSection('summer-applications-section', 'summer-applications-btn')">Summer School Applications</button>
            <button id="boarding-applications-btn" class="tab-button" onclick="showSection('boarding-applications-section', 'boarding-applications-btn')">Boarding School Applications</button>
            <button id="application-overview-btn" class="tab-button" onclick="showSection('application-overview-section', 'application-overview-btn')">Application Overview</button>
        </div>

    <!-- Meetings -->
    <div id="meetings-section" class="content-section">
        <a href = "addMeeting.php?studentId=<?php echo $studentId; ?>"> <button class = "btn btn-primary"> <i class="fa-solid fa-plus"></i> Add meetings </button> </a>
        <br>
        <br>

        <h1 style = "float: left;"> Student's Meetings </h1>
        <br>
        <br>
        <br>
        <br>

        <!-- Mini-Nav Bar for Meetings Subsections -->
        <div id="meetings-mini-nav" style="display: flex; justify-content: center; margin-bottom: 24px; gap: 24px; margin-top: 0;">
            <button id="mini-nav-upcoming" class="mini-nav-btn mini-nav-selected" onclick="showMeetingsSubsection('upcoming')">Upcoming Meetings</button>
            <button id="mini-nav-past" class="mini-nav-btn" onclick="showMeetingsSubsection('past')">Past Meetings</button>
        </div>

        <!-- Upcoming Meetings Section -->
        <div id="upcoming-meetings-section">
            <?php if (empty($upcomingMeetings)) { ?>
                <p class="text-center text-muted">No upcoming meetings scheduled.</p>
            <?php } else { ?>
                <ol class="list-group list-group-numbered" id="upcoming-meetings-list">
                    <?php foreach ($upcomingMeetings as $meeting) { ?>
                        <div class="meeting">
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold">Meeting on <?php echo date('Y-m-d H:i', strtotime($meeting['meetingDate'])); ?></div>
                                    <br>
                                    <p style="float: left;"> <span style="font-weight: bold;"> Topics: </span> <?php echo htmlspecialchars($meeting['meetingTopic']); ?> </p>
                                    <br>
                                    <p style="float: left;"> <span style="font-weight: bold;"> Activities: </span> <?php echo htmlspecialchars($meeting['meetingActivities']); ?> </p>
                                </div>
                                <div>
                                    <a href="meeting.php?meetingId=<?php echo $meeting['meetingId']; ?>"> <button type="button" class="btn btn-primary">View details</button> </a>
                                </div>
                            </li>
                        </div>
                    <?php } ?>
                </ol>
            <?php } ?>
        </div>

        <!-- Past Meetings Section -->
        <div id="past-meetings-section" style="display: none;">
            <?php if (empty($pastMeetings)) { ?>
                <p class="text-center text-muted">No past meetings found.</p>
            <?php } else { ?>
                <ol class="list-group list-group-numbered" id="past-meetings-list">
                    <?php foreach ($pastMeetings as $meeting) { ?>
                        <div class="meeting">
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold">Meeting on <?php echo date('Y-m-d H:i', strtotime($meeting['meetingDate'])); ?></div>
                                    <br>
                                    <p style="float: left;"> <span style="font-weight: bold;"> Topics: </span> <?php echo htmlspecialchars($meeting['meetingTopic']); ?> </p>
                                    <br>
                                    <p style="float: left;"> <span style="font-weight: bold;"> Activities: </span> <?php echo htmlspecialchars($meeting['meetingActivities']); ?> </p>
                                </div>
                                <div>
                                    <a href="meeting.php?meetingId=<?php echo $meeting['meetingId']; ?>"> <button type="button" class="btn btn-primary">View details</button> </a>
                                </div>
                            </li>
                        </div>
                    <?php } ?>
                </ol>
            <?php } ?>
        </div>

        <br>
    </div>

    <!-- Universities --> 
    <div id="university-applications-section" class="content-section visible">

        <a href = "addApplicationStudent.php?institutionType=0&studentId=<?php echo $studentId; ?>"> <button class = "btn btn-primary"> <i class="fa-solid fa-plus"></i> Add University application </button> </a>
        <br>
        <br>

        <h1 style = "float: left;"> University Applications</h1>
        <input type="text" class = "search-bar-name" id="search-bar-university-name" onkeyup="searchFunctionUniversities()" placeholder="Search for university's name.." title="Type in a name">
        <input type="text" class = "search-bar-country" id="search-bar-university-country" onkeyup="searchFunctionUniversities()" placeholder="Search for university's country.." title="Type in a name">

        <div class = "comissionable-filter">
            <input checked onchange = "searchFunctionUniversities()" type="checkbox" id="commissionable-university" name="commisionable-university" value="1">
            <label for="commissionable-university"> Commissionable Universities</label>
        </div>

        <div class = "comissionable-filter">
            <input checked onchange = "searchFunctionUniversities()" type="checkbox" id="non-commissionable-university" name="non-commisionable-university" value="0">
            <label for="non-commissionable-university"> Non-commissionable Universities</label>
        </div>

        <ol class="list-group list-group-numbered" id = "university-applications-list">
            <?php
            for ($i = 0; $i < $nUniversities; $i++) { ?>
                <div class = "university-application">
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold university-name"><?php echo $arrUniversityName[$i]; ?></div>
                            <p class = "university-country"> <?php echo $arrUniversityCountry[$i]; ?> </p>
                            <?php
                            if (trim($arrUniversityAppStatus[$i]) == "Accepted") { ?>
                                <p> Scholarship: <span class = "university-commission">  <b><?php echo $arrUniversityScholarship[$i]; ?></b> </span> </p>
                            <?php } ?>
                            <p> Comission: <span class = "university-commission">  <b><?php echo $arrUniversityComission[$i]; ?> </b></span> </p>
                        </div>
                        <span class="badge bg-primary rounded-pill" style = "background-color: <?php echo getStatusColor($arrUniversityAppStatus[$i]); ?> !important;"> <?php echo $arrUniversityAppStatus[$i]; ?></span>
                        <div>
                            <a href = "application.php?applicationId=<?php echo $arrUniversityAppId[$i]; ?>"> <button type="button" class="btn btn-primary">View details</button> </a>
                        </div>
                    </li>
                </div>
            <?php
            }
            ?>
        </ol>

        <br>
    </div>

    <!-- Summer Schools --> 
    <div id="summer-applications-section" class="content-section">
        <a href = "addApplicationStudent.php?institutionType=1&studentId=<?php echo $studentId; ?>"> <button class = "btn btn-primary"> <i class="fa-solid fa-plus"></i> Add Summer School application </button> </a>
        <br>
        <br>

        <h1 style = "float: left;"> Summer School Applications</h1>
        <input type="text" class = "search-bar-name" id="search-bar-summer-name" onkeyup="searchFunctionSummer()" placeholder="Search for summer school's name.." title="Type in a name">
        <input type="text" class = "search-bar-country" id="search-bar-summer-country" onkeyup="searchFunctionSummer()" placeholder="Search for summer school's country.." title="Type in a name">

        <div class = "comissionable-filter">
            <input checked onchange = "searchFunctionSummer()" type="checkbox" id="commissionable-summer" name="commisionable-summer" value="1">
            <label for="commissionable-summer"> Commissionable Schools</label>
        </div>

        <div class = "comissionable-filter">
            <input checked onchange = "searchFunctionSummer()" type="checkbox" id="non-commissionable-summer" name="non-commisionable-summer" value="0">
            <label for="non-commissionable-summer"> Non-commissionable Schools</label>
        </div>

        <ol class="list-group list-group-numbered" id = "summer-applications-list">
            <?php
            for ($i = 0; $i < $nSummer; $i++) { ?>
                <div class = "summer-application">
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold summer-name"><?php echo $arrSummerName[$i]; ?></div>
                            <p class = "summer-country"> <?php echo $arrSummerCountry[$i]; ?> </p>
                            <p> Comission: <span class = "summer-commission">  <?php echo $arrSummerComission[$i]; ?> </span> </p>

                        </div>
                        <span class="badge bg-primary rounded-pill" style = "background-color: <?php echo getStatusColor($arrSummerAppStatus[$i]); ?> !important;"> <?php echo $arrSummerAppStatus[$i]; ?></span>
                        <div>
                            <a href = "application.php?applicationId=<?php echo $arrSummerAppId[$i]; ?>"> <button type="button" class="btn btn-primary">View details</button> </a>
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
        <a href = "addApplicationStudent.php?institutionType=2&studentId=<?php echo $studentId; ?>"> <button class = "btn btn-primary"> <i class="fa-solid fa-plus"></i> Add Boarding School application </button> </a>
        <br>
        <br>

        <h1 style = "float: left;"> Boarding School Applications</h1>
        <input type="text" class = "search-bar-name" id="search-bar-boarding-name" onkeyup="searchFunctionBoarding()" placeholder="Search for boarding school's name.." title="Type in a name">
        <input type="text" class = "search-bar-country" id="search-bar-boarding-country" onkeyup="searchFunctionBoarding()" placeholder="Search for boarding school's country.." title="Type in a name">

        <div class = "comissionable-filter">
            <input checked onchange = "searchFunctionBoarding()" type="checkbox" id="commissionable-boarding" name="commisionable-boarding" value="1">
            <label for="commissionable-boarding"> Commissionable Schools</label>
        </div>

        <div class = "comissionable-filter">
            <input checked onchange = "searchFunctionBoarding()" type="checkbox" id="non-commissionable-boarding" name="non-commisionable-boarding" value="0">
            <label for="non-commissionable-boarding"> Non-commissionable Schools</label>
        </div>

        <ol class="list-group list-group-numbered" id = "boarding-applications-list">
            <?php
            for ($i = 0; $i < $nBoarding; $i++) { ?>
                <div class = "boarding-application">
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold boarding-name"><?php echo $arrBoardingName[$i]; ?></div>
                            <p class = "boarding-country"> <?php echo $arrBoardingCountry[$i]; ?> </p>
                            <p> Comission: <span class = "boarding-commission">  <?php echo $arrBoardingComission[$i]; ?> </span> </p>

                        </div>
                        <span class="badge bg-primary rounded-pill" style = "background-color: <?php echo getStatusColor($arrBoardingAppStatus[$i]); ?> !important;"> <?php echo $arrBoardingAppStatus[$i]; ?></span>
                        <div>
                            <a href = "application.php?applicationId=<?php echo $arrBoardingAppId[$i]; ?>"> <button type="button" class="btn btn-primary">View details</button> </a>
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
            <button id="mini-nav-tasks" class="mini-nav-btn" onclick="showOverviewSubsection('tasks')">Tasks</button>
        </div>
        <style>
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
        </style>
        <script>
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
            } else if (section === 'activities') {
                document.getElementById('overview-activities-section').style.display = '';
                document.getElementById('mini-nav-activities').classList.add('mini-nav-selected');
            } else if (section === 'exams') {
                document.getElementById('overview-exams-section').style.display = '';
                document.getElementById('mini-nav-exams').classList.add('mini-nav-selected');
            } else if (section === 'tasks') {
                document.getElementById('overview-tasks-section').style.display = '';
                document.getElementById('mini-nav-tasks').classList.add('mini-nav-selected');
                loadTasks(); // Load tasks when section is shown
            }
        }
        // Default: show checklist
        window.addEventListener('DOMContentLoaded', function() {
            showOverviewSubsection('checklist');
        });
        </script>

        <script>
        function showMeetingsSubsection(section) {
            // Hide all meeting sections
            document.getElementById('upcoming-meetings-section').style.display = 'none';
            document.getElementById('past-meetings-section').style.display = 'none';
            // Remove selected class
            document.getElementById('mini-nav-upcoming').classList.remove('mini-nav-selected');
            document.getElementById('mini-nav-past').classList.remove('mini-nav-selected');
            // Show selected
            if (section === 'upcoming') {
                document.getElementById('upcoming-meetings-section').style.display = '';
                document.getElementById('mini-nav-upcoming').classList.add('mini-nav-selected');
            } else if (section === 'past') {
                document.getElementById('past-meetings-section').style.display = '';
                document.getElementById('mini-nav-past').classList.add('mini-nav-selected');
            }
        }
        // Default: show upcoming meetings
        window.addEventListener('DOMContentLoaded', function() {
            showMeetingsSubsection('upcoming');
        });
        </script>
        

        <div id="overview-checklist-section">
        <div class="card mb-4 p-3" style="background:#f8f9fa; border-radius:8px; max-width:900px; margin:auto;">
        <?php
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
                $checklistIdToStatus[$checklistId] = $row['status']; // Only one status per checklist item (see user logic)
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
                            // For extra checklist items, get university name directly from universityId
                            $sqlUni = "SELECT universityName FROM universities WHERE universityId = '" . mysqli_real_escape_string($link, $universityId) . "'";
                            $queryUni = mysqli_query($link, $sqlUni);
                            if ($uniRow = mysqli_fetch_assoc($queryUni)) {
                                $universityNames[] = $uniRow['universityName'];
                            }
                        } else {
                            // For university checklist items, get university name via universities_checklist
                            $sqlUniChecklist = "SELECT u.universityName FROM universities_checklist uc JOIN universities u ON uc.universityId = u.universityId WHERE uc.checklistId = '" . mysqli_real_escape_string($link, $checklistId) . "' AND uc.universityId = '" . mysqli_real_escape_string($link, $universityId) . "'";
                            $queryUniChecklist = mysqli_query($link, $sqlUniChecklist);
                            while ($uniRow = mysqli_fetch_assoc($queryUniChecklist)) {
                                $universityNames[] = $uniRow['universityName'];
                            }
                        }
                    }
                }
                // Remove duplicates
                $universityNames = array_unique($universityNames);
                $checklistIdToUniversities[$checklistId] = $universityNames;
            }
            // 4. Display
            // Remove progress bar logic and rendering
            // Only render the checklist table
            // --- Add Checklist Item Button and Form ---
            echo '<button id="show-add-checklist-btn" class="btn btn-primary w-100 mb-2">Add Checklist Item</button>';
            echo '<form id="add-checklist-form" class="mb-2" style="display:none;">';
            echo '<div class="form-group mb-2">';
            echo '<label>Universities associated with this checklist item:</label><br>';
            $universityIdToName = [];
            if (!empty($applicationIdToUniversity)) {
                $universityIds = array_unique(array_values($applicationIdToUniversity));
                $in = implode(',', array_map('intval', $universityIds));
                $sqlUnis = "SELECT universityId, universityName FROM universities WHERE universityId IN ($in)";
                $queryUnis = mysqli_query($link, $sqlUnis);
                while ($row = mysqli_fetch_assoc($queryUnis)) {
                    $universityIdToName[$row['universityId']] = $row['universityName'];
                }
            }
            foreach ($applicationIdToUniversity as $appId => $uniId) {
                $uniName = isset($universityIdToName[$uniId]) ? $universityIdToName[$uniId] : $uniId;
                echo '<div class="form-check d-flex align-items-center mb-1" style="gap:8px;">';
                echo '<input class="form-check-input" type="checkbox" name="applicationIds[]" value="' . $appId . '" id="app-' . $appId . '">';
                echo '<label class="form-check-label mb-0" for="app-' . $appId . '" style="font-weight:500; display:inline;">' . htmlspecialchars($uniName) . '</label>';
                echo '</div>';
            }
            echo '</div>';
            echo '<div class="form-group mb-2">';
            echo '<input type="text" class="form-control" name="checklistName" placeholder="New Checklist Item" required>';
            echo '</div>';
            echo '<div class="d-flex gap-2">';
            echo '<button type="submit" class="btn btn-primary w-100">Add</button>';
            echo '<button type="button" id="cancel-add-checklist-btn" class="btn btn-secondary w-100 ml-2">Cancel</button>';
            echo '</div>';
            echo '<div id="add-checklist-error" class="text-danger mb-2"></div>';
            echo '</form>';
            echo '<script>';
            echo 'document.getElementById("show-add-checklist-btn").addEventListener("click", function() {';
            echo '  document.getElementById("add-checklist-form").style.display = ""; this.style.display = "none"; });';
            echo 'document.getElementById("cancel-add-checklist-btn").addEventListener("click", function() {';
            echo '  document.getElementById("add-checklist-form").style.display = "none"; document.getElementById("show-add-checklist-btn").style.display = ""; });';
            echo 'document.getElementById("add-checklist-form").addEventListener("submit", function(e) {';
            echo '  e.preventDefault(); var form = this; var formData = new FormData(form); formData.append("studentId", ' . json_encode($studentId) . ');';
            echo '  var errorDiv = document.getElementById("add-checklist-error"); errorDiv.textContent = "";';
            echo '  fetch("checklistActions.php?action=add", {';
            echo '    method: "POST", body: new URLSearchParams(Array.from(formData))';
            echo '  }).then(response => response.json()).then(data => {';
            echo '    if (data.error) { errorDiv.textContent = data.error; } else { form.reset(); location.reload(); }';
            echo '  }).catch(() => { errorDiv.textContent = "An error occurred."; });';
            echo '});';
            echo '</script>';
            // --- End Add Checklist Item Button and Form ---
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
                    // Custom display for empty university list
                    if (!$isCustom) {
                        $uniDisplay = (empty($universities) ? 'Checklist item is no longer required' : implode(", ", $universities));
                        echo ' <span style="color: #888; font-size: 13px;">[' . $uniDisplay . ']</span>';
                    } else {
                        echo ' <span style="color: #888; font-size: 13px;">[' . implode(", ", $universities) . ']</span>';
                    }
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
            echo '<p class="mb-0">No applications found for this student.</p>';
        }
        ?>
        </div>
        <div style="height: 40px;"></div>
        </div> <!-- end overview-checklist-section -->

        <div id="overview-activities-section">
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
        <div style="height: 40px;"></div>
        </div> <!-- end overview-activities-section -->


        <div id="overview-exams-section">
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
            <div style="height: 40px;"></div>
        </div>
        </div> <!-- end overview-exams-section -->

        <div id="overview-tasks-section">
        <div class="card mb-4 p-3" style="background:#f8f9fa; border-radius:8px; max-width:900px; margin:auto;">
            <button id="show-add-task-btn" class="btn btn-primary w-100 mb-2">Add Task</button>
            <form id="add-task-form" class="mb-2" style="display:none;">
                <div class="form-group mb-2">
                    <textarea class="form-control" name="taskText" placeholder="Task description" rows="3" required></textarea>
                </div>
                <div class="form-group mb-2">
                    <label for="taskDeadline" class="form-label">Deadline (optional)</label>
                    <input type="date" class="form-control" id="taskDeadline" name="taskDeadline">
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Add Task</button>
                    <button type="button" id="cancel-add-task-btn" class="btn btn-secondary w-100 ml-2">Cancel</button>
                </div>
            </form>
            <div id="add-task-error" class="text-danger mb-2"></div>
            
            <!-- Task Filtering and Sorting -->
            <div class="task-controls mb-3" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div class="task-filters" style="display: flex; gap: 8px; align-items: center;">
                    <label for="task-status-filter" class="form-label mb-0" style="font-size: 0.9em; color: #6c757d;">Filter:</label>
                    <select id="task-status-filter" class="form-control form-control-sm" style="width: auto;">
                        <option value="all">All Tasks</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Done">Done</option>
                        <option value="meeting">Meeting Tasks</option>
                        <option value="general">General Tasks</option>
                    </select>
                </div>
                <div class="task-sorting" style="display: flex; gap: 8px; align-items: center;">
                    <label for="task-sort-by" class="form-label mb-0" style="font-size: 0.9em; color: #6c757d;">Sort by:</label>
                    <select id="task-sort-by" class="form-control form-control-sm" style="width: auto;">
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
        <div style="height: 40px;"></div>
        </div> <!-- end overview-tasks-section -->

    </div>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <script>
        function showSection(sectionId, buttonId) {
            // Hide all sections
            document.getElementById('application-overview-section').classList.remove('visible');
            document.getElementById('university-applications-section').classList.remove('visible');
            document.getElementById('summer-applications-section').classList.remove('visible');
            document.getElementById('boarding-applications-section').classList.remove('visible');
            document.getElementById('meetings-section').classList.remove('visible');

            // Show the selected section
            document.getElementById(sectionId).classList.add('visible');

            if (document.getElementById('application-overview-btn').classList.contains('active')) {
                document.getElementById('application-overview-btn').classList.remove('active');
                // console.log("Overview was active");
            }

            if (document.getElementById('meetings-btn').classList.contains('active')) {
                document.getElementById('meetings-btn').classList.remove('active');
                // console.log("Meetings was active");
            }

            if (document.getElementById('university-applications-btn').classList.contains('active')) {
                document.getElementById('university-applications-btn').classList.remove('active');
                // console.log("University was active");
            }

            if (document.getElementById('summer-applications-btn').classList.contains('active')) {
                document.getElementById('summer-applications-btn').classList.remove('active');
                // console.log("Summer was active");
            }
            
            if (document.getElementById('boarding-applications-btn').classList.contains('active')) {
                document.getElementById('boarding-applications-btn').classList.remove('active');
                // console.log("Boarding was active");
            }

            activeButton = document.getElementById(buttonId);
            activeButton.classList.add('active');
        }
    </script>


<script>
        function searchFunctionUniversities() {
            var input, filter, ul, li, a, i, txtValue, countDisplay;
            inputName = document.getElementById("search-bar-university-name");
            inputCountry = document.getElementById("search-bar-university-country");
            inputCheckbox1 = document.getElementById("commissionable-university");
            inputCheckbox2 = document.getElementById("non-commissionable-university");


            filterName = inputName.value.toUpperCase();
            filterCountry = inputCountry.value.toUpperCase();
            checkbox1 = inputCheckbox1.checked;
            checkbox2 = inputCheckbox2.checked;


            list = document.getElementById("university-applications-list");
            universities = list.getElementsByClassName("university-application");
            countDisplay = 0;

            for (i = 0; i < universities.length; i++) {
                name = universities[i].getElementsByClassName("university-name")[0].innerHTML;
                country = universities[i].getElementsByClassName("university-country")[0].innerHTML;
                commission = universities[i].getElementsByClassName("university-commission")[0].innerHTML;
                
                if (name.toUpperCase().indexOf(filterName) > -1 && country.toUpperCase().indexOf(filterCountry) > -1 && ((commission > 0 && checkbox1) || (commission == 0 && checkbox2))) {
                    universities[i].style.display = "";
                    countDisplay++;
                } else {
                    universities[i].style.display = "none";
                }                


            }

            document.getElementsByClassName("search-count")[0].innerHTML = countDisplay;

        }

        function searchFunctionSummer() {
            var input, filter, ul, li, a, i, txtValue, countDisplay;
            inputName = document.getElementById("search-bar-summer-name");
            inputCountry = document.getElementById("search-bar-summer-country");
            inputCheckbox1 = document.getElementById("commissionable-summer");
            inputCheckbox2 = document.getElementById("non-commissionable-summer");


            filterName = inputName.value.toUpperCase();
            filterCountry = inputCountry.value.toUpperCase();
            checkbox1 = inputCheckbox1.checked;
            checkbox2 = inputCheckbox2.checked;


            list = document.getElementById("summer-applications-list");
            universities = list.getElementsByClassName("summer-application");
            countDisplay = 0;

            for (i = 0; i < universities.length; i++) {
                name = universities[i].getElementsByClassName("summer-name")[0].innerHTML;
                country = universities[i].getElementsByClassName("summer-country")[0].innerHTML;
                commission = universities[i].getElementsByClassName("summer-commission")[0].innerHTML;
                
                if (name.toUpperCase().indexOf(filterName) > -1 && country.toUpperCase().indexOf(filterCountry) > -1 && ((commission > 0 && checkbox1) || (commission == 0 && checkbox2))) {
                    universities[i].style.display = "";
                    countDisplay++;
                } else {
                    universities[i].style.display = "none";
                }                


            }

            document.getElementsByClassName("search-count")[0].innerHTML = countDisplay;

        }

        function searchFunctionBoarding() {
            var input, filter, ul, li, a, i, txtValue, countDisplay;
            inputName = document.getElementById("search-bar-boarding-name");
            inputCountry = document.getElementById("search-bar-boarding-country");
            inputCheckbox1 = document.getElementById("commissionable-boarding");
            inputCheckbox2 = document.getElementById("non-commissionable-boarding");


            filterName = inputName.value.toUpperCase();
            filterCountry = inputCountry.value.toUpperCase();
            checkbox1 = inputCheckbox1.checked;
            checkbox2 = inputCheckbox2.checked;


            list = document.getElementById("boarding-applications-list");
            universities = list.getElementsByClassName("boarding-application");
            countDisplay = 0;

            for (i = 0; i < universities.length; i++) {
                name = universities[i].getElementsByClassName("boarding-name")[0].innerHTML;
                country = universities[i].getElementsByClassName("boarding-country")[0].innerHTML;
                commission = universities[i].getElementsByClassName("boarding-commission")[0].innerHTML;
                
                if (name.toUpperCase().indexOf(filterName) > -1 && country.toUpperCase().indexOf(filterCountry) > -1 && ((commission > 0 && checkbox1) || (commission == 0 && checkbox2))) {
                    universities[i].style.display = "";
                    countDisplay++;
                } else {
                    universities[i].style.display = "none";
                }                


            }

            document.getElementsByClassName("search-count")[0].innerHTML = countDisplay;

        }
</script>

<script>
    function confirmRemove(link) {
        const userConfirmed = confirm("Are you sure you want to delete this student?");
        if (userConfirmed) {
            window.location.href = link;
        } else {
            alert("Action canceled.");
        }
    }

    function confirmRestore(link) {
        const userConfirmed = confirm("Are you sure you want to restore this student?");
        if (userConfirmed) {
            window.location.href = link;
        } else {
            alert("Action canceled.");
        }
    }

    function confirmGraduate(link) {
        const userConfirmed = confirm("Are you sure you want to mark this student as graduated?");
        if (userConfirmed) {
            window.location.href = link;
        } else {
            alert("Action canceled.");
        }
    }
</script>

<script>
    function showPopup() {
        document.getElementById('popup-container').style.display = 'flex';
    }

    function closePopup() {
        document.getElementById('popup-container').style.display = 'none';
    }
</script>

<script>
window.addEventListener('DOMContentLoaded', function() {
    if (document.cookie.split(';').some((item) => item.trim().startsWith('showOverview='))) {
        // Hide all content sections
        var sections = document.querySelectorAll('.content-section');
        sections.forEach(function(section) {
            section.classList.remove('visible');
        });
        // Show only the Application Overview section
        document.getElementById('application-overview-section').classList.add('visible');
        // Remove 'active' from all tab buttons
        var buttons = document.querySelectorAll('.tab-button');
        buttons.forEach(function(btn) {
            btn.classList.remove('active');
        });
        // Add 'active' to the Application Overview button
        document.getElementById('application-overview-btn').classList.add('active');
        document.cookie = 'showOverview=; Max-Age=0; path=/'; // Delete the cookie
    }
});
</script>

<script>
// Set showOverview cookie when Edit button is clicked in the exams table
window.addEventListener('DOMContentLoaded', function() {
    var editButtons = document.querySelectorAll('form button.btn-warning[name="edit_exam"], form button.btn-warning');
    editButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.cookie = 'showOverview=1; path=/';
        });
    });
});
</script>

<script>
let editingExamId = null;

function fetchAndRenderExams() {
    const studentId = <?php echo json_encode($studentId); ?>;
    fetch('examActions.php?action=fetch&studentId=' + studentId)
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
                            '<button class="btn btn-warning btn-sm" onclick="startExamEdit(' + examIdInt + ')">Edit</button> ' +
                            '<button class="btn btn-danger btn-sm" onclick="deleteExam(' + examIdInt + ')">Delete</button>' +
                            '<div id="delete-exam-error-' + examIdInt + '" class="text-danger mt-2"></div>' +
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
    fetch('examActions.php?action=edit', {
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

function deleteExam(examId) {
    if (!confirm('Are you sure you want to delete this exam?')) return;
    const studentId = <?php echo json_encode($studentId); ?>;
    const errorDiv = document.getElementById('delete-exam-error-' + examId);
    if (errorDiv) errorDiv.textContent = '';
    fetch('examActions.php?action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `studentId=${encodeURIComponent(studentId)}&examId=${encodeURIComponent(examId)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            if (errorDiv) errorDiv.textContent = data.error;
        } else {
            fetchAndRenderExams();
        }
    })
    .catch(() => {
        if (errorDiv) errorDiv.textContent = 'An error occurred.';
    });
}

// Fetch exams on page load
fetchAndRenderExams();
// Fetch activities on page load
fetchAndRenderActivities();
</script>

<script>
let editingActivityId = null;
let editingActivityData = null;

function fetchAndRenderActivities() {
    const studentId = <?php echo json_encode($studentId); ?>;
    fetch('activitiesActions.php?action=fetch&studentId=' + studentId)
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
                    html += `<div class=\"activity-card mb-4 p-3\" style=\"background:#f8f9fa; border-radius:8px; max-width:700px; margin:auto; text-align:left;\">
                        <form onsubmit=\"return saveActivityEdit(${activityIdInt})\">
                            <div class=\"form-group mb-2\"><select class=\"form-control\" id=\"edit-activity-type\">
                                <option value=\"\">Select activity type</option>
                                <option value=\"Academic\" ${activity.activityType === 'Academic' ? 'selected' : ''}>Academic</option>
                                <option value=\"Art\" ${activity.activityType === 'Art' ? 'selected' : ''}>Art</option>
                                <option value=\"Athletics: Club\" ${activity.activityType === 'Athletics: Club' ? 'selected' : ''}>Athletics: Club</option>
                                <option value=\"Athletics: JV/Varsity\" ${activity.activityType === 'Athletics: JV/Varsity' ? 'selected' : ''}>Athletics: JV/Varsity</option>
                                <option value=\"Career-Oriented\" ${activity.activityType === 'Career-Oriented' ? 'selected' : ''}>Career-Oriented</option>
                                <option value=\"Community Service (Volunteer)\" ${activity.activityType === 'Community Service (Volunteer)' ? 'selected' : ''}>Community Service (Volunteer)</option>
                                <option value=\"Computer/Technology\" ${activity.activityType === 'Computer/Technology' ? 'selected' : ''}>Computer/Technology</option>
                                <option value=\"Cultural\" ${activity.activityType === 'Cultural' ? 'selected' : ''}>Cultural</option>
                                <option value=\"Dance\" ${activity.activityType === 'Dance' ? 'selected' : ''}>Dance</option>
                                <option value=\"Debate/Speech\" ${activity.activityType === 'Debate/Speech' ? 'selected' : ''}>Debate/Speech</option>
                                <option value=\"Environmental\" ${activity.activityType === 'Environmental' ? 'selected' : ''}>Environmental</option>
                                <option value=\"Family Responsibilities\" ${activity.activityType === 'Family Responsibilities' ? 'selected' : ''}>Family Responsibilities</option>
                                <option value=\"Foreign Exchange\" ${activity.activityType === 'Foreign Exchange' ? 'selected' : ''}>Foreign Exchange</option>
                                <option value=\"Internship\" ${activity.activityType === 'Internship' ? 'selected' : ''}>Internship</option>
                                <option value=\"Journalism/Publication\" ${activity.activityType === 'Journalism/Publication' ? 'selected' : ''}>Journalism/Publication</option>
                                <option value=\"Junior R.O.T.C.\" ${activity.activityType === 'Junior R.O.T.C.' ? 'selected' : ''}>Junior R.O.T.C.</option>
                                <option value=\"LGBT\" ${activity.activityType === 'LGBT' ? 'selected' : ''}>LGBT</option>
                                <option value=\"Music: Instrumental\" ${activity.activityType === 'Music: Instrumental' ? 'selected' : ''}>Music: Instrumental</option>
                                <option value=\"Music: Vocal\" ${activity.activityType === 'Music: Vocal' ? 'selected' : ''}>Music: Vocal</option>
                                <option value=\"Religious\" ${activity.activityType === 'Religious' ? 'selected' : ''}>Religious</option>
                                <option value=\"Research\" ${activity.activityType === 'Research' ? 'selected' : ''}>Research</option>
                                <option value=\"Robotics\" ${activity.activityType === 'Robotics' ? 'selected' : ''}>Robotics</option>
                                <option value=\"School Spirit\" ${activity.activityType === 'School Spirit' ? 'selected' : ''}>School Spirit</option>
                                <option value=\"Science/Math\" ${activity.activityType === 'Science/Math' ? 'selected' : ''}>Science/Math</option>
                                <option value=\"Student Govt./Politics\" ${activity.activityType === 'Student Govt./Politics' ? 'selected' : ''}>Student Govt./Politics</option>
                                <option value=\"Theater/Drama\" ${activity.activityType === 'Theater/Drama' ? 'selected' : ''}>Theater/Drama</option>
                                <option value=\"Work (Paid)\" ${activity.activityType === 'Work (Paid)' ? 'selected' : ''}>Work (Paid)</option>
                                <option value=\"Other Club/Activity\" ${activity.activityType === 'Other Club/Activity' ? 'selected' : ''}>Other Club/Activity</option>
                            </select></div>
                            <div class=\"form-group mb-2\"><input type=\"text\" class=\"form-control\" id=\"edit-activity-organization\" value=\"${activity.activityOrganization || ''}\"></div>
                            <div class=\"form-group mb-2\"><input type=\"text\" class=\"form-control\" id=\"edit-activity-position\" value=\"${activity.activityPosition || ''}\"></div>
                            <div class=\"form-group mb-2\"><input type=\"number\" class=\"form-control\" id=\"edit-activity-hours\" value=\"${activity.hoursPerWeek || ''}\" min=\"0\"></div>
                            <div class=\"form-group mb-2\"><label for=\"edit-activity-start\" class=\"form-label\">Start Date</label><input type=\"date\" class=\"form-control\" id=\"edit-activity-start\" value=\"${activity.startDate || ''}\"></div>
                            <div class=\"form-group mb-2\"><label for=\"edit-activity-end\" class=\"form-label\">End Date</label><input type=\"date\" class=\"form-control\" id=\"edit-activity-end\" value=\"${activity.endDate || ''}\"></div>
                            <div class=\"form-group mb-2\"><textarea class=\"form-control\" id=\"edit-activity-description\" rows=\"2\">${activity.activityDescription || ''}</textarea></div>
                            <div class=\"d-flex gap-2\">
                                <button type=\"submit\" class=\"btn btn-success w-100\">Save</button>
                                <button type=\"button\" class=\"btn btn-secondary w-100 ml-2\" onclick=\"cancelActivityEdit()\">Cancel</button>
                            </div>
                            <div id=\"edit-activity-error\" class=\"text-danger mt-2\"></div>
                        </form>
                    </div>`;
                } else {
                    html += `<div class=\"activity-card mb-4 p-3\" style=\"background:#f8f9fa; border-radius:8px; max-width:700px; margin:auto; text-align:left;\">
                        <div style='display:flex; justify-content:flex-end; align-items:center;'>
                            <div class='activity-stars'>${renderStars(activity.activityId, parseInt(activity.activityRating) || 0)}</div>
                        </div>
                        <div class=\"mb-1\"><strong>Type:</strong> ${activity.activityType || ''}</div>
                        <div class=\"mb-1\"><strong>Organization:</strong> ${activity.activityOrganization || ''}</div>
                        <div class=\"mb-1\"><strong>Position:</strong> ${activity.activityPosition || ''}</div>
                        <div class=\"mb-1\"><strong>Hours/Week:</strong> ${activity.hoursPerWeek || ''}</div>
                        <div class=\"mb-1\"><strong>Start Date:</strong> ${activity.startDate || ''}</div>
                        <div class=\"mb-1\"><strong>End Date:</strong> ${activity.endDate || ''}</div>
                        <div class=\"mb-1\"><strong>Description:</strong> <br><span style=\"white-space:pre-line;\">${activity.activityDescription || ''}</span></div>
                        <div style='display:flex; justify-content:flex-end; align-items:center; margin-top:8px;'>
                            <button class=\"btn btn-warning btn-sm\" onclick=\"startActivityEdit(${activityIdInt})\">Edit</button>
                            <button class=\"btn btn-danger btn-sm\" style=\"margin-left:8px;\" onclick=\"deleteActivity(${activityIdInt})\">Delete</button>
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
    fetch('activitiesActions.php?action=edit', {
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
    fetch('activitiesActions.php?action=delete', {
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
</script>

<script>
// AJAX for Add Activity
const addActivityForm = document.getElementById('add-activity-form');
addActivityForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const studentId = <?php echo json_encode($studentId); ?>;
    const formData = new FormData(addActivityForm);
    formData.append('studentId', studentId);
    const errorDiv = document.getElementById('add-activity-error');
    errorDiv.textContent = '';
    fetch('activitiesActions.php?action=add', {
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
</script>

<script>
document.getElementById('show-add-activity-btn').addEventListener('click', function() {
    document.getElementById('add-activity-form').style.display = '';
    this.style.display = 'none';
});
document.getElementById('cancel-add-activity-btn').addEventListener('click', function() {
    document.getElementById('add-activity-form').style.display = 'none';
    document.getElementById('show-add-activity-btn').style.display = '';
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    fetchAndRenderExams();
    fetchAndRenderActivities();
});
</script>

<script>
document.getElementById('show-add-exam-btn').addEventListener('click', function() {
    document.getElementById('add-exam-form').style.display = '';
    this.style.display = 'none';
});
document.getElementById('cancel-add-exam-btn').addEventListener('click', function() {
    document.getElementById('add-exam-form').style.display = 'none';
    document.getElementById('show-add-exam-btn').style.display = '';
});
</script>

<script>
const addExamForm = document.getElementById('add-exam-form');
addExamForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const studentId = <?php echo json_encode($studentId); ?>;
    const formData = new FormData(addExamForm);
    formData.append('studentId', studentId);
    const errorDiv = document.getElementById('add-exam-error');
    errorDiv.textContent = '';
    fetch('examActions.php?action=add', {
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
</script>

<script>
function renderStars(activityId, rating) {
    let stars = '';
    for (let i = 0; i < 5; i++) {
        if (i < rating) {
            stars += `<span style='cursor:pointer;font-size:1.4em;color:#FFD700;' onclick='setActivityRating(${activityId},${i+1})'>★</span>`;
        } else {
            stars += `<span style='cursor:pointer;font-size:1.4em;color:#CCCCCC;' onclick='setActivityRating(${activityId},${i+1})'>☆</span>`;
        }
    }
    return stars;
}
</script>

<script>
function setActivityRating(activityId, rating) {
    const studentId = <?php echo json_encode($studentId); ?>;
    fetch('activitiesActions.php?action=edit', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `studentId=${encodeURIComponent(studentId)}&activityId=${encodeURIComponent(activityId)}&activityRating=${encodeURIComponent(rating)}`
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
        alert('An error occurred while updating the activity rating.');
    });
}
</script>

<script>
document.addEventListener('submit', function(e) {
    const form = e.target;
    if (form.closest('#activities-table-container')) {
        const onsubmitAttr = form.getAttribute('onsubmit') || '';
        const match = onsubmitAttr.match(/saveActivityEdit\((\d+)\)/);
        if (match) {
            e.preventDefault();
            saveActivityEdit(parseInt(match[1]));
            return false;
        }
    }
}, true);

// Task file upload functionality
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
    fileInput.addEventListener('change', (e) => {
        handleFiles(e.target.files, checklistId);
    });
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });
    dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
    });
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        handleFiles(e.dataTransfer.files, checklistId);
    });
}

function handleFiles(files, checklistId) {
    const fileList = document.getElementById('file-list-' + checklistId);
    fileList.innerHTML = '';
    if (files.length === 0) return;
    const file = files[0];
    const fileItem = document.createElement('div');
    fileItem.className = 'file-item';
    const fileName = document.createElement('span');
    fileName.className = 'file-name';
    fileName.textContent = file.name;
    const fileSize = document.createElement('span');
    fileSize.className = 'file-size';
    fileSize.textContent = formatFileSize(file.size);
    const removeBtn = document.createElement('button');
    removeBtn.className = 'remove-file';
    removeBtn.textContent = '×';
    removeBtn.onclick = () => {
        fileList.innerHTML = '';
        document.getElementById('file-input-' + checklistId).value = '';
    };
    fileItem.appendChild(fileName);
    fileItem.appendChild(fileSize);
    fileItem.appendChild(removeBtn);
    fileList.appendChild(fileItem);
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function saveChecklistFile(event, checklistId) {
    event.stopPropagation();
    const studentId = <?php echo json_encode($studentId); ?>;
    const fileInput = document.getElementById('file-input-' + checklistId);
    const file = fileInput.files[0];
    const formData = new FormData();
    formData.append('file', file);
    formData.append('studentId', studentId);
    formData.append('checklistId', checklistId);
    formData.append('markDone', window['isFirstUpload_' + checklistId] ? '1' : '0');
    fetch('uploadChecklistDocument.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Upload response:', data); // Debug output
        if (data.success) {
            document.getElementById('file-upload-msg-' + checklistId).textContent = 'File uploaded successfully!';
            document.getElementById('file-upload-msg-' + checklistId).classList.remove('text-danger');
            document.getElementById('file-upload-msg-' + checklistId).classList.add('text-success');
            if (data.newStatus) {
                // Update the status badge in the checklist row
                var badge = document.querySelector('li.checklist-item[onclick*="' + checklistId + '"] .badge');
                if (badge) {
                    badge.textContent = data.newStatus;
                    // Update badge color
                    if (data.newStatus && data.newStatus.trim().toLowerCase() === 'done') {
                        badge.style.backgroundColor = '#28a745';
                    } else {
                        badge.style.backgroundColor = '#c61b75';
                    }
                }
            }
            // Update file name display
            var fileNameDiv = document.querySelector('#checklist-files-' + checklistId + ' .file-upload-container div.mb-2');
            if (data.fileName) {
                if (!fileNameDiv) {
                    var container = document.querySelector('#checklist-files-' + checklistId + ' .file-upload-container');
                    fileNameDiv = document.createElement('div');
                    fileNameDiv.className = 'mb-2';
                    container.insertBefore(fileNameDiv, container.querySelector('a.btn-info, button.btn-success, #file-upload-msg-' + checklistId));
                }
                fileNameDiv.innerHTML = '<strong>Uploaded file:</strong> ' + data.fileName;
            } else if (fileNameDiv) {
                fileNameDiv.remove();
            }
            // Update Save button text
            var saveBtn = document.getElementById('save-file-btn-' + checklistId);
            if (saveBtn) {
                saveBtn.textContent = 'Save';
            }
            // Update Download button visibility
            var downloadBtn = document.querySelector('#checklist-files-' + checklistId + ' a.btn-info');
            if (data.hasDocument) {
                if (!downloadBtn) {
                    var container = document.querySelector('#checklist-files-' + checklistId + ' .file-upload-container');
                    var btn = document.createElement('a');
                    btn.href = 'downloadChecklistDocument.php?studentId=' + encodeURIComponent(studentId) + '&checklistId=' + encodeURIComponent(checklistId);
                    btn.className = 'btn btn-info';
                    btn.textContent = 'Download';
                    container.appendChild(btn);
                }
            }
            // Update the JS variable for first upload
            window['isFirstUpload_' + checklistId] = !data.hasDocument;
            // No progress bar to update
        } else {
            document.getElementById('file-upload-msg-' + checklistId).textContent = data.error || 'Upload failed.';
            document.getElementById('file-upload-msg-' + checklistId).classList.remove('text-success');
            document.getElementById('file-upload-msg-' + checklistId).classList.add('text-danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('file-upload-msg-' + checklistId).textContent = 'An error occurred while uploading the file.';
        document.getElementById('file-upload-msg-' + checklistId).classList.remove('text-success');
        document.getElementById('file-upload-msg-' + checklistId).classList.add('text-danger');
    });
}

function onFileSelected(checklistId) {
    const fileInput = document.getElementById('file-input-' + checklistId);
    const msgDiv = document.getElementById('file-upload-msg-' + checklistId);
    msgDiv.textContent = '';
    if (fileInput.files && fileInput.files.length > 0) {
        msgDiv.textContent = 'File selected: ' + fileInput.files[0].name;
        msgDiv.classList.remove('text-danger');
        msgDiv.classList.add('text-info');
    } else {
        msgDiv.textContent = '';
        msgDiv.classList.remove('text-info');
    }
}

// Tasks functionality
let tasksLoaded = false;



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
                <a href="meeting.php?meetingId=${task.meetingId}" style="color: #17a2b8; text-decoration: none;">
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
                    <button class="btn btn-sm btn-outline-primary" 
                            onclick="editTask(${task.taskId})" 
                            title="Edit Task">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" 
                            onclick="deleteTask(${task.taskId})" 
                            title="Delete Task">
                        <i class="fas fa-trash"></i>
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
    
    fetch('tasksActions.php?action=toggle', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            tasksLoaded = false; // Reset to force reload
            loadTasks(); // Reload tasks to update display
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the task.');
    });
}

function editTask(taskId) {
    // Find the task item by looking for the edit button with this taskId
    const editButton = document.querySelector(`button[onclick*="editTask(${taskId})"]`);
    
    if (!editButton) {
        console.error('Edit button not found for taskId:', taskId);
        return;
    }
    
    const taskItem = editButton.closest('li.task-item');
    
    if (!taskItem) {
        console.error('Task item not found for taskId:', taskId);
        return;
    }
    
    const taskTextDiv = taskItem.querySelector('.task-text');
    const deadlineText = taskItem.querySelector('small.text-muted');
    const currentText = taskTextDiv.textContent.trim();
    
    // Extract deadline information before replacing content
    let deadlineValue = '';
    if (deadlineText && deadlineText.textContent) {
        const deadlineTextContent = deadlineText.textContent;
        if (deadlineTextContent.includes('Deadline: ') && !deadlineTextContent.includes('No deadline')) {
            const deadlineDate = deadlineTextContent.replace('Deadline: ', '');
            // Parse date in DD/MM/YYYY format
            const dateParts = deadlineDate.split('/');
            if (dateParts.length === 3) {
                const day = parseInt(dateParts[0]);
                const month = parseInt(dateParts[1]);
                const year = parseInt(dateParts[2]);
                // Format as YYYY-MM-DD directly to avoid timezone issues
                deadlineValue = `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
            }
        }
    }
    
    // Store the original content for restoration
    const originalContent = taskItem.innerHTML;
    taskItem.setAttribute('data-original-content', originalContent);
    
    // Create edit form
    const editForm = document.createElement('div');
    editForm.className = 'edit-task-form';
    editForm.innerHTML = `
        <div class="form-group mb-2">
            <textarea class="form-control" id="edit-task-text-${taskId}" rows="3" required>${escapeHtml(currentText)}</textarea>
        </div>
        <div class="form-group mb-2">
            <label for="edit-task-deadline-${taskId}" class="form-label">Deadline (optional)</label>
            <input type="date" class="form-control" id="edit-task-deadline-${taskId}" value="${deadlineValue}">
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary btn-sm" onclick="saveTaskEdit(${taskId})">Save</button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="cancelTaskEdit(${taskId})">Cancel</button>
        </div>
    `;
    
    // Replace the entire content with edit form
    taskItem.innerHTML = '';
    taskItem.appendChild(editForm);
}

function saveTaskEdit(taskId) {
    const taskText = document.getElementById(`edit-task-text-${taskId}`).value.trim();
    const taskDeadline = document.getElementById(`edit-task-deadline-${taskId}`).value;
    
    if (!taskText) {
        alert('Task text cannot be empty.');
        return;
    }
    
    const formData = new FormData();
    formData.append('taskId', taskId);
    formData.append('taskText', taskText);
    formData.append('taskDeadline', taskDeadline);
    
    fetch('tasksActions.php?action=update', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            tasksLoaded = false; // Reset to force reload
            loadTasks(); // Reload tasks to update display
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the task.');
    });
}

function cancelTaskEdit(taskId) {
    // Find the task item that contains the edit form
    const editForm = document.querySelector(`#edit-task-text-${taskId}`).closest('li.task-item');
    if (!editForm) {
        console.error('Edit form not found for taskId:', taskId);
        return;
    }
    
    const originalContent = editForm.getAttribute('data-original-content');
    
    // Restore the original content
    if (originalContent) {
        editForm.innerHTML = originalContent;
        editForm.removeAttribute('data-original-content');
    }
}

function deleteTask(taskId) {
    if (!confirm('Are you sure you want to delete this task?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('taskId', taskId);
    
    fetch('tasksActions.php?action=delete', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            tasksLoaded = false; // Reset to force reload
            loadTasks(); // Reload tasks to update display
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the task.');
    });
}

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
            
            fetch('tasksActions.php?action=add', {
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
                    loadTasks();
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

// Task filtering and sorting functionality
let allTasks = []; // Store all tasks for filtering/sorting

function loadTasks() {
    if (tasksLoaded) return;
    
    const studentId = <?php echo json_encode($studentId); ?>;
    fetch(`tasksActions.php?action=list&studentId=${studentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allTasks = data.tasks; // Store all tasks
                displayTasks(allTasks);
                tasksLoaded = true;
            } else {
                console.error('Error loading tasks:', data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
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

// Add event listeners for filtering and sorting
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



</script>

</body>
</html>