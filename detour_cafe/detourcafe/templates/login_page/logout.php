<?php
session_start();
require "../../conn/conn.php";

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $full_name = $_SESSION['full_name'];

    // Log logout event
    $log_date = date('Y-m-d');
    $log_time = date('H:i:s');
    $action = 'LOGOUT';
    $log_query = "INSERT INTO db_history (username, full_name, log_date, log_time, action) VALUES ('$username', '$full_name', '$log_date', '$log_time', '$action')";
    mysqli_query($conn, $log_query);

    // Destroy session
    session_unset();
    session_destroy();
}

// Redirect to login page
header("Location: ../login_page/detourcafe.php");
exit();
?>
