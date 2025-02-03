<?php
require "../../conn/conn.php";

if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

// Assuming $conn is your mysqli connection object

if (isset($_POST["import"]) && isset($_FILES["salesFile"])) {
    $file = $_FILES["salesFile"]["tmp_name"];

    try {
        // Load the Excel file
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();

        // Initialize an array to hold the rows for insertion
        $rows_to_insert = [];
        $batch_size = 1000; // Adjust the batch size as needed

        // Prepare the insert statement
        $stmt = $conn->prepare("INSERT INTO db_sales (category, item, items_sold, discounts, net_sales, cost_of_goods, gross_profit, id_number, date_time, cashier_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Disable auto-commit
        $conn->autocommit(FALSE);

        // Loop through each row in the sheet
        foreach ($sheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(FALSE); // Loop through all cells, even if not populated

            $cells = [];
            foreach ($cellIterator as $cell) {
                // Get the value of each cell
                $value = $cell->getValue();
                $cells[] = $value;
            }

            // Skip the header row (assuming 'Category' is in the first cell)
            if ($cells[0] == 'Category') {
                continue;
            }

            // Check if the row is not empty
            if (array_filter($cells)) { // Filter out empty values
                // Select specific columns (adjust indices as needed)
                $category = mysqli_real_escape_string($conn, $cells[0]); // Column A
                $item = mysqli_real_escape_string($conn, $cells[1]);     // Column B
                $itemsSold = mysqli_real_escape_string($conn, $cells[2]); // Column C
                $discounts = mysqli_real_escape_string($conn, $cells[3]); // Column D
                $netSales = mysqli_real_escape_string($conn, $cells[4]);  // Column E
                $cost = mysqli_real_escape_string($conn, $cells[5]);      // Column F
                $gross = mysqli_real_escape_string($conn, $cells[6]);     // Column G
                $id_number = mysqli_real_escape_string($conn, $cells[7]); // Column H
                
                // Convert date from 'YYYY-MM-DDTHH:MM' to 'YYYY-MM-DD HH:MM:SS'
                $date = $cells[8]; // Column I
                if (is_numeric($date)) {
                    // Convert Excel serial date to PHP DateTime object
                    $date = Date::excelToDateTimeObject($date);
                    $date = $date->format('Y-m-d H:i:s');
                } elseif (strpos($date, 'T') !== false) {
                    $date = str_replace('T', ' ', $date) . ':00'; // Append seconds
                } else {
                    $date = date('Y-m-d H:i:s', strtotime($date)); // For dates not in 'T' format
                }

                $cashier_name = mysqli_real_escape_string($conn, $cells[9]); // Column H

                // Add to rows_to_insert array
                $rows_to_insert[] = [
                    $category, $item, $itemsSold, $discounts, $netSales, $cost, $gross, $id_number, $date, $cashier_name
                ];

                // Insert in batches
                if (count($rows_to_insert) >= $batch_size) {
                    foreach ($rows_to_insert as $row) {
                        $stmt->bind_param('ssssssssss', ...$row);
                        $stmt->execute();
                    }
                    // Clear the array for the next batch
                    $rows_to_insert = [];
                }
            }
        }

        // Insert any remaining rows
        if (count($rows_to_insert) > 0) {
            foreach ($rows_to_insert as $row) {
                $stmt->bind_param('ssssssssss', ...$row);
                $stmt->execute();
            }
        }

        // Commit the transaction
        $conn->commit();

        // Re-enable auto-commit
        $conn->autocommit(TRUE);

        echo "Data imported successfully!";
    } catch (Exception $e) {
        $conn->rollback(); // Rollback the transaction on error
        echo 'Error loading file: ', $e->getMessage();
    }
}


function isValidInput($category, $item, $ItemsSold, $NetSales, $cost, $gross) {
  // Regular expression to allow only letters, numbers, and spaces
  return preg_match('/^[a-zA-Z0-9\s]+$/', $category) &&
  preg_match('/^[a-zA-Z0-9\s]+$/', $item) &&
  preg_match('/^[a-zA-Z0-9\s]+$/', $ItemsSold) &&
  preg_match('/^[a-zA-Z0-9\s]+$/', $NetSales) &&
  preg_match('/^[a-zA-Z0-9\s]+$/', $cost) &&
  preg_match('/^[a-zA-Z0-9\s]+$/', $gross);
}

// Handle Save (Insert) operation
if (isset($_POST["save"])) {
    // Retrieve data from POST
    $category = $_POST["category"];
    $item = $_POST["item"];
    $ItemsSold = $_POST["ItemsSold"];
    $discounts = $_POST["discounts"];
    $NetSales = $_POST["NetSales"];
    $cost = $_POST["cost"];
    $gross = $_POST["gross"];
    $date = $_POST["date"];
  
    // SQL query for insertion
    $insert_query = "INSERT INTO db_sales (category, item, items_sold, discounts, net_sales, cost_of_goods, gross_profit, date_time)
                    VALUES ('$category', '$item', '$ItemsSold', '$discounts', '$NetSales', '$cost', '$gross', '$date')";
    
    // Execute insertion query
    if (mysqli_query($conn, $insert_query)) {
        $actionStatus = 'added';
    } else {
        $actionStatus = 'error';
    }
}

// Handle Update operation
if (isset($_POST["update_save"])) {
    // Retrieve data from POST
    $id = $_POST["edit_id"];
    $ItemsSold = $_POST["edit_itemsSold"];
    $discounts = $_POST["edit_discounts"];
    $NetSales = $_POST["edit_NetSales"];
    $cost = $_POST["edit_cost"];
    $gross = $_POST["edit_gross"];
    $edit_id_number = $_POST["edit_id_number"];
    $date = $_POST["edit_date"];

    // SQL query for update
    $update_query = "UPDATE db_sales SET 
                    items_sold = '$ItemsSold', 
                    discounts = '$discounts', 
                    net_sales = '$NetSales', 
                    cost_of_goods = '$cost', 
                    gross_profit = '$gross', 
                    id_number = '$edit_id_number',
                    date_time = '$date' 
                    WHERE ID = '$id'";

    // Execute update query
    if (mysqli_query($conn, $update_query)) {
       $actionStatus = 'updated';
    } else {
        $actionStatus = 'error';
    }
}

if (isset($_POST["delete_save"])) {
  $id = $_POST["edit_id"];
  $edit_item = $_POST['edit_item'];
  $edit_category = $_POST['edit_category'];
  $ItemsSold = $_POST["edit_itemsSold"];
  $discounts = $_POST["edit_discounts"];
  $NetSales = $_POST["edit_NetSales"];
  $cost = $_POST["edit_cost"];
  $gross = $_POST["edit_gross"];
  $date = $_POST["edit_date"];
  $current_date_time = date('Y-m-d H:i:s');

  // Delete the record
  $delete_query = "DELETE FROM db_sales WHERE ID = ?";
  $stmt = $conn->prepare($delete_query);
  $stmt->bind_param("i", $id);

  if ($stmt->execute()) {
      // Insert into archived_discounts
      $insert_query = "INSERT INTO archived_sales (category, item, items_sold, discounts, net_sales, cost_of_goods, gross_profit, date_time, archived_date)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($insert_query);
      $stmt->bind_param("sssssssss", $edit_item, $edit_category, $ItemsSold, $discounts, $NetSales, $cost, $gross, $date, $current_date_time);

      if ($stmt->execute()) {
          $actionStatus = 'deleted';
      } else {
          $actionStatus = 'error';
      }
  } else {
      $actionStatus = 'error';
  }

  $stmt->close();
}

// Fetch categories from the database
$categories_query = "SELECT category FROM db_menu_category";
$categories_result = mysqli_query($conn, $categories_query);

// Initialize an empty array to store categories
$categories = [];

// Loop through results and store categories in the array
while ($row = mysqli_fetch_assoc($categories_result)) {
    $categories[] = $row['category'];
}

// Fetch categories with items
$categories_with_items = [];
$categories_query = "SELECT category, item FROM db_menu"; // Adjust your query according to your database structure
$categories_result = mysqli_query($conn, $categories_query);

while ($row = mysqli_fetch_assoc($categories_result)) {
    $category = $row['category'];
    $item = $row['item'];

    if (!isset($categories_with_items[$category])) {
        $categories_with_items[$category] = [];
    }

    $categories_with_items[$category][] = $item;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Detour Cafe - Sales</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="../../assets/title-logo.png" rel="icon">
  <link href="../../assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../../assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="../../assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="../../assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="../../assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="../../assets/vendor/simple-datatables/style.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

  <!-- Template Main CSS File -->
  <link href="../../assets/css/style.css" rel="stylesheet">
</head>

<body>

<?php require_once '../navbar/header.php';?>

  <main id="main" class="main">

  <div id="alert-container" class="container mt-3" style="display:none;">
  <!-- Alerts will be inserted here -->
  </div>

    <div class="pagetitle">
      <h1>Sales</h1>
      <?php if (isset($_SESSION['position']) && $_SESSION['position'] == "Admin") { ?>
        <nav>
          <ol class="breadcrumb">
            <li class="breadcrumb-item active">Management <i class="bi bi-cash"></i></li>
          </ol>
        </nav>
      <?php }?>
      <?php if (isset($_SESSION['position']) && $_SESSION['position'] == "Employee") { ?>
        <nav>
          <ol class="breadcrumb">
            <li class="breadcrumb-item active">Merchant <i class="bi bi-cash"></i></li>
          </ol>
        </nav>
      <?php }?>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Tracker
              <?php if (isset($_SESSION['position']) && $_SESSION['position'] == "Admin") { ?>
               <button type="button"  class="btn btn-sm float-end" data-bs-toggle="modal" data-bs-target="#FileDownload">
                  <i class="bi bi-file-earmark-plus"></i> Import / Export XLSX Template
                </button>
                <button type="button"  class="btn btn-sm float-end" data-bs-toggle="modal" data-bs-target="#AddSales" style="margin-right: 5px;">
                  <i class="ri ri-add-circle-line"></i> Add Sales Data
                </button>
              <?php }?>
              </h5>
              <table id="salesTable" class="table table-hover table-borderless" style="width: 100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category</th>
                        <th>Item</th>
                        <th>Items Sold</th>
                        <th>Discounts</th>
                        <th>Net Sales</th>
                        <th>Cost of Goods</th>
                        <th>Gross Profit</th>
                        <th>Date / Time</th>
                        <th>Cashier</th>
                        <?php if (isset($_SESSION['position']) && $_SESSION['position'] == "Admin") { ?>
                        <th>Action</th>
                        <?php }?>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTables will populate this -->
                </tbody>
            </table>
              <!-- End Table with stripped rows -->
            </div>
          </div>

        </div>
      </div>
    </section>

  </main><!-- End #main -->

  <!-- Modals -->
  <!-- Add Sales Modal -->
  <div class="modal fade" id="AddSales" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Sales Data</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="salesForm" class="row g-3" method="post">
            <div class="col-md-2">
              <div class="form-floating">
                <input type="number" class="form-control" name="ID" id="SalesID" placeholder="ID" disabled>
                <label for="MenuID">ID</label>
              </div>
            </div>
            <div class="col-md-10">
              <div class="form-floating">
                <select class="form-select" name="category" id="category" aria-label="Category" required>
                  <?php
                    // Populate options from PHP array
                    foreach ($categories as $category) {
                    echo "<option value='$category'>$category</option>";
                    }
                  ?>
                </select>
                <label for="category">Category</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating">
                <select class="form-select" name="item" id="item" aria-label="Item Name" required>
                    <!-- Options will be dynamically populated by JavaScript -->
                </select>
                <label for="item">Item Name</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating">
                <input type="number" class="form-control" name="ItemsSold" id="ItemsSold" placeholder="Items Sold (ex: 2)" min="0" required>
                <label for="ItemsSold">Items Sold (ex: 2)</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating">
                <input type="number" class="form-control" name="discounts" id="discounts" placeholder="Discounts (ex: 20%)" min="0" max="100">
                <label for="discounts">Discounts (ex: 20%)</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating">
                <input type="number" class="form-control" name="NetSales" id="NetSales" placeholder="Net Sales (ex: 200)" min="0" required>
                <label for="NetSales">Net Sales (ex: 200)</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating">
                <input type="number" class="form-control" name="cost" id="cost" placeholder="Cost of Goods (ex: 90)" min="0" required>
                <label for="cost">Cost of Goods (ex: 90)</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating">
                <input type="number" class="form-control" name="gross" id="gross" placeholder="Gross Profit (ex: 200)" min="0" required>
                <label for="gross">Gross Profit (ex: 200)</label>
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-floating">
                <input type="datetime-local" class="form-control" name="date" id="date" placeholder="Date (ex: 12/17/2003)" step="1" required>
                <label for="date">Date / Time</label>
              </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" name="save" class="btn btn-save">Save</button>
        </div>
        </form>
      </div>
    </div>
  </div>
  <!-- End of Add Sales Modal -->

  <!-- Edit Sales Modal -->
  <div class="modal fade" id="EditSales" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Sales Data</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="editSalesForm" class="form-sales row g-3" method="post">
            <input type="hidden" class="form-control" id="edit_id" name="edit_id"> <!-- Ensure name is correct -->
            <div class="col-md-12">
              <div class="form-floating">
                <input type="text" class="form-control" name="edit_category" id="edit_category" placeholder="Category" readonly>
                <label for="edit_category">Category</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating">
                <input type="text" class="form-control" name="edit_item" id="edit_item" placeholder="Item Name" readonly>
                <label for="edit_item">Item Name</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating">
                <input type="number" class="form-control" id="edit_itemsSold" name="edit_itemsSold" placeholder="Items Sold (ex: 2)" min="0" required>
                <label for="edit_itemsSold">Items Sold (ex: 2)</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating">
                <input type="number" class="form-control" id="edit_discounts" name="edit_discounts" placeholder="Discounts (ex: 20%)" min="0" max="100">
                <label for="edit_discounts">Discounts (ex: 20%)</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating">
                <input type="number" class="form-control" id="edit_NetSales" name="edit_NetSales" placeholder="Net Sales (ex: 200)" min="0" required>
                <label for="edit_NetSales">Net Sales (ex: 200)</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating">
                <input type="number" class="form-control" id="edit_cost" name="edit_cost" placeholder="Cost of Goods (ex: 90)" min="0" required>
                <label for="edit_cost">Cost of Goods (ex: 90)</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating">
                <input type="number" class="form-control" id="edit_gross" name="edit_gross" placeholder="Gross Profit" min="0" required>
                <label for="edit_gross">Gross Profit (ex: 200)</label>
              </div>
            </div>
            <div class="col-md-7">
              <div class="form-floating">
                <input type="datetime-local" class="form-control" id="edit_date" name="edit_date" placeholder="Date" step="1" required>
                <label for="edit_date">Date / Time</label>
              </div>
            </div>
            <div class="col-md-5">
              <div class="form-floating">
                <input type="text" class="form-control" id="edit_id_number" name="edit_id_number" placeholder="ID Number" required>
                <label for="edit_id_number">ID Number</label>
              </div>
            </div>
        </div>
        <div class="modal-footer justify-content-between">
        <button type="button" name="delete_save" id="delete_save" class="btn btn-danger"><i class="bi bi-bookmark-plus"></i> Archive</button>
          <div>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="update_save" class="btn btn-save" id="update_save">Update</button>
          </div>
        </div>
        </form>
      </div>
    </div>
  </div>
  <!-- End of Edit Sales Modal -->

  <!-- File Modal -->
  <div class="modal fade" id="FileDownload" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <form method="post" enctype="multipart/form-data" id="importSales">
          <div class="modal-header">
            <h5 class="modal-title">Import / Export XLSX Template</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row mb-3">
              <div class="col-md-12 d-flex align-items-center" style="padding:12px;">
                <h6 class="me-3 mb-0">Export:</h6>
                <div class="flex-grow-1">
                  <a href="Sales XLSX Template.xlsx" download="Sales XLSX Template.xlsx" class="btn btn-save d-flex align-items-center justify-content-center w-100">
                    <i class="bi bi-file-earmark-arrow-up me-2"></i>Export XLSX Template
                  </a>
                </div>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-12 d-flex align-items-center">
                <h6 class="me-3 mb-0">Import:</h6>
                <div class="flex-grow-1">
                  <!-- Custom button for file input -->
                  <label id="fileLabel" class="btn btn-save w-100" for="fileInput">
                    <i class="bi bi-file-earmark-arrow-down me-2"></i>Choose File
                  </label>
                  <input type="file" id="fileInput" name="salesFile" id="salesFile" class="d-none" accept=".xlsx, .xls" required>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="import" class="btn btn-save">Import</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- End of File Modal -->

  
  <!-- End of Modals -->

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span>Detour Cafe</span></strong>. All Rights Reserved 2024
    </div>
    <div class="credits">
    </div>
  </footer><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Bootstrap Bundle with Popper -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

  <!-- Vendor JS Files -->
  <script src="../../assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="../../assets/vendor/chart.js/chart.min.js"></script>
  <script src="../../assets/vendor/echarts/echarts.min.js"></script>
  <script src="../../assets/vendor/quill/quill.min.js"></script>
  <script src="../../assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="../../assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="../../assets/vendor/php-email-form/validate.js"></script>
  
  <!-- jQuery -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

  <!-- Template Main JS File -->
  <script src="../../assets/js/main.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

  <script>
  // Handle showing data in modal
  $(document).ready(function() {
    $('#EditSales').on('show.bs.modal', function(event) {
      var button = $(event.relatedTarget);
      var id = button.data('id');
      var category = button.data('category');
      var item = button.data('item');
      var itemsSold = button.data('itemssold');
      var discounts = button.data('discounts');
      var netSales = button.data('netsales');
      var cost = button.data('cost');
      var gross = button.data('gross');
      var idnumber = button.data('id-number');
      var date = button.data('date');

      var modal = $(this);
      modal.find('#edit_id').val(id);
      modal.find('#edit_category').val(category);
      modal.find('#edit_item').val(item);
      modal.find('#edit_itemsSold').val(itemsSold);
      modal.find('#edit_discounts').val(discounts);
      modal.find('#edit_NetSales').val(netSales);
      modal.find('#edit_cost').val(cost);
      modal.find('#edit_gross').val(gross);
      modal.find('#edit_id_number').val(idnumber);
      modal.find('#edit_date').val(date);
    });
  });

    document.addEventListener("DOMContentLoaded", function () {
      var categories = <?php echo json_encode($categories_with_items); ?>; // Assuming you fetch this array from PHP

      var categorySelect = document.getElementById('category');
      var itemSelect = document.getElementById('item');

      categorySelect.addEventListener('change', function () {
          var selectedCategory = this.value;
          var items = categories[selectedCategory] || [];

          // Clear previous options
          itemSelect.innerHTML = '';

          // Add new options
          items.forEach(function (item) {
              var option = document.createElement('option');
              option.value = item;
              option.textContent = item;
              itemSelect.appendChild(option);
          });
      });

      // Trigger change event to populate items on page load
      categorySelect.dispatchEvent(new Event('change'));
  });

  // Confirm delete action
  document.getElementById('delete_save').addEventListener('click', function () {
        if (confirm('Are you sure you want to archive this data?')) {
            // Create a hidden input element for the delete_save action
            var deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'delete_save';
            deleteInput.value = '1';

            // Append the input to the form and submit the form
            var form = document.getElementById('editSalesForm');
            form.appendChild(deleteInput);
            form.submit();
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('fileInput');
    const fileLabel = document.getElementById('fileLabel');

    fileInput.addEventListener('change', function () {
      if (fileInput.files.length > 0) {
        const fileName = fileInput.files[0].name;
        fileLabel.innerHTML = `<i class="bi bi-file-earmark-arrow-down me-2"></i>${fileName}`;
      } else {
        fileLabel.innerHTML = `<i class="bi bi-file-earmark-arrow-down me-2"></i>Choose File`;
      }
    });
  });
  </script>

  <script>
  $(document).ready(function() {
      $('#salesTable').DataTable({
          "processing": true,
          "serverSide": true,
          "ajax": {
              "url": "loader.php", // Path to your PHP script
              "type": "GET",
              "data": function (d) {
                  // Add user information and role to the request
                  d.user_name = '<?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : ''; ?>';
                  d.user_role = '<?php echo isset($_SESSION['position']) ? $_SESSION['position'] : ''; ?>';
              }
          },
          "columns": [
              { "data": "ID" },
              { "data": "category" },
              { "data": "item" },
              { "data": "items_sold" },
              { "data": "discounts" },
              { "data": "net_sales" },
              { "data": "cost_of_goods" },
              { "data": "gross_profit" },
              { "data": "date_time" },
              { "data": "cashier_name" },
              <?php if (isset($_SESSION['position']) && $_SESSION['position'] == "Admin") { ?>
              {
                  "data": null,
                  "render": function(data, type, row) {
                      return '<button type="button" class="btn btn-sm btn-edit" ' +
                          'data-bs-toggle="modal" ' +
                          'data-bs-target="#EditSales" ' +
                          'data-id="' + row.ID + '" ' +
                          'data-category="' + row.category + '" ' +
                          'data-item="' + row.item + '" ' +
                          'data-itemsSold="' + row.items_sold + '" ' +
                          'data-discounts="' + row.discounts + '" ' +
                          'data-netSales="' + row.net_sales + '" ' +
                          'data-cost="' + row.cost_of_goods + '" ' +
                          'data-gross="' + row.gross_profit + '" ' +
                          'data-id-number="' + row.id_number + '" ' +
                          'data-date="' + row.date_time + '">' +
                          '<i class="bi bi-pencil"></i></button>';
                  }
              }
              <?php }?>
          ]
      });
  });
  </script>

  <script>
      document.addEventListener("DOMContentLoaded", function () {
        var actionStatus = "<?php echo $actionStatus; ?>";

        if (actionStatus) {
            var alertType = "";
            var alertMessage = "";
            var alertIcon = "";

            if (actionStatus === "added") {
                alertType = "alert-success";
                alertMessage = "Data has been successfully added.";
                alertIcon = "bi bi-check-circle";
            } else if (actionStatus === "updated") {
                alertType = "alert-info";
                alertMessage = "Data has been successfully updated.";
                alertIcon = "bi bi-pencil";
            } else if (actionStatus === "deleted") {
                alertType = "alert-secondary";
                alertMessage = "Data has been successfully archived.";
                alertIcon = "bi bi-bookmark-plus";
            } else if (actionStatus === "error") {
                alertType = "alert-warning";
                alertMessage = "There was an error performing the operation.";
                alertIcon = "bi bi-exclamation-circle";
            }

            var alertHtml = `
                <div class="alert ${alertType} alert-dismissible fade show" role="alert">
                    <i class="${alertIcon} me-1"></i>
                    ${alertMessage}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;

            document.getElementById('alert-container').innerHTML = alertHtml;
            $('#alert-container').show();
        }
    });
  </script>
</body>

</html>