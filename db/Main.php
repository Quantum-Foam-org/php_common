<?php

namespace common\db;

/**
 * Class which extends PHP's PDO.
 * 
 * This should be used when conducting database queries
 */
class Main extends \PDO {

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
                self::$dbh->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
                self::$dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            } catch (\PDOException $e) {
                self::$dbh = FALSE;
            }
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
    public function fetchColumn($sql, array $params = array(), $columnKey = 0) {
        return $this->getSth($sql, $params)->fetchColumn($columnKey);
    }
    
    /**
     * Will return the result of a query by calling the PDOStatement::fetchAll method
     * @param String $sql - The sql to run
     * @param Array $params - the params to run in a prepared statement
     * @return Array 
     */
    public function fetchAll($sql, array $params = array()) {
        return $this->getSth($sql, $params)->fetchAll();
    }
    
    /**
     * @param String $sql - The sql to run
     * @param Array $params - the params to run in a prepared statement
     * @param Integer $cursor - cursor position, the default is 0
     * @return Array
     */
    public function getOne($sql, array $params = array(), $cursor = 0) {
        return $this->getSth($sql, $params)->fetch($this->getAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE), \PDO::FETCH_ORI_NEXT, $cursor);
    }

    /**
     * @param String $sql - The sql to run
     * @param Array $params - the params to run in a prepared statement
     * @param Integer $cursor - cursor position, the default is 0
     * @return Scalar
     */
    public function getVal($sql, array $params = array(), $cursor = 0) {
        if (($row = $this->getOne($sql, $params, $cursor))) {
            $val = array_pop($row);
        }
        unset($row);

        return $val;
    }

    /**
     * @param String $table - the name of the table to run the insert
     * @param Array $values - A key=>value pair of table field names and values
     * @return mixed - last insert_id on success
     */
    public function insert($table, array $values)
    {
        try
        {
            $stmt = $this->getSth('INSERT INTO ' . $table . ' (' . implode(', ', array_keys($values)) . ') VALUES(?'.str_repeat(', ?', count($values) - 1).')');
        }
        catch (\RuntimeException $e)
        {
            throw $e;
        }
        finally
        {
            unset($stmt);
        }
        
        return $this->lastInsertId();
    }
    
    public function update($table, array $values)
    {
        try
        {
            $stmt = $this->getSth('UPDATE ' . $table . ' SET ' . implode(' = ? ,', array_keys($values)).' = ?');
            $rowCount = $stmt->rowCount();
        }
        catch (\RuntimeException $e)
        {
            throw $e;
        }
        finally
        {
            unset($stmt);
        }
        
        return $rowCount;
    }
    
    public function delete($table, array $values)
    {
        try
        {
            $stmt = $this->getSth('DELETE FROM ' . $table . ' WHERE ' . implode(' = ? AND ', array_keys($values)).'  = ?');
            $rowCount = $stmt->rowCount();
        }
        catch (\RuntimeException $e)
        {
            throw $e;
        }
        finally
        {
            unset($stmt);
        }
        
        return $rowCount;
    }

    /**
     * @param String $sql - The sql to run
     * @param Array $params - the params to run in a prepared statement
     * @return PDOStatement - the statement of a PDO query
     */
    protected function getSth($sql, array $params = array())
    {
        if (!empty($params)) {
            try
            {
                $sth = $this->prepare($sql);
                $sth->execute($params);
            }
            catch(\PDOException $e)
            {
                Logger::obj()->writeException($e);
            }
        } else {
            $sth = $this->query($sql);
        }
        
        if ($sth->errorCode() === 0)
            throw new \RuntimeException('QUERY FAILED: '.$sth->debugDumpParams()."\nDRIVER ERROR: ".array_slice($sth->errorInfo(), 2, 1));
        
        return $sth;
    }

}
