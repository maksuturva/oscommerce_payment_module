<?php
/**
 * Maksuturva Payment Module for osCommerce 2.3.x
 * Module developed by
 * 	RunWeb Desenvolvimento de Sistemas LTDA and
 *  Movutec Oy
 *
 * www.runweb.com.br
 * www.movutec.com
 * Creation date: 01/12/2011
 * Last update: 31/01/2012
 */
require_once dirname(__FILE__) . '/MaksuturvaGatewayAbstract.php';

/**
 * Main class for gateway payments
 * @author RunWeb
 */
class MaksuturvaGatewayImplementation extends MaksuturvaGatewayAbstract
{
    var $sandbox = false;

	function __construct($id, $order, $encoding, $url = 'https://www.maksuturva.fi')
	{
		global $languages_id;
		require_once(DIR_WS_CLASSES . 'currencies.php');
		$currencies = new currencies();
		
	    if (strtoupper(MODULE_PAYMENT_MAKSUTURVA_SANDBOX) == 'TRUE') {
	        $this->sandbox = true;
	        $secretKey = '11223344556677889900';
	        $sellerId = 'testikauppias';
	    } else {
	        $secretKey = MODULE_PAYMENT_MAKSUTURVA_SECRET_KEY;
	        $sellerId = MODULE_PAYMENT_MAKSUTURVA_SELLER_ID;
	    }
		$dueDate = date("d.m.Y");

		//Adding each product from order
		$products_rows = array();
		foreach ($order->products as $product) {
			$product_query = tep_db_query("select products_description from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$product["id"] . "' and language_id = '" . (int)$languages_id . "'");
    		$productDesc = tep_db_fetch_array($product_query);
    		
			$description = '';
			
    		if ($productDesc) {
    			$description .= $productDesc["products_description"];
    		} else {
    			$description .= $description . $product["name"];
    		}
			
    		$row = array();
		    $row['pmt_row_name'] = mb_substr($product["name"], 0, 40);                                                        //alphanumeric        max lenght 40             -
            $row['pmt_row_desc'] = $description;                                                       //alphanumeric        max lenght 1000      min lenght 1
            $row['pmt_row_quantity'] = $product["qty"];                                                   //numeric             max lenght 8         min lenght 
		    if (isset( $product["model"]) && trim($product["model"]) != ''){
		    	$row['pmt_row_articlenr'] = mb_substr($product["model"], 0, 10);
		    }
		    $row['pmt_row_deliverydate'] = date("d.m.Y");                                                   //alphanumeric        max lenght 10        min lenght 10        dd.MM.yyyy
            $row['pmt_row_price_net'] = str_replace('.', ',', sprintf("%.2f", $product["final_price"] * $currencies->get_value("EUR")));          //alphanumeric        max lenght 17        min lenght 4         n,nn
            $row['pmt_row_vat'] = str_replace('.', ',', sprintf("%.2f", $product["tax"]));                //alphanumeric        max lenght 5         min lenght 4         n,nn
            $row['pmt_row_discountpercentage'] = "0,00";                                                    //alphanumeric        max lenght 5         min lenght 4         n,nn
            $row['pmt_row_type'] = 1;
		    array_push($products_rows, $row);
		}

		// Adding the shipping cost as a row
		$row = array(
		    'pmt_row_name' => MODULE_PAYMENT_MAKSUTURVA_SHIPPING_ROW,
        	'pmt_row_desc' => $order->info["shipping_method"],
        	'pmt_row_quantity' => 1,
        	'pmt_row_deliverydate' => date("d.m.Y"),
        	'pmt_row_price_net' => str_replace('.', ',', sprintf("%.2f", $order->info['shipping_cost'] * $currencies->get_value("EUR"))),
        	'pmt_row_vat' => "0,00",
        	'pmt_row_discountpercentage' => "0,00",
        	'pmt_row_type' => 2,
		);
		array_push($products_rows, $row);

		$options = array(
			"pmt_keygeneration" => MODULE_PAYMENT_MAKSUTURVA_SECRET_KEY_VERSION,
		
			"pmt_id" 		=> $id,
			"pmt_orderid"	=> $id,
			"pmt_reference" => $id,
			"pmt_sellerid" 	=> $sellerId,
			"pmt_duedate" 	=> $dueDate,

			"pmt_okreturn"	=> tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'),
			"pmt_errorreturn"	=> tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'),
			"pmt_cancelreturn"	=> tep_href_link('ext/modules/payment/maksuturva/cancel.php', '', 'SSL', false, false),
			"pmt_delayedpayreturn"	=> tep_href_link('ext/modules/payment/maksuturva/delayed.php', '', 'SSL', false, false),
			"pmt_amount" 		=> str_replace('.', ',', sprintf("%.2f", $order->info['subtotal'] * $currencies->get_value("EUR"))),

			// Customer Information
			"pmt_buyername" 	=> trim($order->billing["firstname"] . " " . $order->billing["lastname"]),
		    "pmt_buyeraddress" => $order->billing["street_address"],
			"pmt_buyerpostalcode" => $order->billing["postcode"],
			"pmt_buyercity" => $order->billing["city"],
			"pmt_buyercountry" => $order->billing["country"]["iso_code_2"],
		    "pmt_buyeremail" => $order->customer["email_address"],

			// emaksut
			"pmt_escrow" => (MODULE_PAYMENT_MAKSUTURVA_EMAKSUT == "True" ? "N" : "Y"),

		    // Delivery information
			"pmt_deliveryname" => trim($order->delivery["firstname"] . " " . $order->delivery["lastname"]),
			"pmt_deliveryaddress" => $order->delivery["street_address"],
			"pmt_deliverypostalcode" => $order->delivery["postcode"],
		    "pmt_deliverycity" => $order->delivery["city"],
			"pmt_deliverycountry" => $order->delivery["country"]["iso_code_2"],

			"pmt_sellercosts" => str_replace('.', ',', sprintf("%.2f", $order->info['shipping_cost'] * $currencies->get_value("EUR"))),

		    "pmt_rows" => count($products_rows),
		    "pmt_rows_data" => $products_rows

		);
		foreach($options["pmt_rows_data"] as $key => $row){
			foreach($row as $param_key => $param_value){
				$options["pmt_rows_data"][$key][$param_key] = parent::filterValueString($param_value);
			}
		}
		parent::__construct($secretKey, $options, $encoding, $url);
	}

	/**
	 * Uses status query to verify a given payment
	 * Enter description here ...
	 */
	public static function verifyPendingPayments()
  	{
  		//select * from mk_status where delayed = 1 and order_id != 0 and status = 0;
  		$query = tep_db_query("SELECT * FROM mk_status WHERE delayed = 1 AND order_id != 0 AND status = 0;");
  		// nothing to verify
  		if (tep_db_num_rows($query) == 0) {
  			return;
  		}

  	}

    public function calcPmtReferenceCheckNumber()
    {
        return $this->getPmtReferenceNumber($this->_formData['pmt_reference']);
    }

    public function calcHash()
    {
        return $this->generateHash();
    }

    public function getHashAlgo()
    {
        return $this->_hashAlgoDefined;
    }

}