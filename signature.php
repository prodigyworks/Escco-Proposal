<?php
	function checkSignature() {
		if (isset($_POST['output'])) {
			require_once('signature-to-image.php');
			
			try {
				$img = null;
				
				if (isset($_POST['output']) && $_POST['output'] != "") {
					$img = sigJsonToImage($_POST['output']);
				
				} else {
					// Create the image
					$img = imagecreatetruecolor(400, 30);
					
					// Create some colors
					$white = imagecolorallocate($img, 255, 255, 255);
					$grey = imagecolorallocate($img, 128, 128, 128);
					$black = imagecolorallocate($img, 0, 0, 0);
					imagefilledrectangle($img, 0, 0, 399, 29, $white);
					
					// The text to draw
					$text = $_POST['name'];
					// Replace path by your own font path
					$font = 'build/journal.ttf';
					
					// Add some shadow to the text
					imagettftext($img, 20, 0, 11, 21, $grey, $font, $text);
					
					// Add the text
					imagettftext($img, 20, 0, 10, 20, $black, $font, $text);
					
					// Using imagepng() results in clearer text compared with imagejpeg()
				}
				
				ob_start();
				imagepng($img);
				$imgstring = ob_get_contents(); 
		        ob_end_clean();
				
				$escimgstring = mysql_escape_string($imgstring);
				$id = $_POST['signatureid'];
				
				$qry = "INSERT INTO {$_SESSION['ESCCO_DB_PREFIX']}images  
						( 
						mimetype, name, image, createddate 
						)  
						VALUES  
						( 
						'image/png', 'Signature for proposal $id', '$escimgstring', NOW() 
						)";
				$result = mysql_query($qry);
				$imageid = mysql_insert_id();
				
				file_put_contents("uploads/signature_" . $imageid . ".png", $imgstring);
				
				if (! $result) {
					logError($qry . " - " . mysql_error());
				}
				
				$qry = "UPDATE {$_SESSION['ESCCO_DB_PREFIX']}quotation SET 
						signeddatetime = NOW(), 
						signatureid = $imageid  
						WHERE id = $id";
				$result = mysql_query($qry);
				
				if (! $result) {
					logError($qry . " - " . mysql_error());
				}
				
				$qry = "SELECT DATE_FORMAT(A.orderdate, '%d/%m/%Y') AS orderdate, B.name 
						FROM {$_SESSION['ESCCO_DB_PREFIX']}quotation A 
						INNER JOIN {$_SESSION['ESCCO_DB_PREFIX']}customers B 
						ON B.id = A.customerid 
						WHERE A.quotation = $id";
				
				$result = mysql_query($qry);
				
				//Check whether the query was successful or not
				if ($result) {
					while (($member = mysql_fetch_assoc($result))) {
						$customer = $member['name'];
						$stock = $member['stockname'];
						$serial = $member['serialnumber'];
						$orderdate = $member['orderdate'];
				
						$details = "<p>Proposal from customer: $customer has been signed.</p>";
						$details .= "<p>Order : $orderdate</p>";
						
				    	sendUserMessage(getLoggedOnMemberID(), "Proposals note signed", $details);
				    	sendRoleMessage("SIGNATURE", "Proposals note signed", $details);
					}
				}
				
				
			} catch (Exception $e) {
				logError("Signing image: " . $e->getMessage());
			}

		}
	}
	
	function addSignatureForm() {
	?>
		  <link rel="stylesheet" href="build/jquery.signaturepad.css">
		  <!--[if lt IE 9]><script src="build/flashcanvas.js"></script><![endif]-->
		  <script src="build/jquery.signaturepad.min.js"></script>
		  <script src="build/json2.min.js"></script>
			  <form method="post" action="" class="sigPad">
			    <label for="name">Print your name</label>
			    <input type="text" name="name" id="name" class="name">
			    <p class="typeItDesc">Review your signature</p>
			    <p class="drawItDesc">Draw your signature</p>
			    <ul class="sigNav">
			      <li class="typeIt"><a href="#type-it" class="current">Type It</a></li>
			      <li class="drawIt"><a href="#draw-it" >Draw It</a></li>
			      <li class="clearButton"><a href="#clear">Clear</a></li>
			    </ul>
			    <div class="sig sigWrapper">
			      <div class="typed"></div>
			      <canvas class="pad" width="198" height="55"></canvas>
			      <input type="hidden" name="output" class="output">
			      <input type="hidden" id="signatureid" name="signatureid">
			    </div>
			    <button type="submit">I accept the terms of this agreement.</button>
			  </form>
		<script>
<?php
			if (isset($_POST['output'])) {
?>
			$(document).ready(function() {
					$("#messageDialog").dialog({
							autoOpen: true,
							modal: true,
							title: "Signed",
							buttons: {
								Ok: function() {
									$(this).dialog("close");
								}
							}
						});
				});
<?php
			}
?>				
		</script>
<?php
		if (isset($_POST['output'])) {
?>
			<div id="messageDialog" class="modal">
				Many thanks for signing the proposal.
			</div>
<?php			
				
		}
	}
?>
