<?php 
	require_once("system-db.php");
	
	start_db();
	
	require_once('signature.php');
	
	checkSignature();
	
	require_once("system-header.php"); 
	
	addSignatureForm();
?>
<script>
	$(document).ready(
			function() {
		      	$('.sigPad').signaturePad();
			}
		);
		
	function sign(pk) {
		$("#signatureid").val(pk);
		$(".sigPad").fadeIn();
    } 	
</script>
<table width='100%'>
	<tr valign=top>
		<td style='border: 1px solid #CCCCCC; padding: 10px'>
			<h4>Awaiting Signature</h4>
			<table  width='770px' class='grid view'>
				<thead>
					<tr>
						<td width='80px'>Quotation</td>
						<td>Table</td>
						<td width='75px'>Order Date</td>
						<td>Your Order Number</td>
						<td>&nbsp;</td>
					</tr>
				</thead>
			<?php
				$customerid = $_SESSION['ESCCO_SESS_CUSTOMER_ID'];
				$sql = 
					   "SELECT A.id, B.name, A.yourordernumber, DATE_FORMAT(A.orderdate, '%d/%m/%Y') AS orderdate
					    FROM {$_SESSION['ESCCO_DB_PREFIX']}quotation A 
					    INNER JOIN {$_SESSION['ESCCO_DB_PREFIX']}product B
					    ON B.id = A.productid 
					    WHERE (A.signatureid = 0 OR A.signatureid IS NULL) 
						AND A.customerid = $customerid  
						ORDER BY A.id";
				
				$result = mysql_query($sql);
				if (! $result) die("Error: " . mysql_error());
				
				//Check whether the query was successful or not
				if ($result) {
					while (($member = mysql_fetch_assoc($result))) {
						echo "<tr>\n";
						echo "<td>" . getSiteConfigData()->bookingprefix . sprintf("%06d", $member['id']). "</td>";
						echo "<td>" . $member['name'] . "</td>";
						echo "<td>" . $member['orderdate'] . "</td>";
						echo "<td>" . $member['yourordernumber'] . "</td>";
						echo "<td><img title='Click to sign' src='images/stock.png' onclick='sign(" . $member['id'] . ")'/></td>";
						echo "</tr>\n";
					}
					
				} else {
					logError($sql . " - " . mysql_error());
				}
			?>
			</table>
		</td>
		<td style='border: 1px solid #CCCCCC; padding: 10px'>
			<div class="welcome"	>
				<div class="fright welcome">
				<?php 
					echo getSiteConfigData()->welcometext; 
				?>
			</div>
		</td>
	</tr>
</table>
<p>Please click on the icon above to sign your proposal</p>
<?php include("system-footer.php"); ?>
