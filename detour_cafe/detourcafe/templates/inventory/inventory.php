<?php
require "../../conn/conn.php";

if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

function addForm($category, $item) {
  // Regular expression to allow only letters, numbers, and spaces
  return preg_match('/^[a-zA-Z0-9\s]+$/', $category) &&
  preg_match('/^[a-zA-Z0-9\s]+$/', $item);
}

if (isset($_POST["save"])) {
    $category = trim($_POST["category"]);
    $item = trim($_POST["item"]);
    $updated = date('Y-m-d H:i:s');

    // Validate inputs
    if (empty($category) || empty($item) || !addForm($category, $item)) {
      $actionStatus = 'invalid_input';
  } else {
      // Prepare the SQL statement
      $stmt = $conn->prepare("INSERT INTO db_inventory (category, item, cases, pack, kgs, pcs, gms, status, last_updated) VALUES (?, ?, 0, 0, 0, 0, 0, 'In Stock', ?)");
      $stmt->bind_param('sss', $category, $item, $updated);

      // Execute the statement and check for success
      if ($stmt->execute()) {
          $actionStatus = 'added';
      } else {
          $actionStatus = 'error';
      }
      // Close the statement
      $stmt->close();
  }
}

// Check if the form was submitted for updating
if (isset($_POST["update_save"])) {
  $id = $_POST["edit_id"];
  $edit_category = $_POST["edit_category"];
  $edit_item = $_POST["edit_item"];
  $edit_case = $_POST["edit_case"];
  $edit_pack = $_POST["edit_pack"];
  $edit_kgs = $_POST["edit_kgs"];
  $edit_pcs = $_POST["edit_pcs"];
  $edit_gms = $_POST["edit_gms"];
  $edit_threshold = $_POST["edit_threshold"];
  $edit_updated = date('Y-m-d H:i:s');
  $orig_item = $_POST["edit_item_orig"];

  $totalInventory = $edit_case + $edit_pack + $edit_kgs + $edit_pcs + $edit_gms;

    // Determine status based on threshold
    if ($totalInventory == 0) {
        $edit_status = 'Out of Stock';
    } elseif ($totalInventory <= $edit_threshold) {
        $edit_status = 'Critical (Buy Now)';
    } else {
        $edit_status = 'In Stock';
    }

  // Update query for inventory table
  $updateInventoryQuery = "UPDATE db_inventory SET
      category='$edit_category',
      item='$edit_item',
      cases='$edit_case',
      pack='$edit_pack',
      kgs='$edit_kgs',
      pcs='$edit_pcs',
      gms='$edit_gms',
      threshold='$edit_threshold',
      status='$edit_status',
      last_updated='$edit_updated'
      WHERE ID = '$id'";

  // Update query for ingredients table
  $updateIngredientsQuery = "UPDATE db_ingredients SET
      item='$edit_item'
      WHERE item = '$orig_item'";

  // Execute update queries
  $query1 = mysqli_query($conn, $updateInventoryQuery);
  $query2 = mysqli_query($conn, $updateIngredientsQuery);

  // Check for errors
  if ($query1 && $query2) {
      $actionStatus = 'updated';
  } else {
      // Log and handle errors
      $error_message1 = mysqli_error($conn);
      $error_message2 = mysqli_error($conn);
      $actionStatus = 'error';
  }
}


