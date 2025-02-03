<?php
// Function to check if the user is logged in
function check_login() {
    if (!isset($_SESSION['username'])) {
        header("Location: ../login_page/detourcafe.php"); // Adjust the path to your login page
        exit();
    }
}
?>
