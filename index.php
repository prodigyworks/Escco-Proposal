<?php
	require_once("system-db.php");
	
	start_db();
	
	if (isUserInRole("CUSTOMER")) {
		header("location: dashboard.php");
		
	} else {
		header("location: managequotations.php");
	}
?>
