<?php
require "../../conn/conn.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Select all distinct categories from db_menu
$sql = 'SELECT DISTINCT category FROM db_menu';
$result = mysqli_query($conn, $sql);

if (!$result) {
    die('Error in query: ' . mysqli_error($conn));
}

$items_by_category = array();
while ($row = mysqli_fetch_assoc($result)) {
    $category = $row['category'];
    $category_id = str_replace(' ', '_', $category);

    // Fetch items for the current category
    $sql2 = 'SELECT * FROM db_menu WHERE category = "' . mysqli_real_escape_string($conn, $category) . '" ORDER BY item ASC';
    $result2 = mysqli_query($conn, $sql2);
    
    if (!$result2) {
        die('Error in query: ' . mysqli_error($conn));
    }
    
    $items = mysqli_fetch_all($result2, MYSQLI_ASSOC);

    // Store items under the category name (not encoded)
    $items_by_category[$category_id] = $items;
}

$sql3 = "SELECT ID, type, percent FROM db_discounts";
$result3 = mysqli_query($conn, $sql3);

if (!$result3) {
    die('Error in query: ' . mysqli_error($conn));
}

$discount_types = array();
while ($row3 = mysqli_fetch_assoc($result3)) {
    $discount_types[] = $row3;
}

date_default_timezone_set('Asia/Manila');

