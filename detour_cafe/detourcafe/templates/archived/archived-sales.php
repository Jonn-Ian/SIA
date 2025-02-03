<?php
require "../../conn/conn.php";

if (session_status() == PHP_SESSION_NONE) {
  session_start();
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

  // Delete the record
  $delete_query = "DELETE FROM archived_sales WHERE ID = ?";
  $stmt = $conn->prepare($delete_query);
  $stmt->bind_param("i", $id);

  if ($stmt->execute()) {
      $insert_query = "INSERT INTO db_sales (category, item, items_sold, discounts, net_sales, cost_of_goods, gross_profit, date_time)
      VALUES ( ?, ?, ?, ?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($insert_query);
      $stmt->bind_param("ssssssss", $edit_item, $edit_category, $ItemsSold, $discounts, $NetSales, $cost, $gross, $date);

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

$sql = 'SELECT * FROM archived_sales';
$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Detour Cafe - Archive</title>
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

<!-- Display message if any -->
<?php if (!empty($message)): ?>
    <script>alert("<?php echo $message; ?>");</script>
<?php endif; ?>

<?php require_once '../navbar/header.php';?>

  <main id="main" class="main">

  <div id="alert-container" class="container mt-3" style="display:none;">
  <!-- Alerts will be inserted here -->
  </div>

    <div class="pagetitle">
      <h1>Archived</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item active">Archived / Sales <i class="bi bi-cash"></i></li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
          <div class="col-lg-12">
  
            <div class="card">
              <div class="card-body">
                  <h5 class="card-title">Sales
                  </h5>
                  <table class="table datatable table-hover table-borderless">
                <thead>
                  <tr>
                    <th><b>ID</b></th>
                    <th>Category</th>
                    <th>Item</th>
                    <th>Items Sold</th>
                    <th>Discounts</th>
                    <th>Net Sales</th>
                    <th>Cost of Goods</th>
                    <th>Gross Profit</th>
                    <th>Sale Date/Time</th>
                    <th>Archived Date/Time</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                      <td><?php echo $row['ID']; ?></td>
                      <td><?php echo $row['category']; ?></td>
                      <td><?php echo $row['item']; ?></td>
                      <td><?php echo $row['items_sold']; ?></td>
                      <td><?php echo $row['discounts'] . "%"; ?></td>
                      <td><?php echo $row['net_sales']; ?></td>
                      <td><?php echo $row['cost_of_goods']; ?></td>
                      <td><?php echo $row['gross_profit']; ?></td>
                      <td><?php echo $row['date_time']; ?></td>
                      <td><?php echo $row['archived_date']; ?></td>
                      <td>
                        <!-- Pass row data to modal -->
                        <button type="button" class="btn btn-sm btn-edit"
                                data-bs-toggle="modal"
                                data-bs-target="#EditSales"
                                data-id="<?php echo $row['ID']; ?>"
                                data-category="<?php echo $row['category']; ?>"
                                data-item="<?php echo $row['item']; ?>"
                                data-itemsSold="<?php echo $row['items_sold']; ?>"
                                data-discounts="<?php echo $row['discounts']; ?>"
                                data-netSales="<?php echo $row['net_sales']; ?>"
                                data-cost="<?php echo $row['cost_of_goods']; ?>"
                                data-gross="<?php echo $row['gross_profit']; ?>"
                                data-date="<?php echo $row['date_time']; ?>">
                          <i class="bi bi-eye"></i>
                        </button>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
              <!-- End Table with stripped rows -->
              </div>
            </div>
  
          </div>
        </div>
      </section>

  </main><!-- End #main -->

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
                <input type="number" class="form-control" id="edit_itemsSold" name="edit_itemsSold" placeholder="Items Sold (ex: 2)" min="0" readonly>
                <label for="edit_itemsSold">Items Sold (ex: 2)</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating">
                <input type="number" class="form-control" id="edit_discounts" name="edit_discounts" placeholder="Discounts (ex: 20%)" min="0" max="100" readonly>
                <label for="edit_discounts">Discounts (ex: 20%)</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating">
                <input type="number" class="form-control" id="edit_NetSales" name="edit_NetSales" placeholder="Net Sales (ex: 200)" min="0" readonly>
                <label for="edit_NetSales">Net Sales (ex: 200)</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating">
                <input type="number" class="form-control" id="edit_cost" name="edit_cost" placeholder="Cost of Goods (ex: 90)" min="0" readonly>
                <label for="edit_cost">Cost of Goods (ex: 90)</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating">
                <input type="number" class="form-control" id="edit_gross" name="edit_gross" placeholder="Gross Profit" min="0" readonly>
                <label for="edit_gross">Gross Profit (ex: 200)</label>
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-floating">
                <input type="datetime-local" class="form-control" id="edit_date" name="edit_date" placeholder="Date" step="1" readonly>
                <label for="edit_date">Date / Time</label>
              </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" name="delete_save" id="delete_save" class="btn btn-save"><i class="bi bi-bookmark-x"></i> Unrchive</button>
        </div>
        </div>
        </form>
      </div>
    </div>
  </div>
  <!-- End of Edit Sales Modal -->

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
      modal.find('#edit_date').val(date);
    });
  });
  
  // Confirm delete action
  document.getElementById('delete_save').addEventListener('click', function () {
        if (confirm('Are you sure you want to unarchive this data?')) {
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
  </script>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
        var actionStatus = "<?php echo $actionStatus; ?>";

        if (actionStatus) {
            var alertType = "";
            var alertMessage = "";
            var alertIcon = "";

            if (actionStatus === "deleted") {
                alertType = "alert-secondary";
                alertMessage = "Data has been successfully unarchived.";
                alertIcon = "bi bi-bookmark-x";
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