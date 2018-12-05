<?php

namespace common\obj;


class Config extends \ArrayObject {
	protected $config = [];
	private static $instance = null;
	
	public function offsetExists($offset){
		return property_exists($this, $offset);
	}
	
	public function offsetGet($offset) {
		if (!$this->offsetExists($offset)) {
			throw new \UnexpectedValueException('Offset, '.$offset.', does not exist');
		}
		return $this->$offset;
	}
	
	public function offsetSet($offset, $values) {
		if (property_exists($this, $offset)) {
			if (isset($this->config[$offset][0])) {
                            try {
                                if (is_array($values)) {
                                    foreach ($values as $value) {
                                        $this->$offset[] = $this->filterOffset($offset, $value);
                                    }
                                } else {
                                    $this->$offset = $this->filterOffset($offset, $values);
                                }
                            } catch(\UnexpectedValueException $e) {
                                throw $e;
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
	    
		if (is_array($arguments)) {
                    foreach ($arguments as $name => $value) {
                            try {
                                    $this->offsetSet($name, $value);
                            } catch (\OutOfBoundsException | \UnexpectedValueException | \RuntimeException $oe) {
                                    \common\logging\Error::handle($oe);
                                    throw $oe;
                            }
                    }
                } else {
                    parent::exchangeArray($arguments);
                }
		unset($tmp, $value);
		
		return $oldArray;
	}	
	
	public static function obj() : Config {
	    
	    if (self::$instance === null) {
	        $cls = get_called_class();
	        self::$instance = new $cls();
	    }
	    
	    return self::$instance;
	}
        
        private function filterOffset(string $offset, $value) {
            if (is_scalar($value) && !($value = filter_var($value, $this->config[$offset][0], (isset($this->config[$offset][1]) && is_array($this->config[$offset][1]) ? $this->config[$offset][1] : array())))) {
                throw new \UnexpectedValueException('Invalid value, '.$value.', for offset '.$offset);
            }
            
            return $value;
        }
}