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
	header("Location: main.php?upload_status=CSV file not found on server");
	exit;
}

$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
	header("Location: main.php?upload_status=DB connection failed: " . $conn->connect_error);
	exit;
}

$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) !== TRUE) {
	header("Location: main.php?upload_status=Error creating database: " . $conn->error);
	exit;
}

$conn->select_db($dbname);

$conn->set_charset($charset);

$csvData = array_map(function($line) use ($separator) {
    return str_getcsv(removeBOM($line), $separator);
}, file($csvFile));

if (empty($csvData)) {
	header("Location: main.php?upload_status=CSV can't be read");
	exit;
}

$headers = $csvData[0];

$sql = "DROP TABLE IF EXISTS $tableName";
if ($conn->query($sql) !== TRUE) {
	header("Location: main.php?upload_status=Error dropping table: " . $conn->error);
	exit;
}

$createTableQuery = "CREATE TABLE $tableName (ID int NOT NULL AUTO_INCREMENT,";
foreach ($headers as $header) {
    $createTableQuery .= "`" . mysqli_real_escape_string($conn, $header) . "` VARCHAR(255),";
}
$createTableQuery = rtrim($createTableQuery, ',') . ', PRIMARY KEY(ID))';
if ($conn->query($createTableQuery) !== TRUE) {
	header("Location: main.php?upload_status=Error creating table: " . $conn->error);
	exit;
}
$setEncodingQuery = "ALTER TABLE $tableName CONVERT TO CHARACTER SET $charset";
if ($conn->query($setEncodingQuery) !== True){
	header("Location: main.php?upload_status=Error setting the encoding: " . $conn->error);
	exit;
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
		header("Location: main.php?upload_status=Error inserting data: " . $conn->error);
		exit;
    }
}

$conn->close();

// Redirect to the main.php script
header("Location: main.php?upload_status=csv uploaded correctly");
exit;
?>
