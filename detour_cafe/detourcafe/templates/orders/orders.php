<?php 
require "../../conn/conn.php";
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle Update operation
if (isset($_POST["update_save"])) {
    // Retrieve data from POST and sanitize inputs
    $id = mysqli_real_escape_string($conn, $_POST["edit_id"]);
    $order_status = mysqli_real_escape_string($conn, $_POST["edit_orderstatus"]);
    $category = mysqli_real_escape_string($conn, $_POST["edit_category"]);
    $item = mysqli_real_escape_string($conn, $_POST["edit_item"]);
    $itemsSold = (int)$_POST["edit_itemsold"];
    $price = (float)$_POST["edit_netsales"];
    $discount_percent = (float)$_POST["edit_discounts"];
    $cost_of_goods = (float)$_POST["edit_cost"];
    $id_number = isset($_POST['edit_number']) && !empty($_POST['edit_number']) ? mysqli_real_escape_string($conn, $_POST['edit_number']) : "Not Applicable";
    $current_date_time = date('Y-m-d H:i:s');

    // SQL query for update
    $update_query = "UPDATE order_statuses SET 
                    order_status = '$order_status'
                    WHERE ID = '$id'";

    // Execute update query
    if (mysqli_query($conn, $update_query)) {
        // Check if the new status is 'Completed'
        if ($order_status === 'Completed') {
            $new_price = $price * ($discount_percent / 100);
            $final_price = $price - $new_price;
            $cashier_name = mysqli_real_escape_string($conn, $_SESSION['full_name']);
            $gross_profit = $final_price - $cost_of_goods;
            
            // Insert into sales table
            $sqlInsertSales = "INSERT INTO db_sales (category, item, items_sold, discounts, net_sales, cost_of_goods, gross_profit, id_number, date_time, cashier_name) 
                                VALUES ('$category', '$item', $itemsSold, $discount_percent, $final_price, $cost_of_goods, $gross_profit, '$id_number', '$current_date_time', '$cashier_name')";
            
            if (mysqli_query($conn, $sqlInsertSales)) {
                // Get ingredients and current inventory for the item
                $sqlIngredients = "SELECT * FROM db_ingredients WHERE ingredient_for = '$item'";
                $resultIngredients = mysqli_query($conn, $sqlIngredients);
            
                if ($resultIngredients && mysqli_num_rows($resultIngredients) > 0) {
                    while ($row = mysqli_fetch_assoc($resultIngredients)) {
                        $ingredientName = $row['item'];
                        $unit = $row['unit'];
                        $ingredientCount = $row['count'];
                        $finalIngredientCount = $ingredientCount * $itemsSold;
            
                        // Fetch inventory for the ingredient
                        $sqlInventory = "SELECT * FROM db_inventory WHERE item = '$ingredientName'";
                        $resultInventory = mysqli_query($conn, $sqlInventory);
            
                        if ($resultInventory && mysqli_num_rows($resultInventory) > 0) {
                            $row2 = mysqli_fetch_assoc($resultInventory);
            
                            $inventorycount = $row2['cases'] + $row2['pack'] + $row2['kgs'] + $row2['pcs'] + $row2['gms'];
                            $threshold = $row2['threshold'];
                            $newCount = max($inventorycount - $finalIngredientCount, 0); // Correct inventory calculation
                            $current_date_time = date('Y-m-d H:i:s');
            
                            // Determine status based on threshold
                            if ($newCount == 0) {
                                $edit_status = 'Out of Stock';
                            } elseif ($newCount <= $threshold) {
                                $edit_status = 'Critical (Buy Now)';
                            } else {
                                $edit_status = 'In Stock';
                            }
            
                            // Update inventory
                            $updateInventory = "UPDATE db_inventory SET $unit = '$newCount', status = '$edit_status', last_updated = '$current_date_time' WHERE item = '$ingredientName'";
            
                            // Execute the update query
                            if (!mysqli_query($conn, $updateInventory)) {
                                echo "Error updating inventory: " . mysqli_error($conn);
                            }
                        }
                    }
                }
            } else {
                echo "Error inserting into sales: " . mysqli_error($conn);
            }
        }

        $actionStatus = 'updated';
    } else {
        echo "Error updating order status: " . mysqli_error($conn);
        $actionStatus = 'error';
    }
}

