<?php
    session_start();

    require_once "configDatabase.php";

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

    // Handle AJAX requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $response = array();
        
        if ($_POST['action'] === 'getPackages') {
            $grade = mysqli_real_escape_string($link, $_POST['grade']);
            $sql = "SELECT packageId, packageName FROM packages WHERE grade = '$grade' ORDER BY packageName";
            $result = mysqli_query($link, $sql);
            
            $packages = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $packages[] = $row;
            }
            
            $response['success'] = true;
            $response['packages'] = $packages;
            echo json_encode($response);
            exit;
        }
        
        if ($_POST['action'] === 'getServices') {
            $packageId = mysqli_real_escape_string($link, $_POST['packageId']);
            $sql = "SELECT packageServices FROM packages WHERE packageId = '$packageId'";
            $result = mysqli_query($link, $sql);
            
            if ($row = mysqli_fetch_assoc($result)) {
                $response['success'] = true;
                $response['services'] = $row['packageServices'];
                echo json_encode($response);
                exit;
            }
        }
        
        if ($_POST['action'] === 'updateServices') {
            $packageId = mysqli_real_escape_string($link, $_POST['packageId']);
            $services = mysqli_real_escape_string($link, $_POST['services']);
            
            $sql = "UPDATE packages SET packageServices = '$services' WHERE packageId = '$packageId'";
            if (mysqli_query($link, $sql)) {
                $response['success'] = true;
                $response['message'] = 'Services updated successfully!';
            } else {
                $response['success'] = false;
                $response['message'] = 'Error updating services: ' . mysqli_error($link);
            }
            echo json_encode($response);
            exit;
        }
    }

    // Get all unique grade-package combinations
    $sqlCombinations = "SELECT DISTINCT grade, packageId, packageName FROM packages ORDER BY grade, packageName";
    $resultCombinations = mysqli_query($link, $sqlCombinations);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
    <script src="https://unpkg.com/react-phone-number-input@3.x/bundle/react-phone-number-input.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/react-phone-number-input@3.x/bundle/style.css"/>

    <title>Edit Package Services</title>

    <style>
        #content {
            width: 70%;
            margin: auto;
        }
        #search-bar {
            background-image: url('/css/searchicon.png');
            background-position: 10px 12px;
            background-repeat: no-repeat;
            width: 100%;
            font-size: 16px;
            padding: 12px 20px 12px 40px;
            border: 1px solid #ddd;
            margin-bottom: 12px;
        }
        .full-name {
            font-weight: bold;
        }

        .navbar {
            height: 150px;
        }

        .badge {
            /* height: 30px; */
            font-size: 15px;
            color: white;
            background-color: var(--pink) !important;
            position: fixed;
            right: 50%;
        }
        
        .fw-bold {
            font-weight: bold;
        }

        .student-info {
            font-size: 18px;
            font-weight: bold;
        }

        .title-info {
            font-weight: bold;
            color: var(--pink);
            font-size: 20px;
        }

        .info-row {
            display: inline; /* the default for span */
        }

        .statusSelect {
            width: 100px;
            height: 25px;
        }

        input[name = "name"] {
            width: 30%;
        }

        input[name = "country"] {
            width: 30%;
        }

        input[name = "commission"] {
            width: 30%;
        }

        input, select, textarea {
            border-radius: 10px; /* Adjust the value to control the roundness */
            padding: 8px 12px; /* Adjust padding as needed */
            border: 1px solid #ccc; /* Add a border for visual distinction */
        }

        textarea {
            font-weight: normal !important;
        }

        .step-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }

        .hidden {
            display: none;
        }

        .alert {
            margin-top: 20px;
        }

        .selection-row {
            display: flex;
            gap: 20px;
            align-items: center;
            margin-bottom: 15px;
        }

        .selection-row select {
            min-width: 200px;
        }

    </style>
  </head>

  
  <?php include("navbar.php"); ?>

  <div id = "content">
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>

    <h1 style = "color: rgba(79, 35, 95, .9);">Edit Package Services</h1>
    <br>
    <br>

    <!-- Step 1: Grade and Package Selection -->
    <div class="step-section" id="selectionSection">
        <h3>Step 1: Select Grade and Package</h3>
        <div class="selection-row">
            <p class="student-info">
                <span class="title-info">Grade: </span>
                <select id="gradeSelect" name="grade" required>
                    <option value="">Select a grade...</option>
                    <?php 
                    $currentGrade = null;
                    while ($row = mysqli_fetch_assoc($resultCombinations)) { 
                        if ($currentGrade !== $row['grade']) {
                            $currentGrade = $row['grade'];
                            echo '<option value="' . $row['grade'] . '">Grade ' . $row['grade'] . '</option>';
                        }
                    } 
                    ?>
                </select>
            </p>
        </div>
        <div class="selection-row">
            <p class="student-info">
                <span class="title-info">Package: </span>
                <select id="packageSelect" name="package" required>
                    <option value="">Select a package...</option>
                </select>
            </p>
        </div>
    </div>

    <!-- Step 2: Services Editing -->
    <div class="step-section hidden" id="servicesSection">
        <h3>Step 2: Edit Package Services</h3>
        <p class="student-info">
            <span class="title-info">Package Services: </span>
        </p>
        <br>
        <textarea id="servicesTextarea" rows="15" cols="50" name="services" placeholder="Enter package services..."></textarea>
        <br>
        <br>
        <button class="btn btn-primary" id="updateBtn" type="button">Update Services</button>
    </div>

    <!-- Alert for messages -->
    <div id="alertContainer"></div>

    <br>
    <br>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <script>
        let selectedPackageId = null;

        // Grade selection handler
        document.getElementById('gradeSelect').addEventListener('change', function() {
            const grade = this.value;
            if (grade) {
                // Load packages for selected grade
                fetch('editPackage-new.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=getPackages&grade=' + encodeURIComponent(grade)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const packageSelect = document.getElementById('packageSelect');
                        packageSelect.innerHTML = '<option value="">Select a package...</option>';
                        
                        data.packages.forEach(package => {
                            const option = document.createElement('option');
                            option.value = package.packageId;
                            option.textContent = package.packageName;
                            packageSelect.appendChild(option);
                        });
                        
                        // Reset services section
                        document.getElementById('servicesSection').classList.add('hidden');
                        selectedPackageId = null;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Error loading packages', 'danger');
                });
            } else {
                // Reset package dropdown and hide services section
                document.getElementById('packageSelect').innerHTML = '<option value="">Select a package...</option>';
                document.getElementById('servicesSection').classList.add('hidden');
                selectedPackageId = null;
            }
        });

        // Package selection handler
        document.getElementById('packageSelect').addEventListener('change', function() {
            const packageId = this.value;
            if (packageId) {
                selectedPackageId = packageId;
                
                // Load services for selected package
                fetch('editPackage-new.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=getServices&packageId=' + encodeURIComponent(packageId)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('servicesTextarea').value = data.services;
                        
                        // Show services section
                        document.getElementById('servicesSection').classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Error loading services', 'danger');
                });
            } else {
                // Hide services section if package is deselected
                document.getElementById('servicesSection').classList.add('hidden');
                selectedPackageId = null;
            }
        });

        // Update services handler
        document.getElementById('updateBtn').addEventListener('click', function() {
            if (!selectedPackageId) {
                showAlert('Please select a package first', 'warning');
                return;
            }
            
            const services = document.getElementById('servicesTextarea').value;
            if (!services.trim()) {
                showAlert('Please enter package services', 'warning');
                return;
            }
            
            fetch('editPackage-new.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=updateServices&packageId=' + encodeURIComponent(selectedPackageId) + '&services=' + encodeURIComponent(services)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error updating services', 'danger');
            });
        });

        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            `;
            alertContainer.appendChild(alertDiv);
            
            // Auto-remove alert after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.parentNode.removeChild(alertDiv);
                }
            }, 5000);
        }
    </script>

</body>
</html>