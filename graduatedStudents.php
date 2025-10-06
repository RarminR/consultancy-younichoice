<?php
    session_start();
    require_once "configDatabase.php";


    if (!isset($_SESSION['type'])) {
        header("location: index.php");
        die();
    }
    else {
        $typeAccount = $_SESSION["type"];
        $userId = $_SESSION["id"];
    }


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
    //echo $consultantString;
    // eu/us checkbox
    $packageType = $_GET['package'];

    $packageString = "('";
    $firstElem = 0;

    foreach ($packageType as $package) {
        if ($firstElem > 0)
            $packageString .= "','";
        $packageString .= $package;
        $freqPackage[$package] = 1;

        $firstElem += 1;
    }
    $packageString .= "')";

    if ($firstElem == 0) {
        // $freqPackage['EU'] = 1;
        // $freqPackage['US'] = 1;
        $packageString = "('EU', 'US', 'EUP', 'USP', 'USAP')";
    }

    // echo $packageString;
    // grade checkbox 
    $gradeType = $_GET['graduationYear'];

    $gradeString = "(";
    $firstElem = 0;

    foreach ($gradeType as $grade) {
        if ($firstElem > 0)
            $gradeString .= ',';
        $gradeString .= $grade;
        $freqGrade[$grade] = 1;

        $firstElem += 1;
    }
    $gradeString .= ')';

    if ($firstElem == 0) {
        $gradeString = "(2024, 2025, 2026, 2027, 2028, 2029)";
        // $freq[8] = 1;
        // $freq[9] = 1;
        // $freq[10] = 1;
        // $freq[11] = 1;
        // $freq[12] = 1;

    }
    // echo $gradeString;
    
    // interest field checkbox
    $interestFieldType = isset($_GET['interestField']) ? $_GET['interestField'] : array();
    
    $interestFieldString = "('";
    $firstElem = 0;
    
    foreach ($interestFieldType as $interestField) {
        if ($firstElem > 0)
            $interestFieldString .= "','";
        $interestFieldString .= addslashes($interestField);
        $freqInterestField[$interestField] = 1;
        
        $firstElem += 1;
    }
    $interestFieldString .= "')";
    
    if ($firstElem == 0) {
        $interestFieldString = "('Architecture', 'Art', 'Biotechnology', 'Business', 'Chemistry', 'Computer Science', 'Criminology', 'Culinary Arts', 'Economics', 'Film', 'History', 'Hospitality', 'International Relations', 'Law', 'Mathematics', 'Media / Journalism', 'Medicine', 'Music', 'Philosophy', 'Physics', 'Political Science', 'Psychology', 'Sustainability', 'Theatre', 'Undecided')";
    }
    // echo $interestFieldString;
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
    <title>Graduated Students List - Youni</title>

    <style>
        /* Graduated Students List Page Design System Styles */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
        }

        #content {
            max-width: 1400px;
            margin: 0 auto;
            padding: var(--spacing-2xl);
            min-height: calc(100vh - 120px);
            display: flex;
            gap: var(--spacing-2xl);
        }

        #contentFilter {
            width: 400px;
            background: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-xl);
            padding: var(--spacing-2xl);
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
            box-shadow: var(--shadow-xl);
            padding: var(--spacing-2xl);
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
        }

        .navbar {
            background: var(--white);
            box-shadow: var(--shadow-sm);
            border-bottom: 1px solid var(--light-gray);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-2xl);
            padding-bottom: var(--spacing-lg);
            border-bottom: 2px solid var(--light-gray);
        }

        h1 {
            color: var(--primary-color);
            font-weight: var(--font-weight-bold);
            font-size: var(--font-size-4xl);
            margin: 0;
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
        }

        h1::before {
            content: '\f19c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-2xl);
            color: var(--success-color);
        }

        h3 {
            color: var(--primary-color);
            font-weight: var(--font-weight-bold);
            font-size: var(--font-size-2xl);
            margin-bottom: var(--spacing-2xl);
            padding-bottom: var(--spacing-lg);
            border-bottom: 3px solid var(--primary-color);
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            text-align: center;
            justify-content: center;
        }

        h3::before {
            content: '\f0b0';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-xl);
            color: var(--primary-color);
        }

        h4 {
            color: var(--primary-color);
            font-weight: var(--font-weight-bold);
            font-size: var(--font-size-xl);
            margin: var(--spacing-2xl) 0 var(--spacing-lg);
            padding: var(--spacing-md) var(--spacing-lg);
            background: var(--primary-gradient);
            color: var(--white);
            border-radius: var(--border-radius-lg);
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: var(--shadow-md);
        }

        .consultants-label::before { content: '\f007'; }
        .package-label::before { content: '\f1b3'; }
        .grade-label::before { content: '\f073'; }

        .status-group {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-md);
            margin-bottom: var(--spacing-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--light-gray);
            transition: var(--transition-normal);
        }

        .status-group:hover {
            box-shadow: var(--shadow-md);
        }

        .status-group-title {
            color: var(--primary-color);
            font-size: var(--font-size-base);
            font-weight: var(--font-weight-semibold);
            margin-bottom: var(--spacing-sm);
            padding-bottom: var(--spacing-sm);
            border-bottom: 2px solid var(--light-gray);
        }

        .filter-row {
            display: flex;
            align-items: center;
            padding: var(--spacing-sm) var(--spacing-md);
            margin: var(--spacing-xs) 0;
            border-radius: var(--border-radius-lg);
            transition: var(--transition-normal);
            background: var(--white);
            border: 1px solid var(--light-gray);
        }

        .filter-row:hover {
            background: var(--light-bg);
            transform: translateX(5px);
            box-shadow: var(--shadow-sm);
        }

        .filter-row input[type="checkbox"] {
            margin-right: var(--spacing-sm);
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--primary-color);
        }

        .filter-row label {
            margin: 0;
            cursor: pointer;
            user-select: none;
            color: var(--secondary-color);
            font-size: var(--font-size-sm);
            flex-grow: 1;
            transition: var(--transition-normal);
        }

        .filter-row:hover label {
            color: var(--primary-color);
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            padding: var(--spacing-sm) var(--spacing-md);
            margin: var(--spacing-xs) 0;
            border-radius: var(--border-radius-lg);
            transition: var(--transition-normal);
            background: var(--white);
            border: 1px solid var(--light-gray);
        }

        .checkbox-container:hover {
            background: var(--light-bg);
            transform: translateX(5px);
            box-shadow: var(--shadow-sm);
        }

        .checkbox-container input[type="checkbox"] {
            margin-right: var(--spacing-sm);
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--primary-color);
        }

        .checkboxLabel {
            margin: 0;
            cursor: pointer;
            user-select: none;
            color: var(--secondary-color);
            font-size: var(--font-size-sm);
            flex-grow: 1;
            transition: var(--transition-normal);
        }

        .checkbox-container:hover .checkboxLabel {
            color: var(--primary-color);
        }

        .judet-tag {
            display: inline-flex;
            align-items: center;
            background: var(--white);
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-xs) var(--spacing-sm) var(--spacing-xs) var(--spacing-sm);
            margin: 2px var(--spacing-sm) 2px 0;
            font-size: var(--font-size-sm);
            color: var(--secondary-color);
            box-shadow: var(--shadow-sm);
            transition: var(--transition-normal);
        }
        .judet-tag .remove-judet {
            margin-left: var(--spacing-sm);
            color: var(--secondary-light);
            font-weight: var(--font-weight-bold);
            cursor: pointer;
            font-size: var(--font-size-lg);
            transition: var(--transition-normal);
        }
        .judet-tag .remove-judet:hover {
            color: var(--danger-color);
        }
        .judet-tag-row {
            display: flex;
            align-items: center;
            background: var(--white);
            border: 1px solid var(--light-gray);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-sm) var(--spacing-md);
            margin: var(--spacing-xs) 0;
            font-size: var(--font-size-base);
            font-weight: var(--font-weight-semibold);
            color: var(--secondary-color);
            box-shadow: none;
            transition: var(--transition-normal);
            position: relative;
        }
        .judet-tag-row .judet-checkbox {
            margin-right: var(--spacing-md);
            width: 18px;
            height: 18px;
            accent-color: var(--primary-color);
        }
        .judet-tag-row .remove-judet {
            margin-left: auto;
            color: var(--secondary-light);
            font-weight: var(--font-weight-bold);
            cursor: pointer;
            font-size: var(--font-size-lg);
            padding-left: var(--spacing-sm);
            transition: var(--transition-normal);
        }
        .judet-tag-row .remove-judet:hover {
            color: var(--danger-color);
        }
        .judet-tag-row.filter-row:hover {
            background: var(--light-bg);
            transform: translateX(5px);
            box-shadow: var(--shadow-sm);
            color: var(--primary-color);
        }

        .search-container {
            margin-bottom: var(--spacing-2xl);
            position: relative;
        }

        .search-bar {
            width: 100%;
            padding: var(--spacing-md) var(--spacing-lg) var(--spacing-md) var(--spacing-2xl);
            border: 2px solid var(--light-gray);
            border-radius: var(--border-radius-lg);
            font-size: var(--font-size-base);
            font-family: 'Poppins', sans-serif;
            transition: var(--transition-normal);
            background: var(--white);
        }

        .search-bar:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: var(--shadow-md);
        }

        .search-icon {
            position: absolute;
            left: var(--spacing-md);
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
            font-size: var(--font-size-lg);
            pointer-events: none;
        }

        .search-count {
            color: var(--primary-color);
            font-weight: var(--font-weight-semibold);
            font-size: var(--font-size-lg);
            margin-bottom: var(--spacing-xl);
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
            padding: var(--spacing-sm) var(--spacing-lg);
            background: var(--light-bg);
            border-radius: var(--border-radius-lg);
            border: 1px solid var(--light-gray);
        }

        .search-count::before {
            content: '\f19c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-base);
            color: var(--success-color);
        }

        .list-group-item {
            margin-bottom: var(--spacing-lg);
            border-radius: var(--border-radius-lg) !important;
            border: 1px solid var(--light-gray);
            transition: var(--transition-normal);
            padding: var(--spacing-xl);
            background: var(--white);
            box-shadow: var(--shadow-sm);
        }

        .list-group-item:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
            border-color: var(--primary-color);
        }

        .full-name {
            font-weight: var(--font-weight-semibold);
            color: var(--primary-color);
            font-size: var(--font-size-xl);
            margin-bottom: var(--spacing-md);
            line-height: 1.4;
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .full-name::before {
            content: '\f007';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-lg);
            color: var(--success-color);
        }

        .student-info {
            color: var(--secondary-color);
            font-size: var(--font-size-base);
            margin-bottom: var(--spacing-sm);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .student-info::before {
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-sm);
            color: var(--primary-color);
        }

        .highSchool::before { content: '\f19c'; }
        .email::before { content: '\f0e0'; }
        .consultant::before { content: '\f007'; }
        .grade::before { content: '\f3d1'; }
        .package::before { content: '\f1b3'; }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            padding: var(--spacing-sm) var(--spacing-lg);
            border-radius: var(--border-radius-lg);
            font-weight: var(--font-weight-semibold);
            transition: var(--transition-normal);
            color: var(--white);
            font-family: 'Poppins', sans-serif;
            font-size: var(--font-size-sm);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            opacity: 0.9;
        }

        .btn-primary::after {
            content: '\f061';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-sm);
        }

        .filter-buttons {
            display: flex;
            gap: var(--spacing-sm);
            margin-top: var(--spacing-lg);
        }

        .filter-buttons input[type="button"],
        .filter-buttons input[type="submit"] {
            flex: 1;
            text-align: center;
            background: var(--primary-gradient);
            color: var(--white);
            border: none;
            padding: var(--spacing-sm) var(--spacing-lg);
            border-radius: var(--border-radius-lg);
            cursor: pointer;
            transition: var(--transition-normal);
            font-weight: var(--font-weight-medium);
        }

        .filter-buttons input[type="button"]:hover,
        .filter-buttons input[type="submit"]:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        #filters-form input[type="button"],
        #filters-form input[type="submit"] {
            width: 100%;
            padding: var(--spacing-sm);
            margin: var(--spacing-xs) 0;
            border: none;
            border-radius: var(--border-radius-lg);
            font-weight: var(--font-weight-medium);
            cursor: pointer;
            transition: var(--transition-normal);
        }

        #filters-form input[type="button"] {
            background: var(--light-gray);
            color: var(--secondary-color);
        }

        #filters-form input[type="submit"] {
            background: var(--primary-gradient);
            color: var(--white);
        }

        #filters-form input[type="button"]:hover {
            background: var(--secondary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        #filters-form input[type="submit"]:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--light-gray);
            border-radius: var(--border-radius-sm);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--secondary-color);
            border-radius: var(--border-radius-sm);
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }

        /* Custom scrollbar for filters */
        #contentFilter::-webkit-scrollbar {
            width: 6px;
        }

        #contentFilter::-webkit-scrollbar-track {
            background: var(--light-gray);
            border-radius: var(--border-radius-sm);
        }

        #contentFilter::-webkit-scrollbar-thumb {
            background: var(--secondary-color);
            border-radius: var(--border-radius-sm);
        }

        #contentFilter::-webkit-scrollbar-thumb:hover {
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
            }

            #contentStudents {
                padding: var(--spacing-lg);
            }

            h1 {
                font-size: var(--font-size-2xl);
            }
        }
    </style>
    
    
  </head>

  
  <?php include("navbar.php"); ?>

  <div id="content">
    <div id="contentFilter">
        <h3>Filters</h3>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET" id="filters-form"> 
        <?php if ($typeAccount == 1) { ?>
        <div class="status-group">
            <h4 class="status-group-title">Consultants</h4>
            <?php 
                $sql = "SELECT userId, fullName FROM users WHERE type = 0"; // iau consultantii
                $result = mysqli_query($link, $sql);
                $nConsultant = 0;
                while ($row = mysqli_fetch_assoc($result)) {
                    // Hide consultants whose name ends with '(inactive)'
                    if (preg_match('/\(inactive\)$/i', trim($row['fullName']))) {
                        continue;
                    }
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


        <div class="status-group">
            <h4 class="status-group-title">Package Type</h4>
            <div class="filter-row">
                <input type="checkbox" id="checkboxEU" value="EU" name="package[]" onchange="submitForm()" <?php echo ($freqPackage['EU'] == 1) ? 'checked' : ''; ?>>
                <label for="checkboxEU">Europe</label>
            </div>

            <div class="filter-row">
                <input type="checkbox" id="checkboxEUP" value="EUP" name="package[]" onchange="submitForm()" <?php echo ($freqPackage['EUP'] == 1) ? 'checked' : ''; ?>>
                <label for="checkboxEUP">Europe Premium</label>
            </div>

            <div class="filter-row">
                <input type="checkbox" id="checkboxUS" value="US" name="package[]" onchange="submitForm()" <?php echo ($freqPackage['US'] == 1) ? 'checked' : ''; ?>>
                <label for="checkboxUS">USA</label>
            </div>

            <div class="filter-row">
                <input type="checkbox" id="checkboxUSP" value="USP" name="package[]" onchange="submitForm()" <?php echo ($freqPackage['USP'] == 1) ? 'checked' : ''; ?>>
                <label for="checkboxUSP">USA Premium</label>
            </div>

            <div class="filter-row">
                <input type="checkbox" id="checkboxUSAP" value="USAP" name="package[]" onchange="submitForm()" <?php echo ($freqPackage['USAP'] == 1) ? 'checked' : ''; ?>>
                <label for="checkboxUSAP">USA Advanced Package</label>
            </div>
        </div>

        <div class="status-group">
            <h4 class="status-group-title">Graduation Year</h4>

            <div class="filter-row">
                <input type="checkbox" id="checkboxGrade8" value="2024" name="graduationYear[]" onchange="submitForm()" <?php echo ($freqGrade[2024] == 1) ? 'checked' : ''; ?>>
                <label for="checkboxGrade8">2024</label>
            </div>

            <div class="filter-row">
                <input type="checkbox" id="checkboxGrade9" value="2025" name="graduationYear[]" onchange="submitForm()" <?php echo ($freqGrade[2025] == 1) ? 'checked' : ''; ?>>
                <label for="checkboxGrade9">2025</label>
            </div>

            <div class="filter-row">
                <input type="checkbox" id="checkboxGrade10" value="2026" name="graduationYear[]" onchange="submitForm()" <?php echo ($freqGrade[2026] == 1) ? 'checked' : ''; ?>>
                <label for="checkboxGrade10">2026</label>
            </div>

            <div class="filter-row">
                <input type="checkbox" id="checkboxGrade11" value="2027" name="graduationYear[]" onchange="submitForm()" <?php echo ($freqGrade[2027] == 1) ? 'checked' : ''; ?>>
                <label for="checkboxGrade11">2027</label>
            </div>

            <div class="filter-row">
                <input type="checkbox" id="checkboxGrade12" value="2028" name="graduationYear[]" onchange="submitForm()" <?php echo ($freqGrade[2028] == 1) ? 'checked' : ''; ?>>
                <label for="checkboxGrade12">2028</label>
            </div>

            <div class="filter-row">
                <input type="checkbox" id="checkboxBachelor" value="2029" name="graduationYear[]" onchange="submitForm()" <?php echo ($freqGrade[2029] == 1) ? 'checked' : ''; ?>>
                <label for="checkboxBachelor">2029</label>
            </div>
        </div>
        
        <div class="status-group">
            <h4 class="status-group-title">Location</h4>
            <?php
            // Get unique judet values from studentData
            $judetOptions = array();
            $sqlJudet = "SELECT DISTINCT judet FROM studentData WHERE judet IS NOT NULL AND judet != ''";
            $resultJudet = mysqli_query($link, $sqlJudet);
            while ($row = mysqli_fetch_assoc($resultJudet)) {
                $judetOptions[] = $row['judet'];
            }
            ?>
            <div id="judet-autocomplete-container">
                <input type="text" id="judet-autocomplete" placeholder="Type to search location..." autocomplete="off" style="width:100%;padding:10px;border-radius:6px;border:1px solid #e9ecef;">
                <div id="judet-suggestions" style="position:relative;"></div>
                <div id="judet-tags" style="margin-top:10px;"></div>
            </div>
            <!-- Hidden judet[] inputs will be appended here -->
        </div>
        
        <div class="status-group">
            <h4 class="status-group-title" onclick="toggleInterestSection()" style="cursor: pointer;">
                Field of Interest <span id="interestArrow" style="float: right;">▼</span>
            </h4>
            <div id="selectedInterestsDisplay" style="color: #6c757d; font-size: 14px; margin: 5px 0; display: none;"></div>
            <div id="interestSection" style="display: none;">
                <div class="filter-row">
                    <input type="checkbox" id="checkboxArchitecture" value="Architecture" name="interestField[]" onchange="submitForm(); updateSelectedInterestsDisplay();" <?php echo (isset($freqInterestField['Architecture']) && $freqInterestField['Architecture'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxArchitecture">Architecture</label>
                </div>
                <div class="filter-row">
                    <input type="checkbox" id="checkboxArt" value="Art" name="interestField[]" onchange="submitForm(); updateSelectedInterestsDisplay();" <?php echo (isset($freqInterestField['Art']) && $freqInterestField['Art'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxArt">Art</label>
                </div>
                <div class="filter-row">
                    <input type="checkbox" id="checkboxBiotechnology" value="Biotechnology" name="interestField[]" onchange="submitForm(); updateSelectedInterestsDisplay();" <?php echo (isset($freqInterestField['Biotechnology']) && $freqInterestField['Biotechnology'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxBiotechnology">Biotechnology</label>
                </div>
                <div class="filter-row">
                    <input type="checkbox" id="checkboxBusiness" value="Business" name="interestField[]" onchange="submitForm(); updateSelectedInterestsDisplay();" <?php echo (isset($freqInterestField['Business']) && $freqInterestField['Business'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxBusiness">Business</label>
                </div>
                <div class="filter-row">
                    <input type="checkbox" id="checkboxChemistry" value="Chemistry" name="interestField[]" onchange="submitForm(); updateSelectedInterestsDisplay();" <?php echo (isset($freqInterestField['Chemistry']) && $freqInterestField['Chemistry'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxChemistry">Chemistry</label>
                </div>
                <div class="filter-row">
                    <input type="checkbox" id="checkboxComputerScience" value="Computer Science" name="interestField[]" onchange="submitForm(); updateSelectedInterestsDisplay();" <?php echo (isset($freqInterestField['Computer Science']) && $freqInterestField['Computer Science'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxComputerScience">Computer Science</label>
                </div>
                <div class="filter-row">
                    <input type="checkbox" id="checkboxCriminology" value="Criminology" name="interestField[]" onchange="submitForm(); updateSelectedInterestsDisplay();" <?php echo (isset($freqInterestField['Criminology']) && $freqInterestField['Criminology'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxCriminology">Criminology</label>
                </div>
                <div class="filter-row">
                    <input type="checkbox" id="checkboxCulinaryArts" value="Culinary Arts" name="interestField[]" onchange="submitForm(); updateSelectedInterestsDisplay();" <?php echo (isset($freqInterestField['Culinary Arts']) && $freqInterestField['Culinary Arts'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxCulinaryArts">Culinary Arts</label>
                </div>
                <div class="filter-row">
                    <input type="checkbox" id="checkboxEconomics" value="Economics" name="interestField[]" onchange="submitForm(); updateSelectedInterestsDisplay();" <?php echo (isset($freqInterestField['Economics']) && $freqInterestField['Economics'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxEconomics">Economics</label>
                </div>
                <div class="filter-row">
                    <input type="checkbox" id="checkboxFilm" value="Film" name="interestField[]" onchange="submitForm(); updateSelectedInterestsDisplay();" <?php echo (isset($freqInterestField['Film']) && $freqInterestField['Film'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxFilm">Film</label>
                </div>
                <div class="filter-row">
                    <input type="checkbox" id="checkboxHistory" value="History" name="interestField[]" onchange="submitForm(); updateSelectedInterestsDisplay();" <?php echo (isset($freqInterestField['History']) && $freqInterestField['History'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxHistory">History</label>
                </div>
                <div class="filter-row">
                    <input type="checkbox" id="checkboxHospitality" value="Hospitality" name="interestField[]" onchange="submitForm(); updateSelectedInterestsDisplay();" <?php echo (isset($freqInterestField['Hospitality']) && $freqInterestField['Hospitality'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxHospitality">Hospitality</label>
                </div>
                <div class="filter-row">
                    <input type="checkbox" id="checkboxInternationalRelations" value="International Relations" name="interestField[]" onchange="submitForm(); updateSelectedInterestsDisplay();" <?php echo (isset($freqInterestField['International Relations']) && $freqInterestField['International Relations'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxInternationalRelations">International Relations</label>
                </div>
                <div class="filter-row">
                    <input type="checkbox" id="checkboxLaw" value="Law" name="interestField[]" onchange="submitForm(); updateSelectedInterestsDisplay();" <?php echo (isset($freqInterestField['Law']) && $freqInterestField['Law'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxLaw">Law</label>
                </div>
                <div class="filter-row">
                    <input type="checkbox" id="checkboxMathematics" value="Mathematics" name="interestField[]" onchange="submitForm(); updateSelectedInterestsDisplay();" <?php echo (isset($freqInterestField['Mathematics']) && $freqInterestField['Mathematics'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxMathematics">Mathematics</label>
                </div>
                <div class="filter-row">
                    <input type="checkbox" id="checkboxMediaJournalism" value="Media / Journalism" name="interestField[]" onchange="submitForm(); updateSelectedInterestsDisplay();" <?php echo (isset($freqInterestField['Media / Journalism']) && $freqInterestField['Media / Journalism'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxMediaJournalism">Media / Journalism</label>
                </div>
                <div class="filter-row">
                    <input type="checkbox" id="checkboxMedicine" value="Medicine" name="interestField[]" onchange="submitForm(); updateSelectedInterestsDisplay();" <?php echo (isset($freqInterestField['Medicine']) && $freqInterestField['Medicine'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxMedicine">Medicine</label>
                </div>
                <div class="filter-row">
                    <input type="checkbox" id="checkboxMusic" value="Music" name="interestField[]" onchange="submitForm(); updateSelectedInterestsDisplay();" <?php echo (isset($freqInterestField['Music']) && $freqInterestField['Music'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxMusic">Music</label>
                </div>
                <div class="filter-row">
                    <input type="checkbox" id="checkboxPhilosophy" value="Philosophy" name="interestField[]" onchange="submitForm(); updateSelectedInterestsDisplay();" <?php echo (isset($freqInterestField['Philosophy']) && $freqInterestField['Philosophy'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxPhilosophy">Philosophy</label>
                </div>
                <div class="filter-row">
                    <input type="checkbox" id="checkboxPhysics" value="Physics" name="interestField[]" onchange="submitForm(); updateSelectedInterestsDisplay();" <?php echo (isset($freqInterestField['Physics']) && $freqInterestField['Physics'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxPhysics">Physics</label>
                </div>
                <div class="filter-row">
                    <input type="checkbox" id="checkboxPoliticalScience" value="Political Science" name="interestField[]" onchange="submitForm(); updateSelectedInterestsDisplay();" <?php echo (isset($freqInterestField['Political Science']) && $freqInterestField['Political Science'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxPoliticalScience">Political Science</label>
                </div>
                <div class="filter-row">
                    <input type="checkbox" id="checkboxPsychology" value="Psychology" name="interestField[]" onchange="submitForm(); updateSelectedInterestsDisplay();" <?php echo (isset($freqInterestField['Psychology']) && $freqInterestField['Psychology'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxPsychology">Psychology</label>
                </div>
                <div class="filter-row">
                    <input type="checkbox" id="checkboxSustainability" value="Sustainability" name="interestField[]" onchange="submitForm(); updateSelectedInterestsDisplay();" <?php echo (isset($freqInterestField['Sustainability']) && $freqInterestField['Sustainability'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxSustainability">Sustainability</label>
                </div>
                <div class="filter-row">
                    <input type="checkbox" id="checkboxTheatre" value="Theatre" name="interestField[]" onchange="submitForm(); updateSelectedInterestsDisplay();" <?php echo (isset($freqInterestField['Theatre']) && $freqInterestField['Theatre'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxTheatre">Theatre</label>
                </div>
                <div class="filter-row">
                    <input type="checkbox" id="checkboxUndecided" value="Undecided" name="interestField[]" onchange="submitForm(); updateSelectedInterestsDisplay();" <?php echo (isset($freqInterestField['Undecided']) && $freqInterestField['Undecided'] == 1) ? 'checked' : ''; ?>>
                    <label for="checkboxUndecided">Undecided</label>
                </div>
            </div>
        </div>
        
        <div class="filter-buttons">
            <input type="button" onclick="location.href='<?php echo $base_url; ?>graduatedStudents.php';" value="Reset" />
            <input type="submit" value="Apply Filters">
        </div>
        </form>
    </div>  
    
    <div id="contentStudents">
        <div class="page-header">
            <h1>Graduated Students List</h1>
        </div>

        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="search-bar" class="search-bar" onkeyup="searchFunction()" placeholder="Search for graduated students..." title="Type in a name">
        </div>

        <p>Found <span class="search-count"><?php echo $noStudents; ?></span> graduated students</p>

        <ol id="students-list" class="list-group list-group-numbered">
        <?php
            // Add after $gradeString logic, before HTML output
            // Build $judetVariants and $freqJudet for sticky tags and SQL filter
            $judetFilter = isset($_GET['judet']) ? $_GET['judet'] : array();
            $judetVariants = array();
            $freqJudet = array();
            if (!empty($judetFilter)) {
                foreach ($judetFilter as $j) {
                    $freqJudet[$j] = 1;
                    $judetVariants[] = addslashes($j);
                    // Also add diacritic-insensitive version if different
                    $noDiacritics = removeDiacritics($j);
                    if ($noDiacritics !== $j) {
                        $judetVariants[] = addslashes($noDiacritics);
                    }
                }
            }
            // In the SQL query for students, add filtering by judet if set
            if ($typeAccount == 1) {
                $sqlStudent = "SELECT * FROM studentData WHERE `consultantId` IN " . $consultantString ." AND `graduationYear` IN ". $gradeString ." AND `packageType` IN " . $packageString . " AND `activityStatus` = 1";
                if (!empty($judetVariants)) {
                    $judetSqlList = "('" . implode("','", $judetVariants) . "')";
                    $sqlStudent .= " AND `judet` IN $judetSqlList";
                }
                // Only add interest filter if specific fields are selected
                if (isset($_GET['interestField']) && !empty($_GET['interestField'])) {
                    $sqlStudent .= " AND `interest` IN " . $interestFieldString;
                }
            } else {
                $sqlStudent = "SELECT * FROM studentData WHERE `consultantId` IN " . $consultantString ." AND `graduationYear` IN ". $gradeString ." AND `packageType` IN " .$packageString . " AND `consultantId` = '$userId' AND `activityStatus` = 1";
                if (!empty($judetVariants)) {
                    $judetSqlList = "('" . implode("','", $judetVariants) . "')";
                    $sqlStudent .= " AND `judet` IN $judetSqlList";
                }
                // Only add interest filter if specific fields are selected
                if (isset($_GET['interestField']) && !empty($_GET['interestField'])) {
                    $sqlStudent .= " AND `interest` IN " . $interestFieldString;
                }
            }
            $queryStudent = mysqli_query($link, $sqlStudent);

            $noStudents = mysqli_num_rows($queryStudent);

            if (!isset($noStudents))
                $noStudents = 0;

            while ($row = mysqli_fetch_assoc($queryStudent)) {
              ?>
                <div class="student">
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="full-name"><?php echo htmlspecialchars($row['name']); ?></div>
                            
                            <p class="student-info highSchool">
                                <strong>High School:</strong> <?php echo htmlspecialchars($row['highSchool']); ?>
                            </p>
                            
                            <p class="student-info email">
                                <strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?>
                            </p>
                            
                            <p class="student-info consultant">
                                <strong>Consultant:</strong> <?php echo htmlspecialchars($row['consultantName']); ?>
                            </p>
                            
                            <?php if ($row['grade'] <= 12) { ?>
                                <p class="student-info grade">
                                    <strong>Grade:</strong> <?php echo htmlspecialchars($row['grade']); ?>
                                </p>
                            <?php } else { ?>
                                <p class="student-info grade">
                                    <strong>Grade:</strong> Bachelor
                                </p>
                            <?php } ?>
                            
                            <p class="student-info grade">
                                <strong>Graduation Year:</strong> <?php echo htmlspecialchars($row['graduationYear']); ?>
                            </p>
                            
                            <?php if ($row['grade'] <= 12) { ?>
                                <p class="student-info grade">
                                    <strong>Start Grade:</strong> <?php echo htmlspecialchars($row['signGrade']); ?>
                                </p>
                            <?php } else { ?>
                                <p class="student-info grade">
                                    <strong>Start Grade:</strong> Bachelor
                                </p>
                            <?php } ?>

                            <p class="student-info package">
                                <strong>Package Type:</strong> <?php echo htmlspecialchars($row['packageType']); ?>
                            </p>
                        </div>
                        
                        <?php 
                        $urlStudent = "student.php?studentId=" . $row['studentId'];
                        ?>
                        <a href="<?php echo $urlStudent; ?>">
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

        // Diacritic removal (same as PHP)
        function removeDiacritics(str) {
            const diacritics = {
                'ă': 'a', 'â': 'a', 'î': 'i', 'ș': 's', 'ş': 's', 'ț': 't', 'ţ': 't',
                'Ă': 'A', 'Â': 'A', 'Î': 'I', 'Ș': 'S', 'Ş': 'S', 'Ț': 'T', 'Ţ': 'T',
                'é': 'e', 'É': 'E', 'ó': 'o', 'Ó': 'O', 'í': 'i', 'Í': 'I', 'ú': 'u', 'Ú': 'U',
                'ü': 'u', 'Ü': 'U', 'ö': 'o', 'Ö': 'O', 'ő': 'o', 'Ő': 'O', 'ű': 'u', 'Ű': 'U',
                'á': 'a', 'Á': 'A'
            };
            return str.replace(/[ăâîșşțţĂÂÎȘŞȚŢéÉóÓíÍúÚüÜöÖőŐűŰáÁ]/g, m => diacritics[m] || m);
        }
        // Judet options from PHP
        const judetOptions = <?php echo json_encode($judetOptions); ?>;
        // Get selected judets from GET (for sticky tags)
        const selectedJudets = <?php echo json_encode(isset($judetFilter) ? $judetFilter : []); ?>;
        // Elements
        const input = document.getElementById('judet-autocomplete');
        const suggestions = document.getElementById('judet-suggestions');
        const tagsContainer = document.getElementById('judet-tags');
        const form = document.getElementById('filters-form');
        // Helper: create tag
        function createTag(value) {
            const row = document.createElement('div');
            row.className = 'judet-tag-row filter-row';
            // Label
            const label = document.createElement('span');
            label.textContent = value;
            label.style.fontWeight = '600';
            row.appendChild(label);
            // Remove button
            const removeBtn = document.createElement('span');
            removeBtn.className = 'remove-judet';
            removeBtn.textContent = '×';
            removeBtn.title = 'Remove';
            removeBtn.onclick = function() {
                row.remove();
                // Remove hidden input
                const hidden = form.querySelector('input[type="hidden"][name="judet[]"][value="'+value.replace(/"/g,'\\"')+'"]');
                if (hidden) hidden.remove();
                // Auto-submit form
                submitForm();
            };
            row.appendChild(removeBtn);
            return row;
        }
        // Helper: add tag and hidden input
        function addJudet(value, autoSubmit = true) {
            value = value.trim();
            if (!value) return;
            // Prevent duplicates
            if (Array.from(form.querySelectorAll('input[type="hidden"][name="judet[]"]')).some(i => i.value === value)) return;
            // Tag
            const tag = createTag(value);
            tagsContainer.appendChild(tag);
            // Hidden input
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'judet[]';
            hidden.value = value;
            form.appendChild(hidden);
            // Auto-submit form only if not loading existing tags
            if (autoSubmit) {
                submitForm();
            }
        }
        // Autocomplete logic
        input.addEventListener('input', function() {
            const val = removeDiacritics(this.value.toLowerCase());
            suggestions.innerHTML = '';
            if (!val) return;
            // Filter options (case and diacritic insensitive)
            const matches = judetOptions.filter(j => removeDiacritics(j.toLowerCase()).includes(val));
            if (matches.length === 0) return;
            const list = document.createElement('div');
            list.style = 'position:absolute;z-index:10;background:white;border:1px solid #e9ecef;width:100%;border-radius:0 0 6px 6px;max-height:180px;overflow-y:auto;';
            matches.forEach(j => {
                const item = document.createElement('div');
                item.textContent = j;
                item.style = 'padding:8px 12px;cursor:pointer;';
                item.onmousedown = function(e) { // use onmousedown to avoid blur
                    e.preventDefault();
                    addJudet(j);
                    input.value = '';
                    suggestions.innerHTML = '';
                };
                list.appendChild(item);
            });
            suggestions.appendChild(list);
        });
        // Enter key: add as tag (free text or suggestion)
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const val = this.value.trim();
                if (val) {
                    addJudet(val);
                    this.value = '';
                    suggestions.innerHTML = '';
                }
            }
        });
        // Blur: hide suggestions after short delay
        input.addEventListener('blur', function() {
            setTimeout(() => { suggestions.innerHTML = ''; }, 100);
        });
        // On page load: show sticky tags and initialize interest display
        window.addEventListener('DOMContentLoaded', function() {
            if (selectedJudets && selectedJudets.length) {
                selectedJudets.forEach(j => addJudet(j, false));
            }
            updateSelectedInterestsDisplay();
        });

        // Interest Field Toggle Function
        function toggleInterestSection() {
            const section = document.getElementById('interestSection');
            const arrow = document.getElementById('interestArrow');
            const display = document.getElementById('selectedInterestsDisplay');
            
            if (section.style.display === 'none') {
                section.style.display = 'block';
                display.style.display = 'none';
                arrow.textContent = '▲';
            } else {
                section.style.display = 'none';
                arrow.textContent = '▼';
                updateSelectedInterestsDisplay();
            }
        }

        // Update selected interests display
        function updateSelectedInterestsDisplay() {
            const checkboxes = document.querySelectorAll('input[name="interestField[]"]:checked');
            const display = document.getElementById('selectedInterestsDisplay');
            
            if (checkboxes.length === 0) {
                display.style.display = 'none';
            } else {
                const selectedValues = Array.from(checkboxes).map(cb => cb.value);
                display.textContent = selectedValues.join(', ');
                display.style.display = 'block';
            }
        }
    </script>
</body>
</html>