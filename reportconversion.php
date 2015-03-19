<?php
	require_once('system-db.php');
	require_once('pdfreport.php');
	require_once("simple_html_dom.php");
	
	class LeadsCallbackReport extends PDFReport {
		function newPage() {
			$this->Image("images/logomain2.png", 245.6, 1);
			$this->Image("images/footer.png", 134, 190);
			
			$size = $this->addText( 10, 13, "Conversion Statistics : " . GetUserName($_POST['userid']), 12, 4, 'B') + 5;
			$this->SetFont('Arial','', 6);
				
			$cols = array( 
					"Customer"  => 72,
					"Customer Code"  => 35,
					"Quotation Number"  => 39,
					"Quotation Date"  => 37,
					"Conversion Date"  => 37,
					"Time Taken"  => 29,
					"Total"  => 28
				);
			
			$this->addCols($size, $cols);

			$cols = array(
					"Customer"  => "L",
					"Customer Code"  => "L",
					"Quotation Number"  => "L",
					"Quotation Date"  => "L",
					"Conversion Date"  => "L",
					"Time Taken"  => "L",
					"Total"  => "R"
				);
			$this->addLineFormat( $cols);
			$this->SetY(30);
		}
		
		function AddPage($orientation='', $size='') {
			parent::AddPage($orientation, $size);
			
			$this->newPage();
		}
		
		function __construct($orientation, $metric, $size, $startdate, $enddate, $userid) {
			$dynamicY = 0;
			
	        parent::__construct($orientation, $metric, $size);
	        
	        $this->SetAutoPageBreak(true, 30);
	        
			$this->AddPage();
			
			try {
				$sql = "SELECT A.*, 
					    B.name AS customername, B.accountnumber, 
						DATE_FORMAT(A.metacreateddate, '%d/%m/%Y %H:%I') AS metacreateddate, 
						DATE_FORMAT(A.converteddatetime, '%d/%m/%Y %H:%I') AS converteddatetime,
						TIMEDIFF(A.converteddatetime, A.metacreateddate) as diff
						FROM {$_SESSION['ESCCO_DB_PREFIX']}quotation A 
						LEFT OUTER JOIN {$_SESSION['ESCCO_DB_PREFIX']}customer B 
						ON B.id = A.customerid 
						LEFT OUTER JOIN {$_SESSION['ESCCO_DB_PREFIX']}members C 
						ON C.member_id = A.takenbyid 
						WHERE A.takenbyid = $userid 
						AND A.metacreateddate >= '$startdate' 
						AND A.metacreateddate <= '$enddate'  
						ORDER BY A.metacreateddate DESC";
				$result = mysql_query($sql);
				
				if ($result) {
					while (($member = mysql_fetch_assoc($result))) {
						$diff = $member['diff'];
						$conversiondate = $member['converteddatetime'];
						
						if (substr($diff, 0, 1) == "-") {
							$diff = " ";
						}
						
						if (substr($conversiondate, 0, 2) == "00") {
							$conversiondate = " ";
						}
						
						$line=array(
								"Customer"  => $member['customername'],
								"Customer Code"  => $member['accountnumber'],
								"Quotation Number"  => getSiteConfigData()->bookingprefix . "-" . sprintf("%06d", $member['id']),
								"Quotation Date"  => $member['metacreateddate'],
								"Conversion Date"  => $conversiondate,
								"Time Taken"  => $diff,
								"Total"  => number_format($member['total'], 2)
							);
							
						$this->addLine( $this->GetY(), $line );
					}
					
				} else {
					logError($sql . " - " . mysql_error());
				}
				
			} catch (Exception $e) {
				logError($e->getMessage());
			}
		}
	}
	
	start_db();
	
	$pdf = new LeadsCallbackReport( 'L', 'mm', 'A4', convertStringToDate($_POST['datefrom']), convertStringToDate($_POST['dateto']), $_POST['userid']);
	$pdf->Output();
?>