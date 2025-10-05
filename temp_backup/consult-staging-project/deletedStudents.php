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

    if ($typeAccount != 1) {
        header("location:index.php");
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
    <title>Deleted Students List </title>

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
        <h1 style = "float: left;"> Deleted Students List</h1>

        <input type="text" id="search-bar" onkeyup="searchFunction()" placeholder="Search for students.." title="Type in a name">
        <ol id = "students-list" class="list-group list-group-numbered">
            <?php
            
            $sqlStudent = "SELECT * FROM studentData WHERE `activityStatus` = 2";
            $queryStudent = mysqli_query($link, $sqlStudent);

            $noStudents = mysqli_num_rows($queryStudent);

            if (!isset($noStudents))
                $noStudents = 0;

            ?> <p style = "font-weight: bold;"> There are <span class = "search-count"><?php echo $noStudents; ?></span> students in your search </p> <?php
            while ($row = mysqli_fetch_assoc($queryStudent)) {
              ?>
                <div class = "student">
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                        <div class="full-name"><?php echo $row['name']; ?></div>
                        <br>
                        <p class = "highSchool"> <span style = "font-weight: bold"> High School: </span> <?php echo $row['highSchool'];?> </p>
                        <p class = "email"> <span style = "font-weight: bold"> Email: </span> <?php echo $row['email'];?> </p>
                        <p class = "consultant"> <span style = "font-weight: bold"> Consultant: </span> <?php echo $row['consultantName'];?> </p>
                        <?php
                        if ($row['grade'] <= 12) {
                            ?> <p class = "grade"> <span style = "font-weight: bold"> Grade: </span> <?php echo $row['grade'];?> </p> <?php
                        }
                        else {
                            ?> <p class = "grade"><span style = "font-weight: bold"> Grade: </span> Bachelor</p> <?php
                        } ?>

                        <?php
                        if ($row['grade'] <= 12) {
                            ?> <p class = "grade"> <span style = "font-weight: bold"> Start Grade: </span> <?php echo $row['signGrade'];?> </p> <?php
                        }
                        else {
                            ?> <p class = "grade"><span style = "font-weight: bold"> Start Grade: </span> Bachelor</p> <?php
                        } ?>

                        <p class = "package"> <span style = "font-weight: bold"> Package type: </span>  <?php echo $row['packageType'];?> </p>

                        
                        </div>
                        <?php $urlStudent = "student.php?studentId=";
                               $urlStudent .= $row['studentId'];
                        ?>
                        <a href = <?php echo $urlStudent;?> > <button type="button" class="btn btn-primary">View details</button> </a>
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
            countDisplay = 0;
            for (i = 0; i < students.length; i++) {
                name1 = students[i].getElementsByClassName("full-name")[0].innerHTML;
                name2 = students[i].getElementsByClassName("highSchool")[0].innerHTML;
                name3 = students[i].getElementsByClassName("consultant")[0].innerHTML;

                name = name1 + name2 + name3;
                if (name.toUpperCase().indexOf(filter) > -1) {
                    students[i].style.display = "";
                    countDisplay++;
                } else {
                    students[i].style.display = "none";
                }
            }
            document.getElementsByClassName("search-count")[0].innerHTML = countDisplay;
        }

        function submitForm() {
            document.getElementById('filters-form').submit();
        }
    </script>
</body>
</html>