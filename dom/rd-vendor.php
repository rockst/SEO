<?php
	include_once(dirname(__FILE__) . "/.config.php");
	include_once(dirname(__FILE__) . "/.library.php");
	echo "訊息：" . runScript("vendor-index.xml", $ven_projects, "rd-vendor", VENDORPATH_DIARY, VENDORPATH_FULL) . "\n";
?>
