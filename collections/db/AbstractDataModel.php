<?php

namespace common\collections\db;

abstract class AbstractDataModelStorage  {
    private $offset = 0;
    
    abstract public function insert() : void;
    
    abstract public function delete() : void;
    
    abstract public function update() : void;
    
    public function offsetExists($offset) : bool  {
        return array_key_exists($offset, $this->dataModels);
    }
    
    public function offsetGet($offset) {
        return $this->dataModels[$offset];
    }
    
    public function offsetSet($offset, $value) : void {
        if (!is_int($offset)) {
            throw new UnexpectedValueException('Offset must be an integer');
        }
        
        $this->dataModels[$offset] = $value;
    }
    
    public function offsetUnset($offset) : void {
        unset($this->dataModels[$offset]);
    }
    
    public function current() {
        return $this->dataModels[$this->offset];
    }
    
    public function key() {
        return $this->offset;
    }

    public function next() {
        return $this->dataModels[++$this->offset];
    }
    
    public function rewind() {
        return $this->offset = 0;
    }

    public function valid() : bool {
        return $this->offset < $this->count();
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
