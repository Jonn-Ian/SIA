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

if(isset($_POST["update_save"])){
    $ID = filter_var($_POST["edit_id"], FILTER_SANITIZE_NUMBER_INT);
    $full_name = htmlspecialchars(trim($_POST["edit_fname"]), ENT_QUOTES, 'UTF-8');
    $orig_name = htmlspecialchars(trim($_POST["orig_name"]), ENT_QUOTES, 'UTF-8');
    $username = htmlspecialchars(trim($_POST["edit_username"]), ENT_QUOTES, 'UTF-8');
    $password = htmlspecialchars(trim($_POST["edit_passkey"]), ENT_QUOTES, 'UTF-8');

    // Check if username is already taken (excluding current user's ID)
    if (isUsernameTaken($conn, $username, $ID)) {
        // Username is taken
        $username_taken = true;
        $message = "Username is already taken.";
    } else {
        // Username is available, proceed with UPDATE
        if (mysqli_query($conn, "UPDATE db_login SET full_name = '$full_name', username = '$username', password = '$password' WHERE ID = $ID")) {
            // Update db_sales if the update was successful
            if (mysqli_query($conn, "UPDATE db_sales SET cashier_name = '$full_name' WHERE cashier_name = '$orig_name'")) {
                // Update session if the updated ID is the current user
                if ($_SESSION['ID'] == $ID) {
                    $_SESSION['full_name'] = $full_name;
                    $_SESSION['username'] = $username;
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

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Detour Cafe - Profile</title>
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

<?php if (!empty($message)): ?>
    <script>alert("<?php echo $message; ?>");</script>
<?php endif; ?>

<?php require_once '../navbar/header.php';?>

  <main id="main" class="main">

  <div id="alert-container" class="container mt-3" style="display:none;">
  <!-- Alerts will be inserted here -->
  </div>

    <div class="pagetitle">
      <h1>My Profile</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item active">View / Edit <i class="bi bi-person"></i></li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Profile Details</h5>
                        <form class="row">
                            <div class="profile-row">
                                <div class="col-md-12">
                                    <div class="form-floating">
                                    <input type="text" class="form-control" id="fname" placeholder="Full Name" value="<?php if (isset($_SESSION['full_name'])){ ?><?php echo $_SESSION['full_name']; }?>" disabled>
                                    <label for="fname">Full Name</label>
                                    </div>
                                </div>
                            </div>
                            <div class="profile-row">
                                <div class="col-md-12">
                                    <div class="form-floating">
                                    <input type="text" class="form-control" id="username" placeholder="Username" value="<?php if (isset($_SESSION['username'])){ ?><?php echo $_SESSION['username']; }?>" disabled>
                                    <label for="username">Username</label>
                                    </div>
                                </div>
                            </div>
                            <div class="profile-row">
                                <div class="col-md-12">
                                    <div class="form-floating">
                                    <input type="password" class="form-control" id="passkey" value="<?php if (isset($_SESSION['password'])){ ?><?php echo $_SESSION['password']; }?>" placeholder="Password" disabled>
                                    <label for="passkey">Password</label>
                                    </div>
                                </div>
                            </div>
                    </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Edit Profile</h5>
                        <form class="row" method="post">
                                <div class="profile-row">
                                    <div class="col-md-12">
                                        <div class="form-floating">
                                        <input type="hidden" name="edit_id" value="<?php if (isset($_SESSION['ID'])){ ?><?php echo $_SESSION['ID']; }?>">
                                        <input type="hidden" name="orig_name" value="<?php if (isset($_SESSION['full_name'])){ ?><?php echo $_SESSION['full_name']; }?>">
                                        <input type="text" class="form-control" id="fname" name="edit_fname" placeholder="Full Name" maxlength = "50" required>
                                        <label for="fname">Full Name</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="profile-row">
                                    <div class="col-md-12">
                                        <div class="form-floating">
                                        <input type="text" class="form-control" id="username" name="edit_username" placeholder="Username" minlength = "8" maxlength = "30" required>
                                        <label for="username">Username</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="profile-row">
                                    <div class="col-md-12">
                                        <div class="form-floating">
                                        <input type="password" class="form-control" id="passkey" name="edit_passkey" placeholder="Password" minlength = "8" maxlength = "128" required>
                                        <label for="passkey">Password</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="profile-row">
                                    <div class="col-md-12">
                                        <button class="btn btn-savechanges" name="update_save"><i class="bi bi-pencil"></i> Save Changes</button>
                                    </div>
                                </div>
                        </form>
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
    document.addEventListener("DOMContentLoaded", function () {
        var actionStatus = "<?php echo $actionStatus; ?>";

        if (actionStatus) {
            var alertType = "";
            var alertMessage = "";
            var alertIcon = "";

            if (actionStatus === "updated") {
                alertType = "alert-info";
                alertMessage = "Data has been successfully updated.";
                alertIcon = "bi bi-pencil";
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