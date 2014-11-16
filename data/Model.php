<?php

namespace \local\classes\models;

class DataModel {

    protected $tableName;
    protected $idWhere;
    public $id;
    protected $idField;
    protected $valuesDefault = array();
    protected $values = array();
    public $fields = array();
    private static $noId = -1;
    protected static $instance = array(-1 => null);

    public static function obj($id = null, $admin = FALSE) {
        $class = get_called_class();
        if ($id === null) {
            $id = self::$noId;
        }
        if (!isset(self::$instance[$id]) || !(self::$instance[$id] instanceof $class)) {
            self::$instance[$id] = new $class($id);
        }

        return self::$instance[$id];
    }

    public function __construct($id) {
        $db = db::obj();
        if (count($this->fields) && $id > -1) {
            $this->id = $id;
            $this->idWhere = '`' . $this->idField . '` = ' . $db->quote($this->id);

            $this->values = $this->valuesDefault = $db->get_one('SELECT ' . implode(', ', array_keys($this->fields)) . ' FROM ' . $this->tableName . ' WHERE ' . $this->idWhere);
        }
    }

    protected function getDelta() {
        $delta = array();
        if ($this->valuesDefault != $this->values) {
            foreach ($this->valuesDefault as $i => $field) {
                if ($field != $this->values[$i]) {
                    $delta[$i] = $this->values[$i];
                }
            }
        }
        if (array_keys($this->fieldsDefault) != array_keys($this->fields)) {
            logger::obj()->write('Incorrect field assignment', 0);
            $delta = array();
        }

        return $delta;
    }

    private function getChangedSqlUpdate($changed) {
        $sqlSet = 'SET ';
        $values = array();

        foreach ($changed as $i => $field) {
            $values[] = $this->hasSqlFormat($i, $field);
            $sqlSet .= $i . ' = ?, ';
        }
        $sqlSet = substr($sqlSet, 0, -2);
        return array($sqlSet, $values);
    }

    private function getChangedSqlInsert($changed) {
        $sqlSet = '(' . implode(', ', array_keys($changed)) . ') VALUES (';
        $values = array();
        foreach ($changed as $i => $field) {
            $values[] = $this->hasSqlFormat($i, $field);
            $sqlSet .= '?, ';
        }
        $sqlSet = substr($sqlSet, 0, -2) . ')';

        return array($sqlSet, $values);
    }

    private function hasSqlFormat($name, $data) {
        $type = $this->fieldTypes[$name];
        switch ($type) {
            case 'date':
                sanitize::clean($data);
                $data = date('Y-m-d', strtotime($data));
                break;
        }

        return $data;
    }

    public function commit() {
        $commit = FALSE;
        $numRows = 0;
        $db = db::obj();
        logger::obj()->write('commit');

        $changed = $this->getDelta();

        logger::obj()->write($changed);

        try {
            $db->beginTransaction();
            if ($this->id == -1) {
                $this->id = null;
            }

            if (!empty($changed)) {
                if ($this->id) { // update
                    list($sql, $values) = $this->getChangedSqlUpdate($changed);
                    $commit = $numRows = $db->update($this->tableName, array($this->idField => $this->id), $values);
                } else { // insert
                    list($sql, $values) = $this->getChangedSqlInsert($changed);
                    $commit = $this->fields[$this->idField] = $this->id = $db->insert($this->tableName, $values);
                    self::$instance[$this->id] = self::$instance[self::$noId];
                    self::$instance[self::$noId] = null;
                }
            } else {
                $commit = null;
            }

            if ($commit === TRUE) {
                $db->commit();
                logger::obj()->write('Row updated/inserted into table: ' . $this->tableName . '. Row ID: ' . $this->id);
                $commit = ($numRows == 1 ? TRUE : null);
            } else if ($commit !== null) {
                $db->rollBack();
                $commit = FALSE;
            }
        } catch (Exception $e) {
            $db->rollBack();
            $commit = FALSE;
            logger::obj()->write('FAILED: ' . $this->tableName . '. Row ID: ' . $this->id);
        }

        logger::obj()->write('commit');


        return $commit;
    }

}
