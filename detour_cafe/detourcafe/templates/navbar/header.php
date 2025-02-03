<?php 
@session_start();

if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

require "../../conn/conn.php";
require_once "../session/session.php";
check_login();

// Fetch notifications
$notifications = [];
$sql10 = "SELECT item, status, last_updated FROM db_inventory WHERE status IN ('Critical (Buy Now)', 'Out of Stock')";
$result10 = $conn->query($sql10);

if ($result10) {
    while ($row10 = $result10->fetch_assoc()) {
        $notifications[] = $row10;
    }
}
?>

<!-- ======= Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center">
<div class="d-flex align-items-center justify-content-between">
  <a href="../POS/POS.php" class="logo d-flex align-items-center">
    <img src="../../assets/title-logo.png" alt="">
    <span class="d-none d-lg-block">DETOUR CAFE</span>
  </a>
</div><!-- End Logo -->

<i class="bi bi-list toggle-sidebar-btn"></i>

<nav class="header-nav ms-auto">
  <ul class="d-flex align-items-center">

    <?php if (isset($_SESSION['username'])){ ?>
      <?php if (isset($_SESSION['position']) && $_SESSION['position'] == "Admin") { ?>
      <li class="nav-item dropdown">
        <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
          <i class="bi bi-bell"></i>
          <span class="badge badge-number"><?php echo count($notifications); ?></span>
        </a><!-- End Notification Icon -->
      <?php }?>
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications scrollable">
          <li class="dropdown-header">
            You have <span id="notification-count"><?php echo count($notifications); ?></span> Notifications
            <a href="../inventory/inventory.php"><span class="badge rounded-pill bg-view p-2 ms-2">View</span></a>
          </li>
          <li><hr class="dropdown-divider"></li>
          <div id="notification-items"></div>
        </ul><!-- End Notification Dropdown Items -->
      </li><!-- End Notification Nav -->


    <li class="nav-item dropdown pe-3">

    <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
        <span class="d-none d-md-block dropdown-toggle ps-2">
            Hello, <span class="username"><?php echo $_SESSION['username']; }?></span> !
        </span>
    </a><!-- End Profile Image Icon -->

      <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
        <li class="dropdown-header">
          <?php if (isset($_SESSION['full_name'])){ ?>
          <h6><?php echo $_SESSION['full_name']; }?></h6>
          <?php if (isset($_SESSION['position'])){ ?>
          <span><?php echo $_SESSION['position']; }?></span>
        </li>
        
        <?php if (isset($_SESSION['position']) && $_SESSION['position'] == "Admin") { ?>
        <li>
          <a class="dropdown-item-last d-flex align-items-center" href="../profile/profile.php">
            <i class="bi bi-person"></i>
              <span>My Profile</span>
          </a>
        </li>
        <?php } ?>

        <?php if (isset($_SESSION['position']) && $_SESSION['position'] == "Admin") { ?>
        <li>
          <a class="dropdown-item-last d-flex align-items-center" href="../users/users.php">
            <i class="bi bi-people"></i>
            <span>Employees</span>
          </a>
        </li>
        <?php } ?>

        <li>
          <a class="dropdown-item-last d-flex align-items-center" href="../login_page/logout.php">
            <i class="bi bi-box-arrow-right"></i>
            <span>Sign Out</span>
          </a>

        </li>
      </ul><!-- End Profile Dropdown Items -->
    </li><!-- End Profile Nav -->

  </ul>
</nav><!-- End Icons Navigation -->

</header><!-- End Header -->

<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar collapsed">

<ul class="sidebar-nav" id="sidebar-nav">

<?php if (isset($_SESSION['position']) && $_SESSION['position'] == "Admin") { ?>
<li class="nav-heading">Management</li>

  <li class="nav-item">
    <a class="nav-link collapsed" href="../dashboard/dashboard.php" id="sidebarDASHBOARD">
      <i class="bi bi-grid"></i>
      <span>Dashboard</span>
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link collapsed" href="../menu/menu.php" id="sidebarMENU">
      <i class="bi bi-journal-text"></i>
      <span>Menu</span>
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link collapsed" href="../sales/sales.php" id="sidebarSALES">
      <i class="bi bi-cash"></i>
      <span>Sales</span>
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link collapsed" href="../inventory/inventory.php" id="sidebarINVENTORY">
      <i class="bi bi-archive"></i>
      <span>Inventory</span>
    </a>
  </li>

  <?php } ?>

<li class="nav-heading">Merchant</li>

<li class="nav-item">
    <a class="nav-link collapsed" href="../POS/POS.php" id="sidebarPOS">
      <i class="bi bi-basket"></i>
      <span>Point of Sales</span>
    </a>
