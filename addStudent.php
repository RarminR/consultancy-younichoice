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
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Design System CSS -->
    <link rel="stylesheet" href="student/design-system.css">
    <script src="https://unpkg.com/react-phone-number-input@3.x/bundle/react-phone-number-input.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/react-phone-number-input@3.x/bundle/style.css"/>
    <title>Add Student - Youni</title>

    <style>
        /* Add Student Page Design System Styles */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
        }

        #content {
            max-width: 1000px;
            margin: 0 auto;
            padding: var(--spacing-2xl);
            min-height: calc(100vh - 120px);
        }

        .navbar {
            background: var(--white);
            box-shadow: var(--shadow-sm);
            border-bottom: 1px solid var(--light-gray);
        }

        .add-student-container {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-2xl);
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--light-gray);
            backdrop-filter: blur(10px);
        }

        .add-student-header {
            text-align: center;
            margin-bottom: var(--spacing-2xl);
            padding-bottom: var(--spacing-lg);
            border-bottom: 2px solid var(--light-gray);
        }

        .add-student-title {
            color: var(--primary-color);
            font-size: var(--font-size-4xl);
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--spacing-md);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-md);
        }

        .add-student-title::before {
            content: '\f067';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-2xl);
            color: var(--primary-color);
        }

        .form-section {
            background: var(--light-bg);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-2xl);
            margin-bottom: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--light-gray);
        }

        .form-group {
            margin-bottom: var(--spacing-xl);
        }

        .form-label {
            color: var(--primary-color);
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-semibold);
            margin-bottom: var(--spacing-sm);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .form-label::before {
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-base);
            color: var(--primary-color);
        }

        .name-label::before { content: '\f007'; }
        .email-label::before { content: '\f0e0'; }
        .parent-email-label::before { content: '\f0e0'; }
        .school-label::before { content: '\f19c'; }
        .drive-label::before { content: '\f1c0'; }
        .phone-label::before { content: '\f3cd'; }
        .type-label::before { content: '\f0c0'; }
        .graduation-label::before { content: '\f073'; }
        .package-label::before { content: '\f1b0'; }
        .details-label::before { content: '\f15c'; }
        .consultant-label::before { content: '\f0c0'; }

        .form-control, .form-select, .form-textarea {
            width: 100%;
            padding: var(--spacing-md) var(--spacing-lg);
            border: 2px solid var(--light-gray);
            border-radius: var(--border-radius-lg);
            font-size: var(--font-size-base);
            font-family: 'Poppins', sans-serif;
            transition: var(--transition-normal);
            background: var(--white);
        }

        .form-control:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: var(--shadow-md);
        }

        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }

        .radio-group {
            display: flex;
            gap: var(--spacing-xl);
            margin-top: var(--spacing-sm);
        }

        .radio-item {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        input[type="radio"] {
            margin: 0;
            accent-color: var(--primary-color);
            transform: scale(1.2);
        }

        .radio-item label {
            margin: 0;
            font-weight: var(--font-weight-medium);
            color: var(--text-color);
            font-size: var(--font-size-base);
            cursor: pointer;
        }

        .char-count {
            color: var(--secondary-color);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-medium);
            margin-bottom: var(--spacing-sm);
        }

        .grade-calculation {
            color: var(--primary-color);
            font-weight: var(--font-weight-semibold);
            font-size: var(--font-size-sm);
            margin-top: var(--spacing-sm);
            padding: var(--spacing-sm);
            background: var(--light-bg);
            border-radius: var(--border-radius-md);
            border-left: 4px solid var(--primary-color);
        }

        .phone-validation {
            font-size: var(--font-size-sm);
            margin-top: var(--spacing-xs);
            font-weight: var(--font-weight-medium);
        }

        .valid-phone {
            color: var(--success-color);
        }

        .invalid-phone {
            color: var(--danger-color);
        }

        .btn-submit {
            background: var(--primary-gradient);
            color: var(--white);
            border: none;
            padding: var(--spacing-lg) var(--spacing-2xl);
            border-radius: var(--border-radius-lg);
            font-weight: var(--font-weight-semibold);
            font-size: var(--font-size-lg);
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: var(--transition-normal);
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-submit::before {
            content: '\f067';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-base);
        }

        .alert {
            padding: var(--spacing-md) var(--spacing-lg);
            border-radius: var(--border-radius-lg);
            margin-bottom: var(--spacing-lg);
            font-weight: var(--font-weight-medium);
        }

        .alert-danger {
            background: var(--danger-light);
            color: var(--danger-dark);
            border: 1px solid var(--danger-color);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--light-gray);
            border-radius: var(--border-radius-sm);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--secondary-color);
            border-radius: var(--border-radius-sm);
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            #content {
                padding: var(--spacing-md);
            }

            .add-student-title {
                font-size: var(--font-size-2xl);
            }

            .form-section {
                padding: var(--spacing-lg);
            }

            .radio-group {
                flex-direction: column;
                gap: var(--spacing-md);
            }

            .btn-submit {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
  </head>

  
  <?php include("navbar.php"); ?>

  <div id="content">
    <div class="add-student-container">
        <div class="add-student-header">
            <h1 class="add-student-title">Add Student</h1>
        </div>

        <form method="post" onsubmit="return validateForm()">
            <div class="form-section">
                <div class="form-group">
                    <label class="form-label name-label">Student Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter student's full name" required>
                </div>

                <div class="form-group">
                    <label class="form-label email-label">Student's Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter student's email" required>
                    <?php if (isset($errorMail)) { ?>
                        <div class="alert alert-danger"><?php echo $errorMail; ?></div>
                    <?php } ?>
                </div>

                <div class="form-group">
                    <label class="form-label parent-email-label">Parent's Email</label>
                    <input type="email" name="parentEmail" class="form-control" placeholder="Enter parent's email" required>
                </div>

                <div class="form-group">
                    <label class="form-label school-label">High School</label>
                    <input type="text" name="highSchool" class="form-control" placeholder="Enter student's high school" required>
                </div>

                <div class="form-group">
                    <label class="form-label drive-label">Drive Link</label>
                    <input type="text" name="driveLink" class="form-control" value="#" placeholder="Enter student's drive link" required>
                </div>

                <div class="form-group">
                    <label class="form-label phone-label">Phone Number</label>
                    <input name="phoneNumber" type="tel" id="phoneInput" class="form-control" placeholder="Enter student's phone number" pattern="^[\\+]?[(]?[0-9]{3}[)]?[-\\s\\.]?[0-9]{3}[-\\s\\.]?[0-9]{4,6}$" required>
                    <div id="statusPhoneNumber" class="phone-validation invalid-phone">Invalid phone number</div>
                </div>

                <div class="form-group">
                    <label class="form-label type-label">Student Type</label>
                    <div class="radio-group">
                        <div class="radio-item">
                            <input type="radio" name="isBachelor" value="0" id="highSchool" required onchange="toggleGraduationInput()">
                            <label for="highSchool">High School Student</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" name="isBachelor" value="1" id="bachelor" required onchange="toggleGraduationInput()">
                            <label for="bachelor">Bachelor Student</label>
                        </div>
                    </div>
                </div>

                <div class="form-group" id="graduationYearSection" style="display: none;">
                    <label class="form-label graduation-label" id="graduationLabel">Graduation Year</label>
                    <input type="number" name="graduationYear" id="graduationYearInput" class="form-control" min="2024" max="2030" placeholder="Enter year" required onchange="calculateGrade()">
                    <div id="gradeCalculation" class="grade-calculation"></div>
                </div>

                <div class="form-group">
                    <label class="form-label package-label">Package Type</label>
                    <select id="packageSelect" name="package" class="form-select" required onchange="updateTextarea()">
                        <option value="" disabled selected hidden>Select package</option>
                        <option value="US">US</option>
                        <option value="USP">US Premium</option>
                        <option value="USAP">US Advanced Package</option>
                        <option value="EU">Europe</option>
                        <option value="EUP">Europe Premium</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label details-label">Package Details</label>
                    <div id="charCount" class="char-count">0 / 1000</div>
                    <textarea id="textBox" name="packageDetails" class="form-textarea" maxlength="1000" placeholder="Enter package details (max 1000 characters)..." oninput="updateCharCount()"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label consultant-label">Consultant</label>
                    <select name="consultant" class="form-select" required>
                        <?php if ($typeAccount == 1) { ?>
                            <option value="" selected>Select consultant</option>
                        <?php } ?>
                        <?php
                            for ($i = 0; $i < $nConsultant; $i++) {
                                echo '<option value="' . htmlspecialchars($vIdConsultant[$i]) . '">' . htmlspecialchars($vNameConsultant[$i]) . '</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>

            <div style="text-align: center; margin-top: var(--spacing-2xl);">
                <button class="btn-submit" type="submit" name="submit">
                    Add Student
                </button>
            </div>
        </form>
    </div>
  </div>


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
                invalidPhoneNumberDisplay.textContent = 'Valid phone number';
                invalidPhoneNumberDisplay.classList.remove("invalid-phone");
                invalidPhoneNumberDisplay.classList.add("valid-phone");
            } else {
                invalidPhoneNumberDisplay.textContent = 'Invalid phone number';
                invalidPhoneNumberDisplay.classList.add("invalid-phone");
                invalidPhoneNumberDisplay.classList.remove("valid-phone");
            }
        });

        function validateForm() {
            var phoneNumberInput = document.getElementById("phoneInput");
            
            if (validatePhoneNumber(phoneNumberInput.value)) {
                return true; // allow form submission
            }

            invalidPhoneNumberDisplay.classList.add("fw-bold");
            return false; // prevent form submission
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