<?php
   include_once(dirname(__FILE__) . "/.account.php");
	include_once(dirname(__FILE__) . "/.config.php");
	include_once(dirname(__FILE__) . "/.library.php");
	runScript("vendor-index.xml", $ven_projects, "rd-vendor", VENDORPATH_DIARY, VENDORPATH_FULL);
	echo "\n";
?>
