<?php
/**
 * Maksuturva Payment Module
 * Creation date: 05/01/2012
 */

$currentDir = dirname(__FILE__);
chdir('../../../../');
require_once('includes/application_top.php');


$refId = $_GET['pmt_id'];
$customerId = $_SESSION['customer_id'];

// Delete the mk_status row
tep_db_query("delete from `mk_status` WHERE `cart_id` = '" . tep_db_prepare_input(($refId - 100)) . "' AND `order_id` = '0' AND `customer_id` = '" . $customerId . "'");

// Warn the user that the payment has been cancelled
$cancelUrl = tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=maksuturva', 'SSL');
tep_redirect($cancelUrl . '&error=CANCEL');
  	        