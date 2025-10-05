<?php
    session_start();

    if (!isset($_SESSION['type'])) {
        header("location: index.php");
        die();
    }
    else {
        $typeAccount = $_SESSION["type"];
        $userId = $_SESSION["id"];
    }

?>

<?php 
    require_once "configDatabase.php";
    
    if (!function_exists('removeDiacritics')) {
        function removeDiacritics($string) {
            $diacritics = [
                'ă' => 'a', 'â' => 'a', 'î' => 'i', 'ș' => 's', 'ş' => 's', 'ț' => 't', 'ţ' => 't',
                'Ă' => 'A', 'Â' => 'A', 'Î' => 'I', 'Ș' => 'S', 'Ş' => 'S', 'Ț' => 'T', 'Ţ' => 'T',
                'é' => 'e', 'É' => 'E', 'ó' => 'o', 'Ó' => 'O', 'í' => 'i', 'Í' => 'I', 'ú' => 'u', 'Ú' => 'U',
                'ü' => 'u', 'Ü' => 'U', 'ö' => 'o', 'Ö' => 'O', 'ő' => 'o', 'Ő' => 'O', 'ű' => 'u', 'Ű' => 'U',
                'á' => 'a', 'Á' => 'A'
            ];
            return strtr($string, $diacritics);
        }
    }
?>

