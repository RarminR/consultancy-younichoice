<?php
    session_start();

    function getStatusColor($status) {
        $status = trim($status);
        if ($status == "In progress")
            $colorStatus = "#FFA500";
        else if ($status == "Accepted")
            $colorStatus = "#008000";
        else if ($status == "Rejected")
            $colorStatus = "#FF0000";
        else if ($status == "Waitlisted")
            $colorStatus = "#808080";
        else if ($status == "Suggested")
            $colorStatus = "#4169E1"; // Royal Blue
        else if ($status == "Not Interested Anymore")
            $colorStatus = "#8B4513"; // Saddle Brown
        else
            $colorStatus = "#c61b75";

        return $colorStatus;
    }

    if (!isset($_SESSION['type'])) {
        header("location: index.php");
        die();
    }
    else {
        $typeAccount = $_SESSION["type"];
        $userId = $_SESSION["id"];
    }

    require_once "configDatabase.php";
    
    
?>

<?php // GET DATA
    $consultants = $_GET['consultant'];

    $consultantString = "(";
    $firstElem = 0;

    foreach ($consultants as $consultant) {
        if ($firstElem > 0)
            $consultantString .= ',';
        $consultantString .= $consultant;
        $freqConsultant[$consultant] = 1;

        $firstElem += 1;
    }
    $consultantString .= ')';
    // echo $consultantString;

    if ($firstElem == 0) {
        $consultantString = "(";

        $sqlConsultants = "SELECT userId FROM users WHERE type = 0";
        $resultConsultants = mysqli_query($link, $sqlConsultants);

        $firstElem = 0;
        while ($row = mysqli_fetch_assoc($resultConsultants)) {
            if ($firstElem > 0)
                $consultantString .= ",";
            $consultantString .= $row['userId'];
            // $freqConsultant[$row['userId']] = 1;

            $firstElem += 1;
        }
        $consultantString .= ")";
    }

    $institutionType = $_GET['institution'];

    $institutionString = "('";
    $firstElem = 0;

    foreach ($institutionType as $institution) {
        if ($firstElem > 0)
            $institutionString .= "','";
        $institutionString .= $institution;
        $freqInstitution[$institution] = 1;

        $firstElem += 1;
    }
    $institutionString .= "')";

    if ($firstElem == 0) {
        $institutionString = "(0, 1, 2)";
    }

    // echo $packageString;
    // grade checkbox 
    $commissionType = $_GET['commission'];

    $commissionString = "(";
    $firstElem = 0;

    foreach ($commissionType as $commission) {
        if ($firstElem > 0)
            $commissionString .= ',';
        $commissionString .= $commission;
        $freqCommission[$commission] = 1;

        $firstElem += 1;
    }
    $commissionString .= ')';

    if ($firstElem == 0) {
        $commissionString = "(0, 1)";
    }


    $statusType = $_GET['status'];
    $statusString = "(";
    $firstElem = 0;

    foreach($statusType as $status) {
        if ($firstElem > 0)
            $statusString .= ',';
        $statusString .= "'";
        $statusString .= $status;
        $statusString .= "'";
        $freqStatus[$status] = 1;

        $firstElem += 1;
    }
    $statusString .= ')';


    if ($firstElem == 0) {
        $statusString = "('In progress', 'Accepted', 'Waitlisted', 'Rejected', 'Enrolled', 'Suggested', 'Not Interested Anymore')";
    }

    // Country checkbox filter
    $mainCountries = ["USA", "The Netherlands", "UK", "Italy", "Spain", "Germany", "Ireland", "Switzerland", "France", "Belgium"];
    $countryVariants = [
        "USA" => ["USA", "US", "United States"],
        "UK" => ["UK", "United Kingdom"],
        "The Netherlands" => ["The Netherlands", "Netherlands"],
        "Spain" => ["Spain", "Spania"],
    ];

    $countryType = $_GET['country'] ?? [];
    $hasOther = in_array("Other", $countryType);

    $expandedCountries = [];
    $freqCountry = [];
    $firstElem = 0;

    foreach ($countryType as $country) {
        $freqCountry[$country] = 1;
        if ($country == "Other") continue; 
        if (isset($countryVariants[$country])) {
            $expandedCountries = array_merge($expandedCountries, $countryVariants[$country]);
        } else {
            $expandedCountries[] = $country;
        }
        $firstElem += 1;
    }
    $expandedCountries = array_unique($expandedCountries);

    if ($hasOther && $firstElem > 0) {
        // Some countries + Other selected
        $countryCondition = "(u.universityCountry IN ('" . implode("','", $expandedCountries) . "') OR u.universityCountry NOT IN ('" . implode("','", $mainCountries) . "'))";
    } else if ($hasOther) {
        // Only Other selected
        $countryCondition = "(u.universityCountry NOT IN ('" . implode("','", $mainCountries) . "'))";
    } else if ($firstElem > 0) {
        // Only main countries selected
        $countryCondition = "(u.universityCountry IN ('" . implode("','", $expandedCountries) . "'))";
    } else {
        // None selected, show all
        $countryCondition = "(u.universityCountry IN ('" . implode("','", $mainCountries) . "'))";
    }
