<?php

namespace \common\db\MySQL;

Class Where {
    
    private $where = [];
    private $values = [];
    
    /**
     * Creates a where clause for SQL queries
     * 
     * @param array $whereDef an multidimensional array with index 0 a logical operator and index 1 an expression 
     * @throws Exception
     * @return string the where clause
     */
    public function getWhere() : string {
        $where = '';
        
        foreach ($this->where as $def) {
            $where .= implode(' ', $def);
        }
        unset($def);
        
        if (strlen($where) > 0) {
            $where = 'WHERE '.$where;
        }
        
        return $where;
    }

    /**
     * Will add a where expression
     *
     * @param string $logicalOp one of null, AND, NOT, OR, XOR
     * @param string $parenthesisOpen a ( character which is optional 
     * @param string $field a field from the database table
     * @param string $compOp a comparison operator
     * @param string $parenthesisClose a ) character which is optional
     * @param string $value the value of the expression
     */
    public function addWhereExpression(?string $logicalOp, ?string $parenthesisOpen, string $field, string $compOp, ?string $parenthesisClose, string $value) : void
    {
        $logicalOpAc = array(
            null,
            'AND',
            'NOT',
            'OR',
            'XOR'
        );
        if (! in_array($logicalOp, $logicalOpAc)) {
            throw new Exception('Can only use ' . implode(', ', $logicalOpAc), - 1);
        }
        
        $this->where[] = [
            $logicalOp,
            ($parenthesisOpen !== '(' ? $parenthesisOpen : ''),
            '`' . $field . '`',
            $compOp,
            '?',
            ($parenthesisClose !== ')' ? $parenthesisClose : '')
        ];

        $this->values[] = $value;
    }
    
    /**
     * Returns an array that can be used in PDOStatment::execute
     * @return array
     */
    public function getValues() : array {
        return $this->values;
    }
}


?>