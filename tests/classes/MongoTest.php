<?php

namespace common\tests;

use common\logging\Logger as Logger;
use common\db\Mongo\Main as Mongo;

class MongoTest {
    private $mongodb;
    private $insertModel;
    
    public function setUp() : void {
        $this->mongodb = Mongo::obj();
        $this->insertModel = new MongoTestMongoModelClass();
        $this->insertModel->field1 = 'http://www.debian.org';
        $this->insertModel->field2 = 'field2';
        
    
    }
    
    public function testMongoQuery() : bool {
        $this->mongodb->insert($this->insertModel->namespace, $this->insertModel);
        //Logger::obj()->write('1message', 1, true);
        return true;
    }
    
    public function testMongoDelete() : bool {
        
        return false;
    }
}

