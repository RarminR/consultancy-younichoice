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
        header("location: index.php");
        die();
    }

    if (isset($_GET['packageId'])) // testez daca e setata o universitate
        $packageId = $_GET['packageId'];
    else {
        header("location: index.php");
        die();
    }

    $sqlPackageData = "SELECT * FROM packages WHERE `packageId` = '$packageId'";    
    $queryPackageData = mysqli_query($link, $sqlPackageData);

    if (mysqli_num_rows($queryPackageData) > 0) // testez daca exista universitatea cu id-ul dat
        $dataPackage = mysqli_fetch_assoc($queryPackageData);
    else {
        header("location: index.php");
        die();
    }

    $name = $dataPackage["packageName"];
    $services = $dataPackage["packageServices"];
    $grade = $dataPackage["grade"];

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST['name'];
        $services = $_POST['services'];

        echo $services;
        echo $name;
        echo $packageId;

        $sqlUpdate = "UPDATE packages SET `packageServices` = '$services' WHERE `packageId` = '$packageId'";
        mysqli_query($link, $sqlUpdate);

        header("location: packagesList.php");
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


    <title> Edit Package <?php echo $name; ?> (Grade <?php echo $grade; ?>)</title>

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

        input[name = "name"] {
            width: 30%;
        }

        input[name = "country"] {
            width: 30%;
        }

        input[name = "commission"] {
            width: 30%;
        }

        input, select, textarea {
            border-radius: 10px; /* Adjust the value to control the roundness */
            padding: 8px 12px; /* Adjust padding as needed */
            border: 1px solid #ccc; /* Add a border for visual distinction */
        }

        textarea {
            font-weight: normal !important;
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

    <h1 style = "color: rgba(79, 35, 95, .9);"> Edit Package's Services</h1>
    <br>
    <br>
    <form method = "post" onsubmit = "return validateForm()">
        <p class = "student-info"> <span class = "title-info"> Package Name: </span> <input type = "text" value = "<?php echo $name; ?>" name = "name" placeholder = "Package name" required disabled /> </p>
        <br>
        <p class = "student-info"> <span class = "title-info"> Grade: </span> <input type = "text" value = "Grade <?php echo $grade; ?>" name = "grade" placeholder = "Grade" required disabled /> </p>
        <br>        
        <p class = "student-info"> <span class = "title-info"> Package Services: </span>
        <br>
        <br>
        <textarea class = "student-info" rows="15" cols="50" name = "services"><?php echo $services; ?></textarea>
        <br>
        <br>

        <input class="btn btn-primary" type="submit" name = "submit" value="Apply changes">
    </form>



    <br>
    <br>


    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

</body>
</html>