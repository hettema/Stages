<?php
/**
 * class Main_Mysql
 * Database connector model for MySQL
 * 
 * @package Core
 * @subpackage Db
 * @category Lib-Object
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Main_Mysql
{
    var $host;
    var $port = 3306;
    var $userName;
    var $dbPass;
    var $database;
    var $dbLink;
    var $rowsAffected;
    var $lastInsertId;
    var $errors;
    var $dbError;
    var $dataTypes = array();
    var $logQueries;

    /**
     * Class Constructor establishes connection to the mysql database
     *
     */
    function  __construct($dbParams = array())
    {
       $this->setFieldTypes();
       $this->connect($dbParams);
       $this->logQueries = false;
       //$this->setTimeZone(date_default_timezone_get());
       return $this;
    }

    /**
     * Sets the field types allowed for each of the data types
     *
     */
    function setFieldTypes()
    {
        $this->dataTypes['int'] = array("int", "tinyint", "smallint", "mediumint", "bigint", "integer", "bool", "boolean");
        $this->dataTypes['decimal']   = array("float", "double", "decimal", "numeric");
        $this->dataTypes['date']    = array("date", "datetime", "timestamp", "time", "year");
        $this->dataTypes['string']  = array("char", "varchar", "tinytext", "tinyblob", "text", "blob", "mediumtext", "mediumblob" , "longtext", "longblob", "binary", "varbinary", "enum", "set");
    }

    /**
     * Set db connection parameters in to the class values
     * Values can be passed to the function to connect to a different mysql database than the normal one.
     * @param string $host
     * @param integer $port
     * @param string $userName
     * @param string $dbPass
     * @param string $database
     */
    function setDbParams ($params = array())
    {
        global $connectDb;

        $this->host     = !empty($params['host'])     ? $params['host']     : $connectDb["host"];
        $this->port     = !empty($params['port'])     ? $params['port']     : $connectDb["port"];
        $this->userName = !empty($params['userName']) ? $params['userName'] : $connectDb["user"];
        $this->dbPass   = !empty($params['dbPass'])   ? $params['dbPass']   : $connectDb["pass"];
        $this->database = !empty($params['database']) ? $params['database'] : $connectDb["datb"];
        $this->charSet  = !empty($params['charSet'])  ? $params['charSet']  : $connectDb["charSet"];
    }

    /**
     * Establish connection to the database
     * If the connection parameters are not set in the object this will try to run
     *
     * @return string error on exception
     */
    function connect ($params = array())
    {
        if(empty($this->host) || empty($this->userName)) {
            self::setDbParams($params);
        }

        // Return error in case on an incomplete configuration
        if(empty($this->host)) $this->throwError("Mysql Connect Error : Host not specified");
        if(empty($this->port)) $this->throwError("Mysql Connect Error : Mysql Port number is missing");
        if(empty($this->userName)) $this->throwError("Mysql Connect Error : No Username");


        $er = ini_get('display_errors');
        ini_set('display_errors', 0);
        try { // Establish connection to the database
            $this->dbLink = mysql_connect("$this->host:$this->port", $this->userName, $this->dbPass);
        } catch (Exception $e) {
            $this->throwError($e->toString());
        }
        ini_set('display_errors', $er);

        if (!$this->dbLink) {
            $this->throwError(mysql_error());
        }
        //Return if there is an error
        if(!empty($this->errors)) { return false; }

        if(!empty($this->database)) {
            mysql_select_db($this->database);
        }
        if(!empty($this->charSet)) {
            //echo mysql_client_encoding();
            mysql_set_charset($this->charSet,$this->dbLink);
            //echo "[ -" .mysql_client_encoding();
        }
        return $this;
    }

    /**
     * Closes the mysql connection
     *
     */
    function close ()
    {
        mysql_close($this->dbLink);
    }

    /**
     * Sets the MySQL Time zone
     *
     * @param string $UTCtimeDif
     */
    function setTimeZone ($UTCtimeDif)
    {
        self::queryDb("SET SESSION time_zone = '$UTCtimeDif'");
    }

    /**
     * Enable logging of the query strings
     */
    function startQueryLogging() {
        $this->logQueries = true;
    }

    /**
     * Stop logging of the query strings
     */
    function stopQueryLogging() {
        $this->logQueries = false;
    }

    /**
     * Do a query on the database
     * @param string $query
     * @param bool $assocField
     * @return array result
     */
    function query ($query, $assocField = true)
    {
        /*** LOG Query **************/
        if ($this->logQueries) {
            $qrFile = BP . DS .'var'. DS .'queries.sql';
            if(!file_exists($qrFile)) {
                $fh = fopen($qrFile,'w');
                fclose($fh);
            }
            $prevQr = file_get_contents($qrFile);
            file_put_contents($qrFile,$prevQr . "\r\n" . $query);
        }
        /********************************************/

        $result                = mysql_query($query) or die(mysql_error());
        $this->rowsAffected    = mysql_affected_rows();

        $return = array(); 
        if ($this->_queryReturnsResult($query)) {
            if(!$assocField) {
                while($row = mysql_fetch_array($result, MYSQL_NUM)) {
                    $return[] = $row;
                }
            } else {
                while ($row = mysql_fetch_assoc($result)) {
                    $return[] = $row;
                }
            }
        } else if($this->_isAnInsert($query)) {
            $return = self::lastInsertId();
        }

        return (!empty($this->rowsAffected) && ! empty($return) ? $return : null);
    }

    /** 
     * Get the type of the query from the query string
     * Returns the first word on the query string
     *
     * @param string $sql
     * @return string 
     */
    protected function _getQueryType($sql)
    {
        $queryParts = preg_split("/[\s,]+/", ltrim($sql));
        return strtoupper(array_shift($queryParts));
    }

    /**
     * Check whether to return a result set for the query from the query type
     * 
     * @param string $sql
     * @return bool 
     */
    protected function _queryReturnsResult($sql)
    {
        $arrayQueryReturnTypes = array("SELECT" , "DESCRIBE", "SHOW");        
        return in_array($this->_getQueryType($sql), $arrayQueryReturnTypes);
    }

    /**
     * Check whether the query is an insert query
     * 
     * @param string $sql
     * @return bool 
     */
    protected function _isAnInsert($sql)
    {
        return $this->_getQueryType($sql) == 'INSERT';
    }

    /**
     * Get the fist rows first field from the resultset
     *
     * @param string $sql
     * @return mixed 
     */
    public function fetchOne($sql)
    {
        $result = $this->query($sql, false);
        if(!empty($result[0]) && !empty($result[0][0])) { 
            return $result[0][0];
        }
        return false;
    }

    /**
     * Get the first|prefered colum from the result set associated to a specific field's value
     *
     * @param string $sql
     * @param string $column colum whose value will be set as the array element value
     * @param string $keyField filed name whose value will be set as the key of each array element
     * @return array 
     */
    public function fetchColumn($sql, $column = false, $keyField = false)
    {
        $result = $this->fetchAssoc($sql);
        if(empty($result) || !is_array($result)) {
            return false;
        }
        if(!$column) {
            $columns = array_keys($result[0]);
            $column = $columns[0];
        }
        
        $return = array();
        for($idx = 0; $idx < count($result); $idx++) {
            $row = $result[$idx];
            if(!isset($row[$column])) continue;
            
            //identify the key for the array node
            $key = (!empty($keyField) && isset($row[$keyField])) ? $row[$keyField] : $idx;
            $return[$key] = $row[$column];
        }
        return $return;
    }
    
    /**
     * Get the first row of the result set as associative array
     * 
     * @param string $sql
     * @return array 
     */
    public function fetchRow($sql)
    {
        $result = $this->fetchAssoc($sql);
        if(!empty($result[0])) { return $result[0]; }
        return false;
    }

    /**
     * 
     * @param string $sql
     * @return array 
     */
    public function fetchAssoc($sql)
    {
        return $this->query($sql);
    }

    /**
     *
     * @param string $sql
     * @return array 
     */
    public function fetchAll($sql)
    {
        return $this->query($sql);
    }

    /**
     * Get all the tables defined in the acitive database
     * 
     * @return array 
     */
    public function getTables()
    {
        return $this->fetchColumn("SHOW TABLES");
    }

    /**
     *
     * @param string $table
     * @return array table details 
     */
    public function describeTable($table)
    {
        $result = $this->fetchAll("DESCRIBE ". $table);
        $return = array();
        foreach ($result as $field) {
            $field['data_type'] = $this->getFieldTypeParsed($field['Type']);
            $return[$field['Field']] = $field;
        }
        return $return;
    }

    /**
     * Get the field type
     * 
     * @param string $fieldType
     * @return string 
     */
    public function getFieldTypeParsed($fieldType)
    {
        foreach($this->dataTypes as $type=>$types) {
            foreach($types as $subType) {
                if(stristr($fieldType, $subType)) {
                    return $type;
                }
            }
        }
        return 'string';
    }

    /**
     * Inset the data into the specified table
     * The idField is used to identify the profary field so that if it is set in $data
     * it is nullified
     *
     * @param string $table
     * @param array $data
     * @param string $_idField
     * @return bool 
     */
    function insert ($table, $data, $_idField = null)
    {
        if(!empty($_idField) && !empty ($data[$_idField])) {
            unset($data[$_idField]);
        }
        $_sql =  "INSERT INTO ". $table ." (". implode(',', array_keys($data)) .") VALUES (". implode(',', $data) .")";
        return $this->query($_sql);
    }

    /**
     * Update the data into the specified table identified by the idField
     *
     * @param string $table
     * @param array $data
     * @param string $_idField
     * @return bool 
     */
    function update ($table, $data, $_idField)
    {
        if(!empty($_idField) && !empty ($data[$_idField])) {
            $_entityId = $data[$_idField];
            unset($data[$_idField]);
        }
        $fields = array_keys($data);
        $updates = array();
        foreach($fields as $field) {
            $updates[] = $field ."=". $data[$field];
        }
        if(empty($updates)) { return true; }
        $_sql =  "UPDATE ". $table ." SET ". implode(',', $updates) ." WHERE ". $_idField . "=" . $_entityId;
        return $this->query($_sql);
    }

    /**
     * Delete one row from $table based on $condition
     * 
     * @param string $table
     * @param string $condition
     * @return type 
     */
    function delete($table, $condition) {
        $query = "DELETE FROM ". $table ." WHERE ". $condition ." LIMIT 1";
        return $this->query($query);
    }

    /**
     * Get the last insert id
     * 
     * @return int 
     */
    function lastInsertId()
    {
        $this->lastInsertId = $this->fetchOne("SELECT LAST_INSERT_ID() AS last_id;");
        return $this->lastInsertId;
    }

    /**
     * Get the number of rows affected by the last query
     * 
     * @return int 
     */
    function rowsAffected()
    {
        return $this->rowsAffected;
    }

    /**
     * Throw exception on an error
     *
     * @param string $str
     * @return bool 
     */
    private function throwError($str)
    {
        return App_Main::throwException($str);
    }

    public function getErrors()
    {
        if(!empty($this->errors)) {
            return $this->errors;
        }
        return false;
    }
}
