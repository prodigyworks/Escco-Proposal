<?php
	require_once("crud.php");
	
	$crud = new Crud();
	$crud->dialogwidth = 790;
	$crud->title = "Accessories";
	$crud->table = "{$_SESSION['ESCCO_DB_PREFIX']}accessory";
	$crud->sql = "SELECT A.* 
				  FROM  {$_SESSION['ESCCO_DB_PREFIX']}accessory A
				  ORDER BY A.code";
	$crud->columns = array(
			array(
				'name'       => 'id',
				'viewname'   => 'uniqueid',
				'length' 	 => 6,
				'showInView' => false,
				'filter'	 => false,
				'bind' 	 	 => false,
				'editable' 	 => false,
				'pk'		 => true,
				'label' 	 => 'ID'
			),
			array(
				'name'       => 'code',
				'length' 	 => 20,
				'label' 	 => 'Code'
			),
			array(
				'name'       => 'description',
				'length' 	 => 120,
				'type'	 	 => 'BASICTEXTAREA',
				'label' 	 => 'Description'
			),			
			array(
				'name'       => 'imageid',
				'type'		 => 'IMAGE',
				'required'   => false,
				'length' 	 => 35,
				'showInView' => false,
				'label' 	 => 'Image'
			),			
			array(
				'name'       => 'price',
				'length' 	 => 12,
				'datatype'   => 'double',
				'align'		 => 'right',
				'label' 	 => 'Price'
			)
		);
		
	$crud->run();
?>
