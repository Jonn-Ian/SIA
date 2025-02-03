<?php
require "../../conn/conn.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// SQL query to fetch sales data for the current week
$startOfWeek = date('Y-m-d', strtotime('sunday this week -7 days'));
$endOfWeek = date('Y-m-d 23:59:59', strtotime('saturday this week'));
$sql = "SELECT DATE_FORMAT(date_time, '%a') AS day_of_week, SUM(items_sold) AS total_items_sold
        FROM db_sales
        WHERE date_time >= '$startOfWeek' AND date_time <= '$endOfWeek'
        GROUP BY DAYOFWEEK(date_time)";
$result = $conn->query($sql);

$dataDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
$dataOrders = [0, 0, 0, 0, 0, 0, 0];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $dayIndex = array_search($row['day_of_week'], $dataDays);
        if ($dayIndex !== false) {
            $dataOrders[$dayIndex] = (int)$row['total_items_sold'];
        }
    }
}

// SQL query to fetch inventory status data
$statusQuery = "SELECT status, COUNT(*) AS count FROM db_inventory GROUP BY status";
$statusResult = $conn->query($statusQuery);

$inventoryData = [];
if ($statusResult->num_rows > 0) {
    while ($row = $statusResult->fetch_assoc()) {
        $inventoryData[] = [
            'value' => (int)$row['count'],
            'name' => $row['status']
        ];
    }
}

// Query to get sales data grouped by category and hour for today only
$sql = "SELECT category, HOUR(date_time) as hour, SUM(items_sold) as total_sold
        FROM db_sales
        WHERE DATE(date_time) = CURDATE()
        GROUP BY category, hour
        ORDER BY hour";

$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[$row['category']][$row['hour']] = $row['total_sold'];
}

// Prepare data for ECharts
$categories = array_keys($data);
$hours = array_map(function($hour) {
    return sprintf('%02d:00', $hour);
}, range(0, 23));
$series = [];

// Define the color scheme
$colorScheme = [
    '#ffcc80', '#ffab40', '#ff9100', '#ff6f00', '#ff5722',
    '#f4511e', '#e64a19', '#d84315', '#bf360c', '#a84315',
    '#8e360c', '#7f3d3a', '#5c2e1f', '#3e2723', '#2c1b1a'
];

