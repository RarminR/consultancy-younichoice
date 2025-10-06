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
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Design System CSS -->
    <link rel="stylesheet" href="student/design-system.css">
    <title>Applications List</title>

    <style>
        /* Applications List Design System Styles */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
        }

        #content {
            display: flex;
            gap: var(--spacing-xl);
            padding: var(--spacing-xl);
            max-width: 1400px;
            margin: 0 auto;
            min-height: calc(100vh - 120px);
        }

        #contentFilter {
            width: 320px;
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-xl);
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--light-gray);
            height: fit-content;
            position: sticky;
            top: var(--spacing-xl);
            max-height: calc(100vh - 140px);
            overflow-y: auto;
        }

        #contentStudents {
            flex: 1;
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-2xl);
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--light-gray);
            backdrop-filter: blur(10px);
        }

        .navbar {
            background: var(--white);
            box-shadow: var(--shadow-sm);
            border-bottom: 1px solid var(--light-gray);
        }

        h1 {
            color: var(--primary-color);
            font-size: var(--font-size-4xl);
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--spacing-xl);
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
        }

        h1::before {
            content: '\f0f6';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-2xl);
            color: var(--primary-color);
        }

        .search-container {
            margin-bottom: var(--spacing-xl);
            position: relative;
        }

        .search-bar {
            width: 100%;
            padding: var(--spacing-md) var(--spacing-lg) var(--spacing-md) 3rem;
            border: 2px solid var(--light-gray);
            border-radius: var(--border-radius-lg);
            font-size: var(--font-size-base);
            font-family: 'Poppins', sans-serif;
            transition: var(--transition-normal);
            background: var(--white);
            box-shadow: var(--shadow-sm);
        }

        .search-bar:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: var(--shadow-md);
        }

        .search-bar::placeholder {
            color: var(--secondary-color);
            font-style: italic;
        }

        .search-icon {
            position: absolute;
            left: var(--spacing-lg);
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
            font-size: var(--font-size-lg);
        }

        h3 {
            color: var(--primary-color);
            font-size: var(--font-size-2xl);
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--spacing-xl);
            padding-bottom: var(--spacing-md);
            border-bottom: 3px solid var(--primary-color);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        h3::before {
            content: '\f0b0';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-lg);
        }

        h4 {
            color: var(--secondary-color);
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-semibold);
            margin-bottom: var(--spacing-lg);
            padding-bottom: var(--spacing-sm);
            border-bottom: 2px solid var(--light-gray);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .filterConsultants h4::before {
            content: '\f007';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
        }

        .filterCountry h4::before {
            content: '\f57c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
        }

        .filterInstitutionType h4::before {
            content: '\f19c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
        }

        .filterComission h4::before {
            content: '\f3d1';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
        }

        .filterStatus h4::before {
            content: '\f0f3';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
        }

        .filter-section {
            background: var(--light-bg);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
            border: 1px solid var(--light-gray);
            transition: var(--transition-normal);
        }

        .filter-section:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .status-group {
            background: var(--light-bg);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
            border: 1px solid var(--light-gray);
            transition: var(--transition-normal);
        }

        .status-group:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .status-group-title {
            color: var(--primary-color);
            font-size: var(--font-size-base);
            font-weight: var(--font-weight-semibold);
            margin-bottom: var(--spacing-md);
            padding-bottom: var(--spacing-sm);
            border-bottom: 2px solid var(--light-gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Custom scrollbar for filter panel */
        #contentFilter::-webkit-scrollbar {
            width: 6px;
        }

        #contentFilter::-webkit-scrollbar-track {
            background: var(--light-bg);
            border-radius: var(--border-radius-sm);
        }

        #contentFilter::-webkit-scrollbar-thumb {
            background: var(--secondary-color);
            border-radius: var(--border-radius-sm);
        }

        #contentFilter::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }

        .filter-row {
            display: flex;
            align-items: center;
            padding: var(--spacing-sm) var(--spacing-md);
            margin: var(--spacing-xs) 0;
            border-radius: var(--border-radius-md);
            transition: var(--transition-normal);
            cursor: pointer;
        }

        .filter-row:hover {
            background: var(--light-bg);
            transform: translateX(var(--spacing-sm));
        }

        .filter-row input[type="checkbox"] {
            margin-right: var(--spacing-md);
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--primary-color);
        }

        .filter-row label {
            margin: 0;
            cursor: pointer;
            user-select: none;
            color: var(--text-color);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-medium);
            flex-grow: 1;
        }

        .list-group-item {
            margin-bottom: var(--spacing-md);
            border-radius: var(--border-radius-lg) !important;
            border: 1px solid var(--light-gray) !important;
            transition: var(--transition-normal);
            padding: var(--spacing-xl);
            background: var(--white);
        }

        .list-group-item:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-4px);
            border-color: var(--primary-color) !important;
        }

        .badge {
            padding: var(--spacing-xs) var(--spacing-sm);
            font-size: var(--font-size-xs);
            font-weight: var(--font-weight-semibold);
            border-radius: var(--border-radius-full);
            box-shadow: var(--shadow-sm);
            white-space: nowrap;
            color: var(--white) !important;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            padding: var(--spacing-sm) var(--spacing-lg);
            border-radius: var(--border-radius-lg);
            font-weight: var(--font-weight-semibold);
            color: var(--white);
            transition: var(--transition-normal);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-xs);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            color: var(--white);
            text-decoration: none;
        }

        .btn-primary::after {
            content: '\f061';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-xs);
        }

        input[type="button"], input[type="submit"] {
            background: var(--primary-gradient);
            color: var(--white);
            border: none;
            padding: var(--spacing-sm) var(--spacing-lg);
            border-radius: var(--border-radius-lg);
            cursor: pointer;
            transition: var(--transition-normal);
            font-weight: var(--font-weight-semibold);
            margin: var(--spacing-xs);
            font-family: 'Poppins', sans-serif;
        }

        input[type="button"]:hover, input[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        input[type="button"] {
            background: var(--secondary-gradient);
        }

        .full-name {
            font-weight: var(--font-weight-semibold);
            color: var(--primary-color);
            font-size: var(--font-size-lg);
            margin-bottom: var(--spacing-md);
            line-height: 1.4;
        }

        .consultant {
            color: var(--text-color);
            font-size: var(--font-size-sm);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-sm);
            flex-wrap: wrap;
        }

        .consultant-label {
            font-weight: var(--font-weight-semibold);
            min-width: 90px;
            color: var(--secondary-color);
        }

        .scholarship-info {
            color: var(--success-color);
            font-size: var(--font-size-sm);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            margin-top: var(--spacing-sm);
        }

        .scholarship-info .consultant-label {
            color: var(--success-color);
        }

        .search-count {
            color: var(--primary-color);
            font-weight: var(--font-weight-bold);
            font-size: var(--font-size-lg);
        }

        .filter-buttons {
            display: flex;
            gap: var(--spacing-sm);
            margin-top: var(--spacing-xl);
        }

        .filter-buttons input[type="button"],
        .filter-buttons input[type="submit"] {
            flex: 1;
            text-align: center;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--light-bg);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--secondary-color);
            border-radius: var(--border-radius-sm);
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            #content {
                flex-direction: column;
                padding: var(--spacing-md);
            }

            #contentFilter {
                width: 100%;
                position: static;
                margin-bottom: var(--spacing-lg);
            }

            h1 {
                font-size: var(--font-size-2xl);
            }

            .list-group-item {
                padding: var(--spacing-lg);
            }

            .consultant {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--spacing-xs);
            }
        }
    </style>
    
  </head>

  
  <?php include("navbar.php"); ?>

  <br>
    <br>
    <br>
    <br>
    <br>
        
  <div id="content">
    <div id="contentFilter">
        <h3>Filters</h3>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET" id="filters-form"> 
        <?php
        if ($typeAccount == 1) { 
        ?>
        <div class="filter-section filterConsultants">
            <h4>Consultants</h4>
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

        <div class="filter-section filterCountry">
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

        <div class="filter-section filterInstitutionType">
            <h4>Institution Type</h4>
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

        <div class="filter-section filterComission">
            <h4>Commission</h4>
            <div class="filter-row">
                <input type="checkbox" id="checkboxComissionable" value="1" name="commission[]" onchange="submitForm()" <?php echo ($freqCommission[1] == 1) ? 'checked' : ''; ?>>
                <label for="checkboxComissionable">Commissionable</label>
            </div>

            <div class="filter-row">
                <input type="checkbox" id="checkboxNon-Comissionable" value="0" name="commission[]" onchange="submitForm()" <?php echo ($freqCommission[0] == 1) ? 'checked' : ''; ?>>
                <label for="checkboxNon-Comissionable">Non-Commissionable</label>
            </div>
        </div>

        <div class="filter-section filterStatus">
            <h4>Status</h4>
            
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
    <div id="contentStudents">
        <h1>Applications List</h1>
        
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="search-bar" class="search-bar" onkeyup="searchFunction()" placeholder="Search for applications..." title="Type in a name">
        </div>

        <ol id="students-list" class="list-group list-group-numbered">
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

            ?> 
            <p style="font-weight: bold; margin-bottom: var(--spacing-xl);"> 
                There are <span class="search-count"><?php echo $noApplications; ?></span> applications in your search 
            </p> 
            <?php
            while ($row = mysqli_fetch_assoc($queryApplication)) {
              ?>
                <div class="student">
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="full-name">
                                Application from <?php echo $row['studentName'] . " to " . $row['universityName'] . " (" . $row['universityCountry'] . ")"; ?>
                            </div>
                            <div class="consultant">
                                <span class="consultant-label">Consultant:</span>
                                <span><?php echo $row['consultantName']; ?></span>
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
                        <?php $urlApplication = "application.php?applicationId=" . $row['applicationId']; ?>
                        <a href="<?php echo $urlApplication; ?>">
                            <button type="button" class="btn btn-primary">View Details</button>
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
                name3 = students[i].getElementsByClassName("consultant")[0].innerHTML;

                name = name1 + name3;
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