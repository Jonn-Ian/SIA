<?php
require "../../conn/conn.php";

if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

// Handle Update operation
if (isset($_POST["save"])) {
    // Retrieve data from POST
    $category = $_POST["category"];
    $type = $_POST["type"];

    // SQL query for update
    $save_query = "INSERT INTO db_inventory_category (category, type) VALUES ('$category', '$type')";

    // Execute update query
    if (mysqli_query($conn, $save_query)) {
        $actionStatus = 'added';
    } else {
        $actionStatus = 'error';
    }
}

// Handle Update operation
if (isset($_POST["update_save"])) {
    // Retrieve data from POST
    $id = $_POST["edit_id"];
    $category = $_POST["edit_category"];
    $type = $_POST["edit_type"];

    // SQL query for update
    $update_query = "UPDATE db_inventory_category SET 
                    category = '$category', 
                    type = '$type'
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
  $category = $_POST["edit_category"];
  $type = $_POST["edit_type"];
  $current_date_time = date('Y-m-d H:i:s');

  // Delete the record
  $delete_query = "DELETE FROM db_inventory_category WHERE ID = ?";
  $stmt = $conn->prepare($delete_query);
  $stmt->bind_param("i", $id);

  if ($stmt->execute()) {
      // Insert into archived_discounts
      $insert_query = "INSERT INTO archived_inventory_category (category, type, archive_date) VALUES (?, ?, ?)";
      $stmt = $conn->prepare($insert_query);
      $stmt->bind_param("sss", $category, $type, $current_date_time);

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

// Fetch all data from db_ingredients
$sql = 'SELECT * FROM db_inventory_category';
$result = mysqli_query($conn, $sql);
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Detour Cafe - Ingredients</title>
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

  <div id="alert-container" class="container mt-3 float-end" style="display:none;">
  <!-- Alerts will be inserted here -->
  </div>

    <div class="pagetitle">
      <h1>Categories</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item active">Management / Inventory <i class="bi bi-card-list"></i></li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Menu Item Categories
                <a href="../inventory/inventory.php" class="btn btn-sm float-end"><i class="bi bi-archive"></i> ‎ Cafe Inventory</a>
                <button type="button" class="btn btn-sm float-end" style="margin-right: 5px;" data-bs-toggle="modal" data-bs-target="#AddCategory">
                <i class="ri ri-add-circle-line"></i> Add Category
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
                    <th>Type</th>
                    <th>Action</th>
                  </tr>
                </thead>

                <tbody>

                <?php
                      while ($row = mysqli_fetch_assoc($result)){
                  ?>

                  <tr>
                    <td><?php echo $row['ID'];?></td>
                    <td><?php echo $row['category'];?></td>
                    <td><?php echo $row['type'];?></td>
                    <td>
                      <button type="button"
                      class="btn btn-sm"
                      data-bs-toggle="modal"
                      data-bs-target="#EditCategory"
                      data-id="<?php echo $row['ID'];?>"
                      data-category="<?php echo $row['category'];?>"
                      data-type="<?php echo $row['type'];?>">
                      <i class="bi bi-pencil"></i></button>
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
   <!-- Add Category Modal -->
    <div class="modal fade" id="AddCategory" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <form id="addCategoryForm" method="post">
            <div class="modal-header">
            <h5 class="modal-title">Add Category for Menu Item</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-2">
                <div class="form-floating">
                    <input type="number" name="ID" class="form-control" id="CategoryID" placeholder="ID" disabled>
                    <label for="CategoryID">ID</label>
                </div>
                </div>
                <div class="col-md-10">
                <div class="form-floating">
                    <select class="form-select" name="type" id="type" aria-label="type" required>
                    <option selected value="Permanent">Permanent</option>
                    <option value="Seasonal">Seasonal</option>
                    </select>
                    <label for="type">Type</label>
                </div>
                </div>
                <div class="col-md-12">
                <div class="form-floating">
                    <input type="text" name="category" class="form-control" id="category" placeholder="Category (Example: Bar Needs)" required>
                    <label for="category">Category (Example: Bar Needs)</label>
                </div>
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
  <!-- End of Add Category Modal -->

  <!-- Edit Category Modal -->
  <div class="modal fade" id="EditCategory" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Category for Menu Item</h5>
          <button type="button"  class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method = "post" id="editCategoryForm" class="row g-3">
            <input type="hidden" id="edit_id" name="edit_id">
            <div class="col-md-4">
              <div class="form-floating">
                <select class="form-select" name="edit_type" id="edit_type" aria-label="edit_type">
                  <option selected value="Permanent">Permanent</option>
                  <option value="Seasonal">Seasonal</option>
                </select>
                <label>Type</label>
              </div>
            </div>
            <div class="col-md-8">
                <div class="form-floating">
                    <input type="text" name="edit_category" class="form-control" id="edit_category" placeholder="Category (Example: Bar Needs)" required>
                    <label for="edit_category">Category (Example: Bar Needs)</label>
                </div>
            </div>
        </div>
        <div class="modal-footer justify-content-between">
        <button type="button" name="delete_save" id="delete_save" class="btn btn-danger"><i class="bi bi-bookmark-plus"></i> Archive</button>
          <div>
            <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-save" name="update_save">Update</button>
          </div>
        </div>
      </form>
      </div>
    </div>
  </div>
  <!-- End of Edit Category Modal -->

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

  <script>
  // Pass row data to the modal
  $('#EditCategory').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget); // Button that triggered the modal
      var id = button.data('id');
      var category = button.data('category');
      var type = button.data('type');

      var modal = $(this);
      modal.find('#edit_id').val(id);
      modal.find('#edit_category').val(category);
      modal.find('#edit_type').val(type);
  });

  document.getElementById('delete_save').addEventListener('click', function () {
        if (confirm('Are you sure you want to archive this data?')) {
            // Create a hidden input element for the delete_save action
            var deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'delete_save';
            deleteInput.value = '1';

            // Append the input to the form and submit the form
            var form = document.getElementById('editCategoryForm');
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