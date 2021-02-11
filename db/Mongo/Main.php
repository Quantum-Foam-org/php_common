<?php

namespace common\db\mongodb;

class Main 
{
    protected $mongodb;
    protected static $mongoDbh;
    
    public function __construct($uri) {
        $this->mongodb =  new \MongoDB\Driver\Manager($uri);
    }
    
    public static function obj() : Main {
        if (static::$mongoDbh === null) {
            \common\Config::obj()->system['mongo']['dbPass'];

            $uri = sprintf('mongodb://%s:%s@%s:%d', 
                    \common\Config::obj()->system['mongo']['user'],
                    \common\Config::obj()->system['mongo']['password'],
                    \common\Config::obj()->system['mongo']['host'], 
                    \common\Config::obj()->system['mongo']['port']);

            try {
                static::$mongoDbh =  new Main($uri);
            } catch (\MongoDB\Driver\Exception\InvalidArgumentException  | \MongoDB\Driver\Exception\RuntimeException $e) {
                \common\logging\Logger::obj()->writeException($e);
            }
        }
        
        return static::$mongoDbh;
    }
    
    protected function command(
            string $namespace, 
            array $command, 
            array $options = array()) : ?\MongoDB\Driver\Cursor { 
        try {
            $cursor = $this->mongodb->executeCommand($namespace, new \MongoDB\Driver\Command($command), $options);
        } catch(\MongoDB\Driver\Exception\ExecutionTimeoutException $e) {
            \common\logging\Logger::obj()->writeException($e);
            
            $cursor = null;
        }
        
        return $cursor;
    }
    
    protected function query(
            string $namespace, 
            array $query, 
            array $options = array()) : ?\MongoDB\Driver\Cursor {
        try {
            $cursor = $this->mongodb->executeQuery($namespace, new \MongoDB\Driver\ExecuteQuery($query), $options);
        } catch(\MongoDB\Driver\Exception\ExecutionTimeoutException $e) {
            \common\logging\Logger::obj()->writeException($e);
            $cursor = null;
        }
        
        return $cursor;
    }
    
    public function bulkWrite(
            string $namespace, 
            MongoDB\Driver\BulkWrite $bulk) : ?MongoDB\Driver\WriteResult {
        try {
            $writeResult = $this->mongodb->executeBulkWrite(
                    $namespace, 
                    $bulk, 
                    new MongoDB\Driver\WriteConcern(1, 500));
        } catch(\MongoDB\Driver\Exception\ExecutionTimeoutException $e) {
            \common\logging\Logger::obj()->writeException($e);
            $writeResult = null;
        }
        
        return $writeResult;
    }
    
    public function insert(
            string $namespace, 
            MongoDocument $document) : ?MongoDB\Driver\WriteResult {
        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->insert($document);
        
        return $this->bulkWrite($namespace, $bulk);
    }
    
    public function update(
            string $namespace, 
            MongoDocument $document,
            array $updateOptions =  array()) : ?MongoDB\Driver\WriteResult {
        $bulk = new MongoDB\Driver\BulkWrite();
        
        $bulk->update($document->getFilter(), $document->getArrayCopy(), $updateOptions);

        
        return $this->bulkWrite($namespace, $bulk);
    }
    
    public function delete(
            string $namespace, 
            MongoDocument $document,
            array $deleteOptions = array()) : ?MongoDB\Driver\WriteResult {
        $bulk = new MongoDB\Driver\BulkWrite();
        
        $bulk->delete($document->getFilter(), $deleteOptions);

        
        return $this->bulkWrite($namespace, $bulk);
    }
}