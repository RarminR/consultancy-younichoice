<!doctype html>

<?php

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

    if (isset($_GET['meetingId'])) // testez daca e setat un meeting
        $meetingId = $_GET['meetingId'];
    else {
        header("location: index.php");
        die();
    }

    $sqlMeetingData = "SELECT * FROM meetings WHERE `meetingId` = '$meetingId'";    
    $queryMeetingData = mysqli_query($link, $sqlMeetingData);

    if (mysqli_num_rows($queryMeetingData) > 0) // testez daca exista un meeting cu id-ul dat
        $dataMeeting = mysqli_fetch_assoc($queryMeetingData);
    else {
        header("location: index.php");
        die();
    }

    $studentId = $dataMeeting['studentId'];
    $consultantId = $dataMeeting['consultantId'];

    if (!($consultantId == $accountId || $typeAccount == 1)) { // testez daca are acces userul la acest meeting
        header("location: index.php");
        die();
    }

    $studentName = $dataMeeting['studentName'];
    $studentSchool = $dataMeeting['studentSchool'];
    $consultantName = $dataMeeting['consultantName'];
    $meetingDate = $dataMeeting['meetingDate'];
    $meetingNotes = $dataMeeting['meetingNotes'];

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $meetingNotes = $_POST['meeting-notes'];
        
        $meetingTopic = $_POST['topic'];
        $selectedActivities = $_POST['activities']; // Retrieves selected values as an array

        $stringActivities = "";
        foreach ($selectedActivities as $activity) {
            $stringActivities .= $activity;
            $stringActivities .= " + ";
        }
        $stringActivities = substr($stringActivities, 0, -3);
        $sqlAddMeeting = "UPDATE meetings SET `meetingNotes` = '$meetingNotes', `meetingTopic` = '$meetingTopic', `meetingActivities` = '$stringActivities' WHERE `meetingId` = '$meetingId'";

        // Execute the query
        mysqli_query($link, $sqlAddMeeting);
        header("location: meeting.php?meetingId=$meetingId");
        die();
    }

    


