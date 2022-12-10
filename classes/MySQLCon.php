<?php
class MySQLCon
{
    private string $MYSQL_HOST;
    private string $MYSQL_USER;
    private string $MYSQL_PASS;
    private string $MYSQL_BASE;

    private mysqli $MYSQL_CON;



    public function __construct($configfile)
    {
        $dbhost = $dbuser = $dbpass = $dbbase = NULL;

        if(Utils::is_cli()){
            try {
                require sprintf("%s/%s", getenv('PWD'), Utils::buildCleanString($configfile));
            } catch (Exception $ex)
            {
                die(sprintf("Configfile could not be found! <br> Error-Message: <b>%s</b>", $ex->getMessage()));
            }
        }else{
            try{
                require sprintf("%s/%s", filter_input(INPUT_SERVER, 'DOCUMENT_ROOT'), Utils::buildCleanString($configfile));
            }catch (Exception $ex)
            {
                die(sprintf("Configfile could not be found! <br> Error-Message: <b>%s</b>", $ex->getMessage()));
            }
        }

        $this->MYSQL_HOST = $dbhost;
        $this->MYSQL_USER = $dbuser;
        $this->MYSQL_PASS = $dbpass;
        $this->MYSQL_BASE = $dbbase;


        // Testing only!
        //$this->fields = array("twitchUsername" => "lusaca87", "twitchUserID" =>1687467, "APIToken" => "blablabla");
        //$this->insertInto("test" ,$this->fields );




    }
    public function printStatus()
    {
        //printf("Irgendeine Ausgabe die keinen Sinn ergibt im Moment");
    }

    /**
     * Get the mysqli connection or open a new one if needet.
     * @return false|mysqli the connection or false.
     */
    private function getMYSQLCon()
    {
        if(empty($this->MYSQL_CON))
        {
           $this->MYSQL_CON = mysqli_connect($this->MYSQL_HOST, $this->MYSQL_USER, $this->MYSQL_PASS, $this->MYSQL_BASE);
        }
        return $this->MYSQL_CON;
    }

    /**
     * Insert data into a mysql table
     * @param string $table The mysql-table where the data should be stored
     * @param array $fields The data as array which match the table-structure!
     * @return boolean true/false for success after try to insert the data
     */
    public function insertInto(string $table, array $fields): bool
    {
        $placeholders = array_fill(0, count($fields), '?');
        $keys   = array();
        //$values = array();

        foreach($fields as $k => $v) {
            $keys[] = $k;
            //$values[] = !empty($v) ? $v : null;
        }

        $stmt = $this->getMYSQLCon()->prepare(sprintf("insert into %s(%s) values (%s)", $table, implode(', ', $keys), implode(', ', $placeholders)));

        $params = array();
        foreach ($fields as &$value) {
            $params[] = &$value;
        }
        $types  = array(str_repeat('s', count($params)));
        $values = array_merge($types, $params);

        call_user_func_array(array($stmt, 'bind_param'), $values);

        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    /**
     * Select data from a table with given information
     * @param $table string The mysql-table from where you want the data
     * @param $selects array the select statement as array (Default: array("*") ).
     * @param string|null $whereCondition string Your Where condition which you want to provide. Example: "ID".
     * @param string|null $types a string of the types for parameter you want to provide. Example: "is" counts for 2 parameter. First is an integer, second a string.
     * @param array|null $params an array of parameter. Count must match with types.
     * @return array|boolean the data from the mysql-table or false
     */
    public function getMysqlArray(string $table, array $selects = array("*"), string $whereCondition = null, string $types = null, array $params = null)
    {
        if($whereCondition != null)
            $sql = sprintf("SELECT %s from %s WHERE %s = ?", implode(',', $selects), $table, $whereCondition);
        else
            $sql = sprintf("SELECT %s from %s", implode(',', $selects), $table);

        $stmt = $this->getMYSQLCon()->prepare($sql);
        if($types&&$params)
        {
            $bind_names[] = $types;
            for ($i=0; $i<count($params);$i++)
            {
                $bind_name = 'bind' . $i;
                $$bind_name = $params[$i];
                $bind_names[] = &$$bind_name;
            }
            call_user_func_array(array($stmt,'bind_param'),$bind_names);
        }

        $stmt->execute();
        $meta = $stmt->result_metadata();

        while ($field = $meta->fetch_field()) {
            $var = $field->name;
            $$var = null;
            $parameters[$field->name] = &$$var;
        }

        call_user_func_array(array($stmt, 'bind_result'), $parameters);
        $returns = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        if(count($returns) == 0)
        {
            return false;
        }else{
            return $returns;
        }
    }

    /**
     * Get then number of rows with this data
     * @param $table string The mysql-table from where you want the number of rows
     * @param $selects array the select statement as array (Default: array("*") ).
     * @param string|null $whereCondition string Your Where condition which you want to provide. Example: "ID".
     * @param string|null $types string a string of the types for parameter you want to provide. Example: "is" counts for 2 parameter. First is an integer, second a string.
     * @param array|null $params array an array of parameter. Count must match with types.
     * @return int the number from the mysql-table
     */
    public function getNumRows(string $table, array $selects = array("*"), string $whereCondition = null, string $types = null, array $params = null): int
    {
        if($whereCondition != null)
            $sql = sprintf("SELECT %s from %s WHERE %s = ?", implode(',', $selects), $table, $whereCondition);
        else
            $sql = sprintf("SELECT %s from %s", implode(',', $selects), $table);

        $stmt = $this->getMYSQLCon()->prepare($sql);
        if($types&&$params)
        {
            $bind_names[] = $types;
            for ($i=0; $i<count($params);$i++)
            {
                $bind_name = 'bind' . $i;
                $$bind_name = $params[$i];
                $bind_names[] = &$$bind_name;
            }
            call_user_func_array(array($stmt,'bind_param'),$bind_names);
        }

        $stmt->execute();
        $stmt->store_result();

        return $stmt->num_rows;
    }

    /**
     * Update a row in the table with given fields and where-conditions
     * @param string $table the mysql table.
     * @param array $fields an array which match your mysql-table data which you want to update. (eg. array("name" => "peter") );
     * @param string $whereClause a string which defines a single where statement (eg. where 'ID' = 0 ). OR is currently not supported.
     * @param string $whereParam the parameter for the where field to update the row.
     * @return bool returns true or false if the update was a success.
     */
    public function updateRow(string $table, array $fields, string $whereClause, string $whereParam):bool
    {

        $str = "";
        foreach($fields as $key => $val){
            $str .= sprintf("%s=?, ", $key);
        }
        $str = substr($str, 0, strlen($str)-2);
        $sql = sprintf("UPDATE %s SET %s WHERE %s=?", $table, $str, $whereClause);
        $stmt = $this->getMYSQLCon()->prepare($sql);

        $params = array();
        foreach ($fields as &$value) {
            $params[] = &$value;
        }
        $params[] = &$whereParam;
        $types  = array(str_repeat('s', count($params)));
        $values = array_merge($types, $params);
        call_user_func_array(array($stmt, 'bind_param'), $values);
        $succes = $stmt->execute();
        $stmt->close();
        return $succes;
    }


    public function __toString()
    {
        // TODO: Implement __toString() method.
        return "";
    }



}