<?php

namespace common\xml;

class XmlRead extends \DomDocument {
	private $url = null;
	
	public static function readFromUrl($url) {
		$url = \filter_var($url, FILTER_VALIDATE_URL);
		
		
		if ($url !== FALSE) {
			$cl = \get_called_class();
			$xmlRead = new $cl;
			$xmlRead->load($url, LIBXML_NOBLANKS);
		} else {
			$xmlRead = null;
		}
		
		unset($url);
		
		return $xmlRead;
	}
}