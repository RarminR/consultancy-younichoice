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
        $judet = $_POST['judet'];
        $phoneNumber = $_POST['phoneNumber'];
        $highSchool = $_POST['highSchool'];
        $interest = $_POST['interest'];
        $isBachelor = $_POST['isBachelor'];
        $graduationYear = $_POST['graduationYear'];
        $package = $_POST['package'];
        $consultantId = $_POST['consultant'];
        $driveLink = $_POST['driveLink'];
        $packageDetails = $_POST['packageDetails'];
        $activityStatus = $_POST['activityStatus'];
        
        // Calculate grade based on graduation year and current date
        $currentYear = date('Y');
        $currentMonth = date('n'); // 1-12
        $currentDay = date('j'); // 1-31
        
        if ($isBachelor == '1') {
            // Bachelor student - contract end year is stored in graduationYear
            $calculatedGrade = $graduationYear; // For bachelor, store contract end year
            $isMaster = 1;
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
        }

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
                $sqlUpdateInformation = "UPDATE `studentData` SET `consultantId`='$consultantId',`consultantName`='$consultantName',`name`='$name',`email`='$email',`emailParent`='$emailParent',`judet`='$judet',`phoneNumber`='$phoneNumber',`highSchool`='$highSchool',`interest`='$interest',`grade`='$calculatedGrade',`graduationYear`='$graduationYear',`isMaster`='$isMaster', `driveLink`='$driveLink', `packageDetails`='$packageDetails', `packageType`='$package', `activityStatus` = '$activityStatus' WHERE `studentId`=".$studentId;
            else 
                $sqlUpdateInformation = "UPDATE `studentData` SET `consultantId`='$consultantId',`consultantName`='$consultantName',`name`='$name',`email`='$email',`emailParent`='$emailParent',`judet`='$judet',`phoneNumber`='$phoneNumber',`highSchool`='$highSchool',`interest`='$interest',`grade`='$calculatedGrade',`graduationYear`='$graduationYear',`isMaster`='$isMaster', `driveLink`='$driveLink', `activityStatus` = '$activityStatus' WHERE `studentId`=".$studentId;
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
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Design System CSS -->
    <link rel="stylesheet" href="student/design-system.css">
    <script src="https://unpkg.com/react-phone-number-input@3.x/bundle/react-phone-number-input.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/react-phone-number-input@3.x/bundle/style.css"/>

    <title>Edit Student</title>

    <style>
        /* Edit Student Design System Styles */
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

        .edit-header {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-2xl);
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--light-gray);
            margin-bottom: var(--spacing-2xl);
            backdrop-filter: blur(10px);
        }

        .edit-title {
            color: var(--primary-color);
            font-size: var(--font-size-3xl);
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--spacing-lg);
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
        }

        .edit-title::before {
            content: '\f044';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-xl);
            color: var(--primary-color);
        }

        .form-section {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-2xl);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--light-gray);
            margin-bottom: var(--spacing-xl);
        }

        .form-group {
            margin-bottom: var(--spacing-lg);
        }

        .form-label {
            color: var(--primary-color);
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-semibold);
            margin-bottom: var(--spacing-md);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .form-label::before {
            content: '\f007';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-base);
        }

        .email-label::before {
            content: '\f0e0';
        }

        .parent-email-label::before {
            content: '\f0e0';
        }

        .location-label::before {
            content: '\f57c';
        }

        .school-label::before {
            content: '\f19c';
        }

        .interest-label::before {
            content: '\f0f3';
        }

        .drive-label::before {
            content: '\f1c0';
        }

        .phone-label::before {
            content: '\f095';
        }

        .type-label::before {
            content: '\f0c0';
        }

        .graduation-label::before {
            content: '\f073';
        }

        .package-label::before {
            content: '\f3d1';
        }

        .consultant-label::before {
            content: '\f007';
        }

        .status-label::before {
            content: '\f0f3';
        }

        .form-control {
            width: 100%;
            padding: var(--spacing-md) var(--spacing-lg);
            border: 2px solid var(--light-gray);
            border-radius: var(--border-radius-lg);
            font-size: var(--font-size-base);
            font-family: 'Poppins', sans-serif;
            transition: var(--transition-normal);
            background: var(--white);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: var(--shadow-md);
        }

        .form-select {
            width: 100%;
            padding: var(--spacing-md) var(--spacing-lg);
            border: 2px solid var(--light-gray);
            border-radius: var(--border-radius-lg);
            font-size: var(--font-size-base);
            font-family: 'Poppins', sans-serif;
            transition: var(--transition-normal);
            background: var(--white);
            cursor: pointer;
        }

        .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: var(--shadow-md);
        }

        .form-textarea {
            width: 100%;
            padding: var(--spacing-md) var(--spacing-lg);
            border: 2px solid var(--light-gray);
            border-radius: var(--border-radius-lg);
            font-size: var(--font-size-base);
            font-family: 'Poppins', sans-serif;
            transition: var(--transition-normal);
            background: var(--white);
            resize: vertical;
            min-height: 120px;
        }

        .form-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: var(--shadow-md);
        }

        .form-row {
            display: flex;
            gap: var(--spacing-lg);
            align-items: end;
        }

        .form-col {
            flex: 1;
        }

        .radio-group {
            display: flex;
            gap: var(--spacing-lg);
            margin-top: var(--spacing-sm);
        }

        .radio-item {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .radio-item input[type="radio"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary-color);
        }

        .radio-item label {
            font-size: var(--font-size-base);
            font-weight: var(--font-weight-medium);
            color: var(--text-color);
            cursor: pointer;
            margin: 0;
        }

        .btn-submit {
            background: var(--primary-gradient);
            color: var(--white);
            border: none;
            padding: var(--spacing-md) var(--spacing-2xl);
            border-radius: var(--border-radius-lg);
            font-weight: var(--font-weight-semibold);
            font-size: var(--font-size-lg);
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: var(--transition-normal);
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-submit::before {
            content: '\f0c7';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-base);
        }

        .char-count {
            color: var(--secondary-color);
            font-size: var(--font-size-sm);
            margin-bottom: var(--spacing-sm);
        }

        .grade-calculation {
            margin-top: var(--spacing-sm);
            font-weight: var(--font-weight-semibold);
            color: var(--primary-color);
            font-size: var(--font-size-sm);
        }

        .phone-validation {
            margin-top: var(--spacing-xs);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-medium);
        }

        .valid-phone {
            color: var(--success-color);
        }

        .invalid-phone {
            color: var(--danger-color);
        }

        .alert {
            padding: var(--spacing-md) var(--spacing-lg);
            border-radius: var(--border-radius-lg);
            margin-bottom: var(--spacing-lg);
            border: 1px solid transparent;
            font-weight: var(--font-weight-medium);
        }

        .alert-danger {
            background: var(--danger-light);
            color: var(--danger-dark);
            border-color: var(--danger-color);
        }

        .alert-success {
            background: var(--success-light);
            color: var(--success-dark);
            border-color: var(--success-color);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            #content {
                padding: var(--spacing-md);
            }

            .edit-title {
                font-size: var(--font-size-2xl);
            }

            .form-row {
                flex-direction: column;
                gap: var(--spacing-md);
            }

            .form-col {
                width: 100%;
            }

            .radio-group {
                flex-direction: column;
                gap: var(--spacing-sm);
            }
        }
    </style>
  </head>


  <?php include("navbar.php"); ?>

  <div id="content">
    <div class="edit-header">
      <h1 class="edit-title">Edit Student: <?php echo htmlspecialchars($studentData["name"]); ?></h1>
      <?php if (isset($errorMail)) { ?>
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-triangle"></i>
          <?php echo htmlspecialchars($errorMail); ?>
        </div>
      <?php } ?>
    </div>

    <div class="form-section">
      <form method="post" onsubmit="return validateForm()">
        <div class="form-group">
          <label class="form-label" for="name">Student Name</label>
          <input value="<?php echo htmlspecialchars($studentData['name']); ?>" type="text" name="name" id="name" class="form-control" placeholder="Student's full name" required />
        </div>

        <div class="form-group">
          <label class="form-label email-label" for="email">Student's Email</label>
          <input value="<?php echo htmlspecialchars($studentData['email']); ?>" type="email" name="email" id="email" class="form-control" placeholder="Student's email" required />
        </div>

        <div class="form-group">
          <label class="form-label parent-email-label" for="emailParent">Parent's Email</label>
          <input value="<?php echo htmlspecialchars($studentData['emailParent']); ?>" type="email" name="emailParent" id="emailParent" class="form-control" placeholder="Parent's email" />
        </div>

        <div class="form-group">
          <label class="form-label location-label" for="judet">Location</label>
          <input value="<?php echo htmlspecialchars($studentData['judet']); ?>" type="text" name="judet" id="judet" class="form-control" placeholder="Student's location" />
        </div>

        <div class="form-group">
          <label class="form-label school-label" for="highSchool">High School</label>
          <input value="<?php echo htmlspecialchars($studentData['highSchool']); ?>" type="text" name="highSchool" id="highSchool" class="form-control" placeholder="Student's High School" required />
        </div>
        <div class="form-group">
          <label class="form-label interest-label" for="interest">Field of Interest</label>
          <select name="interest" id="interest" class="form-select">
            <option value="">Select Field of Interest</option>
                <?php if ($studentData['interest'] == 'Architecture') { ?>
                    <option value="Architecture" selected>Architecture</option>
                <?php } else { ?>
                    <option value="Architecture">Architecture</option>
                <?php } ?>
                
                <?php if ($studentData['interest'] == 'Art') { ?>
                    <option value="Art" selected>Art</option>
                <?php } else { ?>
                    <option value="Art">Art</option>
                <?php } ?>
                
                <?php if ($studentData['interest'] == 'Biotechnology') { ?>
                    <option value="Biotechnology" selected>Biotechnology</option>
                <?php } else { ?>
                    <option value="Biotechnology">Biotechnology</option>
                <?php } ?>
                
                <?php if ($studentData['interest'] == 'Business') { ?>
                    <option value="Business" selected>Business</option>
                <?php } else { ?>
                    <option value="Business">Business</option>
                <?php } ?>
                
                <?php if ($studentData['interest'] == 'Chemistry') { ?>
                    <option value="Chemistry" selected>Chemistry</option>
                <?php } else { ?>
                    <option value="Chemistry">Chemistry</option>
                <?php } ?>
                
                <?php if ($studentData['interest'] == 'Computer Science') { ?>
                    <option value="Computer Science" selected>Computer Science</option>
                <?php } else { ?>
                    <option value="Computer Science">Computer Science</option>
                <?php } ?>
                
                <?php if ($studentData['interest'] == 'Criminology') { ?>
                    <option value="Criminology" selected>Criminology</option>
                <?php } else { ?>
                    <option value="Criminology">Criminology</option>
                <?php } ?>
                
                <?php if ($studentData['interest'] == 'Culinary Arts') { ?>
                    <option value="Culinary Arts" selected>Culinary Arts</option>
                <?php } else { ?>
                    <option value="Culinary Arts">Culinary Arts</option>
                <?php } ?>
                
                <?php if ($studentData['interest'] == 'Economics') { ?>
                    <option value="Economics" selected>Economics</option>
                <?php } else { ?>
                    <option value="Economics">Economics</option>
                <?php } ?>
                
                <?php if ($studentData['interest'] == 'Film') { ?>
                    <option value="Film" selected>Film</option>
                <?php } else { ?>
                    <option value="Film">Film</option>
                <?php } ?>
                
                <?php if ($studentData['interest'] == 'History') { ?>
                    <option value="History" selected>History</option>
                <?php } else { ?>
                    <option value="History">History</option>
                <?php } ?>
                
                <?php if ($studentData['interest'] == 'Hospitality') { ?>
                    <option value="Hospitality" selected>Hospitality</option>
                <?php } else { ?>
                    <option value="Hospitality">Hospitality</option>
                <?php } ?>
                
                <?php if ($studentData['interest'] == 'International Relations') { ?>
                    <option value="International Relations" selected>International Relations</option>
                <?php } else { ?>
                    <option value="International Relations">International Relations</option>
                <?php } ?>
                
                <?php if ($studentData['interest'] == 'Law') { ?>
                    <option value="Law" selected>Law</option>
                <?php } else { ?>
                    <option value="Law">Law</option>
                <?php } ?>
                
                <?php if ($studentData['interest'] == 'Mathematics') { ?>
                    <option value="Mathematics" selected>Mathematics</option>
                <?php } else { ?>
                    <option value="Mathematics">Mathematics</option>
                <?php } ?>
                
                <?php if ($studentData['interest'] == 'Media / Journalism') { ?>
                    <option value="Media / Journalism" selected>Media / Journalism</option>
                <?php } else { ?>
                    <option value="Media / Journalism">Media / Journalism</option>
                <?php } ?>
                
                <?php if ($studentData['interest'] == 'Medicine') { ?>
                    <option value="Medicine" selected>Medicine</option>
                <?php } else { ?>
                    <option value="Medicine">Medicine</option>
                <?php } ?>
                
                <?php if ($studentData['interest'] == 'Music') { ?>
                    <option value="Music" selected>Music</option>
                <?php } else { ?>
                    <option value="Music">Music</option>
                <?php } ?>
                
                <?php if ($studentData['interest'] == 'Philosophy') { ?>
                    <option value="Philosophy" selected>Philosophy</option>
                <?php } else { ?>
                    <option value="Philosophy">Philosophy</option>
                <?php } ?>
                
                <?php if ($studentData['interest'] == 'Physics') { ?>
                    <option value="Physics" selected>Physics</option>
                <?php } else { ?>
                    <option value="Physics">Physics</option>
                <?php } ?>
                
                <?php if ($studentData['interest'] == 'Political Science') { ?>
                    <option value="Political Science" selected>Political Science</option>
                <?php } else { ?>
                    <option value="Political Science">Political Science</option>
                <?php } ?>
                
                <?php if ($studentData['interest'] == 'Psychology') { ?>
                    <option value="Psychology" selected>Psychology</option>
                <?php } else { ?>
                    <option value="Psychology">Psychology</option>
                <?php } ?>
                
                <?php if ($studentData['interest'] == 'Sustainability') { ?>
                    <option value="Sustainability" selected>Sustainability</option>
                <?php } else { ?>
                    <option value="Sustainability">Sustainability</option>
                <?php } ?>
                
                <?php if ($studentData['interest'] == 'Theatre') { ?>
                    <option value="Theatre" selected>Theatre</option>
                <?php } else { ?>
                    <option value="Theatre">Theatre</option>
                <?php } ?>
                
                <?php if ($studentData['interest'] == 'Undecided') { ?>
                    <option value="Undecided" selected>Undecided</option>
                <?php } else { ?>
                    <option value="Undecided">Undecided</option>
                <?php } ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label drive-label" for="driveLink">Drive Link</label>
          <input value="<?php echo htmlspecialchars($studentData['driveLink']); ?>" type="text" name="driveLink" id="driveLink" class="form-control" placeholder="Student's Drive Link" required />
        </div>
        <div class="form-group">
          <label class="form-label phone-label" for="phoneInput">Phone Number</label>
          <input value="<?php echo htmlspecialchars($studentData['phoneNumber']); ?>" name="phoneNumber" type="tel" id="phoneInput" class="form-control" placeholder="Enter your phone number" pattern="^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$" required>
          <div id="statusPhoneNumber" class="phone-validation valid-phone">Valid phone number</div>
        </div>
        <div class="form-group">
          <label class="form-label type-label">Student Type</label>
          <div class="radio-group">
            <?php 
            $isBachelor = isset($studentData['isMaster']) ? $studentData['isMaster'] : 0;
            ?>
            <div class="radio-item">
              <input type="radio" name="isBachelor" value="0" id="highSchool" <?php echo ($isBachelor == 0) ? 'checked' : ''; ?> required onchange="toggleGraduationInput()">
              <label for="highSchool">High School Student</label>
            </div>
            <div class="radio-item">
              <input type="radio" name="isBachelor" value="1" id="bachelor" <?php echo ($isBachelor == 1) ? 'checked' : ''; ?> required onchange="toggleGraduationInput()">
              <label for="bachelor">Bachelor Student</label>
            </div>
          </div>
        </div>
        <div class="form-group" id="graduationYearSection" style="display: block;">
          <label class="form-label graduation-label" id="graduationLabel" for="graduationYearInput"><?php echo ($isBachelor == 1) ? 'Contract End Year' : 'Graduation Year'; ?></label>
          <input type="number" name="graduationYear" id="graduationYearInput" class="form-control" min="2024" max="2030" value="<?php echo isset($studentData['graduationYear']) ? $studentData['graduationYear'] : ''; ?>" placeholder="<?php echo ($isBachelor == 1) ? 'Enter contract end year' : 'Enter graduation year'; ?>" required onchange="calculateGrade()">
          <div id="gradeCalculation" class="grade-calculation"></div>
        </div>
         <!--UPDATE ALSO PACKAGE FEATURE IN DEV-->
         <?php if ($_SESSION['type'] == 1) { ?> 
         <div class="form-group">
           <label class="form-label package-label" for="package">Package Type</label>
           <select id="package" name="package" class="form-select" required>
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
         </div>

         <div class="form-group">
           <label class="form-label" for="textBox">Package Details</label>
           <div id="charCount" class="char-count">0 / 1000</div>
           <textarea id="textBox" name="packageDetails" class="form-textarea" maxlength="1000" rows="10" placeholder="Enter package details (max 1000 characters)..." oninput="updateCharCount()"><?php echo htmlspecialchars($studentData['packageDetails']); ?></textarea>
         </div>
         <?php } ?>
         <div class="form-group">
           <label class="form-label consultant-label" for="consultant">Consultant</label>
           <select name="consultant" id="consultant" class="form-select" required>
             <?php if ($typeAccount == 1) { ?>
               <option value="" selected>Select consultant</option>
             <?php } ?>
             <?php
               for ($i = 0; $i < $nConsultant; $i++) {
                 if ($vIdConsultant[$i] == $studentData['consultantId']) {?>
                   <option value="<?php echo htmlspecialchars($vIdConsultant[$i]); ?>" selected><?php echo htmlspecialchars($vNameConsultant[$i]); ?></option>
                 <?php } else { ?>
                   <option value="<?php echo htmlspecialchars($vIdConsultant[$i]); ?>"><?php echo htmlspecialchars($vNameConsultant[$i]); ?></option>
                 <?php }
               }
             ?>
           </select>
         </div>

         <div class="form-group">
           <label class="form-label status-label" for="status">Status</label>
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
           <select id="status" name="activityStatus" class="form-select" required>
             <option value="0" <?php echo $stringActive; ?>>Active</option>
             <option value="1" <?php echo $stringGraduated; ?>>Graduated</option>
             <option value="2" <?php echo $stringDeleted; ?>>Deleted</option>
           </select>
         </div>
         
         <div class="form-group">
           <button class="btn-submit" type="submit" name="submit">Update Student Information</button>
         </div>
       </form>
     </div>


    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script>
        // Initialize grade calculation on page load
        window.onload = function() {
            calculateGrade();
        };
        
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