<?php
	require_once("crud.php");
	
	class ProductCrud extends Crud {
		
		/* Post header event. */
		public function postHeaderEvent() {
			createDocumentLink();
		}
		
		public function postScriptEvent() {
?>
			function editDocuments(node) {
				viewDocument(node, "addproductdocument.php", node, "productdocs", "productid");
			}
	
<?php			
		}
	}
	
	$crud = new ProductCrud();
	$crud->dialogwidth = 790;
	$crud->title = "Products";
	$crud->table = "{$_SESSION['ESCCO_DB_PREFIX']}product";
	$crud->sql = "SELECT A.* 
				  FROM  {$_SESSION['ESCCO_DB_PREFIX']}product A
				  ORDER BY A.productcode";
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
				'name'       => 'productcode',
				'length' 	 => 20,
				'label' 	 => 'Product Code'
			),
			array(
				'name'       => 'name',
				'length' 	 => 105,
				'label' 	 => 'Name'
			),			
			array(
				'name'       => 'description',
				'showInView' => false,
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

	$crud->subapplications = array(
			array(
				'title'		  => 'Documents',
				'imageurl'	  => 'images/document.gif',
				'script' 	  => 'editDocuments'
			)
		);
		
	$crud->run();
?>
