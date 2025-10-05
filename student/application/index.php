<!doctype html>

<?php
    session_start();
    require_once "../../configDatabase.php";

    if (!isset($_SESSION['idStudent'])) { /// testez daca userul este logat
        header("location: ../index.php");
        die();
    }
    else {
        $studentId = $_SESSION["idStudent"];
    }

    if (isset($_GET["applicationId"])) /// testez daca este setata vreo aplicatie
        $applicationId = $_GET["applicationId"];
    else {
        header("location:../index.php");
        die();
    }

    $sqlApplicationData = "SELECT * FROM applicationStatus WHERE `applicationId` = " .$applicationId;
    $queryApplicationData = mysqli_query($link, $sqlApplicationData);

    if (mysqli_num_rows($queryApplicationData) > 0) /// testez daca exista o aplicatie cu id-ul dat
        $applicationData = mysqli_fetch_assoc($queryApplicationData);
    else  {
        header("location: ../index.php");
        die();
    }

    if ($applicationData['studentId'] != $studentId) {
        header("location: ../index.php");
        die();
    }

    $universityId = $applicationData['universityId'];
    $scolarship = $applicationData['scholarship'];

    $sqlStudentData = "SELECT * FROM studentData WHERE `studentId` = " .$studentId;
    $queryStudentData = mysqli_query($link, $sqlStudentData);

    if (mysqli_num_rows($queryStudentData) > 0) /// testez daca exista user cu id-ul respectiv (daca nu probabil exista un bug)
        $dataStudent = mysqli_fetch_assoc($queryStudentData);
    else { // nu ar trebui sa intre niciodata aici!!
        header("location: ../index.php");
        die(); 
    }

    $sqlUniveristyData = "SELECT * FROM universities WHERE `universityId` =" . $universityId;
    $queryUniversityData = mysqli_query($link, $sqlUniveristyData);

    if (mysqli_num_rows($queryUniversityData) > 0)/// testez daca exista universitatea cu id-ul respectiv cu id-ul respectiv (daca nu probabil exista un bug)
        $dataUniversity = mysqli_fetch_assoc($queryUniversityData);
    else { // nu ar trebui sa intre niciodata aici!!
        header("location: ../index.php");
        die();
    }

    $universityName = $dataUniversity['universityName'];
    $universityCountry = $dataUniversity['universityCountry'];
    $universityCommission = $dataUniversity['commission'];

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <title>Application Details - <?php echo $universityName; ?></title>

    <style>
        :root {
            --primary-color: #4f235f;
            --secondary-color: #6c757d;
            --accent-color: #007bff;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-bg: #f8f9fa;
            --white: #ffffff;
            --border-radius: 12px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        #content {
            width: 90%;
            max-width: 1000px;
            margin: auto;
            padding: 20px;
        }

        .page-header {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--box-shadow);
            text-align: center;
        }

        .page-title {
            color: var(--primary-color);
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .page-subtitle {
            color: var(--secondary-color);
            font-size: 1.1rem;
            margin-bottom: 0;
        }

        .info-cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            border-left: 4px solid var(--primary-color);
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }

        .info-card h3 {
            color: var(--primary-color);
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .info-card h3 i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: var(--secondary-color);
            font-size: 0.9rem;
        }

        .info-value {
            color: var(--primary-color);
            font-weight: 500;
        }

        .status-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
            text-align: center;
        }

        .status-badge {
            display: inline-block;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--white);
            margin-bottom: 15px;
        }

        .scholarship-info {
            background: linear-gradient(135deg, var(--success-color) 0%, #20c997 100%);
            color: var(--white);
            border-radius: var(--border-radius);
            padding: 25px;
            text-align: center;
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
        }

        .scholarship-amount {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .checklist-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
        }

        .checklist-header {
            color: var(--primary-color);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .checklist-header i {
            margin-right: 10px;
        }

        .checklist-item {
            background: var(--light-bg);
            border-radius: var(--border-radius);
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: var(--transition);
        }

        .checklist-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .checklist-name {
            flex: 1;
            margin-right: 15px;
        }

        .checklist-type {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .checklist-type.extra {
            color: #5bc0de;
        }

        .checklist-type.university {
            color: #f0ad4e;
        }

        .checklist-status {
            padding: 6px 12px;
            border-radius: 15px;
            font-weight: 600;
            font-size: 0.8rem;
            color: var(--white);
        }

        .btn-back {
            background: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            box-shadow: var(--box-shadow);
        }

        .btn-back:hover {
            background: #3a1a47;
            color: var(--white);
            text-decoration: none;
            transform: translateY(-2px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            #content {
                width: 95%;
                padding: 10px;
            }

            .page-title {
                font-size: 2rem;
            }

            .info-cards-container {
                grid-template-columns: 1fr;
            }
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #3a1a47;
        }
    </style>
  </head>

  <div id="content">
    <?php include("../navbarStudent.php"); ?>
    
    <br>
    <br>
    <br>
    <br>
    <br>

    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-university"></i> Application Details
        </h1>
        <p class="page-subtitle">Viewing application for <?php echo $universityName; ?></p>
    </div>

    <!-- Error Messages -->
    <?php if (isset($_SESSION["error"])) { ?>
        <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION["error"]; ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php } ?>

    <!-- Information Cards -->
    <div class="info-cards-container">
        <div class="info-card">
            <h3><i class="fas fa-user-graduate"></i> Student Information</h3>
            <div class="info-item">
                <span class="info-label">Name:</span>
                <span class="info-value">
                    <a href="../" style="color: var(--primary-color); text-decoration: none;">
                        <?php echo $dataStudent['name']; ?>
                    </a>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Grade:</span>
                <span class="info-value"><?php echo $dataStudent['grade']; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">High School:</span>
                <span class="info-value"><?php echo $dataStudent['highSchool']; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Consultant:</span>
                <span class="info-value"><?php echo $dataStudent['consultantName']; ?></span>
            </div>
        </div>

        <div class="info-card">
            <h3><i class="fas fa-university"></i> University Information</h3>
            <div class="info-item">
                <span class="info-label">University:</span>
                <span class="info-value"><?php echo $universityName; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Country:</span>
                <span class="info-value"><?php echo $universityCountry; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Commission:</span>
                <span class="info-value"><?php echo $universityCommission; ?></span>
            </div>
        </div>
    </div>

    <!-- Application Status -->
    <div class="status-card">
        <h3><i class="fas fa-clipboard-check"></i> Application Status</h3>
        <div class="status-badge" style="background-color: <?php echo $colorStatus; ?>;">
            <?php echo $status; ?>
        </div>
        <p class="text-muted">Current application status</p>
    </div>

    <!-- Scholarship Information (if accepted) -->
    <?php if ($status == "Accepted") { ?>
        <div class="scholarship-info">
            <h3><i class="fas fa-graduation-cap"></i> Scholarship Awarded</h3>
            <div class="scholarship-amount">
                <?php echo $applicationData['scholarship'] . "$"; ?>
            </div>
            <p>Congratulations! You have been awarded a scholarship.</p>
        </div>
    <?php } ?>

    <!-- Application Checklist -->
    <div class="checklist-card">
        <h3 class="checklist-header">
            <i class="fas fa-tasks"></i> Application Checklist
        </h3>
        
        <?php
        // Fetch checklist items for this application
        $sqlChecklist = "SELECT ac.checklistId, ac.isCustom, ac.status, c.checklistName FROM applications_checklist ac LEFT JOIN checklist c ON ac.checklistId = c.checklistId WHERE ac.applicationId = $applicationId";
        $queryChecklist = mysqli_query($link, $sqlChecklist);
        ?>
        
        <?php if (mysqli_num_rows($queryChecklist) > 0) { ?>
            <?php while ($checklist = mysqli_fetch_assoc($queryChecklist)) { ?>
                <div class="checklist-item">
                    <div class="checklist-name">
                        <div class="checklist-type <?php echo ((int)$checklist['isCustom'] === 1) ? 'extra' : 'university'; ?>">
                            <?php echo ((int)$checklist['isCustom'] === 1) ? 'Extra Checklist Item' : 'University Checklist Item'; ?>
                        </div>
                        <div class="checklist-description">
                            <?php echo htmlspecialchars($checklist['checklistName'] ? $checklist['checklistName'] : $checklist['checklistId']); ?>
                        </div>
                    </div>
                    <div class="checklist-status" style="background-color: <?php echo $checklist['status'] === 'Done' ? '#28a745' : '#dc3545'; ?>;">
                        <?php echo htmlspecialchars($checklist['status']); ?>
                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <div class="text-center text-muted" style="padding: 40px;">
                <i class="fas fa-clipboard-list" style="font-size: 3rem; margin-bottom: 20px;"></i>
                <p>No checklist items found for this application.</p>
            </div>
        <?php } ?>
    </div>

    <!-- Back Button -->
    <div class="text-center">
        <a href="../" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
  </div>

  <!-- Optional JavaScript -->
  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>