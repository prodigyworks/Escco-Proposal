<?php
	require_once('system-db.php');
	require_once('pdfreport.php');
	require_once("simple_html_dom.php");
	
	class LeadsCallbackReport extends PDFReport {
		function newPage() {
			$this->Image("images/logomain2.png", 245.6, 1);
			$this->Image("images/footer.png", 134, 190);
			
			$size = $this->addText( 10, 13, "Bulk Quotations", 12, 4, 'B') + 5;
			$this->SetFont('Arial','', 6);
				
			$cols = array( 
					"Customer"  => 93,
					"Customer Code"  => 35,
					"Quotation Number"  => 35,
					"User"  => 49,
					"Quotation Date"  => 37,
					"Value"  => 28
				);
			
			$this->addCols($size, $cols);

			$cols = array(
					"Customer"  => "L",
					"Customer Code"  => "L",
					"Quotation Number"  => "L",
					"User"  => "L",
					"Quotation Date"  => "L",
					"Value"  => "R"
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
				$and = "";
				
				if ($startdate != "") {
					$and .= " AND A.metacreateddate >= '$startdate'  ";
				}
				
				if ($enddate != "") {
					$and .= " AND A.metacreateddate <= '$enddate'  ";
				}
				
				if ($userid != "0") {
					$and .= " AND A.takenbyid = $userid   ";
				}
				
				$sql = "SELECT A.*, 
					    B.name AS customername, B.accountnumber, 
					    C.fullname,
						DATE_FORMAT(A.metacreateddate, '%d/%m/%Y %H:%I') AS metacreateddate
						FROM {$_SESSION['ESCCO_DB_PREFIX']}quotation A 
						LEFT OUTER JOIN {$_SESSION['ESCCO_DB_PREFIX']}customer B 
						ON B.id = A.customerid 
						LEFT OUTER JOIN {$_SESSION['ESCCO_DB_PREFIX']}members C 
						ON C.member_id = A.takenbyid 
						WHERE 1 = 1 $and  
						ORDER BY B.name, A.metacreateddate";
				$result = mysql_query($sql);
				
				if ($result) {
					while (($member = mysql_fetch_assoc($result))) {
						$line=array(
								"Customer"  => $member['customername'],
								"Customer Code"  => $member['accountnumber'],
								"Quotation Number"  => getSiteConfigData()->bookingprefix . "-" . sprintf("%06d", $member['id']),
								"User"  => $member['fullname'],
								"Quotation Date"  => $member['metacreateddate'],
								"Value"  => number_format($member['total'], 2)
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