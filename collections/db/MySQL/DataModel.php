<?php

namespace common\collections\db\MySQL;

use common\collections\db\AbstractDataModelStorage;
use common\db\PDO;
use common\db\MySQL;
use common\logging\Logger;

class DataModelStorage extends AbstractDataModelStorage {
    private $error = false;
    private $dataModels = [];
    
    public function insert() : bool {
        PDO\Main::obj()->beginTransaction();
        
        while ($this->valid()) {
            try {
                $this->current()->insert();
            } catch (\RuntimeException $e) {
                $this->error = true;
                Logger::obj()->writeException($e);
                
                PDO\Main::obj()->rollback();
                break;
            }
            
            $this->next();
        }
        
        if ($this->error === false) {
            PDO\Main::obj()->commit();
        }
        
        return !$this->error;
    }
    
    public function delete() : bool {
        PDO\Main::obj()->beginTransaction();
        
        while ($this->valid()) {
            try {
                $this->current()->delete();
            } catch (\RuntimeException $e) {
                $this->error = true;
                Logger::obj()->writeException($e);
                
                PDO\Main::obj()->rollback();
                break;
            }
            
            $this->next();
        }
        
        if ($this->error === false) {
            PDO\Main::obj()->commit();
        }
        
        return !$this->error;
    }
    
    public function update() : bool {
        PDO\Main::obj()->beginTransaction();
        
        while ($this->valid()) {
            try {
                $this->current()->update();
            } catch (\RuntimeException $e) {
                $this->error = true;
                Logger::obj()->writeException($e);
                
                PDO\Main::obj()->rollback();
                break;
            }
            
            $this->next();
        }
        
        if ($this->error === false) {
            PDO\Main::obj()->commit();
        }
        
        return !$this->error;
    }
    
    public function offsetSet($offset, $value) : void  {
        if (!($value instanceof MySQL\MySQLModel)) {
            throw new \UnexpectedValueException('Value must be an instance of MySQL\MySQLModel');
        }
        
        parent::offsetSet($offset, $value);
    }
}