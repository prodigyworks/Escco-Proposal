<?php
	include("system-header.php"); 
	
	$substringstart = 0;
	
	function startsWith($Haystack, $Needle){
	    // Recommended version, using strpos
	    return strpos($Haystack, $Needle) === 0;
	}
	
	class PriceItem {
	    // property declaration
	    public $from = 0;
	    public $to = 0;
	}
 
	class ProductLength {
	    // property declaration
	    public $length = 0;
	    public $longline = 0;
	}

	if (isset($_FILES['customerfile']) && $_FILES['customerfile']['tmp_name'] != "") {
		if ($_FILES["customerfile"]["error"] > 0) {
			echo "Error: " . $_FILES["customerfile"]["error"] . "<br />";
			
		} else {
		  	echo "Upload: " . $_FILES["customerfile"]["name"] . "<br />";
		  	echo "Type: " . $_FILES["customerfile"]["type"] . "<br />";
		  	echo "Size: " . ($_FILES["customerfile"]["size"] / 1024) . " Kb<br />";
		  	echo "Stored in: " . $_FILES["customerfile"]["tmp_name"] . "<br>";
		}
		
		$subcat1 = "";
		$row = 1;
		
		if (($handle = fopen($_FILES['customerfile']['tmp_name'], "r")) !== FALSE) {
		    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		        if ($row++ == 1) {
		        	continue;
		        }
		        
		        $num = count($data);
		        $index = 0;
		        
		        $customerteam = mysql_escape_string($data[$index++]);
		        $owner = mysql_escape_string($data[$index++]);
		        $accountnumber = mysql_escape_string($data[$index++]);
		        $name = mysql_escape_string($data[$index++]);
		        $firstname = mysql_escape_string($data[$index++]);
                $lastname = mysql_escape_string($data[$index++]);
		        $invoiceaddress1 = mysql_escape_string($data[$index++]);
		        $invoiceaddress2 = mysql_escape_string($data[$index++]);
		        $invoiceaddress3 = mysql_escape_string($data[$index++]);
		        $invoicecity = mysql_escape_string($data[$index++]);
		        $invoicecounty = mysql_escape_string($data[$index++]);
		        $invoicepostcode = mysql_escape_string($data[$index++]);
		        $invoicecountry = mysql_escape_string($data[$index++]);
		        $email1 = mysql_escape_string($data[$index++]);
		        $telephone1 = mysql_escape_string($data[$index++]);
		        $fax1 = mysql_escape_string($data[$index++]);
		        $deliverymethod = mysql_escape_string($data[$index++]);
		        $deliveryaddress1 = mysql_escape_string($data[$index++]);
		        $deliveryaddress2 = mysql_escape_string($data[$index++]);
		        $deliveryaddress3 = mysql_escape_string($data[$index++]);
		        $deliverycity = mysql_escape_string($data[$index++]);
		        $deliverycounty = mysql_escape_string($data[$index++]);
		        $deliverypostcode = mysql_escape_string($data[$index++]);
		        $deliverycountry = mysql_escape_string($data[$index++]);
		        $email2 = mysql_escape_string($data[$index++]);
		        $telephone2 = mysql_escape_string($data[$index++]);
		        		        
		        if ($data[3] != "") {
		        	echo "<div>Customer: $name</div>";
		        	
					$qry = "INSERT INTO {$_SESSION['ESCCO_DB_PREFIX']}customerteam " .
							"(name) " .
							"VALUES " .
							"('$customerteam')";
							
					$result = mysql_query($qry);
		        	$teamid =  mysql_insert_id();
        	
					if (mysql_errno() == 1062) {
						$qry = "SELECT id " .
								"FROM {$_SESSION['ESCCO_DB_PREFIX']}customerteam " .
								"WHERE name = '$customerteam'";
						$result = mysql_query($qry);
						
						//Check whether the query was successful or not
						if ($result) {
							while (($member = mysql_fetch_assoc($result))) {
								$teamid = $member['id'];
							}
						}
					}
					
					$qry = "INSERT INTO {$_SESSION['ESCCO_DB_PREFIX']}deliverymethod " .
							"(name) " .
							"VALUES " .
							"('$deliverymethod')";
							
					$result = mysql_query($qry);
		        	$deliverymethodid =  mysql_insert_id();
        	
					if (mysql_errno() == 1062) {
						$qry = "SELECT id " .
								"FROM {$_SESSION['ESCCO_DB_PREFIX']}customerteam " .
								"WHERE name = '$deliverymethod'";
						$result = mysql_query($qry);
						
						//Check whether the query was successful or not
						if ($result) {
							while (($member = mysql_fetch_assoc($result))) {
								$deliverymethodid = $member['id'];
							}
						}
					}
		        
					$qry = "INSERT INTO {$_SESSION['ESCCO_DB_PREFIX']}customer 
							(
							owner, customerteamid, accountnumber, name, firstname, lastname, 
							invoiceaddress1, invoiceaddress2, invoiceaddress3, invoicecity, 
							invoicepostcode, email1, telephone1, fax1,
							deliverymethodid, deliveryaddress1, deliveryaddress2, 
							deliveryaddress3, deliverycity, deliverypostcode, email2, telephone2
							)  
							VALUES  
							(
							'$owner', $teamid, '$accountnumber', '$name', '$firstname', '$lastname', 
							'$invoiceaddress1', '$invoiceaddress2', '$invoiceaddress3', '$invoicecity', 
							'$invoicepostcode', '$email1', '$telephone1', '$fax1', 
							$deliverymethodid, '$deliveryaddress1', '$deliveryaddress2', 
							'$deliveryaddress3', '$deliverycity', '$deliverypostcode', '$email2', '$telephone2'
							)";
							
					$result = mysql_query($qry);
        	
					if (mysql_errno() != 1062 && mysql_errno() != 0 ) {
						logError(mysql_error() . " : " .  $qry);
					}
		        }
		    }
		    
		    fclose($handle);
			echo "<h1>" . $row . " downloaded</h1>";
		}
	}
	
	if (! isset($_FILES['customerfile'])) {
?>	
		
<form class="contentform" method="post" enctype="multipart/form-data" onsubmit="return askPassword()">
	<label>Upload customer CSV file </label>
	<input type="file" name="customerfile" id="customerfile" /> 
	
	<br />
	 	
	<div id="submit" class="show">
		<input type="submit" value="Upload" />
	</div>
</form>
<?php
	}
	
	include("system-footer.php"); 
?>