<?php

namespace \local;

/**
 * Class which extends PHP's PDO.
 * 
 * This should be used when conducting database queries
 */

class Db extends PDO {

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
            } catch (PDOException $e) {
                throw $e;
            }
            self::$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }
        
        return self::$dbh;
    }
	
	/**
	 * Will call a method prefixed by _ and automatically pass in the PDOStatement
	 * @return Array
	 * @throws Exception when there is no method _.$name
	 */
	public function __call($name, array $arguments) {
		$name = '_'.$name;
		if (method_exists($this, $name)) {
			$get_sth_params = array($arguments[0], (isset($arguments[1]) ? $arguments[1] : array()));
			$sth = call_user_func_array(array($this, 'get_sth'), $get_sth_params);
			$result = call_user_func_array(array($this, $name), array($sth, isset($arguments[2]) ? $arguments[2] : 0));
		} else {
			throw new Exception('Method does not exist: '.__CLASS__.'::'.$name);
		}
		
		return $result;
	}
	
	
    /**
	 * Will return a column, via the columnKey property
	 * @param PDOStatement $sth of recently ran query
	 * @param Integer $columnKey - the column of the query to return, default is 0
	 * @return Array 
	 */
    public function _fetch_column(PDOStatement $sth, $columnKey = 0) {
		return $sth->fetchColumn($columnKey);
    }
    
	
	/**
	 * Will return the result of a query by calling the PDOStatement::fetchAll method
	 * @param PDOStatement $sth of recently ran query
	 * @return Array 
	 */
    public function _fetch_all(PDOStatement $sth) {
        return $sth->fetchAll();
    }
    
	
	/**
	 * @param PDOStatement $sth of recently ran query
	 * @param Integer $cursor - cursor position, the default is 0
	 * @return Array
	 */
	public function _get_one(PDOStatement $sth, $cursor = 0) {
        return $sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT, $cursor);
	}
	
	
	/**
	 * @param PDOStatement $sth of recently ran query
	 * @param Integer $cursor - cursor position, the default is 0
	 * @return Scalar
	 */
    public function _get_val(PDOStatement $sth, $cursor = 0) {
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