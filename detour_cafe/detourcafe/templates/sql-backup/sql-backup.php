<?php
// Database connection parameters
$host = 'localhost';
$dbname = 'detour_cafe';
$user = 'root';
$pass = '';

// File name and path
$backupFile = 'backup-' . date('Y-m-d-H-i-s') . '.sql';

// Create a connection
$mysqli = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Open file for writing
$fileHandle = fopen($backupFile, 'w');

if (!$fileHandle) {
    die("Unable to open file for writing.");
}

// Fetch all tables
$tables = $mysqli->query('SHOW TABLES');

if (!$tables) {
    die("Error fetching tables: " . $mysqli->error);
}

// Iterate over tables
while ($table = $tables->fetch_array()) {
    $tableName = $table[0];
    
    // Write table structure
    $createTableQuery = $mysqli->query("SHOW CREATE TABLE $tableName")->fetch_array()[1];
    fwrite($fileHandle, "$createTableQuery;\n\n");

    // Fetch table data
    $rows = $mysqli->query("SELECT * FROM $tableName");

    while ($row = $rows->fetch_assoc()) {
        $columns = array_keys($row);
        $values = array_values($row);

        // Escape values
        $values = array_map([$mysqli, 'real_escape_string'], $values);
        $values = array_map(function($value) { return "'$value'"; }, $values);

        // Construct INSERT statement
        $query = "INSERT INTO $tableName (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
        fwrite($fileHandle, $query);
    }

    fwrite($fileHandle, "\n");
}

// Close file
fclose($fileHandle);

// Close connection
$mysqli->close();

// Serve the file for download
if (file_exists($backupFile)) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $backupFile . '"');
    header('Content-Length: ' . filesize($backupFile));
    readfile($backupFile);

    // Optionally delete the file after download
    unlink($backupFile);
    exit;
} else {
    echo "Backup file does not exist.";
}
?>
