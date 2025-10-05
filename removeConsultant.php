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

    if ($typeAccount != 1) {
        header("location: index.php");
        die();
    }

    if (isset($_GET['consultantId'])) // testez daca e setat un consultant
        $userId = $_GET['consultantId'];
    else {
        header("location: index.php");
        die();
    }

    $sqlViewConsultant = "SELECT * FROM users WHERE userId = '$userId' AND isActive = 1;";
    $resultViewConsultant = mysqli_query($link, $sqlViewConsultant);
    if (mysqli_num_rows($resultViewConsultant) == 0) {
        $_SESSION['error'] = "Consultant not found or already removed.";
        header("location: consultantsList.php");
        die();
    }

    $sqlViewStudents = "SELECT * FROM studentData WHERE consultantId = '$userId' AND activityStatus = 0;";
    $resultViewStudents = mysqli_query($link, $sqlViewStudents);
    if (mysqli_num_rows($resultViewStudents) > 0) {
        $_SESSION['error'] = "You cannot remove this consultant because they have active students.";
        header("location: consultant.php?consultantId=$userId");
        die();
    }
    else {
        $sqlDeleteConsultant = "UPDATE users
            SET 
            isActive = false,
            fullName = CONCAT(fullName, ' (inactive)')
            WHERE userId = '$userId' AND isActive = 1;";
        mysqli_query($link, $sqlDeleteConsultant);
    }

    header("location: index.php");
    die();
?>