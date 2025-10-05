<?php 
    session_start();

    require_once "configDatabase.php";

    function generateRandomPassword($length = 8) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $password;
    }

    function sendPasswordResetEmail($to, $newPassword) {
        $apiKey = 're_6XaDD7dc_2ZLrH3sHnrQhdnFnzPJsdiG9';
        $url = 'https://api.resend.com/emails';

        $subject = "Resetare Parolă - Youni Choice";
        
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>$subject</title>
        </head>
        <body>
            <p>Bună,</p>
            <p>Parola dumneavoastră a fost resetată cu succes. Vă rugăm să folosiți următoarea parolă temporară pentru a vă autentifica:</p>
            <p><strong>Parolă nouă:</strong> $newPassword</p>
            <p>Vă recomandăm să schimbați această parolă din setările contului dumneavoastră imediat după autentificare.</p>
            <p>Dacă nu ați solicitat resetarea parolei, vă rugăm să contactați echipa de suport.</p>
            <p>Vă mulțumim!</p>
            <br>
            <p><strong>Echipa Youni Choice</strong></p>
        </body>
        </html>
        ";

        $data = [
            'from' => 'Youni Choice <office@younichoice.com>',
            'to' => [$to],
            'subject' => $subject,
            'html' => $message,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        if(empty(trim($_POST["username"]))){
            $username_err = "Please enter username.";
        } else{
            $username = trim($_POST["username"]);
        }

        if(empty($username_err)) {
            $newPassword = generateRandomPassword();
            $sql = "SELECT `userId`, `email`, `password`, `type`, `fullName`, `isActive` FROM users WHERE `email` = '$username'";
            $result = mysqli_query($link, $sql);

            if (mysqli_num_rows($result) > 0) {
                $newPasswordMd5 = md5($newPassword);
                $sqlUpdate = "UPDATE users SET `password` = '$newPasswordMd5' WHERE `email` = '$username'";
                $updateResult = mysqli_query($link, $sqlUpdate);

                sendPasswordResetEmail($username, $newPassword);
                header("location: index.php");
            }
        }
    }
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Forget password</title>
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
    
						<input class="input100" type="email" name="username" placeholder="Email" required>
                         

						<span class="focus-input100"></span>
						<span class="symbol-input100">
							<i class="fa fa-envelope" aria-hidden="true"></i>
						</span>
					</div>



					<div class="container-login100-form-btn">
						<button class="login100-form-btn">
							Reseteaza parola
						</button>
					</div>


					<div class="text-center p-t-136">
						<a class="txt2" href="index.php">
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