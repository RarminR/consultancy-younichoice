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

    $typeAccount = $_SESSION["type"];
    $name = "";
    $country = "";
    $commission = "";

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST['name'];
        $country = $_POST['country'];
        if (isset($_POST['commission']))
            $commission = $_POST['commission'];

        $sqlCheckUniversity = "SELECT * FROM universities WHERE `universityName` = '$name' AND `institutionType` = 1";
        $restultCheckUniversity = mysqli_query($link, $sqlCheckUniversity);

        if (mysqli_num_rows($restultCheckUniversity) > 0) {
            $row = mysqli_fetch_assoc($restultCheckUniversity);

            $universityId = $row['universityId'];
            $universityLink = $base_url . "university.php?universityId=" . $universityId;

            $errorName = "This university already exists!";
        }
        else {
            $sql = "INSERT INTO universities (`universityName`, `universityCountry`, `commission`, `institutionType`) VALUES ('$name', '$country', '$commission', '1')";
            mysqli_query($link, $sql);  
            header("location: summerList.php");
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
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Design System CSS -->
    <link rel="stylesheet" href="student/design-system.css">
    <script src="https://unpkg.com/react-phone-number-input@3.x/bundle/react-phone-number-input.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/react-phone-number-input@3.x/bundle/style.css"/>
    <title>Add Summer School - Youni</title>

    <style>
        /* Add Summer School Page Design System Styles */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
        }

        #content {
            max-width: 800px;
            margin: 0 auto;
            padding: var(--spacing-2xl);
            min-height: calc(100vh - 120px);
        }

        .navbar {
            background: var(--white);
            box-shadow: var(--shadow-sm);
            border-bottom: 1px solid var(--light-gray);
        }

        .add-summer-container {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-2xl);
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--light-gray);
            backdrop-filter: blur(10px);
        }

        .add-summer-header {
            text-align: center;
            margin-bottom: var(--spacing-2xl);
            padding-bottom: var(--spacing-lg);
            border-bottom: 2px solid var(--light-gray);
        }

        .add-summer-title {
            color: var(--primary-color);
            font-size: var(--font-size-4xl);
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--spacing-md);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-md);
        }

        .add-summer-title::before {
            content: '\f1b3';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-2xl);
            color: var(--primary-color);
        }

        .form-section {
            background: var(--light-bg);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-2xl);
            margin-bottom: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--light-gray);
        }

        .form-group {
            margin-bottom: var(--spacing-xl);
        }

        .form-label {
            color: var(--primary-color);
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-semibold);
            margin-bottom: var(--spacing-sm);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .form-label::before {
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-base);
            color: var(--primary-color);
        }

        .name-label::before { content: '\f1b3'; }
        .country-label::before { content: '\f0ac'; }
        .commission-label::before { content: '\f3d1'; }

        .form-control {
            width: 100%;
            padding: var(--spacing-md) var(--spacing-lg);
            border: 2px solid var(--light-gray);
            border-radius: var(--border-radius-lg);
            font-size: var(--font-size-base);
            font-family: 'Poppins', sans-serif;
            transition: var(--transition-normal);
            background: var(--white);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: var(--shadow-md);
        }

        .btn-submit {
            background: var(--primary-gradient);
            color: var(--white);
            border: none;
            padding: var(--spacing-lg) var(--spacing-2xl);
            border-radius: var(--border-radius-lg);
            font-weight: var(--font-weight-semibold);
            font-size: var(--font-size-lg);
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: var(--transition-normal);
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-submit::before {
            content: '\f067';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: var(--font-size-base);
        }

        .alert {
            padding: var(--spacing-md) var(--spacing-lg);
            border-radius: var(--border-radius-lg);
            margin-bottom: var(--spacing-lg);
            font-weight: var(--font-weight-medium);
        }

        .alert-danger {
            background: var(--danger-light);
            color: var(--danger-dark);
            border: 1px solid var(--danger-color);
        }

        .alert-info {
            background: var(--info-light);
            color: var(--info-dark);
            border: 1px solid var(--info-color);
        }

        .alert a {
            color: var(--primary-color);
            font-weight: var(--font-weight-semibold);
            text-decoration: none;
        }

        .alert a:hover {
            text-decoration: underline;
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

            .add-summer-title {
                font-size: var(--font-size-2xl);
            }

            .form-section {
                padding: var(--spacing-lg);
            }

            .btn-submit {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
  </head>

  
  <?php include("navbar.php"); ?>

  <div id="content">
    <div class="add-summer-container">
        <div class="add-summer-header">
            <h1 class="add-summer-title">Add Summer School</h1>
        </div>

        <form method="post" onsubmit="return validateForm()">
            <div class="form-section">
                <div class="form-group">
                    <label class="form-label name-label">Summer School Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" placeholder="Enter summer school name" required>
                    <?php if (isset($errorName)) { ?>
                        <div class="alert alert-danger">
                            <?php echo $errorName; ?> You can edit it at this link: 
                            <a href="<?php echo $universityLink; ?>">Summer School Details</a>
                        </div>
                    <?php } ?>
                </div>

                <div class="form-group">
                    <label class="form-label country-label">Summer School Country</label>
                    <input type="text" name="country" class="form-control" value="<?php echo htmlspecialchars($country); ?>" placeholder="Enter summer school country" required>
                </div>

                <div class="form-group">
                    <label class="form-label commission-label">Summer School Commission</label>
                    <input type="number" name="commission" class="form-control" value="<?php echo htmlspecialchars($commission); ?>" placeholder="Enter summer school commission (optional)" min="0" max="100" step="0.01">
                </div>
            </div>

            <div style="text-align: center; margin-top: var(--spacing-2xl);">
                <button class="btn-submit" type="submit" name="submit">
                    Add Summer School
                </button>
            </div>
        </form>
    </div>
  </div>


    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script>
        function validateForm() {
            // Basic form validation
            var nameInput = document.querySelector('input[name="name"]');
            var countryInput = document.querySelector('input[name="country"]');
            var commissionInput = document.querySelector('input[name="commission"]');
            
            if (!nameInput.value.trim()) {
                alert('Please enter summer school name');
                return false;
            }
            
            if (!countryInput.value.trim()) {
                alert('Please enter summer school country');
                return false;
            }
            
            // Validate commission if provided
            if (commissionInput.value.trim()) {
                var commission = parseFloat(commissionInput.value);
                if (isNaN(commission) || commission < 0 || commission > 100) {
                    alert('Commission must be a number between 0 and 100');
                    return false;
                }
            }
            
            return true;
        }
    </script>
</body>
</html>