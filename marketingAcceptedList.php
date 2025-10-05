<?php
    session_start();

    function getStatusColor($status) {
        switch ($status) {
            case 0:
                return "#FF0000"; // Red for Not Posted
            case 1:
                return "#008000"; // Green for Posted (both platforms)
            case 2:
                return "#E1306C"; // Instagram Pink
            case 3:
                return "#1877F2"; // Facebook Blue
            case 4:
                return "#008000"; // Green for Posted (both platforms)
            default:
                return "#FF0000";
        }
    }

    function getTextStatus($status) {
        switch ($status) {
            case 0:
                return "Not Posted";
            case 1:
                return "Posted on Both";
            case 2:
                return "Posted on Instagram";
            case 3:
                return "Posted on Facebook";
            case 4:
                return "Posted on Both";
            default:
                return "Not Posted";
        }
    }

    if (!isset($_SESSION['type'])) {
        header("location: index.php");
        die();
    }
    else {
        $typeAccount = $_SESSION["type"];
        $userId = $_SESSION["id"];
    }

    if ($typeAccount != 1) {
        header("location: index.php");
        die();
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


    $postedType = $_GET['posted'];

    $postedString = "(";
    $firstElem = 0;

    foreach($postedType as $postedStatus) {
        if ($firstElem > 0)
            $postedString .= ",";
        
        $postedString .= $postedStatus;
        $freqPosted[$postedStatus] = 1;
        $firstElem += 1;
    }
    $postedString .= ')';

    if ($firstElem == 0) {
        $postedString = "(0, 1, 2, 3)";
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
    <title>Marketing Accepted List</title>

    <style>
        :root {
            --primary-color: #4f235f;
            --secondary-color: #6c757d;
            --accent-color: #007bff;
            --background-color: #f8f9fa;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            --gradient-start: #4f235f;
            --gradient-end: #cb1b80;
            --success-color: #28a745;
            --danger-color: #dc3545;
        }

        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        #content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            margin-top: 100px;
            display: flex;
            gap: 30px;
        }

        #contentFilter {
            width: 300px;
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            padding: 25px;
            height: fit-content;
            position: sticky;
            top: 120px;
            max-height: calc(100vh - 140px);
            overflow-y: auto;
        }

        #contentStudents {
            flex: 1;
            background-color: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            padding: 40px;
            position: relative;
            z-index: 1;
        }

        .navbar {
            height: 80px;
            background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        h1 {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 2.4rem;
            margin: 0;
            background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        h3 {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.8rem;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        h4 {
            color: var(--secondary-color);
            font-weight: 600;
            font-size: 1.2rem;
            margin: 25px 0 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
        }

        .filter-section {
            margin-bottom: 30px;
            padding-right: 10px;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .checkbox-container:hover {
            background-color: #f8f9fa;
        }

        .checkbox-container input[type="checkbox"] {
            margin-right: 12px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .checkboxLabel {
            font-size: 1rem;
            color: var(--secondary-color);
            cursor: pointer;
            user-select: none;
        }

        .list-group-item {
            margin-bottom: 20px;
            border-radius: 12px !important;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            padding: 25px;
            background: white;
        }

        .list-group-item:hover {
            box-shadow: var(--hover-shadow);
            transform: translateY(-3px);
            border-color: var(--accent-color);
        }

        .full-name {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 1.3rem;
            margin-bottom: 15px;
            line-height: 1.4;
        }

        .consultant {
            color: var(--secondary-color);
            font-size: 1.1rem;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }

        .badge {
            padding: 6px 12px;
            font-size: 0.85rem;
            font-weight: 500;
            border-radius: 6px;
            position: static;
            margin-left: 10px;
            color: white;
        }

        .badge.posted {
            background-color: var(--success-color) !important;
        }

        .badge.not-posted {
            background-color: var(--danger-color) !important;
        }

        .badge.instagram {
            background-color: #E1306C !important;
            color: white !important;
        }

        .badge.facebook {
            background-color: #1877F2 !important;
            color: white !important;
        }

        .btn-primary {
            background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--hover-shadow);
            opacity: 0.9;
        }

        .search-count {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 25px;
            display: inline-block;
            padding: 8px 16px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .filter-buttons {
            position: sticky;
            bottom: 0;
            background: white;
            padding-top: 15px;
            margin-top: 20px;
            border-top: 1px solid #f0f0f0;
        }

        .filter-buttons input[type="button"],
        .filter-buttons input[type="submit"] {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }

        .filter-buttons input[type="button"] {
            background-color: #f8f9fa;
            color: var(--secondary-color);
        }

        .filter-buttons input[type="submit"] {
            background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
            color: white;
        }

        .filter-buttons input:hover {
            transform: translateY(-2px);
            box-shadow: var(--hover-shadow);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, var(--gradient-start), var(--gradient-end));
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            opacity: 0.8;
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
            background: linear-gradient(to bottom, var(--gradient-start), var(--gradient-end));
            border-radius: 4px;
        }

        #contentFilter::-webkit-scrollbar-thumb:hover {
            opacity: 0.8;
        }

        @media (max-width: 768px) {
            #content {
                flex-direction: column;
                padding: 10px;
                margin-top: 80px;
            }

            #contentFilter {
                width: 100%;
                position: static;
            }

            #contentStudents {
                padding: 20px;
            }

            h1 {
                font-size: 2rem;
            }
        }

        .gap-3 {
            gap: 1rem;
        }
    </style>
  </head>

  <?php include("navbar.php"); ?>

  <div id="content">
    <div id="contentFilter">
        <h3>Filters</h3>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET" id="filters-form">
            <?php if ($typeAccount == 1) { ?>
                <div class="filter-section">
                    <h4>Consultants</h4>
                    <?php 
                    $sql = "SELECT userId, fullName FROM users WHERE type = 0";
                    $result = mysqli_query($link, $sql);
                    while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                        <div class="checkbox-container">
                            <input <?php echo ($freqConsultant[$row['userId']] == 1) ? 'checked' : ''; ?> 
                                   type="checkbox" 
                                   id="checkbox<?php echo $row['userId']; ?>" 
                                   value="<?php echo $row['userId']; ?>" 
                                   name="consultant[]" 
                                   onchange="submitForm()">
                            <label class="checkboxLabel" for="checkbox<?php echo $row['userId']; ?>">
                                <?php echo $row['fullName']; ?>
                            </label>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

            <div class="filter-section">
                <h4>Institution Type</h4>
                <div class="checkbox-container">
                    <input <?php echo ($freqInstitution['0'] == 1) ? 'checked' : ''; ?> 
                           type="checkbox" 
                           id="checkboxUniversity" 
                           value="0" 
                           name="institution[]" 
                           onchange="submitForm()">
                    <label class="checkboxLabel" for="checkboxUniversity">University</label>
                </div>

                <div class="checkbox-container">
                    <input <?php echo ($freqInstitution['1'] == 1) ? 'checked' : ''; ?> 
                           type="checkbox" 
                           id="checkboxSummer" 
                           value="1" 
                           name="institution[]" 
                           onchange="submitForm()">
                    <label class="checkboxLabel" for="checkboxSummer">Summer School</label>
                </div>

                <div class="checkbox-container">
                    <input <?php echo ($freqInstitution['2'] == 1) ? 'checked' : ''; ?> 
                           type="checkbox" 
                           id="checkboxBoarding" 
                           value="2" 
                           name="institution[]" 
                           onchange="submitForm()">
                    <label class="checkboxLabel" for="checkboxBoarding">Boarding School</label>
                </div>
            </div>

            <div class="filter-section">
                <h4>Posted Status</h4>
                <div class="checkbox-container">
                    <input <?php echo ($freqPosted[0] == 1) ? 'checked' : ''; ?> 
                           type="checkbox" 
                           id="checkboxNotPosted" 
                           value="0" 
                           name="posted[]" 
                           onchange="submitForm()">
                    <label class="checkboxLabel" for="checkboxNotPosted">Not Posted</label>
                </div>

                <div class="checkbox-container">
                    <input <?php echo ($freqPosted[1] == 1) ? 'checked' : ''; ?> 
                           type="checkbox" 
                           id="checkboxPosted" 
                           value="1" 
                           name="posted[]" 
                           onchange="submitForm()">
                    <label class="checkboxLabel" for="checkboxPosted">Posted</label>
                </div>

                <div class="checkbox-container">
                    <input <?php echo ($freqPosted[2] == 1) ? 'checked' : ''; ?> 
                           type="checkbox" 
                           id="checkboxInstagram" 
                           value="2" 
                           name="posted[]" 
                           onchange="submitForm()">
                    <label class="checkboxLabel" for="checkboxInstagram">Posted on Instagram</label>
                </div>

                <div class="checkbox-container">
                    <input <?php echo ($freqPosted[3] == 1) ? 'checked' : ''; ?> 
                           type="checkbox" 
                           id="checkboxFacebook" 
                           value="3" 
                           name="posted[]" 
                           onchange="submitForm()">
                    <label class="checkboxLabel" for="checkboxFacebook">Posted on Facebook</label>
                </div>
            </div>

            <div class="filter-buttons">
                <input type="button" onclick="location.href='applicationsList.php';" value="Reset">
                <input type="submit" value="Apply Filters">
            </div>
        </form>
    </div>

    <div id="contentStudents">
        <div class="page-header">
            <h1>Marketing Accepted List</h1>
        </div>

        <?php
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
            aps.scholarship AS scholarship,
            aps.postedStatus AS postedStatus
        FROM 
            applicationStatus aps
        INNER JOIN 
            studentData sd ON aps.studentId = sd.studentId
        INNER JOIN 
            universities u ON aps.universityId = u.universityId
        WHERE 
            sd.consultantId IN " . $consultantString . "
            AND aps.appStatus = 'Accepted'
            AND aps.postedStatus IN " . $postedString . "
            AND u.institutionType IN " . $institutionString;

        $queryApplication = mysqli_query($link, $sqlApplication);
        $noApplications = mysqli_num_rows($queryApplication);

        if (!isset($noApplications)) {
            $noApplications = 0;
        }
        ?>

        <p>Found <span class="search-count"><?php echo $noApplications; ?></span> applications</p>

        <ol id="students-list" class="list-group list-group-numbered">
            <?php while ($row = mysqli_fetch_assoc($queryApplication)) { ?>
                <div class="student">
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="full-name">
                                Application from <?php echo $row['studentName']; ?> to<br>
                                <?php echo $row['universityName']; ?> (<?php echo $row['universityCountry']; ?>)
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <p class="consultant mb-0">
                                    <i class="fas fa-user-tie mr-2"></i>
                                    <strong>Consultant:</strong> <?php echo $row['consultantName']; ?>
                                </p>
                                <span class="badge <?php 
                                    switch($row['postedStatus']) {
                                        case 0:
                                            echo 'not-posted';
                                            break;
                                        case 1:
                                            echo 'posted';
                                            break;
                                        case 2:
                                            echo 'instagram';
                                            break;
                                        case 3:
                                            echo 'facebook';
                                            break;
                                        case 4:
                                            echo 'posted';
                                            break;
                                        default:
                                            echo 'not-posted';
                                    }
                                ?>">
                                    <?php echo getTextStatus($row['postedStatus']); ?>
                                </span>
                            </div>
                            <p class="consultant">
                                <i class="fas fa-graduation-cap mr-2"></i>
                                <strong>Scholarship:</strong> 
                                <span style="color: var(--success-color); font-weight: bold;">
                                    <?php echo $row['scholarship'] . "$"; ?>
                                </span>
                            </p>
                        </div>

                        <?php 
                        $urlApplication = 'postApplication.php?applicationId=' . $row['applicationId'];
                        $urlUnpost = 'unpostApplication.php?applicationId=' . $row['applicationId'];
                        if ($row['postedStatus'] == 0) { ?>
                            <div class="d-flex gap-2">
                                <a onclick="confirmPost('<?php echo $urlApplication; ?>&platform=2')">
                                    <button type="button" class="btn btn-primary" style="background: #E1306C;">
                                        <i class="fab fa-instagram mr-2"></i> Post on Instagram
                                    </button>
                                </a>
                                <a onclick="confirmPost('<?php echo $urlApplication; ?>&platform=3')">
                                    <button type="button" class="btn btn-primary" style="background: #1877F2;">
                                        <i class="fab fa-facebook mr-2"></i> Post on Facebook
                                    </button>
                                </a>
                            </div>
                        <?php } elseif ($row['postedStatus'] == 2) { ?>
                            <div class="d-flex gap-2">
                                <a onclick="confirmPost('<?php echo $urlApplication; ?>&platform=3')">
                                    <button type="button" class="btn btn-primary" style="background: #1877F2;">
                                        <i class="fab fa-facebook mr-2"></i> Post on Facebook
                                    </button>
                                </a>
                                <a onclick="confirmUnpost('<?php echo $urlUnpost; ?>&platform=2')">
                                    <button type="button" class="btn btn-primary" style="background: #E1306C;">
                                        <i class="fas fa-undo mr-2"></i> Unpost from Instagram
                                    </button>
                                </a>
                            </div>
                        <?php } elseif ($row['postedStatus'] == 3) { ?>
                            <div class="d-flex gap-2">
                                <a onclick="confirmPost('<?php echo $urlApplication; ?>&platform=2')">
                                    <button type="button" class="btn btn-primary" style="background: #E1306C;">
                                        <i class="fab fa-instagram mr-2"></i> Post on Instagram
                                    </button>
                                </a>
                                <a onclick="confirmUnpost('<?php echo $urlUnpost; ?>&platform=3')">
                                    <button type="button" class="btn btn-primary" style="background: #1877F2;">
                                        <i class="fas fa-undo mr-2"></i> Unpost from Facebook
                                    </button>
                                </a>
                            </div>
                        <?php } elseif ($row['postedStatus'] == 1 || $row['postedStatus'] == 4) { ?>
                            <div class="d-flex gap-2">
                                <a onclick="confirmUnpost('<?php echo $urlUnpost; ?>&platform=2')">
                                    <button type="button" class="btn btn-primary" style="background: #E1306C;">
                                        <i class="fas fa-undo mr-2"></i> Unpost from Instagram
                                    </button>
                                </a>
                                <a onclick="confirmUnpost('<?php echo $urlUnpost; ?>&platform=3')">
                                    <button type="button" class="btn btn-primary" style="background: #1877F2;">
                                        <i class="fas fa-undo mr-2"></i> Unpost from Facebook
                                    </button>
                                </a>
                            </div>
                        <?php } ?>
                    </li>
                </div>
            <?php } ?>
        </ol>
    </div>
  </div>

  <!-- Optional JavaScript -->
  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  <script>
    function submitForm() {
        document.getElementById('filters-form').submit();
    }

    function confirmPost(link) {
        const userConfirmed = confirm("Are you sure you want to post this application?");
        if (userConfirmed) {
            window.location.href = link;
        } else {
            alert("Action canceled.");
        }
    }

    function confirmUnpost(link) {
        const userConfirmed = confirm("Are you sure you want to unpost this application?");
        if (userConfirmed) {
            window.location.href = link;
        } else {
            alert("Action canceled.");
        }
    }
  </script>
</body>
</html>