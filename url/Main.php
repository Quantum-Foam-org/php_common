<?php

namespace common\url;

class Main {
	private $url = null;
	private $origUrl = null;
	
	public function __construct($url) {
	    $this->origUrl = filter_var($url, FILTER_VALIDATE_URL, array('fags' => FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED));
	    if ($this->origUrl !== FALSE) {
		    $this->url = parse_url($this->origUrl);
                    if (!in_array($this->url['scheme'], array('http', 'https'))) {
                        throw new \UnexpectedValueException('Unexpected Scheme:' . $url);
                    }
			$this->sortQuery();
			$this->setPath();
			$this->url['urlString'] = sprintf('%s://%s%s%s%s', $this->url['scheme'], 
			    $this->url['host'], 
			    (isset($this->url['path']) && is_array($this->url['path']) ? '/'.implode('/', $this->url['path']) : ''), 
			    (isset($this->url['query']) && is_array($this->url['query']) ? '?'.http_build_query($this->url['query'], '', '&amp;', PHP_QUERY_RFC3986) : ''), 
			    (isset($this->url['fragment']) ? '#'.$this->url['fragment'] : ''));
		} else {
		    throw new \UnexpectedValueException('Invaild URL:' . $url);
		}
	}
	
	private function sortQuery() : void {
	    if (isset($this->url['query'])) {
			parse_str($this->url['query'], $query);
			foreach ($query as $i => $value) {
			    if (strpos($i, 'amp;') === 0) {
			        unset($query[$i]);
			        $query[str_replace('amp;', '', $i)] = $value;
			    }
			}
			ksort($query, SORT_LOCALE_STRING | SORT_STRING);
			$this->url['query'] = $query;
			unset($query);
		}
	}
	
	public function setPath() : void {
	    if (isset($this->url['path'])) {
    		$path = explode('/', $this->url['path']);
    		if ($path !== false) {
    			$this->url['pathInfo'] = pathinfo($this->url['path']);
    			$path = array_filter($path, function($i) { return strlen(trim($i)); });
    			if (count($path) > 0) {
    				$this->url['path'] = $path;
    			}
    		}
    		unset($path);
	    }
	}
	
	public function compare(\common\url\Main $url) : bool {
		return $this->url === $url->url;
	}
	
	public function __get($name) {
	    if (array_key_exists($name, $this->url)) {
			$result = $this->url[$name];
		} else {
		    \common\logging\Logger::obj()->writeDebug('Unable to get url index: '. $name, -1);
		}
		
		return $result;
	}
	
	public function __toString() {
	    return $this->url['urlString'];
	}
	
	public function __destruct() {
	    unset($this->url, $this->origUrl);
	}
}