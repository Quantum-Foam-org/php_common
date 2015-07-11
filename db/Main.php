<?php

namespace common;

/**
 * Class which extends PHP's PDO.
 * 
 * This should be used when conducting database queries
 */

class Db extends \PDO {
    private static $dbh = null; /// the PDO database handler
    
	
    public function __construct($dsn, $user, $pass) {
        parent::__construct($dsn, $user, $pass);
    }
    
	
	/**
	 * Will return the connection to the database
	 * @return PDO 
	 */
    public static function obj() {
        if (!self::$dbh) {
            try {
                self::$dbh = new db(DB_DSN, DB_USER_NAME, DB_USER_PASS);
            } catch (\PDOException $e) {
                throw $e;
            }
            self::$dbh->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        }
        
        return self::$dbh;
    }
	
	
    /**
	 * Will return a column, via the columnKey property
	 * @param String $sql - The sql to run
	 * @param Array $params - the params to run in a prepared statement
	 * @param Integer $columnKey - the column of the query to return, default is 0
	 * @return Array 
	 */
    public function fetch_column($sql, array $params = array(), $columnKey = 0) {
		return $this->get_sth($sql, $params)->fetchColumn($columnKey);
    }
    
	
	/**
	 * Will return the result of a query by calling the PDOStatement::fetchAll method
	 * @param String $sql - The sql to run
	 * @param Array $params - the params to run in a prepared statement
	 * @return Array 
	 */
    public function fetch_all($sql, array $params = array()) {
        return $this->get_sth($sql, $params)->fetchAll();
    }
    
	
	/**
	 * @param String $sql - The sql to run
	 * @param Array $params - the params to run in a prepared statement
	 * @param Integer $cursor - cursor position, the default is 0
	 * @return Array
	 */
	public function get_one($sql, array $params = array(), $cursor = 0) {
        return $this->get_sth($sql, $params)->fetch(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_NEXT, $cursor);
	}
	
	
	/**
	 * @param String $sql - The sql to run
	 * @param Array $params - the params to run in a prepared statement
	 * @param Integer $cursor - cursor position, the default is 0
	 * @return Scalar
	 */
    public function get_val($sql, array $params = array(), $cursor = 0) {
        if (($row = $this->get_one($sql, $params))) {
            $val =  array_pop($row);
        }
		unset($row);
		
        return $val;
    }
    
	
	/**
	 * @param String $table - the name of the table to run the insert
	 * @param Array $values - A key=>value pair of table field names and values
	 * @return mixed - lasst insert_id on success
	 */
    public function insert($table, array $values) {
        $fields = '';
        $quest = '';
        foreach ($values as $key => $value) {
            $fields .= $key.',';
            $quest .= '?,';
        }
        $fields = substr($fields, 0, -1);
        $quest = substr($quest, 0, -1);
        
        $stmt = $this->prepare('INSERT INTO '.$table.' ('.$fields.') VALUES('.$quest.')');
        if ($stmt->exec($values)) {
            $result = $this->lastInsertId();
        } else {
            $result = FALSE;
        }
        
        return $result;
    }
    
	
	/**
	 * @param String $sql - The sql to run
	 * @param Array $params - the params to run in a prepared statement
	 * @return PDOStatement - the statement of a PDO query
	 */
    private function get_sth($sql, array $params = array()) {
        if (!empty($params)) {
            $sth = $this->prepare($sql);
            $sth->execute($params);
        } else {
            $sth = $this->query($sql);
        }
        
        return $sth;
    }
}
