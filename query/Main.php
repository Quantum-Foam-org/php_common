<?php

namespace \local\classes\query;

/**
 * Creates a SQL select statement and returns the result set
 */
class Main {
	
	private $field_def = array();
	private $select_fields = array();
	private $tables = array();
	private $joins = array();
	private $order = '';
	private $limit = '';
	private $query = '';
	
	
	/**
	 * @param array $fields - The field definition
	 */
	public function add_fields(array $fields) {
		foreach ($fields as $def) {
			$this->field_def[] = $def;
			$this->select_fields[] = (strlen($def[1]) ? $def[1].'.' : '').$def[0];
		}
		unset($fields, $def);
		return $this;
	}
	
	public function add_tables(array $tables) {
		foreach ($tables as $def) {
			if (!isset($def[1], $def[0])) {
				throw new Exception('Must set table and table alias', -1);
			} else { 
				$this->tables[$def[1]] = $def[0];
			}
		}
		unset($tables, $def);
		return $this;
	}
	
	public function add_joins(array $joins) {
		foreach ($joins as $def) {
			if (isset($def[0], $def[1], $def[2])) {
				throw new Exception('Must set table alias, join type and join condition', -1);
			} else {
				$this->joins[$def[0]] = array($def[1], $def[2]);
			}
		}
		unset($joins, $def);
		return $this;
	}
	
	public function add_order(array $order) {
		$this->order_by = 'ORDER BY '.$order[0].' '.(!isset($order[1]) ? 'ASC' : $order[1]);
		unset($order);
		return $this;
	}
	
	public function add_limit(array $limit) {
		$this->limit = 'LIMIT '.$limit[0].', '.$limit[1];
		unset($limit);
		return $this;
	}
	
	public function get_rows() {
		$query = $this->build_query();
		
		return db::obj()->fetch_all($query);
	}
	
	private function build_query() {
		$select = implode(', '. $this->select_fields);
		$tables = '';
		foreach ($this->tables as $alias => $def) {
			$table = array(0=>'',1=>'',2=>'');
			if (isset($this->joins[$alias])) {
					$table[0] = $this->joins[$alias][0];
					$table[2] = $this->joins[$alias][1];
			}
			$table[1] = $table.' as '.$alias;
			
			$tables .= implode(' ', $table);
		}
		unset($table, $alias, $def);
		
		$order = $this->order_by;
		
		$limit = $this->limit;
		
		$this->query = 'SELECT '.$select.' FROM '.$tables.' '.$order.' '.$limit;
		
		return $this;
	}
	
}