?>

<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
    <title>Applications List </title>

    <style>
        :root {
            --primary-color: #4f235f;
            --secondary-color: #6c757d;
            --accent-color: #007bff;
            --background-color: #f8f9fa;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        #contentStudents {
            width: 75%;
            margin: auto;
            padding: 20px;
            background-color: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
        }

        #contentFilter {
            width: 20%;
            float: left;
            margin: 20px;
            padding: 20px;
            background-color: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
        }

        .navbar {
            height: 80px;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        h1 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 30px;
            font-size: 2.2rem;
        }

        h3 {
            color: var(--primary-color);
            border-bottom: 3px solid var(--primary-color);
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        h4 {
            color: var(--secondary-color);
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 8px;
            margin-bottom: 15px;
            font-weight: 500;
        }

        .status-group {
            background-color: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: var(--card-shadow);
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .status-group:hover {
            box-shadow: var(--hover-shadow);
        }

        .status-group-title {
            color: var(--primary-color);
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e9ecef;
        }

        .checkbox-container {
            margin: 10px 0;
            transition: all 0.2s ease;
            padding: 8px;
            border-radius: 6px;
        }

        .checkbox-container:hover {
            transform: translateX(5px);
            background-color: #f8f9fa;
        }

        .checkbox-container input[type="checkbox"] {
            margin-right: 12px;
            cursor: pointer;
            width: 18px;
            height: 18px;
        }

        .checkboxLabel {
            cursor: pointer;
            user-select: none;
            color: var(--secondary-color);
            font-size: 0.95rem;
        }

        .list-group-item {
            margin-bottom: 10px;
            border-radius: 8px !important;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            padding: 20px;
        }

        .list-group-item:hover {
            box-shadow: var(--hover-shadow);
            transform: translateY(-2px);
        }

        .badge {
            padding: 6px 12px;
            font-size: 0.85rem;
            font-weight: 500;
            border-radius: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            white-space: nowrap;
            color: white !important;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }

        .btn-primary {
            background-color: var(--accent-color);
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--hover-shadow);
        }

        input[type="button"], input[type="submit"] {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            margin: 5px;
        }

        input[type="button"]:hover, input[type="submit"]:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
            box-shadow: var(--hover-shadow);
        }

        .full-name {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 1.1rem;
            margin-bottom: 12px;
        }

        .consultant {
            color: var(--secondary-color);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .consultant-label {
            font-weight: 600;
            min-width: 90px;
        }

        .search-count {
            color: var(--primary-color);
            font-weight: 600;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--secondary-color);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }

        .filter-section {
            background-color: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: var(--card-shadow);
            border: 1px solid #e9ecef;
        }

        .filter-section h4 {
            color: var(--primary-color);
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e9ecef;
        }

        .filter-row {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            margin: 5px 0;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .filter-row:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }

        .filter-row input[type="checkbox"] {
            margin-right: 12px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .filter-row label {
            margin: 0;
            cursor: pointer;
            user-select: none;
            color: var(--secondary-color);
            font-size: 0.95rem;
            flex-grow: 1;
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .filter-buttons input[type="button"],
        .filter-buttons input[type="submit"] {
            flex: 1;
            text-align: center;
        }
    </style>
    
  </head>

  
  <?php include("navbar.php"); ?>

  <br>
    <br>
    <br>
    <br>
    <br>
        
  <div id = "content">
    <div id = "contentFilter" >
        <h3>Filters</h3>
        <br>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET" id = "filters-form"> 
        <?php
        if ($typeAccount == 1) { 
        ?>
        <div class = "filterConsultants">
            <h4> Consultants </h4>
            <?php 
                $sql = "SELECT userId, fullName FROM users WHERE type = 0"; // iau consultantii
                $result = mysqli_query($link, $sql);
                $nConsultant = 0;
                while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <div class="filter-row">
                        <input type="checkbox" 
                               id="checkbox<?php echo $row['userId']; ?>" 
                               value="<?php echo $row['userId']; ?>" 
                               name="consultant[]" 
                               onchange="submitForm()"
                               <?php echo ($freqConsultant[$row['userId']] == 1) ? 'checked' : ''; ?>>
                        <label for="checkbox<?php echo $row['userId']; ?>"><?php echo $row['fullName']; ?></label>
                    </div>
                    <?php
                }
            ?>

        </div>
        <?php
        }
        ?>

        <div class="filterCountry">
            <h4>Country</h4>
            <?php
                $countries = ["USA", "The Netherlands", "UK", "Italy", "Spain", "Germany", "Ireland", "Switzerland", "France", "Belgium", "Other"];
                foreach ($countries as $country) {
            ?>
                <div class="filter-row">
                    <input type="checkbox"
                        id="checkboxCountry<?php echo $country; ?>"
                        value="<?php echo $country; ?>"
                        name="country[]"
                        onchange="submitForm()"
                        <?php echo ($freqCountry[$country] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxCountry<?php echo $country; ?>"><?php echo $country; ?></label>
                </div>
            <?php
                }
            ?>
        </div>

        <div class = "filterInstitutionType">
            <h4> Institution Type </h4>
            <div class="filter-row">
                <input type="checkbox" id="checkboxUniversity" value="0" name="institution[]" onchange="submitForm()" <?php echo ($freqInstitution['0'] == 1) ? 'checked' : ''; ?>>
                <label for="checkboxUniversity">University</label>
            </div>

            <div class="filter-row">
                <input type="checkbox" id="checkboxSummer" value="1" name="institution[]" onchange="submitForm()" <?php echo ($freqInstitution['1'] == 1) ? 'checked' : ''; ?>>
                <label for="checkboxSummer">Summer School</label>
            </div>

            <div class="filter-row">
                <input type="checkbox" id="checkboxBoarding" value="2" name="institution[]" onchange="submitForm()" <?php echo ($freqInstitution['2'] == 1) ? 'checked' : ''; ?>>
                <label for="checkboxBoarding">Boarding School</label>
            </div>
        </div>

        <div class = "filterComission">
            <h4> Commission </h4>
            <div class="filter-row">
                <input type="checkbox" id="checkboxComissionable" value="1" name="commission[]" onchange="submitForm()" <?php echo ($freqCommission[1] == 1) ? 'checked' : ''; ?>>
                <label for="checkboxComissionable">Commissionable</label>
            </div>

            <div class="filter-row">
                <input type="checkbox" id="checkboxNon-Comissionable" value="0" name="commission[]" onchange="submitForm()" <?php echo ($freqCommission[0] == 1) ? 'checked' : ''; ?>>
                <label for="checkboxNon-Comissionable">Non-Commissionable</label>
            </div>
        </div>

        <div class = "filterStatus">
            <h4> Status </h4>
            
            <div class="status-group">
                <h5 class="status-group-title">Active Applications</h5>
                <div class="filter-row">
                    <input type="checkbox" id="checkboxProgress" value="In progress" name="status[]" onchange="submitForm()" <?php echo ($freqStatus['In progress'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxProgress">In progress</label>
                </div>

                <div class="filter-row">
                    <input type="checkbox" id="checkboxSuggested" value="Suggested" name="status[]" onchange="submitForm()" <?php echo ($freqStatus['Suggested'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxSuggested">Suggested</label>
                </div>

                <div class="filter-row">
                    <input type="checkbox" id="checkboxNotInterested" value="Not Interested Anymore" name="status[]" onchange="submitForm()" <?php echo ($freqStatus['Not Interested Anymore'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxNotInterested">Not Interested Anymore</label>
                </div>
            </div>

            <div class="status-group">
                <h5 class="status-group-title">Final Status</h5>
                <div class="filter-row">
                    <input type="checkbox" id="checkboxAccepted" value="Accepted" name="status[]" onchange="submitForm()" <?php echo ($freqStatus['Accepted'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxAccepted">Accepted</label>
                </div>

                <div class="filter-row">
                    <input type="checkbox" id="checkboxEnrolled" value="Enrolled" name="status[]" onchange="submitForm()" <?php echo ($freqStatus['Enrolled'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxEnrolled">Enrolled</label>
                </div>

                <div class="filter-row">
                    <input type="checkbox" id="checkboxWaitlisted" value="Waitlisted" name="status[]" onchange="submitForm()" <?php echo ($freqStatus['Waitlisted'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxWaitlisted">Waitlisted</label>
                </div>

                <div class="filter-row">
                    <input type="checkbox" id="checkboxRejected" value="Rejected" name="status[]" onchange="submitForm()" <?php echo ($freqStatus['Rejected'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxRejected">Rejected</label>
                </div>
            </div>
        </div>

        
        <div class="filter-buttons">
            <input type="button" onclick="location.href='<?php echo $base_url; ?>applicationsList.php';" value="Reset">
            <input type="submit" value="Apply Filters">
        </div>
        </form>
    </div>  
    <div id = "contentStudents">
        <h1 style = "float: left;"> Applications List</h1>
        <br>
        <br>
        <br>
        <br>


        <!-- <input type="text" id="search-bar" onkeyup="searchFunction()" placeholder="Search for students.." title="Type in a name"> -->
        <ol id = "students-list" class="list-group list-group-numbered">
            <?php
            if ($typeAccount == 1) {
                $sqlApplication = "SELECT 
                    sd.consultantId,
                    sd.consultantName,
                    sd.name AS studentName,
                    u.commission AS commission,
                    u.universityCountry AS universityCountry,
                    u.universityName AS universityName,
                    u.institutionType AS institutionType,
                    aps.appStatus AS appStatus,
                    aps.applicationId AS applicationId,
                    aps.scholarship AS scholarship
                FROM 
                    applicationStatus aps
                INNER JOIN 
                    studentData sd ON aps.studentId = sd.studentId
                INNER JOIN 
                    universities u ON aps.universityId = u.universityId
                WHERE 
                    sd.consultantId IN " . $consultantString . "
                    AND aps.appStatus IN " . $statusString . "
                    AND u.institutionType IN " . $institutionString . "
                    AND u.commission IN " . $commissionString .  "
                    AND $countryCondition;";
            }
            else {
                $sqlApplication = "SELECT 
                    sd.consultantId,
                    sd.consultantName,
                    sd.name AS studentName,
                    u.commission AS commission,
                    u.universityCountry AS universityCountry,
                    u.universityName AS universityName,
                    u.institutionType AS institutionType,
                    aps.appStatus AS appStatus,
                    aps.applicationId AS applicationId,
                    aps.scholarship AS scholarship
                FROM 
                    applicationStatus aps
                INNER JOIN 
                    studentData sd ON aps.studentId = sd.studentId
                INNER JOIN 
                    universities u ON aps.universityId = u.universityId
                WHERE 
                    sd.consultantId = '$userId'
                    AND sd.consultantId IN " . $consultantString . "
                    AND aps.appStatus IN " . $statusString . "
                    AND u.institutionType IN " . $institutionString . "
                    AND u.commission IN " . $commissionString . "
                    AND $countryCondition;";
            }
            $queryApplication = mysqli_query($link, $sqlApplication);

            $noApplications = mysqli_num_rows($queryApplication);

            if (!isset($noApplications))
                $noApplications = 0;

            ?> <p style = "font-weight: bold;"> There are <span class = "search-count"><?php echo $noApplications; ?></span> applications in your search </p> <?php
            while ($row = mysqli_fetch_assoc($queryApplication)) {
              ?>
                <div class = "student">
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="full-name">Application from <?php echo $row['studentName'] . " to <br>" . $row['universityName'] . "(" . $row['universityCountry'] . ")"; ?></div>
                            <div class="consultant">
                                <span class="consultant-label">Consultant:</span>
                                <span><?php echo $row['consultantName'];?></span>
                                <span class="badge" style="background-color: <?php echo getStatusColor($row['appStatus']); ?> !important; color: white !important;">
                                    <?php echo $row['appStatus']; ?>
                                </span>
                            </div>
                            <?php if (trim($row['appStatus']) == "Accepted") { ?>
                                <div class="scholarship-info">
                                    <span class="consultant-label">Scholarship:</span>
                                    <span><?php echo $row['scholarship'] . "$"; ?></span>
                                </div>
                            <?php } ?>
                        </div>
                        <?php $urlApplication = "application.php?applicationId=" . $row['applicationId'];?>
                        <a href="<?php echo $urlApplication;?>">
                            <button type="button" class="btn btn-primary">View details</button>
                        </a>
                    </li>
                </div>
              <?php      
            }
            ?>

        </ol>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script>
        function searchFunction() {
            var input, filter, ul, li, a, i, txtValue;
            input = document.getElementById("search-bar");
            filter = input.value.toUpperCase();
            list = document.getElementById("students-list");
            students = list.getElementsByClassName("student");
            countDisplay = 0;
            for (i = 0; i < students.length; i++) {
                name1 = students[i].getElementsByClassName("full-name")[0].innerHTML;
                name2 = students[i].getElementsByClassName("highSchool")[0].innerHTML;
                name3 = students[i].getElementsByClassName("consultant")[0].innerHTML;

                name = name1 + name2 + name3;
                if (name.toUpperCase().indexOf(filter) > -1) {
                    students[i].style.display = "";
                    countDisplay++;
                } else {
                    students[i].style.display = "none";
                }
            }
            document.getElementsByClassName("search-count")[0].innerHTML = countDisplay;
        }

        function submitForm() {
            document.getElementById('filters-form').submit();
        }
    </script>
</body>
</html>