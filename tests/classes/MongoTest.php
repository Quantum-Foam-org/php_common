<?php

namespace common\tests;

use common\logging\Logger as Logger;

class MongoTest {
    
    public function testMongoQuery() : bool {
        Logger::obj()->write('1message', 1, true);
        return true;
    }
    
        public function testMongoDelete() : bool {
        
        return false;
    }
}

