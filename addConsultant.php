<?php
    session_start();

    require_once "configDatabase.php";

    if (!isset($_SESSION["type"])) {
        header("location: index.php");
        die();
    }

    $typeAccount = $_SESSION["type"];
    if ($typeAccount == 0) {
        header("location: signOut.php");
        die();
    }

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $defaultPassword = md5("Youni2024!");

        $password = md5($password);
        $sql = "INSERT INTO users (`fullName`, `email`, `password`) VALUES ('$name', '$email', '$defaultPassword');";
        mysqli_query($link, $sql);  
        
        header("location: index.php");
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


    <title> Add Consultant</title>

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

        input[name = "email"] {
            width: 50%;
        }

        input[name = "highSchool"] {
            width: 60%;
        }

        input[name = "phoneNumber"] {
            width: 40%;
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

    <h1 style = "color: rgba(79, 35, 95, .9);"> Add Consultant </h1>
    <br>
    <br>
    <form method = "post" onsubmit = "return validateForm()">
        <p class = "student-info"> <span class = "title-info"> Consultant Name: </span> <input type = "text" name = "name" placeholder = "Consultant's full name" required /> </p>
        <br>
        <p class = "student-info"> <span class = "title-info"> Consultant's Email: </span> <input type = "email" name = "email" placeholder = "Consultant's email" required /> </p>
        <br>
        <br>
        <input class="btn btn-primary" type="submit" name = "submit" value="Add consultant">
    </form>



    <br>
    <br>


    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script>
        const phoneInput = document.getElementById('phoneInput');
        const phoneNumberDisplay = document.getElementById('phoneNumber');
        const invalidPhoneNumberDisplay = document.getElementById('statusPhoneNumber');

        function validatePhoneNumber(phoneNumber) {
            // Regular expression to match phone numbers with optional "+" or "00" at the beginning
            const phoneRegex = /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/im;

            // Remove any non-numeric characters
            const numericPhoneNumber = phoneNumber.replace(/\D/g, '');

            // Check if the phone number matches the regular expression
            return phoneRegex.test(numericPhoneNumber);
        }

        phoneInput.addEventListener('input', () => {
            const phoneNumber = phoneInput.value;
            
            if (validatePhoneNumber(phoneNumber)) {
                invalidPhoneNumberDisplay.textContent = 'Valid phnoe number';
                invalidPhoneNumberDisplay.classList.remove("invalidPhoneNumber");
                invalidPhoneNumberDisplay.classList.add("validPhoneNumber");

            } else {
                invalidPhoneNumberDisplay.textContent = 'Invalid phone number';
                invalidPhoneNumberDisplay.classList.add("invalidPhoneNumber");
                invalidPhoneNumberDisplay.classList.remove("validPhoneNumber");
            }
        });

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