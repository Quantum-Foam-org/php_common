<?php

namespace common\tests;

use common\db\Mongo\MongoModel;
use common\filters\Mongo\ObjectId;

class MongoTestMongoModelClass extends MongoModel {
    public $namespace = 'db.MongoTestMongoModelClass';
    public $field1;
    public $field2;
    
    protected $config = [
        'field1' => [
            FILTER_VALIDATE_URL
        ],
        'field2' => [
            FILTER_SANITIZE_STRING
        ]
    ];
} 
