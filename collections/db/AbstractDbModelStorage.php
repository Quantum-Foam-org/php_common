<?php

namespace common\collections\db;

abstract class AbstractDbModelStorage implements \ArrayAccess, \Countable  {
    private $next = 0;
    
    protected $dataModels = [];
    
    abstract public function insert();
    
    abstract public function delete();
    
    abstract public function update();
    
    public function offsetExists($offset) : bool  {
        return array_key_exists($offset, $this->dataModels);
    }
    
    public function offsetGet($offset) {
        return $this->dataModels[$offset];
    }
    
    public function offsetSet($offset, $value) : void {
        if (!is_null($offset) && !is_int($offset)) {
            throw new \UnexpectedValueException('Offset must be an integer');
        }
        
        if (is_null($offset)) {
            $this->dataModels[] = $value;
        } else {
            $this->dataModels[$offset] = $value;
        }
    }
    
    public function offsetUnset($offset) : void {
        unset($this->dataModels[$offset]);
    }
    
    public function current() {
        return $this->dataModels[$this->next];
    }
    
    public function key() {
        return $this->next;
    }

    public function next() {
        return $this->dataModels[$this->next++];
    }
    
    public function rewind() {
        return $this->next = 0;
    }

    public function valid() : bool {
        return $this->next < $this->count();
    }
    
    public function count ( ) : int {
        return count($this->dataModels);
    }
    
    public function __unset($offset) {
        $this->offsetUnset($offset);
    }
    
    public function __isset($offset) {
        return isset($this->$offset);
    }
}
