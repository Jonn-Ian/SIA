<?php
require "../../conn/conn.php";

if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

// Function to check if username exists
function isUsernameTaken($conn, $username, $excludeID = null) {
    $query = "SELECT COUNT(*) as count FROM db_login WHERE username = '$username'";
    if ($excludeID) {
        $query .= " AND ID != $excludeID";
    }
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['count'] > 0;
}

// Initialize variables to hold messages
$username_taken = false;
$message = '';

// Handle form submissions
if(isset($_POST["save"])){
    $full_name = htmlspecialchars(trim($_POST["full_name"]), ENT_QUOTES, 'UTF-8');
    $username = htmlspecialchars(trim($_POST["username"]), ENT_QUOTES, 'UTF-8');
    $passkey = htmlspecialchars(trim($_POST["passkey"]), ENT_QUOTES, 'UTF-8');
    $position = htmlspecialchars(trim($_POST["position"]), ENT_QUOTES, 'UTF-8');

    // Check if username is already taken
    if (isUsernameTaken($conn, $username)) {
        // Username is taken
        $username_taken = true;
        $message = "Username is already taken.";
    } else {
        // Username is available, proceed with INSERT
        if(mysqli_query($conn, "INSERT INTO db_login(full_name, username, password, position) VALUES('$full_name', '$username', '$passkey', '$position');")){
          $actionStatus = 'added';
        } else {
          $actionStatus = 'error';
        }
    }
}