<?php // GRADE CALCULATION FUNCTION
    function calculateCurrentGrade($dataStudent) {
        $currentYear = date('Y');
        $currentMonth = date('n'); // 1-12
        $currentDay = date('j'); // 1-31
        
        // Check if student is bachelor (isMaster = 1)
        if (isset($dataStudent['isMaster']) && $dataStudent['isMaster'] == 1) {
            // Bachelor student - show contract end year
            return "Bachelor Student";
        } else {
            // Non-bachelor student - calculate grade based on graduation year
            $graduationYear = $dataStudent['graduationYear'];
            
            // Calculate base grade (12 - years until graduation)
            $yearsUntilGraduation = $graduationYear - $currentYear;
            $baseGrade = 12 - $yearsUntilGraduation;
            
            // Adjust grade based on current date
            if ($currentMonth >= 6 && $currentMonth <= 9) {
                // June 1 - September 15: upcoming year
                if ($currentMonth == 6 || ($currentMonth == 9 && $currentDay <= 15)) {
                    $calculatedGrade = $baseGrade + 1;
                    return "Grade " . $calculatedGrade . " (upcoming year)";
                } else {
                    $calculatedGrade = $baseGrade + 1;
                    return "Grade " . $calculatedGrade;
                }
            } elseif ($currentMonth >= 10 || $currentMonth <= 12) {
                // September 16 - December 31: current year
                $calculatedGrade = $baseGrade + 1;
                return "Grade " . $calculatedGrade;
            } else {
                // January 1 - May 31: current year
                $calculatedGrade = $baseGrade;
                return "Grade " . $calculatedGrade;
            }
        }
    }
    
    // Function to get numeric grade for filtering
    function getNumericGrade($dataStudent) {
        $currentYear = date('Y');
        $currentMonth = date('n'); // 1-12
        $currentDay = date('j'); // 1-31
        
        // Check if student is bachelor (isMaster = 1)
        if (isset($dataStudent['isMaster']) && $dataStudent['isMaster'] == 1) {
            return 13; // Bachelor = 13 for filtering
        } else {
            // Non-bachelor student - calculate grade based on graduation year
            $graduationYear = $dataStudent['graduationYear'];
            
            // Calculate base grade (12 - years until graduation)
            $yearsUntilGraduation = $graduationYear - $currentYear;
            $baseGrade = 12 - $yearsUntilGraduation;
            
            // Adjust grade based on current date
            if ($currentMonth >= 6 && $currentMonth <= 9) {
                // June 1 - September 15: upcoming year
                if ($currentMonth == 6 || ($currentMonth == 9 && $currentDay <= 15)) {
                    return $baseGrade + 1;
                } else {
                    return $baseGrade + 1;
                }
            } elseif ($currentMonth >= 10 || $currentMonth <= 12) {
                // September 16 - December 31: current year
                return $baseGrade + 1;
            } else {
                // January 1 - May 31: current year
                return $baseGrade;
            }
        }
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
    $gradeType = $_GET['grade'];

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
        $gradeString = "(8, 9, 10, 11, 12, 13)";
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
    <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
    <title>Students List </title>

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

        #content {
            display: flex;
            gap: 20px;
            padding: 20px;
            max-width: 1600px;
            margin: 0 auto;
        }

        #contentFilter {
            width: 300px;
            flex-shrink: 0;
            position: sticky;
            top: 100px;
            max-height: calc(100vh - 140px);
            overflow-y: auto;
            background-color: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            padding: 20px;
        }

        #contentStudents {
            flex-grow: 1;
            background-color: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            padding: 20px;
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

        #search-bar {
            width: 100%;
            padding: 12px 20px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: var(--card-shadow);
        }

        #search-bar:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .list-group-item {
            margin-bottom: 15px;
            border-radius: 8px !important;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            padding: 20px;
        }

        .list-group-item:hover {
            box-shadow: var(--hover-shadow);
            transform: translateY(-2px);
        }

        .full-name {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 1.2rem;
            margin-bottom: 15px;
        }

        .student-info {
            color: var(--secondary-color);
            font-size: 0.95rem;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-label {
            font-weight: 600;
            min-width: 120px;
            color: var(--primary-color);
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

        .filter-row {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            margin: 5px 0;
            border-radius: 6px;
            transition: all 0.2s ease;
            background-color: white;
            border: 1px solid #e9ecef;
        }

        .filter-row:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
            box-shadow: var(--card-shadow);
        }

        .filter-row input[type="checkbox"] {
            margin-right: 12px;
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
            font-size: 0.95rem;
            flex-grow: 1;
            transition: color 0.2s ease;
        }

        .filter-row:hover label {
            color: var(--primary-color);
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
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .filter-buttons input[type="button"]:hover,
        .filter-buttons input[type="submit"]:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
            box-shadow: var(--hover-shadow);
        }

        .search-count {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 20px;
        }

        /* Custom scrollbar for filters */
        #contentFilter::-webkit-scrollbar {
            width: 8px;
        }

        #contentFilter::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        #contentFilter::-webkit-scrollbar-thumb {
            background: var(--secondary-color);
            border-radius: 4px;
        }

        #contentFilter::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }

        /* Update the filter sections */
        .filterConsultants,
        .filterPackage,
        .filterGrade {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }

        .filterConsultants h4,
        .filterPackage h4,
        .filterGrade h4 {
            color: var(--primary-color);
            font-size: 1.1rem;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--primary-color);
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            margin: 5px 0;
            border-radius: 6px;
            transition: all 0.2s ease;
            background-color: white;
            border: 1px solid #e9ecef;
        }

        .checkbox-container:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
            box-shadow: var(--card-shadow);
        }

        .checkbox-container input[type="checkbox"] {
            margin-right: 12px;
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
            font-size: 0.95rem;
            flex-grow: 1;
            transition: color 0.2s ease;
        }

        .checkbox-container:hover .checkboxLabel {
            color: var(--primary-color);
        }

        /* Update the form buttons */
        #filters-form input[type="button"],
        #filters-form input[type="submit"] {
            width: 100%;
            padding: 12px;
            margin: 5px 0;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        #filters-form input[type="button"] {
            background-color: #e9ecef;
            color: var(--secondary-color);
        }

        #filters-form input[type="submit"] {
            background-color: var(--accent-color);
            color: white;
        }

        #filters-form input[type="button"]:hover {
            background-color: #dee2e6;
            transform: translateY(-2px);
            box-shadow: var(--card-shadow);
        }

        #filters-form input[type="submit"]:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
            box-shadow: var(--hover-shadow);
        }
        .judet-tag {
            display: inline-flex;
            align-items: center;
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 7px 14px 7px 12px;
            margin: 2px 6px 2px 0;
            font-size: 0.97em;
            color: var(--secondary-color);
            box-shadow: var(--card-shadow);
            transition: box-shadow 0.2s;
        }
        .judet-tag .remove-judet {
            margin-left: 10px;
            color: #888;
            font-weight: bold;
            cursor: pointer;
            font-size: 1.1em;
            transition: color 0.2s;
        }
        .judet-tag .remove-judet:hover {
            color: #c00;
        }
        .judet-tag-row {
            display: flex;
            align-items: center;
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 16px;
            margin: 5px 0;
            font-size: 1rem;
            font-weight: 600;
            color: var(--secondary-color);
            box-shadow: none;
            transition: box-shadow 0.2s, transform 0.2s, color 0.2s, background-color 0.2s;
            position: relative;
        }
        .judet-tag-row .judet-checkbox {
            margin-right: 16px;
            width: 18px;
            height: 18px;
            accent-color: var(--primary-color);
        }
        .judet-tag-row .remove-judet {
            margin-left: auto;
            color: #888;
            font-weight: bold;
            cursor: pointer;
            font-size: 1.2em;
            padding-left: 12px;
            transition: color 0.2s;
        }
        .judet-tag-row .remove-judet:hover {
            color: #c00;
        }
        .judet-tag-row.filter-row:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
            box-shadow: var(--card-shadow);
            color: var(--primary-color);
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
        <div class = "status-group">
            <h4 class = "status-group-title">Consultants</h4>
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

        <div class = "status-group">
            <h4 class = "status-group-title">Package Type</h4>
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

        <div class = "status-group">
            <h4 class = "status-group-title">Student's Grade</h4>
            <div class="filter-row">
                <input type="checkbox" id="checkboxGrade8" value="8" name="grade[]" onchange="submitForm()" <?php echo ($freqGrade[8] == 1) ? 'checked' : ''; ?>>
                <label for="checkboxGrade8">8</label>
            </div>

            <div class="filter-row">
                <input type="checkbox" id="checkboxGrade9" value="9" name="grade[]" onchange="submitForm()" <?php echo ($freqGrade[9] == 1) ? 'checked' : ''; ?>>
                <label for="checkboxGrade9">9</label>
            </div>

            <div class="filter-row">
                <input type="checkbox" id="checkboxGrade10" value="10" name="grade[]" onchange="submitForm()" <?php echo ($freqGrade[10] == 1) ? 'checked' : ''; ?>>
                <label for="checkboxGrade10">10</label>
            </div>

            <div class="filter-row">
                <input type="checkbox" id="checkboxGrade11" value="11" name="grade[]" onchange="submitForm()" <?php echo ($freqGrade[11] == 1) ? 'checked' : ''; ?>>
                <label for="checkboxGrade11">11</label>
            </div>

            <div class="filter-row">
                <input type="checkbox" id="checkboxGrade12" value="12" name="grade[]" onchange="submitForm()" <?php echo ($freqGrade[12] == 1) ? 'checked' : ''; ?>>
                <label for="checkboxGrade12">12</label>
            </div>

            <div class="filter-row">
                <input type="checkbox" id="checkboxBachelor" value="13" name="grade[]" onchange="submitForm()" <?php echo ($freqGrade[13] == 1) ? 'checked' : ''; ?>>
                <label for="checkboxBachelor">Bachelor</label>
            </div>
        </div>
        
        <div class = "status-group">
            <h4 class = "status-group-title">Location</h4>
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
        
                <div class = "status-group">
            <h4 class = "status-group-title" onclick="toggleInterestSection()" style="cursor: pointer;">
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
            <input type="button" onclick="location.href='<?php echo $base_url; ?>studentsList.php';" value="Reset">
            <input type="submit" value="Apply Filters">
        </div>
        </form>
    </div>  
    <div id = "contentStudents">
        <h1 style = "float: left;"> Students List</h1>

        <input type="text" id="search-bar" onkeyup="searchFunction()" placeholder="Search for students.." title="Type in a name">
        <ol id = "students-list" class="list-group list-group-numbered">
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
            // Note: Grade filtering is now done in PHP after fetching data due to complex calculation
            if ($typeAccount == 1) {
                $sqlStudent = "SELECT * FROM studentData WHERE `consultantId` IN " . $consultantString ." AND `packageType` IN " . $packageString . " AND `activityStatus` = 0";
                if (!empty($judetVariants)) {
                    $judetSqlList = "('" . implode("','", $judetVariants) . "')";
                    $sqlStudent .= " AND `judet` IN $judetSqlList";
                }
                // Only add interest filter if specific fields are selected
                if (isset($_GET['interestField']) && !empty($_GET['interestField'])) {
                    $sqlStudent .= " AND `interest` IN " . $interestFieldString;
                }
            } else {
                $sqlStudent = "SELECT * FROM studentData WHERE `consultantId` IN " . $consultantString ." AND `packageType` IN " .$packageString . " AND `consultantId` = '$userId' AND `activityStatus` = 0";
                if (!empty($judetVariants)) {
                    $judetSqlList = "('" . implode("','", $judetVariants) . "')";
                    $sqlStudent .= " AND `judet` IN $judetSqlList";
                }
                // Only add interest filter if specific fields are selected
                if (isset($_GET['interestField']) && !empty($_GET['interestField'])) {
                    $sqlStudent .= " AND `interest` IN " . $interestFieldString;
                }
            }
            // echo $sqlStudent;
            $queryStudent = mysqli_query($link, $sqlStudent);
            
            // Filter students by calculated grade in PHP
            $filteredStudents = array();
            $selectedGrades = isset($_GET['grade']) ? $_GET['grade'] : array();
            
            // If no grades selected, show all grades
            if (empty($selectedGrades)) {
                $selectedGrades = array(8, 9, 10, 11, 12, 13);
            }
            
            while ($row = mysqli_fetch_assoc($queryStudent)) {
                $calculatedGrade = getNumericGrade($row);
                if (in_array($calculatedGrade, $selectedGrades)) {
                    $filteredStudents[] = $row;
                }
            }

            $noStudents = count($filteredStudents);

            if (!isset($noStudents))
                $noStudents = 0;

            ?> <p style = "font-weight: bold;"> There are <span class = "search-count"><?php echo $noStudents; ?></span> students in your search </p> <?php
            foreach ($filteredStudents as $row) {
              ?>
                <div class = "student">
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="full-name"><?php echo $row['name']; ?></div>
                            <div class="student-info">
                                <span class="info-label">High School:</span>
                                <span><?php echo $row['highSchool'];?></span>
                            </div>
                            <div class="student-info">
                                <span class="info-label">Email:</span>
                                <span><?php echo $row['email'];?></span>
                            </div>
                            <div class="student-info">
                                <span class="info-label">Consultant:</span>
                                <span><?php echo $row['consultantName'];?></span>
                            </div>
                            <div class="student-info">
                                <span class="info-label">Grade:</span>
                                <span><?php echo calculateCurrentGrade($row);?></span>
                            </div>
                            <div class="student-info">
                                <span class="info-label">Start Grade:</span>
                                <span><?php echo ($row['grade'] <= 12) ? $row['signGrade'] : 'Bachelor';?></span>
                            </div>
                            <div class="student-info">
                                <span class="info-label">Package:</span>
                                <span><?php echo $row['packageType'];?></span>
                            </div>
                        </div>
                        <?php $urlStudent = "student.php?studentId=" . $row['studentId'];?>
                        <a href="<?php echo $urlStudent;?>">
                            <button type="button" class="btn btn-primary">View details</button>
                        </a>
                    </div>
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
            var input, filter, list, students, i, name, countDisplay;
            input = document.getElementById("search-bar");
            filter = input.value.toUpperCase();
            list = document.getElementById("students-list");
            students = list.getElementsByClassName("student");
            countDisplay = 0;

            for (i = 0; i < students.length; i++) {
                var studentInfo = students[i].getElementsByClassName("student-info");
                var fullName = students[i].getElementsByClassName("full-name")[0].innerHTML;
                
                // Get all text content from student info divs
                var infoText = "";
                for (var j = 0; j < studentInfo.length; j++) {
                    infoText += studentInfo[j].innerText;
                }

                // Combine full name and all info text
                var searchableText = fullName + infoText;

                if (searchableText.toUpperCase().indexOf(filter) > -1) {
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