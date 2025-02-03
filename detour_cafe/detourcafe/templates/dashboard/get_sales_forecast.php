<?php
// Path to the JSON file
$jsonFile = 'C:\\xampp\\htdocs\\detourcafe\\templates\\dashboard\\forecast_data_sales.json';  // Adjust the path as needed

// Check if the file exists
if (file_exists($jsonFile)) {
    // Read the JSON file
    $jsonData = file_get_contents($jsonFile);
    // Output the JSON data
    header('Content-Type: application/json');
    echo $jsonData;
} else {
    // Handle the error if the file does not exist
    http_response_code(404);
    echo json_encode(['error' => 'Forecast data not found']);
}
?>
