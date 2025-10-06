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
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    
    <!-- Design System CSS -->
    <link rel="stylesheet" href="student/design-system.css">
    
    <title>Packages List - Youni</title>

    <style>
        body {
            background: var(--bg-gradient-primary);
            font-family: var(--font-family-primary);
            min-height: 100vh;
        }

        .packages-container {
            width: 90%;
            max-width: 1200px;
            margin: var(--spacing-2xl) auto;
            padding: var(--spacing-lg);
        }

        .packages-header {
            text-align: center;
            margin-bottom: var(--spacing-3xl);
        }

        .packages-title {
            color: var(--primary-color);
            font-size: var(--font-size-4xl);
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--spacing-md);
        }

        .packages-subtitle {
            color: var(--secondary-color);
            font-size: var(--font-size-lg);
        }

        .selector-container {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            padding: var(--spacing-2xl);
            margin-bottom: var(--spacing-2xl);
        }

        .form-group {
            margin-bottom: var(--spacing-lg);
        }

        .form-label {
            font-weight: var(--font-weight-semibold);
            color: var(--primary-color);
            margin-bottom: var(--spacing-sm);
            display: block;
            font-size: var(--font-size-lg);
        }

        .form-select {
            width: 100%;
            padding: var(--spacing-sm) var(--spacing-lg);
            border: 2px solid var(--light-gray);
            border-radius: var(--border-radius-lg);
            font-size: var(--font-size-base);
            transition: var(--transition-normal);
            background-color: var(--white);
            color: var(--text-color);
        }

        .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 35, 95, 0.1);
        }

        .btn-edit {
            background: var(--primary-gradient);
            border: none;
            padding: var(--spacing-sm) var(--spacing-xl);
            border-radius: var(--border-radius-lg);
            font-weight: var(--font-weight-semibold);
            transition: var(--transition-normal);
            color: var(--white);
            text-decoration: none;
            display: inline-block;
            font-size: var(--font-size-base);
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            opacity: 0.9;
            color: var(--white);
            text-decoration: none;
        }

        .btn-edit:disabled {
            background: var(--gray);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .select-container {
            position: relative;
        }

        .select-container::after {
            content: '\f107';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: var(--spacing-lg);
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: var(--secondary-color);
        }

        #contentStudents {
            width: 100%;
            margin: var(--spacing-xl) auto;
            background: var(--white);
            padding: var(--spacing-2xl);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
        }

        .package-category {
            margin-bottom: var(--spacing-xl);
            background: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--light-gray);
        }

        .category-header {
            padding: var(--spacing-lg) var(--spacing-xl);
            background: var(--light-gray);
            border-radius: var(--border-radius-lg);
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: var(--transition-normal);
        }

        .category-header:hover {
            background: var(--gray);
        }

        .category-title {
            color: var(--primary-color);
            font-weight: var(--font-weight-semibold);
            margin: 0;
            display: flex;
            align-items: center;
            font-size: var(--font-size-lg);
        }

        .category-title i {
            margin-right: var(--spacing-sm);
        }

        .category-arrow {
            transition: var(--transition-normal);
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
            padding: var(--spacing-xl);
            max-height: 2000px;
        }

        .package-item {
            background: var(--light-gray);
            border-radius: var(--border-radius-lg);
            margin-bottom: var(--spacing-lg);
            transition: var(--transition-normal);
            padding: var(--spacing-lg);
        }

        .package-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .package-name {
            font-weight: var(--font-weight-semibold);
            font-size: var(--font-size-lg);
            color: var(--text-color);
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            padding: var(--spacing-sm) var(--spacing-lg);
            border-radius: var(--border-radius-full);
            transition: var(--transition-normal);
            color: var(--white);
            font-weight: var(--font-weight-medium);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .package-info {
            margin-left: var(--spacing-lg);
            color: var(--secondary-color);
            font-size: var(--font-size-sm);
        }

        .package-type-badge {
            background: var(--success-gradient);
            color: var(--white);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--border-radius-full);
            font-size: var(--font-size-xs);
            margin-left: var(--spacing-sm);
        }

        .package-count {
            background: var(--primary-gradient);
            color: var(--white);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--border-radius-full);
            font-size: var(--font-size-xs);
            margin-left: var(--spacing-sm);
        }

        .edit-section {
            text-align: center;
            padding: var(--spacing-xl);
            background: var(--light-gray);
            border-radius: var(--border-radius-lg);
            margin-top: var(--spacing-xl);
        }

        .package-grade-badge {
            background: var(--success-gradient);
            color: var(--white);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--border-radius-full);
            font-size: var(--font-size-xs);
            margin-left: var(--spacing-xs);
        }

        @media (max-width: 768px) {
            .packages-container {
                width: 95%;
                padding: var(--spacing-md);
            }
            
            .selector-container {
                padding: var(--spacing-lg);
            }
            
            #contentStudents {
                padding: var(--spacing-lg);
            }
        }
    </style>
    
  </head>

  
  <?php include("navbar.php"); ?>

  <div class="packages-container">
    <div class="packages-header">
      <h1 class="packages-title"><i class="fas fa-box"></i> Packages List</h1>
      <p class="packages-subtitle">Manage and organize student packages by grade and type</p>
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