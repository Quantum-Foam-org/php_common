<?php

namespace \local\classes\xml;

class RssRead extends DomDocument {
	private $url = null;
	
	public function __construct($version = '1.0', $encoding = 'UTF-8') {
		
		
		parent::__construct($version, $encoding);
	}
	
	public function setUrl($url) {
		$this->url = filter_var($url, FILTER_VALIDATE_URL);
	}
	
	public function fetch() {
		
	}
}
