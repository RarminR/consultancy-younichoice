<!DOCTYPE html>

<html> 

<?php
    require_once "configDatabase.php";

    // consultants checkbox
    $consultants = $_GET['consultant'];

    $consultantString = "(";
    $firstElem = 0;

    foreach ($consultants as $consultant) {
        if ($firstElem > 0)
            $consultantString .= ',';
        $consultantString .= $consultant;

        $firstElem += 1;
    }
    $consultantString .= ')';
    // echo $consultantString;

    if ($firstElem == 0) {
        $consultantString = "(";

        $sqlConsultants = "SELECT `consultantId` FROM consultantData";
        $resultConsultants = mysqli_query($link, $sqlConsultants);

        $firstElem = 0;
        while ($row = mysqli_fetch_assoc($resultConsultants)) {
            if ($firstElem > 0)
                $consultantString .= ",";
            $consultantString .= $row['consultantId'];
            
            $firstElem += 1;
        }
        $consultantString .= ")";
    }
    echo $consultantString;

    // eu/us checkbox
    $packageType = $_GET['package'];

    $packageString = "(";
    $firstElem = 0;

    foreach ($packageType as $package) {
        if ($firstElem > 0)
            $packageString .= ',';
        $packageString .= $package;

        $firstElem += 1;
    }
    $packageString .= ')';

    if ($firstElem == 0) 
        $packageString = "(EU, US)";

    echo $packageString;
    // grade checkbox 
    $gradeType = $_GET['grade'];

    $gradeString = "(";
    $firstElem = 0;

    foreach ($gradeType as $grade) {
        if ($firstElem > 0)
            $gradeString .= ',';
        $gradeString .= $grade;

        $firstElem += 1;
    }
    $gradeString .= ')';

    if ($firstElem == 0)
        $gradeString = "(8, 9, 10, 11, 12)";
    echo $gradeString;


?>

</html> 