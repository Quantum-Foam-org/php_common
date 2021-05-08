<?php

namespace common\db\PDO;

interface WhereInterface {
    public function getWhere() : string;
    
    public function getValues() : array;
}