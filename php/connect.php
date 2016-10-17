<?php
	

	/*// Create connection
	$conn = mysqli_connect($servername, $username, $password, $dbname);

	// Check connection
	if (!$conn) {
	    die("Connection failed: " . mysqli_connect_error());
	}

	mysqli_set_charset($conn,"utf8");*/

	$cookie_expired = time() + (60 * 30);//60sec*20 = 20mins 86400 = 1 day

	$CURRENT_DATE = date_format(new DateTime("now", new DateTimeZone('Asia/Tokyo')),'Y-m-d H:i:s');

	$since_date = new DateTime();
	$since_date->setDate(2016,1,1);
	$since_date->setTime(0,0,0);
	$since_date = date_format($since_date,'Y-m-d H:i:s');

	function CreateConnection()
	{

		$servername = "localhost";
		$username = "root";
		$password = "1q2w3e4r5t";
		$dbname = "db_datacollection";
		// Create connection
		$conn = mysqli_connect($servername, $username, $password, $dbname);

		// Check connection
		if (!$conn) {
		    die("Connection failed: " . mysqli_connect_error());
		}
		mysqli_set_charset($conn,"utf8");
		return $conn;
	}
	function CloseConnection($conn)
	{
		mysqli_close($conn);
	}

?>