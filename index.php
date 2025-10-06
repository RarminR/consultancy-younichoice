<?php

session_start();

require_once "configDatabase.php";

 
// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_SESSION["email"])){
    header("location: studentsList.php");
    exit;
}
else if(isset($_SESSION["emailStudent"])){
    header("location: student");
    exit;
}
 
// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";
 
$remainTry = 10;
$isError = 0;


if (isset($_SESSION['noTry']) && $_SESSION['noTry'] >= 10) {
    $remainTry = 0;
}
else if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Check if username is empty
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter username.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }

    
    // Validate credentials
    if(empty($username_err) && empty($password_err)){
        // First, try to find user in users table
        $sql = "SELECT `userId`, `email`, `password`, `type`, `fullName`, `isActive` FROM users WHERE `email` = '$username'";
        $result = mysqli_query($link, $sql);

        if (mysqli_num_rows($result) > 0) {
            // User found in users table
            $row = mysqli_fetch_assoc($result);

            $validPassword = $row['password'];
            $isActive = $row['isActive'];
            $password = md5($password);
            
            if ($password == $validPassword) {
                if ($isActive == '1') {
                    $_SESSION["email"] = $row['email'];      
                    $_SESSION["id"]  = $row['userId'];
                    $_SESSION["type"]  = $row['type'];      
                    $_SESSION["fullName"] = $row['fullName'];

                    header("location: studentsList.php");
                }
                else {
                    $isError = 2;
                    if (!isset($_SESSION['noTry']))
                        $_SESSION['noTry'] = 1;
                    else {
                        $_SESSION['noTry']++;
                        $remainTry = 10 - $_SESSION['noTry'];
                    }
                }
            }
            else {
                $isError = 1;
                if (!isset($_SESSION['noTry']))
                    $_SESSION['noTry'] = 1;
                else {
                    $_SESSION['noTry']++;
                    $remainTry = 10 - $_SESSION['noTry'];
                }
            }
        }
        else {
            // User not found in users table, check studentData table
            $sqlStudent = "SELECT `studentId`, `email`, `studentPassword`, `name`, `isVerified` FROM studentData WHERE `email` = '$username'";
            $resultStudent = mysqli_query($link, $sqlStudent);

            if (mysqli_num_rows($resultStudent) > 0) {
                // Student found in studentData table
                $rowStudent = mysqli_fetch_assoc($resultStudent);

                $validStudentPassword = $rowStudent['studentPassword'];
                $isVerified = $rowStudent['isVerified'];
                $password = md5($password);
                
                if ($password == $validStudentPassword) {
                    if ($isVerified == 1) {
                        $_SESSION["emailStudent"] = $rowStudent['email'];      
                        $_SESSION["idStudent"]  = $rowStudent['studentId'];
                        $_SESSION["typeStudent"]  = 2; // Type 2 for students
                        $_SESSION["fullNameStudent"] = $rowStudent['name'];

                        header("location: student");
                    }
                    else {
                        $isError = 2;
                        if (!isset($_SESSION['noTry']))
                            $_SESSION['noTry'] = 1;
                        else {
                            $_SESSION['noTry']++;
                            $remainTry = 10 - $_SESSION['noTry'];
                        }
                    }
                }
                else {
                    $isError = 1;
                    if (!isset($_SESSION['noTry']))
                        $_SESSION['noTry'] = 1;
                    else {
                        $_SESSION['noTry']++;
                        $remainTry = 10 - $_SESSION['noTry'];
                    }
                }
            }
            else {
                // User not found in either table
                $isError = 1;
                if (!isset($_SESSION['noTry']))
                    $_SESSION['noTry'] = 1;
                else {
                    $_SESSION['noTry']++;
                    $remainTry = 10 - $_SESSION['noTry'];
                }
            }
        }
    }
    else {
        $isError = 1;
        if (!isset($_SESSION['noTry']))
            $_SESSION['noTry'] = 1;
        else {
            $_SESSION['noTry']++;
            $remainTry = 10 - $_SESSION['noTry'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Login - Youni</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    
    <!-- Design System CSS -->
    <link rel="stylesheet" href="student/design-system.css">
    
    <style>
        body {
            background: var(--bg-gradient-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-family-primary);
        }

        .login-container {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-3xl);
            box-shadow: var(--shadow-xl);
            width: 100%;
            max-width: 450px;
            margin: var(--spacing-lg);
        }

        .login-header {
            text-align: center;
            margin-bottom: var(--spacing-2xl);
        }

        .login-title {
            color: var(--primary-color);
            font-size: var(--font-size-3xl);
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--spacing-sm);
        }

        .login-subtitle {
            color: var(--secondary-color);
            font-size: var(--font-size-lg);
        }

        .form-group {
            margin-bottom: var(--spacing-lg);
        }

        .form-label {
            display: block;
            margin-bottom: var(--spacing-sm);
            font-weight: var(--font-weight-medium);
            color: var(--dark-gray);
        }

        .input-group {
            position: relative;
            margin-bottom: var(--spacing-lg);
        }

        .form-control {
            width: 100%;
            padding: var(--spacing-md) var(--spacing-lg) var(--spacing-md) var(--spacing-3xl);
            font-size: var(--font-size-base);
            border: 2px solid var(--light-gray);
            border-radius: var(--border-radius-lg);
            transition: var(--transition-normal);
            background: var(--white);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 35, 95, 0.1);
        }

        .input-icon {
            position: absolute;
            left: var(--spacing-md);
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
            font-size: var(--font-size-lg);
            z-index: 2;
        }

        .btn-login {
            width: 100%;
            padding: var(--spacing-md) var(--spacing-lg);
            font-size: var(--font-size-lg);
            font-weight: var(--font-weight-semibold);
            background: var(--primary-gradient);
            color: var(--white);
            border: none;
            border-radius: var(--border-radius-full);
            transition: var(--transition-normal);
            cursor: pointer;
            margin-top: var(--spacing-md);
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .btn-login:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            padding: var(--spacing-md) var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
            border: 1px solid transparent;
            border-radius: var(--border-radius-lg);
            font-weight: var(--font-weight-medium);
        }

        .alert-danger {
            color: var(--danger-dark);
            background: rgba(220, 53, 69, 0.1);
            border-color: rgba(220, 53, 69, 0.2);
        }

        .alert-warning {
            color: var(--warning-dark);
            background: rgba(255, 193, 7, 0.1);
            border-color: rgba(255, 193, 7, 0.2);
        }

        .invalid-feedback {
            color: var(--danger-color);
            font-size: var(--font-size-sm);
            margin-top: var(--spacing-xs);
            display: block;
        }

        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto var(--spacing-lg);
            display: block;
        }

        .login-image {
            display: none;
        }

        @media (min-width: 768px) {
            .login-image {
                display: block;
                background: var(--primary-gradient);
                border-radius: var(--border-radius-lg) 0 0 var(--border-radius-lg);
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--white);
                font-size: var(--font-size-5xl);
            }
            
            .login-container {
                border-radius: 0 var(--border-radius-lg) var(--border-radius-lg) 0;
                margin: 0;
            }
        }
    </style>
