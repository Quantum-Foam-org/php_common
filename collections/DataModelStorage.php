<?php

namespace common\collections;

use \common\db\DbModel as DbModel;

class DataModelStorage extends \ArrayObject {

    public function insert() {
        $models = $this->getIterator();

        while ($models->valid()) {
            $models->insert();

            $models->next();
        }
    }
    
    public function insert() {
        $models = $this->getIterator();
        
        while ($models->valid()) {
            $models->delete();
            
            $models->next();
        }
    }
    
    public function update() {
        $models = $this->getIterator();
        
        while ($models->valid()) {
            $models->update();
            
            $models->next();
        }
    }
    
}