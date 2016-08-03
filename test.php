<?php

$common_php_dir = '../php_common';
$common_autoload_file = $common_php_dir.'/autoload.php';
require($common_autoload_file);
try {
	\common\Config::obj(__DIR__.'/config/config.ini');
} catch (\RuntimeException $re) {
	echo $re->getMessage();
}
$rss = \common\xml\XmlRead::readFromUrl("http://news.php.net/group.php?group=php.announce&format=rss");
foreach ($rss->getNodes('item') as $node) {
	foreach ($node as $item) {
		var_dump($item->nodeName, $item->textContent);
	}
	echo "--------------------------\n";
}

var_dump(memory_get_peak_usage(TRUE)/pow(1000,2));