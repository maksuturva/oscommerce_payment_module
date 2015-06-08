<?php
/**
 * Maksuturva Payment Module
 * Creation date: 05/01/2012
 */

/**
 * OsCommerce main class for gateway payments
 * @author RunWeb
 */
class maksuturva
{
    var $code             = 'maksuturva';
	var $title            = MODULE_PAYMENT_MAKSUTURVA_TEXT_TITLE;
	var $public_title     = MODULE_PAYMENT_MAKSUTURVA_TEXT_TITLE;
	var $description      = MODULE_PAYMENT_MAKSUTURVA_TEXT_DESCRIPTION;
	var $sort_order       = MODULE_PAYMENT_MAKSUTURVA_SORT_ORDER;
	var $encoding         = MODULE_PAYMENT_MAKSUTURVA_ENCODING;
	var $baseUrl          = MODULE_PAYMENT_MAKSUTURVA_URL;
	var $image 			  = 'https://www.maksuturva.fi/img/Maksuturva_LM_logo.jpg';
	var $enabled          = null;
	var $_check           = false;
	var $delayed          = false;

    // variables from GET on payment return
    var $mandatoryFields = array(
    	"pmt_action",
    	"pmt_version",
    	"pmt_id",
    	"pmt_reference",
    	"pmt_amount",
    	"pmt_currency",
    	"pmt_sellercosts",
    	"pmt_paymentmethod",
    	"pmt_escrow",
    	"pmt_hash"
    );

    /**
     * The default OsCommerce order statuses
     * @var array
     */
    var $_orderStatus = array(
    	"pending" => 1,
    	"processing" => 2,
    	"delivered" => 3
    );

    /**
     * This method is called on every page load
     * Specifically, when viewing an order
     */
    function maksuturva()
    {
        global $order;
        $this->enabled = $this->check();

        require_once dirname(__FILE__) . '/maksuturva/MaksuturvaGatewayImplementation.php';
        //EETU (ei toimi)
//         if (empty($currency_code) || !$currencies->is_set($currency_code)) {
//         	$currency_code = $currency;
//         }
        
//         if ($currency_code == 'EUR') {
//         	$this->form_action_url = MaksuturvaGatewayImplementation::getPaymentUrl($this->baseUrl);
//         }
        $this->form_action_url = MaksuturvaGatewayImplementation::getPaymentUrl($this->baseUrl);
    }

    /**
     * Select your payment option
     */
    function selection()
    {
    	if (!$this->isEnabled()) {
    		return false;
    	}
    	global $currencies, $currency;
		if (empty($currency_code) || !$currencies->is_set($currency_code)) {
			$currency_code = $currency;
		}
		$msg = '';
		if ($currency_code != 'EUR') {
			$msg = ' <span style="color: red"><strong>'. MODULE_PAYMENT_MAKSUTURVA_TEXT_ERROR_CURRENCY .'</strong></span>';
		}
    	$eMaksut = (MODULE_PAYMENT_MAKSUTURVA_EMAKSUT == 'True');

    	if ($eMaksut) {
    		$label = MODULE_PAYMENT_MAKSUTURVA_INSTALL_EMAKSUT;
		} else {
			$label = MODULE_PAYMENT_MAKSUTURVA_INSTALL_MAKSUTURVA;
		}
        return array(
            'id' => $this->code,
            'module' => $label . $msg,
        );
    }

    function confirmation()
    {
        return false;
    }

    /**
     * Creates an unique id to work with during the checkout process.
     * How: the cart has an internal ID which is used after the payment is
     *  done (after_process) to link with the actual order_id.
     */
	function pre_confirmation_check()
	{
		global $cartID, $cart, $_SESSION;

        if ( empty($cart->cartID))
        {
            $cartID = $cart->cartID = $cart->generate_cart_id();
        }
        if (!tep_session_is_registered('cartID'))
        {
            tep_session_register('cartID');
        }

	    // create a follow up item
	    if (!$this->getMaksuturvaStatusByCurrentCartId()) {
	    	tep_db_perform('mk_status', array(
	    	  'customer_id'  => $_SESSION['customer_id'],
	    	  'status'       => 1,
	    	  'cart_id'		 => $_SESSION["cartID"]
	    	));
	    }
  	}

