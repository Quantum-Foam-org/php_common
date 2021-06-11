<?php

namespace common\tests;

use common\logging\Logger as Logger;


class AbstractDbModelStorageTest {
    private $abstractModel;
    
    public function setUp() : void {
        $this->abstractModel = new AbstractDbModelStorageClass();
    }
    
    public function testCountableInterace() : bool {
        return count($this->abstractModel) === 10;
    }
}
