<?php
    session_start();

    require_once "configDatabase.php";

    if (!isset($_SESSION["type"])) {
        header("location: index.php");
        die();
    }

    $typeAccount = $_SESSION["type"];
    if ($typeAccount == 0) {
        header("location: signOut.php");
        die();
    }

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $defaultPassword = md5("Youni2024!");

        $password = md5($password);
        $sql = "INSERT INTO users (`fullName`, `email`, `password`) VALUES ('$name', '$email', '$defaultPassword');";
        mysqli_query($link, $sql);  
        
        header("location: index.php");
        die();
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
    <title>Add Consultant - Youni</title>

    <style>
        /* Add Consultant Page Design System Styles */
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

        .add-consultant-container {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-2xl);
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--light-gray);
            backdrop-filter: blur(10px);
        }

        .add-consultant-header {
            text-align: center;
            margin-bottom: var(--spacing-2xl);
            padding-bottom: var(--spacing-lg);
            border-bottom: 2px solid var(--light-gray);
        }

        .add-consultant-title {
            color: var(--primary-color);
            font-size: var(--font-size-4xl);
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--spacing-md);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-md);
        }

        .add-consultant-title::before {
            content: '\f0c0';
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

        .name-label::before { content: '\f007'; }
        .email-label::before { content: '\f0e0'; }

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

        .alert-success {
            background: var(--success-light);
            color: var(--success-dark);
            border: 1px solid var(--success-color);
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

            .add-consultant-title {
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
    <div class="add-consultant-container">
        <div class="add-consultant-header">
            <h1 class="add-consultant-title">Add Consultant</h1>
        </div>

        <form method="post" onsubmit="return validateForm()">
            <div class="form-section">
                <div class="form-group">
                    <label class="form-label name-label">Consultant Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter consultant's full name" required>
                </div>

                <div class="form-group">
                    <label class="form-label email-label">Consultant's Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter consultant's email" required>
                </div>
            </div>

            <div style="text-align: center; margin-top: var(--spacing-2xl);">
                <button class="btn-submit" type="submit" name="submit">
                    Add Consultant
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
            var emailInput = document.querySelector('input[name="email"]');
            
            if (!nameInput.value.trim()) {
                alert('Please enter consultant name');
                return false;
            }
            
            if (!emailInput.value.trim()) {
                alert('Please enter consultant email');
                return false;
            }
            
            // Basic email validation
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(emailInput.value)) {
                alert('Please enter a valid email address');
                return false;
            }
            
            return true;
        }
    </script>
</body>
</html>