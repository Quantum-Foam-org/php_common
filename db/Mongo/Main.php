<?php

namespace common\db\Mongo;

use common\logging\Logger;

class Main 
{
    protected $mongodb;
    protected static $mongoDbh;
    
    public function __construct($uri) {
        try {
            $this->mongodb =  new \MongoDB\Driver\Manager($uri);
            
            $this->mongodb->startSession();
        } catch (\MongoDB\Driver\Exception\InvalidArgumentException 
                | \MongoDB\Driver\Exception\RuntimeException $e) {
            Logger::obj()->writeException($e);
        }
    }
    
    public static function obj() : Main {
        if (static::$mongoDbh === null) {
            $uri = sprintf('mongodb://%s:%s@%s:%d', 
                    \common\Config::obj()->system['mongo']['user'],
                    \common\Config::obj()->system['mongo']['password'],
                    \common\Config::obj()->system['mongo']['host'], 
                    \common\Config::obj()->system['mongo']['port']);

            try {
                static::$mongoDbh =  new Main($uri);
            } catch (\MongoDB\Driver\Exception\InvalidArgumentException  
                    | \MongoDB\Driver\Exception\RuntimeException $e) {
                throw $e;
            }
        }
        
        return static::$mongoDbh;
    }
    
    protected function command(
            string $namespace, 
            array $document, 
            array $options = []) : ?\MongoDB\Driver\Cursor { 
        try {
            $cursor = $this->mongodb->executeCommand(
                    $namespace, 
                    new \MongoDB\Driver\Command($document), 
                    $options);
        } catch(\MongoDB\Driver\Exception\ExecutionTimeoutException $e) {
            Logger::obj()->writeException($e);
            
            $cursor = null;
        }
        
        return $cursor;
    }
    
    protected function query(
            string $namespace, 
            array $filter, 
            array $queryOptions = [],
            array $options = array()) : ?\MongoDB\Driver\Cursor {
        try {
            $cursor = $this->mongodb->executeQuery(
                    $namespace, 
                    new \MongoDB\Driver\Query(
                            $filter, 
                            $queryOptions), 
                    $options);
        } catch(\MongoDB\Driver\Exception\ExecutionTimeoutException $e) {
            Logger::obj()->writeException($e);
            $cursor = null;
        }
        
        return $cursor;
    }
    
    public function bulkWrite(
            string $namespace, 
            \MongoDB\Driver\BulkWrite $bulk) : ?\MongoDB\Driver\WriteResult {
        try {
            $writeResult = $this->mongodb->executeBulkWrite(
                    $namespace, 
                    $bulk, 
                    new \MongoDB\Driver\WriteConcern(1, 500));
        } catch(\MongoDB\Driver\Exception\ExecutionTimeoutException 
                | \MongoDB\Driver\Exception\InvalidArgumentException $e) {
            Logger::obj()->writeException($e);
            $writeResult = null;
        }
        
        return $writeResult;
    }
    
    public function insert(
            string $namespace, 
            MongoModel $document) : ?\MongoDB\Driver\WriteResult {
        $bulk = new \MongoDB\Driver\BulkWrite();
        $bulk->insert($document->getArrayCopy());
        
        return $this->bulkWrite($namespace, $bulk);
    }
    
    public function update(
            string $namespace, 
            MongoModel $document,
            array $updateOptions =  []) : ?\MongoDB\Driver\WriteResult {
        $bulk = new \MongoDB\Driver\BulkWrite();
        
        $bulk->update($document->getFilter(), $document->getArrayCopy(), $updateOptions);

        
        return $this->bulkWrite($namespace, $bulk);
    }
    
    public function delete(
            string $namespace, 
            MongoModel $document,
            array $deleteOptions = []) : ?\MongoDB\Driver\WriteResult {
        $bulk = new \MongoDB\Driver\BulkWrite();
        
        $bulk->delete($document->getFilter(), $deleteOptions);

        
        return $this->bulkWrite($namespace, $bulk);
    }
}