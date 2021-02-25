<?php

namespace common\collections;

use common\db\Mongo as Mongo;

class DataStorage extends \ArrayObject {
    private $bulk;
    private $namespace;
    
    public function offsetSet($index, $newval): void {
        if ($this->namespace === null) {
            $this->namespace = $newval->namespace;
        }
        
        if ($this->namespace !== $newval->namespace) {
            throw new \RuntimeException('Mongo Data Storage Models must all use the same namespace');
        }
        
        parent::offsetSet($index, $newval);
    }
    
    public function insert(array $options = []) : void {
        $models = $this->getIterator();

        while ($models->valid()) {
            $models->current()->prepareInsert($this->getBulk($options));

            $models->next();
        }
    }
    
    public function delete(array $options = []) : void {
        $models = $this->getIterator();
        
        while ($models->valid()) {
            $models->current()->prepareDelete($this->getBulk($options));
            
            $models->next();
        }
    }
    
    public function update(array $options = []) : void {
        $models = $this->getIterator();
        
        while ($models->valid()) {
            $models->current()->prepareUpdate($this->getBulk($options));
            
            $models->next();
        }
    }
    
    public function bulkWrite() {
        $mongo = new Mongo\Main();
        $mongo->bulkWrite($this->namespace, $this->getBulk());
    }
    
    private function getBulk(array $options) : MongoDB\Driver\BulkWrite {
        if (!(static::$bulk instanceof MongoDB\Driver\BulkWrite)) {
            static::$bulk = new MongoDB\Driver\BulkWrite($options);
        }
        return static::$bulk;
    }
    
}