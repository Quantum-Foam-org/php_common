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
Class DbModel extends objectConfig {
    protected $pkField;
    protected $pkId;
    protected $namespace;
    protected $db;
    protected $order;
    protected $limit;
    
    public function __construct() {
        $this->db = \common\db\Mongo::obj();
       /* $this->select = new Select();
        $this->select->addFields(\array_keys($this->arrayCopy()))->
                    setTables($tables)->
                    setJoins($this->joins)->
                    setOrder($this->order)->
                    setLimit($this->limit);
        */
    }
     
    /**
     * Inserts a new row into the database
     * @return int the primary key
     */
    public function prepareInsert(MongoDB\Driver\BulkWrite $bulk) : void {
       $bulk->insert($this->getArrayCopy());
    }
    
    /**
     * Will delete a row from the database
     * 
     * @return null|int the row count or false on failer
     */
    public function prepareDelete(MongoDB\Driver\BulkWrite $bulk) : void {
        $bulk->delete([$this->pkField => $this->pkId]);
    }
    
    /**
     * Will update a row in the database
     * 
     * @return null|int
     */
    public function prepareUpdate(MongoDB\Driver\BulkWrite $bulk) : void {
        $bulk->update([$this->pkField => $this->pkId], $this->getArrayCopy());
    }
    
    /**
     * The where clause using primary key
     * 
     * @return Where
    protected function getPkWhere() : Where {
        $where = new Where();
        $where->addWhereExpression(null, null, $this->pkField, '=', null, $this->pkId);
        
        return $where;
    }
    
    /**
     * Will get a row from the database
     * 
     * @return array
    public function get() : array {
       $select = $this->getQuery();
       
       try {
        $row = $this->db->getOne($this->db->getSth((string)$select, $select->getValues()));
       } catch (\RuntimeException $e) {
        \common\logging\Logger::obj()->writeException($e);
        $row = [];
       }
       
       return $row;
    }
    
    /**
     * Populates the object with a row from the database
     * 
     * @param int $id the primary key value
     * @return bool
    public function populateFromDb(int $id) : bool {
        $this->pkId = $id;
        $row = $this->get();
        
        foreach ($row as $column => $value) {
            $this->offsetSet($column, $value);
        }
        
        unset($row, $column, $value, $id);
    }
    
    /**
     * The select query 
     * 
     * @return \query\Main
    protected function getQuery() : Select {
        $this->select->addWhere($this->getPKWhere());
        
        return $this->select;
    }
     * 
     */
}