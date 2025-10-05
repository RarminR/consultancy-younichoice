<?php
    session_start();

    require_once "configDatabase.php";

    if (!isset($_SESSION['type'])) {
        header("location: index.php");
        die();
    }
    else if ($_SESSION['type'] == 1) {
        $typeAccount = $_SESSION["type"];
        $userId = $_SESSION["id"];
    }
    else {
        header("location: index.php");
        die();
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
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Log the response for debugging
        error_log("Consultant Assignment Email Response: " . $response);
        error_log("Consultant Assignment Email HTTP Code: " . $httpCode);
        if ($error) {
            error_log("Consultant Assignment Email cURL Error: " . $error);
        }

        return $response;
    }

    function sendStudentAssignmentEmail($to, $studentName, $consultantName, $consultantEmail, $calendarLink) {
        $apiKey = 're_6XaDD7dc_2ZLrH3sHnrQhdnFnzPJsdiG9';
        $url = 'https://api.resend.com/emails';
    
        $subject = "Consultant Atribuit - Youni Choice";
        
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>$subject</title>
        </head>
        <body>
            <p>Bună, <strong>$studentName</strong>,</p>
            <p>Acesta este un mesaj pentru a vă informa că ați fost asignat unui consultant pentru a vă ghida în procesul academic.</p>
            <p><strong>Detalii consultant:</strong></p>
            <ul>
                <li><strong>Nume:</strong> $consultantName</li>
                <li><strong>Email:</strong> $consultantEmail</li>
            </ul>
            <p>Pentru a programa o întâlnire cu consultantul dumneavoastră, vă rugăm să accesați următorul link:</p>
            <p><a href='$calendarLink'>$calendarLink</a></p>
            <p>Dacă aveți întrebări suplimentare, nu ezitați să ne contactați.</p>
            <p>Vă dorim mult succes!</p>
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
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Log the response for debugging
        error_log("Student Assignment Email Response: " . $response);
        error_log("Student Assignment Email HTTP Code: " . $httpCode);
        if ($error) {
            error_log("Student Assignment Email cURL Error: " . $error);
        }
    
        return $response;
    }

    function sendStudentAccountCreationEmail($to, $studentName, $accountCreationLink) {
        $apiKey = 're_6XaDD7dc_2ZLrH3sHnrQhdnFnzPJsdiG9';
        $url = 'https://api.resend.com/emails';
    
        $subject = "Creați-vă contul - Youni Choice";
        
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>$subject</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4f235f; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background-color: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .btn { display: inline-block; background-color: #4f235f; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .btn:hover { background-color: #3a1a47; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Bun venit la Youni Choice!</h1>
                </div>
                <div class='content'>
                    <p>Bună, <strong>$studentName</strong>,</p>
                    <p>Vă mulțumim că ați ales să lucrați cu echipa noastră pentru viitorul dumneavoastră academic!</p>
                    <p>Pentru a începe să vă folosiți serviciile noastre, vă rugăm să vă creați un cont personalizat în platforma noastră.</p>
                    <p><strong>Contul dumneavoastră vă va permite să:</strong></p>
                    <ul>
                        <li>Accesați materialele și resursele personalizate</li>
                        <li>Comunicați direct cu consultantul dumneavoastră</li>
                        <li>Urmăriți progresul și programările</li>
                        <li>Accesați documentele și fișierele importante</li>
                    </ul>
                    <p style='text-align: center;'>
                        <a href='$accountCreationLink' class='btn'>Creați-vă contul acum</a>
                    </p>
                    <p><strong>Link direct:</strong> <a href='$accountCreationLink'>$accountCreationLink</a></p>
                    <p><em>Notă: Acest link este unic și personal. Nu îl partajați cu alții.</em></p>
                    <p>Dacă aveți întrebări sau întâmpinați probleme, nu ezitați să ne contactați la <strong>office@younichoice.com</strong>.</p>
                    <p>Vă dorim mult succes în călătoria dumneavoastră academică!</p>
                </div>
                <div class='footer'>
                    <p><strong>Echipa Youni Choice</strong></p>
                    <p>office@younichoice.com</p>
                </div>
            </div>
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
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Log the response for debugging
        error_log("Account Creation Email Response: " . $response);
        error_log("Account Creation Email HTTP Code: " . $httpCode);
        if ($error) {
            error_log("Account Creation Email cURL Error: " . $error);
        }
    
        return $response;
    }

    function generateStudentHashLink($email) {
        // Generate a unique hash based on email and current timestamp
        $hash = hash('sha256', $email . time() . uniqid());
        return $hash;
    }
    

    $typeAccount = $_SESSION["type"];

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $parentEmail = $_POST['parentEmail'];

        $highSchool = $_POST['highSchool'];
        $phoneNumber = $_POST['phoneNumber'];
        $isBachelor = $_POST['isBachelor'];
        $graduationYear = $_POST['graduationYear'];
        $packageType = $_POST['package'];
        $consultantId = $_POST['consultant'];
        $driveLink = $_POST['driveLink'];
        $packageDetails = $_POST['packageDetails'];
        
        // Calculate grade based on graduation year and current date
        $currentYear = date('Y');
        $currentMonth = date('n'); // 1-12
        $currentDay = date('j'); // 1-31
        
        if ($isBachelor == '1') {
            // Bachelor student - contract end year is stored in graduationYear
            $calculatedGrade = $graduationYear; // For bachelor, store contract end year
            $isMaster = 1;
            $signGrade = 13;
        } else {
            // Non-bachelor student - calculate grade based on graduation year
            $isMaster = 0;
            
            // Calculate base grade (12 - years until graduation)
            $yearsUntilGraduation = $graduationYear - $currentYear;
            $baseGrade = 12 - $yearsUntilGraduation;
            
            // Adjust grade based on current date
            if ($currentMonth >= 6 && $currentMonth <= 9) {
                // June 1 - September 15: upcoming year
                if ($currentMonth == 6 || ($currentMonth == 9 && $currentDay <= 15)) {
                    $calculatedGrade = $baseGrade + 1;
                } else {
                    $calculatedGrade = $baseGrade + 1;
                }
            } elseif ($currentMonth >= 10 || $currentMonth <= 12) {
                // September 16 - December 31: current year
                $calculatedGrade = $baseGrade + 1;
            } else {
                // January 1 - May 31: current year
                $calculatedGrade = $baseGrade;
            }
            
            // signGrade should be the calculated grade (current or upcoming year grade)
            $signGrade = $calculatedGrade;
        }

        $sqlConsultantName = "SELECT * FROM users WHERE `userId` = '$consultantId'";
        $queryConsultantName = mysqli_query($link, $sqlConsultantName);
        $dataConsultantName = mysqli_fetch_assoc($queryConsultantName);

        $consultantName = $dataConsultantName['fullName'];
        $consultantEmail = $dataConsultantName['email'];
        $calendarLink = $dataConsultantName['calendlyLink'];

        $sqlCheckEmail = "SELECT * FROM studentData WHERE `email` = '$email'";
        $resultChechEmail = mysqli_query($link, $sqlCheckEmail);

        if (mysqli_num_rows($resultChechEmail) > 0) {
            $errorMail = "This email adress already exists!";
        }
        else if (trim($consultantId) == trim($userId) || $typeAccount == 1) {
            // Generate unique hash for student onboarding
            $studentHashLink = generateStudentHashLink($email);
            
            $sql = "INSERT INTO studentData (`name`, `email`, `emailParent`, `highSchool`, `phoneNumber`, `grade`, `signGrade`, `graduationYear`, `isMaster`, `packageType`, `consultantId`, `consultantName`, `packageDetails`, `driveLink`, `studentHashLink`) VALUES ('$name', '$email', '$parentEmail', '$highSchool', '$phoneNumber', '$calculatedGrade', " . ($isBachelor == '1' ? 13 : "'$signGrade'") . ", '$graduationYear', '$isMaster', '$packageType', '$consultantId', '$consultantName', '$packageDetails', '$driveLink', '$studentHashLink')";
            mysqli_query($link, $sql);  
            
            // Send emails
            sendConsultantAssignmentEmail($consultantEmail, $consultantName, $name, $email, $phoneNumber, $grade);
            sendStudentAssignmentEmail($email, $name, $consultantName, $consultantEmail, $calendarLink);
            
            // Generate account creation link and send account creation email
            $accountCreationLink = "https://" . $_SERVER['HTTP_HOST'] . "/student/onboarding?hash=" . $studentHashLink;
            error_log("Account Creation Link: " . $accountCreationLink);
            error_log("Sending account creation email to: " . $email . " for student: " . $name);
            sendStudentAccountCreationEmail($email, $name, $accountCreationLink);
            
            header("location: index.php");
            die();
        }
    }
    else if ($typeAccount == 1) {
        $sql = "SELECT userId, fullName FROM users WHERE type = 0";
        $result = mysqli_query($link, $sql);
        $nConsultant = 0;
        while ($row = mysqli_fetch_assoc($result)) {
            $vIdConsultant[$nConsultant] = $row['userId'];
            $vNameConsultant[$nConsultant] = $row['fullName'];
            $nConsultant++;
        }

        $sqlPackages = "SELECT * FROM packages";
        $resultPackages = mysqli_query($link, $sqlPackages);
        
        $packageShortToLong = [
            "EU" => "Europe",
            "EUP" => "Europe Premium",
            "US" => "USA",
            "USP" => "USA Premium",
            "USAP" => "USA Advanced Package",
        ];

        $packageServices = [];
        while ($row = mysqli_fetch_assoc($resultPackages)) {
            // echo $row['packageName'];
            $packageServices[$row['packageName']][$row['grade']] = $row['packageServices'];
        }
    }
    else if ($typeAccount == 0) {
        $vIdConsultant[0] = $_SESSION['id'];
        $vNameConsultant[0] = $_SESSION['fullName'];
        $nConsultant++;
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


    <title> Add Student</title>

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

        input[name = "parentEmail"] {
            width: 50%;
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
        
        input[type="radio"] {
            margin-right: 8px;
            margin-left: 15px;
        }
        
        input[type="radio"]:first-child {
            margin-left: 0;
        }
        
        label {
            margin-right: 20px;
            font-weight: normal;
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

    <h1 style = "color: rgba(79, 35, 95, .9);"> Add Student </h1>
    <br>
    <br>
    <form method = "post" onsubmit = "return validateForm()">
        <p class = "student-info"> <span class = "title-info"> Student Name: </span> <input type = "text" name = "name" placeholder = "Student's full name" required /> </p>
        <br>
        <p class = "student-info"> <span class = "title-info"> Student's Email: </span> <input type = "email" name = "email" placeholder = "Student's email" required /> </p>
        <?php
        if (isset($errorMail)) {
            ?> <span style = "color: red;"> <?php echo $errorMail; ?> </span> <br> <?php
        }?>
        <br>

        <p class = "student-info"> <span class = "title-info"> Parent's Email: </span> <input type = "email" name = "parentEmail" placeholder = "Parent's email" required /> </p>
        
        <br>
        <p class = "student-info"> <span class = "title-info"> HighSchool: </span> <input type = "text" name = "highSchool" placeholder = "Student's HighSchool" required /> </p>
        <br>
        <p class = "student-info"> <span class = "title-info"> Drive Link: </span> <input value = "#" type = "text" name = "driveLink" placeholder = "Student's Drive Link" required /> </p>
        <br>
        <p class = "student-info"> <span class = "title-info"> Phone number: </span> 
            <input name = "phoneNumber" type="tel" id="phoneInput" placeholder = "Enter student's phone number" pattern="^[\\+]?[(]?[0-9]{3}[)]?[-\\s\\.]?[0-9]{3}[-\\s\\.]?[0-9]{4,6}$" required>
            <div id="statusPhoneNumber" class = "invalidPhoneNumber">Invalid phone number</div>
        </p>
         <br>
        <p class="student-info">
            <span class="title-info">Student Type: </span>
            <label><input type="radio" name="isBachelor" value="0" required onchange="toggleGraduationInput()"> High School Student</label>
            <label><input type="radio" name="isBachelor" value="1" required onchange="toggleGraduationInput()"> Bachelor Student</label>
        </p>
        <br>
        <p class="student-info" id="graduationYearSection" style="display: none;">
            <span class="title-info" id="graduationLabel">Graduation Year: </span>
            <input type="number" name="graduationYear" id="graduationYearInput" min="2024" max="2030" placeholder="Enter year" required onchange="calculateGrade()">
            <div id="gradeCalculation" style="margin-top: 10px; font-weight: bold; color: #4f235f;"></div>
        </p>
         <br>
         <p class="student-info">
            <span class="title-info"> Package type: </span> 
            <select id="packageSelect" name="package" required onchange="updateTextarea()">
                <option value="" disabled selected hidden>Select package</option>
                <option value="US">US</option>
                <option value="USP">US Premium</option>
                <option value="USAP">US Advanced Package</option>
                <option value="EU">Europe</option>
                <option value="EUP">Europe Premium</option>
            </select>
        </p>
        <br>
        <p> 
            <span class="title-info"> Package Details: </span> 
            <p id="charCount">0 / 1000</p> 

            <textarea id="textBox" name="packageDetails" maxlength="1000" rows="10" cols="50" 
                    placeholder="Enter package details (max 1000 characters)..." 
                    oninput="updateCharCount()"></textarea>
        </p>


            <p class = "student-info"> <span class = "title-info"> Consultant: </span> 
                <select name="consultant" required>
                    <?php if ($typeAccount == 1) { ?>
                        <option value="" selected>Select consultant</option>
                    <?php } ?>
                    <?php
                        for ($i = 0; $i < $nConsultant; $i++) {
                            ?> <option value="<?php echo $vIdConsultant[$i]; ?> "> <?php echo $vNameConsultant[$i]; ?> </option> <?php
                        }
                    ?>
                </select>
            </p>
         <br>
         <br>
        <input class="btn btn-primary" type="submit" name = "submit" value="Add student">
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
        var packageShortToLong = <?php echo json_encode($packageShortToLong, JSON_HEX_TAG); ?>; 
        var packageServices = <?php echo json_encode($packageServices, JSON_HEX_TAG); ?>;
        
        function toggleGraduationInput() {
            var isBachelor = document.querySelector('input[name="isBachelor"]:checked').value;
            var graduationSection = document.getElementById('graduationYearSection');
            var graduationLabel = document.getElementById('graduationLabel');
            var graduationInput = document.getElementById('graduationYearInput');
            var gradeCalculation = document.getElementById('gradeCalculation');
            
            graduationSection.style.display = 'block';
            
            if (isBachelor == '1') {
                graduationLabel.textContent = 'Contract End Year: ';
                graduationInput.placeholder = 'Enter contract end year';
                gradeCalculation.textContent = '';
            } else {
                graduationLabel.textContent = 'Graduation Year: ';
                graduationInput.placeholder = 'Enter graduation year';
                calculateGrade();
            }
        }
        
        function calculateGrade() {
            var isBachelor = document.querySelector('input[name="isBachelor"]:checked');
            if (!isBachelor) return;
            
            var isBachelorValue = isBachelor.value;
            var graduationYear = document.getElementById('graduationYearInput').value;
            var gradeCalculation = document.getElementById('gradeCalculation');
            
            if (!graduationYear) {
                gradeCalculation.textContent = '';
                return;
            }
            
            if (isBachelorValue == '1') {
                gradeCalculation.textContent = 'Contract will end in: ' + graduationYear;
                return;
            }
            
            // Calculate grade for non-bachelor students
            var currentDate = new Date();
            var currentYear = currentDate.getFullYear();
            var currentMonth = currentDate.getMonth() + 1; // 0-11 to 1-12
            var currentDay = currentDate.getDate();
            
            var yearsUntilGraduation = graduationYear - currentYear;
            var baseGrade = 12 - yearsUntilGraduation;
            var calculatedGrade;
            var message;
            
            if (currentMonth >= 6 && currentMonth <= 9) {
                // June 1 - September 15: upcoming year
                if (currentMonth == 6 || (currentMonth == 9 && currentDay <= 15)) {
                    calculatedGrade = baseGrade + 1;
                    message = 'Student will be in grade ' + calculatedGrade + ' in the upcoming year';
                } else {
                    calculatedGrade = baseGrade + 1;
                    message = 'Student will be in grade ' + calculatedGrade + ' in the upcoming year';
                }
            } else if (currentMonth >= 10 || currentMonth <= 12) {
                // September 16 - December 31: current year
                calculatedGrade = baseGrade + 1;
                message = 'Student is currently in grade ' + calculatedGrade;
            } else {
                // January 1 - May 31: current year
                calculatedGrade = baseGrade;
                message = 'Student is currently in grade ' + calculatedGrade;
            }
            
            gradeCalculation.textContent = message;
        }
        
        function updateTextarea() {
            var packageSelect = document.getElementById("packageSelect");
            var selectedPackage = packageSelect.value; // Get selected value

            if (selectedPackage) {
                // For now, use a default grade of 12 for package services
                // You might want to adjust this based on your needs
                document.getElementById("textBox").value = packageServices[packageShortToLong[selectedPackage]]["12"];
                updateCharCount();
            } else {
                document.getElementById("textBox").value = "";
                updateCharCount();
            }
        }

        function updateCharCount() {
            var textBox = document.getElementById("textBox");
            var charCount = document.getElementById("charCount");
            charCount.textContent = textBox.value.length + " / 1000";
        }
    </script>

</body>
</html>