<?php
require "../../conn/conn.php";

if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

$sql = 'SELECT * FROM db_history ORDER BY log_date DESC, log_time DESC';
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Detour Cafe - History</title>
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

    <div class="pagetitle"> 
    <h1>History</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item active">Settings <i class="bi bi-clock-history"></i></li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
          <div class="col-lg-12">
  
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">Logs
                <button type="button" class="btn btn-print float-end" id="printBtn-inventory" data-bs-toggle="modal" data-bs-target="#dateRangeModal">
                  <i class="bi bi-printer"></i> ‎ Print Logs
                </button>
                </h5>
                <!-- Table with stripped rows -->
                <table class="table datatable table-hover table-borderless">
                  <thead>
                    <tr>
                      <th>
                        <b>ID</b>
                      </th>
                      <th>Username</th>
                      <th>Full Name</th>
                      <th>Date</th>
                      <th>Time</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                    while ($row = mysqli_fetch_assoc($result)) {
                      ?>
                      <tr>
                        <td>
                          <?php echo $row['ID']; ?></td>
                          <td><?php echo $row['username']; ?></td>
                          <td><?php echo $row['full_name']; ?></td>
                          <td><?php echo $row['log_date']; ?></td>
                          <td><?php echo $row['log_time']; ?></td>
                          <td><?php echo $row['action']; ?></td>
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

    <!-- Add an iframe to load the PHP file -->
    <iframe id="printFrame" style="display:none;"></iframe>

  </main><!-- End #main -->

  <!--MODAL FOR DATE RANGE-->
  <div class="modal fade" id="dateRangeModal" tabindex="-1" aria-labelledby="dateRangeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Date Range</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form id="dateRangeForm">
              <div class="mb-3">
                <label for="startDate" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="startDate" name="startDate" required>
              </div>
              <div class="mb-3">
                <label for="endDate" class="form-label">End Date</label>
                <input type="date" class="form-control" id="endDate" name="endDate" required>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-save" onclick="printHistoryWithDateRange()"><i class="bi bi-printer"></i> ‎ Print Logs</button>
            </div>
            </form>
        </div>
    </div>
  </div>

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
     function printHistoryWithDateRange() {
        var startDate = document.getElementById('startDate').value;
        var endDate = document.getElementById('endDate').value;
        
        // Check if both startDate and endDate are filled out
        if (startDate && endDate) {
            // Construct the URL to pass date range to print_sales.php
            var file = 'print_history.php?startDate=' + encodeURIComponent(startDate) + '&endDate=' + encodeURIComponent(endDate);
            
            // Call the print function with constructed URL
            printAndManage(file);
        } else {
            // Show an alert or handle the validation as per your UI/UX design
            alert('Please select both start and end dates.');
        }
    }

    function printAndManage(file) {
        var iframe = document.getElementById('printFrame');

        // Load the PHP file into the iframe
        iframe.src = file;

        // Wait for the iframe to load the content
        iframe.onload = function() {
            iframe.contentWindow.print(); // Print the content of the iframe
        };
    }
  </script>

</body>

</html>