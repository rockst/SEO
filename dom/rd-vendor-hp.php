<?php
	include_once(dirname(__FILE__) . "/.account.php");
	include_once(dirname(__FILE__) . "/.config.php");
	include_once(dirname(__FILE__) . "/.library.php");
	domProcess(VENDORPATH_HP, "rd-vendor.xml");
	echo "FTP to beta: " . ((ftp2beta("rd-vendor.xml.gz")) ? "success" : "fail") . "\n";
	echo "Rsync to online: " . ((rsync2online()) ? "success" : "fail") . "\n";
	echo "Submit " . WWWRoot . "rd-vendor.xml.gz\n";
	SubmitSitemapCurl(WWWRoot . "rd-vendor.xml.gz");
?>
