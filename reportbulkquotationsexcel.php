<?php
	/** Error reporting */
	error_reporting(0);
	
	/** Include path **/
	/** PHPExcel */
	include 'system-db.php';
	include 'PHPExcel.php';
	include 'PHPExcel/Writer/Excel2007.php';
	
	start_db();
	
	header('Content-type: application/excel');
	header('Content-disposition: attachment; filename="bulkquotes.xlsx');
	
	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();
	
	// Set properties
	$objPHPExcel->getProperties()->setCreator("Kevin Hilton");
	$objPHPExcel->getProperties()->setLastModifiedBy("Kevin Hilton");
	$objPHPExcel->getProperties()->setTitle("Bulk Quotes Report");
	$objPHPExcel->getProperties()->setSubject("Bulk Quotes Report");
	$objPHPExcel->getProperties()->setDescription("Bulk Quotes Report");
	
	$objPHPExcel->setActiveSheetIndex(0);
	
	$startdate = convertStringToDate($_POST['datefrom']);
	$enddate = convertStringToDate($_POST['dateto']);
	$userid = $_POST['userid'];

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
	
	if (! $result) {
		die($sql . " - " . mysql_error());
	}
	
	$row = 1;
	$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial')->setSize(8);
	
	$headerArray = array(	
			'font' => array(		'bold' => true),
			'borders' => array(
		    'allborders' => array(
		      'style' => PHPExcel_Style_Border::BORDER_THIN
		    )
		  )
		);
	
	$styleArray = array(
		  'borders' => array(
		    'allborders' => array(
		      'style' => PHPExcel_Style_Border::BORDER_THIN
		    )
		  )
		);
		
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(72);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(49);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(49);
	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(37);
	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(37);
	
	$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Customer');
	$objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Customer Code');
	$objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Quotation Number');
	$objPHPExcel->getActiveSheet()->SetCellValue('D1', 'User');
	$objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Quotation Date');
	$objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Value');

	while (($member = mysql_fetch_assoc($result))) {
		$row++;
		
		$objPHPExcel->getActiveSheet()->SetCellValue('A' . $row, $member['customername']);
		$objPHPExcel->getActiveSheet()->SetCellValue('B' . $row, $member['accountnumber']);
		$objPHPExcel->getActiveSheet()->SetCellValue('C' . $row, getSiteConfigData()->bookingprefix . "-" . sprintf("%06d", $member['id']));
		$objPHPExcel->getActiveSheet()->SetCellValue('D' . $row, $member['fullname']);
		$objPHPExcel->getActiveSheet()->SetCellValue('E' . $row, $member['metacreateddate']);
		$objPHPExcel->getActiveSheet()->SetCellValue('F' . $row, number_format($member['total'], 2));
	}
	
			
	$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
	$objWriter->save('php://output');
?>
