<?php
	//Include database connection details
	require_once('system-db.php');
	
	start_db();
	initialise_db();
	
	$customerid = $_GET['id'];
	$memberid = getLoggedOnMemberID();
	
	$qry = "SELECT A.id FROM {$_SESSION['ESCCO_DB_PREFIX']}documents A " .
		   "WHERE A.sessionid = '" . session_id() . "' " .
		   	"AND A.id NOT IN (SELECT documentid FROM {$_SESSION['ESCCO_DB_PREFIX']}customerdocs WHERE documentid = A.id) " .
		   "ORDER BY A.id";
	$result = mysql_query($qry);
	
	if (! $result) {
		logError($qry . " = " . mysql_error());
	}
	
	while (($member = mysql_fetch_assoc($result))) {
		$qry = "INSERT INTO {$_SESSION['ESCCO_DB_PREFIX']}customerdocs " .
				"(customerid, documentid, createddate, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) " .
				"VALUES " .
				"($customerid, " . $member['id'] . ", NOW(), NOW(), $memberid, NOW(), $memberid)";
				
		$itemresult = mysql_query($qry);
		
		if (! $itemresult) {
			logError($qry . " = " . mysql_error());
		}
	}
	
	$qry = "UPDATE {$_SESSION['ESCCO_DB_PREFIX']}documents " .
		   "SET sessionid = NULL, metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . " " .
		   "WHERE sessionid = '" . session_id() . "'";
	$result = mysql_query($qry);
	
	if (! $result) {
		logError($qry . " = " . mysql_error());
	}
		
	if (isset($_GET['refer'])) {
	  	header("location: " . base64_decode($_GET['refer']));
		
	} else {
	  	header("location: " . $_SERVER['HTTP_REFERER']);
	}	
?>