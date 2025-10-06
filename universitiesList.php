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

    $button1Color = $button2Color = $button3Color = "#4f235f";
    $button1HoverColor = $button2HoverColor = $button3HoverColor = "#cb1b80";

    if (!isset($_GET["university-filter"]) || $_GET["university-filter"] == 0)
        $button1Color = "#cb1b80";
    else if ($_GET["university-filter"] == 1)
        $button2Color = "#cb1b80";
    else if ($_GET["university-filter"] == 2)
        $button3Color = "#cb1b80";
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
    <title>Universities List</title>

    <style>
        /* Universities List Design System Styles */
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

        .universities-container {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-2xl);
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--light-gray);
            backdrop-filter: blur(10px);
        }

        .universities-header {
            text-align: center;
            margin-bottom: var(--spacing-2xl);
            padding-bottom: var(--spacing-lg);
            border-bottom: 2px solid var(--light-gray);
        }

        .universities-title {
            color: var(--primary-color);
            font-size: var(--font-size-4xl);
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--spacing-md);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-md);
        }

        .universities-title::before {
            content: '\f19c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-2xl);
            color: var(--primary-color);
        }

        .filter-buttons {
            display: flex;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-2xl);
            justify-content: center;
        }

        .filter-btn {
            flex: 1;
            max-width: 300px;
            padding: var(--spacing-md) var(--spacing-xl);
            border-radius: var(--border-radius-lg);
            font-weight: var(--font-weight-semibold);
            font-size: var(--font-size-base);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-sm);
            transition: var(--transition-normal);
            border: 2px solid transparent;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            text-decoration: none;
        }

        .filter-btn.active {
            background: var(--primary-gradient);
            color: var(--white);
            border-color: var(--primary-color);
        }

        .filter-btn.inactive {
            background: var(--white);
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .filter-btn.inactive:hover {
            background: var(--primary-color);
            color: var(--white);
        }

        .search-container {
            display: flex;
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-2xl);
        }

        .search-bar {
            flex: 1;
            padding: var(--spacing-md) var(--spacing-lg) var(--spacing-md) var(--spacing-3xl);
            border: 2px solid var(--light-gray);
            border-radius: var(--border-radius-lg);
            font-size: var(--font-size-base);
            font-family: 'Poppins', sans-serif;
            transition: var(--transition-normal);
            background: var(--white);
            position: relative;
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

        .universities-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .university-item {
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

        .university-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-color);
        }

        .university-info {
            flex: 1;
        }

        .university-name {
            color: var(--primary-color);
            font-size: var(--font-size-xl);
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--spacing-md);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .university-name::before {
            content: '\f19c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-lg);
            color: var(--primary-color);
        }

        .university-details {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
        }

        .detail-row {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .detail-label {
            color: var(--secondary-color);
            font-weight: var(--font-weight-semibold);
            font-size: var(--font-size-sm);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            min-width: 100px;
        }

        .detail-value {
            color: var(--text-color);
            font-weight: var(--font-weight-medium);
            font-size: var(--font-size-base);
        }

        .commission-badge {
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

        .btn-view-applications {
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

        .btn-view-applications:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            color: var(--white);
            text-decoration: none;
        }

        .btn-view-applications::after {
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

            .universities-title {
                font-size: var(--font-size-2xl);
            }

            .filter-buttons {
                flex-direction: column;
                gap: var(--spacing-sm);
            }

            .filter-btn {
                max-width: none;
            }

            .search-container {
                flex-direction: column;
                gap: var(--spacing-md);
            }

            .university-item {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--spacing-lg);
            }

            .university-details {
                width: 100%;
            }
        }
    </style>
  </head>

  <?php include("navbar.php"); ?>

  <div id="content">
    <div class="universities-container">
        <div class="universities-header">
            <h1 class="universities-title">Universities List</h1>
        </div>

        <div class="filter-buttons">
            <a href="<?php echo $base_url; ?>universitiesList.php?university-filter=0" class="filter-btn <?php echo (!isset($_GET["university-filter"]) || $_GET["university-filter"] == 0) ? 'active' : 'inactive'; ?>">
                <i class="fas fa-university"></i>
                All Universities
            </a>
            <a href="<?php echo $base_url; ?>universitiesList.php?university-filter=1" class="filter-btn <?php echo (isset($_GET["university-filter"]) && $_GET["university-filter"] == 1) ? 'active' : 'inactive'; ?>">
                <i class="fas fa-check-circle"></i>
                Commissionable Universities
            </a>
            <a href="<?php echo $base_url; ?>universitiesList.php?university-filter=2" class="filter-btn <?php echo (isset($_GET["university-filter"]) && $_GET["university-filter"] == 2) ? 'active' : 'inactive'; ?>">
                <i class="fas fa-times-circle"></i>
                Non-Commissionable Universities
            </a>
        </div>

        <div class="search-container">
            <div style="position: relative; flex: 1;">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="search-bar-name" class="search-bar" onkeyup="searchFunction()" placeholder="Search by university name..." title="Type in a name">
            </div>
            <div style="position: relative; flex: 1;">
                <i class="fas fa-globe search-icon"></i>
                <input type="text" id="search-bar-country" class="search-bar" onkeyup="searchFunction()" placeholder="Search by country..." title="Type in a country">
            </div>
        </div>

        <div class="search-count">
            <i class="fas fa-university"></i>
            There are <strong><?php echo $noUniversities; ?></strong> universities in your search
        </div>

        <ul id="universities-list" class="universities-list">
            <?php

            if (!isset($_GET["university-filter"]) || $_GET["university-filter"] == 0)
                $sqlUniversities = "SELECT * FROM universities WHERE `institutionType` = 0";
            else if ($_GET["university-filter"] == 1)
                $sqlUniversities = "SELECT * FROM universities WHERE `commission` != 0 AND `institutionType` = 0";
            else if ($_GET["university-filter"] == 2)
                $sqlUniversities = "SELECT * FROM universities WHERE `commission` = 0 AND `institutionType` = 0";


            $queryUniversities = mysqli_query($link, $sqlUniversities);


            $noUniversities = mysqli_num_rows($queryUniversities);
            ?>
            <?php while ($university = mysqli_fetch_assoc($queryUniversities)) { ?>
                <li class="university-item">
                    <div class="university-info">
                        <div class="university-name"><?php echo htmlspecialchars($university['universityName']); ?></div>
                        <div class="university-details">
                            <div class="detail-row">
                                <span class="detail-label">Country:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($university['universityCountry']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Commission:</span>
                                <span class="commission-badge <?php echo $university['commission'] != 0 ? 'commission-yes' : 'commission-no'; ?>">
                                    <?php echo $university['commission'] != 0 ? 'Yes' : 'No'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php $urlUniversity = "university.php?universityId=" . $university['universityId']; ?>
                    <a href="<?php echo $urlUniversity; ?>" class="btn-view-applications">
                        View Applications
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
        var inputName = document.getElementById("search-bar-name");
        var inputCountry = document.getElementById("search-bar-country");
        var filterName = inputName.value.toUpperCase();
        var filterCountry = inputCountry.value.toUpperCase();
        var list = document.getElementById("universities-list");
        var universities = list.getElementsByClassName("university-item");
        var countDisplay = 0;

        for (var i = 0; i < universities.length; i++) {
            var name = universities[i].getElementsByClassName("university-name")[0].innerHTML;
            var country = universities[i].getElementsByClassName("detail-value")[0].innerText;

            if (name.toUpperCase().indexOf(filterName) > -1 && country.toUpperCase().indexOf(filterCountry) > -1) {
                universities[i].style.display = "flex";
                countDisplay++;
            } else {
                universities[i].style.display = "none";
            }
        }

        document.getElementsByClassName("search-count")[0].innerHTML = '<i class="fas fa-university"></i> There are <strong>' + countDisplay + '</strong> universities in your search';
    }
  </script>
</body>
</html>