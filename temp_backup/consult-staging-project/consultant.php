<?php

    session_start();
    require_once "configDatabase.php";

    if (!isset($_SESSION['type'])) { // testez daca userul est logat
        header("location: index.php");
        die();
    }
    else {
        $typeAccount = $_SESSION["type"];
        $accountId = $_SESSION["id"];
    }

    if ($typeAccount != 1) {
        header("location: index.php");
        die();
    }

    if (isset($_GET['consultantId'])) // testez daca e setat un consultant
        $userId = $_GET['consultantId'];
    else {
        header("location: index.php");
        die();
    }

    $sqlConsultantData = "SELECT * FROM users WHERE `userId` = '$userId'";
    $queryConsultantData = mysqli_query($link, $sqlConsultantData);

    if (mysqli_num_rows($queryConsultantData) > 0) // testez daca exista vreun consultatn cu id-ul dat
        $dataConsultant = mysqli_fetch_assoc($queryConsultantData);
    else { // nu exista consultantul dat
        header("location: index.php");
        die();
    }

    $consultantName = $dataConsultant["fullName"];
    $consultantEmail = $dataConsultant["email"]
?>




<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>Consultant <?php echo $consultantName ?></title>

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
            position: absolute;
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


    <p class = "student-info"> <span class = "title-info"> Consultant Name: </span> <?php echo $consultantName; ?> </p>
    <p class = "student-info"> <span class = "title-info"> Consultant Email: </span> <?php echo $consultantEmail; ?> </p>
    <a href = "studentsList.php?consultant%5B%5D=<?php echo $userId; ?>"> <button class = "btn btn-primary"> <i class="fas fa-eye"></i> See students </button> </a>
    <br>
    <br>
    <a href = "<?php echo $dataConsultant['calendlyLink']; ?>" target="_blank"> <button class = "btn btn-primary"> <i class="fas fa-eye"></i> See Calendly </button> </a>

    <br>
    <br>
    <button onclick = "confirmRemove('removeConsultant.php?consultantId=<?php echo $userId; ?>')" class = "btn btn-danger"> <i class="fa-solid fa-minus"></i> Make Consultant Inactive </button>

</div>

</body>

<script>
    function confirmRemove(link) {
        const userConfirmed = confirm("Are you sure you want to make this consultant inactive?");
        if (userConfirmed) {
            window.location.href = link;
        } else {
            alert("Action canceled.");
        }
    }
</script>

</html>