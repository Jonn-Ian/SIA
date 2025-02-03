<?php 
require "../../conn/conn.php";
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

include "../token/token.php";

// Create
if (isset($_POST["save"])) {
  $category = htmlspecialchars(trim($_POST["category"]), ENT_QUOTES, 'UTF-8');
  $item = htmlspecialchars(trim($_POST["item"]), ENT_QUOTES, 'UTF-8');
  $price = htmlspecialchars(trim($_POST["price"]), ENT_QUOTES, 'UTF-8');
  $cost = htmlspecialchars(trim($_POST["cost_of_goods"]), ENT_QUOTES, 'UTF-8');

      // Handle image upload
      $imagePath = '';
      if (isset($_FILES['menuImage']) && $_FILES['menuImage']['error'] == UPLOAD_ERR_OK) {
          $imagePath = '../../assets/uploads/' . basename($_FILES['menuImage']['name']);
          move_uploaded_file($_FILES['menuImage']['tmp_name'], $imagePath);
      }

  // Insert main menu item into db_menu table
  $query = "INSERT INTO db_menu (category, item, price, cost_of_goods, image_path) VALUES ('$category', '$item', '$price', '$cost', '$imagePath')";
  if(mysqli_query($conn, $query)){
      $actionStatus = 'added';
    } else {
      $actionStatus = 'error';
    }

  // Check and process dynamically added ingredients/components
  if (!empty($_POST["unit"]) && !empty($_POST["count"]) && !empty($_POST["ingredient_category"]) && !empty($_POST["ingredient_name"])) {
      $units = htmlspecialchars(trim($_POST["unit"]), ENT_QUOTES, 'UTF-8');
      $counts = htmlspecialchars(trim($_POST["count"]), ENT_QUOTES, 'UTF-8');
      $ingredient_categories = htmlspecialchars(trim($_POST["ingredient_category"]), ENT_QUOTES, 'UTF-8');
      $ingredient_names = htmlspecialchars(trim($_POST["ingredient_name"]), ENT_QUOTES, 'UTF-8');

      // Loop through dynamically added fields
      for ($i = 0; $i < count($units); $i++) {
          $unit = mysqli_real_escape_string($conn, $units[$i]);
          $count = mysqli_real_escape_string($conn, $counts[$i]);
          $ingredient_category = mysqli_real_escape_string($conn, $ingredient_categories[$i]);
          $ingredient_name = mysqli_real_escape_string($conn, $ingredient_names[$i]);

          // Insert each ingredient/component into db_ingredients table
          $query = "INSERT INTO db_ingredients (ingredient_for, category, item, unit, count) VALUES ('$item', '$ingredient_category', '$ingredient_name', '$unit', '$count')";
          if($result = mysqli_query($conn, $query)){
            $actionStatus = 'ingredient-added';
          } else {
            $actionStatus = 'error'; // Output any SQL error for debugging
          }
      }
  }
}

function isValidInput($id, $orig_item, $categories, $item, $price, $cost) {
  // Regular expression to allow only letters, numbers, and spaces
  return preg_match('/^[a-zA-Z0-9\s]+$/', $id);
  return preg_match('/^[a-zA-Z0-9\s]+$/', $orig_item);
  return preg_match('/^[a-zA-Z0-9\s]+$/', $categories);
  return preg_match('/^[a-zA-Z0-9\s]+$/', $item);
  return preg_match('/^[a-zA-Z0-9\s]+$/', $price);
  return preg_match('/^[a-zA-Z0-9\s]+$/', $cost);
}

