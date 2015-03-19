<?php
	require_once('system-db.php');
	require_once('pdfreport.php');
	require_once("simple_html_dom.php");
	
	class QuoteReport extends PDFReport {
		private $headermember = null;
		
		function AddPage($orientation='', $size='') {
			parent::AddPage($orientation, $size);
			
			$this->Image("images/logomain.png", 70, 10);
			$this->Image("images/footer.png", 0, 242);
			
			$this->SetY(70);
		}
		
		function __construct($orientation, $metric, $size, $id) {
			start_db();
				
	        parent::__construct($orientation, $metric, $size);
	        
	        $this->AddPage();
	        
			try {
				$sql = "SELECT A.*, DATE_FORMAT(A.orderdate, '%d/%m/%Y') AS orderdate,
						B.name AS customername, B.accountnumber, B.invoiceaddress1, B.invoiceaddress2, B.invoiceaddress3, 
						B.invoicecity, B.invoicepostcode, B.deliveryaddress1, B.deliveryaddress2, 
						B.deliveryaddress3, B.deliverycity, B.deliverypostcode, B.firstname, B.lastname,
						C.signatureid, C.fullname AS takenbyname, D.name AS productname, D.description
					    FROM  {$_SESSION['ESCCO_DB_PREFIX']}quotation A
					    INNER JOIN  {$_SESSION['ESCCO_DB_PREFIX']}customer B
					    ON B.id = A.customerid
					    INNER JOIN  {$_SESSION['ESCCO_DB_PREFIX']}members C
					    ON C.member_id = A.takenbyid
					    INNER JOIN  {$_SESSION['ESCCO_DB_PREFIX']}product D
					    ON D.id = A.productid
					    WHERE A.id = $id
					    ORDER BY A.id DESC";
				$result = mysql_query($sql);
				
				if ($result) {
					while (($this->headermember = mysql_fetch_assoc($result))) {
						$address = $member['invoiceaddress1'];
						
						if (trim($this->headermember['invoiceaddress2']) != "") {
							$address .= "\n" . $this->headermember['invoiceaddress2'];
						}
						
						if (trim($this->headermember['invoiceaddress3']) != "") {
							$address .= "\n" . $this->headermember['invoiceaddress3'];
						}
						
						if (trim($this->headermember['invoicecity']) != "") {
							$address .= "\n" . $this->headermember['invoicecity'];
						}
						
						if (trim($this->headermember['invoicepostcode']) != "") {
							$address .= "\n" . $this->headermember['invoicepostcode'];
						}
						
				        $this->addText(-1, 70, "PROPOSAL", 13, 4, 'B');
				        $this->addText(-1, 80, "For", 10, 3, '');
			            $this->addText(-1, 90, $this->headermember['customername'], 15, 4, 'B');
			            
			            $this->SetY(100);
			            
						if (trim($this->headermember['invoiceaddress1']) != "") {
				            $this->addText(-1, $this->GetY(), $this->headermember['invoiceaddress1'], 8, 4, '');
						}
			            
						if (trim($this->headermember['invoiceaddress2']) != "") {
				            $this->addText(-1, $this->GetY(), $this->headermember['invoiceaddress2'], 8, 4, '');
						}
									            
						if (trim($this->headermember['invoiceaddress3']) != "") {
				            $this->addText(-1, $this->GetY(), $this->headermember['invoiceaddress3'], 8, 4, '');
						}
									            
						if (trim($this->headermember['invoicecity']) != "") {
				            $this->addText(-1, $this->GetY(), $this->headermember['invoicecity'], 8, 4, '');
						}
															            
						if (trim($this->headermember['invoicepostcode']) != "") {
				            $this->addText(-1, $this->GetY(), $this->headermember['invoicepostcode'], 8, 4, '');
						}

                        $this->addText(-1, 140, "For the attention of", 11, 3, '');
			            $this->addText(-1, 150, $this->headermember['firstname'] . " " . $this->headermember['lastname'], 13, 4, 'B');
			            $this->addText(-1, 165, date("D, M, Y"), 10, 4);
			            $this->addText(-1, 175, "Our Ref: " . getSiteConfigData()->bookingprefix . sprintf("%06d", $this->headermember['id']), 10, 4, 'B');
			            
			            $this->Rect(30, 60, $this->w - 60, $this->GetY() - 50);

			            /* Page 2 */
			            $this->AddPage();
			            
				        $this->addText(169, 50, "PROPOSAL", 13, 4, 'B');
				        $this->addText(15, 65, "RE:PROPOSAL FOR A CNC PLASMA CUTTING SYSTEM", 9, 4, 'BU');
				        $this->addText(15, 71, "Ref: " . getSiteConfigData()->bookingprefix . sprintf("%06d", $this->headermember['id']), 9, 4, '');

				        $this->addText(15, 81, "Dear " . $this->headermember['firstname'], 9, 4, '');
				        $this->addText(15, 91, "Further to the discussion regarding our range of CNC robotic Plasma cutting systems, we have pleasure in submitting our proposal as follows:-", 9, 4, '', 180);
				        $this->addText(-1, 102, $this->headermember['productname'], 8, 4, 'B');
				        
				        $this->addText(15, $this->GetY() + 2, "As a local company based in Hereford, we offer first class after sales service. All new machines come with a comprehensive parts and labour warranty backed with spare parts and consumables held in stock in Hereford. We have our own service engineers available, should the need arise, giving you a fully comprehensive local, personalised service ensuring the maximum productivity for your company.", 9, 4, '', 180);
				        $this->addText(15, $this->GetY() + 2, "We trust this satisfies your requirements. I will contact you shortly to discuss this further however if you have any further queries please do not hesitate to contact the undersigned. We appreciate you taking the time to offer us this opportunity to quote and extend our thanks.", 9, 4, '', 180);
				        $this->addText(15, $this->GetY() + 8, "Yours sincerely", 9, 4, '', 180);
				        
				        if ($this->headermember['signatureid'] != null && 
				        	$this->headermember['signatureid'] != 0) {
					        $this->DynamicImage($this->headermember['signatureid'], 15, $this->GetY() + 4);
				        }
				        
				        $this->addText(15, $this->GetY() + 14, $this->headermember['takenbyname'], 9, 4, '', 180);
				        
			            /* Page 3 */
			            $this->AddPage();
				        $this->addText(15, 60, "OPTION 1", 10, 4, 'BU');
			            $this->addText(15, 68, $this->headermember['description'], 8, 4, '', 180);
			            $this->addText(15, $this->GetY() + 3, "The following list shows some of the features that benefit our package:-", 8, 4, 'BI');
			            

			            $this->addText(15, $this->GetY() + 3, "•	110/230 volt, 10 amp (supply to table)", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	Hardened V Linear guides (X and Y axis (hardened Linear Thompson Ball slides on all axis", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	5mm pitch hardened ball-screw (Z axis) (5mm pitch belt drive Z axis with torch saver feature)", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	Axis driven by powerful close loop 300 Watt Servo Motors with encoder feedback, giving smooth motion at low speeds with no resonance and a high traverse speed", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	Speed range – 1 to 15m/min", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	Twin-side rack and pinion drive", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	32 bit motion control DSP for fast accurate motion trajectory control", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	High speed precision arc voltage control", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	USB motion controller", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	Adjustable levelling feet", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	Computer (minimum specification) Intel I3 processor Windows 8", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	Dual core cpu Intel I3 processor", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	250GB hard drive", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	2GB ram", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	DVD ROM", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	15” monitor ", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	CAD program: Draft Sight", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	CAM program: Sheet CAM", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	12 months online and telephone support included", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	32 bit DSP motion control with USB motion interface", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	Very easy to use intuitive CNC system with full plasma height sense integration", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	Inbuilt generic flexi shapes", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	DXF, Gcode, Word address shape input", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	Nesting on screen", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	Side mounted rack and slides for easy plate loading", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	Precision planetary gearboxes for backlash free motion", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	Torch mounted laser pointer for easy job setup", 8, 4, '');
						$this->addText(15, $this->GetY(), "•	Comes complete with a pre-programmed PC ", 8, 4, '');
			            
						$this->addText(-1, $this->GetY() + 6, "SPECIAL NET PRICE, INCLUDING DELIVERY AND ONE DAYS TRAINING: £" . number_format($this->headermember['price'], 2), 8, 4, 'B');
						
				        /* Page 4 */
			            $this->AddPage();
				        $this->addText(15, 60, "ACCESSORIES", 10, 4, 'BU');
						
						$sql = "SELECT A.*, B.code, B.description
								FROM {$_SESSION['ESCCO_DB_PREFIX']}quotationitem A 
								INNER JOIN {$_SESSION['ESCCO_DB_PREFIX']}accessory B 
								ON B.id = A.productid 
								WHERE A.quoteid = $id 
								ORDER BY A.id";
						$itemresult = mysql_query($sql);
						
						if ($itemresult) {
							while (($itemmember = mysql_fetch_assoc($itemresult))) {
								$this->SetFont ( 'Arial', 'B', 8);
								
								$y = $this->GetY() + 4;
								$wi = $this->GetStringWidth($itemmember['code']);
								
			            		$this->addText(15, $y, $itemmember['code'], 8, 4, 'B');
			            		$this->addText(15 + $wi, $y, " - " . $itemmember['description'], 8, 4, '', 180 - $wi);

			            		$this->addText(15, $this->GetY() + 2, "SPECIAL PRICE : £" . number_format($itemmember['price'], 2), 8, 4, 'B');
							}
							
						} else {
							logError($qry . " - " . mysql_error());
						}
						
				        $this->addText(15, $this->GetY() + 6, "TERMS & CONDITIONS", 10, 4, 'BU');
				        $this->WriteHTML(15, $this->GetY() , getSiteConfigData()->termsandconditions);
					}
					
				} else {
					logError($sql . " - " . mysql_error());
				}
				
			} catch (Exception $e) {
				logError($e->getMessage());
			}
			
			$this->AliasNbPages();
		}
	}
?>