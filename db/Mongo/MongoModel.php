<?php

namespace \common\db\MySQL;

use common\collections\Mongo as Mongo;
use common\obj\Config as objectConfig;

/**
 * Extend and configure properties to have a database model
 * 
 * @author Michael Alaimo
 *
 */
Class MongoModel extends objectConfig {

    protected $pkField;
    protected $pkId;
    protected $namespace;
    protected $db;
    protected $order;

    public function __construct() {
        $this->db = \common\db\Mongo::obj();
    }

    public function prepareInsert(MongoDB\Driver\BulkWrite $bulk): void {
        $bulk->insert($this->getArrayCopy());
    }

    public function prepareDelete(MongoDB\Driver\BulkWrite $bulk): void {
        $bulk->delete([$this->pkField => $this->pkId]);
    }

    public function prepareUpdate(MongoDB\Driver\BulkWrite $bulk): void {
        $bulk->update([$this->pkField => $this->pkId], $this->getArrayCopy());
    }

    /**
     * Inserts a new row into the database
     * @return int the primary key
     */
    public function insert(): ?MongoDB\Driver\WriteResult {
        return $this->db->insert($this->namespace, $this);
    }

    /**
     * Will update a row in the database
     * 
     * @return null|int
     */
    public function update(): ?MongoDB\Driver\WriteResult {
        return $this->db->update($this->namespace, $this);
    }

    /**
     * Will delete a row from the database
     * 
     * @return null|int the row count or false on failer
     */
    public function delete(): ?MongoDB\Driver\WriteResult {
        return $this->db->delete($this->namespace, $this);
    }

    public function getFilter(): array {
        return [$this->pkField => $this->pkId];
    }

    /**
     * Will get a row from the database
     * 
     * @return array
     */
    public function get(): ?array {
        try {
            $cursor = $this->db->query($this->namespace, $this->getFilter());
            $document = $cursor->current();
        } catch (\MongoDB\Driver\Exception\InvalidArgumentException
                | \MongoDB\Driver\Exception\ConnectionException
                | \MongoDB\Driver\Exception\AuthenticationException
                | \MongoDB\Driver\Exception\RuntimeException $e) {
            \common\logging\Logger::obj()->writeException($e);
            $document = null;
        }

        return $document;
    }

    public function populateFromDb(string $id) {
        $this->pkId = $id;
        $document = $this->get();

        foreach ($document as $column => $value) {
            $this->offsetSet($column, $value);
        }

        unset($document, $column, $value, $id);
    }
}
