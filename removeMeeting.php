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

    if (isset($_GET['meetingId'])) // testez daca e setata vreo universitate
        $meetingId = $_GET['meetingId'];
    else {
        header("location: index.php");
        die();
    }

    $sqlMeetingData = "SELECT `consultantId` FROM meetings WHERE `meetingId` = '$meetingId'";
    $result = mysqli_query($link, $sqlMeetingData);

    if (mysqli_num_rows($result) > 0) {
        $rowConsultantId = mysqli_fetch_assoc($result);
        $consultantId = $rowConsultantId['consultantId'];
    }
    else {
        header("location: index.php");
        die();
    }

    if (!($consultantId == $accountId || $typeAccount == 1)) { // testez daca are acces userul la acest meeting
        header("location: index.php");
        die();
    }

    $sqlDeleteMeeting = "DELETE FROM meetings WHERE `meetingId` = " .$meetingId;
    mysqli_query($link, $sqlDeleteMeeting);

    header("location: index.php");
    die();
?>