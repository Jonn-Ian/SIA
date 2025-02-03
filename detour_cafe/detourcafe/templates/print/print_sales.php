<?php
require "../../conn/conn.php";

// Initialize variables for date range filtering
$startDate = $_GET['startDate'] ?? null;
$endDate = $_GET['endDate'] ?? null;

// Construct SQL query with date range filtering
$sql = "SELECT * FROM db_sales";

// Append WHERE clause for date range if both start and end dates are provided
if ($startDate && $endDate) {
  $sql .= " WHERE date_time >= '$startDate 00:00:00' AND date_time <= '$endDate 23:59:59'";
}

$result = $conn->query($sql);

$currentDateTime = date('Y-m-d H:i:s'); // Get current date and time

if ($result->num_rows > 0) {
  // Output data in a printable format
  echo '<html><head><title>Sales</title>';
  echo '<link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.min.css">';
    echo '<link href="../../assets/css/style.css" rel="stylesheet">';
    echo '<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600&display=swap" rel="stylesheet">'; // Include Nunito font
    echo '<style>
      body {
        font-family: \'Nunito\', sans-serif;
      }
      .table {
        margin-top: 20px;
        border-collapse: collapse;
      }
      .table th, .table td {
        padding: 10px;
        text-align: left;
        border: 1px solid #dee2e6;
      }
      .table thead th {
        background-color: #f8f9fa;
        font-weight: 600;
      }
      .table tbody tr:nth-child(even) {
        background-color: #f2f2f2;
      }
      .table tbody tr:hover {
        background-color: #e9ecef;
      }
    </style>';
    echo '</head><body>';
    echo '<div class="container mt-5">';

    echo '<div class="row">';
    echo '<div class="col-md-6">';
    echo '<h1><img src="../../assets/login-logo.png" style="height:60px;" alt="Logo"></h1>';
    echo '</div>';
    echo '<div class="col-md-6 text-end">';
    echo '<h6 class="small">Building, Lot 6, Barangay, Maligaya Park,<br>';
    echo 'Ground floor, ICS, Block 33 Sampaguita,<br>';
    echo 'Quezon City, 1118 Metro Manila<br>';
    echo '<strong>TIN Number:</strong> 643-562-320-00000</h6>';
    echo '</div>';
    echo '</div>';
    echo '<br>';

    echo '<div class="row">';
    echo '<div class="col-md-12">';
    echo '<h1>SALES</h1>';
    echo '<h6 class="small"><strong>Printed On: </strong>' . $currentDateTime . '</h6>';
    echo '</div>';
    echo '</div>';
  echo '<table class="table table-bordered" style="width:100%;">';
  echo '<thead style>
  <tr>
  <th>ID</th>
  <th>Category</th>
  <th>Item</th>
  <th>Items Sold</th>
  <th>Discounts</th>
  <th>Net Sales</th>
  <th>Cost of Goods</th>
  <th>Gross Profit</th>
  <th>ID Number</th>
  <th>Date / Time</th>
  </tr>
  </thead>';
  echo '<tbody>';
  
  // Output data of each row
  while($row = $result->fetch_assoc()) {
    echo '<tr>';
    echo '<td>' . $row["ID"] . '</td>';
    echo '<td>' . $row["category"] . '</td>';
    echo '<td>' . $row["item"] . '</td>';
    echo '<td>' . $row["items_sold"] . '</td>';
    echo '<td>' . $row["discounts"] . '</td>';
    echo '<td>' . $row["net_sales"] . '</td>';
    echo '<td>' . $row["cost_of_goods"] . '</td>';
    echo '<td>' . $row["gross_profit"] . '</td>';
    echo '<td>' . $row["id_number"] . '</td>';
    echo '<td>' . $row["date_time"] . '</td>';
    echo '</tr>';
  }
  
  echo '</tbody>';
  echo '</table><br><br>';

  echo '<div class="row">';
  echo '<div class="col-md-6">';
  echo '<center><h6 class="small">___________________________</h6>';
  echo '<h6>Mr. Ben Trapal</h6>';
  echo '<h6><strong>OWNER</strong></h6>';
  echo '</center></div>';
  echo '<div class="col-md-6">';
  echo '<center><h6 class="small">___________________________</h6>';
  echo '<h6>Mr. Jan Yrigan</h6>';
  echo '<h6><strong>OWNER</strong></h6>';
  echo '</center></div>';
  echo '</div>';
  echo '</div>';
  echo '</body></html>';
} else {
  echo '<html><head><title>Sales</title>';
  echo '<link rel="stylesheet" href="../../assets/vendor/bootstrap/css/bootstrap.min.css">';
  echo '<link href="../../assets/css/style.css" rel="stylesheet">';
  echo '<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600&display=swap" rel="stylesheet">'; // Include Nunito font
  echo '<style>
    body {
      font-family: \'Nunito\', sans-serif;
    }
    .table {
      margin-top: 20px;
      border-collapse: collapse;
    }
    .table th, .table td {
      padding: 10px;
      text-align: left;
      border: 1px solid #dee2e6;
    }
    .table thead th {
      background-color: #f8f9fa;
      font-weight: 600;
    }
    .table tbody tr:nth-child(even) {
      background-color: #f2f2f2;
    }
    .table tbody tr:hover {
      background-color: #e9ecef;
    }
  </style>';
  echo '</head><body>';
  echo '<div class="container mt-5">';

  echo '<div class="row">';
  echo '<div class="col-md-6">';
  echo '<h1><img src="../../assets/login-logo.png" style="height:60px;" alt="Logo"></h1>';
  echo '</div>';
  echo '<div class="col-md-6 text-end">';
  echo '<h6 class="small">Building, Lot 6, Barangay, Maligaya Park,<br>';
  echo 'Ground floor, ICS, Block 33 Sampaguita,<br>';
  echo 'Quezon City, 1118 Metro Manila<br>';
  echo '<strong>TIN Number:</strong> 643-562-320-00000</h6>';
  echo '</div>';
  echo '</div>';
  echo '<br>';

  echo '<div class="row">';
  echo '<div class="col-md-12">';
  echo '<h1>SALES</h1>';
  echo '<h6 class="small"><strong>Printed On: </strong>' . $currentDateTime . '</h6>';
  echo '</div>';
  echo '</div>';
  echo '<table class="table table-bordered" style="width:100%;">';
  echo '<thead>
  <tr>
  <th>ID</th>
  <th>Category</th>
  <th>Item</th>
  <th>Items Sold</th>
  <th>Discounts</th>
  <th>Net Sales</th>
  <th>Cost of Goods</th>
  <th>Gross Profit</th>
  <th>ID Number</th>
  <th>Date / Time</th>
  </tr>
  </thead>
  <tbody>
  <tr>
   <td colspan="9" style="text-align:center;">0 Results</td>
   </tr>
 </tbody>';
    echo '</table><br><br>';

    echo '<div class="row">';
    echo '<div class="col-md-6">';
    echo '<center><h6 class="small">___________________________</h6>';
    echo '<h6>Mr. Ben Trapal</h6>';
    echo '<h6><strong>OWNER</strong></h6>';
    echo '</center></div>';
    echo '<div class="col-md-6">';
    echo '<center><h6 class="small">___________________________</h6>';
    echo '<h6>Mr. Jan Yrigan</h6>';
    echo '<h6><strong>OWNER</strong></h6>';
    echo '</center></div>';
    echo '</div>
 </div>
 </body></html>';
}

$conn->close();
?>
