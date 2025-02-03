<?php
require "../../conn/conn.php";

if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

  // Handle Update operation
  if (isset($_POST["update_save"])) {
    // Retrieve data from POST
    $id = $_POST["edit_id"];
    $category = $_POST["edit_category"];
    $item = $_POST["edit_item"];
    $unit = $_POST["edit_unit"];
    $count = $_POST["edit_count"];

    // SQL query for update
    $update_query = "UPDATE db_ingredients SET 
                    category = '$category', 
                    item = '$item', 
                    unit = '$unit', 
                    count = '$count'
                    WHERE ID = '$id'";

    // Execute update query
    if (mysqli_query($conn, $update_query)) {
        $actionStatus = 'updated';
    } else {
        $actionStatus = 'error';
    }
  }

  // Handle Delete operation
  if (isset($_POST["delete_save"])) {
    // Retrieve data from POST
    $id = $_POST["edit_id"];
    $delete_query = "DELETE FROM db_ingredients WHERE ID = '$id'";

    // Execute delete query
    if (mysqli_query($conn, $delete_query)) {
      $actionStatus = 'deleted';
  } else {
      $actionStatus = 'error';
  }
  }

  // Fetch all data from db_ingredients
  $sql = 'SELECT * FROM db_ingredients';
  $result = mysqli_query($conn, $sql);

  // Fetch categories from the database
  $categories_inventory = "SELECT category FROM db_inventory_category";
  $categories_result_inventory = mysqli_query($conn, $categories_inventory);

  // Initialize an empty array to store categories
  $categories_inventory = [];

  // Loop through results and store categories in the array
  while ($row = mysqli_fetch_assoc($categories_result_inventory)) {
    $categories_inventory[] = $row['category'];
  }

  // Fetch categories with items
  $categories_with_items = [];
  $categories_query = "SELECT category, item FROM db_inventory"; // Adjust your query according to your database structure
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
      <h1>Ingredients</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item active">Management / Menu <i class="bi bi-egg"></i></li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <div class="col-lg-12">

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Menu Item Ingredients
                <a href="../menu/menu.php" class="btn btn-sm float-end"><i class="bi bi-journal-text"></i> ‎ Cafe Menu</a>
              </h5>

              <!-- Table with stripped rows -->
              <table class="table datatable table-hover table-borderless">
                <thead>
                  <tr>
                    <th>
                      <b>ID</b>
                    </th>
                    <th>Menu Item</th>
                    <th>Category</th>
                    <th>Item Name</th>
                    <th>Unit</th>
                    <th>Count</th>
                    <th>Action</th>
                  </tr>
                </thead>

                <tbody>

                <?php
                      while ($row = mysqli_fetch_assoc($result)){
                  ?>

                  <tr>
                    <td><?php echo $row['ID'];?></td>
                    <td><?php echo $row['ingredient_for'];?></td>
                    <td><?php echo $row['category'];?></td>
                    <td><?php echo $row['item'];?></td>
                    <td><?php echo strtoupper($row['unit'])?></td>
                    <td><?php echo $row['count'];?></td>
                    <td>
                      <button type="button"
                      class="btn btn-sm"
                      data-bs-toggle="modal"
                      data-bs-target="#EditIngredient"
                      data-id="<?php echo $row['ID'];?>"
                      data-ingredientfor="<?php echo $row['ingredient_for'];?>"
                      data-category="<?php echo $row['category'];?>"
                      data-item="<?php echo $row['item'];?>"
                      data-unit="<?php echo $row['unit'];?>"
                      data-count="<?php echo $row['count'];?>">
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
  <!-- Edit Ingredient Modal -->
  <div class="modal fade" id="EditIngredient" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Ingredient for Menu Item</h5>
          <button type="button"  class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method = "post" id="editIngredientForm" class="row g-3">
          <input type="hidden" name="edit_id" class="form-control" id="edit_id" placeholder="ID">
            <div class="col-md-12">
              <div class="form-floating">
                <input type="text" class="form-control" name="edit_ingredientfor" id="edit_ingredientfor" placeholder="Menu Item" disabled>
                <label for="edit_ingredientfor">Menu Item</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-floating">
                <select class="form-select" name="edit_category" id="edit_category" aria-label="edit_category">
                  <?php
                    // Populate options from PHP array
                    foreach ($categories_inventory as $category) {
                    echo "<option value='$category'>$category</option>";
                    }
                  ?>  
                </select>
                <label>Category</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="col-md-12">
                <div class="form-floating">
                <select class="form-select" name="edit_item" id="edit_item" aria-label="Ingredient / Component" required>
                  <!-- Options will be dynamically populated by JavaScript -->
                </select>
                <label>Ingredient / Component</label>
                </div>
              </div>
            </div>
            <div class="col-md-8">
              <div class="form-floating">
                <select class="form-select" name="edit_unit" id="edit_unit" aria-label="edit_unit">
                  <option value="cases">Case</option>
                  <option value="pack">Pack / Inner</option>
                  <option value="kgs">KGS</option>
                  <option value="pcs">PCS</option>
                  <option value="gms">GMS</option>
                </select>
                <label>Unit</label>
              </div>
            </div>
            <div class="col-md-4">
              <div class="col-md-12">
                <div class="form-floating">
                  <input type="number" name = "edit_count" class="form-control" id="edit_count" placeholder="Count" required>
                  <label for="edit_count">Count</label>
                </div>
              </div>
            </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" name = "delete_save" id="delete_save" class="btn btn-danger"><i class="bi bi-trash"></i></button>
          <div>
            <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-save" name="update_save">Update</button>
          </div>
        </div>
      </form>
      </div>
    </div>
  </div>
  <!-- End of Edit Ingredient Modal -->

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
  $('#EditIngredient').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget); // Button that triggered the modal
      var id = button.data('id');
      var ingredientfor = button.data('ingredientfor');
      var category = button.data('category');
      var item = button.data('item');
      var unit = button.data('unit');
      var count = button.data('count');

      var modal = $(this);
      modal.find('#edit_id').val(id);
      modal.find('#edit_ingredientfor').val(ingredientfor);
      modal.find('#edit_category').val(category);
      modal.find('#edit_item').val(item);
      modal.find('#edit_unit').val(unit);
      modal.find('#edit_count').val(count);
  });

  // Debugging: Check form submission
  $('#editIngredientForm').on('submit', function(e) {
      console.log("Form submitted");
      var formData = $(this).serializeArray();
      formData.forEach(function(item) {
          console.log(item.name + ": " + item.value);
      });
  });

  document.addEventListener("DOMContentLoaded", function () {
      var categories = <?php echo json_encode($categories_with_items); ?>; // Assuming you fetch this array from PHP

      var categorySelect = document.getElementById('edit_category');
      var itemSelect = document.getElementById('edit_item');

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

  document.getElementById('delete_save').addEventListener('click', function () {
        if (confirm('Are you sure you want to delete this data?')) {
            // Create a hidden input element for the delete_save action
            var deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'delete_save';
            deleteInput.value = '1';

            // Append the input to the form and submit the form
            var form = document.getElementById('editIngredientForm');
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
                alertType = "alert-danger";
                alertMessage = "Data has been successfully deleted.";
                alertIcon = "bi bi-trash";
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