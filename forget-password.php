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
	<title>Forgot Password - Youni</title>
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

        .forgot-password-container {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: var(--spacing-3xl);
            box-shadow: var(--shadow-xl);
            width: 100%;
            max-width: 450px;
            margin: var(--spacing-lg);
        }

        .forgot-password-header {
            text-align: center;
            margin-bottom: var(--spacing-2xl);
        }

        .forgot-password-title {
            color: var(--primary-color);
            font-size: var(--font-size-3xl);
            font-weight: var(--font-weight-bold);
            margin-bottom: var(--spacing-sm);
        }

        .forgot-password-subtitle {
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

        .btn-forgot-password {
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

        .btn-forgot-password:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .alert {
            padding: var(--spacing-md) var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
            border: 1px solid transparent;
            border-radius: var(--border-radius-lg);
            font-weight: var(--font-weight-medium);
        }

        .alert-success {
            color: var(--success-dark);
            background: rgba(40, 167, 69, 0.1);
            border-color: rgba(40, 167, 69, 0.2);
        }

        .alert-danger {
            color: var(--danger-dark);
            background: rgba(220, 53, 69, 0.1);
            border-color: rgba(220, 53, 69, 0.2);
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

<body>
	
	<div class="forgot-password-container">
		<div class="forgot-password-header">
			<h1 class="forgot-password-title">Forgot Password?</h1>
			<p class="forgot-password-subtitle">Enter your email address and we'll send you a reset link</p>
		</div>

		<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
			<?php if(isset($success)): ?>
				<div class="alert alert-success">
					<i class="fas fa-check-circle"></i> <?php echo $success; ?>
				</div>
			<?php endif; ?>

			<?php if(isset($error)): ?>
				<div class="alert alert-danger">
					<i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
				</div>
			<?php endif; ?>

			<div class="form-group">
				<input class="form-control" type="email" name="email" placeholder="Enter your email address" required>
			</div>

			<button class="btn-forgot-password" name="forgetPassword" type="submit">
				<i class="fas fa-paper-plane"></i> Send Reset Link
			</button>
		</form>

		<div class="back-to-login">
			<span>Remember your password? </span>
			<a href="index.php">Back to Login</a>
		</div>
	</div>
	

</body>
</html>