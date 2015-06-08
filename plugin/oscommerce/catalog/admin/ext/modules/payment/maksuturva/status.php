<?php
/**
 * Maksuturva Payment Module
 * Creation date: 05/01/2012
 */

chdir('../../../../');
require_once ('includes/application_top.php');
require_once ('../includes/languages/'.$language.'/modules/payment/maksuturva.php');
require_once ('../includes/modules/payment/maksuturva.php');

$summary = maksuturva::verifyPending();
?>

<?php if (is_file(DIR_WS_INCLUDES . 'template_top.php')): ?>
	<?php require_once(DIR_WS_INCLUDES . 'template_top.php'); ?>
<?php elseif (is_file(DIR_WS_INCLUDES . 'header.php')): ?>
	<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
	<html <?php echo HTML_PARAMS; ?>>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
	<meta name="robots" content="noindex,nofollow">
	<title><?php echo TITLE; ?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo '../../../../includes' ?>/stylesheet.css">
	<script language="javascript" src="<?php echo '../../../../includes' ?>/general.js"></script>
	</head>
	<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onload="SetFocus();">
	<!-- header //-->
	<?php
  		if ($messageStack->size > 0) {
	    	echo $messageStack->output();
	  	}
	?>
	<table border="0" width="100%" cellspacing="0" cellpadding="0">
	  <tr>
	    <td colspan="2"><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT, '', 'NONSSL') . '">' . tep_image('../../../../' . DIR_WS_IMAGES . 'oscommerce.png', PROJECT_VERSION) . '</a>'; ?></td>
	  </tr>
	  <tr class="headerBar">
	    <td class="headerBarContent">&nbsp;&nbsp;<?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT, '', 'NONSSL') . '" class="headerLink">' . HEADER_TITLE_ADMINISTRATION . '</a> &nbsp;|&nbsp; <a href="' . tep_catalog_href_link() . '" class="headerLink">' . HEADER_TITLE_ONLINE_CATALOG . '</a> &nbsp;|&nbsp; <a href="http://www.oscommerce.com" class="headerLink">' . HEADER_TITLE_SUPPORT_SITE . '</a>'; ?></td>
	    <td class="headerBarContent" align="right"><?php echo (tep_session_is_registered('admin') ? 'Logged in as: ' . $admin['username']  . ' (<a href="' . tep_href_link(FILENAME_LOGIN, 'action=logoff') . '" class="headerLink">Logoff</a>)' : ''); ?>&nbsp;&nbsp;</td>
	  </tr>
	</table>
	<!-- header_eof //-->

	<!-- body //-->
	<table border="0" width="100%" cellspacing="2" cellpadding="2">
	  <tr>
	    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
	<!-- left_navigation //-->
	<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
	<!-- left_navigation_eof //-->
	    </table></td>
	<!-- body_text //-->
	    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
	      <tr>
	        <td><table border="0" width="100%" cellspacing="0" cellpadding="2" height="40">
	          <tr>
	            <td class="pageHeading"><?php echo STORE_NAME; ?></td>
	            <td class="pageHeading" align="right"></td>
	          </tr>
	        </table></td>
	      </tr>
	      <tr>
	        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">

<?php endif; ?>

      <h2 style="margin-left: 20px;">Verifying <?php echo count($summary); ?> payments</h1>
      <table border="0" width="60%" cellspacing="2" cellpadding="5">
          <tr>
            <td class="pageHeading">Order ID</td>
            <td class="pageHeading">Message</td>
          </tr>
          <?php foreach ($summary as $row) { ?>
          <tr>
            <td>
                <a target="_blank" href="<?php echo tep_href_link(FILENAME_ORDERS, 'oID='.$row["order_id"].'&action=edit', 'NONSSL', true, true); ?>">
                    <?php echo $row["order_id"]; ?>
                </a>
            </td>
            <td><?php echo $row["message"]; ?></td>
          </tr>
          <?php } ?>
      </table>
<?php if (is_file(DIR_WS_INCLUDES . 'template_bottom.php')): ?>
	<?php require(DIR_WS_INCLUDES . 'template_bottom.php'); ?>
<?php elseif (is_file(DIR_WS_INCLUDES . 'footer.php')): ?>
			</table></td>
	      </tr>
	    </table></td>
	  </tr>
	</table>
	<!-- body_eof //-->

	<!-- footer //-->
	<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
	<!-- footer_eof //-->
	<br>
	</body>
	</html>
<?php endif; ?>

<?php  require_once (DIR_WS_INCLUDES . 'application_bottom.php'); ?>