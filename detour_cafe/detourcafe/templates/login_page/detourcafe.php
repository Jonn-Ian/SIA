<?php
session_start();
require "../../conn/conn.php"; // Adjust the path to your database connection file

// Check if the user is already logged in
if (isset($_SESSION['username'])){
  if (isset($_SESSION['position']) && $_SESSION['position'] == "Admin") {
    header("Location: ../dashboard/dashboard.php"); // Redirect to POS page
    }
    if (isset($_SESSION['position']) && $_SESSION['position'] == "Employee") {
      header("Location: ../POS/POS.php"); // Redirect to POS page
    }
  exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Debug: Check if connection to the database is successful
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Query to check if the username exists
    $sql = "SELECT ID, username, full_name, password, position FROM db_login WHERE username='$username'";
    $result = mysqli_query($conn, $sql);

    // Debug: Check if the query was successful
    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        // Verify the password
        if ($password == $user['password']) {
            $_SESSION['ID'] = $user['ID'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['position'] = $user['position'];
            $_SESSION['password'] = $user['password'];

            // Log login event
            $log_date = date('Y-m-d');
            $log_time = date('H:i:s');
            $action = 'LOGIN';
            $log_query = "INSERT INTO db_history (username, full_name, log_date, log_time, action) VALUES ('{$user['username']}', '{$user['full_name']}', '$log_date', '$log_time', '$action')";
            mysqli_query($conn, $log_query);
            if (isset($_SESSION['position']) && $_SESSION['position'] == "Admin") {
            header("Location: ../dashboard/dashboard.php"); // Redirect to POS page
            }
            if (isset($_SESSION['position']) && $_SESSION['position'] == "Employee") {
              header("Location: ../POS/POS.php"); // Redirect to POS page
            }
            exit();
        } else {
            $error = "Invalid username or password";
            error_log("Password verification failed for user: " . $username);
        }
    } else {
        $error = "Invalid username or password";
        error_log("No user found with username: " . $username);
    }
}
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/font-awesome.min.css" rel="stylesheet">
    <link href="../../assets/css/style-login.css" rel="stylesheet">
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <title>Detour Cafe - Login</title>
    <link rel="icon" href="../../assets/title-logo.png">
    
  </head>
  <body>
    <section class="form-01-main">
      <div class="form-cover">
        <div class="container">
          <div class="row">
            <div class="col-md-12">
              <form action="" method="post">
                <div class="form-sub-main">
                  <a href="#">
                    <img src="../../assets/login-logo.png">
                  </a>
                  <div class="col-md-12">
                    <input type="text" name="username" class="form-control" id="username" placeholder="Username" required>
                  </div>
                  <div class="col-md-12">
                    <input type="password" name="password" class="form-control" id="passkey" placeholder="Password" required>
                  </div>
                  <div class="form-group">
                    <div class="btn_uy">
                      <button type="submit" name="login" class="btn">Login</button>
                    </div>
                    <?php if (isset($error)) { echo '<br><div class="error">'.$error.'</div>'; } ?>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
  </body>
</html>

<script>
    if ( window.history.replaceState ) {
            window.history.replaceState( null, null, window.location.href );
        }
</script>