if (isset($_POST["delete_save"])) {
  $id = $_POST["edit_id"];
  $edit_category = $_POST["edit_category"];
  $edit_item = $_POST["edit_item"];
  $edit_case = $_POST["edit_case"];
  $edit_pack = $_POST["edit_pack"];
  $edit_kgs = $_POST["edit_kgs"];
  $edit_pcs = $_POST["edit_pcs"];
  $edit_gms = $_POST["edit_gms"];
  $edit_status = $_POST["edit_status"];
  $edit_threshold = $_POST["edit_threshold"];
  $orig_item = $_POST["edit_item_orig"];
  $current_date_time = date('Y-m-d H:i:s');

  // Delete the record
  $delete_query = "DELETE FROM db_inventory WHERE ID = ?";
  $stmt = $conn->prepare($delete_query);
  $stmt->bind_param("i", $id);

  if ($stmt->execute()) {
      // Insert into archived_discounts
      $insert_query = "INSERT INTO archived_inventory (category, item, cases, pack, kgs, pcs, gms, threshold, status, archived_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($insert_query);
      $stmt->bind_param("ssssssssss", $edit_category, $edit_item, $edit_case, $edit_pack, $edit_kgs, $edit_pcs, $edit_gms, $edit_threshold, $edit_status, $current_date_time);

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

$sql = 'SELECT * FROM db_inventory';
$result = mysqli_query($conn, $sql);

// Fetch categories from the database
$categories_query = "SELECT category FROM db_inventory_category";
$categories_result = mysqli_query($conn, $categories_query);

// Initialize an empty array to store categories
$categories = [];

// Loop through results and store categories in the array
while ($row = mysqli_fetch_assoc($categories_result)) {
    $categories[] = $row['category'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Detour Cafe - Inventory</title>
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
      <h1>Inventory</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item active">Management <i class="bi bi-archive"></i></li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Tracker
                <a class="btn btn-sm float-end" href="../categories-inventory/categories-inventory.php">
                  <i class="bi bi-card-list"></i> Categories
                </a>
                <button type="button"  class="btn btn-sm float-end" data-bs-toggle="modal" data-bs-target="#AddInventory" style="margin-right: 5px;">
                  <i class="ri ri-add-circle-line"></i> Add Inventory Data
                </button>
              </h5>
              <!-- Table with stripped rows -->
              <table class="table datatable table-hover table-borderless">
                <thead>
                  <tr>
                    <th>
                      <b>ID</b>
                    </th>
                    <th>Category</th>
                    <th>Item</th>
                    <th>Case</th>
                    <th>Pack/Inner</th>
                    <th>KG</th>
                    <th>PCS</th>
                    <th>GMS</th>
                    <th>Threshold</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    while ($row = mysqli_fetch_assoc($result)) {
                        // Determine the class based on the status
                        $statusClass = '';
                        switch ($row['status']) {
                            case 'In Stock':
                                $statusClass = 'background-color: #C1E1C1;'; // Green background for In Stock
                                break;
                            case 'Critical (Buy Now)':
                                $statusClass = 'background-color: #FDFD96;'; // Yellow background for Critical
                                break;
                            case 'Out of Stock':
                                $statusClass = 'background-color: #FF6961;'; // Red background for Out of Stock
                                break;
                            default:
                                $statusClass = ''; // Default or unknown status
                                break;
                        }
                      ?>
                      <tr>
                        <td>
                          <?php echo $row['ID']; ?></td>
                          <td><?php echo $row['category']; ?></td>
                          <td><?php echo $row['item']; ?></td>
                          <td><?php echo $row['cases']; ?></td>
                          <td><?php echo $row['pack']; ?></td>
                          <td><?php echo $row['kgs']; ?></td>
                          <td><?php echo $row['pcs']; ?></td>
                          <td><?php echo $row['gms']; ?></td>
                          <td><?php echo $row['threshold']; ?></td>
                          <td style="<?php echo $statusClass; ?>"><?php echo $row['status']; ?></td>
                          <td>
                            <!-- Pass row data to modal -->
                            <button type="button" class="btn btn-sm btn-edit"
                              data-bs-toggle="modal"
                              data-bs-target="#EditInventoryData"
                              data-id="<?php echo $row['ID']; ?>"
                              data-category="<?php echo $row['category']; ?>"
                              data-item="<?php echo $row['item']; ?>"
                              data-cases="<?php echo $row['cases']; ?>"
                              data-pack="<?php echo $row['pack']; ?>"
                              data-kgs="<?php echo $row['kgs']; ?>"
                              data-pcs="<?php echo $row['pcs']; ?>"
                              data-gms="<?php echo $row['gms']; ?>"
                              data-threshold="<?php echo $row['threshold']; ?>"
                              data-status="<?php echo $row['status']; ?>"
                              data-updated="<?php echo $row['last_updated']; ?>">
                              <i class="bi bi-pencil"></i>
                            </button>
                          </td>
                        </tr>
                          <?php
                          }
                          ?>
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
  <!-- Add Inventory Modal -->
  <div class="modal fade" id="AddInventory" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Inventory Data</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form class="row g-3" method="POST">
            <div class="col-md-2">
              <div class="form-floating">
                <input type="number" class="form-control" id="InventoryID" placeholder="ID" disabled>
                <label for="InventoryID">ID</label>
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
            <div class="col-md-12">
              <div class="form-floating">
                <input type="text" class="form-control" name="item" id="item" placeholder="Item Name (ex: Butterscotch sauce/2L)" required>
                <label for="item">Item Name (ex: Butterscotch sauce/2L)</label>
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
  <!-- End of Add Inventory Modal -->

  <!-- Edit Inventory Modal -->
  <div class="modal fade" id="EditInventoryData" tabindex="-1">
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
            <input type="hidden" class="form-control" id="edit_id" name="edit_id" placeholder="ID" required>
            <input type="hidden" class="form-control" id="edit_item_orig" name="edit_item_orig" placeholder="ID" required>
            <input type="hidden" class="form-control" id="edit_status" name="edit_status" placeholder="ID" required>
            <input type="hidden" class="form-control" id="edit_updated" name="edit_updated" placeholder="ID" required>
            <div class="col-md-12">
              <div class="form-floating">
                <select class="form-select" id="edit_category" name="edit_category" aria-label="edit_category">
                  <?php
                    // Populate options from PHP array
                    foreach ($categories as $category) {
                    echo "<option value='$category'>$category</option>";
                    }
                  ?>
                </select>
                <label for="edit_category">Category</label>
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-floating">
                <input type="text" class="form-control" id="edit_item" name="edit_item" placeholder="Item Name (ex: Butterscotch sauce/2L)" required>
                <label for="edit_item">Item Name (ex: Butterscotch sauce/2L)</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating">
                <input type="number" class="form-control" id="edit_case" name="edit_case" placeholder="Case (ex: 1)" min="0" required>
                <label for="edit_case">Case (ex: 1)</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating">
                <input type="number" class="form-control" id="edit_pack" name="edit_pack" placeholder="Pack / Inner (ex: 1)" min="0" required>
                <label for="edit_pack">Pack / Inner (ex: 1)</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating">
                <input type="number" class="form-control" id="edit_kgs" name="edit_kgs" placeholder="KG (ex: 1)" min="0" required>
                <label for="edit_kgs">KG (ex: 1)</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating">
                <input type="number" class="form-control" id="edit_pcs" name="edit_pcs" placeholder="PCS (ex: 1)" min="0" required>
                <label for="edit_pcs">PCS (ex: 1)</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating">
                <input type="number" class="form-control" id="edit_gms" name="edit_gms" placeholder="GMS (ex: 1)" min="0" required>
                <label for="edit_gms">GMS (ex: 1)</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating">
                <input type="number" class="form-control" id="edit_threshold" name="edit_threshold" placeholder="GMS (ex: 1)" min="0" required>
                <label for="edit_gms">Threshold (ex: 200)</label>
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
      $('#EditInventoryData').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var id = button.data('id');
        var category = button.data('category');
        var item = button.data('item');
        var cases = button.data('cases');
        var pack = button.data('pack');
        var kgs = button.data('kgs');
        var pcs = button.data('pcs');
        var gms = button.data('gms');
        var threshold = button.data('threshold');
        var status = button.data('status');
        var updated = button.data('updated');

        var modal = $(this);
        modal.find('#edit_id').val(id);
        modal.find('#edit_category').val(category);
        modal.find('#edit_item_orig').val(item);
        modal.find('#edit_item').val(item);
        modal.find('#edit_case').val(cases);
        modal.find('#edit_pack').val(pack);
        modal.find('#edit_kgs').val(kgs);
        modal.find('#edit_pcs').val(pcs);
        modal.find('#edit_gms').val(gms);
        modal.find('#edit_threshold').val(threshold);
        modal.find('#edit_status').val(status);
        modal.find('#edit_updated').text('Last Updated: ' + updated);
      });
    }); 

      $('#editInventoryForm').on('submit', function(e) {
        console.log("Form submitted");
        var formData = $(this).serializeArray();
        formData.forEach(function(item) {
          console.log(item.name + ": " + item.value);
        });
      });
      

      document.getElementById('delete_save').addEventListener('click', function () {
        if (confirm('Are you sure you want to archive this data?')) {
            // Create a hidden input element for the delete_save action
            var deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'delete_save';
            deleteInput.value = '1';

            // Append the input to the form and submit the form
            var form = document.getElementById('editInventoryForm');
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