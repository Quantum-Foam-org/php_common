<?php
namespace \common\db\query;

use \common\db\Util\Query as Where;

/**
 * Creates a SQL select statement and returns the result set
 */
class Main
{

    private $fieldDef = [];

    private $selectFields = [];

    private $tables = [];

    private $joins = [];
    
    private $where = '';
    
    private $values = [];

    private $order = '';

    private $limit = '';

    private $query = '';

    /**
     *
     * @param array $fields
     *            - The field definition
     */
    public function addFields(array $fields)
    {
        foreach ($fields as $def) {
            $this->fieldDef[] = $def;
            $this->selectFields[] = (strlen($def[1]) ? $def[1] . '.' : '') . $def[0];
        }
        unset($fields, $def);
        return $this;
    }

    public function addTables(array $tables)
    {
        foreach ($tables as $def) {
            if (! isset($def[1], $def[0])) {
                throw new Exception('Must set table and table alias', - 1);
            } else {
                $this->tables[$def[1]] = $def[0];
            }
        }
        unset($tables, $def);
        return $this;
    }

    public function addJoins(array $joins)
    {
        foreach ($joins as $def) {
            if (isset($def[0], $def[1], $def[2])) {
                throw new Exception('Must set table alias, join type and join condition', - 1);
            } else {
                $this->joins[$def[0]] = array(
                    $def[1],
                    $def[2]
                );
            }
        }
        unset($joins, $def);
        return $this;
    }
    
    public function addWhere(Util\Query $where) {
        $this->where = $where->getWhere();
        $this->values = $this->where->getValues();
        
        return $this;
    }

    public function addOrder(array $order)
    {
        $this->order = (isset($order[0]) ? 'ORDER BY ' . $order[0] . ' ' . (! isset($order[1]) ? 'ASC' : $order[1]) : '');
        unset($order);
        return $this;
    }

    public function addLimit(array $limit)
    {
        $this->limit = (isset($limit[0], $limit[1]) ? 'LIMIT ' . $limit[0] . ', ' . $limit[1] : '');
        unset($limit);
        return $this;
    }

    private function buildQuery()
    {
        $select = implode(', ' . $this->select_fields);
        $tables = '';
        foreach ($this->tables as $alias => $def) {
            $table = array(
                0 => '',
                1 => '',
                2 => ''
            );
            if (isset($this->joins[$alias])) {
                $table[0] = $this->joins[$alias][0];
                $table[2] = $this->joins[$alias][1];
            }
            $table[1] = $table . ' as ' . $alias;
            
            $tables .= implode(' ', $table);
        }
        unset($table, $alias, $def);
        
        $where = $this->where;
        
        $order = $this->order;
        
        $limit = $this->limit;
        
        $this->query = 'SELECT ' . $select . ' FROM ' . $tables . ' ' . $where . ' ' . $order . ' ' . $limit;
        
        $this->query = sprintf('SELECT %s FROM %s %s %s %s', $select, $tables, $where, $order, $limit);
        
        return $this;
    }
    
    public function getValues() {
        return $this->values;
    }

    public function __toString()
    {
        return $this->query;
    }
}
