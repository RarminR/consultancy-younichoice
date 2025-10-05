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
    $newStatus = $platform; // Default to the platform status
    if (($currentStatus == 2 && $platform == 3) || ($currentStatus == 3 && $platform == 2)) {
        // If it's posted on one platform and being posted on the other, set to "Posted on Both"
        $newStatus = 1;
    }

    // Update the postedStatus
    $sqlPostApplication = "UPDATE applicationStatus SET postedStatus = " . $newStatus . " WHERE `applicationId` = " . $applicationId;
    $queryApplicationData = mysqli_query($link, $sqlPostApplication);

    header("location: marketingAcceptedList.php");
?>

