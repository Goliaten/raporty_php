<?php
$target_dir = "uploads/";
$target_file = $target_dir . "uploaded_file.csv";

if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}
print_r($_FILES);

if ($_FILES["fileToUpload"]["error"] !== UPLOAD_ERR_OK) {
	if ($_FILES["fileToUpload"]["error"] == 4){
	}
	switch($_FILES["fileToUpload"]["error"]){
		case 1:
			header("Location: main.php?upload_status=File exceeds max upload size");
			break;
		case 4:
			header("Location: main.php?upload_status=No file was uploaded");
			break;
		default:
			die("Upload failed with error code " . $_FILES["fileToUpload"]["error"]);
			break;
	}
}

$fileType = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));
if ($fileType != "csv") {
	header("Location: main.php?upload_status=File uploaded is not .csv");
	exit;
}

if (!move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
	header("Location: main.php?upload_status=Couldn't move file on server.");
	exit;
}

header("Location: process_csv.php");
exit;
?>
