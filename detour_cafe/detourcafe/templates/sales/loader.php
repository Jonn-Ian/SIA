<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require "../../conn/conn.php";
require_once "../session/session.php";
check_login();

// Get the logged-in user information from the request
$user_name = isset($_GET['user_name']) ? mysqli_real_escape_string($conn, $_GET['user_name']) : '';
$user_role = isset($_GET['user_role']) ? mysqli_real_escape_string($conn, $_GET['user_role']) : '';

// Number of rows per page
$limit = isset($_GET['length']) ? (int)$_GET['length'] : 100; 

// Get current page number and offset
$offset = isset($_GET['start']) ? (int)$_GET['start'] : 0;

// Get sorting parameters
$order_column = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : 0;
$order_dir = isset($_GET['order'][0]['dir']) ? $_GET['order'][0]['dir'] : 'desc';

// Mapping DataTables column index to database column names
$columns = ['ID', 'category', 'item', 'items_sold', 'discounts', 'net_sales', 'cost_of_goods', 'gross_profit', 'date_time'];
$order_by = isset($columns[$order_column]) ? $columns[$order_column] : 'date_time';

// Get search value
$search_value = isset($_GET['search']['value']) ? mysqli_real_escape_string($conn, $_GET['search']['value']) : '';

// Determine query based on user role
if ($user_role == 'Admin') {
    $sql = "SELECT * FROM db_sales 
            WHERE CONCAT_WS(' ', ID, category, item, items_sold, discounts, net_sales, cost_of_goods, gross_profit, date_time) LIKE '%$search_value%'
            ORDER BY date_time DESC, $order_by $order_dir
            LIMIT $limit OFFSET $offset";
} else {
    $sql = "SELECT * FROM db_sales 
            WHERE cashier_name = '$user_name' 
            AND CONCAT_WS(' ', ID, category, item, items_sold, discounts, net_sales, cost_of_goods, gross_profit, date_time) LIKE '%$search_value%'
            ORDER BY date_time DESC, $order_by $order_dir
            LIMIT $limit OFFSET $offset";
}

$result = mysqli_query($conn, $sql);

if (!$result) {
    die('Error: ' . mysqli_error($conn));
}

// Fetch data
$data = array();
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

// Calculate total number of records
if ($user_role == 'Admin') {
    $total_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM db_sales");
} else {
    $total_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM db_sales WHERE cashier_name = '$user_name'");
}
$total_row = mysqli_fetch_assoc($total_result);
$total_records = $total_row['total'];

// Prepare response
$response = array(
    "draw" => isset($_GET['draw']) ? intval($_GET['draw']) : 1, // Provide default value if draw is not set
    "recordsTotal" => $total_records,
    "recordsFiltered" => $total_records,
    "data" => $data
);

// Output JSON
header('Content-Type: application/json');
echo json_encode($response);

// Close the database connection
mysqli_close($conn);
?>
