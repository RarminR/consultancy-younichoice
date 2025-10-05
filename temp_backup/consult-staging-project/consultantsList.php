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
    <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
    <title>Consultants List</title>

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

        .search-container {
            margin-bottom: 30px;
        }

        #search-bar {
            width: 100%;
            padding: 12px 20px 12px 40px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: white;
            box-shadow: var(--card-shadow);
            background-image: url('/css/searchicon.png');
            background-position: 10px 12px;
            background-repeat: no-repeat;
        }

        #search-bar:focus {
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

        .consultant-name {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 1.2rem;
            margin-bottom: 10px;
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

        .btn-add-consultant {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-bottom: 30px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-add-consultant:hover {
            background-color: #cb1b80;
            transform: translateY(-2px);
            box-shadow: var(--hover-shadow);
            color: white;
            text-decoration: none;
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
        <h1>Consultants List</h1>

        <a href="addConsultant.php" class="btn-add-consultant">
            <i class="fas fa-plus"></i> Add Consultant
        </a>

        <div class="search-container">
            <input type="text" id="search-bar" onkeyup="searchFunction()" placeholder="Search consultants..." title="Type in a name">
        </div>

        <p>There are <span class="search-count"><?php echo $noConsultants; ?></span> consultants</p>

        <ol id="consultants-list" class="list-group list-group-numbered">
            <?php 
            // Fetch consultants from database
            $sqlConsultants = "SELECT * FROM users WHERE type = 0";
            $queryConsultants = mysqli_query($link, $sqlConsultants);
            $noConsultants = mysqli_num_rows($queryConsultants);

            while ($consultant = mysqli_fetch_assoc($queryConsultants)) { ?>
                <div class="consultant">
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="consultant-name"><?php echo $consultant['fullName']; ?></div>
                        </div>
                        <?php $urlConsultant = "consultant.php?consultantId=" . $consultant['userId']; ?>
                        <a href="<?php echo $urlConsultant; ?>">
                            <button type="button" class="btn btn-primary">More Details</button>
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
        var input = document.getElementById("search-bar");
        var filter = input.value.toUpperCase();
        var list = document.getElementById("consultants-list");
        var consultants = list.getElementsByClassName("consultant");
        var countDisplay = 0;

        for (var i = 0; i < consultants.length; i++) {
            var name = consultants[i].getElementsByClassName("consultant-name")[0].innerHTML;
            
            if (name.toUpperCase().indexOf(filter) > -1) {
                consultants[i].style.display = "";
                countDisplay++;
            } else {
                consultants[i].style.display = "none";
            }
        }

        document.getElementsByClassName("search-count")[0].innerHTML = countDisplay;
    }
  </script>
</body>
</html>