foreach ($categories as $index => $category) {
    $color1 = $colorScheme[$index % count($colorScheme)];
    $color2 = $colorScheme[($index + 1) % count($colorScheme)];
    $seriesData = [];
    foreach ($hours as $hour) {
        $hourIndex = (int)explode(':', $hour)[0];
        $seriesData[] = isset($data[$category][$hourIndex]) ? $data[$category][$hourIndex] : 0;
    }
    $series[] = [
        'name' => $category,
        'type' => 'line',
        'stack' => 'Total',
        'smooth' => true,
        'lineStyle' => ['width' => 0],
        'showSymbol' => false,
        'areaStyle' => [
            'opacity' => 0.8,
            'color' => [
                'type' => 'linear',
                'x' => 0,
                'y' => 0,
                'x2' => 0,
                'y2' => 1,
                'colorStops' => [
                    ['offset' => 0, 'color' => $color1],
                    ['offset' => 1, 'color' => $color2]
                ]
            ]
        ],
        'data' => $seriesData
    ];
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Detour Cafe - Dashboard</title>
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
      <h1>Dashboard</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item active">Management <i class="bi bi-grid"></i></li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
    <div class="row">
        <div class="col-lg-6">

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Order Metrics</h5>
                <div id="barchart" style="height: 250px;" class="echart"></div>
            </div>
          </div>

        </div>

        <div class="col-lg-6">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Orders Today</h5>
              <div id="linechart_categories" style="height: 250px;" class="echart"></div>
          </div>
        </div>
      </div>

    </div>


      <div class="row">

      <div class="col-lg-4">

        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Inventory Status</h5>
              <div id="piechart" style="height: 250px;" class="echart"></div>
          </div>
        </div>

        </div>

        <div class="col-lg-8">

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Inventory Forecast</h5>
                <div id="linechart_inventory" style="height: 250px;" class="echart"></div>
            </div>
          </div>

        </div>

        <div class="col-lg-12">

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Demand Forecast</h5>
                <div id="linechart_demand" style="height: 250px;" class="echart"></div>
            </div>
          </div>

        </div>

        <div class="col-lg-12">

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Sales Forecast</h5>
                <div id="linechart_sales" style="height: 250px;" class="echart"></div>
            </div>
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
  <script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
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
    document.addEventListener("DOMContentLoaded", () => {
      var barChartDom = document.getElementById('barchart');
      var barChart = echarts.init(barChartDom);
      var barOption = {
        tooltip: {
          trigger: 'axis',
          axisPointer: {
            type: 'shadow'
          }
        },
        grid: {
          top: '3%',
          left: '3%',
          right: '4%',
          bottom: '3%',
          containLabel: true
        },
        xAxis: [
          {
            type: 'category',
            data: <?php echo json_encode($dataDays); ?>,
            axisTick: {
              alignWithLabel: true
            },
            axisLabel: {
              fontFamily: 'Nunito'
            }
          }
        ],
        yAxis: [
          {
            type: 'value',
            axisLabel: {
              fontFamily: 'Nunito'
            }
          }
        ],
        series: [
          {
            name: 'Orders',
            type: 'bar',
            barWidth: '60%',
            data: <?php echo json_encode($dataOrders); ?>,
            itemStyle: {
              barBorderRadius: [5, 5, 0, 0],
              color: function(params) {
                var colorList = ['#fb912d', '#f57c00', '#e65100', '#d84315', '#bf360c', '#a84315', '#8e360c'];
                return colorList[params.dataIndex];
              }
            }
          }
        ]
      };
      barOption && barChart.setOption(barOption);

      var pieChartDom = document.getElementById('piechart');
      var pieChart = echarts.init(pieChartDom);

      var colorMapping = {
        "in stock": '#fb912d',
        "critical (buy now)": '#e65100',
        "out of stock": '#bf360c'
      };

      var sortOrder = ["in stock", "critical (buy now)", "out of stock"];

      var inventoryData = <?php echo json_encode($inventoryData); ?>;
      
      inventoryData.sort(function(a, b) {
        return sortOrder.indexOf(a.name.toLowerCase()) - sortOrder.indexOf(b.name.toLowerCase());
      });

      inventoryData.forEach(function(item) {
        item.itemStyle = { color: colorMapping[item.name.toLowerCase()] };
      });

      var pieOption = {
        tooltip: {
          trigger: 'item'
        },
        legend: {
          left: 'center'
        },
        series: [
          {
            name: 'Inventory Status',
            type: 'pie',
            radius: ['55%', '75%'],
            avoidLabelOverlap: false,
            itemStyle: {
              borderRadius: 5,
              borderColor: '#fff',
              borderWidth: 2
            },
            label: {
              show: false,
              position: 'center'
            },
            emphasis: {
              label: {
                show: true,
                fontSize: 25,
                fontFamily: 'Nunito'
              }
            },
            labelLine: {
              show: false
            },
            data: inventoryData,
            center: ['50%', '55%'] // Adjust this to move the chart vertically
          }
        ]
      };

      pieOption && pieChart.setOption(pieOption);

      var chartDom = document.getElementById('linechart_categories');
      var myChart = echarts.init(chartDom);
      var option;

      option = {
          color: <?php echo json_encode(array_values($colorScheme)); ?>, // Use PHP to set the color scheme
          tooltip: {
              trigger: 'axis',
              axisPointer: {
                  type: 'cross',
                  label: {
                      backgroundColor: '#6a7985'
                  }
              },
              formatter: function(params) {
                  let tooltipContent = '';
                  params.forEach(param => {
                      tooltipContent += `
                          <div style="display: flex; align-items: center;">
                              <div style="width: 10px; height: 10px; background-color: ${param.color}; border-radius: 50%; margin-right: 5px;"></div>
                              ${param.seriesName}: ${param.value}
                          </div>`;
                  });
                  return tooltipContent;
              }
          },
          legend: {
              data: <?php echo json_encode($categories); ?>,
          },
          grid: {
              left: '3%',
              right: '4%',
              bottom: '3%',
              containLabel: true
          },
          xAxis: {
              type: 'category',
              boundaryGap: false,
              data: <?php echo json_encode($hours); ?>
          },
          yAxis: {
              type: 'value'
          },
          series: <?php echo json_encode($series); ?>,
          emphasis: {
              focus: 'series'
          }
      };

      option && myChart.setOption(option);
 
     // Fetch data from JSON file
    fetch('forecast_data_sales.json')
    .then(response => response.json())
    .then(jsonData => {
        // Extract the data
        const dates = jsonData.data.map(item => item[0]);
        const netSales = jsonData.data.map(item => item[1]);
        const grossProfit = jsonData.data.map(item => item[2]);

        // Initialize ECharts
        const chart = echarts.init(document.getElementById('linechart_sales'));

        // ECharts options
        const options = {
            color: ['#ffcc80', '#ff9100'], // Light orange for Gross Profit and medium orange for Net Sales
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'cross',
                    label: {
                        backgroundColor: '#6a7985'
                    }
                },
                formatter: function(params) {
                    let tooltipContent = '';
                    params.forEach(param => {
                        // Format values as currency and include the number of pieces
                        const valueFormatted = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'PHP' }).format(param.value);
                        tooltipContent += `
                            <div style="display: flex; align-items: center;">
                                <div style="width: 10px; height: 10px; background-color: ${param.color}; border-radius: 50%; margin-right: 5px;"></div>
                                ${param.seriesName}: ${valueFormatted}
                            </div>`;
                    });
                    return tooltipContent;
                }
            },
            legend: {
                data: ['Net Sales', 'Gross Profit']
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis: {
                type: 'category',
                boundaryGap: false,
                data: dates,
                axisLabel: {
                    rotate: 45,
                    formatter: function (value) {
                        // Format x-axis labels to show month and year
                        const date = new Date(value);
                        const options = { year: 'numeric', month: 'short' }; // 'short' for abbreviated month names
                        return new Intl.DateTimeFormat('en-US', options).format(date);
                    }
                }
            },
            yAxis: {
                type: 'value',
                axisLabel: {
                    formatter: function (value) {
                        // Format y-axis values as currency
                        return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'PHP' }).format(value);
                    }
                }
            },
            series: [
                {
                    name: 'Net Sales',
                    type: 'line',
                    smooth: true,
                    lineStyle: {
                        width: 0 // Hide line border
                    },
                    showSymbol: false,
                    areaStyle: {
                        opacity: 0.8, // Adjust opacity for the area
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                            {
                                offset: 0,
                                color: '#ff9100' // Medium orange
                            },
                            {
                                offset: 1,
                                color: '#ff5722' // Bright orange-red
                            }
                        ])
                    },
                    emphasis: {
                        focus: 'series'
                    },
                    data: netSales
                },
                {
                    name: 'Gross Profit',
                    type: 'line',
                    smooth: true,
                    lineStyle: {
                        width: 0 // Hide line border
                    },
                    showSymbol: false,
                    areaStyle: {
                        opacity: 0.8, // Adjust opacity for the area
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                            {
                                offset: 0,
                                color: '#ffcc80' // Light orange
                            },
                            {
                                offset: 1,
                                color: '#ffab40' // Lighter orange
                            }
                        ])
                    },
                    emphasis: {
                        focus: 'series'
                    },
                    data: grossProfit
                }
            ],
            emphasis: {
                focus: 'series'
            }
        };

        // Set the options
        chart.setOption(options);
    })
    .catch(error => console.error('Error loading JSON data:', error));


    async function loadForecastData() {
    try {
        const response = await fetch('forecast_levels.json');
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const rawData = await response.json();

        // Get the start date as the current date and generate the next 3 weeks
        const now = new Date();
        const startDate = new Date(now);
        startDate.setDate(now.getDate() - now.getDay() + 1); // Set to the Monday of the current week

        // Generate days of the week for 3 weeks
        const xAxisData = [];
        for (let week = 0; week < 3; week++) {
            for (let day = 0; day < 7; day++) {
                const date = new Date(startDate);
                date.setDate(startDate.getDate() + week * 7 + day);
                xAxisData.push(date.toISOString().slice(0, 10)); // Format as YYYY-MM-DD
            }
        }

        // Aggregate data by category
        const aggregatedData = rawData.reduce((acc, curr) => {
            if (!acc[curr.category]) {
                acc[curr.category] = {};
            }
            curr.forecast.forEach(forecast => {
                const date = forecast.date_time.slice(0, 10); // Extract YYYY-MM-DD
                const units = forecast.forecast_units;
                if (!xAxisData.includes(date)) return; // Skip dates not in xAxisData
                if (!acc[curr.category][date]) {
                    acc[curr.category][date] = 0;
                }
                acc[curr.category][date] += units;
            });
            return acc;
        }, {});

        // Convert aggregated data to ECharts series format
        const categories = Object.keys(aggregatedData);
        const colorPalette = [
            '#ffcc80', '#ffab40', '#ff9100', '#ff6f00', '#ff5722',
            '#f4511e', '#e64a19', '#d84315', '#bf360c', '#a84315',
            '#8e360c', '#7f3d3a', '#5c2e1f', '#3e2723', '#2c1b1a'
        ];

        const series = categories.map((category, index) => ({
            name: category,
            type: 'line',
            smooth: true,
            lineStyle: {
                width: 0 // Hide line border
            },
            showSymbol: false,
            areaStyle: {
                opacity: 0.8, // Adjust opacity for the area
                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                    {
                        offset: 0,
                        color: colorPalette[index % colorPalette.length] // Lighter color
                    },
                    {
                        offset: 1,
                        color: colorPalette[(index + 1) % colorPalette.length] // Darker color
                    }
                ])
            },
            emphasis: {
                focus: 'series'
            },
            data: xAxisData.map(date => {
                const value = aggregatedData[category][date] || 0;
                return { name: date, value };
            })
        }));

        // Initialize ECharts instance
        const chart = echarts.init(document.getElementById('linechart_inventory'));

        // Chart options
        const option = {
            color: colorPalette,
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                top: '10%',
                containLabel: true
            },
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'cross',
                    label: {
                        backgroundColor: '#6a7985'
                    }
                },
                formatter: function(params) {
                    const date = new Date(params[0].name);
                    const dayOfWeek = date.toLocaleDateString('en-US', { weekday: 'short' });
                    const formattedDate = date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
                    let tooltipText = `${dayOfWeek}, ${formattedDate}<br/>`;
                    params.forEach(param => {
                        const value = param.value.toFixed(2); // Round to 2 decimals
                        tooltipText += `
                            <div style="display: flex; align-items: center;">
                                <div style="width: 10px; height: 10px; border-radius: 50%; background-color: ${param.color}; margin-right: 5px;"></div>
                                ${param.seriesName}: ${value}
                            </div>`;
                    });
                    return tooltipText;
                }
            },
            legend: {
                data: categories,
                orient: 'horizontal',
                left: 'center',
                top: 'top'
            },
            xAxis: {
                type: 'category',
                boundaryGap: false,
                data: xAxisData,
                axisLabel: {
                    formatter: function(value) {
                        const date = new Date(value);
                        return date.toLocaleDateString('en-US', { weekday: 'short' }); // Mon, Tue, Wed, etc.
                    },
                    rotate: 90, // Rotate the labels 90 degrees for vertical alignment
                    interval: 0 // Show all labels
                }
            },
            yAxis: {
                type: 'value'
            },
            series: series,
            emphasis: {
                focus: 'series'
            }
        };

        // Set chart options
        chart.setOption(option);

        // Resize chart to fit the card better
        window.addEventListener('resize', () => {
            chart.resize();
        });
    } catch (error) {
        console.error('Error loading forecast data:', error);
    }
}

