<?php

namespace common\collections;

use common\db;
use common\logging\Logger;

class DataModelStorage extends \ArrayObject {
    private $error = false;
    
    public function insert() : bool {
        PDO\Main::obj()->beginTransaction();
        
        $models = $this->getIterator();

        while ($models->valid()) {
            try {
                $models->current()->insert();
            } catch (\RuntimeException $e) {
                $this->error = true;
                Logger::obj()->writeException($e);
                
                PDO\Main::obj()->rollback();
                break;
            }
            
            $models->next();
        }
        
        if ($this->error === false) {
            PDO\Main::obj()->commit();
        }
        
        return !$this->error;
    }
    
    public function delete() : bool {
        PDO\Main::obj()->beginTransaction();
        
        $models = $this->getIterator();
        
        while ($models->valid()) {
            try {
                $models->current()->delete();
            } catch (\RuntimeException $e) {
                $this->error = true;
                Logger::obj()->writeException($e);
                
                PDO\Main::obj()->rollback();
                break;
            }
            
            $models->next();
        }
        
        if ($this->error === false) {
            PDO\Main::obj()->commit();
        }
        
        return !$this->error;
    }
    
    public function update() : bool {
        PDO\Main::obj()->beginTransaction();
        
        $models = $this->getIterator();
        
        while ($models->valid()) {
            try {
                $models->current()->update();
            } catch (\RuntimeException $e) {
                $this->error = true;
                Logger::obj()->writeException($e);
                
                PDO\Main::obj()->rollback();
                break;
            }
            
            $models->next();
        }
        
        if ($this->error === false) {
            PDO\Main::obj()->commit();
        }
        
        return !$this->error;
    }
}