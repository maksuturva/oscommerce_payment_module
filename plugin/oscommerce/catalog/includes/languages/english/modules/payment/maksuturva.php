<?php
/**
 * Maksuturva Payment Module
 * Creation date: 05/01/2012
 */

define('MODULE_PAYMENT_MAKSUTURVA_VERIFY_PAYMENTS', 'Verify all pending payments');
define('MODULE_PAYMENT_MAKSUTURVA_OPEN_EXTRANET', 'Open KauppiasExtranet to view payments.');

$statusQuerybutton = '';
if (defined('MODULE_PAYMENT_MAKSUTURVA_STATUS')) {
    $statusQuerybutton = '<p><img src="images/icons/locked.gif" border="0">&nbsp;<a href=' . tep_href_link('ext/modules/payment/maksuturva/status.php', '', 'SSL') . ' target="_blank" style="text-decoration: underline; font-weight: bold;">' . MODULE_PAYMENT_MAKSUTURVA_VERIFY_PAYMENTS . '</a></p>' .
    	'<br/><a style="font-weight: bold; text-decoration: underline; color: blue;" target="_blank" href="https://www.maksuturva.fi/extranet/PaymentEventInformation.xtnt">' . MODULE_PAYMENT_MAKSUTURVA_OPEN_EXTRANET . '</a>';
}

define('MODULE_PAYMENT_MAKSUTURVA_TEXT_TITLE', 'Maksuturva/eMaksut');
define('MODULE_PAYMENT_MAKSUTURVA_TEXT_DESCRIPTION', 'This is a module for allowing your users to pay with the Maksuturva.fi checkout' . $statusQuerybutton);

define('MODULE_PAYMENT_MAKSUTURVA_SHIPPING_ROW', 'Shipping cost');
define('MODULE_PAYMENT_MAKSUTURVA_TEXT_ERROR_TITLE', 'Payment error');
define('MODULE_PAYMENT_MAKSUTURVA_TEXT_RETURN_HASH', 'Return hash from Maksuturva don\'t match the calculated');
define('MODULE_PAYMENT_MAKSUTURVA_TEXT_ERROR_URL', 'Maksuturva returned an error on your payment');
define('MODULE_PAYMENT_MAKSUTURVA_TEXT_ERROR_UNKNOWN', 'Maksuturva returned an unknown error');
define('MODULE_PAYMENT_MAKSUTURVA_TEXT_CANCEL', 'You have cancelled your payment on Maksturva, please try again or select another payment method.');
define('MODULE_PAYMENT_MAKSUTURVA_TEXT_ERROR_VALUES_MISMATCH', 'Value returned from Maksuturva doesn\' match:');
define('MODULE_PAYMENT_MAKSUTURVA_TEXT_ERROR_EMPTY_FIELD', 'Maksuturva return an empty field:');
define('MODULE_PAYMENT_MAKSUTURVA_TEXT_ERROR_CART_NOT_FOUND', 'Cart not found on payment return');
define('MODULE_PAYMENT_MAKSUTURVA_TEXT_ERROR_NO_MK_STATUS', 'Internal control row for payment not found');
define('MODULE_PAYMENT_MAKSUTURVA_TEXT_ERROR_CART_FROM_OTHER_OWNER', 'Error: the cart belong to other user');
define('MODULE_PAYMENT_MAKSUTURVA_TEXT_ERROR_CURRENCY', 'Error: Maksuturva accepts only Euro as currency');

//ADDED TO REVISION 121
define('MODULE_PAYMENT_MAKSUTURVA_TEXT_ERROR_SELLERCOSTS_MISMATCH', 'PURCHASE IS NOT SAVED. Please contact the web store to confirm the purchase.');
define('MODULE_PAYMENT_MAKSUTURVA_TEXT_ERROR_SELLERCOSTS_MISMATCH_NEW_AMOUNT', 'Payment seller costs do not match. New value: ');

// order status update
define('MODULE_PAYMENT_MAKSUTURVA_PAYMENT_STATUS_PROCESSED', 'Payment confirmed by Maksuturva');
define('MODULE_PAYMENT_MAKSUTURVA_PAYMENT_STATUS_PENDING', 'Waiting for payment confirmation by Maksuturva');
define('MODULE_PAYMENT_MAKSUTURVA_PAYMENT_CANCELLED', 'Payment cancelled by customer.');
define('MODULE_PAYMENT_MAKSUTURVA_PAYMENT_IDENTIFIED', 'Payment confirmed by Maksuturva.');

// installation strings
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
define('MODULE_PAYMENT_MAKSUTURVA_INSTALL_MAKSUTURVA', 'Maksuturva');
define('MODULE_PAYMENT_MAKSUTURVA_INSTALL_EMAKSUT_DESCRIPTION', 'Use eMaksut payment service instead of Maksuturva.');
define('MODULE_PAYMENT_MAKSUTURVA_INSTALL_URL_DATE', 'Communication URL');
define('MODULE_PAYMENT_MAKSUTURVA_INSTALL_URL_DESCRIPTION', 'The URL used to communicate with maksuturva. Do not change this configuration unless you know what you are doing.' );

