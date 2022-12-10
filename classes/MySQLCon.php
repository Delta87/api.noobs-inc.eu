<?php
class MySQLCon
{
    private $MYSQL_HOST = NULL;
    private $MYSQL_USER = NULL;
    private $MYSQK_PASS = NULL;
    private $MYSQL_BASE = NULL;

    private $MYSQL_CON = NULL;
    private $MYSQL_DATABSE = NULL;



    public function __construct($configfile)
    {
        $dbhost = $dbuser = $dbpass = $dbbase = NULL;

        if(Utils::is_cli()){
            require sprintf("%s/%s", getenv('PWD'), Utils::buildCleanString($configfile));
        }else{
            require sprintf("%s/%s", filter_input(INPUT_SERVER, 'DOCUMENT_ROOT'), Utils::buildCleanString($configfile));
        }

        $this->MYSQL_HOST = $dbhost;
        $this->MYSQL_USER = $dbuser;
        $this->MYSQK_PASS = $dbpass;
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
        if($this->MYSQL_CON == NULL)
        {
            $this->MYSQL_CON = mysqli_connect($this->MYSQL_HOST, $this->MYSQL_USER, $this->MYSQK_PASS, $this->MYSQL_BASE);

        }
        return $this->MYSQL_CON;
    }

    /**
     * Insert data into a mysql table
     * @param $table MySQLCon The mysql-table where the data should be stored
     * @param $fields array The data as array which match the table-structure!
     * @return boolean true/false for success after try to insert the data
     */
    public function insertInto($table, $fields)
    {
        ;

        $placeholders = array_fill(0, count($fields), '?');
        $keys   = array();
        $values = array();

        foreach($fields as $k => $v) {
            $keys[] = $k;
            $values[] = !empty($v) ? $v : null;
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
     * @param $table string The mysql-table from wher you want the data
     * @param $selects array the select statement as array (Default: array("*") ).
     * @param $whereCondition string Your Where condition which you want to provide. Example: "ID".
     * @param $types string an string of the types for parameter you want to provide. Example: "is" counts for 2 parameter. First is an integer, second a string.
     * @param $params array an array of parameter. Count must match with types.
     * @return array the data from the mysql-table
     *
     */
    public function getMysqlArray($table, $selects = array("*"), $whereCondition = null, $types = null, $params = null)
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
            $return = call_user_func_array(array($stmt,'bind_param'),$bind_names);
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
        return $returns;
    }




    public function __toString()
    {
        // TODO: Implement __toString() method.
        return "";
    }



}