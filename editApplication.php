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

    if (isset($_GET["applicationId"])) /// testez daca este setata vreo aplicatie anume
        $applicationId = $_GET["applicationId"];
    else {
        header("location: index.php");
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
    $scholarship = $applicationData['scholarship'];
    $appStatus = $applicationData['appStatus'];

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

    if (!($dataStudent['consultantId'] == $accountId || $typeAccount == 1)) { //testez daca userul curent (admin / consultant) are acces la acest elev 
        header("location: index.php");
        die();
    }

    $universityName = $dataUniversity['universityName'];
    $universityCountry = $dataUniversity['universityCountry'];


    $statusArray = ["In progress","Accepted","Rejected","Waitlisted", "Enrolled", "Suggested", "Not Interested Anymore"];
    $nStatusArray = 7;

    $statusArray[0] = trim($statusArray[0]);
    $statusArray[1] = trim($statusArray[1]);
    $statusArray[2] = trim($statusArray[2]);
    $statusArray[3] = trim($statusArray[3]);
    $statusArray[4] = trim($statusArray[4]);
    $statusArray[5] = trim($statusArray[5]);
    $statusArray[6] = trim($statusArray[6]);





    if (!($dataStudent['consultantId'] == $accountId || $typeAccount == 1)) { //testez daca userul curent (admin / consultant) are acces la acest elev 
        header("location: index.php");
        die();
    }
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
          $status = $_POST["status"];
          $scholarship = $_POST["scholarship"];
          echo $status;  # update later status and scholarship in DB 
          echo $scholarship; 

          $sqlUpdate = "UPDATE `applicationStatus` SET `appStatus` = '$status', `scholarship` = '$scholarship' WHERE `applicationId` = '$applicationId'";
          mysqli_query($link, $sqlUpdate);

          $url = $base_url . "application.php?applicationId=" . $applicationId;
          header("location: $url");
          die();
    }
?>

