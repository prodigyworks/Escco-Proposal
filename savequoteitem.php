<?php
	//Include database connection details
	require_once('system-db.php');
	require_once("sqlprocesstoarray.php");
	
	start_db();
	
	$quoteid = $_POST['quoteid'];
	$id = $_POST['id'];
	$qty = ($_POST['qty']);
	$unitprice = ($_POST['unitprice']);
	$vatrate = $_POST['vatrate'];
	$vat = $_POST['vat'];
	$total = $_POST['total'];
	$productid = ($_POST['productid']);
	
	if ($id == "") {
		$qry = "INSERT INTO {$_SESSION['ESCCO_DB_PREFIX']}quoteitem " .
				"(quoteid, quantity, priceeach, vatrate, vat, linetotal, " .
				"productid, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) " .
				"VALUES " .
				"($quoteid, '$qty', '$unitprice', $vatrate, '$vat', $total, " .
				"'$productid', NOW(), " . getLoggedOnMemberID() . ", NOW(), " .  getLoggedOnMemberID() . ")";
		$result = mysql_query($qry);
		
		if (! $result) {
			logError($qry . " - " . mysql_error());
		}
		
	} else {
		$qry = "UPDATE {$_SESSION['ESCCO_DB_PREFIX']}quoteitems SET " .
				"quantity = '$qty', " .
				"priceeach = '$unitprice', " .
				"vatrate = '$vatrate', " .
				"vat = '$vat', " .
				"linetotal = $total, " .
				"productid = '$productid', metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
				"WHERE id = $id";
		$result = mysql_query($qry);
		
		if (! $result) {
			logError($qry . " - " . mysql_error());
		}
	}
	
//	$qry = "UPDATE {$_SESSION['ESCCO_DB_PREFIX']}quotes SET " .
//			"total = (SELECT SUM(B.total) FROM {$_SESSION['ESCCO_DB_PREFIX']}quoteitems B WHERE B.quoteid = $quoteid), " .
//			"depositrequired = ((SELECT SUM(B.total) FROM {$_SESSION['ESCCO_DB_PREFIX']}quoteitems B WHERE B.quoteid = $quoteid)), metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
//			"WHERE id = $quoteid";
//	$result = mysql_query($qry);
//	
//	if (! $result) {
//		logError($qry . " - " . mysql_error());
//	}
	
	$qry = "SELECT A.*, B.description  " .
			"FROM {$_SESSION['ESCCO_DB_PREFIX']}quoteitems A " .
			"LEFT OUTER JOIN {$_SESSION['ESCCO_DB_PREFIX']}product B " .
			"ON B.id = A.productid " .
			"WHERE A.quoteid = $quoteid " .
			"ORDER BY A.id";
	
	$json = new SQLProcessToArray();
	
	echo json_encode($json->fetch($qry));
?>