if (isset($_POST["update_save"])) {
  // Database connection (ensure $conn is properly defined)
  // ...

  // Sanitize input data
  $id = htmlspecialchars(trim($_POST["edit_id"]), ENT_QUOTES, 'UTF-8');
  $orig_item = htmlspecialchars(trim($_POST["orig_item"]), ENT_QUOTES, 'UTF-8');
  $category = htmlspecialchars(trim($_POST["edit_category"]), ENT_QUOTES, 'UTF-8');
  $item = htmlspecialchars(trim($_POST["edit_item"]), ENT_QUOTES, 'UTF-8');
  $price = htmlspecialchars(trim($_POST["edit_price"]), ENT_QUOTES, 'UTF-8');
  $cost = htmlspecialchars(trim($_POST["edit_cost"]), ENT_QUOTES, 'UTF-8');

  // Prepare SQL query to update menu item
  $query = "UPDATE db_menu SET category = '$category', item = '$item', price = '$price', cost_of_goods = '$cost' WHERE ID = '$id'";
  
  if(mysqli_query($conn, $query)){
      // Check if a new image was uploaded
      if (isset($_FILES['menuImageEdit']) && $_FILES['menuImageEdit']['error'] == UPLOAD_ERR_OK) {
          $targetDir = "../../assets/uploads/"; // Define your image folder path
          $fileName = basename($_FILES['menuImageEdit']['name']);
          $targetFilePath = $targetDir . $fileName;
          $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

          // Validate file type
          $allowedTypes = array('jfif', 'avif', 'jpeg', 'jpg', 'png', 'webp');
          if (in_array($imageFileType, $allowedTypes)) {
              // Delete the old image if it exists
              $query = "SELECT image_path FROM db_menu WHERE ID = '$id'";
              $result = mysqli_query($conn, $query);
              if ($row = mysqli_fetch_assoc($result)) {
                  $oldImagePath = $row['image_path'];
                  if (file_exists($oldImagePath)) {
                      unlink($oldImagePath);
                  }
              }

              // Move new image to the target directory
              if (move_uploaded_file($_FILES['menuImageEdit']['tmp_name'], $targetFilePath)) {
                  // Update database with new image path
                  $updateImageQuery = "UPDATE db_menu SET image_path = '$targetFilePath' WHERE ID = '$id'";
                  mysqli_query($conn, $updateImageQuery);
              } else {
                  echo "Failed to upload image.";
              }
          } else {
              echo "Invalid file type. Only JPG, PNG, and WEBP files are allowed.";
          }
      }

      $actionStatus = 'updated';
  } else {
      $actionStatus = 'error'; // Output any SQL error for debugging
  }

  $query = "UPDATE db_ingredients SET ingredient_for = '$item' WHERE ingredient_for = '$orig_item'";
  if(mysqli_query($conn, $query)){
    $actionStatus = 'ingredient-updated';
  } else {
    $actionStatus = 'error'; // Output any SQL error for debugging
  }

  // Check and process dynamically added ingredients/components
  if (!empty($_POST["edit_unit"]) && !empty($_POST["edit_count"]) && !empty($_POST["edit_ingredient_category"]) && !empty($_POST["edit_ingredient_name"])) {
      $units = htmlspecialchars(trim($_POST["edit_unit"]), ENT_QUOTES, 'UTF-8');
      $counts = htmlspecialchars(trim($_POST["edit_count"]), ENT_QUOTES, 'UTF-8');
      $ingredient_categories = htmlspecialchars(trim($_POST["edit_ingredient_category"]), ENT_QUOTES, 'UTF-8');
      $ingredient_names = htmlspecialchars(trim($_POST["edit_ingredient_name"]), ENT_QUOTES, 'UTF-8');

      // Loop through dynamically added fields
      for ($i = 0; $i < count($units); $i++) {
          $unit = mysqli_real_escape_string($conn, $units[$i]);
          $count = mysqli_real_escape_string($conn, $counts[$i]);
          $ingredient_category = mysqli_real_escape_string($conn, $ingredient_categories[$i]);
          $ingredient_name = mysqli_real_escape_string($conn, $ingredient_names[$i]);

          // Insert each ingredient/component into db_ingredients table
          $query = "INSERT INTO db_ingredients (ingredient_for, category, item, unit, count) VALUES ('$item', '$ingredient_category', '$ingredient_name', '$unit', '$count')";
          if(mysqli_query($conn, $query)){
            $actionStatus = 'ingredient-added';
          } else {
            $actionStatus = 'error'; // Output any SQL error for debugging
          }
      }
  }
}

