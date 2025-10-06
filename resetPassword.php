<?php
session_start();
require_once "configDatabase.php";

$mail = "";
$password = "";
$fullName = "";
$phoneNumber = "";

if (!isset($_SESSION['email'])) {
    header("location: index.php");
    die();
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
  $mail = $_SESSION['email'];
  $password = $_POST['password'];
  $password = md5($password);
  $sql = "UPDATE `users` SET `password` = '$password' WHERE `email` = '$mail'";
  mysqli_query($link, $sql);  

  header("location: index.php");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Reset Password - Youni</title>
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

        .reset-password-container {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-3xl);
            box-shadow: var(--shadow-xl);
            width: 100%;
            max-width: 500px;
            margin: var(--spacing-lg);
        }

        .reset-password-header {
            text-align: center;
            margin-bottom: var(--spacing-2xl);
        }

        .reset-password-title {
            color: var(--primary-color);
            font-size: var(--font-size-3xl);
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--spacing-sm);
        }

        .reset-password-subtitle {
            color: var(--secondary-color);
            font-size: var(--font-size-lg);
        }

        .form-group {
            margin-bottom: var(--spacing-lg);
        }

        .form-control {
            width: 100%;
            padding: var(--spacing-sm) var(--spacing-lg);
            font-size: var(--font-size-base);
            border: 2px solid var(--light-gray);
            border-radius: var(--border-radius-lg);
            transition: var(--transition-normal);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 35, 95, 0.1);
        }

        .btn-reset-password {
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
        }

        .btn-reset-password:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .password-requirements {
            background: var(--light-gray);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-lg);
            margin: var(--spacing-md) 0;
        }

        .password-requirements h4 {
            color: var(--primary-color);
            font-size: var(--font-size-lg);
            margin-bottom: var(--spacing-md);
        }

        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: var(--spacing-sm);
            font-size: var(--font-size-sm);
        }

        .requirement.valid {
            color: var(--success-color);
        }

        .requirement.invalid {
            color: var(--danger-color);
        }

        .requirement i {
            margin-right: var(--spacing-sm);
            width: 16px;
        }

        .back-to-login {
            text-align: center;
            margin-top: var(--spacing-lg);
        }

        .back-to-login a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: var(--font-weight-medium);
        }

        .back-to-login a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<?php 
    if (isset($_SESSION['userType']))
        include ("navbarUser.php"); 
    else
        include ("navbar.php");
?>
<body>
	
	<div class="reset-password-container">
		<div class="reset-password-header">
			<h1 class="reset-password-title">Reset Password</h1>
			<p class="reset-password-subtitle">Enter your new password below</p>
		</div>

		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" onsubmit="return validateMyForm();">
			<div class="form-group">
				<input class="form-control" type="password" name="password" placeholder="New Password" id="psw" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Password must contain at least one digit, one uppercase letter, one lowercase letter, and be at least 8 characters long." required>
			</div>

			<div class="password-requirements" id="message" style="display: none;">
				<h4>Password Requirements:</h4>
				<div class="requirement" id="letter">
					<i class="fas fa-times"></i>
					<span>One lowercase letter</span>
				</div>
				<div class="requirement" id="capital">
					<i class="fas fa-times"></i>
					<span>One uppercase letter</span>
				</div>
				<div class="requirement" id="number">
					<i class="fas fa-times"></i>
					<span>One number</span>
				</div>
				<div class="requirement" id="length">
					<i class="fas fa-times"></i>
					<span>Minimum 8 characters</span>
				</div>
			</div>

			<div class="form-group">
				<input class="form-control" type="password" name="confirm-password" id="confirm" placeholder="Confirm Password" required>
			</div>

			<div id="message1" style="display: none;">
				<div class="requirement invalid" id="check-passwords">
					<i class="fas fa-times"></i>
					<span>Passwords must match!</span>
				</div>
			</div>

			<button class="btn-reset-password" type="submit">
				<i class="fas fa-key"></i> Reset Password
			</button>
		</form>

		<div class="back-to-login">
			<span>Remember your password? </span>
			<a href="index.php">Back to Login</a>
		</div>
	</div>

</body>

<script>
var myInput = document.getElementById("psw");
var passwordConfirm = document.getElementById("confirm");
var letter = document.getElementById("letter");
var capital = document.getElementById("capital");
var number = document.getElementById("number");
var length = document.getElementById("length");

