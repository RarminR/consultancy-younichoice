<!doctype html>

<?php
    session_start();
    require_once "configDatabase.php";
    
    // Grade calculation function
    function calculateCurrentGrade($dataStudent) {
        $currentYear = date('Y');
        $currentMonth = date('n'); // 1-12
        $currentDay = date('j'); // 1-31
        
        // Check if student is bachelor (isMaster = 1)
        if (isset($dataStudent['isMaster']) && $dataStudent['isMaster'] == 1) {
            // Bachelor student - show contract end year
            return "Bachelor Student";
        } else {
            // Non-bachelor student - calculate grade based on graduation year
            $graduationYear = $dataStudent['graduationYear'];
            
            // Calculate base grade (12 - years until graduation)
            $yearsUntilGraduation = $graduationYear - $currentYear;
            $baseGrade = 12 - $yearsUntilGraduation;
            
            // Adjust grade based on current date
            if ($currentMonth >= 6 && $currentMonth <= 9) {
                // June 1 - September 15: upcoming year
                if ($currentMonth == 6 || ($currentMonth == 9 && $currentDay <= 15)) {
                    $calculatedGrade = $baseGrade + 1;
                    return "Grade " . $calculatedGrade . " (upcoming year)";
                } else {
                    $calculatedGrade = $baseGrade + 1;
                    return "Grade " . $calculatedGrade;
                }
            } elseif ($currentMonth >= 10 || $currentMonth <= 12) {
                // September 16 - December 31: current year
                $calculatedGrade = $baseGrade + 1;
                return "Grade " . $calculatedGrade;
            } else {
                // January 1 - May 31: current year
                $calculatedGrade = $baseGrade;
                return "Grade " . $calculatedGrade;
            }
        }
    }

    if (!isset($_SESSION['type'])) { /// testez daca userul este logat
        header("location: index.php");
        die();
    }
    else {
        $typeAccount = $_SESSION["type"];
        $accountId = $_SESSION["id"];
    }

    if (isset($_GET["applicationId"])) /// testez daca este setata vreo aplicatie
        $applicationId = $_GET["applicationId"];
    else {
        header("location:index.php");
        die();
    }

    $sqlApplicationData = "SELECT * FROM applicationStatus WHERE `applicationId` = " .$applicationId;
    $queryApplicationData = mysqli_query($link, $sqlApplicationData);

    if (mysqli_num_rows($queryApplicationData) > 0) /// testez daca exista o aplicatie cu id-ul dat
        $applicationData = mysqli_fetch_assoc($queryApplicationData);
    else  {
        header("location: index.php");
        die();
    }

    $studentId = $applicationData['studentId'];
    $universityId = $applicationData['universityId'];
    $scolarship = $applicationData['scholarship'];

    $sqlStudentData = "SELECT * FROM studentData WHERE `studentId` = " .$studentId;
    $queryStudentData = mysqli_query($link, $sqlStudentData);

    if (mysqli_num_rows($queryStudentData) > 0) /// testez daca exista user cu id-ul respectiv (daca nu probabil exista un bug)
        $dataStudent = mysqli_fetch_assoc($queryStudentData);
    else { // nu ar trebui sa intre niciodata aici!!
        header("location: index.php");
        die(); 
    }

    $sqlUniveristyData = "SELECT * FROM universities WHERE `universityId` =" . $universityId;
    $queryUniversityData = mysqli_query($link, $sqlUniveristyData);

    if (mysqli_num_rows($queryUniversityData) > 0)/// testez daca exista universitatea cu id-ul respectiv cu id-ul respectiv (daca nu probabil exista un bug)
        $dataUniversity = mysqli_fetch_assoc($queryUniversityData);
    else { // nu ar trebui sa intre niciodata aici!!
        header("location: index.php");
        die();
    }

    $universityName = $dataUniversity['universityName'];
    $universityCountry = $dataUniversity['universityCountry'];
    $universityCommission = $dataUniversity['commission'];


    if (!($dataStudent['consultantId'] == $accountId || $typeAccount == 1)) { //testez daca userul curent (admin / consultant) are acces la acest elev 
        header("location: index.php");
        die();
    }

    $colorStatus = "black";
    $colorStatusFinancial = "black";

    $status = $applicationData['appStatus'];
    $status = trim($status);
    if ($status == "In progress")
        $colorStatus = "#FFA500";
    else if ($status == "Accepted")
        $colorStatus = "#008000";
    else if ($status == "Rejected")
        $colorStatus = "#FF0000";
    else if ($status == "Waitlisted")
        $colorStatus = "#808080";
?>

