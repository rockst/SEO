<?php
	include_once(dirname(__FILE__) . "/.account.php");
	include_once(dirname(__FILE__) . "/.config.php");
	include_once(dirname(__FILE__) . "/.library.php");
	runScript("member-index.xml", $mem_projects, "rd-member", MEMBERPATH_DIARY, MEMBERPATH_FULL);
	echo "\n";
	
?>
