<?php

namespace common\collections\DataStorage\Mongo;

use common\db\Mongo as Mongo;
use common\collections\DataStorage\AbstractDataStorage;
use common\logging\Logger;

class DataModel extends AbstractDataStorage {
    private $bulk;
    private $namespace;
    
    public function offsetSet($offset, $value) : void {
        if ($this->namespace === null) {
            $this->namespace = $value->namespace;
        }
        
        if ($this->namespace !== $value->namespace) {
            throw new \RuntimeException('Mongo Data Storage Models must all use the same namespace');
        }
        
        parent::offsetSet($offset, $value);
    }
    
    public function insert(array $options = []) : void {
        while ($this->valid()) {
            $this->current()->prepareInsert($this->getBulk($options));

            $this->next();
        }
    }
    
    public function delete(array $options = []) : void {
        while ($this->valid()) {
            $this->current()->prepareDelete($this->getBulk($options));
            
            $this->next();
        }
    }
    
    public function update(array $options = []) : void {
        while ($this->valid()) {
            $this->current()->prepareUpdate($this->getBulk($options));
            
            $this->next();
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