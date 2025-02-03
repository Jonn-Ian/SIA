window.onload = function() {
    // Inventory Chart
    var ctxInventory = document.getElementById('inventoryChart').getContext('2d');
    var inventoryChart = new Chart(ctxInventory, {
        type: 'line',
        data: {
            labels: ['1pm', '2pm', '3pm', '4pm', '5pm'],
            datasets: [{
                label: 'Inventory',
                data: [90, 92, 95, 93, 95],
                backgroundColor: 'rgba(255, 165, 0, 0.2)',
                borderColor: 'rgba(255, 165, 0, 1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Demand Chart
    var ctxDemand = document.getElementById('demandChart').getContext('2d');
    var demandChart = new Chart(ctxDemand, {
        type: 'line',
        data: {
            labels: ['1pm', '2pm', '3pm', '4pm', '5pm'],
            datasets: [{
                label: 'Demand',
                data: [80, 82, 86, 84, 86],
                backgroundColor: 'rgba(255, 165, 0, 0.2)',
                borderColor: 'rgba(255, 165, 0, 1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Sales Chart
    var ctxSales = document.getElementById('salesChart').getContext('2d');
    var salesChart = new Chart(ctxSales, {
        type: 'line',
        data: {
            labels: ['1pm', '2pm', '3pm', '4pm', '5pm'],
            datasets: [{
                label: 'Sales',
                data: [40, 45, 50, 48, 50],
                backgroundColor: 'rgba(255, 165, 0, 0.2)',
                borderColor: 'rgba(255, 165, 0, 1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Orders Chart
    var ctxOrders = document.getElementById('ordersChart').getContext('2d');
    var ordersChart = new Chart(ctxOrders, {
        type: 'bar',
        data: {
            labels: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            datasets: [{
                label: 'Orders',
                data: [100, 200, 300, 400, 500, 600, 700],
                backgroundColor: [
                    '#ccc',
                    '#ccc',
                    '#ccc',
                    '#f90',
                    '#ccc',
                    '#ccc',
                    '#ccc'
                ],
                borderColor: [
                    '#bbb',
                    '#bbb',
                    '#bbb',
                    '#e80',
                    '#bbb',
                    '#bbb',
                    '#bbb'
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Order Summary Chart
    var ctxOrderSummary = document.getElementById('orderSummaryChart').getContext('2d');
    var orderSummaryChart = new Chart(ctxOrderSummary, {
        type: 'doughnut',
        data: {
            labels: ['Dine In', 'Take Out', 'Online'],
            datasets: [{
                label: 'Order Summary',
                data: [2452, 942, 25],
                backgroundColor: [
                    '#f90',
                    '#ccc',
                    '#999'
                ],
                borderColor: [
                    '#e80',
                    '#bbb',
                    '#888'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Order Summary'
                }
            }
        }
    });

    // Sidebar Navigation
    const sidebarItems = document.querySelectorAll('.sidebar-item');

    sidebarItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();

            // Remove 'active' class from all sidebar items
            sidebarItems.forEach(i => i.classList.remove('active'));

            // Add 'active' class to the clicked item
            this.classList.add('active');

            // Get the corresponding content ID from id attribute
            const contentId = this.getAttribute('id').replace('content', ''); // Get id number

            // Hide all content sections
            const contentItems = document.querySelectorAll('.main-content > section');
            contentItems.forEach(item => item.style.display = 'none');

            // Show the corresponding content section
            document.getElementById(`content${contentId}`).style.display = 'block';
        });
    });
};