</li>

<li class="nav-item">
    <a class="nav-link collapsed" href="../orders/orders.php" id="sidebarORDERS">
      <i class="bi bi-bag-dash"></i>
      <span>Order Statuses</span>
    </a>
</li>

<li class="nav-item">
    <a class="nav-link collapsed" href="../discounts/discounts.php" id="sidebarDISCOUNTS">
        <i class="bi bi-tag"></i>
        <span>Discounts</span>
    </a>
</li>

<?php if (isset($_SESSION['position']) && $_SESSION['position'] == "Employee") { ?>

<li class="nav-item">
    <a class="nav-link collapsed" href="../sales/sales.php" id="sidebarSALES">
      <i class="bi bi-cash"></i>
      <span>Sales</span>
    </a>
</li>

<?php }?>

<?php if (isset($_SESSION['position']) && $_SESSION['position'] == "Admin") { ?>

  <li class="nav-heading">Settings</li>

  <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#tables-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-bookmark"></i><span>Archives</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="tables-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
          <li>
            <a class="nav-link collapsed" href="../archived/archived-discounts.php" id="archivedDISCOUNTS">
              <i class="bi bi-circle"></i><span>Discounts</span>
            </a>
          </li>
          <li>
            <a class="nav-link collapsed" href="../archived/archived-menu.php" id="archivedMENU">
              <i class="bi bi-circle"></i><span>Menu</span>
            </a>
          </li>
          <li>
            <a class="nav-link collapsed" href="../archived/archived-menu-categories.php"  id="archivedMENUCATEGORIES">
              <i class="bi bi-circle"></i><span>Menu Categories</span>
            </a>
          </li>
          <li>
            <a class="nav-link collapsed" href="../archived/archived-sales.php" id="archivedSALES">
              <i class="bi bi-circle"></i><span>Sales</span>
            </a>
          </li>
          <li>
            <a class="nav-link collapsed" href="../archived/archived-inventory.php" id="archivedINVENTORY">
              <i class="bi bi-circle"></i><span>Inventory</span>
            </a>
          </li>
          <li>
            <a class="nav-link collapsed" href="../archived/archived-inventory-categories.php" id="archivedINVENTORYCATEGORIES">
              <i class="bi bi-circle"></i><span>Inventory Categories</span>
            </a>
          </li>
          <li>
            <a class="nav-link collapsed" href="../archived/archived-employees.php" id="archivedEMPLOYEES">
              <i class="bi bi-circle"></i><span>Employees</span>
            </a>
          </li>
        </ul>
      </li>

  <li class="nav-item">
    <a class="nav-link collapsed" href="../print/print.php" id="sidebarPRINT">
      <i class="bi bi-printer"></i>
      <span>Print</span>
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link collapsed" href="../sql-backup/sql-backup.php" id="sidebarBACKUP">
    <i class="bi bi-cloud-download"></i>
      <span>Backup</span>
    </a>
  </li>

  <li class="nav-item">
    <a class="nav-link collapsed" href="../history/history.php" id="sidebarHISTORY">
      <i class="bi bi-clock-history"></i>
      <span>History</span>
    </a>
  </li>
  <?php } ?>

</ul>

