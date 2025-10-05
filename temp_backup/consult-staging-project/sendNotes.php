<?php 
    function sendMeetingSummary($to, $studentName, $meetingDate, $meetingNotes, $driveLink, $consultantName, $consultantEmail) {
        $apiKey = 're_6XaDD7dc_2ZLrH3sHnrQhdnFnzPJsdiG9';
        $url = 'https://api.resend.com/emails';
        
        $subject = "Rezumat Meeting Youni - " . $studentName;
        
        $message = "
        <html>
        <head>
            <title>$subject</title>
        </head>
        <body>
            <p><strong>Elev:</strong> $studentName</p>
            <p><strong>Data întâlnirii:</strong> $meetingDate</p>
            <p><strong>Rezumat întâlnire:</strong></p>
            <p>$meetingNotes</p>
            <br>
            <p><strong>Un tracker complet poate fi găsit la acest link:</strong> <a href='$driveLink'>$driveLink</a></p>
            <br>
            <p>Dacă aveți întrebări suplimentare sau doriți să discutăm mai detaliat, vă rugăm să nu ezitați să ne contactați. Suntem aici pentru a sprijini dezvoltarea academică și personală a $studentName.</p>
            <br>
            <p>Vă mulțumim pentru colaborare!</p>
            <br>
            <p><strong>$consultantName</strong></p>
            <p>Consultant</p>
            <p><strong>Echipa Youni</strong></p>
            <p><a href='mailto:$consultantEmail'>$consultantEmail</a></p>
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
        
        if (curl_errno($ch)) {
            curl_close($ch);
            return 'cURL error: ' . curl_error($ch);
        }
        
        $responseData = json_decode($response, true);
        curl_close($ch);
        
        if (isset($responseData['id'])) {
            return "Email sent successfully to $to";
        } else {
            return "Failed to send email to $to. Response: " . print_r($responseData, true);
        }
    }
    
    function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
?>


<?php 
    session_start();
    require_once "configDatabase.php";

    if (!isset($_SESSION['type'])) { // testez daca userul est logat
        header("location: index.php");
        die();
    }
    else {
        $typeAccount = $_SESSION["type"];
        $accountId = $_SESSION["id"];
    }

    if (isset($_GET['meetingId'])) // testez daca e setat un meeting
        $meetingId = $_GET['meetingId'];
    else {
        header("location: index.php");
        die();
    }

    $sqlMeetingData = "SELECT * FROM meetings WHERE `meetingId` = '$meetingId'";    
    $queryMeetingData = mysqli_query($link, $sqlMeetingData);

    if (mysqli_num_rows($queryMeetingData) > 0) // testez daca exista un meeting cu id-ul dat
        $dataMeeting = mysqli_fetch_assoc($queryMeetingData);
    else {
        header("location: index.php");
        die();
    }

    $studentId = $dataMeeting['studentId'];
    $consultantId = $dataMeeting['consultantId'];

    if (!($consultantId == $accountId || $typeAccount == 1)) { // testez daca are acces userul la acest meeting
        header("location: index.php");
        die();
    }

    $studentName = $dataMeeting['studentName'];
    $studentSchool = $dataMeeting['studentSchool'];
    $consultantName = $dataMeeting['consultantName'];
    $meetingDate = $dataMeeting['meetingDate'];
    $meetingNotes = $dataMeeting['meetingNotes'];

    $sqlStudentData = "SELECT email, emailParent, driveLink FROM studentData WHERE `studentId` = '$studentId'";    
    $queryStudentData = mysqli_query($link, $sqlStudentData);
    if (mysqli_num_rows($queryMeetingData) > 0) // testez daca exista un student cu id-ul dat
        $dataStudent = mysqli_fetch_assoc($queryStudentData);

    $email = $dataStudent['email'];
    $emailParent = $dataStudent['emailParent'];
    $driveLink = $dataStudent['driveLink'];

    $sqlConsultantData = "SELECT email FROM users WHERE `userId` = '$consultantId'";    
    $queryConsultantData = mysqli_query($link, $sqlConsultantData);

    if (mysqli_num_rows($queryConsultantData) > 0) { // Check if consultant exists
        $dataConsultant = mysqli_fetch_assoc($queryConsultantData);
    }
    $consultantEmail = $dataConsultant['email'];


    if (isValidEmail($email)) { // test if the email is valid and send notes
        echo sendMeetingSummary($email, $studentName, $meetingDate, $meetingNotes, $driveLink, $consultantName, $consultantEmail);
    } 
    
    if (isValidEmail($emailParent)) {
        echo sendMeetingSummary($emailParent, $studentName, $meetingDate, $meetingNotes, $driveLink, $consultantName, $consultantEmail);
    }
    header("location: meeting.php?meetingId=$meetingId");

?>