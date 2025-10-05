<?php
    session_start();
    require_once "configDatabase.php";

    if (!isset($_SESSION['type'])) {
        header("location: index.php");
        die();
    }

    if ($_SESSION['type'] != 1) {
        header("location: index.php");
        die();
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Check Packages</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .grade-section { margin: 20px 0; }
    </style>
</head>
<body>
    <h1>Packages in Database</h1>
    
    <?php
    // Get all packages
    $sqlAllPackages = "SELECT * FROM packages ORDER BY grade, packageName";
    $resultAllPackages = mysqli_query($link, $sqlAllPackages);
    
    if (!$resultAllPackages) {
        echo "<p>Error: " . mysqli_error($link) . "</p>";
    } else {
        $totalPackages = mysqli_num_rows($resultAllPackages);
        echo "<p><strong>Total packages found: $totalPackages</strong></p>";
        
        if ($totalPackages > 0) {
            echo "<table>";
            echo "<tr><th>Package ID</th><th>Package Name</th><th>Package Type</th><th>Grade</th><th>Description</th></tr>";
            
            while ($package = mysqli_fetch_assoc($resultAllPackages)) {
                echo "<tr>";
                echo "<td>" . $package['packageId'] . "</td>";
                echo "<td>" . $package['packageName'] . "</td>";
                echo "<td>" . $package['packageType'] . "</td>";
                echo "<td>" . $package['grade'] . "</td>";
                echo "<td>" . (isset($package['description']) ? $package['description'] : 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Show packages by grade
            echo "<h2>Packages by Grade</h2>";
            $grades = [8, 9, 10, 11, 12, 13];
            
            foreach ($grades as $grade) {
                $sqlGradePackages = "SELECT * FROM packages WHERE grade = $grade ORDER BY packageName";
                $resultGradePackages = mysqli_query($link, $sqlGradePackages);
                $count = mysqli_num_rows($resultGradePackages);
                
                echo "<div class='grade-section'>";
                echo "<h3>Grade $grade ($count packages)</h3>";
                
                if ($count > 0) {
                    echo "<ul>";
                    while ($package = mysqli_fetch_assoc($resultGradePackages)) {
                        echo "<li>" . $package['packageName'] . " (" . $package['packageType'] . ")</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<p>No packages found for this grade.</p>";
                }
                echo "</div>";
            }
        } else {
            echo "<p>No packages found in the database.</p>";
        }
    }
    ?>
    
    <h2>Database Structure</h2>
    <?php
    $sqlStructure = "DESCRIBE packages";
    $resultStructure = mysqli_query($link, $sqlStructure);
    
    if ($resultStructure) {
        echo "<table>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        while ($field = mysqli_fetch_assoc($resultStructure)) {
            echo "<tr>";
            echo "<td>" . $field['Field'] . "</td>";
            echo "<td>" . $field['Type'] . "</td>";
            echo "<td>" . $field['Null'] . "</td>";
            echo "<td>" . $field['Key'] . "</td>";
            echo "<td>" . $field['Default'] . "</td>";
            echo "<td>" . $field['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    ?>
</body>
</html> 