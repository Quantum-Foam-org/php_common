<?php

namespace common\db\PDO;

use common\logging\Logger;

/**
 * Class which extends PHP's PDO.
 * 
 * This should be used when conducting database queries
 */
class Main extends \PDO {

    private static $dbh = null; /// the PDO database handler

    /**
     * Will return the connection to the database
     * @return boolean|PDO 
     */
    public static function obj() : Main {
        if (!self::$dbh) {
            try {
                self::$dbh = new \common\db\PDO\Main(
                    \common\Config::obj()->system['dbDsn'],
                    \common\Config::obj()->system['dbUser'], 
                    \common\Config::obj()->system['dbPass']);
                self::$dbh->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
                self::$dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            } catch (\PDOException $e) {
                throw $e;
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
    public function fetchAll(string $sql, array $params = array()) : array {
        try {
            $stmt = $this->getSth($sql, $params);
        } catch (\RuntimeException $e) {
            throw $e;
        }

        return $stmt->fetchAll();
    }

    /**
     * closes the cursor so that execute can be called again
     * @param \PDOStatement $stmt a PDOStatement object
     * @return boolean
     */
    public function closeCursor(\PDOStatement $stmt) : bool {
        return $stmt->closeCursor();
    }
    
    /**
     * @param String $sql - The sql to run
     * @param Array $params - the params to run in a prepared statement
     * @return Array
     */
    public function getOne(\PDOStatement $stmt) {
        return $stmt->fetch($this->getAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE), \PDO::FETCH_ORI_NEXT);
    }

    /**
     * @param String $sql - The sql to run
     * @param Array $params - the params to run in a prepared statement
     * @return scalar
     */
    public function getVal(\PDOStatement $stmt) {
        if (($row = $this->getOne($sql, $params))) {
            $val = array_pop($row);
        }
        unset($row);

        return $val;
    }

    /**
     * @param String $table - the name of the table to run the insert
     * @param Array $values - A key=>value pair of table field names and values
     * @return string - last insert_id
     */
    public function insert(string $table, array $values) : string {
        try {
            $placeholders = \substr(\str_repeat('?, ', \count($values)), 0, -2);
            
            $stmt = $this->getSth(sprintf('INSERT INTO `%s` (`%s`) VALUES (%s)', $table, \implode('`, `', \array_keys($values)), $placeholders), $values);
        } catch (\RuntimeException $e) {
            throw $e;
        }

        return $this->lastInsertId();
    }

    /**
     * @param String $table - the name of the table to run the insert
     * @param array $values - A key=>value pair of table field names and values
     * @param array $where - A key=>value pair of table field names and values
     * @throws RuntimeException
     * @return integer
     */
    public function update($table, array $values, ?WhereInterface $where = null) : int {
        if ($where === null) {
            $where = new Where();
        }
        
        try {
            $stmt = $this->getSth(
                sprintf('UPDATE `%s` SET `%s` = ?', $table, \implode('` = ? , `', \array_keys($values)), $where->getWhere()), 
                \array_merge($values, $where->getValues()));
            $rowCount = $stmt->rowCount();
        } catch (\RuntimeException $e) {
            throw $e;
        }

        return $rowCount;
    }

    /**
     * 
     * @param String $table - the name of the table to run the insert
     * @param array $values - A key=>value pair of table field names and values
     * @throws RuntimeException
     * @return integer
     */
    public function delete($table, ?WhereInterface $where = null) : int {
        if ($where === null) {
            $where = new Where();
        }
        try {
            $stmt = $this->getSth(sprintf('DELETE FROM `%s` %s', $table, $where->getWhere()), $where->getValues());
            $rowCount = $stmt->rowCount();
        } catch (\RuntimeException $e) {
            throw $e;
        }

        return $rowCount;
    }

    /**
     * @param String $sql - The sql to run
     * @param Array $params - the params to run in a prepared statement
     * @return PDOStatement - the statement of a PDO query
     */
    public function getSth($sql, array $params = array()) : \PDOStatement {
        if (!empty($params)) {
            try {
                $sth = $this->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
                $sth->execute(array_values($params));
            } catch (\PDOException $e) {
                Logger::obj()->writeException($e);
            }
        } else {
            $sth = $this->query($sql);
        }

        if ($sth->errorCode() !== '00000') {
            throw new \RuntimeException('QUERY FAILED: ' . \var_export($sth->debugDumpParams(), 1) . "\nDRIVER ERROR: " . \var_export($sth->errorInfo(), 1) . "\nQUERY SQL: " . $sth->queryString);
        }

        return $sth;
    }
    
    /**
     * Begins a PDO exception
     * 
     * Logs a PDOException upon failure
     * 
     * @return bool
     */
    public function beginTransaction() : bool {
        try {
            $transaction = Main::obj()->beginTransaction();
        } catch (\PDOException $e) {
            Logger::obj()->writeException($e);
            
            $transaction = false;
        }
        
        return $transaction;
    }
    
    /**
     * 
     * Commits a transaction
     * 
     * Will log PDO Exception if the commit fails
     * 
     * @return bool
     */
    public function commit() : bool {
        try {
            $commit = $this->commit();
        } catch (\PDOException $e) {
            Logger::obj()->writeException($e);
            
            $commit = true;
        }
        
        return $commit;
    }
    
    /**
     * Rolls back a transaction
     * 
     * WIll log a PDO Exception if the rollback fails
     * 
     * @return bool
     */
    public function rollback() : bool {
        try {
            $rollback = $this->rollback();
        } catch (\PDOException $e) {
            Logger::obj()->writeException($e);
            
            $rollback = false;
        }
        
        return $rollback;
    }

    public function __destruct() {
        self::$dbh = null;
    }
}