// Fetch orders based on user role
$userName = mysqli_real_escape_string($conn, $_SESSION['full_name']);
if (isset($_SESSION['position']) && $_SESSION['position'] == "Admin") {
    $orderStatusesQuery = "SELECT * FROM order_statuses ORDER BY ID DESC";
} else {
    $orderStatusesQuery = "SELECT * FROM order_statuses WHERE cashier_name = '$userName' ORDER BY ID DESC";
}
$ordersResult = mysqli_query($conn, $orderStatusesQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Detour Cafe - Menu</title>
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
      <h1>Order Statuses</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item active">Merchant <i class="bi bi-bag-dash"></i></li>
        </ol>
      </nav>
      <div>

      <section class="section">
        <div class="row">
          <div class="col-lg-12">
  
            <div class="card">
              <div class="card-body">
                  <h5 class="card-title">Tracker</h5>
                <!-- Table with stripped rows -->
                <table class="table datatable table-hover table-borderless">
                  <thead>
                    <tr>
                      <th><b>ID</b></th>
                      <th>Category</th>
                      <th>Item</th>
                      <th>Items Sold</th>
                      <th>Status</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                      while ($row = mysqli_fetch_assoc($ordersResult)){
                        // Determine the background color based on the order status
                        $status = $row['order_status'];
                        $bgColor = '';

                        switch ($status) {
                            case 'Pending':
                                $bgColor = 'background-color: #FFEB99;'; // Pastel Yellow
                                break;
                            case 'Completed':
                                $bgColor = 'background-color: #B2FAB4; color: black;'; // Pastel Green
                                break;
                            case 'Preparing':
                                $bgColor = 'background-color: #ADD8E6; color: black;'; // Pastel Blue
                                break;
                            default:
                                $bgColor = '';
                                break;
                        }
                  ?>
                    <tr style="<?php echo $bgColor; ?>">
                      <td><?php echo $row['ID'];?></td>
                      <td><?php echo $row['category'];?></td>
                      <td><?php echo $row['item'];?></td>
                      <td><?php echo $row['items_sold'];?></td>
                      <td style="<?php echo $bgColor; ?>"><?php echo $row['order_status'];?></td>
                      <td>
                        <button type="button" class="btn btn-sm"
                          data-bs-toggle="modal"
                          data-bs-target="#EditOrders"
                          data-id="<?php echo $row['ID'];?>"
                          data-category="<?php echo $row['category'];?>"
                          data-item="<?php echo $row['item'];?>"
                          data-itemssold="<?php echo $row['items_sold'];?>"
                          data-discounts="<?php echo $row['discounts'];?>"
                          data-netsales="<?php echo $row['net_sales'];?>"
                          data-cost="<?php echo $row['cost_of_goods'];?>"
                          data-gross="<?php echo $row['gross_profit'];?>"
                          data-number="<?php echo $row['id_number'];?>"
                          data-date="<?php echo $row['date_time'];?>"
                          data-cashier="<?php echo $row['cashier_name'];?>"
                          data-orders="<?php echo $row['order_status'];?>">
                          <i class="bi bi-pencil"></i>
                        </button>
                      </td>
                    </tr>
                  <?php
                      }
                  ?>
                  </tbody>
                </table>


              </div>
            </div>
  
          </div>
        </div>
      </section>

      </div>
    </div><!-- End Page Title -->

    </main>

      <!-- Edit Inventory Modal -->
  <div class="modal fade" id="EditOrders" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title">Edit Inventory Data</h5>
            <p class="subtotal-discounts" id="edit_updated" style="margin-bottom: 0;"></p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form class="row g-3" id="editInventoryForm" method="POST">
            <input type="hidden" id="edit_id" name="edit_id">
            <input type="hidden" id="edit_category" name="edit_category">
            <input type="hidden" id="edit_item" name="edit_item">
            <input type="hidden" id="edit_itemsold" name="edit_itemsold">
            <input type="hidden" id="edit_discounts" name="edit_discounts">
            <input type="hidden" id="edit_netsales" name="edit_netsales">
            <input type="hidden" id="edit_cost" name="edit_cost">
            <input type="hidden" id="edit_gross" name="edit_gross">
            <input type="hidden" id="edit_number" name="edit_number">
            <input type="hidden" id="edit_date" name="edit_date">
            <input type="hidden" id="edit_cashier" name="edit_cashier">
            <div class="col-md-12">
              <div class="form-floating">
                <select class="form-select" id="edit_orderstatus" name="edit_orderstatus" aria-label="edit_orderstatus">
                  <option value="Pending">Pending</option>
                  <option value="Preparing">Preparing</option>
                  <option value="Completed">Completed</option>
                </select>
                <label for="edit_orderstatus">Status</label>
              </div>
            </div>
        </div>
        <div class="modal-footer justify-content-between">
        <button type="button" name="delete_save" id="delete_save" class="btn btn-danger"><i class="bi bi-bookmark-plus"></i> Archive</button>
          <div>
            <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="update_save" class="btn btn-save">Update</button>
          </div>
        </div>
      </form>
      </div>
    </div>
  </div>
  <!-- End of Edit Inventory Modal -->

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

  <script>
    $(document).ready(function() {
    $('#EditOrders').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var id = button.data('id');
        var orders = button.data('orders');
        var category = button.data('category');
        var item = button.data('item');
        var itemssold = button.data('itemssold');
        var discounts = button.data('discounts');
        var net = button.data('netsales');
        var cost = button.data('cost');
        var gross = button.data('gross');
        var number = button.data('number');
        var date = button.data('date');
        var cashier = button.data('cashier');

        var modal = $(this);
        modal.find('#edit_id').val(id);
        modal.find('#edit_category').val(category);
        modal.find('#edit_item').val(item);
        modal.find('#edit_itemsold').val(itemssold);
        modal.find('#edit_discounts').val(discounts);
        modal.find('#edit_netsales').val(net);
        modal.find('#edit_cost').val(cost);
        modal.find('#edit_gross').val(gross);
        modal.find('#edit_number').val(number);
        modal.find('#edit_date').val(date);
        modal.find('#edit_cashier').val(cashier);
        modal.find('#edit_orderstatus').val(orders);
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
                alertMessage = "Order Status has been successfully updated.";
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