<?php
    session_start();
    require_once "configDatabase.php";

    if (!isset($_SESSION['type'])) { // testez daca userul este logat
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

    if (isset($_GET['institutionType'])) // testez daca e setat un student
        $institutionType = $_GET['institutionType'];
    else {
        header("location: index.php");
        die();
    }

    if ($institutionType == 0) {
        $institutionName = "University";
        $institutionNamePlural = "Universities";

    }
    else if ($institutionType == 1) {
        $institutionName = "Summer School";
        $institutionNamePlural = "Summer Schools";

    }
    else if ($institutionType == 2) {
        $institutionName = "Boarding School";
        $institutionNamePlural = "Boarding Schools";
    }
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



?>

<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
    <title>Select university for  <?php echo $dataStudent["name"]; ?>  </title>

    <style>
        #contentStudents {
            width: 70%;
            /* float: right; */
            margin: auto;
        }
        #contentFilter {
            width: 10%;
            float: left;
            margin-left: 30px;
            margin-top: 0px;
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
        .university-name {
            font-weight: bold;
        }

        .navbar {
            height: 150px;
        }

        #content {
            display: inline;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            margin-bottom: 5px; /* Adjust margin as needed */
        }
        .checkbox-container input[type="checkbox"] {
            margin-right: 10px; /* Adjust margin as needed */
        }

        .checkboxLabel {
            padding-top: 4.5px;
            font-weight: normal;
        }

        h3 {
            border-bottom: 4px solid black;
            text-align: center;
        }

        h4 {
            border-bottom: 3px solid #ccc;
            text-align: center;
        }

        .filterPackage {
            padding-bottom: 5px;
        }

        .filterConsultants {
            padding-bottom: 5px;
        }

        .page-link {
            padding: 5px;
            text-decoration: none;
            font-size: 16px;
        }

        .pagination {
            display: inline-block;
        }

        .pagination a {
            color: black;
            float: left;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
        }

        .pagination a.active {
            background-color: var(--pink);
            color: white;
            border: 1px solid var(--pink);
        }

        .pagination a:hover:not(.active) {background-color: #ddd;}

        .pagination a:first-child {
            border-top-left-radius: 5px;
            border-bottom-left-radius: 5px;
        }

        .pagination a:last-child {
            border-top-right-radius: 5px;
            border-bottom-right-radius: 5px;
        }

        .pagination a.disabled {
            pointer-events: none;
        }

    </style>
    
  </head>

  
  <?php include("navbar.php"); ?>

  <br>
    <br>
    <br>
    <br>
    <br>
        
  <div id = "content">
    
    <div id = "contentStudents">

        <h1 style = "float: left;"> Select <?php echo $institutionName; ?> for  <span style = "color: #c61b75;"><?php echo $dataStudent["name"]; ?> </h1>

        <input type="text" id="search-bar" onkeyup="searchFunction()" placeholder="Search for <?php echo $institutionNamePlural; ?>.." title="Type in a name">
        <ol id = "universities-list" class="list-group list-group-numbered">
            <?php

            $sqlUniversities = "SELECT * FROM universities WHERE `institutionType` = " . $institutionType;
            $queryUniversities = mysqli_query($link, $sqlUniversities);


            $noUniversities = mysqli_num_rows($queryUniversities);
            ?> <p style = "font-weight: bold;"> There are <?php echo $noUniversities; ?> <?php echo $institutionNamePlural; ?> in your search </p> <?php
            while ($university = mysqli_fetch_assoc($queryUniversities)) {
              ?>
                <div class = "university">
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                        <div class="university-name"><?php echo $university['universityName']; ?></div>
                        <p class = "university-country"> <?php echo $university['universityCountry'];?> </p>

                        
                        </div>

                        <a href = "addApplication.php?universityId=<?php echo $university['universityId'];?>&studentId=<?php echo $dataStudent['studentId'];?>" > <button type="button" class="btn btn-primary">Select</button> </a>
                    </li>
                </div>
              <?php      
            }
            ?>

        </ol>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script>
        function searchFunction() {
            var input, filter, ul, li, a, i, txtValue, countDisplay;
            input = document.getElementById("search-bar");
            filter = input.value.toUpperCase();
            list = document.getElementById("universities-list");
            universities = list.getElementsByClassName("university");
            countDisplay = 0;

            for (i = 0; i < universities.length; i++) {
                name1 = universities[i].getElementsByClassName("university-name")[0].innerHTML;
                name2 = universities[i].getElementsByClassName("university-country")[0].innerHTML;

                name = name1; // am pus doar numele, nu si tara
                if (name.toUpperCase().indexOf(filter) > -1) {
                    universities[i].style.display = "";
                    countDisplay++;
                } else {
                    universities[i].style.display = "none";
                }
            }
        }
    </script>
</body>
</html>