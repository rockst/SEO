<?php
	Define('XMLROOT', dirname(__FILE__) . '/output/'); // 放 XML 實體檔案的位置
	Define('GZROOT', dirname(__FILE__) . '/output/'); // 放 gzip 實體檔案的位置
	Define('MEMBERPATH', dirname(__FILE__) . '/member/'); // 放會員 xml, gzip 實體檔案的位置
	Define('VENDORPATH', dirname(__FILE__) . '/vendor/'); // 放廠商 xml, gzip 實體檔案的位置
	Define("URLLIMIT", 50000); // 每個 XML 檔案最大的 URL 數
	Define("XMLSIZE", 10485760); // 每個 gzip 所能接受的最大檔案容量
?>