// When the user clicks on the password field, show the message box
myInput.onfocus = function() {
  document.getElementById("message1").style.display = "none";
  document.getElementById("message").style.display = "block";

  var lowerCaseLetters = /[a-z]/g;
  if(myInput.value.match(lowerCaseLetters)) {  
    letter.classList.remove("invalid");
    letter.classList.add("valid");
    letter.querySelector("i").className = "fas fa-check";
  } else {
    letter.classList.remove("valid");
    letter.classList.add("invalid");
    letter.querySelector("i").className = "fas fa-times";
  }
  
  // Validate capital letters
  var upperCaseLetters = /[A-Z]/g;
  if(myInput.value.match(upperCaseLetters)) {  
    capital.classList.remove("invalid");
    capital.classList.add("valid");
    capital.querySelector("i").className = "fas fa-check";
  } else {
    capital.classList.remove("valid");
    capital.classList.add("invalid");
    capital.querySelector("i").className = "fas fa-times";
  }

  // Validate numbers
  var numbers = /[0-9]/g;
  if(myInput.value.match(numbers)) {  
    number.classList.remove("invalid");
    number.classList.add("valid");
    number.querySelector("i").className = "fas fa-check";
  } else {
    number.classList.remove("valid");
    number.classList.add("invalid");
    number.querySelector("i").className = "fas fa-times";
  }

  // Validate length
  if(myInput.value.length >= 8) {
    length.classList.remove("invalid");
    length.classList.add("valid");
    length.querySelector("i").className = "fas fa-check";
  } else {
    length.classList.remove("valid");
    length.classList.add("invalid");
    length.querySelector("i").className = "fas fa-times";
  }
}


// When the user starts to type something inside the password field
myInput.onkeyup = function() {
  // Validate lowercase letters
  var lowerCaseLetters = /[a-z]/g;
  if(myInput.value.match(lowerCaseLetters)) {  
    letter.classList.remove("invalid");
    letter.classList.add("valid");
    letter.querySelector("i").className = "fas fa-check";
  } else {
    letter.classList.remove("valid");
    letter.classList.add("invalid");
    letter.querySelector("i").className = "fas fa-times";
  }
  
  // Validate capital letters
  var upperCaseLetters = /[A-Z]/g;
  if(myInput.value.match(upperCaseLetters)) {  
    capital.classList.remove("invalid");
    capital.classList.add("valid");
    capital.querySelector("i").className = "fas fa-check";
  } else {
    capital.classList.remove("valid");
    capital.classList.add("invalid");
    capital.querySelector("i").className = "fas fa-times";
  }

  // Validate numbers
  var numbers = /[0-9]/g;
  if(myInput.value.match(numbers)) {  
    number.classList.remove("invalid");
    number.classList.add("valid");
    number.querySelector("i").className = "fas fa-check";
  } else {
    number.classList.remove("valid");
    number.classList.add("invalid");
    number.querySelector("i").className = "fas fa-times";
  }

  // Validate length
  if(myInput.value.length >= 8) {
    length.classList.remove("invalid");
    length.classList.add("valid");
    length.querySelector("i").className = "fas fa-check";
  } else {
    length.classList.remove("valid");
    length.classList.add("invalid");
    length.querySelector("i").className = "fas fa-times";
  }
}


passwordConfirm.onfocus = function() {
  document.getElementById("message").style.display = "none";
  document.getElementById("message1").style.display = "block";

  if (passwordConfirm.value == myInput.value && passwordConfirm.value.length > 0) {
    document.getElementById("check-passwords").classList.remove("invalid");
    document.getElementById("check-passwords").classList.add("valid");
    document.getElementById("check-passwords").querySelector("i").className = "fas fa-check";
  }
  else {
    document.getElementById("check-passwords").classList.remove("valid");
    document.getElementById("check-passwords").classList.add("invalid");
    document.getElementById("check-passwords").querySelector("i").className = "fas fa-times";
  }
}

// When the user starts to type something inside the password field
passwordConfirm.onkeyup = function() {
  // Validate lowercase letters
  if (passwordConfirm.value == myInput.value && passwordConfirm.value.length > 0) {
    document.getElementById("check-passwords").classList.remove("invalid");
    document.getElementById("check-passwords").classList.add("valid");
    document.getElementById("check-passwords").querySelector("i").className = "fas fa-check";
  }
  else {
    document.getElementById("check-passwords").classList.remove("valid");
    document.getElementById("check-passwords").classList.add("invalid");
    document.getElementById("check-passwords").querySelector("i").className = "fas fa-times";
  }
}


function validateMyForm() {
  if (passwordConfirm.value != myInput.value) {
    passwordConfirm.select();
    return false;
  }

  return true;
}

</script>
</html>