?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
    <script src="https://unpkg.com/react-phone-number-input@3.x/bundle/react-phone-number-input.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/react-phone-number-input@3.x/bundle/style.css"/>


    <title>Edit meeting</title>

    <style>
    .dropdown {
        position: relative;
        display: inline-block;
        width: 250px;
    }

    .dropdown-btn {
        background-color: #f9f9f9;
        border: 1px solid #ccc;
        padding: 10px;
        width: 100%;
        text-align: left;
        cursor: pointer;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #fff;
        min-width: 250px;
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #ccc;
        z-index: 1;
    }

    .dropdown-content label {
        display: block;
        padding: 8px;
        cursor: pointer;
    }

    .dropdown-content input {
        margin-right: 10px;
    }

    .show {
        display: block;
    }
    </style>

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
            position: fixed;
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

        input[name = "meeting-title"] {
            width: 40%;
        }

        input[name = "highSchool"] {
            width: 60%;
        }

        input[name = "phoneNumber"] {
            width: 40%;
        }

        input[name = "email"] {
            width: 50%;
        }

        .invalidPhoneNumber {
            color: red;
        }

        .validPhoneNumber {
            color: green;
        }

        input, select {
            border-radius: 10px; /* Adjust the value to control the roundness */
            padding: 8px 12px; /* Adjust padding as needed */
            border: 1px solid #ccc; /* Add a border for visual distinction */
        }

        .formfield {
            display: flex;
            align-items: center;
        }

    </style>
  </head>


  <?php include("navbar.php"); ?>

  <div id = "content">
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>

    <h1 style = "color: rgba(79, 35, 95, .9);"> Edit meeting on <?php echo $meetingDate; ?> with <?php echo $studentName;?> </h1>

    <br>
    <br>

    <!-- <?php 
        $appStatus =  trim($appStatus);
        $statusArray[0] = trim($statusArray[0]);
        
        echo $appStatus;
        echo $statusArray[1];
    ?> -->

    <?php
        $topics = ["Exams & Academic Planning", "Vocational Planning", "Summer Camps", "Essay & Creative Writing", "CV", "Passion Projects", "Activities", "Strategy", "Interview", "Parent & Kid", "Parent", "Financial Aid and Scholarships", "Internships", "University Application"];
        $nTopics = 14;
        
        for ($i = 0; $i < $nTopics; $i = $i + 1) 
            $topics[$i] = trim($topics[$i]);

        $activities = ["Descriere Proces", "Q&A", "Goals and intro", "Workshop 20 de lucruri", "MiniQuiz English", "Workshop Vise", "Eseu 1 Million", "Eseu longest line", "Consultant Prezinta Planingul & Proces", "Prezentare Internships", "Prezentare Voluntariate", "Discutie summercamps", "Prezentare propuneri activitati", "Intro CV Building", "First Draft CV", "Workshop tree", "Workshop essays 1", "Workshop essays 2", "Apply to internships & Shadowing", "Apply to volunteer Activites", "Present Summercamps", "Logistics", "Essays", "Application", "Ai Quizz", "Workshop passion Projects", "Domain Workshop", "Structure", "Activities", "Planning", "MiniQuiz Math", "Workshop Sale Yourself", "Workshop Pitch", "Interview Prep", "Self-Presentation", "Youni Needs", "Discover Youni Ecosystem", "Present Results", "Feedback & Needs", "Next Goals", "Inform Parent for Financial Aid", "Future Costs", "Establish Budget"];
        $nActivities = 43; 

        for ($i = 0; $i < $nActivities; $i = $i + 1)
            $activities[$i] = trim($activities[$i]);
    ?>

    <form method = "post">

        <!-- <p class = "student-info"> <span class = "title-info"> Meeting's Title: </span> <input type = "text" name = "meeting-title" placeholder = "Meeting's Title" required /> </p> -->
        <!-- <br> -->
        <div class="formfield">
            <label for="textarea" class = "title-info">  Meeting Notes:  </label> &nbsp;
            <textarea name = "meeting-notes" id="textarea" rows="8" cols = "50" required><?php echo $meetingNotes; ?></textarea>
        </div>
        <br>
        <p class = "topic-info"> <span class = "title-info"> Meeting Topic: </span> 
            <select id="topic" name="topic" required>
                <option value="" disabled selected hidden>Select Topic</option>
                <?php 

                    for ($i = 0; $i < $nTopics; $i++) {
                        $topics[$i] = trim($topics[$i]); // elimin spatiile din statusArray[i]
                        ?>
                        <option value="<?php echo $topics[$i];?> "><?php echo $topics[$i];?></option> <?php

                    }
                ?>
            </select>
         </p>

        <span class="title-info">Meeting Activities:</span> 

         <div class="dropdown">
            <button type="button" class="dropdown-btn" id="dropdown-btn">Select Activities</button>
            <div class="dropdown-content" id="dropdown-content">
                <?php 
                    for ($i = 0; $i < $nActivities; $i++) {
                        $activities[$i] = trim($activities[$i]); // Elimină spațiile din activități
                ?>
                    <label>
                        <input type="checkbox" name="activities[]" value="<?php echo $activities[$i]; ?>"> 
                        <?php echo $activities[$i]; ?>
                    </label>
                <?php
                    }
                ?>
            </div>
        </div>



        <br>
        <input class="btn btn-primary" type="submit" name = "submit" value="Update meeting">
    </form>



    <br>
    <br>


    <script>
    document.getElementById("dropdown-btn").addEventListener("click", function(event) {
        let dropdownContent = document.getElementById("dropdown-content");
        dropdownContent.classList.toggle("show");
        event.stopPropagation(); // Prevents immediate closing
    });

    document.addEventListener("click", function(event) {
        let dropdownContent = document.getElementById("dropdown-content");
        let dropdownBtn = document.getElementById("dropdown-btn");

        // Close dropdown if clicking outside (but NOT when clicking inside checkboxes)
        if (!dropdownBtn.contains(event.target) && !dropdownContent.contains(event.target)) {
            dropdownContent.classList.remove("show");
        }
    });

    // Prevent dropdown from closing when clicking checkboxes
    document.getElementById("dropdown-content").addEventListener("click", function(event) {
        event.stopPropagation();
    });
</script>


    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>