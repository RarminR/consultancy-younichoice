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
    
    // Set proper character encoding for the database connection
    mysqli_set_charset($link, "utf8mb4");
?>

<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
    <title>Summer Schools list</title>

    <style>
        :root {
            --primary-color: #4f235f;
            --secondary-color: #6c757d;
            --accent-color: #007bff;
            --background-color: #f8f9fa;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        #content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            margin-top: 100px;
        }

        #contentStudents {
            background-color: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            padding: 30px;
            position: relative;
            z-index: 1;
        }

        .navbar {
            height: 80px;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        h1 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 30px;
            font-size: 2.2rem;
        }

        .filter-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .filter-buttons .btn {
            flex: 1;
            padding: 12px 24px;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }

        .filter-buttons .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--hover-shadow);
        }

        .button1, .button2, .button3 {
            background-color: var(--primary-color);
            border: none;
        }

        .button1:hover, .button2:hover, .button3:hover {
            background-color: #cb1b80;
        }

        .search-container {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        #search-bar-name, #search-bar-country {
            flex: 1;
            padding: 12px 20px 12px 40px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: white;
            box-shadow: var(--card-shadow);
        }

        #search-bar-name:focus, #search-bar-country:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        .list-group-item {
            margin-bottom: 15px;
            border-radius: 8px !important;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            padding: 20px;
        }

        .list-group-item:hover {
            box-shadow: var(--hover-shadow);
            transform: translateY(-2px);
        }

        .university-name {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .university-info {
            color: var(--secondary-color);
            font-size: 0.95rem;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-label {
            font-weight: 600;
            min-width: 100px;
            color: var(--primary-color);
        }

        .commission-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            color: white;
        }

        .commission-yes {
            background-color: #28a745;
        }

        .commission-no {
            background-color: #dc3545;
        }

        .btn-primary {
            background-color: var(--accent-color);
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--hover-shadow);
        }

        .search-count {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 20px;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--secondary-color);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }
    </style>
  </head>

  <?php include("navbar.php"); ?>

  <div id="content">
    <div id="contentStudents">
        <div class="filter-buttons">
            <a href="<?php echo $base_url; ?>summerList.php?university-filter=0">
                <button class="btn btn-primary button1">All Programs</button>
            </a>
            <a href="<?php echo $base_url; ?>summerList.php?university-filter=1">
                <button class="btn btn-primary button2">Commissionable Programs</button>
            </a>
            <a href="<?php echo $base_url; ?>summerList.php?university-filter=2">
                <button class="btn btn-primary button3">Non-Commissionable Programs</button>
            </a>
        </div>

        <h1>Summer Schools List</h1>

        <div class="search-container">
            <input type="text" id="search-bar-name" onkeyup="searchFunction()" placeholder="Search by program name..." title="Type in a name">
            <input type="text" id="search-bar-country" onkeyup="searchFunction()" placeholder="Search by country..." title="Type in a country">
        </div>

        <p>There are <span class="search-count"><?php echo $noUniversities; ?></span> summer programs in your search</p>

        <ol id="universities-list" class="list-group list-group-numbered">
            <?php
            if (!isset($_GET["university-filter"]) || $_GET["university-filter"] == 0)
                $sqlUniversities = "SELECT * FROM universities WHERE `institutionType` = 1";
            else if ($_GET["university-filter"] == 1)
                $sqlUniversities = "SELECT * FROM universities WHERE `commission` != 0 AND `institutionType` = 1";
            else if ($_GET["university-filter"] == 2)
                $sqlUniversities = "SELECT * FROM universities WHERE `commission` = 0 AND `institutionType` = 1";

            $queryUniversities = mysqli_query($link, $sqlUniversities);
            $noUniversities = mysqli_num_rows($queryUniversities);

            while ($university = mysqli_fetch_assoc($queryUniversities)) { ?>
                <div class="university">
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="university-name"><?php echo $university['universityName']; ?></div>
                            <div class="university-info">
                                <span class="info-label">Country:</span>
                                <span><?php echo $university['universityCountry']; ?></span>
                            </div>
                            <div class="university-info">
                                <span class="info-label">Commission:</span>
                                <span class="commission-badge <?php echo $university['commission'] != 0 ? 'commission-yes' : 'commission-no'; ?>">
                                    <?php echo $university['commission'] != 0 ? 'Yes' : 'No'; ?>
                                </span>
                            </div>
                        </div>
                        <?php $urlUniversity = "university.php?universityId=" . $university['universityId']; ?>
                        <a href="<?php echo $urlUniversity; ?>">
                            <button type="button" class="btn btn-primary">See applications</button>
                        </a>
                    </li>
                </div>
            <?php } ?>
        </ol>
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
        var universities = list.getElementsByClassName("university");
        var countDisplay = 0;

        for (var i = 0; i < universities.length; i++) {
            var name = universities[i].getElementsByClassName("university-name")[0].innerHTML;
            var country = universities[i].getElementsByClassName("university-info")[0].innerText;

            if (name.toUpperCase().indexOf(filterName) > -1 && country.toUpperCase().indexOf(filterCountry) > -1) {
                universities[i].style.display = "";
                countDisplay++;
            } else {
                universities[i].style.display = "none";
            }
        }

        document.getElementsByClassName("search-count")[0].innerHTML = countDisplay;
    }
  </script>
</body>
</html>