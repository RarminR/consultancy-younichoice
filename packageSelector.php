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

    if ($typeAccount != 1) { // daca countul nu e admin
        header("location: index.php");
        die();
    }

    require_once "configDatabase.php";

    // Get selected grade and package from form submission
    $selectedGrade = isset($_POST['grade']) ? $_POST['grade'] : '';
    $selectedPackage = isset($_POST['package']) ? $_POST['package'] : '';
    $selectedPackageId = isset($_POST['packageId']) ? $_POST['packageId'] : '';

    // Get package details if both grade and package are selected
    $packageDetails = '';
    if ($selectedGrade && $selectedPackageId) {
        $sqlPackageDetails = "SELECT packageServices FROM packages WHERE packageId = '$selectedPackageId'";
        $resultPackageDetails = mysqli_query($link, $sqlPackageDetails);
        if ($resultPackageDetails && mysqli_num_rows($resultPackageDetails) > 0) {
            $packageDetails = mysqli_fetch_assoc($resultPackageDetails)['packageServices'];
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>Package Selector</title>

    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #f8f9fa;
            --accent-color: #28a745;
            --text-color: #333;
            --border-color: #dee2e6;
            --gradient-start: #4f235f;
            --gradient-end: #cb1b80;
        }

        body {
            background-color: #f5f7fa;
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
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

        .main-container {
            max-width: 800px;
            margin: 120px auto 40px;
            padding: 40px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        h1 {
            color: var(--gradient-start);
            font-weight: 700;
            font-size: 2.4rem;
            margin: 0;
            background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-group {
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 10px;
            display: block;
        }

        .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: white;
        }

        .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }

        .btn-primary {
            background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 16px;
            transition: all 0.3s ease;
            color: white;
            cursor: pointer;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            opacity: 0.9;
        }

        .btn-primary:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .package-details {
            margin-top: 30px;
            padding: 25px;
            background: var(--secondary-color);
            border-radius: 12px;
            border-left: 4px solid var(--accent-color);
        }

        .package-details h3 {
            color: var(--accent-color);
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }

        .package-details-content {
            line-height: 1.6;
            color: var(--text-color);
            white-space: pre-wrap;
        }

        .no-package-selected {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 40px 20px;
        }

        .select-container {
            position: relative;
        }

        .select-container::after {
            content: '\f107';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #6c757d;
        }

        @media (max-width: 768px) {
            .main-container {
                margin: 100px 20px 20px;
                padding: 25px;
            }

            h1 {
                font-size: 2rem;
            }
        }
    </style>
    
  </head>

  <?php include("navbar.php"); ?>

  <div class="main-container">
    <div class="page-header">
      <h1><i class="fas fa-box-open"></i> Package Selector</h1>
      <p class="text-muted">Select a grade and package to view details</p>
    </div>

    <form method="POST" action="">
      <div class="form-group">
        <label for="grade" class="form-label">
          <i class="fas fa-graduation-cap"></i> Select Grade
        </label>
        <div class="select-container">
          <select name="grade" id="grade" class="form-select" onchange="this.form.submit()">
            <option value="">Choose a grade...</option>
            <option value="8" <?php echo ($selectedGrade == '8') ? 'selected' : ''; ?>>Grade 8</option>
            <option value="9" <?php echo ($selectedGrade == '9') ? 'selected' : ''; ?>>Grade 9</option>
            <option value="10" <?php echo ($selectedGrade == '10') ? 'selected' : ''; ?>>Grade 10</option>
            <option value="11" <?php echo ($selectedGrade == '11') ? 'selected' : ''; ?>>Grade 11</option>
            <option value="12" <?php echo ($selectedGrade == '12') ? 'selected' : ''; ?>>Grade 12</option>
            <option value="13" <?php echo ($selectedGrade == '13') ? 'selected' : ''; ?>>Bachelor</option>
          </select>
        </div>
      </div>

      <?php if ($selectedGrade): ?>
        <div class="form-group">
          <label for="package" class="form-label">
            <i class="fas fa-box"></i> Select Package
          </label>
          <div class="select-container">
            <select name="package" id="package" class="form-select" onchange="updatePackageIdAndSubmit()">
              <option value="">Choose a package...</option>
              <?php
              // Get packages for the selected grade
              $sqlPackages = "SELECT packageId, packageName, packageType FROM packages WHERE grade = '$selectedGrade' ORDER BY packageName";
              $resultPackages = mysqli_query($link, $sqlPackages);
              
              while ($package = mysqli_fetch_assoc($resultPackages)) {
                $selected = ($selectedPackage == $package['packageName']) ? 'selected' : '';
                echo "<option value='" . $package['packageName'] . "' data-id='" . $package['packageId'] . "' $selected>" . 
                     $package['packageName'] . " (" . $package['packageType'] . ")</option>";
              }
              ?>
            </select>
            <input type="hidden" name="packageId" id="packageId" value="<?php echo $selectedPackageId; ?>">
          </div>
        </div>

        <div class="form-group">
          <button type="submit" class="btn btn-primary" <?php echo (!$selectedPackage) ? 'disabled' : ''; ?>>
            <i class="fas fa-search"></i> View Package Details
          </button>
        </div>
      <?php endif; ?>
    </form>

    <?php if ($selectedGrade && $selectedPackageId && $packageDetails): ?>
      <div class="package-details">
        <h3>
          <i class="fas fa-info-circle"></i> 
          Package Details: <?php echo $selectedPackage; ?>
        </h3>
        <div class="package-details-content">
          <?php echo htmlspecialchars($packageDetails); ?>
        </div>
      </div>
    <?php elseif ($selectedGrade && $selectedPackageId): ?>
      <div class="package-details">
        <h3>
          <i class="fas fa-info-circle"></i> 
          Package Details: <?php echo $selectedPackage; ?>
        </h3>
        <div class="no-package-selected">
          No details available for this package.
        </div>
      </div>
    <?php elseif ($selectedGrade && !$selectedPackage): ?>
      <div class="no-package-selected">
        <i class="fas fa-arrow-up"></i>
        <p>Please select a package to view its details.</p>
      </div>
    <?php else: ?>
      <div class="no-package-selected">
        <i class="fas fa-arrow-up"></i>
        <p>Please select a grade to see available packages.</p>
      </div>
    <?php endif; ?>
  </div>

  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>
  
  <script>
    function updatePackageIdAndSubmit() {
      const packageSelect = document.getElementById('package');
      const packageIdInput = document.getElementById('packageId');
      const selectedOption = packageSelect.options[packageSelect.selectedIndex];
      
      if (selectedOption && selectedOption.dataset.id) {
        packageIdInput.value = selectedOption.dataset.id;
        // Auto-submit the form when package is selected
        packageSelect.form.submit();
      } else {
        packageIdInput.value = '';
      }
    }

    // Initialize package ID if page loads with a selected package
    document.addEventListener('DOMContentLoaded', function() {
      if (document.getElementById('package').value) {
        updatePackageId();
      }
    });

    function updatePackageId() {
      const packageSelect = document.getElementById('package');
      const packageIdInput = document.getElementById('packageId');
      const selectedOption = packageSelect.options[packageSelect.selectedIndex];
      
      if (selectedOption && selectedOption.dataset.id) {
        packageIdInput.value = selectedOption.dataset.id;
      } else {
        packageIdInput.value = '';
      }
    }
  </script>
</body>
</html> 