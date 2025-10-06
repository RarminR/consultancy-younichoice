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
?>

<?php 
    require_once "configDatabase.php";
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
    <title>Consultants List</title>

    <style>
        /* Consultants List Design System Styles */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
        }

        #content {
            max-width: 1400px;
            margin: 0 auto;
            padding: var(--spacing-2xl);
            min-height: calc(100vh - 120px);
        }

        .navbar {
            background: var(--white);
            box-shadow: var(--shadow-sm);
            border-bottom: 1px solid var(--light-gray);
        }

        .consultants-container {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-2xl);
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--light-gray);
            backdrop-filter: blur(10px);
        }

        .consultants-header {
            text-align: center;
            margin-bottom: var(--spacing-2xl);
            padding-bottom: var(--spacing-lg);
            border-bottom: 2px solid var(--light-gray);
        }

        .consultants-title {
            color: var(--primary-color);
            font-size: var(--font-size-4xl);
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--spacing-md);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-md);
        }

        .consultants-title::before {
            content: '\f0c0';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-2xl);
            color: var(--primary-color);
        }

        .btn-add-consultant {
            background: var(--primary-gradient);
            color: var(--white);
            padding: var(--spacing-md) var(--spacing-xl);
            border-radius: var(--border-radius-lg);
            font-weight: var(--font-weight-semibold);
            font-size: var(--font-size-base);
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: var(--transition-normal);
            margin-bottom: var(--spacing-2xl);
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
            text-decoration: none;
            border: none;
        }

        .btn-add-consultant:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            color: var(--white);
            text-decoration: none;
        }

        .btn-add-consultant::before {
            content: '\f067';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-sm);
        }

        .search-container {
            margin-bottom: var(--spacing-2xl);
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

        .search-count {
            color: var(--primary-color);
            font-weight: var(--font-weight-semibold);
            font-size: var(--font-size-lg);
            margin-bottom: var(--spacing-xl);
            text-align: center;
            padding: var(--spacing-md);
            background: var(--light-bg);
            border-radius: var(--border-radius-lg);
            border-left: 4px solid var(--primary-color);
        }

        .consultants-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .consultant-item {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-xl);
            margin-bottom: var(--spacing-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--light-gray);
            transition: var(--transition-normal);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .consultant-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-color);
        }

        .consultant-info {
            flex: 1;
        }

        .consultant-name {
            color: var(--primary-color);
            font-size: var(--font-size-xl);
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--spacing-sm);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .consultant-name::before {
            content: '\f007';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-lg);
            color: var(--primary-color);
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

            .consultants-title {
                font-size: var(--font-size-2xl);
            }

            .consultant-item {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--spacing-lg);
            }

            .btn-view-details {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
  </head>

  <?php include("navbar.php"); ?>

  <div id="content">
    <div class="consultants-container">
        <div class="consultants-header">
            <h1 class="consultants-title">Consultants List</h1>
        </div>

        <a href="addConsultant.php" class="btn-add-consultant">
            Add Consultant
        </a>

        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="search-bar" class="search-bar" onkeyup="searchFunction()" placeholder="Search consultants..." title="Type in a name">
        </div>

        <div class="search-count">
            <i class="fas fa-users"></i>
            There are <strong><?php echo $noConsultants; ?></strong> consultants
        </div>

        <ul id="consultants-list" class="consultants-list">
            <?php 
            // Fetch consultants from database
            $sqlConsultants = "SELECT * FROM users WHERE type = 0";
            $queryConsultants = mysqli_query($link, $sqlConsultants);
            $noConsultants = mysqli_num_rows($queryConsultants);

            while ($consultant = mysqli_fetch_assoc($queryConsultants)) { 
                $urlConsultant = "consultant.php?consultantId=" . $consultant['userId'];
            ?>
                <li class="consultant-item">
                    <div class="consultant-info">
                        <div class="consultant-name"><?php echo htmlspecialchars($consultant['fullName']); ?></div>
                    </div>
                    <a href="<?php echo $urlConsultant; ?>" class="btn-view-details">
                        More Details
                    </a>
                </li>
            <?php } ?>
        </ul>
    </div>
  </div>

  <!-- Optional JavaScript -->
  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  <script>
    function searchFunction() {
        var input = document.getElementById("search-bar");
        var filter = input.value.toUpperCase();
        var list = document.getElementById("consultants-list");
        var consultants = list.getElementsByClassName("consultant-item");
        var countDisplay = 0;

        for (var i = 0; i < consultants.length; i++) {
            var nameElement = consultants[i].getElementsByClassName("consultant-name")[0];
            var name = nameElement ? nameElement.textContent || nameElement.innerText : "";
            
            if (name.toUpperCase().indexOf(filter) > -1) {
                consultants[i].style.display = "";
                countDisplay++;
            } else {
                consultants[i].style.display = "none";
            }
        }

        var searchCountElement = document.querySelector(".search-count strong");
        if (searchCountElement) {
            searchCountElement.textContent = countDisplay;
        }
    }
  </script>
</body>
</html>