</aside><!-- End Sidebar-->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    if ( window.history.replaceState ) {
            window.history.replaceState( null, null, window.location.href );
        }
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Define notifications array directly in JavaScript
    const notifications = <?php echo json_encode($notifications, JSON_PRETTY_PRINT); ?>;

    console.log('Notifications:', notifications); // Check if notifications data is loaded correctly

    const notificationCount = notifications.length;
    document.querySelector('.badge-number').innerText = notificationCount;
    document.getElementById('notification-count').innerText = notificationCount;

    const notificationItems = document.getElementById('notification-items');
    notificationItems.innerHTML = ''; // Clear existing notifications

    notifications.forEach((notification, index) => {
        const notificationElement = document.createElement('li');
        notificationElement.classList.add('notification-item');

        let iconClass = '';
        switch (notification.status) {
            case 'Critical (Buy Now)':
                iconClass = 'bi-exclamation-circle text-warning';
                break;
            case 'Out of Stock':
                iconClass = 'bi-x-circle text-danger';
                break;
            default:
                iconClass = 'bi-info-circle text-primary';
        }

        notificationElement.innerHTML = `
            <i class="bi ${iconClass}"></i>
            <div>
              <h4>${notification.item}</h4>
              <p>${notification.status}</p>
              <p>${new Date(notification.last_updated).toLocaleString()}</p>
            </div>
        `;

        notificationItems.appendChild(notificationElement);

        // Append divider except for the last item
        if (index < notifications.length - 1) {
            const divider = document.createElement('li');
            divider.innerHTML = '<hr class="dropdown-divider">';
            notificationItems.appendChild(divider);
        }
    });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Get the sidebar elements
    var sidebarPOS = document.getElementById("sidebarPOS");
    var sidebarORDERS = document.getElementById("sidebarORDERS");
    var sidebarDISCOUNTS = document.getElementById("sidebarDISCOUNTS");
    var sidebarDASHBOARD = document.getElementById("sidebarDASHBOARD");
    var sidebarMENU = document.getElementById("sidebarMENU");
    var sidebarSALES = document.getElementById("sidebarSALES");
    var sidebarINVENTORY = document.getElementById("sidebarINVENTORY");
    var sidebarPRINT = document.getElementById("sidebarPRINT");
    var sidebarBACKUP = document.getElementById("sidebarBACKUP");
    var sidebarHISTORY = document.getElementById("sidebarHISTORY");
    var archivedDISCOUNTS = document.getElementById("archivedDISCOUNTS");
    var archivedMENU = document.getElementById("archivedMENU");
    var archivedMENUCATEGORIES = document.getElementById("archivedMENUCATEGORIES");
    var archivedSALES = document.getElementById("archivedSALES");
    var archivedINVENTORY = document.getElementById("archivedINVENTORY");
    var archivedINVENTORYCATEGORIES = document.getElementById("archivedINVENTORYCATEGORIES");
    var archivedEMPLOYEES = document.getElementById("archivedEMPLOYEES");
    var tablesNav = document.getElementById("tables-nav");

    var pathname = window.location.pathname;

    // Function to highlight the active menu item and expand dropdowns
    function highlightMenuItem(element) {
        element.classList.remove("collapsed");
        element.classList.add("active");
    }

    if (pathname.includes("orders/orders.php")) {
        highlightMenuItem(sidebarORDERS);
    }

    if (pathname.includes("POS/POS.php")) {
        highlightMenuItem(sidebarPOS);
    }

    if (pathname.includes("discounts/discounts.php")) {
        highlightMenuItem(sidebarDISCOUNTS);
    }

    if (pathname.includes("dashboard/dashboard.php")) {
        highlightMenuItem(sidebarDASHBOARD);
    }

    if (pathname.includes("menu/menu.php")) {
        highlightMenuItem(sidebarMENU);
    }

    if (pathname.includes("sales/sales.php")) {
        highlightMenuItem(sidebarSALES);
    }

    if (pathname.includes("inventory/inventory.php")) {
        highlightMenuItem(sidebarINVENTORY);
    }

    if (pathname.includes("print/print.php")) {
        highlightMenuItem(sidebarPRINT);
    }

    if (pathname.includes("sql-backup/sql-backup.php")) {
        highlightMenuItem(sidebarBACKUP);
    }

    if (pathname.includes("history/history.php")) {
        highlightMenuItem(sidebarHISTORY);
    }

    if (pathname.includes("archived/archived-discounts.php")) {
        highlightMenuItem(archivedDISCOUNTS);
        // Ensure the dropdown is expanded
        if (tablesNav) {
            tablesNav.classList.add("show");
        }
    }
    if (pathname.includes("archived/archived-menu.php")) {
        highlightMenuItem(archivedMENU);
        // Ensure the dropdown is expanded
        if (tablesNav) {
            tablesNav.classList.add("show");
        }
    }
    if (pathname.includes("archived/archived-menu-categories.php")) {
        highlightMenuItem(archivedMENUCATEGORIES);
        // Ensure the dropdown is expanded
        if (tablesNav) {
            tablesNav.classList.add("show");
        }
    }
    if (pathname.includes("archived/archived-sales.php")) {
        highlightMenuItem(archivedSALES);
        // Ensure the dropdown is expanded
        if (tablesNav) {
            tablesNav.classList.add("show");
        }
    }
    if (pathname.includes("archived/archived-inventory.php")) {
        highlightMenuItem(archivedINVENTORY);
        // Ensure the dropdown is expanded
        if (tablesNav) {
            tablesNav.classList.add("show");
        }
    }
    if (pathname.includes("archived/archived-inventory-categories.php")) {
        highlightMenuItem(archivedINVENTORYCATEGORIES);
        // Ensure the dropdown is expanded
        if (tablesNav) {
            tablesNav.classList.add("show");
        }
    }
    if (pathname.includes("archived/archived-employees.php")) {
        highlightMenuItem(archivedEMPLOYEES);
        // Ensure the dropdown is expanded
        if (tablesNav) {
            tablesNav.classList.add("show");
        }
    }
});
</script>
