<!doctype html>

<?php
    session_start();
    require_once "configDatabase.php";

    if (!isset($_SESSION['type'])) { /// testez daca userul este logat
        header("location: index.php");
        die();
    }
    else {
        $typeAccount = $_SESSION["type"];
        $accountId = $_SESSION["id"];
    }

    if (isset($_GET["applicationId"])) /// testez daca este setata vreo aplicatie
        $applicationId = $_GET["applicationId"];
    else {
        header("location:index.php");
        die();
    }

    if ($typeAccount != 1) {
        header("location: index.php");
        die();
    }

    // Get the platform parameter
    $platform = isset($_GET['platform']) ? $_GET['platform'] : 1;

    // First, get the current postedStatus
    $sqlGetCurrentStatus = "SELECT postedStatus FROM applicationStatus WHERE applicationId = " . $applicationId;
    $result = mysqli_query($link, $sqlGetCurrentStatus);
    $currentStatus = mysqli_fetch_assoc($result)['postedStatus'];

    // Determine the new status
    $newStatus = 0; // Default to not posted
    if ($currentStatus == 1) { // If currently posted on both
        if ($platform == 2) { // Unposting from Instagram
            $newStatus = 3; // Set to posted on Facebook only
        } else if ($platform == 3) { // Unposting from Facebook
            $newStatus = 2; // Set to posted on Instagram only
        }
    } else {
        $newStatus = 0; // If not posted on both, set to not posted
    }

    // Update the postedStatus
    $sqlPostApplication = "UPDATE applicationStatus SET postedStatus = " . $newStatus . " WHERE `applicationId` = " . $applicationId;
    $queryApplicationData = mysqli_query($link, $sqlPostApplication);

    header("location: marketingAcceptedList.php");
?>