if(isset($_POST["update_save"])){
  $ID = htmlspecialchars(trim($_POST["edit_id"]), ENT_QUOTES, 'UTF-8');
  $full_name = htmlspecialchars(trim($_POST["edit_fname"]), ENT_QUOTES, 'UTF-8');
  $username = htmlspecialchars(trim($_POST["edit_username"]), ENT_QUOTES, 'UTF-8');
  $password = htmlspecialchars(trim($_POST["edit_password"]), ENT_QUOTES, 'UTF-8');
  $position = htmlspecialchars(trim($_POST["edit_position"]), ENT_QUOTES, 'UTF-8');
  $orig_name = htmlspecialchars(trim($_POST["orig_name"]), ENT_QUOTES, 'UTF-8');

  // Check if username is already taken (excluding current user's ID)
  if (isUsernameTaken($conn, $username, $ID)) {
      // Username is taken
      $username_taken = true;
      $message = "Username is already taken.";
  } else {
      // Username is available, proceed with UPDATE
      // Update db_login
      if (mysqli_query($conn, "UPDATE db_login SET full_name = '$full_name', username = '$username', password = '$password', position = '$position' WHERE ID = $ID")) {
          // Update db_sales if the update was successful
          if (mysqli_query($conn, "UPDATE db_sales SET cashier_name = '$full_name' WHERE cashier_name = '$orig_name'")) {
              // Update session if the updated ID is the current user
              if ($_SESSION['ID'] == $ID) {
                  $_SESSION['full_name'] = $full_name;
                  $_SESSION['username'] = $username;
                  $_SESSION['position'] = $position;
                  $_SESSION['password'] = $password;
              }
              $actionStatus = 'updated';
          } else {
              $actionStatus = 'error';
          }
      } else {
          $actionStatus = 'error';
      }
  }
}


  if (isset($_POST["delete_save"])) {
    $id = htmlspecialchars(trim($_POST["edit_id"]), ENT_QUOTES, 'UTF-8');
    $full_name = htmlspecialchars(trim($_POST["edit_fname"]), ENT_QUOTES, 'UTF-8');
    $username = htmlspecialchars(trim($_POST["edit_username"]), ENT_QUOTES, 'UTF-8');
    $password = htmlspecialchars(trim($_POST["edit_password"]), ENT_QUOTES, 'UTF-8');
    $position = htmlspecialchars(trim($_POST["edit_position"]), ENT_QUOTES, 'UTF-8');
    $current_date_time = date('Y-m-d H:i:s');

    // Delete the record
    $delete_query = "DELETE FROM db_login WHERE ID = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Insert into archived_discounts
        $insert_query = "INSERT INTO archived_employees (full_name, username, password, position, archive_date) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("sssss", $full_name, $username, $password, $position, $current_date_time);

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

// Fetch all data for displaying in the table
$sql = 'SELECT * FROM db_login WHERE position = "Employee"';
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Detour Cafe - Users</title>
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
      <h1>Employees</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item active">Database <i class="bi bi-people"></i></li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
          <div class="col-lg-12">
  
            <div class="card">
              <div class="card-body">
                  <h5 class="card-title">List of Employees
                    <button type="button"  class="btn btn-sm float-end" data-bs-toggle="modal" data-bs-target="#AddUsers">
                      <i class="ri ri-add-circle-line"></i> Add Users
                    </button>
                  </h5>
                <!-- Table with stripped rows -->
                <table class="table datatable table-hover table-borderless">
                  <thead>
                    <tr>
                      <th>
                        <b>ID</b>
                      </th>
                      <th>Full Name</th>
                      <th>Username</th>
                      <!-- <th data-type="date" data-format="YYYY/DD/MM">Start Date</th> -->
                      <th>Password</th>
                      <th>Position</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                      while ($row = mysqli_fetch_assoc($result)){
                  ?>
                    <tr>
                    <td><?php echo $row['ID'];?></td>
                    <td><?php echo $row['full_name'];?></td>
                    <td><?php echo $row['username'];?></td>
                    <td><?php echo $row['password'];?></td>
                    <td><?php echo $row['position'];?></td>
                      <td><button type="button" class="btn btn-sm"
                      data-bs-toggle="modal"
                      data-bs-target="#EditUsers"
                      data-id="<?php echo $row['ID'];?>"
                      data-fname="<?php echo $row['full_name'];?>"
                      data-origname="<?php echo $row['full_name'];?>"
                      data-username="<?php echo $row['username'];?>"
                      data-passkey="<?php echo $row['password'];?>"
                      data-position="<?php echo $row['position'];?>">
                      <i class="bi bi-pencil"></i></button></td>
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

  <!-- Add User Modal -->
  <div class="modal fade" id="AddUsers" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Employees</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <form method = "post" id="addUserForm" class="row g-3">
            <div class="col-md-2">
              <div class="form-floating">
                <input type="number" name = "ID" class="form-control" id="ID" placeholder="ID" disabled>
                <label for="ID">ID</label>
              </div>
            </div>

            <div class="col-md-10">
              <div class="form-floating">
                <input type="text" name = "full_name" class="form-control" id="fname" placeholder="Full Name" maxlength = "30" required>
                <label for="fname">Full Name</label>
              </div>
            </div>

            <div class="col-md-12">
              <div class="form-floating">
                <input type="text" name = "username" class="form-control" id="username" placeholder="Username" minlength = "8" maxlength = "30" required>
                <label for="username">Username</label>
              </div>
            </div>

            <div class="col-md-8">
              <div class="form-floating">
                <input type="password" name = "passkey" class="form-control" id="passkey" placeholder="Password" minlength = "8" maxlength = "128" required>
                <label for="passkey">Password</label>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-floating">
                <select class="form-select" name = "position" id="position" aria-label="Position" required>
                  <option selected value="Admin">Admin</option>
                  <option value="Employee">Employee</option>
                </select>
                <label for="position">Position</label>
              </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="reset" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" name = "save" class="btn btn-save">Save</button>
        </div>
      </form>
      </div>
    </div>
  </div>
  <!-- End of Add User Modal -->

<!-- Edit User Modal -->
<div class="modal fade" id="EditUsers" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUsersForm" method="post" class="row g-3">
                    <input type="hidden" name="edit_id" class="form-control" id="edit_id">
                    <input type="hidden" name="orig_name" class="form-control" id="orig_name">
                    <div class="col-md-12">
                        <div class="form-floating">
                            <input type="text" name="edit_fname" class="form-control" id="edit_fname" placeholder="Full Name" required>
                            <label for="edit_fname">Full Name</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-floating">
                            <input type="text" name="edit_username" class="form-control" id="edit_username" placeholder="Username" required>
                            <label for="edit_username">Username</label>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-floating">
                            <input type="password" name="edit_password" class="form-control" id="edit_password" placeholder="Password" required>
                            <label for="edit_password">Password</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-floating">
                            <select class="form-select" name="edit_position" id="edit_position" aria-label="Position" required>
                                <option selected value="Admin">Admin</option>
                                <option value="Employee">Employee</option>
                            </select>
                            <label for="edit_position">Position</label>
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
  <!-- End of Edit User Modal -->

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
  // Handle showing data in modal
  $(document).ready(function() {
      $('#EditUsers').on('show.bs.modal', function(event) {
          var button = $(event.relatedTarget);
          var id = button.data('id');
          var origname = button.data('origname');
          var fname = button.data('fname');
          var username = button.data('username');
          var passkey = button.data('passkey');
          var position = button.data('position');

          var modal = $(this);
          modal.find('#edit_id').val(id);
          modal.find('#orig_name').val(origname);
          modal.find('#edit_fname').val(fname);
          modal.find('#edit_username').val(username);
          modal.find('#edit_password').val(passkey);
          modal.find('#edit_position').val(position);
      });
  });

  // Debugging: Check form submission
  $('#editUsersForm').on('submit', function(e) {
      console.log("Form submitted");
      var formData = $(this).serializeArray();
      formData.forEach(function(item) {
          console.log(item.name + ": " + item.value);
      });
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
            var form = document.getElementById('editUsersForm');
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