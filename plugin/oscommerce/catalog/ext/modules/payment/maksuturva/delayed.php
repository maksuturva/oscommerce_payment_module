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

// Mark the order as delayed
$query = tep_db_query("SELECT * FROM `mk_status` WHERE `cart_id` = '" . tep_db_prepare_input(($refId - 100)) . "' AND `order_id` = '0' AND `customer_id` = '" . $customerId . "'");
if (tep_db_num_rows($query)) {
	$result = tep_db_fetch_array($query);
	tep_db_perform('mk_status', array('delayed_payment'=> 1), 'update', "id = '{$result['id']}'");
}

//Redirect to checkout process with parameter
tep_redirect(tep_href_link(FILENAME_CHECKOUT_PROCESS, 'delayed=1', 'SSL'));