<?php
    session_start();
    require_once "configDatabase.php";

    if (!isset($_SESSION['type'])) {
        header("location: index.php");
        die();
    }
    else {
        $typeAccount = $_SESSION["type"];
        $userId = $_SESSION["id"];
    }

    if (isset($_GET['universityId'])) // testez daca e setata o universitate
        $universityId = $_GET['universityId'];
    else {
        header("location: index.php");
        die();
    }

    $sqlUniversityData = "SELECT * FROM universities WHERE `universityId` = " . $universityId;
    
    $queryUniversityData = mysqli_query($link, $sqlUniversityData);

    if (mysqli_num_rows($queryUniversityData) > 0) // testez daca exista vreo univ cu id-ul dat
        $dataUniversity = mysqli_fetch_assoc($queryUniversityData);
    else {
        header("location: index.php");
        die();
    }

?>

<?php // GET DATA
    $consultants = $_GET['consultant'];

    $consultantString = "(";
    $firstElem = 0;

    foreach ($consultants as $consultant) {
        if ($firstElem > 0)
            $consultantString .= ',';
        $consultantString .= $consultant;
        $freqConsultant[$consultant] = 1;

        $firstElem += 1;
    }
    $consultantString .= ')';
    // echo $consultantString;

    if ($firstElem == 0) {
        $consultantString = "(";

        $sqlConsultants = "SELECT userId FROM users WHERE type = 0";
        $resultConsultants = mysqli_query($link, $sqlConsultants);

        $firstElem = 0;
        while ($row = mysqli_fetch_assoc($resultConsultants)) {
            if ($firstElem > 0)
                $consultantString .= ",";
            $consultantString .= $row['userId'];
            // $freqConsultant[$row['userId']] = 1;

            $firstElem += 1;
        }
        $consultantString .= ")";
    }
    //echo $consultantString;
    // eu/us checkbox
    $packageType = $_GET['package'];

    $packageString = "('";
    $firstElem = 0;

    foreach ($packageType as $package) {
        if ($firstElem > 0)
            $packageString .= "','";
        $packageString .= $package;
        $freqPackage[$package] = 1;

        $firstElem += 1;
    }
    $packageString .= "')";

    if ($firstElem == 0) {
        // $freqPackage['EU'] = 1;
        // $freqPackage['US'] = 1;
        $packageString = "('EU', 'US')";
    }

    // echo $packageString;
    // grade checkbox 
    $gradeType = $_GET['grade'];

    $gradeString = "(";
    $firstElem = 0;

    foreach ($gradeType as $grade) {
        if ($firstElem > 0)
            $gradeString .= ',';
        $gradeString .= $grade;
        $freqGrade[$grade] = 1;

        $firstElem += 1;
    }
    $gradeString .= ')';

    if ($firstElem == 0) {
        $gradeString = "(8, 9, 10, 11, 12)";
        // $freq[8] = 1;
        // $freq[9] = 1;
        // $freq[10] = 1;
        // $freq[11] = 1;
        // $freq[12] = 1;

    }
    // echo $gradeString;
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
    <title>Select student for <?php echo $dataUniversity['universityName']; ?> </title>

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
        .full-name {
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
        <h1 style = "float: left;">  Select student for <span style = "color: #c61b75"> <?php echo $dataUniversity['universityName']; ?> </span> </h1>

        <input type="text" id="search-bar" onkeyup="searchFunction()" placeholder="Search for students.." title="Type in a name">
        <ol id = "students-list" class="list-group list-group-numbered">
            <?php
            
            if ($typeAccount == 1)
                $sqlStudent = "SELECT * FROM studentData WHERE 1";
            else
                $sqlStudent = "SELECT * FROM studentData WHERE `consultantId` = '$userId'";

            $queryStudent = mysqli_query($link, $sqlStudent);

            $noStudents = mysqli_num_rows($queryStudent);
            ?> <p style = "font-weight: bold;"> There are <?php echo $noStudents; ?> students in your search </p> <?php
            while ($row = mysqli_fetch_assoc($queryStudent)) {
              ?>
                <div class = "student">
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                        <div class="full-name"><?php echo $row['name']; ?></div>
                        <p class = "highSchool"> <?php echo $row['highSchool'];?> </p>
                        <p class = "consultant"> <?php echo $row['consultantName'];?> </p>
                        <p class = "grade"> Grade: <?php echo $row['grade'];?> </p>
                        <p class = "package"> Package type: <?php echo $row['packageType'];?> </p>

                        
                        </div>
                        <?php $urlStudent = "student.php?studentId=";
                               $urlStudent .= $row['studentId'];
                        ?>
                        <a href = "addApplication.php?universityId=<?php echo $universityId;?>&studentId=<?php echo $row['studentId'];?>" > <button type="button" class="btn btn-primary">Select Student</button> </a>
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
            var input, filter, ul, li, a, i, txtValue;
            input = document.getElementById("search-bar");
            filter = input.value.toUpperCase();
            list = document.getElementById("students-list");
            students = list.getElementsByClassName("student");
            for (i = 0; i < students.length; i++) {
                name1 = students[i].getElementsByClassName("full-name")[0].innerHTML;
                name2 = students[i].getElementsByClassName("highSchool")[0].innerHTML;
                name3 = students[i].getElementsByClassName("consultant")[0].innerHTML;

                name = name1 + name2 + name3;
                if (name.toUpperCase().indexOf(filter) > -1) {
                    students[i].style.display = "";
                } else {
                    students[i].style.display = "none";
                }
            }
        }
    </script>
</body>
</html>