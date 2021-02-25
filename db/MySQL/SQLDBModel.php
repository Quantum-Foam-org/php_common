<?php 

namespace \common\db\MySQL;

use query\Main as Select;
use \common\obj\Config as objectConfig;

/**
 * Extend and configure properties to have a database model
 * 
 */
Class DbModel extends objectConfig {
    protected $pkField;
    protected $pkId;
    protected $table;
    protected $tables;
    protected $joins;
    protected $order;
    protected $limit;
    protected $db;
    
    public function __construct() {
        $this->db = \common\db\PDO\Main::obj();
        $this->select = new Select();
        $this->select->addFields(\array_keys($this->getArrayCopy()))->
                    setTables($this->tables)->
                    setJoins($this->joins)->
                    setOrder($this->order)->
                    setLimit($this->limit);
    }
     
    /**
     * Inserts a new row into the database
     * @return int the primary key
     */
    public function insert() : int {
       try {
        $this->pkId = $this->db->insert($this->table, $this->getArrayCopy());
       } catch (\RuntimeException $e) {
           $this->pkId = false;
       }
       
       return $this->pkId;
    }
    
    /**
     * Will delete a row from the database
     * 
     * @return null|int the row count or false on failer
     */
    public function delete() : ?int {
        try {
            $rowCount = $this->db->delete($this->table, $this->getPkWhere());
        } catch (\RuntimeException $e) {
            \common\logging\Logger::obj()->writeException($e);
            $rowCount = null;
        }
        
        return $rowCount;
    }
    
    /**
     * Will update a row in the database
     * 
     * @return null|int
     */
    public function update() : ?int {
        try {
            $rowCount = $this->db->update(
                    $this->table, 
                    $this->getArrayCopy(), 
                    $this->getPkWhere());
        } catch (\RuntimeException $e) {
            \common\logging\Logger::obj()->writeException($e);
            $rowCount = null;
        }
        
        return $rowCount;
    }
    
    /**
     * The where clause using primary key
     * 
     * @return Where
     */
    protected function getPkWhere() : Where {
        $where = new Where();
        $where->addWhereExpression(null, null, $this->pkField, '=', null, $this->pkId);
        
        return $where;
    }
    
    /**
     * Will get a row from the database
     * 
     * @return array
     */
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
     */
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
     */
    protected function getQuery() : Select {
        $this->select->addWhere($this->getPKWhere());
        
        return $this->select;
    }
}