  	/**
  	 * Get the maksuturva status from database based on the
  	 * current cart Id.
  	 */
  	protected function getMaksuturvaStatusByCurrentCartId()
  	{
  		global $cartID, $cart;

        if ( empty($cart->cartID)) {
        	return false;
        }

        $query = tep_db_query("SELECT * FROM `mk_status` WHERE `cart_id` = '". tep_db_prepare_input($cart->cartID) ."'");
        if (tep_db_num_rows($query)) {
        	$result = tep_db_fetch_array($query);
        	return $result;
        }
        return false;
  	}

  	/**
  	 * Get the maksuturva cartId (increment of 100 on oscommerce cart_id)
  	 * @throws Exception when there is no cart_id
  	 */
  	protected function getMaksuturvaCartId()
  	{
  		global $cartID, $cart, $_SESSION;

        if ( empty($cart->cartID)) {
        	throw new Exception("No cart id!");
        }
        return $cart->cartID + 100; // minimum of 3 digit
  	}


    /**
     * Returns the HTML hidden fields for a given order
     */
    function process_button()
    {
    	global $order, $insert_id, $currencies, $currency;

    	// not installed
    	if (!$this->isEnabled()) {
    		return false;
    	}
		

		if (empty($currency_code) || !$currencies->is_set($currency_code)) {
			$currency_code = $currency;
		}
		
		if ($currency_code != 'EUR') {
			return '<span style="color: red"><strong>'. MODULE_PAYMENT_MAKSUTURVA_TEXT_ERROR_CURRENCY .'</strong></span>';
		}
		

		require_once dirname(__FILE__) . '/maksuturva/MaksuturvaGatewayImplementation.php';
    	$gateway = new MaksuturvaGatewayImplementation($this->getMaksuturvaCartId(), $order, $this->encoding, $this->baseUrl);

    	$returnString = '';
    	foreach($gateway->getFieldArray() as $key => $value) {
	    	$returnString .= '<input type="hidden" name="' . str_replace('"', '&quot;', $key). '" value="' . str_replace('"', '&quot;', $value) . '"/>';
    	}

    	return $returnString;
    }

  	/**
  	 * Updates the mk_status with order status
  	 */
  	function before_process()
  	{
  	    global $order, $customer_id, $_GET;

  	    $this->delayed = (intval($_GET['delayed']) == 1);

  	    // If this is not a delayed notification
  	    if (!$this->delayed ) {

  	    	//Check if is a proper error message
  	    	if($this->isErrorResponse()){
  	    		$errorUrl = tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'NONSSL', true, true);
  	    		tep_redirect($errorUrl . '&error=ERROR_URL');
  	    		return;
  	    	}
            // fields are mandatory, so we discard the request if it is empty
            // Also when return through the error url given to maksuturva
            foreach ($this->mandatoryFields as $field) {
            	if (isset($_GET[$field])) {
            	    $values[$field] = $_GET[$field];
                } else {
                	$errorUrl = tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'NONSSL', true, true);
      	        	tep_redirect($errorUrl . '&error=EMPTY_FIELD&field=' . $field);
      	        	return;
                }
            }

