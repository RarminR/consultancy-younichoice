<?php
require_once "configDatabase.php";

function sendEmailStudentNoMeeting($prenume_elev, $nume_consultant, $to) {
    $subject = "Hai să programăm următoarea întâlnire!";

    $message = "
    Salut, $prenume_elev,<br><br>

    A trecut ceva timp de la ultima noastră discuție și vrem să ne asigurăm că ești pe drumul cel bun! Întâlnirile regulate cu consultantul tău sunt esențiale pentru a-ți menține progresul, așa că te încurajăm să stabilești următoarea sesiune.<br><br>

    Poți programa o nouă întâlnire pe calendly sau contactând direct consultantul tău, $nume_consultant.<br><br>

    Dacă ai întrebări sau ai nevoie de ajutor, suntem aici pentru tine!<br><br>

    Cu prietenie,<br>
    <strong>Echipa Youni</strong>
    ";

    // Set email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: office@younichoice.com" . "\r\n";
    $headers .= "Reply-To: office@younichoice.com" . "\r\n"; // Replace with actual sender email
    $headers .= "X-Mailer: PHP/" . phpversion();

    if (mail($to, $subject, $message, $headers)) {
        echo "Notification sent to $to\n";
    } else {
        echo "Failed to send notification to $to\n";
    }
}

function sendEmailConsultantNoMeeting($prenume_elev, $nume_consultant, $to) {
    $subject = "Reminder: O lună fără meetinguri cu $prenume_elev";
    $message = "
            Salut, $nume_consultant,<br><br>

            Am observat că de la ultima întâlnire cu $prenume_elev a trecut deja o lună și nu a fost programat un nou meeting. Te rugăm să iei legătura cu $prenume_elev și/sau părintele său pentru a planifica o discuție cât mai curând posibil.<br><br>

            Continuitatea întâlnirilor este esențială pentru a ne asigura că elevul își atinge obiectivele stabilite. Dacă întâmpini dificultăți sau ai nevoie de suport din partea echipei, te rugăm să ne anunți.<br><br>

            Mulțumim pentru implicare,<br>
            <strong>Echipa Youni</strong>
            ";
    // Set email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: office@younichoice.com" . "\r\n";
    $headers .= "Reply-To: office@younichoice.com" . "\r\n"; // Replace with actual sender email
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // Send the email
    if (mail($to, $subject, $message, $headers)) {
        echo "Notification sent to $to\n";
    } else {
        echo "Failed to send notification to $to\n";
    }
}

function sendEmailYouniMeeting($prenume_elev, $nume_consultant, $to) {
    $subject = "Alertă: O lună fără meeting între $prenume_elev și $nume_consultant";
    $message = "
    Salut, Youni,<br><br>

    Dorim să te informăm că elevul <strong>$prenume_elev</strong> nu a avut nicio întâlnire cu consultantul său, <strong>$nume_consultant</strong>, de mai bine de o lună. Aceasta poate afecta progresul elevului și implicarea acestuia în program.<br><br>

    <strong>Recomandare:</strong><br>
    - Trimite notificări către consultant, părinte și elev pentru a programa o nouă întâlnire.<br>
    - Monitorizează progresul pentru a identifica dacă există alte obstacole sau probleme care necesită atenție.<br><br>

    Dacă este nevoie de mai multe informații sau ai alte sugestii de acțiune, sunt la dispoziția ta.<br><br>

    Cu respect,<br>
    <strong>Echipa Youni</strong>
    ";

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: office@younichoice.com" . "\r\n";
    $headers .= "Reply-To: office@younichoice.com" . "\r\n"; // Replace with actual sender email
    $headers .= "X-Mailer: PHP/" . phpversion();
    if (mail($to, $subject, $message, $headers)) {
        echo "Notification sent to $to\n";
    } else {
        echo "Failed to send notification to $to\n";
    }
}

function sendEmailParent($prenume_elev, $nume_consultant, $numele_platformei, $to) {
    $numele_platformei = "Calendly";
    $subject = "Reminder: O lună fără întâlniri pentru $prenume_elev";
    $message = "
    Bună ziua,<br><br>

    Dorim să vă informăm că de la ultima întâlnire dintre <strong>$prenume_elev</strong> și consultantul său, <strong>$nume_consultant</strong>, a trecut deja o lună. În absența acestor întâlniri regulate, progresul poate fi încetinit.<br><br>

    Vă încurajăm să discutați cu $prenume_elev și să programați o nouă întâlnire cât mai curând, fie prin platforma <strong>$numele_platformei</strong>, fie contactându-l direct pe consultant.<br><br>

    Dacă aveți întrebări sau aveți nevoie de suport, nu ezitați să ne contactați.<br><br>

    Cu respect,<br>
    <strong>Echipa Youni</strong>
    ";

    // Set email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: office@younichoice.com" . "\r\n";
    $headers .= "Reply-To: sender@example.com" . "\r\n"; // Replace with actual sender email
    $headers .= "X-Mailer: PHP/" . phpversion();
    if (mail($to, $subject, $message, $headers)) {
        echo "Notification sent to $to\n";
    } else {
        echo "Failed to send notification to $to\n";
    }
}

// Fetch students with data for notification
$sql = "
    SELECT m.*
    FROM meetings m
    INNER JOIN (
        SELECT studentId, MAX(meetingDate) AS latestMeetingDate
        FROM meetings
        GROUP BY studentId
    ) latest ON m.studentId = latest.studentId AND m.meetingDate = latest.latestMeetingDate;
";

$result = mysqli_query($link, $sql);

// Check if query returned results
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Calculate days since last meeting
        $meetingDate = strtotime($row['meetingDate']);
        $today = strtotime(date("Y-m-d"));
        $daysSinceMeeting = floor((($today - $meetingDate) / (60 * 60 * 24))); // Convert seconds to days

        // Display results
        if ($daysSinceMeeting == 21) {
            // send email to student & consultant
            sendEmailStudentNoMeeting($nume, $nume_consultant, $email);
            sendEmailConsultantNoMeeting($prenume_elev, $nume_consultant, $to);
        }
        else if ($daysSinceMeeting == 28) {
            // send email to 
            sendEmailYouniMeeting($prenume_elev, $nume_consultant, $to);
        }
        else if ($daysSinceMeeting == 35) {
            sendEmailParent($prenume_elev, $nume_consultant, $numele_platformei, $to);
        }
    }
} else {
    echo "No meeting records found.";
}





?>
