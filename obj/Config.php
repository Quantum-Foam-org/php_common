<?php

namespace common\obj;


class Config extends \ArrayObject {
	protected $config = [];
	
	public function offsetExists($offset){
		return property_exists($this, $offset);
	}
	
	public function offsetGet($offset) {
		if (!$this->offsetExists($offset)) {
			throw new \UnexpectedValueException('Offset, '.$offset.', does not exist');
		}
		return $this->$offset;
	}
	
	public function offsetSet($offset, $value) {
		if (property_exists($this, $offset)) {
			if (isset($this->config[$offset][0])) {
				if (!($this->$offset = filter_var($value, $this->config[$offset][0], (isset($this->config[$offset][1]) && is_array($this->config[$offset][1]) ? $this->config[$offset][1] : array())))) {
					throw new \UnexpectedValueException('Invalid value, '.$value.', for offset '.$offset);
				}
			} else {
				throw new \RuntimeException('Filter has not been set for offset '.$offset);
			}
		} else {
			throw new \OutOfBoundsException($offset.' does not exist for '.__CLASS__);
		}
	}
	
	public function offsetUnset($offset) {
	    if ($this->offsetExists($offset)) {
	       $this->$offset = null;
	    }
	}
	
	public function __get($offset) {
		return $this->offsetGet($offset);
	}
	
	public function __set($offset, $value) {
	   $this->offsetSet($offset, $value);
	}
	
	/**
	 * The keys of Config::$config array must be protected variables of the class.
	 * This will return a list of class variables that will be configured using offsetSet.
	 * @return array - keys of Config::$config
	 */
	protected function getConfiguredParams() : array {
	    static $params = null;
	    
	    if ($params === null) {
	        $params = array_keys($this->config);
	    }
	    
	    return $params; 
	}
	
	public function getArrayCopy() {
	    $params = $this->getConfiguredParams();
	    
	    $arrayCopy = [];
	    foreach ($params as $param) {
	        $arrayCopy[$param] = $this->$param;
	    }
	    
	    return $arrayCopy;
	}
	
	public function exchangeArray($arguments) {
	    $oldArray = $this->getArrayCopy();
	    
		if (!is_array($arguments)) {
			$arguments = array($arguments);
		}
		foreach ($arguments as $name => $value) {
			try {
				$this->offsetSet($name, $value);
			} catch (\OutOfBoundsException | \UnexpectedValueException | \RuntimeException $oe) {
				\common\logging\Error::handle($oe);
				throw $oe;
			}
		}
		unset($tmp, $value);
		
		return $oldArray;
	}	
}