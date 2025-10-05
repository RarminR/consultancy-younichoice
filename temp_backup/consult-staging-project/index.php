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
	<title>Logare</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
<!--===============================================================================================-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="css/util.css">
	<link rel="stylesheet" type="text/css" href="css/styleLogin.css">
<!--===============================================================================================-->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
</head>

<body>
	
	<div class="limiter">
		<div class="container-login100">
			<div class="wrap-login100">
				<div class="login100-pic js-tilt" data-tilt>
					<img src="images/img-01.png" alt="IMG">
				</div>

				<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="login100-form validate-form">
					<span class="login100-form-title" style = "color: #D12E71;">
						Bine ați revenit!
					</span>

					<div class="wrap-input100 validate-input" data-validate = "Valid email is required: ex@abc.xyz">
                        <?php
                        if ($isError == 0) { ?>
						    <input class="input100" type="email" name="username" placeholder="Email" required>
                        <?php 
                        } else {?>
                            <input class="input100" type="email" name="username" placeholder="Email" value = "<?php echo $username; ?>"required>
                        <?php
                        } ?>

						<span class="focus-input100"></span>
						<span class="symbol-input100">
							<i class="fa fa-envelope" aria-hidden="true"></i>
						</span>
					</div>

					<div class="wrap-input100 validate-input" data-validate = "Password is required">
						<input class="input100" type="password" name="password" placeholder="Password" required>
						<span class="focus-input100"></span>
						<span class="symbol-input100">
							<i class="fa fa-lock" aria-hidden="true"></i>
						</span>
					</div>

                    <div id="message-email">
                        <?php
                            if ($isError == 1) {
                            ?>  <p id = "error-message" class = "invalid"> <b id = "text-message-email">Email sau parolă incorectă! </b></p> <?php
                            }
                            else if ($isError == 2) {
                                ?>  <p id = "error-message" class = "invalid"> <b id = "text-message-email">Contul tau nu mai este valid! </b></p> <?php
                            }
                        ?>
                    </div>

                    <div id="message-email">
                        <?php
                            if ($remainTry <= 0 ){
                                ?>  <p id = "error-message" class = "invalid"> <b id = "text-message-email">Ne pare rău, nu mai aveți încercări </b></p> <?php
                            }
                            else if ($remainTry <= 5) {
                            ?>  <p id = "error-message" class = "invalid"> <b id = "text-message-email">Mai ai <?php echo $remainTry; ?> încercări </b></p> <?php
                            }
                        ?>
                    </div>

					<div class="container-login100-form-btn">
						<button class="login100-form-btn">
							Logare
						</button>
					</div>

					<!-- <div class="text-center p-t-12">
						<a class="txt2" href="forget-password.php">
							Ai uitat Parola?
						</a>
					</div> -->

					<div class="text-center p-t-136">
						<a class="txt2" href="signUp.php">
							Logare
							<i class="fa fa-long-arrow-right m-l-5" aria-hidden="true"></i>
						</a>
					</div>
				</form>
			</div>
		</div>
	</div>
	

</body>
</html>