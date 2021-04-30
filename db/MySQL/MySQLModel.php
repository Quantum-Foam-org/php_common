<?php 

namespace common\db\MySQL;

use common\obj\Config as objectConfig;
use common\logging\Logger;
use common\db\DbModelInterface;

/**
 * Extend and configure properties to have a database model
 * 
 */
Class MySQLModel extends objectConfig implements DbModelInterface {
    protected $pkField;
    protected $pkId;
    protected $table;
    protected $tables = [];
    protected $joins = [];
    protected $order = [];
    protected $limit = [1];
    protected $db;
    protected $select;
    
    
    public function __construct() {
        $this->db = \common\db\PDO\Main::obj();
        $this->select = new Select();
        $this->select->addFields(\array_keys($this->getArrayCopy()))->
                    addTables($this->tables)->
                    addJoins($this->joins)->
                    addOrder($this->order)->
                    addLimit($this->limit);
    }
     
    /**
     * Inserts a new row into the database
     * @return int the primary key
     */
    public function insert() {
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
     * @return null|int the row count or false on failure
     */
    public function delete() {
        try {
            $rowCount = $this->db->delete($this->table, $this->getPkWhere());
        } catch (\RuntimeException $e) {
            Logger::obj()->writeException($e);
            $rowCount = null;
        }
        
        return $rowCount;
    }
    
    /**
     * Will update a row in the database
     * 
     * @return null|int
     */
    public function update() {
        try {
            $rowCount = $this->db->update(
                    $this->table, 
                    $this->getArrayCopy(), 
                    $this->getPkWhere());
        } catch (\RuntimeException $e) {
            Logger::obj()->writeException($e);
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
    public function get() : ?array {
       $select = $this->getQuery();
       
       try {
        $row = $this->db->getOne($this->db->getSth((string)$select, $select->getValues()));
       } catch (\RuntimeException $e) {
        Logger::obj()->writeException($e);
        $row = null;
       }
       
       return $row;
    }
    
    /**
     * Populates the object with a row from the database
     * 
     * @param int $id the primary key value
     * @return void
     */
    public function populateFromDb($id) : bool {
        $this->pkId = $id;
        $row = $this->get();
        
        foreach ($row as $column => $value) {
            $this->offsetSet($column, $value);
        }
        
        return (bool)$row;
    }
    
    /**
     * The select query 
     * 
     * @return Select
     */
    protected function getQuery() : Select {
        $this->select->addWhere($this->getPKWhere());
        
        return $this->select;
    }
}