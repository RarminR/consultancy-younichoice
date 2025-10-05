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
    <title>Packages List</title>

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
        }

        .selector-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 8px;
            display: block;
        }

        .form-select {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background-color: white;
        }

        .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }

        .btn-edit {
            background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            color: white;
            text-decoration: none;
            display: inline-block;
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            opacity: 0.9;
            color: white;
            text-decoration: none;
        }

        .btn-edit:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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

        #contentStudents {
            width: 80%;
            margin: 20px auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }

        .package-category {
            margin-bottom: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .category-header {
            padding: 15px 20px;
            background: var(--secondary-color);
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.3s;
        }

        .category-header:hover {
            background: #e9ecef;
        }

        .category-title {
            color: var(--primary-color);
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .category-title i {
            margin-right: 10px;
        }

        .category-arrow {
            transition: transform 0.3s;
        }

        .category-arrow.expanded {
            transform: rotate(180deg);
        }

        .category-content {
            padding: 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out, padding 0.3s ease;
        }

        .category-content.expanded {
            padding: 20px;
            max-height: 2000px;
        }

        .package-item {
            background: var(--secondary-color);
            border-radius: 8px;
            margin-bottom: 15px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .package-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .package-name {
            font-weight: 600;
            font-size: 1.1em;
            color: var(--text-color);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #357abd;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .package-info {
            margin-left: 15px;
            color: #666;
            font-size: 0.9em;
        }

        .package-type-badge {
            background-color: var(--accent-color);
            color: white;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.8em;
            margin-left: 10px;
        }

        .package-count {
            background-color: var(--primary-color);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            margin-left: 10px;
        }

        .edit-section {
            text-align: center;
            padding: 20px;
            background: var(--secondary-color);
            border-radius: 12px;
            margin-top: 20px;
        }

        .package-grade-badge {
            background-color: var(--accent-color);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            margin-left: 5px;
        }
    </style>
    
  </head>

  
  <?php include("navbar.php"); ?>

  <div class="container-fluid mt-5">
    <div id="contentStudents">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-box"></i> Packages List</h1>
        </div>

        <!-- Package Selector Section -->
        <div class="selector-container">
            <h3><i class="fas fa-search"></i> Quick Package Selector</h3>
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="grade" class="form-label">
                                <i class="fas fa-graduation-cap"></i> Select Grade
                            </label>
                            <div class="select-container">
                                <select name="grade" id="grade" class="form-select" onchange="this.form.submit()">
                                    <option value="">Choose a grade...</option>
                                    <option value="9" <?php echo ($selectedGrade == '9') ? 'selected' : ''; ?>>Grade 9</option>
                                    <option value="10" <?php echo ($selectedGrade == '10') ? 'selected' : ''; ?>>Grade 10</option>
                                    <option value="11" <?php echo ($selectedGrade == '11') ? 'selected' : ''; ?>>Grade 11</option>
                                    <option value="12" <?php echo ($selectedGrade == '12') ? 'selected' : ''; ?>>Grade 12</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($selectedGrade): ?>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="package" class="form-label">
                                <i class="fas fa-box"></i> Select Package
                            </label>
                            <div class="select-container">
                                <!-- PHP Test: Grade selected is <?php echo $selectedGrade; ?> -->
                                <select name="package" id="package" class="form-select" onchange="updatePackageIdAndSubmit()">
                                    <option value="">Choose a package...</option>
                                    <?php
                                    // Simple test to see if PHP is working
                                    echo "<!-- PHP is working -->";
                                    
                                    if ($selectedGrade) {
                                        echo "<!-- Grade is selected: $selectedGrade -->";
                                        
                                        // Get packages for the selected grade - removed packageType since it doesn't exist
                                        $sqlPackages = "SELECT packageId, packageName, grade FROM packages WHERE grade = " . intval($selectedGrade) . " ORDER BY packageName";
                                        echo "<!-- SQL Query: $sqlPackages -->";
                                        
                                        $resultPackages = mysqli_query($link, $sqlPackages);
                                        
                                        if (!$resultPackages) {
                                            echo "<option value='' disabled>Database error: " . mysqli_error($link) . "</option>";
                                        } else {
                                            $packageCount = mysqli_num_rows($resultPackages);
                                            echo "<!-- Found $packageCount packages -->";
                                            
                                            if ($packageCount == 0) {
                                                echo "<option value='' disabled>No packages found for Grade $selectedGrade</option>";
                                                // Let's check what grades actually exist in the database
                                                $checkGrades = "SELECT DISTINCT grade FROM packages ORDER BY grade";
                                                $resultGrades = mysqli_query($link, $checkGrades);
                                                if ($resultGrades) {
                                                    $grades = [];
                                                    while ($row = mysqli_fetch_assoc($resultGrades)) {
                                                        $grades[] = $row['grade'];
                                                    }
                                                    echo "<!-- Available grades in DB: " . implode(', ', $grades) . " -->";
                                                }
                                            } else {
                                                while ($package = mysqli_fetch_assoc($resultPackages)) {
                                                    $selected = ($selectedPackage == $package['packageName']) ? 'selected' : '';
                                                    echo "<option value='" . $package['packageName'] . "' data-id='" . $package['packageId'] . "' $selected>" . 
                                                         $package['packageName'] . " (Grade " . $package['grade'] . ")</option>";
                                                }
                                            }
                                        }
                                    } else {
                                        echo "<!-- No grade selected -->";
                                    }
                                    ?>
                                </select>
                                <input type="hidden" name="packageId" id="packageId" value="<?php echo $selectedPackageId; ?>">
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($selectedGrade && $selectedPackage): ?>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">&nbsp;</label>
                            <a href="editPackage.php?packageId=<?php echo $selectedPackageId; ?>" class="btn-edit">
                                <i class="fas fa-edit"></i> Edit Package
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Original Package List -->
        <?php
        // Define grade categories
        $gradeCategories = [
            'Grade 8' => [8],
            'Grade 9' => [9],
            'Grade 10' => [10],
            'Grade 11' => [11],
            'Grade 12' => [12],
            'Bachelor' => [13]
        ];

        // Get all packages
        $sqlPackages = "SELECT * FROM packages ORDER BY packageName";
        $queryPackages = mysqli_query($link, $sqlPackages);
        $packages = [];
        
        while ($package = mysqli_fetch_assoc($queryPackages)) {
            $packages[] = $package;
        }

        // Display packages by category
        foreach ($gradeCategories as $category => $grades) {
            $categoryPackages = array_filter($packages, function($package) use ($grades) {
                return in_array($package['grade'], $grades);
            });

            if (!empty($categoryPackages)) {
                ?>
                <div class="package-category">
                    <div class="category-header" onclick="toggleCategory(this)">
                        <h2 class="category-title">
                            <i class="fas fa-graduation-cap"></i>
                            <?php echo $category; ?>
                            <span class="package-count"><?php echo count($categoryPackages); ?></span>
                        </h2>
                        <i class="fas fa-chevron-down category-arrow"></i>
                    </div>
                    <div class="category-content">
                        <div class="list-group">
                            <?php foreach ($categoryPackages as $package) { ?>
                                <div class="package-item list-group-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="package-name">
                                            <?php echo $package['packageName']; ?>
                                            <span class="package-grade-badge">Grade <?php echo $package['grade']; ?></span>
                                        </div>
                                        <div class="package-info">
                                            <?php if (!empty($package['description'])) { ?>
                                                <p class="mb-0"><?php echo $package['description']; ?></p>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <a href="editPackage.php?packageId=<?php echo $package['packageId']; ?>" class="btn btn-primary">
                                        <i class="fas fa-edit"></i> Edit Services
                                    </a>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
        ?>
    </div>
  </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>
    <script>
        function toggleCategory(element) {
            const content = element.nextElementSibling;
            const arrow = element.querySelector('.category-arrow');
            
            content.classList.toggle('expanded');
            arrow.classList.toggle('expanded');
        }

        function updatePackageIdAndSubmit() {
            const packageSelect = document.getElementById('package');
            const packageIdInput = document.getElementById('packageId');
            
            if (!packageSelect || !packageIdInput) {
                console.error('Package select or package ID input not found');
                return;
            }
            
            const selectedOption = packageSelect.options[packageSelect.selectedIndex];
            
            if (selectedOption && selectedOption.dataset.id) {
                packageIdInput.value = selectedOption.dataset.id;
                console.log('Package ID set to:', selectedOption.dataset.id);
                // Auto-submit the form when package is selected
                packageSelect.form.submit();
            } else {
                packageIdInput.value = '';
                console.log('No package selected or no ID found');
            }
        }

        // Initialize package ID if page loads with a selected package
        document.addEventListener('DOMContentLoaded', function() {
            const packageSelect = document.getElementById('package');
            if (packageSelect && packageSelect.value) {
                updatePackageId();
            }
        });

        function updatePackageId() {
            const packageSelect = document.getElementById('package');
            const packageIdInput = document.getElementById('packageId');
            
            if (!packageSelect || !packageIdInput) {
                return;
            }
            
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