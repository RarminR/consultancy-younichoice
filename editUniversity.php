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

    if (isset($_GET['universityId'])) // testez daca e setata o universitate
        $universityId = $_GET['universityId'];
    else {
        header("location: index.php");
        die();
    }

    $universityLink = $base_url . "university.php?universityId=" . $universityId;


    $sqlUnivesitiesData = "SELECT * FROM universities WHERE `universityId` = '$universityId'";    
    $queryUniversitiesData = mysqli_query($link, $sqlUnivesitiesData);

    if (mysqli_num_rows($queryUniversitiesData) > 0) // testez daca exista universitatea cu id-ul dat
        $dataUniversity = mysqli_fetch_assoc($queryUniversitiesData);
    else {
        header("location: index.php");
        die();
    }

    $name = $dataUniversity["universityName"];
    $country = $dataUniversity["universityCountry"];
    $commission = $dataUniversity["commission"];

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST['name'];
        $country = $_POST['country'];
        if (isset($_POST['commission']))
            $commission = $_POST['commission'];

        $sqlCheckUniversity = "SELECT * FROM universities WHERE `universityName` = '$name' AND `universityId` != '$universityId'";
        $restultCheckUniversity = mysqli_query($link, $sqlCheckUniversity);

        if (mysqli_num_rows($restultCheckUniversity) > 0) {
            $row = mysqli_fetch_assoc($restultCheckUniversity);

            $universityId = $row['universityId'];

            $errorName = "This university already exists!";
        }
        else {
            $sql = "UPDATE universities 
            SET universityName = '$name', 
                universityCountry = '$country', 
                commission = '$commission' 
            WHERE universityId = '$universityId'";
             
            mysqli_query($link, $sql);  
            header("location: university.php?universityId=".$universityId);
            die();
        }
    }



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


    <title> Edit University</title>

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

        input, select {
            border-radius: 10px; /* Adjust the value to control the roundness */
            padding: 8px 12px; /* Adjust padding as needed */
            border: 1px solid #ccc; /* Add a border for visual distinction */
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

    <h1 style = "color: rgba(79, 35, 95, .9);"> Edit University </h1>
    <br>
    <br>
    <form method = "post" onsubmit = "return validateForm()">
        <p class = "student-info"> <span class = "title-info"> Univeristy Name: </span> <input type = "text" value = "<?php echo $name; ?>" name = "name" placeholder = "Univerity name" required /> </p>
        <?php
            if (isset($errorName)) {
            ?> <span style = "color: red;"> <?php echo $errorName; ?> You can edit it at this link: <a href = "<?php echo $universityLink; ?>"> University Details </a> </span>
               <br>
            <?php
            }
        ?>

        <br>
        <p class = "student-info"> <span class = "title-info"> Univeristy Country: </span> <input type = "text" value = "<?php echo $country; ?>" name = "country" placeholder = "Univerity country" required /> </p>
        <br>
        <p class = "student-info"> <span class = "title-info"> University Commission:  </span> <input type = "number" value = "<?php echo $commission; ?>" name = "commission" placeholder = "Univerity commission(not required)" /> </p>
        <br>
        <input class="btn btn-primary" type="submit" name="submit" value="Apply changes to university information">
        <hr>
        <!-- University Checklist Section styled like other fields -->
        <p class="student-info"></p>
            <span class="title-info" style="font-size: 20px;">University Checklist:</span>
            <br>
            <form style="margin-bottom: 15px;">
                <div style="margin-bottom: 8px; font-size: 18px;">Assign Existing Checklist Items:</div>
                <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px; padding: 10px; margin-bottom: 10px; background: #fff;">
                    <?php
                    // Fetch all checklistIds already associated with this university
                    $universityChecklistIds = array();
                    $sqlUniversityChecklist = "SELECT checklistId FROM universities_checklist WHERE universityId = '$universityId' AND isActive = 1";
                    $resultUniversityChecklist = mysqli_query($link, $sqlUniversityChecklist);
                    if ($resultUniversityChecklist) {
                        while ($row = mysqli_fetch_assoc($resultUniversityChecklist)) {
                            $universityChecklistIds[] = $row['checklistId'];
                        }
                    }
                    $sqlAllChecklist = "SELECT DISTINCT c.checklistId, c.checklistName FROM checklist c LEFT JOIN applications_checklist ac ON c.checklistId = ac.checklistId WHERE ac.isCustom = 0 OR ac.checklistId IS NULL";
                    $resultAllChecklist = mysqli_query($link, $sqlAllChecklist);
                    if ($resultAllChecklist && mysqli_num_rows($resultAllChecklist) > 0) {
                        while ($row = mysqli_fetch_assoc($resultAllChecklist)) {
                            $checklistId = $row['checklistId'];
                            $checklistName = htmlspecialchars($row['checklistName']);
                            $checked = in_array($checklistId, $universityChecklistIds) ? 'checked' : '';
                            echo '<div class="form-check" style="display:block; margin-bottom:8px; font-size: 18px;">';
                            echo '<input class="form-check-input" type="checkbox" id="checklist' . $checklistId . '" ' . $checked . '>';
                            echo '<label class="form-check-label" for="checklist' . $checklistId . '" style="font-size: 18px;">[' . $checklistId . '] ' . $checklistName . '</label>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div style="font-size: 16px; color: #a55;">No checklist items available.</div>';
                    }
                    ?>
                </div>
            </form>
            <!-- Always show the Create and Assign New Task form -->
            <form id="createAssignTaskForm" style="margin-top: 10px;">
                <div style="margin-bottom: 8px; font-size: 18px;">Create and Assign New Checklist Item:</div>
                <input type="text" class="form-control" placeholder="Checklist Item Name" required style="width: 250px; display:inline-block; margin-right:10px; font-size: 18px;">
                <button type="submit" class="btn btn-primary btn-sm" style="font-size:18px; padding:4px 14px;">Create & Assign</button>
            </form>
        </p>
        <br>
    </form>



    <br>
    <br>


    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const universityId = <?php echo json_encode($universityId); ?>;
    // Checkbox AJAX
    document.querySelectorAll('.form-check-input[id^="checklist"]').forEach(function(checkbox) {
        checkbox.addEventListener('change', function(e) {
            const checklistId = this.id.replace('checklist', '');
            const checked = this.checked;
            const box = this;
            fetch('updateUniversityChecklist.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `universityId=${encodeURIComponent(universityId)}&checklistId=${encodeURIComponent(checklistId)}&checked=${checked}`
            })
            .then(function(response) {
                console.log('Fetch status:', response.status);
                console.log('Fetch ok:', response.ok);
                return response.text();
            })
            .then(function(text) {
                console.log('Raw response:', text);
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    // Null check for error message element (if any)
                    var errDiv = document.getElementById('edit-university-error');
                    if (errDiv) errDiv.textContent = 'Invalid JSON response: ' + text;
                    alert('Invalid JSON response: ' + text);
                    box.checked = !checked;
                    return;
                }
                if (!data.success) {
                    var errDiv = document.getElementById('edit-university-error');
                    if (errDiv) errDiv.textContent = 'Error: ' + (data.error || 'Unknown error');
                    alert('Error: ' + (data.error || 'Unknown error'));
                    box.checked = !checked;
                } else {
                    // Removed showToast(checked ? 'Checklist item activated' : 'Checklist item deactivated');
                }
            })
            .catch(function(err) {
                var errDiv = document.getElementById('edit-university-error');
                if (errDiv) errDiv.textContent = 'Network error: ' + err;
                console.trace('Network error trace:', err);
                alert('Network error: ' + err);
                box.checked = !checked;
            });
        });
    });

    // Create & Assign AJAX
    const createForm = document.getElementById('createAssignTaskForm');
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            console.log('Create & Assign submit handler fired');
            e.preventDefault();
            const input = createForm.querySelector('input[type="text"]');
            const checklistName = input.value.trim();
            if (!checklistName) return;
            fetch('createAndAssignChecklist.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `universityId=${encodeURIComponent(universityId)}&checklistName=${encodeURIComponent(checklistName)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Add new checkbox to the scrollable list, checked
                    const scrollBox = document.querySelector('div[style*="max-height: 200px"]');
                    if (scrollBox) {
                        const div = document.createElement('div');
                        div.className = 'form-check';
                        div.style = 'display:block; margin-bottom:8px; font-size: 18px;';
                        const checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.className = 'form-check-input';
                        checkbox.id = 'checklist' + data.checklistId;
                        checkbox.checked = true;
                        // Add AJAX handler to new checkbox
                        checkbox.addEventListener('change', function() {
                            const checked = this.checked;
                            const box = this;
                            fetch('updateUniversityChecklist.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: `universityId=${encodeURIComponent(universityId)}&checklistId=${encodeURIComponent(data.checklistId)}&checked=${checked}`
                            })
                            .then(function(response) {
                                console.log('Fetch status:', response.status);
                                console.log('Fetch ok:', response.ok);
                                return response.text();
                            })
                            .then(function(text) {
                                console.log('Raw response:', text);
                                let data;
                                try {
                                    data = JSON.parse(text);
                                } catch (e) {
                                    var errDiv = document.getElementById('edit-university-error');
                                    if (errDiv) errDiv.textContent = 'Invalid JSON response: ' + text;
                                    alert('Invalid JSON response: ' + text);
                                    box.checked = !checked;
                                    return;
                                }
                                if (!data.success) {
                                    var errDiv = document.getElementById('edit-university-error');
                                    if (errDiv) errDiv.textContent = 'Error: ' + (data.error || 'Unknown error');
                                    alert('Error: ' + (data.error || 'Unknown error'));
                                    box.checked = !checked;
                                } else {
                                    // Removed showToast(checked ? 'Checklist item activated' : 'Checklist item deactivated');
                                }
                            })
                            .catch(function(err) {
                                var errDiv = document.getElementById('edit-university-error');
                                if (errDiv) errDiv.textContent = 'Network error: ' + err;
                                console.trace('Network error trace:', err);
                                alert('Network error: ' + err);
                                box.checked = !checked;
                            });
                        });
                        const label = document.createElement('label');
                        label.className = 'form-check-label';
                        label.htmlFor = 'checklist' + data.checklistId;
                        label.style = 'font-size: 18px;';
                        label.textContent = `[${data.checklistId}] ${data.checklistName}`;
                        div.appendChild(checkbox);
                        div.appendChild(label);
                        scrollBox.appendChild(div);
                    }
                    input.value = '';
                } else {
                    alert('Error: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(() => {
                alert('Network error.');
            });
        });
    }
});
</script>

</body>
</html>