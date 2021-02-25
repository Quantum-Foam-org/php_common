<?php

namespace common\collections;

class DataModelStorage extends \ArrayObject {

    public function insert() {
        $models = $this->getIterator();

        while ($models->valid()) {
            $models->current()->insert();

            $models->next();
        }
    }
    
    public function delete() {
        $models = $this->getIterator();
        
        while ($models->valid()) {
            $models->current()->delete();
            
            $models->next();
        }
    }
    
    public function update() {
        $models = $this->getIterator();
        
        while ($models->valid()) {
            $models->current()->update();
            
            $models->next();
        }
    }
    
}