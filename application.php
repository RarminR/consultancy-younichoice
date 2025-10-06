<!doctype html>

<?php
    session_start();
    require_once "configDatabase.php";
    
    // Grade calculation function
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

    $sqlApplicationData = "SELECT * FROM applicationStatus WHERE `applicationId` = " .$applicationId;
    $queryApplicationData = mysqli_query($link, $sqlApplicationData);

    if (mysqli_num_rows($queryApplicationData) > 0) /// testez daca exista o aplicatie cu id-ul dat
        $applicationData = mysqli_fetch_assoc($queryApplicationData);
    else  {
        header("location: index.php");
        die();
    }

    $studentId = $applicationData['studentId'];
    $universityId = $applicationData['universityId'];
    $scolarship = $applicationData['scholarship'];

    $sqlStudentData = "SELECT * FROM studentData WHERE `studentId` = " .$studentId;
    $queryStudentData = mysqli_query($link, $sqlStudentData);

    if (mysqli_num_rows($queryStudentData) > 0) /// testez daca exista user cu id-ul respectiv (daca nu probabil exista un bug)
        $dataStudent = mysqli_fetch_assoc($queryStudentData);
    else { // nu ar trebui sa intre niciodata aici!!
        header("location: index.php");
        die(); 
    }

    $sqlUniveristyData = "SELECT * FROM universities WHERE `universityId` =" . $universityId;
    $queryUniversityData = mysqli_query($link, $sqlUniveristyData);

    if (mysqli_num_rows($queryUniversityData) > 0)/// testez daca exista universitatea cu id-ul respectiv cu id-ul respectiv (daca nu probabil exista un bug)
        $dataUniversity = mysqli_fetch_assoc($queryUniversityData);
    else { // nu ar trebui sa intre niciodata aici!!
        header("location: index.php");
        die();
    }

    $universityName = $dataUniversity['universityName'];
    $universityCountry = $dataUniversity['universityCountry'];
    $universityCommission = $dataUniversity['commission'];


    if (!($dataStudent['consultantId'] == $accountId || $typeAccount == 1)) { //testez daca userul curent (admin / consultant) are acces la acest elev 
        header("location: index.php");
        die();
    }

    $colorStatus = "black";
    $colorStatusFinancial = "black";

    $status = $applicationData['appStatus'];
    $status = trim($status);
    if ($status == "In progress")
        $colorStatus = "#FFA500";
    else if ($status == "Accepted")
        $colorStatus = "#008000";
    else if ($status == "Rejected")
        $colorStatus = "#FF0000";
    else if ($status == "Waitlisted")
        $colorStatus = "#808080";