if (isset($_POST["delete_save"])) {
  $id = $_POST["edit_id"];
  $edit_category = trim($_POST["edit_category"]);
  $edit_item = trim($_POST["edit_item"]);
  $edit_price = trim($_POST["edit_price"]);
  $edit_cost = trim($_POST["edit_cost"]);
  $current_date_time = date('Y-m-d H:i:s');

  // Fetch the current image path from the database
  $fetch_image_query = "SELECT image_path FROM db_menu WHERE ID = ?";
  $stmt = $conn->prepare($fetch_image_query);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->bind_result($image_path);
  $stmt->fetch();
  $stmt->close();

  // Prepare for deletion and archiving
  $archived_dir = "../../assets/archived/";
  $file_name = basename($image_path);
  $archived_file_path = $archived_dir . $file_name;

  // Delete the record from the main menu table
  $delete_query = "DELETE FROM db_menu WHERE ID = ?";
  $stmt = $conn->prepare($delete_query);
  $stmt->bind_param("i", $id);

  if ($stmt->execute()) {
      // Move the image to the archived folder if it exists
      if (!empty($image_path) && file_exists($image_path)) {
          if (!file_exists($archived_dir)) {
              mkdir($archived_dir, 0755, true); // Create directory if it doesn't exist
          }

          // Move the file
          if (rename($image_path, $archived_file_path)) {
              // Set the relative path for the archived record
              $archived_image_path = str_replace('assets/', 'assets/', $archived_file_path);

              // Insert the record into the archived_menu table
              $insert_query = "INSERT INTO archived_menu (category, item, price, cost_of_goods, image_path, archive_date) VALUES (?, ?, ?, ?, ?, ?)";
              $stmt = $conn->prepare($insert_query);
              $stmt->bind_param("ssssss", $edit_category, $edit_item, $edit_price, $edit_cost, $archived_image_path, $current_date_time);

              if ($stmt->execute()) {
                  $actionStatus = 'deleted';
              } else {
                  $actionStatus = 'error';
              }
          } else {
              $actionStatus = 'error';
          }
      } else {
          // If no image to move, insert with empty image_path
          $archived_image_path = '';
          $insert_query = "INSERT INTO archived_menu (category, item, price, cost_of_goods, image_path, archive_date) VALUES (?, ?, ?, ?, ?, ?)";
          $stmt = $conn->prepare($insert_query);
          $stmt->bind_param("ssssss", $edit_category, $edit_item, $edit_price, $edit_cost, $archived_image_path, $current_date_time);

          if ($stmt->execute()) {
              $actionStatus = 'deleted';
          } else {
              $actionStatus = 'error';
          }
      }
  } else {
      $actionStatus = 'error';
  }

  $stmt->close();
}


// Select all data from db_menu
$sql = 'SELECT * FROM db_menu';
$result = mysqli_query($conn, $sql);

$sql2 ='SELECT * FROM db_ingredients';
$db_ingredients = mysqli_query($conn, $sql2);

// Fetch categories
$sql3 = 'SELECT DISTINCT category FROM db_inventory';
$category_result = mysqli_query($conn, $sql3);
$categories = [];
while ($row = mysqli_fetch_assoc($category_result)) {
    $categories[] = $row['category'];
}

// Fetch ingredients
$sql4 = 'SELECT * FROM db_inventory';
$ingredients_result = mysqli_query($conn, $sql4);
$ingredients = [];
while ($row = mysqli_fetch_assoc($ingredients_result)) {
    $ingredients[] = $row;
}

// Fetch categories from the database
$categories_query = "SELECT category FROM db_menu_category";
$categories_result = mysqli_query($conn, $categories_query);

// Initialize an empty array to store categories
$categories = [];

// Loop through results and store categories in the array
while ($row = mysqli_fetch_assoc($categories_result)) {
    $categories[] = $row['category'];
}

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

// Fetch distinct categories from the database
$categoryQuery = "SELECT DISTINCT category FROM db_menu";
$categoryResult = mysqli_query($conn, $categoryQuery);

