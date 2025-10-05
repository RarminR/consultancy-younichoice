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

    if (isset($_GET['universityId'])) // testez daca e setata vreo universitate
        $universityId = $_GET['universityId'];
    else {
        header("location: index.php");
        die();
    }

    if ($typeAccount != 1) { // daca userul nu e admin
        header("location: index.php");
        die();
    }

    $sqlDeleteUniversity = "DELETE FROM universities WHERE `universityId` = " .$universityId;
    mysqli_query($link, $sqlDeleteUniversity);

    header("location: universitiesList.php");
    die();
?>