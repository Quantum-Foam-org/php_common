<?php

namespace common\tests;

use common\collections\db\AbstractDbModelStorage;

class AbstractDbModelStorageClass extends AbstractDbModelStorage {
    
    public function __construct() {
        $this->dataModels = range(0, 9);
    }
    
    public function insert() {
        
    }
    
    public function delete() {
        
    }
    
    public function update() {
        
    }
    
} 