<?php 
    // get student data

    $sqlStudent = "SELECT * FROM studentData WHERE `studentId` = ".$studentId;
    $queryStudent = mysqli_query($link, $sqlStudent);

    $studentData = mysqli_fetch_assoc($queryStudent);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title> Edit application </title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Design System CSS -->
    <link rel="stylesheet" href="student/design-system.css">
    <script src="https://unpkg.com/react-phone-number-input@3.x/bundle/react-phone-number-input.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/react-phone-number-input@3.x/bundle/style.css"/>

    <title>Edit Application</title>

    <style>
        /* Edit Application Design System Styles */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
        }

        #content {
            max-width: 800px;
            margin: 0 auto;
            padding: var(--spacing-2xl);
            min-height: calc(100vh - 120px);
        }

        .navbar {
            background: var(--white);
            box-shadow: var(--shadow-sm);
            border-bottom: 1px solid var(--light-gray);
        }

        .edit-header {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-2xl);
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--light-gray);
            margin-bottom: var(--spacing-2xl);
            backdrop-filter: blur(10px);
        }

        .edit-title {
            color: var(--primary-color);
            font-size: var(--font-size-3xl);
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--spacing-lg);
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
        }

        .edit-title::before {
            content: '\f044';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-xl);
            color: var(--primary-color);
        }

        .form-section {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-2xl);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--light-gray);
            margin-bottom: var(--spacing-xl);
        }

        .form-group {
            margin-bottom: var(--spacing-lg);
        }

        .form-label {
            color: var(--primary-color);
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-semibold);
            margin-bottom: var(--spacing-md);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .form-label::before {
            content: '\f0f3';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-base);
        }

        .scholarship-label::before {
            content: '\f3d1';
        }

        .form-control {
            width: 100%;
            padding: var(--spacing-md) var(--spacing-lg);
            border: 2px solid var(--light-gray);
            border-radius: var(--border-radius-lg);
            font-size: var(--font-size-base);
            font-family: 'Poppins', sans-serif;
            transition: var(--transition-normal);
            background: var(--white);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: var(--shadow-md);
        }

        .form-select {
            width: 100%;
            padding: var(--spacing-md) var(--spacing-lg);
            border: 2px solid var(--light-gray);
            border-radius: var(--border-radius-lg);
            font-size: var(--font-size-base);
            font-family: 'Poppins', sans-serif;
            transition: var(--transition-normal);
            background: var(--white);
            cursor: pointer;
        }

        .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: var(--shadow-md);
        }

        .btn-submit {
            background: var(--primary-gradient);
            color: var(--white);
            border: none;
            padding: var(--spacing-md) var(--spacing-2xl);
            border-radius: var(--border-radius-lg);
            font-weight: var(--font-weight-semibold);
            font-size: var(--font-size-lg);
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: var(--transition-normal);
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-submit::before {
            content: '\f0c7';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-base);
        }

        .form-row {
            display: flex;
            gap: var(--spacing-lg);
            align-items: end;
        }

        .form-col {
            flex: 1;
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
            margin-left: var(--spacing-sm);
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

        .status-suggested {
            background: var(--info-gradient);
        }

        .status-not-interested {
            background: var(--danger-gradient);
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

        .alert-success {
            background: var(--success-light);
            color: var(--success-dark);
            border-color: var(--success-color);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            #content {
                padding: var(--spacing-md);
            }

            .edit-title {
                font-size: var(--font-size-2xl);
            }

            .form-row {
                flex-direction: column;
                gap: var(--spacing-md);
            }

            .form-col {
                width: 100%;
            }
        }
    </style>
  </head>


  <?php include("navbar.php"); ?>

  <div id="content">
    <div class="edit-header">
        <h1 class="edit-title">Edit Application</h1>
        <p style="color: var(--secondary-color); font-size: var(--font-size-lg); margin: 0;">
            <strong><?php echo $dataStudent["name"]; ?></strong>'s application at 
            <strong><?php echo $universityName; ?></strong> (<?php echo $universityCountry; ?>)
        </p>
    </div>

    <div class="form-section">
        <form method="post" onsubmit="return validateForm()" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label class="form-label" for="status">Application Status</label>
                <select id="status" name="status" class="form-select" required>
                    <option value="" disabled hidden>Select status</option>
                    <?php 
                    for ($i = 0; $i < $nStatusArray; $i++) {
                        $statusArray[$i] = trim($statusArray[$i]);
                        $appStatusTrimmed = trim($appStatus);
                        if ($appStatusTrimmed == $statusArray[$i]) {
                            echo '<option selected value="' . htmlspecialchars($statusArray[$i]) . '">' . htmlspecialchars($statusArray[$i]) . '</option>';
                        } else {
                            echo '<option value="' . htmlspecialchars($statusArray[$i]) . '">' . htmlspecialchars($statusArray[$i]) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label scholarship-label" for="scholarship">Scholarship (in $)</label>
                <input value="<?php echo $scholarship; ?>" id="scholarship" type="number" name="scholarship" class="form-control" placeholder="Enter scholarship amount" />
            </div>

            <div class="form-group" style="text-align: center; margin-top: var(--spacing-2xl);">
                <button class="btn-submit" type="submit" name="submit">
                    Apply Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script>
        function validateForm() {
            var statusSelect = document.getElementById("status");
            var scholarshipInput = document.getElementById("scholarship");
            
            // Check if status is selected
            if (statusSelect.value === "") {
                alert("Please select an application status.");
                statusSelect.focus();
                return false;
            }
            
            // Check if scholarship is a valid number
            var scholarshipValue = scholarshipInput.value;
            if (scholarshipValue !== "" && (isNaN(scholarshipValue) || parseFloat(scholarshipValue) < 0)) {
                alert("Please enter a valid scholarship amount (positive number).");
                scholarshipInput.focus();
                return false;
            }
            
            return true; // Allow form submission
        }

        // Add visual feedback for form interactions
        document.addEventListener('DOMContentLoaded', function() {
            const formControls = document.querySelectorAll('.form-control, .form-select');
            
            formControls.forEach(control => {
                control.addEventListener('focus', function() {
                    this.style.borderColor = 'var(--primary-color)';
                    this.style.boxShadow = 'var(--shadow-md)';
                });
                
                control.addEventListener('blur', function() {
                    this.style.borderColor = 'var(--light-gray)';
                    this.style.boxShadow = 'none';
                });
            });
        });
    </script>
</body>
</html>