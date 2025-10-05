<?php

    session_start();
    require_once "../../configDatabase.php";

    // Check if student is logged in
    if (!isset($_SESSION['typeStudent']) || !isset($_SESSION['idStudent'])) {
        header("location: ../index.php");
        die();
    }

    $accountId = $_SESSION['idStudent'];
    $studentEmail = $_SESSION['emailStudent'];
    $studentName = $_SESSION['fullNameStudent'];

    if (isset($_GET['meetingId'])) // testez daca e setat un meeting
        $meetingId = $_GET['meetingId'];
    else {
        header("location: ../index.php");
        die();
    }

    $sqlMeetingData = "SELECT * FROM meetings WHERE `meetingId` = '$meetingId'";    
    $queryMeetingData = mysqli_query($link, $sqlMeetingData);

    if (mysqli_num_rows($queryMeetingData) > 0) // testez daca exista un meeting cu id-ul dat
        $dataMeeting = mysqli_fetch_assoc($queryMeetingData);
    else {
        header("location: ../index.php");
        die();
    }

    $studentId = $dataMeeting['studentId'];
    $consultantId = $dataMeeting['consultantId'];

    if (!($studentId == $accountId)) { // testez daca are acces userul la acest meeting
        header("location: ../index.php");
        die();
    }

    $studentName = $dataMeeting['studentName'];
    $studentSchool = $dataMeeting['studentSchool'];
    $consultantName = $dataMeeting['consultantName'];
    $meetingDate = $dataMeeting['meetingDate'];
    $meetingNotes = $dataMeeting['meetingNotes'];
    $meetingTopic = $dataMeeting['meetingTopic'];
    $meetingActivities = $dataMeeting['meetingActivities'];
?>




<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>Meeting details</title>

    <style>
        #content {
            width: 70%;
            margin: auto;
        }
        #search-bar {
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

    </style>
  </head>

  
  <?php include("../navbarStudent.php"); ?>

  <div id = "content">
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>


    <p class = "student-info"> <span class = "title-info"> Meeting's host: </span> <?php echo $consultantName;  ?> </p>
    <p class = "student-info"> <span class = "title-info"> Student's Name: </span> <a href = "../"><?php echo $studentName; ?> </a> </p>
    <p class = "student-info"> <span class = "title-info"> Student's School: </span> <?php echo $studentSchool ?> </p>
    <p class = "student-info"> <span class = "title-info"> Meeting's date and hour: </span> <?php echo $meetingDate; ?> </p>
    <p class = "student-info"> <span class = "title-info"> Meeting's notes: </span>  <br> <br><?php echo $meetingNotes; ?> </p>
    <p class = "student-info"> <span class = "title-info"> Meeting's Topic: </span>  <br> <br><?php echo $meetingTopic; ?> </p>
    <p class = "student-info"> <span class = "title-info"> Meeting's Activity List: </span>  <br> <br><?php echo $meetingActivities; ?> </p>

    <br>
    <br>

    

    


    <br>
    <br>


    
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <script>
        function confirmRemove(link) {
            const userConfirmed = confirm("Are you sure you want to delete this meeting?");
            if (userConfirmed) {
                window.location.href = link;
            }
        }
    </script>
</body>
</html>