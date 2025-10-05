<?php
/* Database credentials. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'armin');
define('DB_PASSWORD', 'sall1385');

if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == 'consult-staging.younichoice.com') {
    $base_url = "https://consult-staging.younichoice.com/";
    define('DB_NAME', 'dataConsultancy-copy');
} else {
    $base_url = "https://internconsultancy.younichoice.com/";
    define('DB_NAME', 'dataConsultancy');
}

/* Attempt to connect to MySQL database */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>