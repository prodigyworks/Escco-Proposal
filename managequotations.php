<?php
	require_once("crud.php");
	
	function accept() {
		$id = $_POST['quotationid'];
		$sql = "UPDATE {$_SESSION['ESCCO_DB_PREFIX']}quotation SET
				status = 1,
				converteddatetime = NOW()
				WHERE id = $id";
		$result = mysql_query($sql);
			
		if (! $result) {
			logError($sql . " - " . mysql_error());
		}
	}
	
	function undo() {
		$id = $_POST['quotationid'];
		$sql = "UPDATE {$_SESSION['ESCCO_DB_PREFIX']}quotation SET
				status = 0,
				converteddatetime = null
				WHERE id = $id";
		$result = mysql_query($sql);
			
		if (! $result) {
			logError($sql . " - " . mysql_error());
		}
	}
	
	class QuotationCrud extends Crud {
		
		/* Post header event. */
		public function postHeaderEvent() {
			createConfirmDialog("confirmacceptdialog", "Confirm acceptance ?", "confirmaccept");
			createConfirmDialog("confirmundodialog", "Confirm undo ?", "confirmundo");
			createConfirmDialog("confirmRemoveDialog", "Confirm removal ?", "confirmRemoval");
			createDocumentLink();
		}
		
		public function afterInsertRow() {
?>
			var status = rowData['status'];

			if (status == "1") {
				$(this).jqGrid('setRowData', rowid, false, { color: '#0000FF' });
		   	}
<?php
		}
		
		public function postUpdateEvent($quoteid) {
			$items = json_decode($_POST['item_serial'], true);
			$memberid = getLoggedOnMemberID();
			
			$qry = "DELETE FROM {$_SESSION['ESCCO_DB_PREFIX']}quotationitem 
					WHERE quoteid = $quoteid";
			
			$result = mysql_query($qry);
			
			if (! $result) {
				logError($qry . " - " . mysql_error());
			}
			
			foreach ($items as $k=>$item) {
				$qty = $item['quantity'];
				$vatrate = $item['vatrate'];
				$linetotal = $item['linetotal'];
				$vat = $item['vat'];
				$unitprice = $item['price'];
				$productid = $item['productid'];
				
				$qry = "INSERT INTO {$_SESSION['ESCCO_DB_PREFIX']}quotationitem 
						(quoteid, quantity, price, vatrate, vat, linetotal, 
						productid, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) 
						VALUES 
						($quoteid, '$qty', '$unitprice', $vatrate, '$vat', $linetotal, 
						'$productid', NOW(), $memberid , NOW(), $memberid)";
				
				$result = mysql_query($qry);
				
				if (! $result) {
					logError($qry . " - " . mysql_error());
				}
			}
			
			$sql = "UPDATE {$_SESSION['ESCCO_DB_PREFIX']}quotation SET
					status = 0,
					converteddatetime = null,
					metacreateddate = NOW()
					WHERE id = $quoteid";
			$result = mysql_query($sql);
			
			if (! $result) {
				logError($sql . " - " . mysql_error());
			}
		}
		
		public function postInsertEvent() {
			$quoteid = mysql_insert_id();
			$items = json_decode($_POST['item_serial'], true);
			$memberid = getLoggedOnMemberID();
			
			foreach ($items as $k=>$item) {
				$qty = $item['quantity'];
				$vatrate = $item['vatrate'];
				$linetotal = $item['linetotal'];
				$vat = $item['vat'];
				$unitprice = $item['price'];
				$productid = $item['productid'];
				
				$qry = "INSERT INTO {$_SESSION['ESCCO_DB_PREFIX']}quotationitem 
						(quoteid, quantity, price, vatrate, vat, linetotal, 
						productid, metacreateddate, metacreateduserid, metamodifieddate, metamodifieduserid) 
						VALUES 
						($quoteid, '$qty', '$unitprice', $vatrate, '$vat', $linetotal, 
						'$productid', NOW(), $memberid , NOW(), $memberid)";
				
				$result = mysql_query($qry);
				
				if (! $result) {
					logError($qry . " - " . mysql_error());
				}
			}
			
		}
		
		public function postAddScriptEvent() {
?>
			$("#deliverycharge").val("0.00");
			$("#discount").val("0.00");
			$("#total").val("0.00");
			$("#orderdate").val("<?php echo date("d/m/Y"); ?>");
			$("#takenbyid").val("<?php echo getLoggedOnMemberID(); ?>");
			itemArray = [];
			
			populateTable();
<?php 
		}
		
		public function postEditScriptEvent() {
?>
				callAjax(
						"finddata.php", 
						{ 
							sql: "SELECT A.* FROM <?php echo $_SESSION['ESCCO_DB_PREFIX'];?>customer A WHERE A.id = " + $("#customerid").val()
						},
						function(data) {
							if (data.length > 0) {
								var node = data[0];
								var invoiceaddress = "";
								var deliveryaddress = "";
								
								if (node.deliveryaddress1 != "") deliveryaddress += node.deliveryaddress1+ "\n";
								if (node.deliveryaddress2!= "") deliveryaddress += node.deliveryaddress2+ "\n";
								if (node.deliveryaddress3!= "") deliveryaddress += node.deliveryaddress3+ "\n";
								if (node.deliverycity!= "") deliveryaddress += node.deliverycity+ "\n";
								if (node.deliverypostcode!= "") deliveryaddress += node.deliverypostcode+ "\n";
								
								if (node.invoiceaddress1!= "") invoiceaddress += node.invoiceaddress1+ "\n";
								if (node.invoiceaddress2!= "") invoiceaddress += node.invoiceaddress2+ "\n";
								if (node.invoiceaddress3!= "") invoiceaddress += node.invoiceaddress3+ "\n";
								if (node.invoicecity!= "") invoiceaddress += node.invoicecity+ "\n";
								if (node.invoicepostcode!= "") invoiceaddress += node.invoicepostcode+ "\n";
								
								if (deliveryaddress == "") {
									deliveryaddress = invoiceaddress;
								}
								
								$("#accountcode").val(node.accountnumber);
								$("#invoiceaddress").val(invoiceaddress);
								$("#deliveryaddress").val(deliveryaddress);
							}
						},
						false
					);
			
			callAjax(
					"finddata.php", 
					{ 
						sql: "SELECT A.*, B.description FROM <?php echo $_SESSION['ESCCO_DB_PREFIX'];?>quotationitem A LEFT OUTER JOIN <?php echo $_SESSION['ESCCO_DB_PREFIX'];?>accessory B ON B.id = A.productid WHERE A.quoteid = " + currentCrudID + " ORDER BY B.description"
					},
					function(data) {
						itemArray = data;
						
						populateTable(data);
					},
					false
				);
<?php 
		}
			
		public function editScreenSetup() {
			include("quoteform.php");
		}
		
		public function postScriptEvent() {
?>
			var currentID = 0;
			var currentItem = -1;
			var itemArray = [];
			
			function customerid_onchange() {
				callAjax(
						"finddata.php", 
						{ 
							sql: "SELECT A.* FROM <?php echo $_SESSION['ESCCO_DB_PREFIX'];?>customer A WHERE A.id = " + $("#customerid").val()
						},
						function(data) {
							if (data.length > 0) {
								var node = data[0];
								var invoiceaddress = "";
								var deliveryaddress = "";
								
								if (node.deliveryaddress1 != "") deliveryaddress += node.deliveryaddress1+ "\n";
								if (node.deliveryaddress2!= "") deliveryaddress += node.deliveryaddress2+ "\n";
								if (node.deliveryaddress3!= "") deliveryaddress += node.deliveryaddress3+ "\n";
								if (node.deliverycity!= "") deliveryaddress += node.deliverycity+ "\n";
								if (node.deliverypostcode!= "") deliveryaddress += node.deliverypostcode+ "\n";
								
								if (node.invoiceaddress1!= "") invoiceaddress += node.invoiceaddress1+ "\n";
								if (node.invoiceaddress2!= "") invoiceaddress += node.invoiceaddress2+ "\n";
								if (node.invoiceaddress3!= "") invoiceaddress += node.invoiceaddress3+ "\n";
								if (node.invoicecity!= "") invoiceaddress += node.invoicecity+ "\n";
								if (node.invoicepostcode!= "") invoiceaddress += node.invoicepostcode+ "\n";
								
								if (deliveryaddress == "") {
									deliveryaddress = invoiceaddress;
								}
								
								$("#accountcode").val(node.accountnumber);
								$("#invoiceaddress").val(invoiceaddress);
								$("#deliveryaddress").val(deliveryaddress);
							}
						},
						false
					);
			}
			
			function total_onchange() {
				calculate_total();
			}
			
			function calculate_total() {
				var total;
				var deliverycharge;
				var discount;
				
				deliverycharge = parseFloat($("#deliverycharge").val());
				discount = parseFloat($("#discount").val());
				
				total = parseFloat($("#total").val());
				total -= deliverycharge;
				
				if (total < 0) {
					total = 0;
				}
				
				total -= (total * (discount) / 100);
				
				$("#discount").val(new Number(discount).toFixed(2));
				$("#deliverycharge").val(new Number(deliverycharge).toFixed(2));
				$("#total").val(new Number(total).toFixed(2));
			}
			
			function productid_onchange() {
				callAjax(
						"finddata.php", 
						{ 
							sql: "SELECT A.price, A.code FROM <?php echo $_SESSION['ESCCO_DB_PREFIX'];?>accessory A WHERE A.id = " + $("#item_accessoryid").val()
						},
						function(data) {
							if (data.length > 0) {
								var node = data[0];
								
								/* Default to unit price. */
								$("#item_unitprice").val(new Number(node.price).toFixed(2)).trigger("change");
								$("#item_productcode").val(node.code);
							}
						}
					);
			}
			
			function qty_onchange(node) {
				var qty = parseInt($("#item_quantity").val());
				var unitprice = parseFloat($("#item_unitprice").val());
				var vatrate = parseFloat($("#item_vatrate").val());

				if (isNaN(unitprice)) {
					unitprice = 0;
				}
				
				if (isNaN(vatrate)) {
					vatrate = 0;
				}
				
				if (isNaN(qty)) {
					qty = 0;
				}
				
				var total = parseFloat(qty * unitprice);
				var vat = total * (vatrate / 100);
				
				total += vat;
				
				$("#item_vatrate").val(new Number(vatrate).toFixed(2));
				$("#item_vat").val(new Number(vat).toFixed(2));
				$("#item_unitprice").val(new Number(unitprice).toFixed(2));
				$("#item_quantity").val(new Number(qty).toFixed(0));
				$("#item_linetotal").val(new Number(total).toFixed(2));
			}
			
			function printQuote(id) {
				window.open("quotereport.php?id=" + id);
			}
			
			function populateTable(data) {
				var total = 0;
				var html = "<TABLE width='100%' class='grid list'><THEAD><?php createHeader(); ?></THEAD>";
				
				if (data != null) {
					data.sort(
							function(a, b) {
							    if(a.description < b.description) return -1;
							    if(a.description > b.description) return 1;
							    
							    return 0;
							}
						);
				}
										
				$("#item_serial").val(JSON.stringify(data));
				
				if (data != null) {
					for (var i = 0; i < data.length; i++) {
						var node = data[i];
						
						if (node.description != null) {
							html += "<TR>";
							html += "<TD><img src='images/edit.png'  title='Edit item' onclick='editItem(" + i + ")' />&nbsp;<img src='images/delete.png'  title='Remove item' onclick='removeItem(" + i + ")' /></TD>";
							html += "<TD>" + node.description + "</TD>";
							html += "<TD align=right>" + new Number(node.quantity).toFixed(0) + "</TD>";
							html += "<TD align=right>" + new Number(node.price).toFixed(2) + "</TD>";
							html += "<TD align=right>" + new Number(node.vatrate).toFixed(2) + "</TD>";
							html += "<TD align=right>" + new Number(node.vat).toFixed(2) + "</TD>";
							html += "<TD align=right>" + new Number(node.linetotal).toFixed(2) + "</TD>";
							html += "</TR>\n";
							
							total += parseFloat(node.linetotal);
						}
					}
				}
				
				if ($("#deliverycharge").val() == "6.50" || $("#deliverycharge").val() == "0.00") {
					if (total < 75) {
						$("#deliverycharge").val("6.50");
						
					} else {
						$("#deliverycharge").val("0.00");
					}
				}
				
				$("#total").val(new Number(total).toFixed(2));
				
				calculate_total();

				html = html + "</TABLE>";
				
				$("#divtable").html(html);
			}
			
			function saveQuoteItem() {
				if (! verifyStandardForm("#invoiceitemform")) {
					pwAlert("Invalid form");
					return false;
				}

				var item = {
						id: $("#item_id").val(),
						quantity: $("#item_quantity").val(),
						price: $("#item_unitprice").val(),
						vatrate: $("#item_vatrate").val(),
						vat: $("#item_vat").val(),
						linetotal: $("#item_linetotal").val(),
						productid: $("#item_accessoryid").val(),
						description: $('select[name=item_accessoryid] option:selected').text()
					};

				if (currentItem == -1) {
					itemArray.push(item);
					
				} else {
					itemArray[currentItem] = item;
				}
				
				populateTable(itemArray);
				
				return true;
			}
			
			function removeItem(id) {
				currentItem = id;
				
				$("#confirmRemoveDialog .confirmdialogbody").html("You are about to approve this item.<br>Are you sure ?");
				$("#confirmRemoveDialog").dialog("open");
			} 
			
			function confirmRemoval() {
				var newItemArray = [];
				var i;
				
				$("#confirmRemoveDialog").dialog("close");
				
				for (i = 0; i < itemArray.length; i++) {
					if (currentItem != i) {
						newItemArray.push(itemArray[i]);
					}
				}
				
				itemArray = newItemArray;
				
				populateTable(itemArray);
			}
			
			function editItem(id) {
				currentItem = id;
				var node = itemArray[id];
			
				$("#item_itemid").val(node.id);
				$("#item_accessoryid").val(node.productid).trigger("change");
				$("#item_quantity").val(node.quantity);
				$("#item_vat").val(node.vat);
				$("#item_vatrate").val(node.vatrate);
				$("#item_unitprice").val(node.price);
				$("#item_linetotal").val(node.linetotal);
				
				$('#invoiceitemdialog').dialog('open');				
			}
			
			function addQuoteItem() {
				currentItem = -1;
				
				$("#item_itemid").val("0");
				$("#item_accessoryid").val("0");
				$("#item_quantity").val("1");
				$("#item_vatrate").val("<?php echo getSiteConfigData()->vatrate; ?>");
				$("#item_vat").val("0.00");
				$("#item_unitprice").val("0.00");
				$("#item_linetotal").val("0.00");
				
				$('#invoiceitemdialog').dialog('open');				
			
			}
			
			function validateForm() {
				return true;
			}
			
			$(document).ready(
					function() {
						$("#item_accessoryid").change(productid_onchange);
						$("#customerid").change(customerid_onchange);
						
						$("#invoiceitemdialog").dialog({
								modal: true,
								autoOpen: false,
								show:"fade",
								closeOnEscape: true,
								width: 690,
								hide:"fade",
								title:"Quote Item",
								open: function(event, ui){
									
								},
								buttons: {
									"Save": function() {
										if (saveQuoteItem()) {
											$(this).dialog("close");
											
										}
									},
									Cancel: function() {
										$(this).dialog("close");
									}
								}
							});
					}
				);


			function bookingReference(node) {
				return "<?php echo getSiteConfigData()->bookingprefix; ?>" + padZero(node.id, 6);
			}
			
			function accept(id) {
				currentID = id;
				
				$("#confirmacceptdialog .confirmdialogbody").html("You are about to accept this quotation.<br>Are you sure ?");
				$("#confirmacceptdialog").dialog("open");
			}
			
			function undo(id) {
				currentID = id;
				
				$("#confirmundodialog .confirmdialogbody").html("You are about to undo this quotation.<br>Are you sure ?");
				$("#confirmundodialog").dialog("open");
			}
			
			function confirmaccept() {
				post("editform", "accept", "submitframe", 
						{ 
							quotationid: currentID
						}
					);
					
				$("#confirmacceptdialog").dialog("close");
			}
			
			function confirmundo() {
				post("editform", "undo", "submitframe", 
						{ 
							quotationid: currentID
						}
					);
					
				$("#confirmundodialog").dialog("close");
			}
			
			function checkStatus(node) {
				if (node.status == 0) {
					$("#acceptbutton").show();
					$("#undobutton").hide();
					
				} else {
					$("#acceptbutton").hide();
					$("#undobutton").show();
				}
			}

			function editDocuments(node) {
				viewDocument(node, "addquotationdocument.php", node, "quotationdocs", "quoteid");
			}
	
<?php			
		}
	}
	
	$crud = new QuotationCrud();
	$crud->dialogwidth = 840;
	$crud->title = "Quotations";
	$crud->onClickCallback = "checkStatus";
	$crud->table = "{$_SESSION['ESCCO_DB_PREFIX']}quotation";
	$crud->sql = "SELECT A.*, B.name AS customername, C.fullname AS takenbyname, D.name AS productname
				  FROM  {$_SESSION['ESCCO_DB_PREFIX']}quotation A
				  INNER JOIN  {$_SESSION['ESCCO_DB_PREFIX']}customer B
				  ON B.id = A.customerid
				  INNER JOIN  {$_SESSION['ESCCO_DB_PREFIX']}product D
				  ON D.id = A.productid
				  INNER JOIN  {$_SESSION['ESCCO_DB_PREFIX']}members C
				  ON C.member_id = A.takenbyid
				  ORDER BY A.id DESC";
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
				'name'       => 'bookingref',
				'function'   => 'bookingReference',
				'sortcolumn' => 'A.id',
				'type'		 => 'DERIVED',
				'length' 	 => 17,
				'editable'	 => false,
				'bind' 	 	 => false,
				'filter'	 => false,
				'label' 	 => 'Proposal Number'
			),
			array(
				'name'       => 'customerid',
				'type'       => 'DATACOMBO',
				'length' 	 => 30,
				'label' 	 => 'Customer',
				'table'		 => 'customer',
				'required'	 => true,
				'table_id'	 => 'id',
				'alias'		 => 'customername',
				'table_name' => 'name'
			),
			array(
				'name'       => 'productid',
				'type'       => 'DATACOMBO',
				'length' 	 => 110,
				'label' 	 => 'Product',
				'table'		 => 'product',
				'required'	 => true,
				'table_id'	 => 'id',
				'alias'		 => 'productname',
				'table_name' => 'name'
			),
			array(
				'name'       => 'orderdate',
				'length' 	 => 12,
				'datatype'   => 'date',
				'label' 	 => 'Quotation Date'
			),
			array(
				'name'       => 'signeddatetime',
				'length' 	 => 21,
				'datatype'   => 'datetime',
				'label' 	 => 'Signed'
			),
			array(
				'name'       => 'converteddatetime',
				'length' 	 => 16,
				'bind'		 => false,
				'edit'		 => false,
				'datatype'   => 'date',
				'label' 	 => 'Conversion Date'
			),
			array(
				'name'       => 'yourordernumber',
				'length' 	 => 20,
				'label' 	 => 'Your Order Number'
			),			
			array(
				'name'       => 'status',
				'type'		 => 'CHECKBOX',
				'length' 	 => 10,
				'label' 	 => 'Accepted'
			),			
			array(
				'name'       => 'takenbyid',
				'type'       => 'DATACOMBO',
				'length' 	 => 18,
				'label' 	 => 'Taken By',
				'table'		 => 'members',
				'required'	 => true,
				'table_id'	 => 'member_id',
				'alias'		 => 'takenbyname',
				'table_name' => 'fullname'
			),
			array(
				'name'       => 'deliverycharge',
				'length' 	 => 13,
				'datatype'   => 'double',
				'align'		 => 'right',
				'label' 	 => 'Delivery Charge'
			),
			array(
				'name'       => 'discount',
				'length' 	 => 12,
				'align'		 => 'right',
				'label' 	 => 'Discount %'
			),	
			array(
				'name'       => 'total',
				'length' 	 => 12,
				'align'		 => 'right',
				'label' 	 => 'Total'
			)	
		);

	$crud->subapplications = array(
			array(
				'title'		  => 'Documents',
				'imageurl'	  => 'images/document.gif',
				'script' 	  => 'editDocuments'
			),
			array(
				'title'		  => 'Accept',
				'id'		  => 'acceptbutton',
				'imageurl'	  => 'images/accept.png',
				'script' 	  => 'accept'
			),
			array(
				'title'		  => 'Undo',
				'id'		  => 'undobutton',
				'imageurl'	  => 'images/invalid.png',
				'script' 	  => 'undo'
			),
			array(
				'title'		  => 'Print',
				'imageurl'	  => 'images/print.png',
				'script' 	  => 'printQuote'
			)
		);
		
	$crud->messages = array(
			array('id'		  => 'quotationid')
		);
		
	$crud->run();
?>
