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

    if (isset($_GET['studentId'])) // testez daca e setat un student
        $studentId = $_GET['studentId'];
    else {
        header("location: index.php");
        die();
    }

    if ($typeAccount != 1) { // daca userul nu e admin
        $sqlStudentData = "SELECT `consultantId` FROM studentData WHERE `studentId` = " .$studentId;
        
        $queryStudentData = mysqli_query($link, $sqlStudentData);

        if (mysqli_num_rows($queryStudentData) > 0) // testez daca exista vreun student cu id-ul dat
            $dataStudent = mysqli_fetch_assoc($queryStudentData);
        else {
            header("location: index.php");
            die();
        }

        if (!$dataStudent['consultantId'] == $accountId) { // testez daca are acces userul la studentul dat
            header("location: index.php");
            die();
        }
    }

    $sqlRestoreStudent = "UPDATE studentData SET `activityStatus` = 0, name = LEFT(name, LENGTH(name) - 10) WHERE `studentId` = " .$studentId;
    mysqli_query($link, $sqlRestoreStudent);

    header("location: index.php");
    die();
?>