if (isset($_POST['create-order'])) {
    $categories = $_POST['categories'];
    $items = $_POST['items']; // Array of items
    $itemsSold = $_POST['itemsSold'];
    $prices = $_POST['prices']; // Array of prices
    $discount_percent = $_POST['percent']; // Discount percentage
    $cost_of_goods = $_POST['cost_of_goods'];
    $id_number = isset($_POST['id_number']) && !empty($_POST['id_number']) ? $_POST['id_number'] : "Not Applicable";
    $current_date_time = date('Y-m-d H:i:s');

    foreach ($items as $index => $item) {
        $category = mysqli_real_escape_string($conn, $categories[$index]);
        $price = mysqli_real_escape_string($conn, $prices[$index]);
        $cost = mysqli_real_escape_string($conn, $cost_of_goods[$index]);
        $itemsSoldFinal = mysqli_real_escape_string($conn, $itemsSold[$index]);
        $new_price = $price * ($discount_percent / 100);
        $final_price = $price - $new_price;
        $cashier_name = $_SESSION['full_name'];
        
        // Calculate gross profit
        $gross_profit = $final_price - $cost;

        // Insert into sales table
        $sqlInsertSales = "INSERT INTO order_statuses (category, item, items_sold, discounts, net_sales, cost_of_goods, gross_profit, id_number, date_time, cashier_name, order_status) 
                            VALUES ('$category', '$item', $itemsSoldFinal, '$discount_percent', $final_price, $cost, $gross_profit, '$id_number', '$current_date_time', '$cashier_name', 'Pending')";
        $insertResult = mysqli_query($conn, $sqlInsertSales);

        if (!$insertResult) {
            die('Error in insert query: ' . mysqli_error($conn));
        }
    }

    $actionStatus = 'added';
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Detour Cafe - POS</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="../../assets/title-logo.png" rel="icon">
    <link href="../../assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500,600,600i,700,700i" rel="stylesheet">

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

    <section class="section">
    <div class="row">
        <!-- Tab Navigation -->
        <div class="col-12 mb-4">
            <div class="menutabs-wrapper">
                <div class="menutabs nav nav-tabs nav-tabs-bordered" id="myTab" role="tablist">
                    <?php foreach ($items_by_category as $category_id => $items) { ?>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link <?php echo reset($items_by_category) === $items ? 'active' : ''; ?>" id="tab-<?php echo htmlspecialchars($category_id); ?>" data-bs-toggle="tab" href="#category-<?php echo htmlspecialchars($category_id); ?>" role="tab" aria-controls="category-<?php echo htmlspecialchars($category_id); ?>" aria-selected="<?php echo reset($items_by_category) === $items ? 'true' : 'false'; ?>">
                                <?php echo str_replace('_', ' ', htmlspecialchars($category_id)); ?>
                            </a>
                        </li>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- Items Section -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title" id="items-header" style="padding-bottom:0;">Items</h5> <hr style="padding-bottom:15px;">
                    <div class="tab-content">
                        <?php foreach ($items_by_category as $category_id => $items) { ?>
                            <div id="category-<?php echo htmlspecialchars($category_id); ?>" class="tab-pane fade <?php echo reset($items_by_category) === $items ? 'show active' : ''; ?>" >
                                <div class="row">
                                    <?php foreach ($items as $index => $item) { ?>
                                        <div class="col-md-3 mb-3">
                                            <button type="button" class="btn btn-secondary btn-item btn-block" 
                                                    style="background-image: url('<?php echo htmlspecialchars($item['image_path']); ?>'); background-size: cover; background-position: center; color: white;" 
                                                    onclick="addItem('<?php echo htmlspecialchars($item['item']); ?>', '<?php echo htmlspecialchars($item['price']); ?>', '<?php echo htmlspecialchars($item['cost_of_goods']); ?>', '<?php echo htmlspecialchars(str_replace('_', ' ', $category_id)); ?>')">
                                                <span><?php echo htmlspecialchars($item['item']); ?> - ₱<?php echo htmlspecialchars($item['price']);?></span>
                                                <div style="display: none;">{<?php echo htmlspecialchars($item['cost_of_goods']); ?>}</div>
                                            </button>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checkout Section -->
        <div class="col-lg-4">
            <div class="card sticky-checkout">
                <div class="card-body">
                    <h5 class="card-title" style="padding-bottom:0;">DC Maligaya Branch
                        <nav>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item active">Date and Time: <?php echo date('m-d-Y'); ?></li>
                                <li class="breadcrumb-item active" id="current-date-time"></li>
                            </ol>
                        </nav>
                        <hr>
                    </h5>
                    <div id="order-list" class="scrollable-content-checkout">
                        <!-- Ordered items will be appended here -->
                    </div>
                    <hr>
                    <p id="subtotal" class="subtotal-discounts">Subtotal: ₱0.00</p>
                    <h6 id="total">Total: ₱0.00</h6>
                    <button type="button" id="charge" class="btn btn-secondary btn-checkout col-md-12"><i class="bi bi-credit-card"></i> ‎ Charge</button>
                </div>
            </div>
        </div>

        <!-- Charge Section -->
        <div class="col-lg-12 hidden" id="charge-section">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title" style="padding-bottom:0;">Charge
                        <hr>
                    </h5>
                    <div class="row align-items-center">
                        <div class="col-md-3 amount-due">
                            <h1 id="due">₱ 0.00</h1>
                            <p class="subtotal-discounts">Amount Due</p>
                        </div>
                        <div class="col-md-3 change">
                            <h1 id="change">₱ 0.00</h1>
                            <p class="subtotal-discounts">Change</p>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" name="payment" step="0.1" min="0" class="form-control" id="payment" placeholder="Cash Received">
                                <label for="payment">Cash Received</label>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <button type="button" id="btn-checkout" class="btn btn-secondary btn-checkout col-md-12"><i class="bi bi-cart"></i> ‎ Checkout</button>
                </div>
            </div>
        </div>
    </div>
</section>


        <!-- Add an iframe to load the PHP file -->
    <iframe id="printFrame" style="display:none;"></iframe>
    
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

    <!-- Checkoout Modal -->
    <div class="modal fade" id="CheckoutModal" tabindex="-1" aria-labelledby="AddDiscountsLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable custom-modal-width">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Checkout</h5>
                    <button type="reset" id="close-button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="resetForm()"></button>
                </div>
                <form method="post" id="checkout-form">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="modal-dialog-scrollable" style="max-height: calc(100vh - 450px); overflow-y: auto;">
                                <div id="dynamic-fields-container"></div>
                            </div>
                            <hr>
                            <div class="modal-dialog-scrollable" style="max-height: calc(100vh - 425px); overflow-y: auto;">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <div class="form-floating">
                                            <select name="type" class="form-select" id="type" required>
                                                <option value="0" data-percent="0">None</option>
                                                <?php foreach ($discount_types as $discount_type) { ?>
                                                    <option value="<?php echo $discount_type['ID']; ?>" data-percent="<?php echo $discount_type['percent']; ?>">
                                                        <?php echo $discount_type['type']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            <label for="type">Discount Type</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <input type="number" name="percent" class="form-control" id="percent" placeholder="Percentage" required readonly>
                                            <label for="percent">Percentage</label>
                                        </div>
                                    </div>
                                    <div id="additional-fields-container" class="col-md-12"></div>
                                </div>
                                <div class="col-md-12">
                                    <p id="modal-subtotal" class="subtotal-discounts">Subtotal: ₱0.00</p>
                                    <p id="modal-discounts" class="subtotal-discounts">Discounts: ₱0.00</p>
                                    <h6 id="modal-total">Total: ₱0.00</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="reset" id="reset-form" class="btn btn-secondary" data-bs-dismiss="modal" onclick="resetForm()">Close</button>
                        <button type="button" id="print-receipt" name="print-receipt" class="btn btn-save" onclick="printAndManage()"><i class="bi bi-printer"></i> ‎ Receipt</button>
                        <button type="submit" id="create-order" name="create-order" class="btn btn-save"><i class="bi bi-cart"></i> Create Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add this inside your existing HTML structure where you want to display the printable area -->
    <div id="printable-area" style="display:none;">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title" style="padding-bottom:0;">Detour Cafe Maligaya Branch
                    <img src="../../assets/title-logo.png" style="height:40px;" class="float-end">
                        <nav>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item active">Date and Time: <span id="print-date-time"><?php echo date('m-d-Y H:i:s'); ?></span></li>
                            </ol>
                        </nav>
                        <hr>
                    </h5>
                    <div id="print-order-list">
                        <!-- Order details will be dynamically added here -->
                    </div>
                    <hr>
                    <p class="subtotal-discounts" style="margin:0 !important;">Cashier Name: <?php echo $_SESSION['full_name'];?></p>
                    <p id="print-due" class="subtotal-discounts" style="margin:0 !important;">Cash: ₱0.00</p>
                    <p id="print-change" class="subtotal-discounts" style="margin:0 !important;">Change: ₱0.00</p>
                    <p id="print-subtotal" class="subtotal-discounts" style="margin:0 !important;">Subtotal: ₱0.00</p>
                    <p id="print-discounts" class="subtotal-discounts" style="margin:0 !important;">Discounts: ₱0.00</p>
                    <h6 id="print-total">Total: ₱0.00</h6>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Checkout Modal -->

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

         function showItems(category) {
            // Hide all items first
            $('.items').hide();
            // Show items for the selected category
            $('#' + category).show();
        }

        function addItem(item, price, cost_of_goods, category) {
            // Check if the item already exists in the order list
            let exists = false;
            $('#order-list p').each(function() {
                if ($(this).text().includes(item)) {
                    exists = true;
                    return false; // Break out of the loop
                }
            });

            // If the item doesn't exist, add it to the order list
            if (!exists) {
                $('#order-list').append('<p><button type="button" class="btn btn-danger btn-delete float-end"><i class="bi bi-x-circle"></i></button> ' + item + ' - ₱' + price + ' <small>[' + category + ']</small><small style="visibility: hidden;">{'+ cost_of_goods + '}</small></p>');

                // Calculate subtotal
                updateSubtotal(parseFloat(price));
            }
        }

        // Function to update the subtotal
        function updateSubtotal(price) {
            var currentSubtotal = parseFloat($('#subtotal').text().replace('Subtotal: ₱', ''));

            // Calculate new subtotal
            var newSubtotal = currentSubtotal + price;

            // Update subtotal in the DOM
            $('#subtotal').text('Subtotal: ₱' + newSubtotal.toFixed(2));

            // Update total (assuming no discounts for now, so total equals subtotal)
            $('#total').text('Total: ₱' + newSubtotal.toFixed(2));
            $('#due').text('₱' + newSubtotal.toFixed(2));
        }

        // Example of delete button event listener (already implemented in your code)
        $(document).ready(function() {
            $('#order-list').on('click', '.btn-delete', function() {
                // Extract price from item text
                var itemText = $(this).parent().text().trim();
                var price = parseFloat(itemText.split('₱')[1]);
                var cost_of_goods = parseFloat(itemText.split('{')[1]);

                // Remove the parent <p> element
                $(this).closest('p').remove();

                // Update subtotal after removing item
                updateSubtotal(-price); // Subtract price from subtotal
            });
        });

        function updateCurrentTime() {
            var currentTime = new Date();
            var hours = currentTime.getHours();
            var minutes = currentTime.getMinutes();
            var seconds = currentTime.getSeconds();

            // Add leading zeros if necessary
            hours = (hours < 10 ? "0" : "") + hours;
            minutes = (minutes < 10 ? "0" : "") + minutes;
            seconds = (seconds < 10 ? "0" : "") + seconds;

            // Format the time as desired (you can change this format)
            var formattedTime = hours + ":" + minutes + ":" + seconds;

            // Update the time in the DOM
            document.getElementById('current-date-time').textContent = formattedTime;
        }

        // Update time every second
        setInterval(updateCurrentTime, 1000);

        // Initial call to display time immediately
        updateCurrentTime();   

        //MODAL SCRIPTS-----------------------------------
        $(document).ready(function() {
        // Function to update percentage based on selected discount type
        document.getElementById('type').addEventListener('change', function() {
            var selectedOption = this.options[this.selectedIndex];
            var percent = selectedOption.getAttribute('data-percent');
            document.getElementById('percent').value = percent;
            updateModalTotals(true); // Apply discount when type changes

            // Check if the selected value is for "Senior" or "PWD"
            // Get the currently selected <option> element
            var selectedOption = this.options[this.selectedIndex];

            // Extract the text content (the visible text) of the selected option
            var selectedValue = selectedOption.textContent.trim();
            console.log('Selected value:', selectedValue); // Log selected value
            if (selectedValue === "Senior" || selectedValue === "PWD") {
                addIdField();
            } else {
                removeIdField();
            }
        });

    // Function to add ID field
    function addIdField() {
        // Check if the ID field already exists
        if ($('#id-field-container').length === 0) {
            var idFieldHtml = `
                <div id="id-field-container" class="mb-3">
                    <div class="form-floating">
                        <input type="text" name="id_number" id="id_number" class="form-control" placeholder="ID Number" required>
                        <label for="id_number">ID Number</label>
                    </div>
                </div>
            `;
            $('#additional-fields-container').append(idFieldHtml);
        }
    }

    // Function to remove ID field
    function removeIdField() {
        $('#id-field-container').remove();
        var idFieldHtml = `
            <input type="hidden" name="id_number" value="Not Applicable">
            `;
        $('#additional-fields-container').append(idFieldHtml);
    }

    // Add event listener for modal hide
    $('#CheckoutModal').on('hide.bs.modal', function () {
        removeIdField();
    });

    // Update subtotal and total when the modal is shown
    $('#CheckoutModal').on('show.bs.modal', function(event) {
        // Clear previous dynamic fields
        $('#dynamic-fields-container').empty();
        $('#print-order-list').empty();

        // Set default discount to "None"
        $('#type').val('0');
        $('#percent').val('0');

        // Iterate over each item in the order list and create corresponding form fields
        $('#order-list p').each(function() {
            var itemText = $(this).text().trim();
            var itemName = itemText.split(' - ')[0];
            var itemPrice = parseFloat(itemText.split('₱')[1].split(' ')[0]);
            var itemCategory = itemText.split('[')[1].split(']')[0];
            var itemCostOfGoods = itemText.split('{')[1].split('}')[0];

            var dynamicFieldHtml = `
                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <input type="hidden" name="cost_of_goods[]" value="${itemCostOfGoods}">
                        <div class="form-floating">
                            <input type="text" name="items[]" class="form-control" value="${itemName}" required readonly>
                            <label>Item</label>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-floating">
                            <input type="number" name="itemsSold[]" class="form-control" placeholder="Quantity" min="1" value="1">
                            <label>Quantity</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating">
                            <input type="text" name="categories[]" class="form-control" value="${itemCategory}" required readonly>
                            <label>Category</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating">
                            <input type="number" name="prices[]" class="form-control" value="${itemPrice}" required readonly>
                            <label>Price</label>
                        </div>
                    </div>
                </div>
            `;

            // Append dynamic field to the container
            $('#dynamic-fields-container').append(dynamicFieldHtml);
            $('#print-order-list').append(dynamicFieldHtml);
        });

        // Update modal content with current subtotal and total
        updateModalTotals(false);

        // Add event listener for quantity changes
        $('#dynamic-fields-container').on('input', 'input[name="itemsSold[]"]', function() {
            updateModalTotals(true); // Re-apply discount when quantity changes
        });
    });

    // Function to update subtotal and total in the modal
    function updateModalTotals(applyDiscount) {
        var newSubtotal = 0;
        $('#dynamic-fields-container .row').each(function() {
            var quantity = parseInt($(this).find('input[name="itemsSold[]"]').val());
            var price = parseFloat($(this).find('input[name="prices[]"]').val());
            newSubtotal += quantity * price;
        });

        var selectedPercent = applyDiscount ? parseFloat($('#percent').val()) : 0;
        var discountAmount = newSubtotal * (selectedPercent / 100);
        var newTotal = newSubtotal - discountAmount;

        $('#modal-subtotal').text('Subtotal: ₱' + newSubtotal.toFixed(2));
        $('#modal-discounts').text('Discounts: - ₱' + discountAmount.toFixed(2));
        $('#modal-total').text('Total: ₱' + newTotal.toFixed(2));

        // Update the print area with the same values
        $('#print-subtotal').text('Subtotal: ₱' + newSubtotal.toFixed(2));
        $('#print-discounts').text('Discounts: - ₱' + discountAmount.toFixed(2));
        $('#print-total').text('Total: ₱' + newTotal.toFixed(2));

        // Clear the print order list
        $('#print-order-list').empty();

        // Create and append the table header once
        var tableHeader = `
            <table class="table table-striped table-borderless">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Category</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody id="print-order-body">
                </tbody>
            </table>
        `;

        $('#print-order-list').append(tableHeader);

        // Append rows to the table body
        $('#dynamic-fields-container .row').each(function() {
            var itemName = $(this).find('input[name="items[]"]').val();
            var itemPrice = parseFloat($(this).find('input[name="prices[]"]').val()).toFixed(2);
            var itemQuantity = parseInt($(this).find('input[name="itemsSold[]"]').val());
            var itemCategory = $(this).find('input[name="categories[]"]').val();

            var printRowHtml = `
                <tr>
                    <td>${itemName}</td>
                    <td>${itemQuantity}</td>
                    <td>${itemCategory}</td>
                    <td>${itemPrice}</td>
                </tr>
            `;

            $('#print-order-body').append(printRowHtml);
        });
    }
});

        function printAndManage() {
            var contentToPrint = document.getElementById('printable-area').innerHTML;
            var printFrame = document.getElementById('printFrame').contentWindow;

            // Create a document in the iframe
            printFrame.document.open();
            printFrame.document.write('<html><head><title>Print</title>');
            printFrame.document.write('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">'); // Add your stylesheets here
            printFrame.document.write('<link href="../../assets/css/style.css" rel="stylesheet">');
            printFrame.document.write('</head><body>');
            printFrame.document.write(contentToPrint);
            printFrame.document.write('</body></html>');
            printFrame.document.close();

            // Wait for the iframe to load and then print
            printFrame.onload = function() {
                printFrame.print();
            }
        }

        document.getElementById('print-receipt').addEventListener('click', printSpecificContent);

        function resetForm() {
            // Reset form fields
            document.getElementById("checkout-form").reset();

            // Update subtotal, discounts, and total to zero
            $('#modal-subtotal').text('Subtotal: ₱0.00');
            $('#modal-discounts').text('Discounts: ₱0.00');
            $('#modal-total').text('Total: ₱0.00');
            $('#print-subtotal').text('Subtotal: ₱0.00');
            $('#print-discounts').text('Discounts: ₱0.00');
            $('#print-total').text('Total: ₱0.00');
        }

        function resetFormAndCloseModal() {
            resetForm(); // Call your existing resetForm function

            // Close the modal
            var myModal = new bootstrap.Modal(document.getElementById('CheckoutModal'));
            myModal.hide();
        }


        function showItems(categoryId, categoryName) {
            // Hide all item categories
            document.querySelectorAll('.items').forEach(function(itemDiv) {
                itemDiv.style.display = 'none';
            });

            // Show the selected category's items
            var categoryDiv = document.getElementById(categoryId);
            if (categoryDiv) {
                categoryDiv.style.display = 'block';
            }

            // Update the items header text with the selected category
            var header = document.getElementById('items-header');
            if (header) {
                header.innerHTML = categoryName + ' <hr>';
            }
        }
    </script>

<script>
    document.getElementById('charge').addEventListener('click', function() {
        var chargeSection = document.getElementById('charge-section');
        if (chargeSection.classList.contains('hidden')) {
            chargeSection.classList.remove('hidden');

            // Scroll to the bottom of the page
            window.scrollTo({
                top: document.body.scrollHeight,
                behavior: 'smooth'
            });
        } else {
            chargeSection.classList.add('hidden');

            // Scroll to the top of the page
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    });

    document.getElementById('payment').addEventListener('input', function() {
        var payment = parseFloat(this.value) || 0;
        var due = parseFloat(document.getElementById('due').innerText.replace('₱', '').trim()) || 0;

        // Calculate change, ensuring it's not negative
        var change = Math.max(payment - due, 0);

        // Update displays
        document.getElementById('change').innerText = '₱ ' + change.toFixed(2);
        document.getElementById('print-change').innerText = 'Change: ₱ ' + change.toFixed(2);
        document.getElementById('print-due').innerText = 'Cash: ₱ ' + payment.toFixed(2);
    });

    // Example of how to update the due amount. This could be part of another function or event.
    function updateSubtotal(price) {
        var currentSubtotal = parseFloat(document.getElementById('subtotal').innerText.replace('Subtotal: ₱', '').trim()) || 0;

        // Calculate new subtotal
        var newSubtotal = currentSubtotal + price;

        // Update subtotal and total in the DOM
        document.getElementById('subtotal').innerText = 'Subtotal: ₱' + newSubtotal.toFixed(2);
        document.getElementById('total').innerText = 'Total: ₱' + newSubtotal.toFixed(2);
        document.getElementById('due').innerText = '₱' + newSubtotal.toFixed(2);
        // Ensure payment value is updated in #print-due
        var payment = parseFloat(document.getElementById('payment').value) || 0;
        document.getElementById('print-due').innerText = 'Cash: ₱ ' + payment.toFixed(2);
    }

    // Open modal only if change is zero and payment equals due amount
    document.getElementById('btn-checkout').addEventListener('click', function() {
        var change = parseFloat(document.getElementById('change').innerText.replace('₱', '').trim()) || 0;
        var payment = parseFloat(document.getElementById('payment').value) || 0;
        var due = parseFloat(document.getElementById('due').innerText.replace('₱', '').trim()) || 0;

        if (change === 0 && payment === due) {
            // Open the modal if change is zero and payment equals due amount
            var modal = new bootstrap.Modal(document.getElementById('CheckoutModal'));
            modal.show();
        } else if (change > 0) {
            // Open the modal if change is zero and payment equals due amount
            var modal = new bootstrap.Modal(document.getElementById('CheckoutModal'));
            modal.show();
        } else {
            alert('Please ensure the payment covers the due amount.');
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
                alertMessage = "Order has successfully been submitted.";
                alertIcon = "bi bi-basket";
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