// Load the data and render the chart
loadForecastData();



// Function to fetch data and render chart
function fetchAndRenderChart() {
    // Fetch data from the JSON file
    fetch('forecast_demand.json')
        .then(response => response.json())
        .then(data => {
            // Convert timestamps to abbreviated month-year format and extract unique categories
            const categories = [...new Set(data.map(item => item.category))];
            const dates = [...new Set(data.map(item => {
                const date = new Date(item.date_time);
                const options = { month: 'short', year: 'numeric' };
                return date.toLocaleDateString('en-US', options);
            }))];

            // Initialize ECharts instance
            const chart = echarts.init(document.getElementById('linechart_demand'));

            // Color scheme
            const colorScheme = ['#ffcc80', '#ffab40', '#ff9100', '#ff6f00', '#ff5722', '#f4511e', '#e64a19', '#d84315', '#bf360c', '#a84315', '#8e360c', '#7f3d3a', '#5c2e1f', '#3e2723', '#2c1b1a'];

            // Prepare series data for each category
            const series = categories.map((category, index) => {
                const categoryData = data.filter(item => item.category === category);
                const totalItemsSold = dates.map(date => {
                    const item = categoryData.find(d => {
                        const itemDate = new Date(d.date_time).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                        return itemDate === date;
                    });
                    return item ? item.forecast_items_sold : 0; // Handle missing data
                });

                return {
                    name: category,
                    type: 'line',
                    smooth: true,
                    lineStyle: {
                        width: 0 // Hide line border
                    },
                    showSymbol: false,
                    areaStyle: {
                        opacity: 0.8, // Adjust opacity for the area
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                            {
                                offset: 0,
                                color: colorScheme[index % colorScheme.length] // Use color scheme
                            },
                            {
                                offset: 1,
                                color: colorScheme[(index + 1) % colorScheme.length] // Use next color in scheme
                            }
                        ])
                    },
                    emphasis: {
                        focus: 'series'
                    },
                    data: totalItemsSold
                };
            });

            // Configure chart options
            const options = {
                color: colorScheme, // Colors for the categories
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'cross',
                        label: {
                            backgroundColor: '#6a7985'
                        }
                    },
                    formatter: function(params) {
                        let tooltipContent = '';
                        params.forEach(param => {
                            // Format values without currency
                            const valueFormatted = param.value.toFixed(0); // No decimal places
                            tooltipContent += `
                                <div style="display: flex; align-items: center;">
                                    <div style="width: 10px; height: 10px; background-color: ${param.color}; border-radius: 50%; margin-right: 5px;"></div>
                                    ${param.seriesName}: ${valueFormatted}
                                </div>`;
                        });
                        return tooltipContent;
                    }
                },
                legend: {
                    data: categories,
                    orient: 'horizontal',
                    left: 'center',
                    top: 'top'
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    boundaryGap: false,
                    data: dates,
                    axisLabel: {
                        rotate: 45,
                        formatter: function (value) {
                            // Format x-axis labels to show month and year
                            return value; // Already formatted as month-year
                        }
                    }
                },
                yAxis: {
                    type: 'value'
                },
                series: series,
                emphasis: {
                    focus: 'series'
                }
            };

            // Set the options to the chart
            chart.setOption(options);

            // Resize chart to fit the card better
            window.addEventListener('resize', () => {
                chart.resize();
            });
        })
        .catch(error => {
            console.error('Error fetching data:', error);
        });
}

// Call the function to fetch data and render the chart
fetchAndRenderChart();

        });
  </script>

</body>

</html>