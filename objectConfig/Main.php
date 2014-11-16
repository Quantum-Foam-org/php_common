<?php

namespasce \local\classes\objectConfig;

class Main extends ArrayIterator {
	protected $config = array();
	
		
	public function offsetExists($offset) {
		return property_exists($this->$offset);
	}
	
	public function offsetGet($offset) {
		return $this->$offset;
	}
	
	public function offsetSet($offset, $value) {
		if (property_exists($this->$offset)) {
			if (isset($this->config[$offset][0])) {
				if (!($this->$offset = filter_var($value, $this->config[$offset][0], (is_array($this->config[$offset][1]) ? $this->config[$offset][1] : array())))) {
					throw new UnexpectedValueException('Invalid value, '.$value.', for offset '.$offset);
				}
			} else {
				throw new RuntimeException('Filter has not been set for offset '.$offset);
			}
		} else {
			throw new OutOfBoundsException($offset.' does not exist for '.__CLASS__);
		}
	}
	
	public function offsetUnset($offset) {
		$this->$offset = null;
	}
}
