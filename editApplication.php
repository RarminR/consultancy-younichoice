<!doctype html>

<?php
    session_start();
    require_once "configDatabase.php";

    if (!isset($_SESSION['type'])) { /// testez daca userul este logat
        header("location: index.php");
        die();
    }
    else {
        $typeAccount = $_SESSION["type"];
        $accountId = $_SESSION["id"];
    }

    if (isset($_GET["applicationId"])) /// testez daca este setata vreo aplicatie anume
        $applicationId = $_GET["applicationId"];
    else {
        header("location: index.php");
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
    $scholarship = $applicationData['scholarship'];
    $appStatus = $applicationData['appStatus'];

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

    if (!($dataStudent['consultantId'] == $accountId || $typeAccount == 1)) { //testez daca userul curent (admin / consultant) are acces la acest elev 
        header("location: index.php");
        die();
    }

    $universityName = $dataUniversity['universityName'];
    $universityCountry = $dataUniversity['universityCountry'];


    $statusArray = ["In progress","Accepted","Rejected","Waitlisted", "Enrolled", "Suggested", "Not Interested Anymore"];
    $nStatusArray = 7;

    $statusArray[0] = trim($statusArray[0]);
    $statusArray[1] = trim($statusArray[1]);
    $statusArray[2] = trim($statusArray[2]);
    $statusArray[3] = trim($statusArray[3]);
    $statusArray[4] = trim($statusArray[4]);
    $statusArray[5] = trim($statusArray[5]);
    $statusArray[6] = trim($statusArray[6]);





    if (!($dataStudent['consultantId'] == $accountId || $typeAccount == 1)) { //testez daca userul curent (admin / consultant) are acces la acest elev 
        header("location: index.php");
        die();
    }
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
          $status = $_POST["status"];
          $scholarship = $_POST["scholarship"];
          echo $status;  # update later status and scholarship in DB 
          echo $scholarship; 

          $sqlUpdate = "UPDATE `applicationStatus` SET `appStatus` = '$status', `scholarship` = '$scholarship' WHERE `applicationId` = '$applicationId'";
          mysqli_query($link, $sqlUpdate);

          $url = $base_url . "application.php?applicationId=" . $applicationId;
          header("location: $url");
          die();
    }
?>

<?php 
    // get student data

    $sqlStudent = "SELECT * FROM studentData WHERE `studentId` = ".$studentId;
    $queryStudent = mysqli_query($link, $sqlStudent);

    $studentData = mysqli_fetch_assoc($queryStudent);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title> Edit application </title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
    <script src="https://unpkg.com/react-phone-number-input@3.x/bundle/react-phone-number-input.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/react-phone-number-input@3.x/bundle/style.css"/>


    <title>Edit Application</title>

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

    <h1 style = "color: rgba(79, 35, 95, .9);"> Edit <?php echo $dataStudent["name"];?>'s application at <?php echo $universityName; ?> (<?php echo $universityCountry; ?>) </h1>
    <br>
    <br>

    <!-- <?php 
        $appStatus =  trim($appStatus);
        $statusArray[0] = trim($statusArray[0]);
        
        echo $appStatus;
        echo $statusArray[1];
    ?> -->

    <form method = "post" onsubmit = "return validateForm()" method = "<?php echo htmlspecialchars($_SERVER[" PHP_SELF "]);?>">
        <p class = "student-info"> <span class = "title-info"> Application Status: </span> 
            <select id="status" name="status" required>
                <option value="" disabled selected hidden>Select status</option>
                <?php 

                    for ($i = 0; $i < $nStatusArray; $i++) {
                        $statusArray[$i] = trim($statusArray[$i]); // elimin spatiile din statusArray[i]
                        if ($appStatus == $statusArray[$i]) {
                            ?> <option selected value="<?php echo $statusArray[$i];?> "><?php echo $statusArray[$i];?></option> <?php
                        }
                        else {
                            ?> <option value="<?php echo $statusArray[$i];?> "><?php echo $statusArray[$i];?></option> <?php
                        }
                    }
                ?>
            </select>
         </p>
         <br>
         <p class = "student-info"> <span class = "title-info"> Scholarship(in $): </span> <input value = "<?php echo $scholarship; ?>" id = "scholarship" type = "number" name = "scholarship" /> </p>
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
    <script>
        const statusInput = document.getElementById('phoneInput');
        const phoneNumberDisplay = document.getElementById('phoneNumber');
        const invalidPhoneNumberDisplay = document.getElementById('statusPhoneNumber');

        function validateForm() {
            var phoneNumberInput = document.getElementById("phoneInput");
            var phoneNumberPattern = /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/;
            
            if (validatePhoneNumber(phoneNumberInput.value)) {
                return true; // prevent form submission
            }

            invalidPhoneNumberDisplay.classList.add("fw-bold");
            return false; // allow form submission
        }
    </script>
</body>
</html>