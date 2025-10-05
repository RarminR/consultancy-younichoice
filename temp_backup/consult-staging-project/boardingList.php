<?php
    session_start();

    if (!isset($_SESSION['type'])) {
        header("location: index.php");
        die();
    }
    else {
        $typeAccount = $_SESSION["type"];
        $userId = $_SESSION["id"];
    }

    $button1Color = $button2Color = $button3Color = "#4f235f";
    $button1HoverColor = $button2HoverColor = $button3HoverColor = "#cb1b80";

    if (!isset($_GET["university-filter"]) || $_GET["university-filter"] == 0)
        $button1Color = "#cb1b80";
    else if ($_GET["university-filter"] == 1)
        $button2Color = "#cb1b80";
    else if ($_GET["university-filter"] == 2)
        $button3Color = "#cb1b80";
    ?>

<?php 
    require_once "configDatabase.php";
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
    <title>Boarding Schools list </title>

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
        #search-bar-name {
            background-image: url('/css/searchicon.png');
            background-position: 10px 12px;
            background-repeat: no-repeat;
            width: 100%;
            font-size: 16px;
            padding: 12px 20px 12px 40px;
            border: 1px solid #ddd;
            margin-bottom: 12px;
        }
        #search-bar-country {
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

        .filter-buttons {
            float: left;
        }

        .button1 {
            background-color: <?php echo $button1Color; ?>;
            border-color: <?php echo $button1Color; ?>;
        }
        .button1:hover{
            background-color: <?php echo $button1HoverColor; ?>;
            border-color: <?php echo $button1HoverColor; ?>;
        }

        .button2 {
            background-color: <?php echo $button2Color; ?>;
            border-color: <?php echo $button2Color; ?>;
        }
        .button2:hover{
            background-color: <?php echo $button2HoverColor; ?>;
            border-color: <?php echo $button2HoverColor; ?>;
        }

        .button3 {
            background-color: <?php echo $button3Color; ?>;
            border-color: <?php echo $button3Color; ?>;

        }
        .button3:hover{
            background-color: <?php echo $button3HoverColor; ?>;
            border-color: <?php echo $button3HoverColor; ?>;

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
        <div class = "filter-buttons">
            <a href = "<?php echo $base_url; ?>boardingList.php?university-filter=0" > <button class = "btn btn-primary button1"> All Programs </button> </a>
            <a href = "<?php echo $base_url; ?>boardingList.php?university-filter=1" > <button class = "btn btn-primary button2" style = "background-color: "> Commissionable Programs </button> </a>
            <a href = "<?php echo $base_url; ?>boardingList.php?university-filter=2" > <button class = "btn btn-primary button3" style = "background-color: "> Non-Comissionable Programs </button> </a>
        </div>
        <br>
        <br>
        <h1 style = "float: left;"> Boarding Schools List</h1>

        <br>
        <br>


        <input type="text" id="search-bar-name" onkeyup="searchFunction()" placeholder="Search for university's name.." title="Type in a name">
        <br>
        <input type="text" id="search-bar-country" onkeyup="searchFunction()" placeholder="Search for university's country.." title="Type in a name">

        <ol id = "universities-list" class="list-group list-group-numbered">
            <?php

            if (!isset($_GET["university-filter"]) || $_GET["university-filter"] == 0)
                $sqlUniversities = "SELECT * FROM universities WHERE `institutionType` = 2";
            else if ($_GET["university-filter"] == 1)
                $sqlUniversities = "SELECT * FROM universities WHERE `commission` != 0 AND `institutionType` = 2";
            else if ($_GET["university-filter"] == 2)
                $sqlUniversities = "SELECT * FROM universities WHERE `commission` = 0 AND `institutionType` = 2";


            $queryUniversities = mysqli_query($link, $sqlUniversities);


            $noUniversities = mysqli_num_rows($queryUniversities);
            ?> <p style = "font-weight: bold;"> There are <span class = "search-count"> <?php echo $noUniversities; ?></span> summer programs in your search </p> <?php
            while ($university = mysqli_fetch_assoc($queryUniversities)) {
              ?>
                <div class = "university">
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                        <div class="university-name"><?php echo $university['universityName']; ?></div>
                        <p class = "university-country"> <?php echo $university['universityCountry'];?> </p>
                        <p class = "university-commission"> Commission: <?php echo $university['commission'];?> </p>


                        
                        </div>
                        <?php $urlUniversity = "university.php?universityId=" . $university['universityId']; ?>

                        <a href = <?php echo $urlUniversity;?> > <button type="button" class="btn btn-primary">See applications</button> </a>
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
            inputName = document.getElementById("search-bar-name");
            inputCountry = document.getElementById("search-bar-country");

            filterName = inputName.value.toUpperCase();
            filterCountry = inputCountry.value.toUpperCase();



            list = document.getElementById("universities-list");
            universities = list.getElementsByClassName("university");
            countDisplay = 0;

            for (i = 0; i < universities.length; i++) {
                name = universities[i].getElementsByClassName("university-name")[0].innerHTML;
                country = universities[i].getElementsByClassName("university-country")[0].innerHTML;

                
                if (name.toUpperCase().indexOf(filterName) > -1 && country.toUpperCase().indexOf(filterCountry) > -1) {
                    universities[i].style.display = "";
                    countDisplay++;
                } else {
                    universities[i].style.display = "none";
                }
            }

            document.getElementsByClassName("search-count")[0].innerHTML = countDisplay;
        }
        
    </script>
</body>
</html>