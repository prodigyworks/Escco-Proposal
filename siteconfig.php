<?php 
	require_once("system-header.php"); 
	require_once("tinymce.php"); 
?>

<!--  Start of content -->
<?php
	if (isset($_POST['domainurl'])) {
		$runscheduledays = mysql_escape_string($_POST['runscheduledays']);
		$domainurl = mysql_escape_string($_POST['domainurl']) ;
		$emailfooter = mysql_escape_string($_POST['emailfooter']);
		$address = mysql_escape_string($_POST['address']);
		$bookingprefix = $_POST['bookingprefix'];
		$productcodeprefix = $_POST['productcodeprefix'];
		$productgroupprefix = $_POST['productgroupprefix'];
		$welcometext = $_POST['welcometext'];
		$vatrate = $_POST['vatrate'];
		$termsandconditions = mysql_escape_string($_POST['termsandconditions']);
		
		$qry = "UPDATE {$_SESSION['ESCCO_DB_PREFIX']}siteconfig SET " .
				"domainurl = '$domainurl', " .
				"vatrate = $vatrate, " .
				"address = '$address', " .
				"bookingprefix = '$bookingprefix', " .
				"productcodeprefix = '$productcodeprefix', " .
				"productgroupprefix = '$productgroupprefix', " .
				"welcometext = '$welcometext', " .
				"termsandconditions = '$termsandconditions', " .
				"runscheduledays = '$runscheduledays', " .
				"emailfooter = '$emailfooter', metamodifieddate = NOW(), metamodifieduserid = " . getLoggedOnMemberID() . "";
		$result = mysql_query($qry);
		
	   	if (! $result) {
	   		logError("UPDATE {$_SESSION['ESCCO_DB_PREFIX']}siteconfig:" . $qry . " - " . mysql_error());
	   	}
	   	
	   	unset($_SESSION['ESCCO_SITE_CONFIG']);
	}
	
	$qry = "SELECT *, DATE_FORMAT(lastschedulerun, '%d/%m/%Y') AS lastschedulerun FROM {$_SESSION['ESCCO_DB_PREFIX']}siteconfig";
	$result = mysql_query($qry);
	
	if ($result) {
		while (($member = mysql_fetch_assoc($result))) {
?>
<form id="contentForm" name="contentForm" method="post" class="entryform">
	<label>Domain URL</label>
	<input required="true" type="text" class="textbox90" id="domainurl" name="domainurl" value="<?php echo $member['domainurl']; ?>" />

	<label>VAT Rate</label>
	<input required="true" type="text" id="vatrate" name="vatrate" value="<?php echo number_format($member['vatrate'], 2); ?>" />

	<label>Run Alert Schedule Cycle (Days)</label>
	<input required="true" type="text" class="textbox20" id="runscheduledays" name="runscheduledays" value="<?php echo $member['runscheduledays']; ?>" />

	<label>Last Schedule Date Run</label>
	<input readonly type="text" class="textbox20" id="lastschedulerun" name="lastschedulerun" value="<?php echo $member['lastschedulerun']; ?>" />
	
	<label>E-mail Footer</label>
	<textarea id="emailfooter" name="emailfooter" rows="15" cols="60" style="height:340px;width: 340px" class="tinyMCE"></textarea>

	<label>Address</label>
	<textarea id="address" name="address" rows="5" cols="60"></textarea>

	<label>Quotation Prefix</label>
	<input type="text" id="bookingprefix" name="bookingprefix" value="<?php echo $member['bookingprefix']; ?>" />

	<label>Product Code Prefix</label>
	<input type="text" id="productcodeprefix" name="productcodeprefix" value="<?php echo $member['productcodeprefix']; ?>" />

	<label>Product Group Prefix</label>
	<input type="text" id="productgroupprefix" name="productgroupprefix" value="<?php echo $member['productgroupprefix']; ?>" />
	
	<label>Welcome Text</label>
	<textarea id="welcometext" name="welcometext" rows="15" cols="60" style="height:340px;width: 340px" class="tinyMCE"></textarea>
	
	<label>Terms and Conditions</label>
	<textarea id="termsandconditions" name="termsandconditions" rows="15" cols="60" style="height:340px;width: 340px" class="tinyMCE"></textarea>
	
	<br>
	<br>
	<span class="wrapper"><a class='link1' href="javascript:if (verifyStandardForm('#contentForm')) $('#contentForm').submit();"><em><b>Update</b></em></a></span>
</form>
<script type="text/javascript">
	$(document).ready(function() {
			$("#termsandconditions").val("<?php echo escape_notes($member['termsandconditions']); ?>");
			$("#welcometext").val("<?php echo escape_notes($member['welcometext']); ?>");
			$("#emailfooter").val("<?php echo escape_notes($member['emailfooter']); ?>");
			$("#address").val("<?php echo escape_notes($member['address']); ?>");
		});
</script>
	<?php
			}
		}
	?>
<!--  End of content -->

<?php include("system-footer.php"); ?>
