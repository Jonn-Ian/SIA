<?php
require "../../conn/conn.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$actionStatus = '';

//to sanitize the inputs
function addForm($type, $percent, $validity) {
    return preg_match('/^[a-zA-Z0-9\s]+$/', $type) &&
           preg_match('/^[a-zA-Z0-9\s]+$/', $percent) &&
           preg_match('/^[a-zA-Z0-9\s]+$/', $validity);
}

if (isset($_POST["save"])) {
    $type = trim($_POST["type"]);
    $percent =trim($_POST["percent"]);
    $validity = trim($_POST["validity"]);


   // Validate inputs
    if (addForm($type, $percent, $validity)) {
        // Prepare SQL query
        $query = "INSERT INTO db_discounts (type, percent, validity) VALUES (?, ?, ?)";

        // Prepare and bind statement
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param('sss', $type, $percent, $validity);

            // Execute the statement
            if ($stmt->execute()) {
                $actionStatus = 'added';
            } 
            else {
                $actionStatus = 'error';
            }
        }
    }

}

//to sanitize the inputs
function editForm($edit_type, $edit_percent, $edit_validity) {
    return preg_match('/^[a-zA-Z0-9\s]+$/', $edit_type) &&
           preg_match('/^[a-zA-Z0-9\s]+$/', $edit_validity) &&
           preg_match('/^[a-zA-Z0-9\s]+$/', $edit_percent);
}

if (isset($_POST["update_save"])) {
    $id = $_POST["edit_id"];
    $edit_type = trim($_POST["edit_type"]);
    $edit_percent = trim($_POST["edit_percent"]);
    $edit_validity = trim($_POST["edit_validity"]);

    // Validate inputs
    if (editForm($edit_type, $edit_percent, $edit_validity)) {
        // Prepare SQL statement
        $query = "UPDATE db_discounts SET type = ?, percent = ?, validity = ? WHERE ID = ?";

        // Prepare the statement
        if ($stmt = $conn->prepare($query)) {
            // Bind the parameters
            $stmt->bind_param('sssi', $edit_type, $edit_percent, $edit_validity, $id);

            // Execute the statement
            if ($stmt->execute()) {
                $actionStatus = 'updated';
            } else {
                $actionStatus = 'error';
            }

            // Close the statement
            $stmt->close();
        } else {
            $actionStatus = 'error';
        }
    } else {
        // Handle invalid input
        $actionStatus = 'invalid_input';
    }


    // if (mysqli_query($conn, "UPDATE db_discounts SET type = '$edit_type', percent = '$edit_percent', validity = '$edit_validity' WHERE ID = '$id'")) {
    //     $actionStatus = 'updated';
    // } else {
    //     $actionStatus = 'error';
    // }
}

if (isset($_POST["delete_save"])) {
    $id = $_POST["edit_id"];
    $edit_type = trim($_POST["edit_type"]);
    $edit_percent = trim($_POST["edit_percent"]);
    $edit_validity = trim($_POST["edit_validity"]);
    $current_date_time = date('Y-m-d H:i:s');

    // Delete the record
    $delete_query = "DELETE FROM db_discounts WHERE ID = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Insert into archived_discounts
        $insert_query = "INSERT INTO archived_discounts (type, percent, validity, archive_date) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssss", $edit_type, $edit_percent, $edit_validity, $current_date_time);

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

$sql = 'SELECT * FROM db_discounts';
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Detour Cafe - Discounts</title>
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

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>

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
    <h1>Discounts</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item active">Merchant <i class="bi bi-tag"></i></li>
        </ol>
    </nav>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-12">

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Available Discounts
                    <?php if (isset($_SESSION['position']) && $_SESSION['position'] == "Admin") { ?>
                        <button type="button" class="btn btn-sm float-end" data-bs-toggle="modal" data-bs-target="#AddDiscounts">
                            <i class="ri ri-add-circle-line"></i> Add Discounts
                        </button>
                    <?php }?>
                    </h5>
                    <!-- Table with stripped rows -->
                    <table class="table datatable table-hover table-borderless">
                        <thead>
                        <tr>
                            <th><b>ID</b></th>
                            <th>Type</th>
                            <th>Percent</th>
                            <th>Validity</th>
                            <?php if (isset($_SESSION['position']) && $_SESSION['position'] == "Admin") { ?>
                            <th>Action</th>
                            <?php }?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                            <tr>
                                <td><?php echo $row['ID']; ?></td>
                                <td><?php echo $row['type']; ?></td>
                                <td><?php echo $row['percent'] . "%"; ?></td>
                                <td><?php echo $row['validity']; ?></td>
                                <?php if (isset($_SESSION['position']) && $_SESSION['position'] == "Admin") { ?>
                                <td>
                                    <!-- Pass row data to modal -->
                                    <button type="button" class="btn btn-sm btn-edit"
                                            data-bs-toggle="modal"
                                            data-bs-target="#EditDiscounts"
                                            data-id="<?php echo $row['ID']; ?>"
                                            data-type="<?php echo $row['type']; ?>"
                                            data-percent="<?php echo $row['percent']; ?>"
                                            data-validity="<?php echo $row['validity']; ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </td>
                                <?php }?>
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

