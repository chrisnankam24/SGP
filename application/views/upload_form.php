<html>
<head>
    <title>Upload Form</title>
</head>
<body>

<?php echo $error;?>

<?php echo form_open_multipart('http://172.21.75.34/SGP/index.php/api/RioAPI/getRioFile');?>

<input type="file" accept=".csv" name="rioFile" size="20" />

<br /><br />

<input type="submit" value="upload" />

</form>

</body>
</html>