            // first, check if the cart id exists
      	    if (count($values) == 1 && $values['pmt_id'] == $this->getMaksuturvaCartId()) {
      	        $errorUrl = tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'NONSSL', true, true);
      	        tep_redirect($errorUrl . '&error=CART_NOT_FOUND');
    	    }

    	    // then, check if the mk_status knows of such cart_id
    		if (($mkStatus = $this->getMaksuturvaStatusByCurrentCartId()) == false) {
    			$errorUrl = tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'NONSSL', true, true);
      	        tep_redirect($errorUrl . '&error=NO_MK_STATUS');
    		}

    		// is the cart from the current customer?
    		if ($mkStatus["customer_id"] != $customer_id) {
    			$errorUrl = tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'NONSSL', true, true);
      	        tep_redirect($errorUrl . '&error=CART_FROM_OTHER_OWNER');
    		}

    		// now, validate the hash
            require_once dirname(__FILE__) . '/maksuturva/MaksuturvaGatewayImplementation.php';
            // instantiate the gateway with the original order
        	$gateway = new MaksuturvaGatewayImplementation($this->getMaksuturvaCartId(), $order, $this->encoding, $this->baseUrl);
    		// calculate the hash for order
        	$calculatedHash = $gateway->generateReturnHash($values);
        	// test the hash
        	if (!($calculatedHash == $values['pmt_hash'])) {
        	    $errorUrl = tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'NONSSL', true, true);
      	        tep_redirect($errorUrl . '&error=RETURN_HASH');
        	}

        	// validate amounts, values, etc
        	// fields which will be ignored
        	$ignore = array("pmt_hash", "pmt_paymentmethod", "pmt_reference", "pmt_sellercosts");
        	foreach ($values as $key => $value) {
        		// just pass if ignore is on
        		if (in_array($key, $ignore)) {
        			continue;
        		}
        		if ($gateway->{$key} != $value) {
        		    $errorUrl = tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'NONSSL', true, true);
      	            tep_redirect($errorUrl . '&error=VALUES_MISMATCH&message=' . urlencode("different $key: $value != " . $gateway->{$key}));
        		}
        	}
        	// pmt_reference is calculated
        	if ($gateway->calcPmtReferenceCheckNumber() != $values["pmt_reference"]) {
        	    $errorUrl = tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'NONSSL', true, true);
      	        tep_redirect($errorUrl . '&error=VALUES_MISMATCH&message=' . urlencode("different $key: $value != " . $gateway->{$key}));
        	}
            if($gateway->{'pmt_sellercosts'} > ($values['pmt_sellercosts'])){
                $errorUrl = tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'NONSSL', true, true);
                tep_redirect($errorUrl . '&error=VALUES_MISMATCH&message=' . urlencode(MODULE_PAYMENT_MAKSUTURVA_TEXT_ERROR_SELLERCOSTS_MISMATCH.' '.MODULE_PAYMENT_MAKSUTURVA_TEXT_ERROR_SELLERCOSTS_MISMATCH_NEW_AMOUNT.' '.$values["pmt_sellercosts"]));
    		}
        }

    	// everything ok: now we return so after_process() can be called with the actual order_id
    	return;
  	}

  	/**
  	 * Stores the order_id into mk_status for later follow ups
  	 */
  	function after_process()
  	{
	    global $insert_id, $order, $_SESSION;

	    if ($this->delayed) {

    	    $query = tep_db_query("SELECT * FROM `mk_status` WHERE `cart_id` = '" . tep_db_prepare_input($this->getMaksuturvaCartId() - 100) . "' LIMIT 1");
    	    if (tep_db_num_rows($query)) {
    	    	$result = tep_db_fetch_array($query);
    	    	tep_db_perform('mk_status', array('order_id'=> $insert_id, 'status' => $this->_orderStatus['pending']), 'update', "id = '{$result['id']}'");
    	    }

    	    $sql_data_array = array(
    	    	'orders_id' => $insert_id,
    	        'orders_status_id' => $order->info['order_status'],
    			'date_added' => 'now()',
    			'customer_notified' => '0',
    			'comments' => MODULE_PAYMENT_MAKSUTURVA_PAYMENT_STATUS_PENDING
    	    );
          	tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

	    } else {

    	    $query = tep_db_query("SELECT * FROM `mk_status` WHERE `cart_id` = '" . tep_db_prepare_input($this->getMaksuturvaCartId() - 100) . "' LIMIT 1");
    	    if (tep_db_num_rows($query)) {
    	    	$result = tep_db_fetch_array($query);
    	    	tep_db_perform('mk_status', array('order_id'=> $insert_id, 'status' => $this->_orderStatus['processing']), 'update', "id = '{$result['id']}'");
    	    }

    	    $sql_data_array = array(
    	    	'orders_id' => $insert_id,
    	        'orders_status_id' => $order->info['order_status'],
    			'date_added' => 'now()',
    			'customer_notified' => '0',
    			'comments' => MODULE_PAYMENT_MAKSUTURVA_PAYMENT_STATUS_PROCESSED
    	    );
          	tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

	    }
  	}

  	/**
  	 * Method which verifies all the pending payments
  	 * (delay payment) returned from maksuturva.
  	 */
  	public static function verifyPending()
  	{
  		$query = tep_db_query("SELECT * FROM mk_status WHERE order_id <> 0 AND status = 1;");

  		// nothing to verify
  		if (tep_db_num_rows($query) == 0) {
  			return array();
  		}
  		$return = array();

  		require_once dirname(__FILE__) . '/maksuturva/MaksuturvaGatewayImplementation.php';
  		require_once (DIR_WS_CLASSES . 'order.php');

  		// for each row, perform the status query verification
  		while ($row = tep_db_fetch_array($query)) {
  			$order = new order($row["order_id"]);
	        // instantiate the gateway with the original order
	    	$gateway = new MaksuturvaGatewayImplementation(($row["cart_id"] + 100), $order, MODULE_PAYMENT_MAKSUTURVA_ENCODING, MODULE_PAYMENT_MAKSUTURVA_URL);
	    	try {
	    		$response = $gateway->statusQuery();
	    	} catch (Exception $e) {
	    		// next status query verification
	    		$return[] = array("order_id" => $row["order_id"], "message" => $e->getMessage(), "status" => 9);
	    		continue;
	    	}

	    	// errors
	    	if ($response === false) {
	    		$return[] = array("order_id" => $row["order_id"], "message" => "Invalid hash or network error.", "status" => 9);
	    		continue;
	    	}

	    	switch ($response["pmtq_returncode"]) {
	    		// set as paid if not already set
	    		case MaksuturvaGatewayImplementation::STATUS_QUERY_PAID:
	    		case MaksuturvaGatewayImplementation::STATUS_QUERY_PAID_DELIVERY:
	    		case MaksuturvaGatewayImplementation::STATUS_QUERY_COMPENSATED:
	    			$sql_data_array = array(
				    	'orders_id' => $row["order_id"],
				        'orders_status_id' => 1,
						'date_added' => 'now()',
						'customer_notified' => '0',
						'comments' => MODULE_PAYMENT_MAKSUTURVA_PAYMENT_IDENTIFIED .
    						(isset($row["response_text"]) ? "(" . $row["response_text"] .")" : "")
				    );
			      	tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

			      	// now we remove the mk_status from the watch list
			      	tep_db_query("update `mk_status` set status = '" . tep_db_prepare_input($response["pmtq_returncode"]) . "' WHERE `id` = '" . tep_db_prepare_input($row["id"]) . "'");
			      	$return[] = array("order_id" => $row["order_id"], "message" => "Payment identified by Maksuturva", "status" => 1);
	    			break;

	    		// set payment cancellation with the notice
	    		// stored in response_text
	    		case MaksuturvaGatewayImplementation::STATUS_QUERY_PAYER_CANCELLED:
    			case MaksuturvaGatewayImplementation::STATUS_QUERY_PAYER_CANCELLED_PARTIAL:
    			case MaksuturvaGatewayImplementation::STATUS_QUERY_PAYER_CANCELLED_PARTIAL_RETURN:
    			case MaksuturvaGatewayImplementation::STATUS_QUERY_PAYER_RECLAMATION:
    			case MaksuturvaGatewayImplementation::STATUS_QUERY_CANCELLED:
    				// insert a notification in order
    				$sql_data_array = array(
				    	'orders_id' => $row["order_id"],
				        'orders_status_id' => 1,
						'date_added' => 'now()',
						'customer_notified' => '0',
						'comments' => MODULE_PAYMENT_MAKSUTURVA_PAYMENT_CANCELLED .
    						(isset($row["response_text"]) ? "(" . $row["response_text"] .")" : "")
				    );
			      	tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

    				// Delete the mk_status row
					tep_db_query("update `mk_status` set status = '" . tep_db_prepare_input($response["pmtq_returncode"]) . "' WHERE `id` = '" . tep_db_prepare_input($row["id"]) . "'");
					$return[] = array("order_id" => $row["order_id"], "message" => "Payment cancelled (" . $row["response_text"] . ").", "status" => 2);
    				break;

    	        // no news for buyer and seller
	    		case MaksuturvaGatewayImplementation::STATUS_QUERY_NOT_FOUND:
	    		case MaksuturvaGatewayImplementation::STATUS_QUERY_FAILED:
	    		case MaksuturvaGatewayImplementation::STATUS_QUERY_WAITING:
    			case MaksuturvaGatewayImplementation::STATUS_QUERY_UNPAID:
    			case MaksuturvaGatewayImplementation::STATUS_QUERY_UNPAID_DELIVERY:
    			default:
    				// no action here
    				$return[] = array("order_id" => $row["order_id"], "message" => "No change, still awaiting payment", "status" => 0);
	    			break;
	    	}
  		}

  		return $return;
  	}

    function get_error()
    {
        global $HTTP_GET_VARS, $language;

        $error = '';
        $error_text['title'] = MODULE_PAYMENT_MAKSUTURVA_TEXT_ERROR_TITLE;

        if (isset($HTTP_GET_VARS['error']))

            $error = urldecode($HTTP_GET_VARS['error']); // otherwise default error is displayed
            switch($error){

            case 'RETURN_HASH':
                    $error_text['error'] = MODULE_PAYMENT_MAKSUTURVA_TEXT_RETURN_HASH;
                    break;
            case 'ERROR_URL':
                    $error_text['error'] = MODULE_PAYMENT_MAKSUTURVA_TEXT_ERROR_URL;
                    break;
            case 'CANCEL':
                    $error_text['error'] = MODULE_PAYMENT_MAKSUTURVA_TEXT_CANCEL;
                    break;
            case 'VALUES_MISMATCH':
                    $error_text['error'] = MODULE_PAYMENT_MAKSUTURVA_TEXT_ERROR_VALUES_MISMATCH . ' ' . $HTTP_GET_VARS['message'];
                    break;
            case 'EMPTY_FIELD':
                    $error_text['error'] = MODULE_PAYMENT_MAKSUTURVA_TEXT_ERROR_EMPTY_FIELD . ' ' . $HTTP_GET_VARS['field'];
                    break;
            case 'CART_NOT_FOUND':
                    $error_text['error'] = MODULE_PAYMENT_MAKSUTURVA_TEXT_ERROR_CART_NOT_FOUND;
                    break;
            case 'NO_MK_STATUS':
                    $error_text['error'] = MODULE_PAYMENT_MAKSUTURVA_TEXT_ERROR_NO_MK_STATUS;
                    break;
			case 'CURRENCY':
					$error_text['error'] = MODULE_PAYMENT_MAKSUTURVA_TEXT_ERROR_CURRENCY;
					break;
            case 'CART_FROM_OTHER_OWNER':
                    $error_text['error'] = MODULE_PAYMENT_MAKSUTURVA_TEXT_ERROR_CART_FROM_OTHER_OWNER;
                    break;

            /*
           		include additional handling for gateway specific errors here
            */
            default: //unknown error
                    $error_text['error'] = MODULE_PAYMENT_MAKSUTURVA_TEXT_ERROR_UNKNOWN ." ($error)";
                    break;
        }
        return $error_text;
    }

    /**
     * Checks module installation by verifying the existence of keys in database
     * @return boolean
     */
    function check()
    {
        if ($this->_check === false) {
        	$keys = $this->keys();
            $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = '" . $keys[0] . "'");
            $this->_check = (tep_db_num_rows($check_query) > 0 ? 1 : 0);
        }
        return $this->_check;
    }

    /**
     * Verifies if the module is enabled
     * @return boolean
     */
    function isEnabled()
    {
    	if ($this->enabled !== null) {
    		return $this->enabled;
    	}
    	$this->enabled = true;

    	if (!$this->check()) {
    		$this->enabled = false;
    	}

    	$check_query = tep_db_query("SELECT `configuration_value` as `value` from `".TABLE_CONFIGURATION."` WHERE `configuration_key` = 'MODULE_PAYMENT_MAKSUTURVA_STATUS'");
    	if (!tep_db_num_rows($check_query)) {
    		$this->enabled = false;
    	}
    	$values = tep_db_fetch_array($check_query);
    	if ($values['configuration_value'] != 'True') {
    		$this->enabled = false;
    	}

    	return $this->enabled;
    }

	/**
     * There is nothing to validate through JS
     * This method is not used
     */
    function javascript_validation()
    {
        return false;
    }

    /**
     * Module installation
     * 	Deploys configuration variables into the system
     */
    function install()
    {
    	// globals provided by upper level (catalog/admin/modules.php)
    	global $module_language_directory, $language, $module_type;
    	if (!defined("MODULE_PAYMENT_MAKSUTURVA_INSTALL_ENABLE")) {
    		$include_lang = $module_language_directory . $language . '/modules/' . $module_type . '/' . basename(__FILE__);
    		if (file_exists($include_lang)) {
    			include_once($include_lang);
    		// just a failsafe
    		} else {
    			define('MODULE_PAYMENT_MAKSUTURVA_INSTALL_ENABLE', 'Enable Maksuturva module?');
				define('MODULE_PAYMENT_MAKSUTURVA_INSTALL_ENABLE_DESCRIPTION', 'Do you wish to enable the Maksuturva payment module?');
				define('MODULE_PAYMENT_MAKSUTURVA_INSTALL_SELLER_ID', 'Seller id');
				define('MODULE_PAYMENT_MAKSUTURVA_INSTALL_SELLER_ID_DESCRIPTION', 'The seller identification provided by Maksuturva upon your registration');
				define('MODULE_PAYMENT_MAKSUTURVA_INSTALL_SECRET_KEY', 'Secret Key');
				define('MODULE_PAYMENT_MAKSUTURVA_INSTALL_SECRET_KEY_DESCRIPTION', 'Your unique secret key provided by Maksuturva');
				define('MODULE_PAYMENT_MAKSUTURVA_INSTALL_SORT_ORDER', 'Sort order');
				define('MODULE_PAYMENT_MAKSUTURVA_INSTALL_SORT_ORDER_DESCRIPTION', 'The order in which this module will be displayed to the user.');
				define('MODULE_PAYMENT_MAKSUTURVA_INSTALL_SECRET_KEY_VERSION', 'Secret Key Version');
				define('MODULE_PAYMENT_MAKSUTURVA_INSTALL_SECRET_KEY_VERSION_DESCRIPTION', 'The version of the secret key provided by Maksuturva');
				define('MODULE_PAYMENT_MAKSUTURVA_INSTALL_SANDBOX', 'Sandbox Mode');
				define('MODULE_PAYMENT_MAKSUTURVA_INSTALL_SANDBOX_DESCRIPTION', 'Do you want to enable the test mode? All the payments will not be real.');
				define('MODULE_PAYMENT_MAKSUTURVA_INSTALL_ENCODING', 'Communication encoding');
                define('MODULE_PAYMENT_MAKSUTURVA_INSTALL_ENCODING_DESCRIPTION', 'Maksuturva accepts both ISO-8859-1 and UTF-8 encodings to receive the transactions.');
                define('MODULE_PAYMENT_MAKSUTURVA_INSTALL_EMAKSUT', 'eMaksut');
                define('MODULE_PAYMENT_MAKSUTURVA_INSTALL_EMAKSUT_DESCRIPTION', 'Use eMaksut payment service instead of Maksuturva.');
    		}
    	}
        tep_db_query(
            "insert into " . TABLE_CONFIGURATION . " (".
            "configuration_title, configuration_key, configuration_value, ".
            "configuration_description, configuration_group_id, sort_order, ".
            "set_function, date_added".
            ") values (".
            "'" . MODULE_PAYMENT_MAKSUTURVA_INSTALL_ENABLE ."', 'MODULE_PAYMENT_MAKSUTURVA_STATUS', 'True', ".
            "'" . MODULE_PAYMENT_MAKSUTURVA_INSTALL_ENABLE_DESCRIPTION . "', '6', '1', ".
            "'tep_cfg_select_option(array(\'True\', \'False\'), ', now())"
        );
        tep_db_query(
            "insert into " . TABLE_CONFIGURATION . " (".
            "configuration_title, configuration_key, configuration_value, ".
            "configuration_description, configuration_group_id, sort_order, ".
            "date_added".
            ") values (".
            "'" . MODULE_PAYMENT_MAKSUTURVA_INSTALL_SELLER_ID. "', 'MODULE_PAYMENT_MAKSUTURVA_SELLER_ID', '', ".
            "'" . MODULE_PAYMENT_MAKSUTURVA_INSTALL_SELLER_ID_DESCRIPTION. "', '6', '8', ".
            "now())"
        );
        tep_db_query(
            "insert into " . TABLE_CONFIGURATION . " (".
            "configuration_title, configuration_key, configuration_value, ".
            "configuration_description, configuration_group_id, sort_order, ".
            "date_added".
            ") values (".
            "'" . MODULE_PAYMENT_MAKSUTURVA_INSTALL_SECRET_KEY . "', 'MODULE_PAYMENT_MAKSUTURVA_SECRET_KEY', '', ".
            "'" . MODULE_PAYMENT_MAKSUTURVA_INSTALL_SECRET_KEY_DESCRIPTION . "', '6', '8', ".
            "now())"
        );
        tep_db_query(
            "insert into " . TABLE_CONFIGURATION . " (".
            "configuration_title, configuration_key, configuration_value, ".
            "configuration_description, configuration_group_id, sort_order, ".
            "date_added".
            ") values (".
            "'" . MODULE_PAYMENT_MAKSUTURVA_INSTALL_SORT_ORDER . "', 'MODULE_PAYMENT_MAKSUTURVA_SORT_ORDER', '0', ".
            "'" . MODULE_PAYMENT_MAKSUTURVA_INSTALL_SORT_ORDER_DESCRIPTION . "', '6', '8', ".
            "now())"
        );
        tep_db_query(
            "insert into " . TABLE_CONFIGURATION . " (".
            "configuration_title, configuration_key, configuration_value, ".
            "configuration_description, configuration_group_id, sort_order, ".
            "date_added".
            ") values (".
            "'" . MODULE_PAYMENT_MAKSUTURVA_INSTALL_SECRET_KEY_VERSION . "', 'MODULE_PAYMENT_MAKSUTURVA_SECRET_KEY_VERSION', '001', ".
            "'" . MODULE_PAYMENT_MAKSUTURVA_INSTALL_SECRET_KEY_VERSION_DESCRIPTION . "', '6', '8', ".
            "now())"
        );
        tep_db_query(
            "insert into " . TABLE_CONFIGURATION . " (".
            "configuration_title, configuration_key, configuration_value, ".
            "configuration_description, configuration_group_id, sort_order, ".
            "date_added".
            ") values (".
            "'" . MODULE_PAYMENT_MAKSUTURVA_INSTALL_URL_DATE . "', 'MODULE_PAYMENT_MAKSUTURVA_URL', 'https://www.maksuturva.fi', ".
            "'" . MODULE_PAYMENT_MAKSUTURVA_INSTALL_URL_DESCRIPTION . "', '6', '8', ".
            "now())"
        );
        tep_db_query(
            "insert into " . TABLE_CONFIGURATION . " (".
            "configuration_title, configuration_key, configuration_value, ".
            "configuration_description, configuration_group_id, sort_order, ".
            "set_function, date_added".
            ") values (".
            "'" . MODULE_PAYMENT_MAKSUTURVA_INSTALL_ENCODING ."', 'MODULE_PAYMENT_MAKSUTURVA_ENCODING', 'UTF-8', ".
            "'" . MODULE_PAYMENT_MAKSUTURVA_INSTALL_ENCODING_DESCRIPTION . "', '6', '1', ".
            "'tep_cfg_select_option(array(\'UTF-8\', \'ISO-8859-1\'), ', now())"
        );
        tep_db_query(
            "insert into " . TABLE_CONFIGURATION . " (".
            "configuration_title, configuration_key, configuration_value, ".
            "configuration_description, configuration_group_id, sort_order, ".
            "set_function, date_added".
            ") values (".
            "'" . MODULE_PAYMENT_MAKSUTURVA_INSTALL_SANDBOX ."', 'MODULE_PAYMENT_MAKSUTURVA_SANDBOX', 'True', ".
            "'" . MODULE_PAYMENT_MAKSUTURVA_INSTALL_SANDBOX_DESCRIPTION . "', '6', '1', ".
            "'tep_cfg_select_option(array(\'True\', \'False\'), ', now())"
        );
        tep_db_query(
            "insert into " . TABLE_CONFIGURATION . " (".
            "configuration_title, configuration_key, configuration_value, ".
            "configuration_description, configuration_group_id, sort_order, ".
            "set_function, date_added".
            ") values (".
            "'" . MODULE_PAYMENT_MAKSUTURVA_INSTALL_EMAKSUT ."', 'MODULE_PAYMENT_MAKSUTURVA_EMAKSUT', 'False', ".
            "'" . MODULE_PAYMENT_MAKSUTURVA_INSTALL_EMAKSUT_DESCRIPTION . "', '6', '1', ".
            "'tep_cfg_select_option(array(\'True\', \'False\'), ', now())"
        );
        
		tep_db_query("CREATE TABLE IF NOT EXISTS `mk_status` (
	        `id` int NOT NULL AUTO_INCREMENT,
	        `order_id`     int DEFAULT 0,
	        `customer_id`  int,
	        `cart_id`      int,
	        `status`       int,
	        `delayed_payment` int DEFAULT 0,
	        PRIMARY KEY (id)
	      )
	    ");
    }

    /**
     * Module keys are fetched using this method
     * They are registered using the method self::install()
     * @return array
     */
    function keys()
    {
      return array(
      	'MODULE_PAYMENT_MAKSUTURVA_STATUS',
      	'MODULE_PAYMENT_MAKSUTURVA_SORT_ORDER',
      	'MODULE_PAYMENT_MAKSUTURVA_SELLER_ID',
      	'MODULE_PAYMENT_MAKSUTURVA_SECRET_KEY',
      	'MODULE_PAYMENT_MAKSUTURVA_SECRET_KEY_VERSION',
        'MODULE_PAYMENT_MAKSUTURVA_URL',
      	'MODULE_PAYMENT_MAKSUTURVA_ENCODING',
      	'MODULE_PAYMENT_MAKSUTURVA_SANDBOX',
      	'MODULE_PAYMENT_MAKSUTURVA_EMAKSUT'
      );
    }

    /**
     * Module removal - removes configuration entries from OsCommerce
     */
    function remove()
    {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
        tep_db_query("DROP TABLE IF EXISTS `mk_status`");
    }
    
    /**
     * 
     * @return boolean true if is error
     */
    function isErrorResponse(){
    	$isErrorResponse = true;
    	foreach ($this->mandatoryFields as $field) {
    		if($field != "pmt_id"){
    			if (isset($_GET[$field])) {
    				$isErrorResponse = false;
    			}
    		}
    	}
    	return !$this->delayed && $isErrorResponse && isset($_GET["pmt_id"]) && $_GET["pmt_id"] != null && !isset($_GET["error"]);
    }
}

?>