<?php
session_start();
require_once "../../configDatabase.php";

$mail = "";
$password = "";
$fullName = "";
$phoneNumber = "";

if (!isset($_SESSION['typeStudent']) || !isset($_SESSION['idStudent'])) {
    header("location: ../");
    die();
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
  $mail = $_SESSION['emailStudent'];
  $password = $_POST['password'];
  $password = md5($password);
  $sql = "UPDATE `studentData` SET `studentPassword` = '$password' WHERE `email` = '$mail'";
  mysqli_query($link, $sql);  

  header("location: ../");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Resetare parolă</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
<!--===============================================================================================-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="../../css/styleSignUp.css">
	<link rel="stylesheet" type="text/css" href="../../css/util.css">
	<link rel="stylesheet" type="text/css" href="../../css/styleLogin.css">

<!--===============================================================================================-->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

</head>

<?php 
    if (isset($_SESSION['userType']))
        include ("navbarUser.php"); 
    else
        include ("navbar.php");
?>
<body>
	
	<div class="limiter">
		<div class="container-login100">
			<div class="wrap-login100">
				<div class="login100-pic js-tilt" data-tilt>
					<img src="../../images/img-01.png" alt="IMG">
				</div>

				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="login100-form validate-form" onsubmit="return validateMyForm();">
					<span class="login100-form-title" style = "color: #D12E71;">
                        Resetează Parola
					</span>




            <div class="wrap-input100 validate-input" data-validate = "Password is required">
                <input class="input100" type="password" name="password" placeholder="Password" id = "psw" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Parola trebuie să conțină cel puțin o cifră, o literă majusculă și o literă minusculă, și să aibă cel puțin 8 sau mai multe caractere." required>
                <span class="focus-input100"></span>
                <span class="symbol-input100">
                    <i class="fa fa-lock" aria-hidden="true"></i>
                </span>
                
            </div>

          <div class = "wrap-input100" id="message">
              <div id = "message-column">
                  <p id="letter" class="invalid"><b>O litera mică</b></p>
                  <p id="capital" class="invalid"><b>O litera mare</b></p>
              </div>
              <div id = "message-column">
                  <p id="number" class="invalid"><b>O cifră</b></p>
                  <p id="length" class="invalid"><b>Minim 8 caractere</b></p>
              </div>
          </div>

          <div class="wrap-input100 validate-input">
						<input class="input100" type="password" name="confirm-password" id = "confirm" placeholder="Confirm password" oninvalid="this.setCustomValidity('Acesta este un câmp obligatoriu!')" required>
						<span class="focus-input100"></span>
						<span class="symbol-input100">
							<i class="fa fa-lock" aria-hidden="true"></i>
						</span>
					</div>

          <div id="message1">
              <p id = "check-passwords" class = "invalid"> <b>Parolele trebuie să coincidă!</b></p>
          </div>


        
          <br>
          <br>

					<div class="container-login100-form-btn">
						<button class="login100-form-btn">
                            Resetează
						</button>
					</div>
				</form>

                
			</div>
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
  } else {
    letter.classList.remove("valid");
    letter.classList.add("invalid");
  }
  
  // Validate capital letters
  var upperCaseLetters = /[A-Z]/g;
  if(myInput.value.match(upperCaseLetters)) {  
    capital.classList.remove("invalid");
    capital.classList.add("valid");
  } else {
    capital.classList.remove("valid");
    capital.classList.add("invalid");
  }

  // Validate numbers
  var numbers = /[0-9]/g;
  if(myInput.value.match(numbers)) {  
    number.classList.remove("invalid");
    number.classList.add("valid");
  } else {
    number.classList.remove("valid");
    number.classList.add("invalid");
  }
  
  // Validate length
  if(myInput.value.length >= 8) {
    length.classList.remove("invalid");
    length.classList.add("valid");
  } else {
    length.classList.remove("valid");
    length.classList.add("invalid");
  }
}


// When the user starts to type something inside the password field
myInput.onkeyup = function() {
  // Validate lowercase letters
  var lowerCaseLetters = /[a-z]/g;
  if(myInput.value.match(lowerCaseLetters)) {  
    letter.classList.remove("invalid");
    letter.classList.add("valid");
  } else {
    letter.classList.remove("valid");
    letter.classList.add("invalid");
  }
  
  // Validate capital letters
  var upperCaseLetters = /[A-Z]/g;
  if(myInput.value.match(upperCaseLetters)) {  
    capital.classList.remove("invalid");
    capital.classList.add("valid");
  } else {
    capital.classList.remove("valid");
    capital.classList.add("invalid");
  }

  // Validate numbers
  var numbers = /[0-9]/g;
  if(myInput.value.match(numbers)) {  
    number.classList.remove("invalid");
    number.classList.add("valid");
  } else {
    number.classList.remove("valid");
    number.classList.add("invalid");
  }
  
  // Validate length
  if(myInput.value.length >= 8) {
    length.classList.remove("invalid");
    length.classList.add("valid");
  } else {
    length.classList.remove("valid");
    length.classList.add("invalid");
  }
}


passwordConfirm.onfocus = function() {
  document.getElementById("message").style.display = "none";
  document.getElementById("message1").style.display = "block";

  if (passwordConfirm.value == myInput.value && passwordConfirm.value.length > 0) {
    document.getElementById("check-passwords").classList.remove("invalid");
    document.getElementById("check-passwords").classList.add("valid");
  }
  else {
    document.getElementById("check-passwords").classList.remove("valid");
    document.getElementById("check-passwords").classList.add("invalid");
  }
}

// When the user starts to type something inside the password field
passwordConfirm.onkeyup = function() {
  // Validate lowercase letters
  if (passwordConfirm.value == myInput.value && passwordConfirm.value.length > 0) {
    document.getElementById("check-passwords").classList.remove("invalid");
    document.getElementById("check-passwords").classList.add("valid");
  }
  else {
    document.getElementById("check-passwords").classList.remove("valid");
    document.getElementById("check-passwords").classList.add("invalid");
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