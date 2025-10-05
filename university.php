<?php
    function getStatusColor($status) {
        $status = trim($status);
        $colorStatus = "#FFA500";
        if ($status == "Accepted")
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

    if (isset($_GET['universityId'])) // testez daca e setata o universitate
        $universityId = $_GET['universityId'];
    else {
        header("location: index.php");
        die();
    }

    $sqlUnivesitiesData = "SELECT * FROM universities WHERE `universityId` = '$universityId'";    
    $queryUniversitiesData = mysqli_query($link, $sqlUnivesitiesData);

    if (mysqli_num_rows($queryUniversitiesData) > 0) // testez daca exista universitatea cu id-ul dat
        $dataUniversity = mysqli_fetch_assoc($queryUniversitiesData);
    else {
        header("location: index.php");
        die();
    }


    if ($dataStudent['consultantId'] == $accountId) { // daca userul este consultant
        $consultantId = $dataStudent['consultantId'];
        $sqlApplicationsData = "SELECT * FROM applicationStatus AS a JOIN studentData AS s ON a.studentId = s.studentId WHERE a.universityId = '$universityId' AND s.consultantId = '$consultantId';";
    }
    else
        $sqlApplicationsData = "SELECT * FROM applicationStatus WHERE `universityId` = '$universityId';";


    if ($dataUniversity['institutionType'] == 0)
        $schoolType = "University";
    else if ($dataUniversity['institutionType'] == 1)
        $schoolType = "Summer School";
    else
        $schoolType = "Boarding School";

    $queryApplicationsData = mysqli_query($link, $sqlApplicationsData);


    $nApplications = 0;
    while ($row = mysqli_fetch_assoc($queryApplicationsData)) {
        $studentId = $row["studentId"];
        if ($typeAccount == 1)
            $sqlStudents = "SELECT * FROM studentData WHERE `studentId` = '$studentId'";
        else
            $sqlStudents = "SELECT * FROM studentData WHERE `studentId` = '$studentId' AND `consultantId` = '$accountId'";
        $queryStudents = mysqli_query($link, $sqlStudents);

        if (mysqli_num_rows($queryStudents) > 0) {
            $rowStudent = mysqli_fetch_assoc($queryStudents);

            $arrStudentName[$nApplications] = $rowStudent["name"];
            $arrStudentGrade[$nApplications] = $rowStudent["grade"];
            $arrStudentConsultant[$nApplications] = $rowStudent["consultantName"];
            $arrApplicationsId[$nApplications] = $row["applicationId"];
            $arrAplicationsStatusColor[$nApplications] = getStatusColor($row["appStatus"]);
            $arrAplicationsStatus[$nApplications] = $row["appStatus"];

            $nApplications++;
        }
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

    <title>School <?php echo $dataUniversity["universityName"]; ?></title>

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

    <!-- <p> <?php echo $arrAplicationsStatusColor[0]; ?> </p> -->

    <p class = "student-info"> <span class = "title-info"> School Type: </span> <?php echo $schoolType;  ?> </p>
    <p class = "student-info"> <span class = "title-info"> School Name: </span> <?php echo $dataUniversity['universityName']; ?> </p>
    <p class = "student-info"> <span class = "title-info"> School Country: </span> <?php echo $dataUniversity['universityCountry']; ?> </p>
    <p class = "student-info"> <span class = "title-info"> School Commission: </span> <?php echo $dataUniversity['commission']; ?> </p>



    <br>
    
    <?php
    if ($typeAccount == 1) { ?>
        <a href = <?php echo "editUniversity.php?universityId=".$universityId; ?> > <button class = "btn btn-primary"> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
        <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
        </svg> Edit info </button> </a>
        <!-- <a onclick="confirmRemove('removeUniversity.php?universityId=<?php echo $universityId; ?>')"> <button class = "btn btn-danger"> <i class="fa-solid fa-minus"></i> Remove </button> </a> -->
        <br>
        <br>
    <?php
    } ?>

    

    

    <a href = "addApplicationUniversity.php?universityId=<?php echo $universityId; ?>"> <button class = "btn btn-primary"> <i class="fa-solid fa-plus"></i> Add application </button> </a>

    <br>
    <br>


    <input type="text" id="search-bar" onkeyup="searchFunction()" placeholder="Search for student names.." title="Type in a name">
    <!-- <p class="text-danger">Even if you press the single update button it will update every change made to all of the applications. If you want to return to the initial configuration, just refresh the page. </p>             -->
    <form method = "post">
        <ol class="list-group list-group-numbered" id = "applications-list">

            <?php
            for ($i = 0; $i < $nApplications; $i++) { ?>
                <div class = "application">
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                        <div class="fw-bold university-name"><?php echo $arrStudentName[$i]; ?></div>
                        <p class = "country-university"> <?php echo $arrStudentConsultant[$i]; ?> </p>
                        </div>
                        <span class="badge bg-primary rounded-pill" style = "background-color: <?php echo $arrAplicationsStatusColor[$i]; ?> !important;"> <?php echo $arrAplicationsStatus[$i]; ?></span>
                        <div>
                            <a href = "application.php?applicationId=<?php echo $arrApplicationsId[$i]; ?>"> <button type="button" class="btn btn-primary">View details</button> </a>
                        </div>
                    </li>
                </div>
            <?php
            }
            ?>
        </ol>

        <br>
        <!-- <div style = "text-align: center;">
            <input style = "width: 50%;" class="btn btn-primary" type="submit" value="Update all">
        </div> -->
    </form>

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

                name = name1;
                console.log(name);
                if (name.toUpperCase().indexOf(filter) > -1) {
                    applications[i].style.display = "";
                } else {
                    applications[i].style.display = "none";
                }
            }
        }
    </script>

    <script>
        function confirmRemove(link) {
            const userConfirmed = confirm("Are you sure you want to delete this university?");
            if (userConfirmed) {
                window.location.href = link;
            } else {
                alert("Action canceled.");
            }
        }
    </script>
</body>
</html>