<?php
	include_once(dirname(__FILE__) . "/.config.php");
	include_once(dirname(__FILE__) . "/.library.php");
	sitemapProcess(VENDORPATH . "album/", "rd-vendor-album.xml", true);
	sitemapProcess(VENDORPATH . "video/", "rd-vendor-video.xml", true);
	sitemapProcess(VENDORPATH . "prom/" , "rd-vendor-prom.xml" , true);
	sitemapProcess(VENDORPATH . "blog/" , "rd-vendor-blog.xml" , true);
?>
