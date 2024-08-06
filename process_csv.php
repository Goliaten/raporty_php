<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "report_db";
$tableName = "report_tb";
$charset = "utf8mb4";

$separator = ";";

$csvFile = "uploads/uploaded_file.csv";

function removeBOM($string) {
    if (substr($string, 0, 3) === "\xEF\xBB\xBF") {
        $string = substr($string, 3);
    }
    return $string;
}

if (!file_exists($csvFile)) {
    die("No CSV file found to process.");
}

$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) !== TRUE) {
    die("Error creating database: " . $conn->error);
}

$conn->select_db($dbname);

$conn->set_charset($charset);

$csvData = array_map(function($line) use ($separator) {
    return str_getcsv(removeBOM($line), $separator);
}, file($csvFile));

if (empty($csvData)) {
    die("The CSV file is empty or could not be read.");
}

$headers = $csvData[0];

$sql = "DROP TABLE IF EXISTS $tableName";
if ($conn->query($sql) !== TRUE) {
    die("Error dropping table: " . $conn->error);
}

$createTableQuery = "CREATE TABLE $tableName (ID int NOT NULL AUTO_INCREMENT,";
foreach ($headers as $header) {
    $createTableQuery .= "`" . mysqli_real_escape_string($conn, $header) . "` VARCHAR(255),";
}
$createTableQuery = rtrim($createTableQuery, ',') . ', PRIMARY KEY(ID))';
if ($conn->query($createTableQuery) !== TRUE) {
    die("Error creating table: " . $conn->error);
}
$setEncodingQuery = "ALTER TABLE $tableName CONVERT TO CHARACTER SET $charset";
if ($conn->query($setEncodingQuery) !== True){
	die("Error setting the encoding: " . $conn->error);
}


foreach (array_slice($csvData, 1) as $row) {
    $row = array_map(function($field) use ($conn) {
        // Escape single quotes and special characters
        return "'" . mysqli_real_escape_string($conn, $field) . "'";
    }, $row);
	
    // Generate the INSERT query
    $insertQuery = "INSERT INTO $tableName (" . implode(",", array_map(function($header) {
        return "`" . $header . "`";
    }, $headers)) . ") VALUES (" . implode(",", $row) . ")";
    
    if ($conn->query($insertQuery) !== TRUE) {
        die("Error inserting data: " . $conn->error);
    }
}

$conn->close();

// Redirect to the main.php script
header("Location: main.php?upload_status=csv uploaded correctly");
exit;
?>
