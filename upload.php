<?php
// Directory where the file will be uploaded
$target_dir = "uploads/";

// Ensure the upload directory exists, if not create it
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}
print_r($_FILES);

// Check if the file was uploaded
if (!isset($_FILES["fileToUpload"])) {
    die("No file was uploaded.");
}

// Check for upload errors
if ($_FILES["fileToUpload"]["error"] !== UPLOAD_ERR_OK) {
    die("Upload failed with error code " . $_FILES["fileToUpload"]["error"]);
}

// Check file size (limit to 5MB)
if ($_FILES["fileToUpload"]["size"] > 5000000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}

// Ensure the file is a CSV
$fileType = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));
if ($fileType != "csv") {
    die("Sorry, only CSV files are allowed.");
}

// Save the uploaded file with a fixed name
$target_file = $target_dir . "uploaded_file.csv";
if (!move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
    die("Sorry, there was an error uploading your file.");
}

// Redirect to the process_csv.php script
header("Location: process_csv.php");
exit;
?>