$categories = [];
while ($row = mysqli_fetch_assoc($categoryResult)) {
    $categories[] = $row['category'];
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Detour Cafe - Menu</title>
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
      <h1>Menu
      <a class="btn btn-menu me-2 float-end" href="../ingredients/ingredients.php">
              <i class="bi bi-egg"></i> Ingredients
            </a>
            <a class="btn btn-menu me-2 float-end" href="../categories-menu/categories-menu.php">
              <i class="bi bi-card-list"></i> Categories
            </a>
            <button type="button" class="btn btn-menu me-2 float-end" data-bs-toggle="modal" data-bs-target="#AddMenu">
              <i class="ri ri-add-circle-line"></i> Add Menu Item
            </button>
      </h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item active">Management <i class="bi bi-journal-text"></i></li>
        </ol>
      </nav>
      <div>

      </div>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row">
        <!-- Tab Navigation -->
        <div class="col-12 mb-4">
          <div class="menutabs-wrapper">
            <div class="menutabs nav nav-tabs nav-tabs-bordered" id="myTab" role="tablist">
              <?php foreach ($categories as $index => $category) { ?>
                <li class="nav-item" role="presentation">
                  <a class="nav-link <?php echo $index === 0 ? 'active' : ''; ?>" id="tab-<?php echo $index; ?>" data-bs-toggle="tab" href="#category-<?php echo $index; ?>" role="tab" aria-controls="category-<?php echo $index; ?>" aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>">
                    <?php echo $category; ?>
                  </a>
                </li>
              <?php } ?>
            </div>
          </div>
        </div>

        <!-- Search Bar -->
        <div class="col-12 mb-4">
          <div class="input-with-icon">
            <i class="bi bi-search"></i>
            <input type="text" id="search-bar" class="form-control search" placeholder="Search Menu Items...">
          </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content" id="myTabContent">
          <?php foreach ($categories as $index => $category) { ?>
            <div class="tab-pane fade <?php echo $index === 0 ? 'show active' : ''; ?>" id="category-<?php echo $index; ?>" role="tabpanel" aria-labelledby="tab-<?php echo $index; ?>">
              <div class="row" id="menu-items-<?php echo $index; ?>">
                <?php
                // Fetch menu items for the current category
                $menuQuery = "SELECT * FROM db_menu WHERE category = '$category' ORDER BY item";
                $menuResult = mysqli_query($conn, $menuQuery);

                while ($row = mysqli_fetch_assoc($menuResult)) {
                ?>
                  <div class="col-md-3 mb-4 menu-item">
                    <div class="card card-menu">
                      <img src="<?php echo $row['image_path'];?>" class="card-img-top" alt="Item Image">
                      <div class="card-body card-body-menu">
                        <h5 class="card-title"><?php echo $row['item'];?></h5>
                        <p class="card-text card-text-menu">
                          <strong>Price:</strong> ₱<?php echo $row['price'];?><br>
                          <strong>Cost of Goods:</strong> ₱<?php echo $row['cost_of_goods'];?>
                        </p>
                        <div class="d-flex justify-content-between">
                          <button type="button" class="btn btn-edit-menu"
                            data-bs-toggle="modal"
                            data-bs-target="#EditMenu"
                            data-id="<?php echo $row['ID'];?>"
                            data-category="<?php echo $row['category'];?>"
                            data-item="<?php echo $row['item'];?>"
                            data-price="<?php echo $row['price'];?>"
                            data-cost="<?php echo $row['cost_of_goods'];?>"
                            data-img="<?php echo $row['image_path'];?>">
                            <i class="bi bi-pencil"></i> Edit
                          </button>
                          <!-- Optional: Add an additional button if needed -->
                        </div>
                      </div>
                    </div>
                  </div>
                <?php
                }
                ?>
              </div>
            </div>
          <?php } ?>
        </div>
      </div>
    </section>
  </main>

  <!-- Modals -->
  <!-- Add Menu Modal -->
  <div class="modal fade" id="AddMenu" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="addMenuForm" method="post" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">Add Menu Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div style="max-height: calc(100vh - 200px); overflow-y: auto;">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-2">
              <div class="form-floating">
                <input type="number" name="ID" class="form-control" id="MenuID" placeholder="ID" disabled>
                <label for="MenuID">ID</label>
              </div>
            </div>

            <div class="col-md-6">
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

            <div class="col-md-4">
              <div class="form-floating">
                <input type="number" name="price" class="form-control" id="price" placeholder="Price (ex: 200)" min="0" required>
                <label for="price">Price (ex: 200)</label>
              </div>
            </div>

            <div class="col-md-7">
              <div class="form-floating">
                <input type="text" name="item" class="form-control" id="item" placeholder="Item Name (ex: Spanish Latte)" required>
                <label for="item">Item Name (ex: Spanish Latte)</label>
              </div>
            </div>

            <div class="col-md-5">
              <div class="form-floating">
                <input type="number" name="cost_of_goods" class="form-control" id="cost_of_goods" placeholder="Cost (ex: 200)" min="0" required>
                <label for="cost_of_goods">Cost (ex: 200)</label>
              </div>
            </div>
              <div class="col-md-12 d-flex align-items-center">
                <h6 class="me-3 mb-0">Import Image:</h6>
                <div class="flex-grow-1">
                  <!-- Custom button for file input -->
                  <label id="menuImageLabel" class="btn btn-save w-100" for="menuImage">
                    <i class="bi bi-file-image me-2"></i>Choose File
                  </label>
                  <input type="file" name="menuImage" id="menuImage" class="d-none" accept=".jfif, .avif, .jpeg, .jpg, .png, .webp">
                </div>
              </div>
          </div>
        </div>
            <div class="col-md-12">
              <div class="modal-header">
                <h5 class="modal-title">Ingredients / Components</h5>
              </div>
              <div class="modal-body modal-dialog-scrollable">
                <div id="ingredient-container">
                  <!-- This container will dynamically populate with ingredients/components -->
                </div>
                <button type="button" class="btn btn-save col-md-12" name="save-button" style="width: 100%;" id="add-ingredient-button">
                  <i class="ri ri-add-circle-line"></i> Add Ingredient / Component
                </button>
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
  <!-- End of Add Menu Modal -->

  <!-- Edit Menu Modal -->
  <div class="modal fade" id="EditMenu" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <form method="post" name="editMenuForm" id="editMenuForm" enctype="multipart/form-data">
          <div class="modal-header">
            <h5 class="modal-title">Edit Menu Item</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div style="max-height: calc(100vh - 200px); overflow-y: auto;">
          <div class="modal-body">
            <!-- Image container -->
            <div class="col-md-12 text-center mb-3">
              <div class="img-container">
                <img src="" alt="Menu Image" id="edit_img">
              </div>
            </div>
            <input type="hidden" name="edit_id" class="form-control" id="edit_id" placeholder="ID">
            <input type="hidden" name="orig_item" class="form-control" id="orig_item" placeholder="orig_item">
            <div class="row g-3">
              <div class="col-md-8">
                <div class="form-floating">
                  <select class="form-select" name="edit_category" id="edit_category" aria-label="Category" required>
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

              <div class="col-md-4">
                <div class="form-floating">
                  <input type="number" name="edit_price" class="form-control" id="edit_price" placeholder="Price (ex: 200)" min="0" required>
                  <label for="price">Price (ex: 200)</label>
                </div>
              </div>

              <div class="col-md-7">
                <div class="form-floating">
                  <input type="text" name="edit_item" class="form-control" id="edit_item" placeholder="Item Name (ex: Spanish Latte)" required>
                  <label for="item">Item Name (ex: Spanish Latte)</label>
                </div>
              </div>

              <div class="col-md-5">
                <div class="form-floating">
                  <input type="number" name="edit_cost" class="form-control" id="edit_cost" placeholder="Cost (ex: 200)" min="0" required>
                  <label for="edit_cost">Cost (ex: 200)</label>
                </div>
              </div>
              <div class="col-md-12 d-flex align-items-center">
                <h6 class="me-3 mb-0">Import New Image:</h6>
                <div class="flex-grow-1">
                  <!-- Custom button for file input -->
                  <label id="menuImageEditLabel" class="btn btn-save w-100" for="menuImageEdit">
                    <i class="bi bi-file-image me-2"></i>Choose File
                  </label>
                  <input type="file" name="menuImageEdit" id="menuImageEdit" class="d-none" accept=".jfif, .avif, .jpeg, .jpg, .png, .webp">
                </div>
              </div>
            </div>
          </div>

              <div class="col-md-12">
                <div class="modal-header">
                  <h5 class="modal-title">Ingredients / Components</h5>
                </div>
                <div class="modal-body modal-dialog-scrollable" >
                  <div id="edit-ingredient-container">
                    <!-- This container will dynamically populate with ingredients/components -->
                  </div>
                  <button type="button" class="btn btn-save col-md-12" name="save-button" style="width: 100%;" id="edit-ingredient-button">
                    <i class="ri ri-add-circle-line"></i> Add Ingredient / Component
                  </button>
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
  <!-- End of Edit Menu Modal -->

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
        $(document).ready(function () {
            // Event listener for adding ingredients
            document.getElementById('add-ingredient-button').addEventListener('click', function () {
                var container = document.getElementById('ingredient-container');
                var div = document.createElement('div');
                div.classList.add('row', 'g-3', 'mb-3');
                div.innerHTML = `
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger col-md-12 delete-ingredient-button"
                            style="width: 100%; height: 100%; font-size: 25px;">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <select class="form-select" name="unit[]" id="unit" aria-label="unit">
                                <option selected value="Case">Case</option>
                                <option value="Pack / Inner">Pack / Inner</option>
                                <option value="KG">KG</option>
                                <option value="PCS">PCS</option>
                                <option value="GMS">GMS</option>
                            </select>
                            <label>Unit</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="col-md-12">
                            <div class="form-floating">
                                <input type="number" name="count[]" class="form-control" id="count" placeholder="Count" min="0"
                                    required>
                                <label>Count</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-floating">
                            <select class="form-select" name="ingredient_category[]" id="ingredient_category" aria-label="edit_category">
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
                    <div class="col-md-7">
                        <div class="form-floating">
                              <select class="form-select" name="ingredient_name[]" id="ingredient_name" aria-label="Ingredient / Component" required>
                                  <!-- Options will be dynamically populated by JavaScript -->
                              </select>
                            <label>Ingredient / Component</label>
                        </div>
                    </div>
                `;
                container.appendChild(div);

                // Add event listener to the delete button
                div.querySelector('.delete-ingredient-button').addEventListener('click', function () {
                    div.remove();
                });
                // Populate categories and items
                var categories = <?php echo json_encode($categories_with_items); ?>; // Assuming you fetch this array from PHP
                var categorySelect = div.querySelector('[name="ingredient_category[]"]');
                var itemSelect = div.querySelector('[name="ingredient_name[]"]');

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
        });

        $(document).ready(function () {
            // Event listener for adding ingredients
            document.getElementById('edit-ingredient-button').addEventListener('click', function () {
                var container = document.getElementById('edit-ingredient-container');
                var div = document.createElement('div');
                div.classList.add('row', 'g-3', 'mb-3');
                div.innerHTML = `
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger col-md-12 delete-edit-ingredient-button"
                            style="width: 100%; height: 100%; font-size: 25px;">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <select class="form-select" name="edit_unit[]" aria-label="unit">
                                <option selected value="Case">Case</option>
                                <option value="Pack / Inner">Pack / Inner</option>
                                <option value="KG">KG</option>
                                <option value="PCS">PCS</option>
                                <option value="GMS">GMS</option>
                            </select>
                            <label>Unit</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="col-md-12">
                            <div class="form-floating">
                                <input type="number" name="edit_count[]" class="form-control" placeholder="Count" min="0"
                                    required>
                                <label>Count</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-floating">
                            <select class="form-select" name="edit_ingredient_category[]" aria-label="edit_category">
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
                    <div class="col-md-7">
                        <div class="form-floating">
                              <select class="form-select" name="edit_ingredient_name[]" id="edit_ingredient_name" aria-label="Ingredient / Component" required>
                                  <!-- Options will be dynamically populated by JavaScript -->
                              </select>
                            <label>Ingredient / Component</label>
                        </div>
                    </div>
                `;
                container.appendChild(div);

                // Add event listener to the delete button
                div.querySelector('.delete-edit-ingredient-button').addEventListener('click', function () {
                    div.remove();
                });
                var categories = <?php echo json_encode($categories_with_items); ?>; // Assuming you fetch this array from PHP
                var categorySelect = div.querySelector('[name="edit_ingredient_category[]"]');
                var itemSelect = div.querySelector('[name="edit_ingredient_name[]"]');

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
        });

    $('#EditMenu').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget); // Button that triggered the modal
      var id = button.data('id');
      var category = button.data('category');
      var item = button.data('item');
      var price = button.data('price');
      var cost = button.data('cost');
      var img = button.data('img');

      var modal = $(this);
      modal.find('#edit_id').val(id);
      modal.find('#edit_category').val(category);
      modal.find('#orig_item').val(item);
      modal.find('#edit_item').val(item);
      modal.find('#edit_price').val(price);
      modal.find('#edit_cost').val(cost);
      modal.find('#edit_img').attr('src', img);
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
            var form = document.getElementById('editMenuForm');
            form.appendChild(deleteInput);
            form.submit();
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('menuImage');
    const fileLabel = document.getElementById('menuImageLabel');

    fileInput.addEventListener('change', function () {
      if (fileInput.files.length > 0) {
        const fileName = fileInput.files[0].name;
        fileLabel.innerHTML = `<i class="bi bi-file-image me-2"></i>${fileName}`;
      } else {
        fileLabel.innerHTML = `<i class="bi bi-file-image me-2"></i>Choose File`;
      }
    });
  });

  document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('menuImageEdit');
    const fileLabel = document.getElementById('menuImageEditLabel');

    fileInput.addEventListener('change', function () {
      if (fileInput.files.length > 0) {
        const fileName = fileInput.files[0].name;
        fileLabel.innerHTML = `<i class="bi bi-file-image me-2"></i>${fileName}`;
      } else {
        fileLabel.innerHTML = `<i class="bi bi-file-image me-2"></i>Choose File`;
      }
    });
  });

  document.addEventListener('DOMContentLoaded', function() {
    const searchBar = document.getElementById('search-bar');
    
    searchBar.addEventListener('input', function() {
      const query = searchBar.value.toLowerCase();
      const menuItems = document.querySelectorAll('.menu-item');

      menuItems.forEach(function(item) {
        const itemName = item.querySelector('.card-title').textContent.toLowerCase();
        if (itemName.includes(query)) {
          item.style.display = '';
        } else {
          item.style.display = 'none';
        }
      });
    });
  });
  </script>

  <script>
      $(document).ready(function () {
      var actionStatus = "<?php echo $actionStatus; ?>";

      if (actionStatus) {
          var alertType = "";
          var alertMessage = "";
          var alertIcon = "";

          if (actionStatus === "added") {
              alertType = "alert-success";
              alertMessage = "Menu Item Data has been successfully added.";
              alertIcon = "bi bi-check-circle";
          } else if (actionStatus === "updated") {
              alertType = "alert-info";
              alertMessage = "Menu Item Data has been successfully updated.";
              alertIcon = "bi bi-pencil";
          } else if (actionStatus === "deleted") {
              alertType = "alert-secondary";
              alertMessage = "Menu Item Data has been successfully archived.";
              alertIcon = "bi bi-bookmark-plus";
          } else if (actionStatus === "error") {
              alertType = "alert-warning";
              alertMessage = "There was an error performing the operation.";
              alertIcon = "bi bi-exclamation-circle";
          } else if (actionStatus === "ingredient-added") {
              alertType = "alert-success";
              alertMessage = "Menu and Ingredient Data has been successfully added.";
              alertIcon = "bi bi-check-circle";
          } else if (actionStatus === "ingredient-updated") {
              alertType = "alert-info";
              alertMessage = "Menu and Ingredient Item Data has been successfully updated.";
              alertIcon = "bi bi-pencil";
          } else if (actionStatus === "ingredient-deleted") {
              alertType = "alert-secondary";
              alertMessage = "Menu and Ingredient Item Data has been successfully archived.";
              alertIcon = "bi bi-bookmark-plus";
          }

          var alertHtml = `
              <div class="alert ${alertType} alert-dismissible fade show" role="alert">
                  <i class="${alertIcon} me-1"></i>
                  ${alertMessage}
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
          `;

          // Ensure this container is not part of the dynamic fields container
          $('#alert-container').html(alertHtml);
          $('#alert-container').show();
      }
  });
  </script>

</body>

</html>