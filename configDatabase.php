<?php
$env    = getenv('APP_ENV') ?: 'development';
$host   = getenv('DB_HOST') ?: 'db-dev';
$port   = (int)(getenv('DB_PORT') ?: 3306);
$dbname = getenv('DB_NAME') ?: 'myapp_dev';
$user   = getenv('DB_USER') ?: 'dev';
$pass   = getenv('DB_PASS') ?: 'devpass';

$link = mysqli_connect($host, $user, $pass, $dbname, $port);
if (!$link) { die('DB connect error: ' . mysqli_connect_error()); }

$base_url = getenv('BASE_URL') ?: "http://localhost:8082/";
