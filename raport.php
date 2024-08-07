<?php

$servername = "localhost";
$username = "root";
$password = "";
$tableName = "report_tb";
$dbname = "report_db";
$charset = "utf8mb4";
$separator = ";";

$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->select_db($dbname);
$conn->set_charset($charset);

// later check if database and table exist
if(mysql_query("select 1 from $report_tb limit 1" )){
	header("Location: main.php?upload_status=Error accessing the table.");
	exit;
}

$date = $_POST['date'];
// extract data we want depending on the report
if($_POST['raport_id'] == 1){
	// zamówienia w danym miesiącu w podziale na pojedynczego pracownika
	// podział na typ zamówień, czyli dla każdego pracownika ile on miał zamówień każdego typu
	$query = "SELECT 
	`Numer rozliczeniowy`,
	CONCAT(Imię, ' ', Nazwisko) as 'Imię i Nazwisko',
	`Status zamówienia`,
	COUNT(ID) as 'Liczba zamówień',
	Sum(`Cena oferty`) as 'Suma kosztów',
	GROUP_CONCAT(`Nazwa oferty` SEPARATOR ' | ') as 'Nazwy ofert'
	FROM $tableName 
	WHERE year(`Data zamówienia`) = year('$date') &&
	month(`Data zamówienia`) = month('$date')
	GROUP BY `Numer rozliczeniowy`, `Status zamówienia`
	having count(ID) > 0";
	$result = $conn->query($query);
	if ($result === FALSE) {
		die("Error executing query: " . $conn->error);
	}
	#--------------------------------------------------------test the report 1. look if there are any ppl that arent correct
}else if ($_POST['raport_id'] == 2){
	// zagregowana statystyka zamówień
	//zawiera daty późniejsze niż wybrana data raportu
	// wszystkie zamówienia nizależnie od statusu
	$query = "SELECT 
	Concat(year(`Data zamówienia`), ' ', month(`Data zamówienia`)) as 'Miesiące',
	if(`Upgrade z zamówienia` = '', 'Nie', 'Tak') as `Powstałe z upgradu`,
	`Nazwa oferty`,
	count(ID) as 'Liczba zamówień'
	FROM $tableName 
	WHERE date(`Data zamówienia`) < date('$date')
	GROUP BY year(`Data zamówienia`), month(`Data zamówienia`), `Nazwa oferty`, `Powstałe z upgradu`
	having count(ID) > 0";
	$result = $conn->query($query);
	if ($result === FALSE) {
		die("Error executing query: " . $conn->error);
	}
}

// assemble another csv
header("Content-Type: text/csv");
header("Content-Disposition: attachment;filename=raport" . $_POST['raport_id'] . "_" . $date . ".csv");

$output = [];
if($result->num_rows > 0){
	while($row = $result->fetch_assoc()){
		$output[] = implode($separator, $row);
	}
}

$fp = fopen("php://output", 'wb');
foreach ($output as $line){
	fputcsv($fp, explode(";", $line));
}
fclose($fp);

?>