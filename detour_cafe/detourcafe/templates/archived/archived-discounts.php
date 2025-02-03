<?php
require "../../conn/conn.php";

if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

if (isset($_POST["delete_save"])) {
  $id = $_POST["edit_id"];
  $edit_type = trim($_POST["edit_type"]);
  $edit_percent = trim($_POST["edit_percent"]);
  $edit_validity = trim($_POST["edit_validity"]);

  // Delete the record
  $delete_query = "DELETE FROM archived_discounts WHERE ID = ?";
  $stmt = $conn->prepare($delete_query);
  $stmt->bind_param("i", $id);

  if ($stmt->execute()) {
      $insert_query = "INSERT INTO db_discounts (type, percent, validity) VALUES (?, ?, ?)";
      $stmt = $conn->prepare($insert_query);
      $stmt->bind_param("sss", $edit_type, $edit_percent, $edit_validity);

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

$sql = 'SELECT * FROM archived_discounts';
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
          <li class="breadcrumb-item active">Archived / Discounts <i class="bi bi-tag"></i></li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
          <div class="col-lg-12">
  
            <div class="card">
              <div class="card-body">
                  <h5 class="card-title">Discounts
                  </h5>
                  <!-- Table with stripped rows -->
                  <table class="table datatable table-hover table-borderless">
                          <thead>
                              <tr>
                                  <th><b>ID</b></th>
                                  <th>Type</th>
                                  <th>Percent</th>
                                  <th>Validity</th>
                                  <th>Archived Date/Time</th>
                                  <th>Action</th>
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
                                      <td><?php echo $row['archive_date']; ?></td>
                                      <td>
                                          <!-- Pass row data to modal -->
                                          <button type="button" class="btn btn-sm btn-edit"
                                                  data-bs-toggle="modal"
                                                  data-bs-target="#EditDiscounts"
                                                  data-id="<?php echo $row['ID']; ?>"
                                                  data-type="<?php echo $row['type']; ?>"
                                                  data-percent="<?php echo $row['percent']; ?>"
                                                  data-validity="<?php echo $row['validity']; ?>">
                                              <i class="bi bi-eye"></i>
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

  <!-- ======= Footer ======= -->
<footer id="footer" class="footer">
        <div class="copyright">
            &copy; Copyright <strong><span>Detour Cafe</span></strong>. All Rights Reserved 2024
        </div>
        <div class="credits">
        </div>
    </footer><!-- End Footer -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

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
                          <input type="text" class="form-control" id="editDiscountType" name="edit_type" placeholder="Discount Type (ex: Senior Citizen)" readonly>
                          <label for="editDiscountType">Discount Type (ex: Senior Citizen)</label>
                      </div>
                  </div>
                  <div class="col-md-5">
                      <div class="form-floating">
                          <input type="number" class="form-control" id="editDiscountPercent" name="edit_percent" placeholder="Percent (ex: 20%)" max="100" readonly>
                          <label for="editDiscountPercent">Percent (ex: 20%)</label>
                      </div>
                  </div>
                  <div class="col-md-7">
                      <div class="form-floating">
                          <input type="text" class="form-control" id="editDiscountValidity" name="edit_validity" placeholder="Validity (ex: Every Day)" readonly>
                          <label for="editDiscountValidity">Validity (ex: Every Day)</label>
                      </div>
                  </div>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="button" name="delete_save" id="delete_save" class="btn btn-save"><i class="bi bi-bookmark-x"></i> Unrchive</button>
          </form>
      </div>
  </div>
  </div>
  <!-- End of Edit Discounts Modal -->

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
        if (confirm('Are you sure you want to unarchive this data?')) {
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