<!-- ======= Footer ======= -->
<footer id="footer" class="footer">
        <div class="copyright">
            &copy; Copyright <strong><span>Detour Cafe</span></strong>. All Rights Reserved 2024
        </div>
        <div class="credits">
        </div>
    </footer><!-- End Footer -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

<!-- Modals -->
<!-- Add Discounts Modal -->
<div class="modal fade" id="AddDiscounts" tabindex="-1" aria-labelledby="AddDiscountsLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Add Discount</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            <form method="post" id="discounts" class="row g-3">
                <div class="col-md-2">
                    <div class="form-floating">
                        <input type="number" name="ID" class="form-control" id="DiscountID" placeholder="ID" disabled>
                        <label for="DiscountID">ID</label>
                    </div>
                </div>

                <div class="col-md-10">
                    <div class="form-floating">
                        <input type="text" name="type" class="form-control" id="type" placeholder="Discount Type (ex: Senior Citizen)" required>
                        <label for="type">Discount Type (ex: Senior Citizen)</label>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="form-floating">
                        <input type="number" name="percent" class="form-control" id="percent" placeholder="Percent (Ex: 20%)" max="100" min="0" required>
                        <label for="percent">Percent (ex: 20%)</label>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="col-md-12">
                        <div class="form-floating">
                            <input type="text" name="validity" class="form-control" id="validity" placeholder="Validity (Ex: Every Day)" required>
                            <label for="validity">Validity (ex: Every Day)</label>
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
<!-- End of Add Discounts Modal -->

<!-- Edit Discounts Modal -->
<div class="modal fade" id="EditDiscounts" tabindex="-1">
<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Edit Discount</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form id="editDiscountForm" class="row g-3" method="post">
                <input type="hidden" class="form-control" id="editDiscountID" name="edit_id">
                <div class="col-md-12">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="editDiscountType" name="edit_type" placeholder="Discount Type (ex: Senior Citizen)" required>
                        <label for="editDiscountType">Discount Type (ex: Senior Citizen)</label>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-floating">
                        <input type="number" class="form-control" id="editDiscountPercent" name="edit_percent" placeholder="Percent (ex: 20%)" max="100" required>
                        <label for="editDiscountPercent">Percent (ex: 20%)</label>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="editDiscountValidity" name="edit_validity" placeholder="Validity (ex: Every Day)" required>
                        <label for="editDiscountValidity">Validity (ex: Every Day)</label>
                    </div>
                </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" name="delete_save" id="delete_save" class="btn btn-danger"><i class="bi bi-bookmark-plus"></i> Archive</button>
        <div>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" name="update_save" class="btn btn-save" id="update_save">Update</button>
        </div>
        </form>
    </div>
</div>
</div>
<!-- End of Edit Discounts Modal -->

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
  $('#EditDiscounts').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget); // Button that triggered the modal
      var id = button.data('id');
      var type = button.data('type');
      var percent = button.data('percent');
      var validity = button.data('validity');

      var modal = $(this);
      modal.find('.modal-body #editDiscountID').val(id);
      modal.find('.modal-body #editDiscountType').val(type);
      modal.find('.modal-body #editDiscountPercent').val(percent);
      modal.find('.modal-body #editDiscountValidity').val(validity);
  });

  // Debugging: Check form submission
  $('#editDiscountForm').on('submit', function(e) {
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
            var form = document.getElementById('editDiscountForm');
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