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

    $typeAccount = $_SESSION["type"];
    $name = "";
    $country = "";
    $commission = "";

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST['name'];
        $country = $_POST['country'];
        if (isset($_POST['commission']))
            $commission = $_POST['commission'];

        $sqlCheckUniversity = "SELECT * FROM universities WHERE `universityName` = '$name' AND `institutionType` = 0";
        $restultCheckUniversity = mysqli_query($link, $sqlCheckUniversity);

        if (mysqli_num_rows($restultCheckUniversity) > 0) {
            $row = mysqli_fetch_assoc($restultCheckUniversity);

            $universityId = $row['universityId'];
            $universityLink = $base_url . "university.php?universityId=" . $universityId;

            $errorName = "This university already exists!";
        }
        else {
            $sql = "INSERT INTO universities (`universityName`, `universityCountry`, `commission`, `institutionType`) VALUES ('$name', '$country', '$commission', '0')";
            mysqli_query($link, $sql);  
            header("location: universitiesList.php");
            die();
        }
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


    <title> Add University</title>

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

        input, select {
            border-radius: 10px; /* Adjust the value to control the roundness */
            padding: 8px 12px; /* Adjust padding as needed */
            border: 1px solid #ccc; /* Add a border for visual distinction */
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

    <h1 style = "color: rgba(79, 35, 95, .9);"> Add University </h1>
    <br>
    <br>
    <form method = "post" onsubmit = "return validateForm()">
        <p class = "student-info"> <span class = "title-info"> Univeristy Name: </span> <input type = "text" value = "<?php echo $name; ?>" name = "name" placeholder = "University name" required /> </p>
        <?php
            if (isset($errorName)) {
            ?> <span style = "color: red;"> <?php echo $errorName; ?> You can edit it at this link: <a href = "<?php echo $universityLink; ?>"> University Details </a> </span>
               <br>
            <?php
            }
        ?>

        <br>
        <p class = "student-info"> <span class = "title-info"> Univeristy Country: </span> <input type = "text" name = "country" placeholder = "University country" required /> </p>
        <br>
        <p class = "student-info"> <span class = "title-info"> University Commission:  </span> <input type = "number" name = "commission" placeholder = "University commission(not required)" /> </p>
         <br>
         <br>
        <input class="btn btn-primary" type="submit" name = "submit" value="Add university">
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