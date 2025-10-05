<?php
session_start();
require_once "../../configDatabase.php";

$error = "";
$success = "";
$studentHashLink = "";

// Check if hash parameter exists
if (!isset($_GET['hash']) || empty($_GET['hash'])) {
    $error = "Invalid or missing hash parameter.";
} else {
    $studentHashLink = $_GET['hash'];
    
    // Check if hash exists in database and student is not verified
    $sql = "SELECT * FROM studentData WHERE studentHashLink = '$studentHashLink' AND isVerified = 0";
    $result = mysqli_query($link, $sql);
    
    if (mysqli_num_rows($result) == 0) {
        $error = "Invalid hash or student already verified.";
    } else {
        $studentData = mysqli_fetch_assoc($result);
        
        if($_SERVER["REQUEST_METHOD"] == "POST") {
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm-password'];
            
            // Validate password requirements
            if (strlen($password) < 8) {
                $error = "Password must be at least 8 characters long.";
            } elseif (strlen($password) > 50) {
                $error = "Password must be maximum 50 characters long.";
            } elseif (!preg_match('/[A-Z]/', $password)) {
                $error = "Password must contain at least one uppercase letter.";
            } elseif (!preg_match('/[a-z]/', $password)) {
                $error = "Password must contain at least one lowercase letter.";
            } elseif (!preg_match('/[0-9]/', $password)) {
                $error = "Password must contain at least one number.";
            } elseif (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
                $error = "Password must contain at least one special character.";
            } elseif ($password !== $confirmPassword) {
                $error = "Passwords do not match.";
            } else {
                // Hash the password and update student data
                $hashedPassword = md5($password);
                $studentId = $studentData['studentId'];
                
                $updateSql = "UPDATE studentData SET studentPassword = '$hashedPassword', isVerified = 1 WHERE studentId = '$studentId'";
                if (mysqli_query($link, $updateSql)) {
                    $success = "Password set successfully! You can now log in to your account.";
                } else {
                    $error = "Error updating password. Please try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Onboarding - Set Password</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="../../css/styleSignUp.css">
    <link rel="stylesheet" type="text/css" href="../../css/util.css">
    <link rel="stylesheet" type="text/css" href="../../css/styleLogin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
</head>

<body>
    <div class="limiter">
        <div class="container-login100">
            <div class="wrap-login100">
                <div class="login100-pic js-tilt" data-tilt>
                    <img src="../../images/img-01.png" alt="IMG">
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" style="margin: 20px; padding: 15px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success" style="margin: 20px; padding: 15px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px;">
                        <?php echo $success; ?>
                        <br><br>
                        <a href="../../index.php" style="color: #155724; text-decoration: underline;">Go to Login</a>
                    </div>
                <?php endif; ?>

                <?php if (empty($error) && empty($success)): ?>
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>?hash=<?php echo urlencode($studentHashLink); ?>" method="post" class="login100-form validate-form" onsubmit="return validateMyForm();">
                        <span class="login100-form-title" style="color: #D12E71;">
                            Set Your Password
                        </span>
                        <p style="text-align: center; color: #666; margin-bottom: 20px;">
                            Welcome! Please set your password to complete your account setup.
                        </p>

                        <div class="wrap-input100 validate-input" data-validate="Password is required">
                            <input class="input100" type="password" name="password" placeholder="Password" id="psw" minlength="8" maxlength="50" required>
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="fa fa-lock" aria-hidden="true"></i>
                            </span>
                        </div>

                        <div class="wrap-input100" id="message">
                            <div id="message-column">
                                <p id="letter" class="invalid"><b>One lowercase letter</b></p>
                                <p id="capital" class="invalid"><b>One uppercase letter</b></p>
                            </div>
                            <div id="message-column">
                                <p id="number" class="invalid"><b>One number</b></p>
                                <p id="symbol" class="invalid"><b>One special character</b></p>
                            </div>
                            <div id="message-column">
                                <p id="length" class="invalid"><b>8-50 characters</b></p>
                            </div>
                        </div>

                        <div class="wrap-input100 validate-input">
                            <input class="input100" type="password" name="confirm-password" id="confirm" placeholder="Confirm password" required>
                            <span class="focus-input100"></span>
                            <span class="symbol-input100">
                                <i class="fa fa-lock" aria-hidden="true"></i>
                            </span>
                        </div>

                        <div id="message1">
                            <p id="check-passwords" class="invalid"><b>Passwords must match!</b></p>
                        </div>

                        <br>
                        <br>

                        <div class="container-login100-form-btn">
                            <button class="login100-form-btn">
                                Set Password
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        var myInput = document.getElementById("psw");
        var passwordConfirm = document.getElementById("confirm");
        var letter = document.getElementById("letter");
        var capital = document.getElementById("capital");
        var number = document.getElementById("number");
        var symbol = document.getElementById("symbol");
        var length = document.getElementById("length");

        // When the user clicks on the password field, show the message box
        myInput.onfocus = function() {
            document.getElementById("message1").style.display = "none";
            document.getElementById("message").style.display = "block";
            validatePassword();
        }

        // When the user starts to type something inside the password field
        myInput.onkeyup = function() {
            validatePassword();
        }

        function validatePassword() {
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

            // Validate special characters
            var specialChars = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/g;
            if(myInput.value.match(specialChars)) {  
                symbol.classList.remove("invalid");
                symbol.classList.add("valid");
            } else {
                symbol.classList.remove("valid");
                symbol.classList.add("invalid");
            }
            
            // Validate length
            if(myInput.value.length >= 8 && myInput.value.length <= 50) {
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
            validatePasswordMatch();
        }

        // When the user starts to type something inside the confirm password field
        passwordConfirm.onkeyup = function() {
            validatePasswordMatch();
        }

        function validatePasswordMatch() {
            if (passwordConfirm.value == myInput.value && passwordConfirm.value.length > 0) {
                document.getElementById("check-passwords").classList.remove("invalid");
                document.getElementById("check-passwords").classList.add("valid");
            } else {
                document.getElementById("check-passwords").classList.remove("valid");
                document.getElementById("check-passwords").classList.add("invalid");
            }
        }

        function validateMyForm() {
            if (passwordConfirm.value != myInput.value) {
                passwordConfirm.select();
                return false;
            }

            // Check all password requirements
            var lowerCaseLetters = /[a-z]/g;
            var upperCaseLetters = /[A-Z]/g;
            var numbers = /[0-9]/g;
            var specialChars = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/g;

            if (!myInput.value.match(lowerCaseLetters)) {
                alert("Password must contain at least one lowercase letter.");
                return false;
            }
            if (!myInput.value.match(upperCaseLetters)) {
                alert("Password must contain at least one uppercase letter.");
                return false;
            }
            if (!myInput.value.match(numbers)) {
                alert("Password must contain at least one number.");
                return false;
            }
            if (!myInput.value.match(specialChars)) {
                alert("Password must contain at least one special character.");
                return false;
            }
            if (myInput.value.length < 8 || myInput.value.length > 50) {
                alert("Password must be between 8 and 50 characters long.");
                return false;
            }

            return true;
        }
    </script>
</body>
</html>
