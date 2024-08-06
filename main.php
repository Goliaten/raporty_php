<?php 
	$choice_index = 0;
?>
<html>
<head>
	
</head>
<body>
	<form action="upload.php" method="POST" enctype="multipart/form-data">
		<input type="file" id="file" name="fileToUpload" accept=".csv">
		<!--  onchange="this.form.submit();" -->
		<input type="submit"/>
	</form>
	<br>
	<?php 
		if($_GET){
			echo $_GET['upload_status'];
		}
	?>
	<br>
	<button type="button" onclick="raport(1)">Wygeneruj raport 1</button>
	<br>
	<button type="button" onclick="raport(2)">Wygeneruj raport 2</button>
	
	<form id="date_picker" style="visibility:collapse;"  method="POST" action="raport.php">
		<div id="date_picker_label"> index - 0 </div>
		<input type="date" name="date" value="2022-10-06"/>
		<input type="submit"/>
		<input type="input" style="visibility:collapse" id="raport_id" name="raport_id"/>
	</form>
	
	<script>
		function raport(number){
			var picker = document.getElementById("date_picker");
			picker.style.visibility='visible';
			document.getElementById("date_picker_label").innerHTML = "Wybierz datę dla raportu " + number;
			document.getElementById("raport_id").value = number;
		}
	</script>
	
</body>
</html>