<?php
    session_start();

    require_once "configDatabase.php";

    if (!isset($_SESSION["type"])) {
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


    function sendStudentRemovalNotification($to, $oldConsultantName, $studentName) {
        $apiKey = 're_6XaDD7dc_2ZLrH3sHnrQhdnFnzPJsdiG9';
        $url = 'https://api.resend.com/emails';

        $subject = "Notificare: Student Eliminat - Youni Choice";
        
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>$subject</title>
        </head>
        <body>
            <p>Bună, <strong>$oldConsultantName</strong>,</p>
            <p>Acesta este un mesaj pentru a vă informa că studentul <strong>$studentName</strong> a fost eliminat din lista dumneavoastră de studenți.</p>
            <p>Dacă aveți întrebări sau aveți nevoie de clarificări, vă rugăm să ne contactați.</p>
            <p>Vă mulțumim pentru implicarea dumneavoastră și sprijinul oferit studenților!</p>
            <br>
            <p><strong>Echipa Youni Choice</strong></p>
        </body>
        </html>
        ";

        $data = [
            'from' => 'Youni Choice <office@younichoice.com>',
            'to' => [$to],
            'subject' => $subject,
            'html' => $message,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    function sendConsultantAssignmentEmail($to, $consultantName, $studentName, $studentEmail, $studentPhone, $studentGrade) {
        $apiKey = 're_6XaDD7dc_2ZLrH3sHnrQhdnFnzPJsdiG9';
        $url = 'https://api.resend.com/emails';

        $subject = "Atribuire Student - Youni Choice";
        
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>$subject</title>
        </head>
        <body>
            <p>Bună, <strong>$consultantName</strong>,</p>
            <p>Acesta este un mesaj pentru a vă informa că ați fost desemnat consultant pentru un nou student.</p>
            <p><strong>Detalii student:</strong></p>
            <ul>
                <li><strong>Nume:</strong> $studentName</li>
                <li><strong>Email:</strong> $studentEmail</li>
                <li><strong>Telefon:</strong> $studentPhone</li>
                <li><strong>Clasă:</strong> $studentGrade</li>
            </ul>
            <p>Vă rugăm să luați legătura cu studentul cât mai curând posibil pentru a începe procesul de consultanță.</p>
            <p>Dacă aveți întrebări sau aveți nevoie de suport, nu ezitați să ne contactați.</p>
            <p>Vă mulțumim pentru implicarea dumneavoastră!</p>
            <br>
            <p><strong>Echipa Youni Choice</strong></p>
        </body>
        </html>
        ";

        $data = [
            'from' => 'Youni Choice <office@younichoice.com>',
            'to' => [$to],
            'subject' => $subject,
            'html' => $message,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }



    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $emailParent = $_POST['emailParent'];
        $phoneNumber = $_POST['phoneNumber'];
        $highSchool = $_POST['highSchool'];
        $grade = $_POST['grade'];
        $package = $_POST['package'];
        $consultantId = $_POST['consultant'];
        $driveLink = $_POST['driveLink'];
        $packageDetails = $_POST['packageDetails'];
        $activityStatus = $_POST['activityStatus'];

        $sqlConsultantName = "SELECT * FROM users WHERE userId =".$consultantId;
        $queryConsultantName = mysqli_query($link, $sqlConsultantName);
        $rowConsultantName = mysqli_fetch_assoc($queryConsultantName);

        $consultantName = $rowConsultantName['fullName'];
        $consultantEmail = $rowConsultantName['email'];
        
        $sqlOldConsultantId = "SELECT * FROM studentData WHERE `studentId` = '$studentId'";
        $queryOldConsultantId = mysqli_query($link, $sqlOldConsultantId);
        $rowOldConsultantId = mysqli_fetch_assoc($queryOldConsultantId);

        $oldConsultantId = $rowOldConsultantId['consultantId'];
        
        $oldSqlConsultantName = "SELECT * FROM users WHERE userId =".$oldConsultantId;
        $queryOldConsultantName = mysqli_query($link, $oldSqlConsultantName);
        $rowOldConsultantName = mysqli_fetch_assoc($queryOldConsultantName);
        $oldConsultantEmail = $rowOldConsultantName['email'];
        $oldConsultantName = $rowOldConsultantName['fullName'];

        $sqlCheckEmail = "SELECT * FROM studentData WHERE `email` = '$email' AND `studentId` != '$studentId'";
        $resultChechEmail = mysqli_query($link, $sqlCheckEmail);

        if (mysqli_num_rows($resultChechEmail) > 0) {
            $errorMail = "The given email adress already exists!";
        }
        else {
            if ($oldConsultantEmail != $consultantEmail) {
                sendConsultantAssignmentEmail($consultantEmail, $consultantName, $name, $email, $phoneNumber, $grade);
                sendStudentRemovalNotification($oldConsultantEmail, $oldConsultantName, $name);
            }
            if ($_SESSION['type'] == 1)
                $sqlUpdateInformation = "UPDATE `studentData` SET `consultantId`='$consultantId',`consultantName`='$consultantName',`name`='$name',`email`='$email',`emailParent`='$emailParent',`phoneNumber`='$phoneNumber',`highSchool`='$highSchool',`grade`='$grade', `driveLink`='$driveLink', `packageDetails`='$packageDetails', `packageType`='$package', `activityStatus` = '$activityStatus' WHERE `studentId`=".$studentId;
            else 
                $sqlUpdateInformation = "UPDATE `studentData` SET `consultantId`='$consultantId',`consultantName`='$consultantName',`name`='$name',`email`='$email',`emailParent`='$emailParent',`phoneNumber`='$phoneNumber',`highSchool`='$highSchool',`grade`='$grade', `driveLink`='$driveLink', `activityStatus` = '$activityStatus' WHERE `studentId`=".$studentId;
            mysqli_query($link, $sqlUpdateInformation);
            // echo $name, $email, $phoneNumber, $highSchool, $grade, $package;
            header("location: student.php?studentId=".$studentId);
        }
    }
    
    if ($typeAccount == 1) {
        $sql = "SELECT userId, fullName FROM users WHERE type = 0";
        $result = mysqli_query($link, $sql);
        $nConsultant = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $vIdConsultant[$nConsultant] = $row['userId'];
            $vNameConsultant[$nConsultant] = $row['fullName'];
            $nConsultant++;
        }
    }
    else if ($typeAccount == 0) {
        $vIdConsultant[0] = $_SESSION['id'];
        $vNameConsultant[0] = $_SESSION['fullName'];
        $nConsultant++;
    }
?>

<?php 
    // get student data

    $sqlStudent = "SELECT * FROM studentData WHERE `studentId` = ".$studentId;
    $queryStudent = mysqli_query($link, $sqlStudent);

    $studentData = mysqli_fetch_assoc($queryStudent);

    if ($typeAccount != 1 && $studentData["consultantId"] != $userId) {

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


    <title>Edit Student</title>

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

        input[name = "driveLink"] {
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

    <h1 style = "color: rgba(79, 35, 95, .9);"> Edit Student <?php echo $studentData["name"]; ?> </h1>
    <br>
    <br>
    <form method = "post" onsubmit = "return validateForm()">
        <p class = "student-info"> <span class = "title-info"> Student Name: </span> <input value = "<?php echo $studentData['name'];?>" type = "text" name = "name" placeholder = "Student's full name" required /> </p>
        <br>
        <p class = "student-info"> <span class = "title-info"> Student's Email: </span> <input value = "<?php echo $studentData['email']; ?>" type = "email" name = "email" placeholder = "Student's email" required /> </p>
        <br>
        <p class = "student-info"> <span class = "title-info"> Parent's Email: </span> <input value = "<?php echo $studentData['emailParent']; ?>" type = "email" name = "emailParent" placeholder = "Parent's email" /> </p>
        <?php
        if (isset($errorMail)) {
            ?> <span style = "color: red;"> <?php echo $errorMail; ?> </span> <br> <?php
        }?>
        <br>
        <p class = "student-info"> <span class = "title-info"> HighSchool: </span> <input value = "<?php echo $studentData['highSchool']; ?>" type = "text" name = "highSchool" placeholder = "Student's HighSchool" required /> </p>
        <br>
        <p class = "student-info"> <span class = "title-info"> Drive Link: </span> <input value = "<?php echo $studentData['driveLink'];?>" type = "text" name = "driveLink" placeholder = "Student's Drive Link" required /> </p>
        <br>
        <p class = "student-info"> <span class = "title-info"> Phone number: </span> 
            <input value = "<?php echo $studentData['phoneNumber']; ?>" name = "phoneNumber" type="tel" id="phoneInput" placeholder = "Enter your phone number" pattern="^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$" required>
            <div id="statusPhoneNumber" class = "validPhoneNumber">Valid phone number</div>
        </p>
        <br>
        <p class = "student-info"> <span class = "title-info"> Grade: </span> 
            <select id="grade" name="grade" required>
                <?php if ($studentData['grade'] == 9) { ?>
                    <option value="9" selected>9th Grade</option>
                <?php } else { ?>
                    <option value="9">9th Grade</option>
                <?php } ?>

                <?php if ($studentData['grade'] == 10) { ?>
                    <option value="10" selected>10th Grade</option>
                <?php } else { ?>
                    <option value="10">10th Grade</option>
                <?php } ?>

                <?php if ($studentData['grade'] == 11) { ?>
                    <option value="11" selected>11th Grade</option>
                <?php } else { ?>
                    <option value="11">11th Grade</option>
                <?php } ?>

                <?php if ($studentData['grade'] == 12) { ?>
                    <option value="12" selected>12th Grade</option>
                <?php } else { ?>
                    <option value="12">12th Grade</option>
                <?php } ?>

                <?php if ($studentData['grade'] == 13) { ?>
                    <option value="13" selected> Bachelor </option>
                <?php } else { ?>
                    <option value="13"> Bachelor </option>
                <?php } ?>
            </select>
         </p>
         <br>
         <!--UPDATE ALSO PACKAGE FEATURE IN DEV-->
         <?php if ($_SESSION['type'] == 1) { ?> 
         <p class = "student-info"> <span class = "title-info"> Package type: </span> 
            <select id="package" name="package" required>
                <?php if ($studentData['packageType'] == 'US') { ?>
                    <option value="US" selected>US</option>
                <?php } else { ?> 
                    <option value="US">US</option>
                <?php } ?>

                <?php if ($studentData['packageType'] == 'USP') { ?>
                    <option value="USP" selected>US Premium</option>
                <?php } else { ?> 
                    <option value="USP">US Premium</option>
                <?php } ?>

                <?php if ($studentData['packageType'] == 'USAP') { ?>
                    <option value="USAP" selected>US Advanced Package</option>
                <?php } else { ?> 
                    <option value="USAP">US Advanced Package</option>
                <?php } ?>

                <?php if ($studentData['packageType'] == 'EU') { ?>
                    <option value="EU" selected>Europe</option>
                <?php } else { ?>
                    <option value="EU">Europe</option>
                <?php } ?>

                <?php if ($studentData['packageType'] == 'EUP') { ?>
                    <option value="EUP" selected>Europe Premium</option>
                <?php } else { ?>
                    <option value="EUP">Europe Premium</option>
                <?php } ?>
            </select>
         </p>
         <br>

         <p> 
            <span class = "title-info"> Package Details: </span> 
            <p id="charCount">0 / 1000</p> 

            <textarea id="textBox" name="packageDetails" maxlength="1000" rows="10" cols="50" placeholder="Enter package details (max 1000 characters)..." oninput="updateCharCount()"><?php echo $studentData['packageDetails']; ?></textarea>
         </p>
         <?php } ?>
         <p class = "student-info"> <span class = "title-info"> Consultant: </span> 
            <select name="consultant" required>
                <?php if ($typeAccount == 1) { ?>
                    <option value="" selected>Select consultant</option>
                <?php } ?>
                <?php
                    for ($i = 0; $i < $nConsultant; $i++) {
                        if ($vIdConsultant[$i] == $studentData['consultantId']) {?>
                            <option value="<?php echo $vIdConsultant[$i]; ?>" selected> <?php echo $vNameConsultant[$i]; ?> </option> <?php
                        }
                        else { ?>
                            <option value="<?php echo $vIdConsultant[$i]; ?>"> <?php echo $vNameConsultant[$i]; ?> </option>
                        <?php }
                    }
                ?>
            </select>
         </p>

         <br>

         <p class = "student-info"> <span class = "title-info"> Status: </span> 
            <?php
                $stringActive = "";
                $stringGraduated = "";
                $stringDeleted = "";

                if ($studentData["activityStatus"] == 0)
                    $stringActive = "selected";
                else if ($studentData["activityStatus"] == 1)
                    $stringGraduated = "selected";
                else
                    $stringDeleted = "selected";
            ?>
            <select id="status" name="activityStatus" required>
                <option value = "0" <?php echo $stringActive; ?>> Active </option>
                <option value = "1" <?php echo $stringGraduated; ?>> Graduated </option>
                <option value = "2" <?php echo $stringDeleted; ?>> Deleted </option>
            </select>
         </p>
         
         <br>
         <br>
        <input class="btn btn-primary" type="submit" name = "submit" value="Edit Information">
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
        <script>
    function updateCharCount() {
        let textBox = document.getElementById("textBox");
        let charCount = document.getElementById("charCount");
        charCount.textContent = textBox.value.length + " / 1000";
    }
    </script>
</body>
</html>