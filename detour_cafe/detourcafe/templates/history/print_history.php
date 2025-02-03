<?php
require "../../conn/conn.php";

// Query the database
$startDate = $_GET['startDate'] ?? null;
$endDate = $_GET['endDate'] ?? null;

// Construct SQL query with date range filtering
$sql = "SELECT * FROM db_history";

// Append WHERE clause for date range if both start and end dates are provided
if ($startDate && $endDate) {
  $sql .= " WHERE log_date BETWEEN '$startDate' AND '$endDate'";
}

$result = mysqli_query($conn, $sql);

if ($result->num_rows > 0) {
  // Output data in a printable format
  echo '<html><head><title>History</title>';
  echo '<link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.min.css">';
  echo '<link href="../../assets/css/style.css" rel="stylesheet">';
  echo '</head><body>';
  echo '<div class="container mt-5">';
  echo '<center><h1 class="mb-4">History</h1></center>';
  echo '<table class="table table-bordered" style="width:100%;">';
  echo '<thead>
          <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Full Name</th>
            <th>Date</th>
            <th>Time</th>
            <th>Action</th>
          </tr>
        </thead>';
  echo '<tbody>';
  
  // Output data of each row
  while($row = mysqli_fetch_assoc($result)) {
    echo '<tr>';
    echo '<td>' . $row["ID"] . '</td>';
    echo '<td>' . $row["username"] . '</td>';
    echo '<td>' . $row["full_name"] . '</td>';
    echo '<td>' . $row["log_date"] . '</td>';
    echo '<td>' . $row["log_time"] . '</td>';
    echo '<td>' . $row["action"] . '</td>';
    echo '</tr>';
  }
  
  echo '</tbody>';
  echo '</table>';
  echo '</div>';
  echo '</body></html>';
} else {
  // If no results found
  echo '<html><head><title>History</title>';
  echo '<link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.min.css">';
  echo '<link href="../../assets/css/style.css" rel="stylesheet">';
  echo '</head><body>';
  echo '<div class="container mt-5">';
  echo '<center><h1 class="mb-4">History</h1></center>';
  echo '<table class="table table-bordered" style="width:100%;">';
  echo '<thead>
          <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Full Name</th>
            <th>Date</th>
            <th>Time</th>
            <th>Action</th>
          </tr>
        </thead>';
  echo '<tbody>';
  echo '<tr><td colspan="6" style="text-align:center;">0 Results</td></tr>';
  echo '</tbody>';
  echo '</table>';
  echo '</div>';
  echo '</body></html>';
}

$conn->close();
?>