</head>

<body>
	
	<div class="container-fluid">
		<div class="row min-vh-100">
			<div class="col-md-6 login-image d-flex align-items-center justify-content-center">
				<i class="fas fa-graduation-cap"></i>
			</div>
			<div class="col-md-6 d-flex align-items-center justify-content-center">
				<div class="login-container">
					<div class="login-header">
						<i class="fas fa-graduation-cap logo"></i>
						<h1 class="login-title">Welcome Back!</h1>
						<p class="login-subtitle">Sign in to your Youni account</p>
					</div>

					<?php if ($isError == 1) { ?>
						<div class="alert alert-danger">
							<i class="fas fa-exclamation-circle"></i> Invalid email or password. Please try again.
						</div>
					<?php } ?>

					<?php if ($isError == 2) { ?>
						<div class="alert alert-warning">
							<i class="fas fa-exclamation-triangle"></i> Your account is not verified. Please check your email for verification instructions.
						</div>
					<?php } ?>

					<?php if ($remainTry == 0) { ?>
						<div class="alert alert-danger">
							<i class="fas fa-lock"></i> Too many failed attempts. Please try again later.
						</div>
					<?php } ?>

					<?php if ($remainTry > 0 && $remainTry <= 5) { ?>
						<div class="alert alert-warning">
							<i class="fas fa-exclamation-triangle"></i> You have <?php echo $remainTry; ?> attempts remaining.
						</div>
					<?php } ?>

					<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
						<div class="form-group">
							<div class="input-group">
								<i class="fas fa-envelope input-icon"></i>
								<input class="form-control" type="email" name="username" placeholder="Email address" 
									value="<?php echo htmlspecialchars($username); ?>" required>
							</div>
						</div>

						<div class="form-group">
							<div class="input-group">
								<i class="fas fa-lock input-icon"></i>
								<input class="form-control" type="password" name="password" placeholder="Password" required>
							</div>
						</div>

						<button class="btn btn-login" type="submit" <?php if ($remainTry == 0) echo "disabled"; ?>>
							<i class="fas fa-sign-in-alt"></i> Sign In
						</button>
					</form>

					<div class="text-center mt-4">
						<a href="forget-password.php" class="text-primary">
							<i class="fas fa-key"></i> Forgot your password?
						</a>
					</div>

					<div class="text-center mt-3">
						<a href="https://younichoice.com/" class="text-secondary">
							<i class="fas fa-user-plus"></i> Create your Account
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	

</body>
</html>