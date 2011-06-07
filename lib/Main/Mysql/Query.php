<?php
/**
 * class Main_Mysql_Query
 * MySQL query helper object
 * 
 * @package Core
 * @subpackage Db
 * @category Lib-Object
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Main_Mysql_Query
{
    private $_selectColumns = array();
    private $_conditions = array();
    private $_conditionTypes = array('WHERE', 'ORDER', 'HAVING', 'GROUP', 'LIMIT');
    private $_sqlTableBody = array();
    private $_countColumn = false;
    
    /**
     * Add a colum to query
     * 
     * @param string $column
     * @return Main_Mysql_Query 
     */
    public function queryColumn($column)
    {
        if(is_array($column)) {
            foreach($column as $selectColumn) {
                $this->_selectColumns[] = $selectColumn;
            }
        } else {
            $this->_selectColumns[] = $column;
        }
        return $this;
    }

    /**
     * Adda table to the query stack
     * $mode can be set for those tables which are not used in conditions so those 
     * can be excluded while getting the counts
     * 
     * @param string $tblSql
     * @param string $mode
     * @return Main_Mysql_Query 
     */
    public function queryTable($tblSql, $mode = 'main') 
    {
        $this->_sqlTableBody[$mode][] = $tblSql;
        return $this;
    }

    /**
     * Add a new condition to the stack of conditions
     * 
     * @param string $cond
     * @param string $type
     *  - WHERE
     *  - GROUP
     *  - HAVING
     *  - ORDER
     *  - LIMIT
     * @return Main_Mysql_Query 
     */
    public function queryCondition($cond, $type = 'WHERE')
    {
        $type = in_array($type, $this->_conditionTypes) ? $type : 'WHERE';
        $this->_conditions[$type][] = $cond;
        return $this;
    }

    /**
     * Add a new order condition
     * 
     * @param string $field
     * @param string $order
     * @return type 
     */
    public function orderCondition($field, $order = null)
    {
        return $this->queryCondition($field .' '. $order, 'ORDER');
    }

    /**
     * Get the query colums to be fetched
     * 
     * @return string 
     */
    public function getQueryColumns()
    {
        if(empty($this->_selectColumns)) { return '*'; }
        if(!is_array($this->_selectColumns)) { return $this->_selectColumns; }
        return implode(', ', $this->_selectColumns);
    }

    /**
     * Get the tables to be quried
     * 
     * @param string $type
     * @return string 
     */
    public function getQueryTables($type = 'all')
    {
        if($type=='main') {
            return implode(' ',  $this->_sqlTableBody['main']);
        }
        $tableSql = "";

        foreach($this->_sqlTableBody as  $tableBody) {
            $tableSql .= (is_array($tableBody) ? implode(' ',  $tableBody) : $tableBody) . ' ';
        }
        return $tableSql;
    }

    /**
     * Get the qiery condition of the specified type
     * @param string $type
     *  - WHERE
     *  - GROUP
     *  - HAVING
     *  - ORDER
     *  - LIMIT
     * 
     * @param string $type
     * @return string 
     */
    public function getQueryCondition($type)
    {
        $type = strtoupper($type);
        if(empty($this->_conditions[$type])) { return false; }

        switch($type)
        {
            case 'WHERE':
            case 'HAVING':
                return $type . ' ' . implode(' AND ', $this->_conditions[$type]);
            break;

            case 'GROUP':
            case 'ORDER':
                return $type . ' BY ' . implode(', ', $this->_conditions[$type]);
            break;

            case 'LIMIT':
                return ' LIMIT ' . $this->_conditions[$type][0] ;
            break;
        }
    }

    /**
     * Set the count column, used for the count query
     * 
     * @param string $field
     * @return Main_Mysql_Query 
     */
    public function setCountColumn($field)
    {
        $this->_countColumn = $field;
        return $this;
    }
    
    /**
     *
     * @return string 
     */
    public function getCountColumn()
    {
        return $this->_countColumn;
    }

    /**
     * Reset the query object
     * 
     * @return Main_Mysql_Query 
     */
    public function resetQuery()
    {
        $this->_selectColumns = array();
        $this->_conditions = array();
        $this->_sqlTableBody = array();
        return $this;
    }

    /**
     * Prepare the count query
     * 
     * @return string 
     */
    public function prepareCountQuery()
    {
        $countCond = $this->getCountColumn() ? "count(DISTINCT ". $this->getCountColumn() .")" : "count(*)";
        $sql = "SELECT
                    ". $countCond ." AS count
               FROM
               ". $this->getQueryTables('main') . "
               ". $this->getQueryCondition('WHERE') . "
               ". $this->getQueryCondition('HAVING') . "
               ";
        return $sql;
    }

    /**
     * Prepare the main query
     * 
     * @return string 
     */
    public function prepareQuery()
    {
       $sql = "SELECT
                    ". $this->getQueryColumns() ."
               FROM
               ". $this->getQueryTables('all') . "
               ". $this->getQueryCondition('WHERE') . "
               ". $this->getQueryCondition('GROUP') . "
               ". $this->getQueryCondition('HAVING') . "
               ". $this->getQueryCondition('ORDER') . "
               ". $this->getQueryCondition('LIMIT') . "
               ";
       return $sql;
    }
}
?>
