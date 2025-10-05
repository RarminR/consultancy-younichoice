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

$sql = "SELECT `calendlyLink` FROM `users` WHERE `userId` = '$userId'";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
$currentLink = $row['calendlyLink'];

if($_SERVER["REQUEST_METHOD"] == "POST") {
  $calendlyLink = $_POST['link'];
  $sql = "UPDATE `users` SET `calendlyLink` = '$calendlyLink' WHERE `userId` = '$userId'";
  mysqli_query($link, $sql);  

  header("location: index.php");
  exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Update Calendly Link</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
<!--===============================================================================================-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="css/styleSignUp.css">
	<link rel="stylesheet" type="text/css" href="css/util.css">
	<link rel="stylesheet" type="text/css" href="css/styleLogin.css">

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
					<img src="images/img-01.png" alt="IMG">
				</div>

				<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="login100-form validate-form">
					<span class="login100-form-title" style = "color: #D12E71;">
                        Update Calendly Link
					</span>




            <input class="input100" type="text" name="link" placeholder="Calendly Link" value = "<?php echo $currentLink; ?>" required>
                


        
          <br>
          <br>

					<div class="container-login100-form-btn">
						<button class="login100-form-btn">
                            Update
						</button>
					</div>
				</form>

                
			</div>
		</div>
	</div>

</body>


</script>
</html>