<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>Application</title>

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

  
  <?php include("navbar.php"); ?>

  <div id = "content">
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>

    
    <?php
        if (isset($_SESSION["error"])) {
            ?> <p style = "color: red">  <?php echo $_SESSION["error"]; ?> </p> <?php
            unset($_SESSION['error']);
        }
    ?>
    <p class = "student-info"> <span class = "title-info"> Applying Student: </span> <a href = "<?php echo 'student.php?studentId='.$studentId; ?>"><?php echo $dataStudent['name']; ?> </a> </p>
    <p class = "student-info"> <span class = "title-info"> Class: </span> <?php echo calculateCurrentGrade($dataStudent); ?> </p>
    <p class = "student-info"> <span class = "title-info"> HighSchool: </span> <?php echo $dataStudent['highSchool']; ?> </p>
    <p class = "student-info"> <span class = "title-info"> Consultant: </span> <a href = "<?php echo 'consultant.php?consultantId='.$dataStudent['consultantId']; ?>"><?php echo $dataStudent['consultantName']; ?> </a> </p>

    <br>
    <br>

    <p class = "student-info"> <span class = "title-info"> Univeristy name: </span> <a href = "<?php echo 'university.php?universityId='.$universityId; ?>"><?php echo $universityName; ?> </a> </p>
    <p class = "student-info"> <span class = "title-info"> Univeristy country: </span> <?php echo $universityCountry; ?> </p>
    <p class = "student-info"> <span class = "title-info"> Univeristy commission: </span> <?php echo $universityCommission; ?> </p>

    <p class = "student-info"> <span class = "title-info"> Application Status: </span> <span style = "color: <?php echo $colorStatus; ?>"> <?php echo $status; ?> </span> </p>

    <?php
    if ($status == "Accepted") { ?>
        <p class = "student-info"> <span class = "title-info"> Scholarship: </span> <span> <?php echo $applicationData['scholarship'] . "$"; ?> </span> </p>
    <?php
    }
    ?>

    <?php
    // Fetch checklist items for this application
    $sqlChecklist = "SELECT ac.checklistId, ac.isCustom, ac.status, c.checklistName FROM applications_checklist ac LEFT JOIN checklist c ON ac.checklistId = c.checklistId WHERE ac.applicationId = $applicationId";
    $queryChecklist = mysqli_query($link, $sqlChecklist);
    ?>
    <br>
    <div class="card mb-3">
        <div class="card-header" style="background-color: #f0ad4e; color: white; font-weight: bold; font-size: 18px;">Application Checklist</div>
        <div class="card-body">
            <?php if (mysqli_num_rows($queryChecklist) > 0) { ?>
                <ul class="list-group">
                <?php while ($checklist = mysqli_fetch_assoc($queryChecklist)) { ?>
                    <li class="list-group-item">
                        <div style="display: flex; align-items: center; width: 100%;">
                            <span style="flex-shrink: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <strong style="color: <?php echo ((int)$checklist['isCustom'] === 1) ? '#5bc0de' : '#f0ad4e'; ?>"><?php echo ((int)$checklist['isCustom'] === 1) ? 'Extra Checklist Item: ' : 'University Checklist Item: '; ?></strong>
                                <?php echo htmlspecialchars($checklist['checklistName'] ? $checklist['checklistName'] : $checklist['checklistId']); ?>
                            </span>
                            <span class="badge badge-pill" style="background-color: #c61b75; color: white; font-size: 15px; margin-left: auto;">
                                <?php echo htmlspecialchars($checklist['status']); ?>
                            </span>
                        </div>
                    </li>
                <?php } ?>
                </ul>
            <?php } else { ?>
                <p class="mb-0">No checklist items found for this application.</p>
            <?php } ?>
        </div>
    </div>
    <br>

    <a href = <?php echo "editApplication.php?applicationId=".$applicationId; ?> > <button class = "btn btn-primary"> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
  <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
</svg> Edit application status </button> </a>


    <br>
    <br>
    <!-- <a href = "addAplication.php" > <button class = "btn btn-primary"> <i class="fa-solid fa-plus"></i> Add application </button> </a> -->

    <br>
    <br>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script>
        function searchFunction() {
            var input, filter, ul, li, a, i, txtValue;
            input = document.getElementById("search-bar");
            filter = input.value.toUpperCase();
            list = document.getElementById("applications-list");
            applications = list.getElementsByClassName("application");
            for (i = 0; i < applications.length; i++) {
                name1 = applications[i].getElementsByClassName("university-name")[0].innerHTML;
                name2 =  applications[i].getElementsByClassName("country-university")[0].innerHTML;

                name = name1 + name2;
                console.log(name);
                if (name.toUpperCase().indexOf(filter) > -1) {
                    applications[i].style.display = "";
                } else {
                    applications[i].style.display = "none";
                }
            }
        }
    </script>
</body>
</html>