?>

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
    <title>Application Details</title>

    <style>
        /* Application Details Design System Styles */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
        }

        #content {
            max-width: 1000px;
            margin: 0 auto;
            padding: var(--spacing-2xl);
            min-height: calc(100vh - 120px);
        }

        .navbar {
            background: var(--white);
            box-shadow: var(--shadow-sm);
            border-bottom: 1px solid var(--light-gray);
        }

        .application-header {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-lg);
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--light-gray);
            margin-bottom: var(--spacing-lg);
            backdrop-filter: blur(10px);
        }

        .application-title {
            color: var(--primary-color);
            font-size: var(--font-size-2xl);
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--spacing-md);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .application-title::before {
            content: '\f0f6';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-lg);
            color: var(--primary-color);
        }

        .info-section {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--light-gray);
            margin-bottom: var(--spacing-lg);
        }

        .section-title {
            color: var(--primary-color);
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-semibold);
            margin-bottom: var(--spacing-md);
            padding-bottom: var(--spacing-sm);
            border-bottom: 2px solid var(--primary-color);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .section-title::before {
            content: '\f007';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-lg);
        }

        .university-section .section-title::before {
            content: '\f19c';
        }

        .checklist-section .section-title::before {
            content: '\f0ae';
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-md);
        }

        .info-item {
            display: flex;
            flex-direction: column;
            padding: var(--spacing-md);
            background: var(--light-bg);
            border-radius: var(--border-radius-md);
            border-left: 3px solid var(--primary-color);
            transition: var(--transition-normal);
        }

        .info-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .info-label {
            color: var(--secondary-color);
            font-size: var(--font-size-xs);
            font-weight: var(--font-weight-semibold);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: var(--spacing-xs);
        }

        .info-value {
            color: var(--text-color);
            font-size: var(--font-size-base);
            font-weight: var(--font-weight-semibold);
        }

        .info-value a {
            color: var(--primary-color);
            text-decoration: none;
            transition: var(--transition-normal);
        }

        .info-value a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .status-badge {
            display: inline-block;
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--border-radius-full);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-semibold);
            color: var(--white);
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
            box-shadow: var(--shadow-sm);
        }

        .status-in-progress {
            background: var(--warning-gradient);
        }

        .status-accepted {
            background: var(--success-gradient);
        }

        .status-rejected {
            background: var(--danger-gradient);
        }

        .status-waitlisted {
            background: var(--secondary-gradient);
        }

        .status-enrolled {
            background: var(--success-gradient);
        }

        .checklist-card {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--light-gray);
            margin-bottom: var(--spacing-lg);
            overflow: hidden;
        }

        .checklist-header {
            background: var(--warning-gradient);
            color: var(--white);
            padding: var(--spacing-lg);
            font-weight: var(--font-weight-bold);
            font-size: var(--font-size-lg);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .checklist-header::before {
            content: '\f0ae';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
        }

        .checklist-body {
            padding: var(--spacing-lg);
        }

        .checklist-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: var(--spacing-md);
            margin-bottom: var(--spacing-sm);
            background: var(--light-bg);
            border-radius: var(--border-radius-md);
            border: 1px solid var(--light-gray);
            transition: var(--transition-normal);
        }

        .checklist-item:hover {
            background: var(--white);
            box-shadow: var(--shadow-sm);
        }

        .checklist-item:last-child {
            margin-bottom: 0;
        }

        .checklist-name {
            flex: 1;
            color: var(--text-color);
            font-weight: var(--font-weight-medium);
        }

        .checklist-type {
            color: var(--info-color);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-semibold);
            margin-right: var(--spacing-sm);
        }

        .checklist-type.extra {
            color: var(--info-color);
        }

        .checklist-type.university {
            color: var(--warning-color);
        }

        .checklist-status {
            background: var(--primary-gradient);
            color: var(--white);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--border-radius-full);
            font-size: var(--font-size-xs);
            font-weight: var(--font-weight-semibold);
            box-shadow: var(--shadow-sm);
        }

        .action-buttons {
            display: flex;
            gap: var(--spacing-md);
            justify-content: center;
            margin-top: var(--spacing-xl);
            padding-top: var(--spacing-lg);
            border-top: 2px solid var(--light-gray);
        }

        .btn-edit {
            background: var(--primary-gradient);
            color: var(--white);
            border: none;
            padding: var(--spacing-md) var(--spacing-xl);
            border-radius: var(--border-radius-lg);
            font-weight: var(--font-weight-semibold);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
            transition: var(--transition-normal);
            font-family: 'Poppins', sans-serif;
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            color: var(--white);
            text-decoration: none;
        }

        .btn-edit::before {
            content: '\f044';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-sm);
        }

        .alert {
            padding: var(--spacing-md) var(--spacing-lg);
            border-radius: var(--border-radius-lg);
            margin-bottom: var(--spacing-lg);
            border: 1px solid transparent;
            font-weight: var(--font-weight-medium);
        }

        .alert-danger {
            background: var(--danger-light);
            color: var(--danger-dark);
            border-color: var(--danger-color);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            #content {
                padding: var(--spacing-md);
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .application-title {
                font-size: var(--font-size-2xl);
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .checklist-item {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--spacing-sm);
            }
        }
    </style>
  </head>

  
  <?php include("navbar.php"); ?>

  <div id="content">
    <div class="application-header">
        <h1 class="application-title">Application Details</h1>
        
        <?php
        if (isset($_SESSION["error"])) {
            echo '<div class="alert alert-danger">' . $_SESSION["error"] . '</div>';
            unset($_SESSION['error']);
        }
        ?>
    </div>

    <div class="info-section">
        <h2 class="section-title">Student Information</h2>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Applying Student</div>
                <div class="info-value">
                    <a href="<?php echo 'student.php?studentId='.$studentId; ?>"><?php echo $dataStudent['name']; ?></a>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Current Class</div>
                <div class="info-value"><?php echo calculateCurrentGrade($dataStudent); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">High School</div>
                <div class="info-value"><?php echo $dataStudent['highSchool']; ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Consultant</div>
                <div class="info-value">
                    <a href="<?php echo 'consultant.php?consultantId='.$dataStudent['consultantId']; ?>"><?php echo $dataStudent['consultantName']; ?></a>
                </div>
            </div>
        </div>
    </div>

    <div class="info-section university-section">
        <h2 class="section-title">University Information</h2>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">University Name</div>
                <div class="info-value">
                    <a href="<?php echo 'university.php?universityId='.$universityId; ?>"><?php echo $universityName; ?></a>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Country</div>
                <div class="info-value"><?php echo $universityCountry; ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Commission</div>
                <div class="info-value"><?php echo $universityCommission; ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Application Status</div>
                <div class="info-value">
                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $status)); ?>">
                        <?php echo $status; ?>
                    </span>
                </div>
            </div>
        </div>
        
        <?php if ($status == "Accepted") { ?>
            <div class="info-item" style="margin-top: var(--spacing-lg);">
                <div class="info-label">Scholarship</div>
                <div class="info-value" style="color: var(--success-color); font-size: var(--font-size-xl); font-weight: var(--font-weight-bold);">
                    <?php echo $applicationData['scholarship'] . "$"; ?>
                </div>
            </div>
        <?php } ?>
    </div>

    <?php
    // Fetch checklist items for this application
    $sqlChecklist = "SELECT ac.checklistId, ac.isCustom, ac.status, c.checklistName FROM applications_checklist ac LEFT JOIN checklist c ON ac.checklistId = c.checklistId WHERE ac.applicationId = $applicationId";
    $queryChecklist = mysqli_query($link, $sqlChecklist);
    ?>
    <div class="info-section checklist-section">
        <h2 class="section-title">Application Checklist</h2>
        <div class="checklist-card">
            <div class="checklist-header">Application Checklist</div>
            <div class="checklist-body">
                <?php if (mysqli_num_rows($queryChecklist) > 0) { ?>
                    <?php while ($checklist = mysqli_fetch_assoc($queryChecklist)) { ?>
                        <div class="checklist-item">
                            <div class="checklist-name">
                                <span class="checklist-type <?php echo ((int)$checklist['isCustom'] === 1) ? 'extra' : 'university'; ?>">
                                    <?php echo ((int)$checklist['isCustom'] === 1) ? 'Extra Checklist Item: ' : 'University Checklist Item: '; ?>
                                </span>
                                <?php echo htmlspecialchars($checklist['checklistName'] ? $checklist['checklistName'] : $checklist['checklistId']); ?>
                            </div>
                            <span class="checklist-status">
                                <?php echo htmlspecialchars($checklist['status']); ?>
                            </span>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="checklist-item">
                        <div class="checklist-name">No checklist items found for this application.</div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="action-buttons">
        <a href="<?php echo "editApplication.php?applicationId=".$applicationId; ?>" class="btn-edit">
            Edit Application Status
        </a>
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
            list = document.getElementById("applications-list");
            applications = list.getElementsByClassName("application");
            for (i = 0; i < applications.length; i++) {
                name1 = applications[i].getElementsByClassName("university-name")[0].innerHTML;
                name2 =  applications[i].getElementsByClassName("country-university")[0].innerHTML;

                name = name1 + name2;
                console.log(name);
                if (name.toUpperCase().indexOf(filter) > -1) {
                    applications[i].style.display = "";
                } else {
                    applications[i].style.display = "none";
                }
            }
        }
    </script>
</body>
</html>