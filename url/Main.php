<?php

namespace \common\url;

class Main {
	private $url = null;
	
	public function __construct($url) {
		$url = filter_var($url, FILTER_VALIDATE_URL, array('fags' => FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED));
		if ($url !== FALSE) {
			$this->url = parse_url($url);
		}
		unset($url);
	}
	
	public function sortQuery() {
		if (isset($this->query)) {
			parse_str($this->url['query'], $query);
			sort($query, SORT_LOCALE_STRING | SORT_STRING);
			$this->query = $query;
			unset($query);
		}
	}
	
	public function setPath() {
		$path = explode('/', $this->url['path']);
		if ($path !== false) {
			$this->ur['pathInfo'] = pathinfo($this->url['path']);
			$path = array_filter($path, function($i) { return strlen(trim($i)); });
			if (count($path) > 0) {
				$this->url['path'] = $path;
			}
		}
		unset($path);
	}
	
	public function compare(\common\url\Main $url) {
		return $this->url === $url->url;
	}
	
	public function __get($name) {
		if (property_exists($this, $name)) {
			$result = $this->{$name};
		} else {
			\common\logging\writeDebug('Unable to get property: '. $name, -1);
		}
		
		return $result;
	}
}