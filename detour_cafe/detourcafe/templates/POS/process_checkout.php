<?php
require "../../conn/conn.php"; // Adjust path as per your file structure

date_default_timezone_set('Asia/Manila');

if (isset($_POST['print-receipt'])) {
    // Assuming you have a table named 'sales' with columns 'category', 'item', 'price', 'discounts', 'net_sales', 'date_time'

    // Fetch and sanitize inputs
    $categories = $_POST['categories'];
    $items = $_POST['items']; // Array of items
    $prices = $_POST['prices']; // Array of prices
    $discount_percent = $_POST['percent']; // Discount percentage
    $current_date_time = date('Y-m-d H:i:s');

    // Insert each item into the sales table
    foreach ($items as $index => $item) {
        $category = mysqli_real_escape_string($conn, $categories[$index]);
        $price = mysqli_real_escape_string($conn, $prices[$index]);

        $sql = "INSERT INTO db_sales (category, item, items_sold, discounts, net_sales, date_time) 
                VALUES ('$category', '$item', 1, '$discount_percent', '$price', '$current_date_time')";

        // Execute insertion query
        if (mysqli_query($conn, $sql)) {
            $actionStatus = 'added';
        } else {
            $actionStatus = 'error';
        }
    }


    // Optionally, you can redirect the user after successful submission
    header('Location: POS.php'); // Redirect to a sales confirmation page
    exit();
} else {
    // Handle error or redirect if checkout button wasn't clicked
    header('Location: POS.php'); // Redirect to sales page or handle error
    exit();
}
?>