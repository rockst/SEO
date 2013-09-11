<?php
	include_once(dirname(__FILE__) . "/.config.php");
	include_once(dirname(__FILE__) . "/.library.php");
	echo "訊息：" . runScript("member-index.xml", $mem_projects, "rd-member", MEMBERPATH_DIARY, MEMBERPATH_FULL) . "\n";
?>
