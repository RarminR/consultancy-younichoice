<?php
    function getStatusColor($status) {
        $status = trim($status);
        $colorStatus = "#FFA500";
        if ($status == "Accepted")
            $colorStatus = "#008000";
        else if ($status == "Rejected")
            $colorStatus = "#FF0000";
        else if ($status == "Waitlisted")
            $colorStatus = "#808080";

        return $colorStatus;
    }

    session_start();
    require_once "configDatabase.php";

    if (!isset($_SESSION['type'])) { // testez daca userul est logat
        header("location: index.php");
        die();
    }
    else {
        $typeAccount = $_SESSION["type"];
        $accountId = $_SESSION["id"];
    }

    if (isset($_GET['universityId'])) // testez daca e setata o universitate
        $universityId = $_GET['universityId'];
    else {
        header("location: index.php");
        die();
    }

    $sqlUnivesitiesData = "SELECT * FROM universities WHERE `universityId` = '$universityId'";    
    $queryUniversitiesData = mysqli_query($link, $sqlUnivesitiesData);

    if (mysqli_num_rows($queryUniversitiesData) > 0) // testez daca exista universitatea cu id-ul dat
        $dataUniversity = mysqli_fetch_assoc($queryUniversitiesData);
    else {
        header("location: index.php");
        die();
    }


    if ($dataStudent['consultantId'] == $accountId) { // daca userul este consultant
        $consultantId = $dataStudent['consultantId'];
        $sqlApplicationsData = "SELECT * FROM applicationStatus AS a JOIN studentData AS s ON a.studentId = s.studentId WHERE a.universityId = '$universityId' AND s.consultantId = '$consultantId';";
    }
    else
        $sqlApplicationsData = "SELECT * FROM applicationStatus WHERE `universityId` = '$universityId';";


    if ($dataUniversity['institutionType'] == 0)
        $schoolType = "University";
    else if ($dataUniversity['institutionType'] == 1)
        $schoolType = "Summer School";
    else
        $schoolType = "Boarding School";

    $queryApplicationsData = mysqli_query($link, $sqlApplicationsData);


    $nApplications = 0;
    while ($row = mysqli_fetch_assoc($queryApplicationsData)) {
        $studentId = $row["studentId"];
        if ($typeAccount == 1)
            $sqlStudents = "SELECT * FROM studentData WHERE `studentId` = '$studentId'";
        else
            $sqlStudents = "SELECT * FROM studentData WHERE `studentId` = '$studentId' AND `consultantId` = '$accountId'";
        $queryStudents = mysqli_query($link, $sqlStudents);

        if (mysqli_num_rows($queryStudents) > 0) {
            $rowStudent = mysqli_fetch_assoc($queryStudents);

            $arrStudentName[$nApplications] = $rowStudent["name"];
            $arrStudentGrade[$nApplications] = $rowStudent["grade"];
            $arrStudentConsultant[$nApplications] = $rowStudent["consultantName"];
            $arrApplicationsId[$nApplications] = $row["applicationId"];
            $arrAplicationsStatusColor[$nApplications] = getStatusColor($row["appStatus"]);
            $arrAplicationsStatus[$nApplications] = $row["appStatus"];

            $nApplications++;
        }
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
    <title>University: <?php echo htmlspecialchars($dataUniversity["universityName"]); ?></title>

    <style>
        /* University Page Design System Styles */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
        }

        #content {
            max-width: 1200px;
            margin: 0 auto;
            padding: var(--spacing-2xl);
            min-height: calc(100vh - 120px);
        }

        .navbar {
            background: var(--white);
            box-shadow: var(--shadow-sm);
            border-bottom: 1px solid var(--light-gray);
        }

        .university-header {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-2xl);
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--light-gray);
            margin-bottom: var(--spacing-2xl);
            backdrop-filter: blur(10px);
        }

        .university-title {
            color: var(--primary-color);
            font-size: var(--font-size-2xl);
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--spacing-md);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .university-title::before {
            content: '\f19c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-lg);
            color: var(--primary-color);
        }

        .university-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-lg);
        }

        .info-item {
            background: var(--light-bg);
            border-radius: var(--border-radius-md);
            padding: var(--spacing-md);
            border-left: 3px solid var(--primary-color);
            transition: var(--transition-normal);
        }

        .info-item:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-sm);
        }

        .info-label {
            color: var(--secondary-color);
            font-size: var(--font-size-xs);
            font-weight: var(--font-weight-semibold);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: var(--spacing-xs);
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
        }

        .info-label::before {
            content: '\f19c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-sm);
        }

        .type-label::before {
            content: '\f0ae';
        }

        .name-label::before {
            content: '\f19c';
        }

        .country-label::before {
            content: '\f57c';
        }

        .commission-label::before {
            content: '\f3d1';
        }

        .info-value {
            color: var(--text-color);
            font-size: var(--font-size-base);
            font-weight: var(--font-weight-semibold);
        }

        .commission-badge {
            display: inline-block;
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--border-radius-full);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-semibold);
            color: var(--white);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .commission-yes {
            background: var(--success-gradient);
        }

        .commission-no {
            background: var(--danger-gradient);
        }

        .action-buttons {
            display: flex;
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-lg);
            flex-wrap: wrap;
        }

        .btn-action {
            padding: var(--spacing-xs) var(--spacing-lg);
            border-radius: var(--border-radius-md);
            font-weight: var(--font-weight-semibold);
            font-size: var(--font-size-sm);
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-xs);
            transition: var(--transition-normal);
            border: 2px solid transparent;
            cursor: pointer;
        }

        .btn-action:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
            text-decoration: none;
        }

        .btn-primary-action {
            background: var(--primary-gradient);
            color: var(--white);
        }

        .btn-primary-action:hover {
            color: var(--white);
        }

        .btn-primary-action::before {
            content: '\f044';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-sm);
        }

        .btn-add-action {
            background: var(--success-gradient);
            color: var(--white);
        }

        .btn-add-action:hover {
            color: var(--white);
        }

        .btn-add-action::before {
            content: '\f067';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-sm);
        }

        .search-container {
            margin-bottom: var(--spacing-lg);
            position: relative;
        }

        .search-bar {
            width: 100%;
            padding: var(--spacing-md) var(--spacing-lg) var(--spacing-md) var(--spacing-3xl);
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
            left: var(--spacing-lg);
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
            font-size: var(--font-size-lg);
            pointer-events: none;
        }

        .applications-section {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-2xl);
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--light-gray);
            backdrop-filter: blur(10px);
        }

        .applications-title {
            color: var(--primary-color);
            font-size: var(--font-size-xl);
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--spacing-lg);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .applications-title::before {
            content: '\f0f6';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-lg);
            color: var(--primary-color);
        }

        .applications-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .application-item {
            background: var(--white);
            border-radius: var(--border-radius-md);
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-md);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--light-gray);
            transition: var(--transition-normal);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .application-item:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary-color);
        }

        .application-info {
            flex: 1;
        }

        .student-name {
            color: var(--primary-color);
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--spacing-xs);
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
        }

        .student-name::before {
            content: '\f007';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-base);
            color: var(--primary-color);
        }

        .consultant-name {
            color: var(--secondary-color);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-medium);
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
        }

        .consultant-name::before {
            content: '\f007';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-xs);
            color: var(--secondary-color);
        }

        .status-badge {
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--border-radius-full);
            font-size: var(--font-size-sm);
            font-weight: var(--font-weight-semibold);
            color: var(--white);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-right: var(--spacing-md);
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

        .btn-view-details {
            background: var(--primary-gradient);
            color: var(--white);
            border: none;
            padding: var(--spacing-sm) var(--spacing-xl);
            border-radius: var(--border-radius-lg);
            font-weight: var(--font-weight-semibold);
            font-size: var(--font-size-base);
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: var(--transition-normal);
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
            text-decoration: none;
        }

        .btn-view-details:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            color: var(--white);
            text-decoration: none;
        }

        .btn-view-details::after {
            content: '\f061';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-sm);
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

        /* Responsive Design */
        @media (max-width: 768px) {
            #content {
                padding: var(--spacing-md);
            }

            .university-title {
                font-size: var(--font-size-2xl);
            }

            .university-info-grid {
                grid-template-columns: 1fr;
                gap: var(--spacing-md);
            }

            .action-buttons {
                flex-direction: column;
                gap: var(--spacing-sm);
            }

            .application-item {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--spacing-lg);
            }

            .application-info {
                width: 100%;
            }
        }
    </style>
  </head>

  
  <?php include("navbar.php"); ?>

  <div id="content">
    <div class="university-header">
      <h1 class="university-title"><?php echo htmlspecialchars($dataUniversity['universityName']); ?></h1>
      
      <div class="university-info-grid">
        <div class="info-item">
          <div class="info-label type-label">School Type</div>
          <div class="info-value"><?php echo htmlspecialchars($schoolType); ?></div>
        </div>
        
        <div class="info-item">
          <div class="info-label name-label">School Name</div>
          <div class="info-value"><?php echo htmlspecialchars($dataUniversity['universityName']); ?></div>
        </div>
        
        <div class="info-item">
          <div class="info-label country-label">School Country</div>
          <div class="info-value"><?php echo htmlspecialchars($dataUniversity['universityCountry']); ?></div>
        </div>
        
        <div class="info-item">
          <div class="info-label commission-label">School Commission</div>
          <div class="info-value">
            <span class="commission-badge <?php echo $dataUniversity['commission'] != 0 ? 'commission-yes' : 'commission-no'; ?>">
              <?php echo $dataUniversity['commission'] != 0 ? 'Yes' : 'No'; ?>
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="action-buttons">
      <?php if ($typeAccount == 1) { ?>
        <a href="editUniversity.php?universityId=<?php echo $universityId; ?>" class="btn-action btn-primary-action">
          Edit University Info
        </a>
      <?php } ?>
      
      <a href="addApplicationUniversity.php?universityId=<?php echo $universityId; ?>" class="btn-action btn-add-action">
        Add Application
      </a>
    </div>

    <div class="search-container">
      <i class="fas fa-search search-icon"></i>
      <input type="text" id="search-bar" class="search-bar" onkeyup="searchFunction()" placeholder="Search for student names..." title="Type in a name">
    </div>
    <div class="applications-section">
      <h2 class="applications-title">Applications</h2>
      
      <ul class="applications-list" id="applications-list">
        <?php
        for ($i = 0; $i < $nApplications; $i++) { 
          // Determine status class based on status
          $statusClass = 'status-in-progress';
          if ($arrAplicationsStatus[$i] == 'Accepted') {
            $statusClass = 'status-accepted';
          } elseif ($arrAplicationsStatus[$i] == 'Rejected') {
            $statusClass = 'status-rejected';
          } elseif ($arrAplicationsStatus[$i] == 'Waitlisted') {
            $statusClass = 'status-waitlisted';
          }
        ?>
          <li class="application-item">
            <div class="application-info">
              <div class="student-name"><?php echo htmlspecialchars($arrStudentName[$i]); ?></div>
              <div class="consultant-name"><?php echo htmlspecialchars($arrStudentConsultant[$i]); ?></div>
            </div>
            <div style="display: flex; align-items: center; gap: var(--spacing-md);">
              <span class="status-badge <?php echo $statusClass; ?>">
                <?php echo htmlspecialchars($arrAplicationsStatus[$i]); ?>
              </span>
              <a href="application.php?applicationId=<?php echo $arrApplicationsId[$i]; ?>" class="btn-view-details">
                View Details
              </a>
            </div>
          </li>
        <?php } ?>
      </ul>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script>
        function searchFunction() {
            var input, filter, list, applications, i, name;
            input = document.getElementById("search-bar");
            filter = input.value.toUpperCase();
            list = document.getElementById("applications-list");
            applications = list.getElementsByClassName("application-item");
            
            for (i = 0; i < applications.length; i++) {
                name = applications[i].getElementsByClassName("student-name")[0].innerHTML;
                
                if (name.toUpperCase().indexOf(filter) > -1) {
                    applications[i].style.display = "flex";
                } else {
                    applications[i].style.display = "none";
                }
            }
        }
    </script>

    <script>
        function confirmRemove(link) {
            const userConfirmed = confirm("Are you sure you want to delete this university?");
            if (userConfirmed) {
                window.location.href = link;
            } else {
                alert("Action canceled.");
            }
        }
    </script>
</body>
</html>