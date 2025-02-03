<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "detour_cafe";

$conn = new mysqli($servername, $username, $password, $dbname);

if (!$conn) {
    die('Failed to connect to MySQL: ' . mysqli_connect_error());
}

date_default_timezone_set('Asia/Manila');
?>